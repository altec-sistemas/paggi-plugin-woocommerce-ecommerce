<?php

/**
 * WooCommerce Gateway Paggi class
 *
 * @version 0.3.3
 */
class WC_Paggi_Gateway extends WC_Payment_Gateway {

    /**
     * Constructor for the gateway.
     *
     * @since 0.1.0
     */
    public function __construct() {

        $this->id = 'paggi_gateway';
        $this->has_fields = false;
        $this->method_title = __('Paggi', 'woocommerce-paggi');
        $this->method_description = __('Accept payments by credit card using the Paggi.', 'woocommerce-paggi');//
        $this->order_button_text = __('Proceed to payment', 'woocommerce-paggi');
        $this->supports[] = 'refunds';

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables
        $this->partner_id = $this->get_option('partner_id');
        $this->title = $this->get_option('paggi_title', 'Paggi');
        $this->description = $this->get_option('paggi_description');
        $this->instructions = $this->get_option('paggi_instructions', $this->description);
        $this->token = $this->get_option('paggi_token');//paggi_token
        $this->sandbox = $this->get_option('paggi_sandbox', 'no');
        $this->debug = $this->get_option('paggi_debug');
        $this->max_installment = $this->get_option('max_installment');
        $this->smallest_installment = $this->get_option('smallest_installment');
        $this->interest_rate = $this->get_option('interest_rate', '0');
        $this->free_installments = $this->get_option('free_installments', '1');

        // Active logs.
        if ('yes' == $this->debug) {
            if (function_exists('wc_get_logger')) {
                $this->log = wc_get_logger();
            } else {
                $this->log = new WC_Logger();
            }
        }

        // Set the API.
        $this->api = new WC_Paggi_API($this);

        // Actions
        add_action('init', array($this, 'init'));

        // Create account default in checkout
        add_filter('woocommerce_create_account_default_checked', function( $isChecked) {
            return true;
        });
    }

    /**
     * Initialize Gateway Settings Form Fields
     *
     * @since 0.1.0
     */
    public function init_form_fields() {

        $this->form_fields = apply_filters('wc_paggi_form_fields', array(
            'partner_id' => array(
                'title' => __('ID PAGGI', 'woocommerce-paggi'),
                'type' => 'text',
                'description' => ('Id de identificação PAGGI'),
            ),
            'general' => array(
                'title' => __('General', 'woocommerce-paggi'),
                'type' => 'title',
                'description' => '',
            ),
            'enabled' => array(
                'title' => __('Habilitar / Desabilitar', 'woocommerce-paggi'),
                'type' => 'checkbox',
                'label' => __('Pagamento Paggi', 'woocommerce-paggi'),
                'default' => 'yes',
            ),
            'paggi_title' => array(
                'title' => __('Título', 'woocommerce-paggi'),
                'type' => 'text',
                'description' => __('This controls the title for the payment method the customer sees during checkout.', 'woocommerce-paggi'),
                'default' => __('Pagamento Paggi', 'woocommerce-paggi'),
                'desc_tip' => true,
            ),
            'paggi_description' => array(
                'title' => __('Descrição', 'woocommerce-paggi'),
                'type' => 'textarea',
                'description' => __('Payment method description that the customer will see on your checkout.', 'woocommerce-paggi'),
                'default' => __('Please remit payment to Store Name upon pickup or delivery.', 'woocommerce-paggi'),
                'desc_tip' => true,
            ),
            'paggi_instructions' => array(
                'title' => __('Instruções', 'woocommerce-paggi'),
                'type' => 'textarea',
                'description' => __('Instructions that will be added to the thank you page and emails.', 'woocommerce-paggi'),
                'default' => '',
                'desc_tip' => true,
            ),
            'paggi_token' => array(
                'title' => __('Token', 'woocommerce-paggi'),
                'type' => 'text',
                'description' => __('Insira o token da Paggi para processar pagamentos.', 'woocommerce-paggi'),
                'default' => '',
            ),
            'installments' => array(
                'title' => __('Parcelamentos', 'woocommerce-paggi'),
                'type' => 'title',
                'description' => '',
            ),
            'max_installment' => array(
                'title' => __('Número de parcelas', 'woocommerce-paggi'),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => '12',
                'description' => __('Número máximo de parcelas possível com pagamento por cartão de crédito.', 'woocommerce-paggi'),
                'desc_tip' => true,
                'options' => array(
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                    '7' => '7',
                    '8' => '8',
                    '9' => '9',
                    '10' => '10',
                    '11' => '11',
                    '12' => '12',
                ),
            ),
            'smallest_installment' => array(
                'title' => __('Valor mínimo de parcela', 'woocommerce-paggi'),
                'type' => 'text',
                'description' => __('Informe qual o valor mínimo aceito para parcela.', 'woocommerce-paggi'),
                'desc_tip' => true,
                'default' => '5',
            ),
            'interest_rate' => array(
                'title' => __('Taxa de juros', 'woocommerce-paggi'),
                'type' => 'text',
                'description' => __('Valor da taxa de juros. Use 0 para parcelamento sem taxa de juros.', 'woocommerce-paggi'),
                'desc_tip' => true,
                'default' => '0',
            ),
            'free_installments' => array(
                'title' => __('Parcelamento sem juros', 'woocommerce-paggi'),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => '1',
                'description' => __('Número de parcelas sem juros.', 'woocommerce-paggi'),
                'desc_tip' => true,
                'options' => array(
                    '0' => _x('None', 'no free installments', 'woocommerce-paggi'),
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                    '7' => '7',
                    '8' => '8',
                    '9' => '9',
                    '10' => '10',
                    '11' => '11',
                    '12' => '12',
                ),
            ),
            'debit_card' => array(
                'title' => __('Cartão de débito', 'woocommerce-paggi'),
                'type' => 'checkbox',
            ),
            'development' => array(
                'title' => __('Desenvolvimento', 'woocommerce-paggi'),
                'type' => 'title',
                'description' => '',
            ),
            'paggi_sandbox' => array(
                'title' => __('Ambiente de Teste Paggi', 'woocommerce-paggi'),
                'type' => 'checkbox',
                'label' => __('Habilitar ambiente de teste Paggi', 'woocommerce-paggi'),
                'desc_tip' => true,
                'default' => 'no',
                'description' => __('Paggi Sandbox can be used to test the payments.', 'woocommerce-paggi'),
            ),
            'paggi_debug' => array(
                'title' => __('Debug Log', 'woocommerce-paggi'),
                'type' => 'checkbox',
                'label' => __('Habilitar logs', 'woocommerce-paggi'),
                'default' => 'no',
                'description' => sprintf(__('Log Paggi events, such as API requests, inside %s', 'woocommerce-paggi'), $this->get_log_view()),
            )
        ));
    }

    /**
     * Payment fields.
     *
     * @since 0.1.0
     */
    public function payment_fields() {
        if ($description = $this->get_description()) {
            echo wp_kses_post(wpautop(wptexturize($description)));
        }
        $cart_total = number_format((float) $this->get_order_total(), 2, '.', '');
        if (file_exists(PLUGIN_DIR_PATH . 'assets/js/jquery.mask/jquery.mask.min.js')) { // jquery-mask
            wp_register_script('jquery-mask', PLUGIN_DIR_URL . 'assets/js/jquery.mask/jquery.mask.min.js');
            wp_enqueue_script('jquery-mask');
        }
        if (file_exists(PLUGIN_DIR_PATH . 'assets/js/card-master/card.js')) { // card-master
            wp_register_script('card-master', PLUGIN_DIR_URL . 'assets/js/card-master/card.js');
            wp_enqueue_script('card-master');
        }
        if (file_exists(PLUGIN_DIR_PATH . 'assets/js/jquery.creditCardValidator/jquery.creditCardValidator.js')) {
            wp_register_script('jquery.creditCardValidator', PLUGIN_DIR_URL . 'assets/js/jquery.creditCardValidator/jquery.creditCardValidator.js');
            wp_enqueue_script('jquery.creditCardValidator');
            wp_localize_script('jquery.creditCardValidator ', 'wpurl ', array('siteurl' => get_option('siteurl')));
        }
        if (file_exists(PLUGIN_DIR_PATH . 'assets/css/paggi.css')) {
            wp_register_style('paggicss', PLUGIN_DIR_URL . 'assets/css/paggi.css');
            wp_enqueue_style('paggicss');
        }
        $current_customer = get_user_meta(wp_get_current_user()->ID);
        if (!isset($current_customer['billing_persontype']) || $current_customer['billing_persontype'][0] === NULL) {
            $cards = NULL;
            $installments = $this->get_installments_view($cart_total);
    
            wc_get_template('credit-card/payment-form.php', array(
                'cart_total' => $cart_total,
                'cards' => $cards,
                'columns' => array(
                    '1' => __('Last Digits', 'woocommerce-paggi'),
                    '2' => __('Brand', 'woocommerce-paggi'),
                    '3' => __('Action', 'woocommerce-paggi')
                ),
                'installments' => $installments,
                    ), 'woocommerce/paggi/', WC_Paggi::get_templates_path());
        } else {
            $person_type = $current_customer['billing_persontype'][0];
            $cards = NULL;

            if ($person_type === '1'){
                $document = $current_customer['billing_cpf'];
            } else {
                $document = $current_customer['billing_cnpj'];
            }

            $installments = $this->get_installments_view($cart_total);
            $json_response = $this->api->get_card($document);
            $response = json_decode(json_encode($json_response), true);

            if (isset($response['code']) && $response['code'] === 404){
                $cards = NULL;
                $columns = array();
            } else {
                foreach ($response as $key => $value) {
                    $cards[$key]['id'] = $value['id'];
                    $cards[$key]['last4'] = substr($value['masked_number'], -4);
                    $cards[$key]['brand'] = $value['brand'];
                }
    
                $columns = array(
                    '1' => __('Last Digits', 'woocommerce-paggi'),
                    '2' => __('Brand', 'woocommerce-paggi'),
                    '3' => __('Action', 'woocommerce-paggi'));
                }        
            
    
            wc_get_template('credit-card/payment-form.php', array(
                'cart_total' => $cart_total,
                'cards' => $cards,
                'columns' => array(
                    '1' => __('Last Digits', 'woocommerce-paggi'),
                    '2' => __('Brand', 'woocommerce-paggi'),
                    '3' => __('Action', 'woocommerce-paggi')
                ),
                'installments' => $installments,
                    ), 'woocommerce/paggi/', WC_Paggi::get_templates_path());
        }    
    }

    /**
     *
     *
     * @since 0.1.0
     * @param type $cart_total
     * @param type $installment
     * @return type
     */
    public function get_installments($cart_total, $installment) {
        if (isset($installment)) {
            if ($installment <= $this->free_installments) {
                $return = number_format((float) floatval($cart_total), 2, '.', '');
            } else {
                $return = number_format((float) ((floatval($cart_total) + (floatval($cart_total) * ($this->interest_rate * $installment) / 100))), 2, '.', '');
            }
        }
        if ('yes' === $this->debug) {
            $this->log->add($this->id, "get_installments($cart_total, $installment) = $return");
        }
        return $return;
    }

    public function get_installments_view($cart_total)
    {
        $return = array('1' => $cart_total);
        $installments = $cart_total / $this->smallest_installment;
        if ($installments > $this->max_installment) {
            $installments = $this->max_installment;
        }
        for ($i = 2; $i <= $installments; $i++) {
            if ($i <= $this->free_installments) {
                $return[$i] = number_format((float) $cart_total / $i, 2, '.', '');//$i
                } else {
                $return[$i] = number_format((float) (($cart_total + ($cart_total * ($this->interest_rate * $i) / 100)) / $i), 2, '.', '');
            }
        }
        return $return;
    }

    /**
     * Process the payment and return the result
     *
     * @since 0.1.0
     * @param int $order_id
     * @return array
     */
    public function process_payment($order_id) {
        $error = '';
        $order = new WC_Order($order_id);

        if ('' === $error) {
            if ($order->get_meta('_billing_persontype') == 1) {
                $document = $order->get_meta('_billing_cpf');
                $name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
            } else {
                $document = $order->get_meta('_billing_cnpj');
                $name = $order->get_billing_company();
            }
            $external_identifier = $order->get_id();
            $ip = $order->get_customer_ip_address();
            $document = strtr($document, array('-' => '','.' => '', '/' => ''));
            $email = $order->get_billing_email();
            $phone = $order->get_billing_phone();
            $street = $order->get_billing_address_1();
            $neighborhood = $order->get_meta('_billing_neighborhood');
            $city = $order->get_billing_city();
            $state = $order->get_billing_state();
            $zipcode = $order->get_billing_postcode();
            $number = $order->get_meta('_billing_number');
            $complement = $order->get_billing_address_2();
            // payment process
            $amount = $_POST['tot'];
            $installments = $_POST['installments'];


        if ('' === $error) {
            // card register
            if (!isset($_POST['card_id']) && isset($_POST['cc_number'])) {
                $result = $this->api->set_card($_POST['cc_name'], $document, $_POST['cc_number'], $_POST['cc_expiry'], $_POST['cc_cvc']);
                if (isset($result->id)) {
                    $card_id = $result->id;
                } else {
                    if (isset($result['error'])) {
                        $error .= '<b>' . $result['error'][0]['param'] . "</b> " . $result['error'][0]['message'] . '<br/>';
                    } else {
                        $error .= $result['errors'][0]['message'] . '<br/>';
                    }
                }
            } else {
                $card_id = $_POST['card_id'];
            }
        }
           
        $result = $this->api->process_payment($card_id, $installments, $amount, $name, $email, $document, $phone, $street, $neighborhood, $zipcode, $city, $state, $number, $complement, $ip, $external_identifier);

        if (isset($result->id)) {
            $transaction_id = $result->id;

            $order = wc_get_order($order_id);

            $this->process_order_status($order, $result->status, $transaction_id);

            // Reduce stock levels
            $order->reduce_order_stock();

            // Remove cart
            WC()->cart->empty_cart();
        } else {
            if (isset($result['error'])) {
                $error .= '<b>' . $result['error'][0]['param'] . "</b> " . $result['error'][0]['message'] . '<br/>';
            } else {
                $error .= $result['errors'][0]['message'] . '<br/>';
                }
            }
        }

        if ('' === $error) {
            // Return thankyou redirect
            return array(
                'result' => 'success',
                'redirect' => $this->get_return_url($order));
        } else {
            wc_add_notice(__('Payment error: ', 'woocommerce-paggi') . ucwords($error), 'error');
            return;
        }
    }

    /**
     * Process the order status.
     *
     * @since 0.1.0
     * @param WC_Order $order
     * @param string $status
     * @param string $transaction_id 
     */
    public function process_order_status($order, $status, $transaction_id) {
        update_post_meta($order->id, 'paggi_transaction_id', $transaction_id);
        switch ($status) {
            case 'captured' :
                if (!in_array($order->get_status(), array('processing', 'completed'), true)) {
                    $order->update_status('completed', sprintf(__('Paggi: The transaction was authorized(id = %s.)', 'woocommerce-paggi'), $transaction_id));
                }
                // Changing the order for processing and reduces the stock.
                $order->payment_complete();
                break;
            case 'capture_pending' :
                $order->update_status('on-hold', sprintf(__('Paggi: The transaction is being processed(id = %s.)', 'woocommerce-paggi'), $transaction_id));
                break;
            case 'capture_declined' :
                $order->update_status('failed', sprintf(__('Paggi: The transaction was rejected by the card company or by fraud(id = %s.)', 'woocommerce-paggi'), $transaction_id));

                $transaction_id = get_post_meta($order->id, '_wc_paggi_transaction_id', true);
                $this->send_email(
                        sprintf(esc_html__('The transaction for order %s was rejected by the card company or by fraud', 'woocommerce-paggi'), $order->get_order_number()), esc_html__('Transaction failed', 'woocommerce-paggi'), sprintf(esc_html__('Order %1$s has been marked as failed, because the transaction was rejected by the card company or by fraud. ID %2$s.', 'woocommerce-paggi'), $order->get_order_number(), $transaction_id)
                );
                break;
            case 'cancelled' :
            case 'chargeback':
                $order->update_status('refunded', sprintf(__('Paggi: The transaction was refunded/canceled (id = %s.)', 'woocommerce-paggi'), $transaction_id));

                $transaction_id = get_post_meta($order->id, 'paggi_transaction_id', true);

                $this->send_email(
                        sprintf(esc_html__('The transaction for order %s refunded', 'woocommerce-paggi'), $order->get_order_number()), esc_html__('Transaction refunded', 'woocommerce-paggi'), sprintf(esc_html__('Order %1$s has been marked as refunded by Paggi. ID %2$s', 'woocommerce-paggi'), $order->get_order_number(), $transaction_id)
                );
                break;
            default :
                break;
        }
    }

    public function process_refund($order_id, $amount = null, $reason = '') {      
        $order = wc_get_order($order_id);
        $transaction_id = get_post_meta($order->id, 'paggi_transaction_id', true);
        $result =  $this->api->cancel_regular_payment($transaction_id, $order_id);
        if (isset($result->status) && $result->status == 'cancelled') {
            $order->update_status('refunded', sprintf(__('Paggi: The transaction was refunded/canceled (id = %s.)', 'woocommerce-paggi'), $transaction_id));            
            $order->add_order_note(sprintf(__('Paggi: Transaction was canceled (id = %s.)', 'woocommerce-paggi'), $transaction_id));
            update_post_meta($order_id, 'paggi_canceled', 'TRUE');
            //$this->process_refund($order_id);
            wp_send_json(array('code' => '200', 'message' => __('Payment was canceled successfuly.', 'woocommerce-paggi')));
        } else {
            wp_send_json(array('code' => '500', 'message' => __('An error has occurred. Try Again', 'woocommerce-paggi')));
        }
    }

    /**
     * Get the smallest installment amount.
     *
     * @since 0.1.0
     * @return int
     */
    public function get_smallest_installment() {
        return wc_format_decimal($this->smallest_installment) * 100;
    }

    /**
     * Send email notification.
     *
     * @since 0.1.0
     * @param string $subject Email subject.
     * @param string $title   Email title.
     * @param string $message Email message.
     */
    protected function send_email($subject, $title, $message) {
        $mailer = WC()->mailer();
        $mailer->send(get_option('admin_email'), $subject, $mailer->wrap_message($title, $message));
    }

    /**
     * Get Token
     *
     * @since 0.1.0
     * @return string
     */
    public function get_token() {
        return 'yes' === $this->sandbox ? 'eyJhbGciOiJIUzUxMiIsInR5cCI6IkpXVCJ9.eyJhdWQiOiJQQUdHSSIsImV4cCI6NjIwNjAzMzA3ODYsImlhdCI6MTU4MDMzMDc4NiwiaXNzIjoiUEFHR0kiLCJqdGkiOiI0NDk1NTU2OS00YTY3LTRmYmItODdlZC04NzUwMmIxNjQ4MDAiLCJuYmYiOjE1ODAzMzA3ODUsInBlcm1pc3Npb25zIjpbeyJwYXJ0bmVyX2lkIjoiMjdhODJjZTEtMmJlMi00NjRhLWJmM2YtOGQyZTE5NjVkMzQwIiwicGVybWlzc2lvbnMiOlsic3lzdGVtX3VzZXIiXX1dLCJzdWIiOiI3MWEzZmM1Ny0yOTBiLTQzNDYtYTgwOC0yOTVkZjM3NmE2NmEiLCJ0eXAiOiJhY2Nlc3MifQ.ZTVGFSsfQaFWnIgXgKmdtY1YUPOInyYBCYf3Ft218VnzFpnYGdnt5MSINq7RtqOlLYy_MIBuIkfZcOSEE4jwhA' : $this->token;
    }

    /**
     * Get log.
     *
     * @since 0.1.0
     * @return string
     */
    protected function get_log_view() {
        if (defined('WC_VERSION') && version_compare(WC_VERSION, '2.2', '>=')) {
            return '<a href="' . esc_url(admin_url('admin.php?page=wc-status&tab=logs&log_file=' . esc_attr($this->id) . '-' . sanitize_file_name(wp_hash($this->id)) . '.log')) . '">' . __('System Status &gt; Logs', 'woocommerce-paggi') . '</a>';
        }
        return '<code>woocommerce/logs/' . esc_attr($this->id) . '-' . sanitize_file_name(wp_hash($this->id)) . '.txt</code>';
    }

    /**
     * Save Account form
     * Synchronize woocommerce registration with Paggi
     *
     * @since 0.1.0
     * @param string $user_id
     */
    public function save_account_form($user_id) {
        $user_info = get_userdata($user_id);
        $name = $user_info->first_name . ' ' . $user_info->last_name;
        $email = $user_info->user_email;
        if (strlen($user_info->billing_cpf) == 14) {
            $document = $user_info->billing_cpf;
        } else {
            $document = $user_info->billing_cnpj;
        }
        $phone = $user_info->billing_phone;
        $street = $user_info->billing_address_1 . ' ' . $user_info->billing_number . ' ' . $user_info->billing_address_2;
        $district = $user_info->billing_neighborhood;
        $city = $user_info->billing_city;
        $state = $user_info->billing_state;
        $zipcode = $user_info->billing_postcode;
        $paggi_customer_id = $user_info->paggi_id;
        // setting up user display name
        wp_update_user(array('ID' => $user_id, 'display_name' => $name));
        
    }

    /**
     * Init
     *
     * @since 0.1.0
     */
    public function init() {
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        // update customers informations
        add_action('woocommerce_save_account_details', array($this, 'save_account_form'));
        add_action('woocommerce_customer_save_address', array($this, 'save_account_form'));

        // add menu in myaccount
        add_filter('woocommerce_account_menu_items'
                , array($this, 'paggicards_account_menu_items'), 10, 1);

        add_rewrite_endpoint('paggicards', EP_PAGES);
        add_filter(
                'the_title', array($this, 'endpoint_title'));
        add_action('woocommerce_account_paggicards_endpoint', array($this, 'paggicards_endpoint_content'));

        // add cancel transaction in order page
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
    }

    /**
     * View Card title
     *
     * @since 0.1.0
     * @global type $wp_query
     * @param String $title
     * @return String
     */
    public function endpoint_title($title) {
        global $wp_query;
        $is_endpoint = isset($wp_query->query_vars[
                'paggicards']);
        if ($is_endpoint && !is_admin() && is_main_query() && in_the_loop() && is_account_page()) {
        // New page title.
            $title = __('Paggi Cards', 'woocommerce-paggi');
            remove_filter('the_title', array($this, 'endpoint_title'));
        }
        return $title;
    }

    /**
     * Card menu items
     *
     * @since 0.1.0
     * @param arr $items
     * @return arr
     */
    function paggicards_account_menu_items($items) {

        $items['paggicards'] = __('Cards', 'woocommerce-paggi');

        return $items;
    }

    /**
     * View card
     *
     * @since 0.1.0
     */
    function paggicards_endpoint_content() {
        if (file_exists(PLUGIN_DIR_PATH . 'assets/js/jquery.mask/jquery.mask.min.js')) { // jquery-mask
            wp_register_script('jquery-mask', PLUGIN_DIR_URL . 'assets/js/jquery.mask/jquery.mask.min.js');
            wp_enqueue_script('jquery-mask');
        }
        if (file_exists(PLUGIN_DIR_PATH . 'assets/js/card-master/card.js')) { // card-master
            wp_register_script('card-master', PLUGIN_DIR_URL . 'assets/js/card-master/card.js');
            wp_enqueue_script('card-master');
        }
        if (file_exists(PLUGIN_DIR_PATH . 'assets/js/jquery.creditCardValidator/jquery.creditCardValidator.js')) {
            wp_register_script('jquery.creditCardValidator', PLUGIN_DIR_URL . 'assets/js/jquery.creditCardValidator/jquery.creditCardValidator.js');
            wp_enqueue_script('jquery.creditCardValidator');
            wp_localize_script('jquery.creditCardValidator', 'wpurl', array('siteurl' => get_option('siteurl')));
        }
        if (file_exists(PLUGIN_DIR_PATH . 'assets/css/paggi.css')) {
            wp_register_style('paggicss', PLUGIN_DIR_URL . 'assets/css/paggi.css');
            wp_enqueue_style('paggicss');
        }
        if (isset($_POST['cc_number'])) {
            $result = $this->api->set_card($_POST['cc_name'], $_POST['cc_document'], $_POST['cc_number'], $_POST['cc_expiry'], $_POST['cc_cvc']);
            if (isset($return['errors'])) {
                wc_add_notice(__('Error: ', 'woocommerce-paggi') . $result['Status']['Description'] . ' - ' . $result['errors'][0]['message'], 'error');
            }
        }
        
        $current_customer = get_user_meta(wp_get_current_user()->ID);


        if (!isset($current_customer['billing_persontype']) || $current_customer['billing_persontype'][0] === NULL) {
            $cards = NULL;
            $columns = array();
        } else {
            $person_type = $current_customer['billing_persontype'][0];
            $cards = NULL;

            if (isset($_POST['cc_document']) && $_POST['cc_document'] !== NULL) {
                $document = $_POST['cc_document'];
            } if ($person_type === '1') {
                $document = $current_customer['billing_cpf'];
            } else {
                $document = $current_customer['billing_cnpj'];
            }

            $json_response = $this->api->get_card($document);
            $response = json_decode(json_encode($json_response), true);

            if (isset($response['code']) && $response['code'] === 404){
                $cards = NULL;
                $columns = array();
            } else {
                foreach ($response as $key => $value) {
                    $cards[$key]['last4'] = substr($value['masked_number'], -4);                    
                    $cards[$key]['brand'] = $value['brand'];
                }
    
                $columns = array(
                    '1' => __('Last digits', 'woocommerce-paggi'),
                    '2' => __('brand', 'woocommerce-paggi'));
                }
            }    

        include dirname(__FILE__) . '/views/html-cards.php';
    }

    /**
     * Cancel meta box
     *
     * @since 0.1.0
     */
    function add_meta_boxes() {
        add_meta_box(
                'woocommerce-meta-paggicards', __('Paggi Cards', 'woocommerce-paggi'), array($this, 'meta_paggicards'), 'shop_order', 'side', 'default');
    }

    /**
     * View cancel metabox
     *
     * @since 0.1.0
     * @param WC_Order $order_id
     */
    function meta_paggicards($order_id) {
        if (file_exists(PLUGIN_DIR_PATH . 'assets/js/jquery.creditCardValidator/jquery.creditCardValidator.js')) {
            wp_register_script('jquery.creditCardValidator', PLUGIN_DIR_URL . 'assets/js/jquery.creditCardValidator/jquery.creditCardValidator.js');
            wp_enqueue_script('jquery.creditCardValidator');
            wp_localize_script('jquery.creditCardValidator', 'wpurl', array('siteurl' => get_option('siteurl')));
        }
        if (file_exists(PLUGIN_DIR_PATH . 'assets/css/paggi.css')) {
            wp_register_style('paggicss', PLUGIN_DIR_URL . 'assets/css/paggi.css');
            wp_enqueue_style('paggicss');
        }
        $order = new WC_Order($order_id->ID);
        if (!$order->get_meta('paggi_canceled'))
            $id = $order->get_meta('paggi_transaction_id');
    }

}
