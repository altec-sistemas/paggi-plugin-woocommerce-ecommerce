<?php

/**
 * WooCommerce Paggi API class
 *
 * @version 0.3.3
 */
if (!defined('ABSPATH')) {
    exit;
}

class WC_Paggi_API {

    /**
     * API URL.
     */
    const API_URL = 'https://online.paggi.com/api/v4/';

    /**
     * Gateway class.
     *
     * @var WC_Gateway_Paggi
     */
    protected $gateway;

    /**
     * Constructor.
     *
     * @since 0.1.0
     * @param WC_Payment_Gateway $gateway Gateway instance.
     */
    public function __construct($gateway = null) {
        $this->gateway = $gateway;
        add_action('wp_ajax_delcard', array($this, 'del_card'));
        add_action('wp_ajax_cancelregularpayment', array($this, 'cancel_regular_payment'));
    }

    /**
     * Get API URL.
     *
     * @since 0.1.0
     * @return string
     */
    public function get_api_url() {
        return $this->API_URL;
    }

    /**
     * Only numbers.
     *
     * @since 0.1.0
     * @param  string|int $string String to convert.
     * @return string|int
     */
    protected function only_numbers($string) {
        return preg_replace('([^0-9])', '', $string);
    }

    /**
     * Perform the comunication with Paggi API
     *
     * @since 0.1.0
     * @param string $action
     * @param string $method
     * @param array $data
     * @return array
     */
    public function ws_comunicate($action = null, $method = null, $data = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::API_URL . '/' . $action);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->gateway->get_token());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($data)
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        if(array_key_exists('X_FORWARDED_FOR', $_SERVER)) {
          curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json', "X-Forwarded-For: {$_SERVER['X_FORWARDED_FOR']}"
          ));
        }
        else {
          curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json', "X-Forwarded-For: {$_SERVER['REMOTE_ADDR']}"
          ));
        }

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        $error = curl_error($ch);
        if ($error != "") {
            $this->gateway->log->add($this->gateway->id, 'Call Paggi API error: ' . $error);
            curl_close($ch);
            return array('code' => '500', 'message' => $error); // erro
        } else {
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            switch ($httpcode) {
                case '200':
                    curl_close($ch);
                    $result = json_decode($result, true);
                    $result['Status']['Code'] = '200';
                    break;
                case '204':
                    curl_close($ch);
                    $result = json_decode($result, true);
                    $result['Status']['Code'] = '200';
                    break;
                case '400':
                    curl_close($ch);
                    $result = json_decode($result, true);
                    $result['Status']['Code'] = '400';
                    $result['Status']['Name'] = 'Bad Request';
                    $result['Status']['Description'] = __('Something went wrong. Often a required param is missing', 'woocommerce-paggi');
                    break;
                case '401':
                    curl_close($ch);
                    $result = json_decode($result, true);
                    $result['Status']['Code'] = '401';
                    $result['Status']['Name'] = 'Unauthorized';
                    $result['Status']['Description'] = __('Not a valid API Key', 'woocommerce-paggi');
                    break;
                case '402':
                    curl_close($ch);
                    $result = json_decode($result, true);
                    $result['Status']['Code'] = '402';
                    $result['Status']['Name'] = 'Request failed';
                    $result['Status']['Description'] = __('Params were right, but something failed, also used to rejected charges', 'woocommerce-paggi');
                    break;
                case '404':
                    curl_close($ch);
                    $result = json_decode($result, true);
                    $result['Status']['Code'] = '404';
                    break;
                case '410':
                    curl_close($ch);
                    $result['message'] = $result;
                    $result['Status']['Code'] = '410';
                    $result['Status']['Name'] = 'Not found';
                    $result['Status']['Description'] = __('Requested item doesn\'t exists', 'woocommerce-paggi');
                    break;
                case '422':
                    curl_close($ch);
                    $result = json_decode($result, true);
                    $result['Status']['Code'] = '422';
                    $result['Status']['Name'] = 'Unprocessable Entity';
                    $result['Status']['Description'] = __('Some param is invalid', 'woocommerce-paggi');
                    break;
                case '500':
                    curl_close($ch);
                    $result = json_decode($result, true);
                    $result['Status']['Code'] = '500';
                    $result['Status']['Name'] = 'Server Error';
                    $result['Status']['Description'] = __('Something went wrong on our servers', 'woocommerce-paggi');
                    break;
                case '501':
                    curl_close($ch);
                    $result = json_decode($result, true);
                    $result['Status']['Code'] = '501';
                    $result['Status']['Name'] = 'Server Error';
                    $result['Status']['Description'] = __('Feature not available yet', 'woocommerce-paggi');
                    break;
                default:
                    curl_close($ch);
                    $result = json_decode($result, true);
                    $result['Status']['Code'] = 'NA';
                    $result['Status']['Name'] = __('Not Available', 'woocommerce-paggi');
                    $result['Status']['Description'] = __('Not Available', 'woocommerce-paggi');
                    ;
                    break;
            }
        }
        return $result;
    }

    /**
     * Set Customer
     *
     * @since 0.1.0
     * @param string $name
     * @param string $email
     * @param string $document
     * @param string $phone
     * @param string $street
     * @param string $district
     * @param string $city
     * @param string $state
     * @param string $zip
     * @return array
     */
    public function set_customer($name, $email, $document, $phone, $street, $district, $city, $state, $zip) {
        $data = array(
            'name' => $name,
            'email' => $email,
            'document' => $this->only_numbers($document),
            'phone' => $this->only_numbers($phone),
            'address' => array(
                'street' => $street,
                'neihborhood' => $district,
                'city' => $city,
                'state' => $state,
                'zip' => $zip
            )
        );

        $action = 'customers';
        $method = 'POST';
        return $this->ws_comunicate($action, $method, $data);
    }

    /**
     * Update customer
     *
     * @since 0.1.0
     * @param string $paggi_id
     * @param string $name
     * @param string $email
     * @param string $document
     * @param string $phone
     * @param string $street
     * @param string $district
     * @param string $city
     * @param string $state
     * @param string $zip
     * @return array
     */
    public function update_customer($paggi_id, $name, $email, $document, $phone, $street, $district, $city, $state, $zip) {
        $data = array(
            'name' => $name,
            'email' => $email,
            'document' => $this->only_numbers($document),
            'phone' => $this->only_numbers($phone),
            'address' => array(
                'street' => $street,
                'neihborhood' => $district,
                'city' => $city,
                'state' => $state,
                'zip' => $zip
            )
        );

        $action = 'customers/' . $paggi_id;
        $method = 'PUT';
        return $this->ws_comunicate($action, $method, $data);
    }

    /**
     * list Paggi customers
     *
     * @since 0.1.0
     * @param string $paggi_id
     * @return array
     */
    public function list_customer($paggi_id) {
        $data = null;
        $action = 'customers/' . $paggi_id;
        $method = 'GET';
        return $this->ws_comunicate($action, $method, $data);
    }

    /**
     * add card to customer
     *
     * @since 0.1.0
     * @param string $paggi_id
     * @param string $name
     * @param string $number
     * @param string $expiry
     * @param string $cvc
     * @return array
     */
    public function set_card($paggi_id, $name, $number, $expiry, $cvc) {
        $expiry = $this->only_numbers($expiry);
        $month = substr($expiry, 0, 2);
        $year = substr($expiry, -2);
        $data = array(
            'customer_id' => $paggi_id,
            'name'   => $name,
            'number' => $this->only_numbers($number),
            'month'  => $this->only_numbers($month),
            'year'   => $this->only_numbers($year),
            'cvc'    => $this->only_numbers($cvc)
        );

        $action = 'cards';
        $method = 'POST';
        return $this->ws_comunicate($action, $method, $data);
    }

    /**
     *  exclude a card in Paggi
     *
     * @since 0.1.0
     * @param string $card_id     *
     */
    public function del_card($card_id) {
        if ($card_id == "" && isset($_REQUEST['data'])) {
            $card_id = $_REQUEST['data'];
        }
        $data = null;
        $action = 'cards/' . $card_id;
        $method = 'DELETE';
        $return = $this->ws_comunicate($action, $method, $data);
        if (isset($return['Status']['Code']) && $return['Status']['Code'] == '200') {
            wp_send_json(array('code' => '200', 'message' => __('Card deleted successfuly.', 'woocommerce-paggi')));
        } else {
            wp_send_json(array('code' => '500', 'message' => __('An error has occurred. Try Again', 'woocommerce-paggi')));
        }
    }

    /**
     * Send payment request to Paggi
     *
     * @since 0.1.0
     * @param string $amount
     * @param string $customer_id
     * @param string $card_id
     * @param string $installments
     * @return array
     */
    public function process_regular_payment($amount, $customer_id, $card_id, $installments) {
        $data = array(
            'amount' => $this->only_numbers($amount),
            'customer_id' => $customer_id,
            'card_id' => $card_id,
            'installments_number' => $installments
        );

        $action = 'charges';
        $method = 'POST';
        return $this->ws_comunicate($action, $method, $data);
    }

    /**
     * Cancel transaction in Paggi
     *
     * @since 0.1.0
     * @param string $transaction_id
     * @param string $order_id
     */
    public function cancel_regular_payment($transaction_id, $order_id) {
        if ($transaction_id == "" && isset($_REQUEST['data'])) {
            $transaction_id = $_REQUEST['data'];
            $order_id = $_REQUEST['order_id'];
        }
        $data = null;
        $action = 'charges/' . $transaction_id . "/cancel";
        $method = 'PUT';
        $return = $this->ws_comunicate($action, $method, $data);
        if (isset($return['status']) && $return['status'] == 'cancelled') {
            $order = new WC_Order($order_id);
            $order->add_order_note(sprintf(__('Paggi: Transaction was canceled (id = %s.)', 'woocommerce-paggi'), $transaction_id));
            update_post_meta($order_id, 'paggi_canceled', 'TRUE');
            wp_send_json(array('code' => '200', 'message' => __('Payment was canceled successfuly.', 'woocommerce-paggi')));
        } else {
            wp_send_json(array('code' => '500', 'message' => __('An error has occurred. Try Again', 'woocommerce-paggi')));
        }
    }

}
