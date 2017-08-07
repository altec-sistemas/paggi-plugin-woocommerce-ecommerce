<?php

/**
 * WooCommerce Gateway Paggi class
 * 
 * @version 0.0.1 
 */
class WC_Paggi_Gateway extends WC_Payment_Gateway {

    /**
     * Constructor for the gateway.
     * 
     * @since 0.0.1
     */
    public function __construct() {

        $this->id = 'paggi_gateway';
        //$this->icon = apply_filters('woocommerce_paggi_icon', plugins_url('assets/images/paggi.png', plugin_dir_path(__FILE__)));
        $this->has_fields = false;
        $this->method_title = __('Paggi', 'woocommerce-paggi');
        $this->method_description = __('Accept payments by credit card using the Paggi.', 'woocommerce-paggi');//
        $this->order_button_text = __('Proceed to payment', 'woocommerce-paggi');

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables
        $this->title = $this->get_option('paggi_title', 'Paggi');
        $this->description = $this->get_option('paggi_description');
        $this->instructions = $this->get_option('paggi_instructions', $this->description);
        $this->token = $this->get_option('paggi_token');
        $this->sandbox = $this->get_option('paggi_sandbox', 'no');
        $this->debug = $this->get_option('paggi_debug');
        $this->risk = $this->get_option('paggi_risk', 'yes');
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
     * @since 0.0.1
     */
    public function init_form_fields() {

        $this->form_fields = apply_filters('wc_paggi_form_fields', array(
            'general' => array(
                'title' => __('General', 'woocommerce-paggi'),
                'type' => 'title',
                'description' => '',
            ),
            'enabled' => array(
                'title' => __('Enable/Disable', 'woocommerce-paggi'),
                'type' => 'checkbox',
                'label' => __('Enable Paggi Payment', 'woocommerce-paggi'),
                'default' => 'yes'
            ),
            'paggi_title' => array(
                'title' => __('Title', 'woocommerce-paggi'),
                'type' => 'text',
                'description' => __('This controls the title for the payment method the customer sees during checkout.', 'woocommerce-paggi'),
                'default' => __('Paggi Payment', 'woocommerce-paggi'),
                'desc_tip' => true,
            ),
            'paggi_description' => array(
                'title' => __('Description', 'woocommerce-paggi'),
                'type' => 'textarea',
                'description' => __('Payment method description that the customer will see on your checkout.', 'woocommerce-paggi'),
                'default' => __('Please remit payment to Store Name upon pickup or delivery.', 'woocommerce-paggi'),
                'desc_tip' => true,
            ),
            'paggi_instructions' => array(
                'title' => __('Instructions', 'woocommerce-paggi'),
                'type' => 'textarea',
                'description' => __('Instructions that will be added to the thank you page and emails.', 'woocommerce-paggi'),
                'default' => '',
                'desc_tip' => true,
            ),
            'paggi_token' => array(
                'title' => __('Token', 'woocommerce-paggi'),
                'type' => 'text',
                'description' => __('Please enter your Paggi token. This is needed to process the payment.', 'woocommerce-paggi'),
                'default' => '',
            ),
            'paggi_risk' => array(
                'title' => __('Risk analysis', 'woocommerce-paggi'),
                'type' => 'checkbox',
                'label' => __('Enable risk analysis', 'woocommerce-paggi'),
                'default' => 'yes',
                'description' => __('Some users may cancel their transactions with their card holder. This will make us to cancel the charge, and you will probably loose money! So it is very important that you send us as much data possible so we can identify users that have suspect profiles. We all would be happier with lower chargebacks. If you have a high incidence of chargebacks, this may affect your tax, making it to go higher, so don\'t take this part unnoticed.', 'woocommerce-paggi'),
            ),
            'installments' => array(
                'title' => __('Installments', 'woocommerce-paggi'),
                'type' => 'title',
                'description' => '',
            ),
            'max_installment' => array(
                'title' => __('Number of Installment', 'woocommerce-paggi'),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => '12',
                'description' => __('Maximum number of installments possible with payments by credit card.', 'woocommerce-paggi'),
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
                'title' => __('Smallest Installment', 'woocommerce-paggi'),
                'type' => 'text',
                'description' => __('Please enter with the value of smallest installment.', 'woocommerce-paggi'),
                'desc_tip' => true,
                'default' => '5',
            ),
            'interest_rate' => array(
                'title' => __('Interest rate', 'woocommerce-paggi'),
                'type' => 'text',
                'description' => __('Please enter with the interest rate amount. Note: use 0 to not charge interest.', 'woocommerce-paggi'),
                'desc_tip' => true,
                'default' => '0',
            ),
            'free_installments' => array(
                'title' => __('Free Installments', 'woocommerce-paggi'),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => '1',
                'description' => __('Number of installments with interest free.', 'woocommerce-paggi'),
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
            'development' => array(
                'title' => __('Development', 'woocommerce-paggi'),
                'type' => 'title',
                'description' => '',
            ),
            'paggi_sandbox' => array(
                'title' => __('Sandbox', 'woocommerce-paggi'),
                'type' => 'checkbox',
                'label' => __('Enable Paggi Sandbox', 'woocommerce-paggi'),
                'desc_tip' => true,
                'default' => 'no',
                'description' => __('Paggi Sandbox can be used to test the payments.', 'woocommerce-paggi'),
            ),
            'paggi_debug' => array(
                'title' => __('Debug Log', 'woocommerce-paggi'),
                'type' => 'checkbox',
                'label' => __('Enable logging', 'woocommerce-paggi'),
                'default' => 'no',
                'description' => sprintf(__('Log Paggi events, such as API requests, inside %s', 'woocommerce-paggi'), $this->get_log_view()),
            )
        ));
    }

    /**
     * Payment fields.
     * 
     * @since 0.0.1
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
        $paggi_customer_id = wp_get_current_user()->paggi_id;
        $cards = NULL;
        if ($paggi_customer_id) {
            $customer = $this->api->list_customer($paggi_customer_id);
            foreach ($customer['cards'] as $key => $value) {
                $cards[$key]['id'] = $value['id'];
                $cards[$key]['name'] = $value['name'];
                $cards[$key]['expires'] = $value['month'] . "/" . $value['year'];
                $cards[$key]['brand'] = $value['brand'];
                $cards[$key]['last4'] = $value['last4'];
            }
        }

        $installments = $this->get_installments($cart_total);
        wc_get_template('credit-card/payment-form.php', array(
            'cart_total' => $cart_total,
            'cards' => $cards,
            'columns' => array(
                '1' => __('Name', 'woocommerce-paggi'),
                '2' => __('Expires', 'woocommerce-paggi'),
                '3' => __('Brand', 'woocommerce-paggi'),
                '4' => __('Last four', 'woocommerce-paggi'),
                '5' => __('Action', 'woocommerce-paggi')
            ),
            'installments' => $installments,
                ), 'woocommerce/paggi/', WC_Paggi::get_templates_path());
    }

    /**
     * 
     * 
     * @since 0.0.1
     * @param type $cart_total
     * @param type $installment
     * @return type
     */
    public function get_installments($cart_total, $installment) {
        if (isset($installment)) {
            if ($installment <= $this->free_installments) {
                $return = number_format((float) floatval($cart_total) / $installment, 2, '.', '');
            } else {
                $return = number_format((float) ((floatval($cart_total) + (floatval($cart_total) * ($this->interest_rate * $installment) / 100)) / $installment), 2, '.', '');
            }
        } else {
            $return = array(
                '1' => $cart_total
            );
            $installments = $cart_total / $this->smallest_installment;
            if ($installments > $this->max_installment) {
                $installments = $this->max_installment;
            }
            for ($i = 2; $i <= $installments; $i++) {
                if ($i <= $this->free_installments) {
                    $return[$i] = number_format((float) $cart_total / $i, 2, '.', '');
                } else {
                    $return[$i] = number_format((float) (($cart_total + ($cart_total * ($this->interest_rate * $i) / 100)) / $i), 2, '.', '');
                }
            }
        }
        if ('yes' === $this->debug) {
            $this->log->add($this->id, "get_installments($cart_total, $installment) = $return");
        }
        return $return;
    }

    /**
     * Process the payment and return the result
     *
     * @since 0.0.1
     * @param int $order_id
     * @return array
     */
    public function process_payment($order_id) {

        if (isset($_POST['payment_method']) && "paggi_gateway" === $_POST['payment_method']) {
            $error = '';
            $paggi_customer_id = wp_get_current_user()->paggi_id;
            if (!$paggi_customer_id) {
                $order = new WC_Order($order_id);
                $paggi_customer_id = $order->get_meta('paggi_customer_id');
            }
            // not registered - guest
            if (!$paggi_customer_id) {
                $name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
                $email = $order->get_billing_email();
                if ($order->get_meta('_billing_persontype') == '1') {
                    $document = $order->get_meta('_billing_cpf');
                } else {
                    $document = $order->get_meta('_billing_cnpj');
                }
                $phone = $order->get_billing_phone();
                $street = $order->get_billing_address_1() . ' ' . $order->get_billing_address_2() . ' ' . $order->get_meta('_billing_number');
                $district = $order->get_meta('_billing_neighborhood');
                $city = $order->get_billing_city();
                $state = $order->get_billing_state();
                $zip = $order->get_billing_postcode();
                $result = $this->api->set_customer($name, $email, $document, $phone, $street, $district, $city, $state, $zip);
                if (isset($result['id'])) {
                    $paggi_customer_id = $result['id'];                    
                    update_post_meta($order_id, 'paggi_customer_id', $paggi_customer_id);
                } else {
                    if (isset($result['error'])) {
                        $error .= '<b>' . $result['error'][0]['param'] . "</b> " . $result['error'][0]['message'] . '<br/>';
                    } else {
                        $error .= $result['errors'][0]['message'] . '<br/>';
                    }
                }
            }
            if ('' === $error) {
                // card register
                if (isset($paggi_customer_id) && !isset($_POST['card_id']) && isset($_POST['cc_number'])) {
                    $result = $this->api->set_card($paggi_customer_id, $_POST['cc_name'], $_POST['cc_number'], $_POST['cc_expiry'], $_POST['cc_cvc']);
                    if (isset($result['id'])) {
                        $card_id = $result['id'];
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
            if ('' === $error) {
                // payment process
                $value = $_POST['tot'];
                if ($_POST['installments'] != '1') {
                    $value = $this->get_installments($value, $_POST['installments']);
                }
                $result = $this->api->process_regular_payment($value, $paggi_customer_id, $card_id, $_POST['installments']);
                if (isset($result['id'])) {
                    $transaction_id = $result['id'];

                    $order = wc_get_order($order_id);

                    $this->process_order_status($order, $result['status'], $transaction_id);

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
                    'redirect' => $this->get_return_url($order)
                );
            } else {
                wc_add_notice(__('Payment error: ', 'woocommerce-paggi') . ucwords($error), 'error');
                return;
            }
        }
    }

    /**
     * Process the order status.
     *
     * @since 0.0.1
     * @param WC_Order $order
     * @param string $status
     * @param string $transaction_id
     */
    public function process_order_status($order, $status, $transaction_id) {
        if ('yes' === $this->debug) {
            $this->log->add($this->id, 'Payment status for order ' . $order->get_order_number() . ' is now: ' . $status);
        }
        update_post_meta($order->id, 'paggi_transaction_id', $transaction_id);
        switch ($status) {
            case 'approved' :
                if (!in_array($order->get_status(), array('processing', 'completed'), true)) {
                    $order->update_status('processing', sprintf(__('Paggi: The transaction was authorized(id = %s.)', 'woocommerce-paggi'), $transaction_id));
                }
                // Changing the order for processing and reduces the stock.
                $order->payment_complete();
                break;
            case 'cleared' :
                $order->update_status('on-hold', sprintf(__('Paggi: The transaction is being processed(id = %s.)', 'woocommerce-paggi'), $transaction_id));
                break;
            case 'registered' :
                $order->update_status('on-hold', sprintf(__('Paggi: The banking ticket was issued but not paid yet(id = %s.)', 'woocommerce-paggi'), $transaction_id));
                break;
            case 'not_cleared' :
            case 'declined' :
                $order->update_status('failed', sprintf(__('Paggi: The transaction was rejected by the card company or by fraud(id = %s.)', 'woocommerce-paggi'), $transaction_id));


                $transaction_id = get_post_meta($order->id, '_wc_paggi_transaction_id', true);
                $this->send_email(
                        sprintf(esc_html__('The transaction for order %s was rejected by the card company or by fraud', 'woocommerce-paggi'), $order->get_order_number()), esc_html__('Transaction failed', 'woocommerce-paggi'), sprintf(esc_html__('Order %1$s has been marked as failed, because the transaction was rejected by the card company or by fraud. ID %2$s.', 'woocommerce-paggi'), $order->get_order_number(), $transaction_id)
                );

                break;
            case 'cancelled' :
            case 'chargeback' :
                $order->update_status('refunded', sprintf(__('Paggi: The transaction was refunded/canceled (id = %s.)', 'woocommerce-paggi'), $transaction_id));

                $transaction_id = get_post_meta($order->id, '_wc_paggi_transaction_id', true);

                $this->send_email(
                        sprintf(esc_html__('The transaction for order %s refunded', 'woocommerce-paggi'), $order->get_order_number()), esc_html__('Transaction refunded', 'woocommerce-paggi'), sprintf(esc_html__('Order %1$s has been marked as refunded by Paggi. ID %2$s', 'woocommerce-paggi'), $order->get_order_number(), $transaction_id)
                );
                break;
            default :
                break;
        }
    }

    /**
     * Get the smallest installment amount.
     *
     * @since 0.0.1
     * @return int
     */
    public function get_smallest_installment() {
        return wc_format_decimal($this->smallest_installment) * 100;
    }

    /**
     * Send email notification.
     *
     * @since 0.0.1
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
     * @since 0.0.1
     * @return string
     */
    public function get_token() {
        return 'yes' === $this->sandbox ? 'B31DCE74-E768-43ED-86DA-85501612548F' : $this->token;
    }

    /**
     * Get log.
     *
     * @since 0.0.1
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
     * @since 0.0.1
     * @param string $user_id
     */
    public function save_account_form($user_id) {
        $user_info = get_userdata($user_id);
        $name = $user_info->first_name . ' ' . $user_info->last_name;
        $email = $user_info->user_email;
        if ($user_info->billing_persontype == '1') {
            $document = $user_info->billing_cpf;
        } else {
            $document = $user_info->billing_cnpj;
        }
        $phone = $user_info->billing_phone;
        $street = $user_info->billing_address_1 . ' ' . $user_info->billing_number . ' ' . $user_info->billing_address_2;
        $district = $user_info->billing_neighborhood;
        $city = $user_info->billing_city;
        $state = $user_info->billing_state;
        $zip = $user_info->billing_postcode;
        $paggi_customer_id = $user_info->paggi_id;
        // setting up user display name
        wp_update_user(array('ID' => $user_id, 'display_name' => $name));
        if (!$paggi_customer_id) {
            $result = $this->api->set_customer($name, $email, $document, $phone, $street, $district, $city, $state, $zip);
        } else {
            $result = $this->api->update_customer($paggi_customer_id, $name, $email, $document, $phone, $street, $district, $city, $state, $zip);
        }
        switch ($result['Status']['Code']) {
            case '200':
                add_user_meta($user_id, 'paggi_id', $result['id'], true);
                return $result['id'];
                break;
            default:
                include dirname(__FILE__) . '/views/html-receipt-page-error.php';
                break;
        }
    }

    /**
     * Init
     * 
     * @since 0.0.1
     */
    public function init() {
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'))

        ;
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
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'))

        ;
    }

    /**
     * View Card title
     * 
     * @since 0.0.1
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
     * @since 0.0.1
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
     * @since 0.0.1
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
        $paggi_customer_id = wp_get_current_user()->paggi_id;
        if (!$paggi_customer_id) {
            $paggi_customer_id = $this->save_account_form(wp_get_current_user()->ID);
        }
        if (isset($_POST['cc_number'])) {
            $return = $this->api->set_card($paggi_customer_id, $_POST['cc_name'], $_POST['cc_number'], $_POST['cc_expiry'], $_POST['cc_cvc']);
            if (isset($return['errors'])) {
                wc_add_notice(__('Error: ', 'woocommerce-paggi') . $result['Status']['Description'] . ' - ' . $result['errors'][0]['message'], 'error');
            }
        }
        if (isset($paggi_customer_id)) {
            $customer = $this->api->list_customer($paggi_customer_id);
            $cards = NULL;
            foreach ($customer['cards'] as $key => $value) {
                $cards[$key]['id'] = $value['id'];
                $cards[$key]['name'] = $value['name'];
                $cards[$key]['expires'] = $value['month'] . "/" . $value['year'];
                $cards[$key]['brand'] = $value['brand'];
                $cards[$key]['last4'] = $value['last4'];
            }
        } else {
            $cards = NULL;
        }
        $columns = array(
            '1' => __('Name', 'woocommerce-paggi'),
            '2' => __('Expires', 'woocommerce-paggi'),
            '3' => __('Brand', 'woocommerce-paggi'),
            '4' => __('Last four', 'woocommerce-paggi'),
            '5' => __('Action', 'woocommerce-paggi')
        );
        include dirname(__FILE__) . '/views/html-cards.php';
    }

    /**
     * Cancel meta box
     * 
     * @since 0.0.1
     */
    function add_meta_boxes() {
        add_meta_box(
                'woocommerce-meta-paggicards', __('Paggi Cards', 'woocommerce-paggi'), array($this, 'meta_paggicards'), 'shop_order', 'side', 'default'
        );
    }

    /**
     * View cancel metabox
     * 
     * @since 0.0.1
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
        include dirname(__FILE__) . '/admin/meta.php';
    }

}
