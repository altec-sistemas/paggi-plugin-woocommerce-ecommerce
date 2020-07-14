<?php

/**
 * WooCommerce Paggi API class
 *
 * @version 0.3.3
 */
if (!defined('ABSPATH')) {
    exit;
}

require_once(__DIR__.'/sdk/vendor/autoload.php');
use \Paggi\SDK\EnviromentConfiguration;
use \Paggi\SDK\Card;
use \Paggi\SDK\Order;

class WC_Paggi_API {

    /**
     * Gateway class.
     *
     * @var WC_Gateway_Paggi
     */
    protected $gateway;

    protected $enviroment;

    protected $api_url;

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
        if ($this->gateway->sandbox === 'yes') 
        {
            $this->enviroment = new \Paggi\SDK\EnvironmentConfiguration();
            $this->enviroment->setEnv('Staging');
        } else {
            $this->enviroment = new \Paggi\SDK\EnvironmentConfiguration();
            $this->enviroment->setEnv('Production');
        }

        $this->enviroment->setToken($this->gateway->token);
        $this->enviroment->setPartnerIdByToken($this->gateway->token);
    }

    /**
     * Get API URL.
     *
     * @since 0.1.0
     * @return string
     */
    public function get_api_url() {
        if($this->gateway->sandbox === 'yes') {
            return 'https://api.stg.paggi.com/v1';
        } else {
            return 'https://api.paggi.com/v1';
        }
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
     * create a card to a order
     *
     * @since 0.1.0
     * @param string $holder
     * @param string $document
     * @param string $number
     * @param string $expire
     * @param string $cvc
     * @return array
     */
    public function set_card($holder, $document, $number, $expire, $cvc) {
        $expire = $this->only_numbers($expire);
        $month = substr($expire, 0, 2);
        $year = substr($expire, -4);
        $document = strtr($document, array(
            '-' => '',
            '.' => '',
            '/' => ''
            )
        );
        $data = array(
            'document' => $document,
            'holder'   => $holder,
            'number' => $this->only_numbers($number),
            'month'  => $this->only_numbers($month),
            'year'   => $this->only_numbers($year),
            'cvc'    => $this->only_numbers($cvc)
        );

        $resource = new \Paggi\SDK\Card();

        return $resource->create($data);
    }

    /**
     * Get all partner cards from a document customer 
     *
     * @since 0.1.0
     * @param string $document
     * @return array
     */
    public function get_card($document) {
        $document = strtr($document, array(
            '-' => '',
            '.' => '',
            '/' => ''
            )
        );
        
        $data = array('document' => $document);

        $resource = new \Paggi\SDK\Card();
                
        return $resource->find($data);
    }

    /**
     *  inactivate a card in Paggi
     *
     * @since 0.1.0
     * @param string $card_id   
     */
    public function del_card($card_id) {
        if ($card_id == "" && isset($_REQUEST['data'])) {
            $card_id = $_REQUEST['data'];
        }

        $resource = new \Paggi\SDK\Card();
        $return = $resource->delete($card_id);
        
        if (isset($return->code) && $return->code == 204) {
            wp_send_json(array('code' => '204', 'message' => __('Card deleted successfuly.', 'woocommerce-paggi')));
        } else {
            wp_send_json(array('code' => '500', 'message' => __('An error has occurred. Try Again', 'woocommerce-paggi')));
        }
    }

    /**
    * create a card to a order
    *
    * @since 0.1.0
    * @param string $card_id
    * @param string $installments
    * @param string $name
    * @param string $email
    * @param string $document
    * @param string $phone
    * @param string $street
    * @param string $neighborhood
    * @param string $zipcode
    * @param string $city
    * @param string $state
    * @param string $number
    * @param string $complement
    * @param string $ip
    * @param string $external_identifier
    */
    public function process_payment($card_id, $installments, $amount, $name, $email, $document, $phone, $street, $neighborhood, $zipcode, $city, $state, $number, $complement, $ip, $external_identifier) {
        if ($complement === '' && $neighborhood === ''){ 
            $data = array( 
                'ip' => $ip,
                'external_identifier' => strval($external_identifier),
                'charges' => array(array(
                    'installments' => $this->only_numbers($installments),
                    'amount' => $this->only_numbers($amount),
                    'card' => array (
                        'id' => $card_id
                    )
                )), 
                'customer' => array(
                    'name' => $name,
                    'email' => $email,
                    'document' => $this->only_numbers($document),
                    'phone1' => $this->only_numbers($phone),
                    
                )
            );
        } else {
            $data = array( 
                'ip' => $ip,
                'external_identifier' => strval($external_identifier),
                'charges' => array(array(
                    'installments' => $this->only_numbers($installments),
                    'amount' => $this->only_numbers($amount),
                    'card' => array (
                        'id' => $card_id
                    )
                )), 
                'customer' => array(
                    'name' => $name,
                    'email' => $email,
                    'document' => $this->only_numbers($document),
                    'phone1' => $this->only_numbers($phone),
                    'address' => array(
                        'street' => $street,
                        'neighborhood' => $neighborhood,
                        'zipcode' => $this->only_numbers($zipcode),
                        'city' => $city,
                        'state' => $state,
                        'number' => $this->only_numbers($number),
                        'complement' => $complement                    
                    )
                )
            );
        }

        $resource = new \Paggi\SDK\Order();
    
        return $resource->create($data);
    }

    /**
     * Void order
     *
     * @since 0.1.0
     * @param string $transaction_id
     * @param string $order_id
     * @return array
     */
    public function cancel_regular_payment($transaction_id, $order_id) {
        $resource = new \Paggi\SDK\Order();
        return $resource->cancel($transaction_id);    
    }
}