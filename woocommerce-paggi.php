<?php

/*
  Plugin Name: Paggi for WooCommerce
  Plugin URI: https://github.com/altec-sistemas/paggi-plugin-woocommerce-ecommerce
  Description: Includes Paggi as a payment gateway to WooCommerce
  Version: 1.0.0
  Author: Paggi
  Author URI: http://www.paggi.com
  License: GPLv2
  Text Domain: woocommerce-paggi
  Domain Path: /languages/

 *      Copyright 2017 Paggi IT Team <contato@paggi.com>
 *
 *      This program is free software; you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation; either version 3 of the License, or
 *      (at your option) any later version.
 *
 *      This program is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *
 *      You should have received a copy of the GNU General Public License
 *      along with this program; if not, write to the Free Software
 *      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *      MA 02110-1301, USA.
 *
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
// Verification of prerequisites
function admin_notice__error($message) {
    $class = 'notice notice-error';
    printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
}

// Make sure WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_action('admin_notices', admin_notice__error(__('For the correct operation of the plugin WooCommerce - Paggi you need the plugin WooCommerce.', 'woocommerce-paggi')));
}
// Make sure WooCommerce Extra Checkout Fields for Brazil is active
if (!in_array('woocommerce-extra-checkout-fields-for-brazil/woocommerce-extra-checkout-fields-for-brazil.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_action('admin_notices', admin_notice__error(__('For the correct operation of the plugin WooCommerce - Paggi you need the plugin WooCommerce Extra Checkout Fields for Brazil.', 'woocommerce-paggi')));
}
if (!extension_loaded('curl')) {
    add_action('admin_notices', admin_notice__error(__('For the correct operation of the plugin WooCommerce - Paggi you need enable the extension PHP CURL.', 'woocommerce-paggi')));
}
// end verification of prerequisites

// Make sure is class is not active
if (!class_exists('WC_Paggi') && in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))
        && in_array('woocommerce-extra-checkout-fields-for-brazil/woocommerce-extra-checkout-fields-for-brazil.php', apply_filters('active_plugins', get_option('active_plugins')))
        && extension_loaded('curl')):
    // define constants
    define('PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));
    define('PLUGIN_DIR_URL', plugin_dir_url(__FILE__));

    class WC_Paggi {

        /**
         * Plugin version.
         *
         * @var string
         */
        const VERSION = '0.3.3';

        /**
         * Instance of this class.
         *
         * @var object
         */
        protected static $instance = null;

        /**
         * Initialize the plugin public actions.
         * @since 0.1.0
         */
        private function __construct() {
            // Load plugin text domain.
            add_action('init', array($this, 'load_plugin_textdomain'));
            $this->wc_paggi_gateway_init();
            // Add Paggi to payments
            add_filter('woocommerce_payment_gateways', array($this, 'wc_paggi_add_to_gateways'));
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'wc_paggi_gateway_plugin_links'));
        }

        /**
         * Load the plugin text domain for translation.
         *
         * @since 0.1.0
         */
        public function load_plugin_textdomain() {
            load_plugin_textdomain('woocommerce-paggi', false, dirname(plugin_basename(__FILE__)) . '/languages/');
        }

        /**
         * Add the gateway to WC Available Gateways
         *
         * @since 0.1.0
         * @param array $gateways all available WC gateways
         * @return array $gateways all WC gateways + paggi gateway
         */
        public function wc_paggi_add_to_gateways($gateways) {
            $gateways[] = 'WC_Paggi_Gateway';
            return $gateways;
        }

        /**
         * Return an instance of this class.
         *
         * @since 0.1.0
         * @return object A single instance of this class.
         */
        public static function get_instance() {
            // If the single instance hasn't been set, set it now.
            if (null === self::$instance) {
                self::$instance = new self;
            }

            return self::$instance;
        }

        /**
         * Adds plugin page links
         *
         * @since 0.1.0
         * @param array $links all plugin links
         * @return array $links all plugin links + our custom links (i.e., "Settings")
         */
        public function wc_paggi_gateway_plugin_links($links) {

            $plugin_links = array(
                '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=paggi_gateway') . '">' . __('Configure', 'wc-gateway-paggi') . '</a>'
            );

            return array_merge($plugin_links, $links);
        }

        /**
         * Init Paggi Payment Gateway
         *
         * @since 0.1.0
         */
        public function wc_paggi_gateway_init() {

            include_once dirname(__FILE__) . '/includes/class-wc-paggi-gateway.php';
            include_once dirname(__FILE__) . '/includes/class-wc-paggi-api.php';
            $this->gateway = new WC_Paggi_Gateway();
        }

        /**
         * Get templates path.
         *
         * @since 0.1.0
         * @return string
         */
        public static function get_templates_path() {
            return plugin_dir_path(__FILE__) . 'templates/';
        }

    }

    add_action('plugins_loaded', array('WC_Paggi', 'get_instance'));
endif;
