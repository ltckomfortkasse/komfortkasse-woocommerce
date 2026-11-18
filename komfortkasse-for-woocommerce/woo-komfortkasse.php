<?php
/*
 * Plugin Name: Komfortkasse for WooCommerce
 * Plugin URI: https://komfortkasse.eu/woocommerce
 * Description: Automatic assignment of bank wire transfers | Automatischer Zahlungsabgleich f&uuml;r Zahlungen per &Uuml;berweisung
 * Version: 1.5.3
 * Author: Komfortkasse Integration Team
 * Author URI: https://komfortkasse.eu
 * License: CC BY-SA 4.0
 * License URI: http://creativecommons.org/licenses/by-sa/4.0/
 * Text Domain: komfortkasse-for-woocommerce
 * Domain Path: /langs
 * WC requires at least: 2.4
 * WC tested up to: 11.2
 */
defined('ABSPATH') or die('Komfortkasse Plugin');

$woocommerce_active = false;
$germanized_active = false;
if (is_multisite()) {
    if (! function_exists('is_plugin_active_for_network')) {
        require_once (ABSPATH . '/wp-admin/includes/plugin.php');
    }
    $woocommerce_active = is_plugin_active_for_network('woocommerce/woocommerce.php');
    $germanized_active = is_plugin_active_for_network('woocommerce-germanized/woocommerce-germanized.php');
}
if (! $woocommerce_active)
    $woocommerce_active = in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')));
    if (! $germanized_active)
        $germanized_active = in_array('woocommerce-germanized/woocommerce-germanized.php', apply_filters('active_plugins', get_option('active_plugins')));

        if ($woocommerce_active) {

            add_action( 'before_woocommerce_init', function() {
                if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
                    \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
                }
            } );

                add_action('woocommerce_thankyou_order_id', 'notifyorder');
                add_action('woocommerce_order_status_on-hold', 'notifyorderstatus');
                add_action('woocommerce_order_status_processing', 'notifyorderstatus');
                add_action('woocommerce_refund_created', 'notifyrefund');
                add_action('update_post_metadata', 'notifyinvoice', null, 5);
                add_action('init', 'load_i18n');
                add_filter('rest_authentication_errors', 'komfortkasse_allow_rest_access', 9999);
                add_filter('woocommerce_rest_is_request_to_rest_api', 'komfortkasse_enable_wc_rest_auth');

                // add custom endpoints for version number, invoice pdfs
                // All endpoints except /version require WooCommerce REST API authentication (API key of the shop).
                add_action('rest_api_init', function () {
                    register_rest_route('komfortkasse/v1', '/invoicepdf/(?P<id>\d+)', array(
                            'methods' => 'GET',
                            'callback' => 'getinvoicepdf',
                            'permission_callback' => 'komfortkasse_rest_permission'
                    ));
                    register_rest_route('komfortkasse/v1', '/version', array(
                            'methods' => 'GET',
                            'callback' => 'getversion',
                            'permission_callback' => '__return_true'
                    ));
                    register_rest_route('komfortkasse/v1', '/orderid/(?P<number>.+)', array(
                            'methods' => 'GET',
                            'callback' => 'getorderid',
                            'permission_callback' => 'komfortkasse_rest_permission'
                    ));
                    register_rest_route('komfortkasse/v1', '/orderidinvoice/(?P<number>.+)', array(
                            'methods' => 'GET',
                            'callback' => 'getorderidinvoice',
                            'permission_callback' => 'komfortkasse_rest_permission'
                    ));
                    register_rest_route('komfortkasse/v1', '/apitest', array(
                            'methods' => 'GET',
                            'callback' => 'apitest',
                            'permission_callback' => 'komfortkasse_rest_permission'
                    ));
                });

                // Save latest invoice number of an order as meta, see https://gist.github.com/vendidero/23de8d9baa10c4c01b4982650c54c334
                if ($germanized_active) {
                    add_action('woocommerce_gzdp_before_invoice_refresh', 'germanized_store_latest_invoice_number', 10, 1);
                }

        }

        function load_i18n() {
            load_plugin_textdomain('woo-komfortkasse', false, dirname(plugin_basename(__FILE__)) . '/langs/');
            __('Komfortkasse', 'komfortkasse-for-woocommerce');
        }

        // Let Komfortkasse reach its REST endpoints on installations that block REST access.
        // Security plugins (Wordfence, iThemes Security, Sucuri, All In One WP Security, Disable REST API, etc.)
        // and custom code in functions.php may hook into 'rest_authentication_errors' to block all
        // unauthenticated REST API access. This filter fires BEFORE the route's permission_callback.
        // Priority 9999 ensures this runs after those plugins.
        //
        // Only errors on requests to the plugin's own routes are lifted, identified by the resolved REST
        // route. Access control stays with the routes' permission_callback. WordPress's cookie/nonce check
        // is never overridden, so requests to other routes and CSRF attempts keep their error.
        function komfortkasse_allow_rest_access($errors) {
            if (! is_wp_error($errors)) {
                return $errors;
            }
            if (in_array('rest_cookie_invalid_nonce', $errors->get_error_codes(), true)) {
                return $errors;
            }
            if (! komfortkasse_is_own_rest_route()) {
                return $errors;
            }
            // An earlier filter may have blocked the request before WordPress's cookie check ran.
            // Run it now, so a browser session without a valid nonce never authenticates the request.
            $cookie_check = rest_cookie_check_errors(null);
            if (is_wp_error($cookie_check)) {
                return $cookie_check;
            }
            return true;
        }

        // Enable WooCommerce REST API authentication (consumer key/secret, OAuth 1.0a) for the plugin's
        // routes. WooCommerce only authenticates requests to its own namespaces by default. Runs when the
        // current user is determined, before the request is parsed, so the route is read from the URL.
        function komfortkasse_enable_wc_rest_auth($is_request_to_rest_api) {
            return $is_request_to_rest_api || komfortkasse_route_matches(komfortkasse_route_from_request_uri());
        }

        function komfortkasse_rest_permission() {
            if (function_exists('wc_rest_check_post_permissions')) {
                return wc_rest_check_post_permissions('shop_order', 'read');
            }
            return current_user_can('read_private_shop_orders');
        }

        function komfortkasse_is_own_rest_route() {
            if (! isset($GLOBALS['wp']) || ! is_object($GLOBALS['wp']) || empty($GLOBALS['wp']->query_vars['rest_route'])) {
                return false;
            }
            return komfortkasse_route_matches($GLOBALS['wp']->query_vars['rest_route']);
        }

        function komfortkasse_route_from_request_uri() {
            if (isset($_GET['rest_route'])) {
                return is_string($_GET['rest_route']) ? wp_unslash($_GET['rest_route']) : '';
            }
            $uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '';
            $path = (string) wp_parse_url($uri, PHP_URL_PATH);
            $prefix = '/' . trim(rest_get_url_prefix(), '/') . '/';
            $pos = strpos($path, $prefix);
            return $pos === false ? '' : '/' . substr($path, $pos + strlen($prefix));
        }

        function komfortkasse_route_matches($route) {
            if (! is_string($route) || $route === '') {
                return false;
            }
            $route = '/' . ltrim(untrailingslashit($route), '/');
            return preg_match('#^/komfortkasse/v1/(invoicepdf/\d+|version|orderid/.+|orderidinvoice/.+|apitest)$#iD', $route) === 1;
        }

        function germanized_store_latest_invoice_number($invoice)
        {
            if ('invoice' === $invoice->content_type && 'simple' === $invoice->type) {
                if ($order = wc_get_order($invoice->order)) {
                    $order->update_meta_data('_wc_gzdp_latest_invoice_number', $invoice->get_title());
                    $order->save();
                }
            }
        }

        function getversion()
        {
            $ret = array();
            $ret['version'] = '1.5.3';
            return $ret;
        }

        function apitest()
        {
            $params = array(
                    'id' => 'apitest',
                    'url' => site_url()
            );
            return notify('test', $params, 10);
        }

        // Notify Komfortkasse (new order, invoice number, API test). Uses the WordPress HTTP API over TLS only:
        // the certificate is verified (against the CA bundle shipped with WordPress) and redirects are not
        // followed, so the request is never sent anywhere else. There is no fallback to plain HTTP; if the
        // request fails, Komfortkasse still picks the order up on its regular sync.
        function notify($path, $params, $timeout = 2)
        {
            $response = wp_remote_post('https://ssl.komfortkasse.eu/api/shop/' . $path . '.jsf', array(
                    'body' => $params,
                    'timeout' => $timeout,
                    'redirection' => 0,
                    'sslverify' => true
            ));
            if (is_wp_error($response))
                return $response->get_error_message();

            return wp_remote_retrieve_body($response);
        }

        function notifyinvoice($check, $object_id, $meta_key, $meta_value, $prev_value)
        {
            if ($meta_key == '_wp_wc_running_invoice_number' || $meta_key == '_wcpdf_invoice_number' || $meta_key == '_wc_gzdp_latest_invoice_number') {
                notify('invoice', array(
                        'id' => $object_id,
                        'url' => site_url(),
                        'invoice_number' => $meta_value
                ));
            }
            return $check;
        }

        function notifyorderstatus($id)
        {
            $order = wc_get_order($id);
            if ($order) {
                $paid = $order->get_date_paid();
                if ($paid == null)
                    return notifyorder($id);
            }
            return $id;
        }

        function notifyrefund($refund_id)
        {
            $refund = wc_get_order($refund_id);
            if ($refund)
                notifyorder($refund->get_parent_id());
        }

        function notifyorder($id)
        {
            notify('neworder', array(
                    'id' => $id,
                    'url' => site_url()
            ));
            return $id;
        }

        function getinvoicepdf($data)
        {
            $ret = array();
            $ret['status'] = false;

            // "PDF Invoices & Packing Slips for WooCommerce" (WP Overnight). wcpdf_get_document() is the current API,
            // wcpdf_get_invoice() the one of older versions (deprecated since 4.6.3). The class
            // Compatibility\WC_Core checked up to 1.5.0 no longer exists in current versions.
            $has_get_document = function_exists('wcpdf_get_document');
            if (! $has_get_document && ! function_exists('wcpdf_get_invoice')) {
                return $ret;
            }
            $ret['status'] = true;

            $order = wc_get_order($data['id']);
            if (! $order) {
                return $ret;
            }
            // existing invoices only, never create one ($init = false)
            $invoice = $has_get_document ? wcpdf_get_document('invoice', $order) : wcpdf_get_invoice($order);
            if (! $invoice || ! is_object($invoice)) {
                return $ret;
            }
            $number = $invoice->get_number();
            if (is_object($number)) {
                $number = method_exists($number, 'get_formatted') ? $number->get_formatted() : (isset($number->formatted_number) ? $number->formatted_number : '');
            }
            if (! is_scalar($number) || (string) $number === '') {
                return $ret;
            }
            $ret['invoice_number'] = (string) $number;
            $ret['pdf_base64'] = base64_encode($invoice->get_pdf());
            return $ret;
        }

        function getorderid($data)
        {
            return komfortkasse_find_order_id(array(
                    '_order_number',
                    '_order_number_formatted',
                    '_alg_wc_full_custom_order_number',
                    '_alg_wc_custom_order_number'
            ), $data['number']);
        }

        function getorderidinvoice($data)
        {
            return komfortkasse_find_order_id(array(
                    '_wp_wc_running_invoice_number',
                    '_wcpdf_invoice_number',
                    '_wc_gzdp_latest_invoice_number'
            ), $data['number']);
        }

        // Returns the id of the order whose meta $key equals $number (first key that matches wins), or '' if none.
        // HPOS gets a meta_query, the legacy post storage the custom query var (see handle_custom_query_var); the post storage
        // rejects meta_query with a notice since WC 9.2. Every result is checked against its meta, so a storage that ignores
        // the condition never returns a wrong order.
        function komfortkasse_find_order_id($keys, $number)
        {
            $number = trim((string) $number);
            if ($number === '')
                return '';

            $hpos = class_exists('\Automattic\WooCommerce\Utilities\OrderUtil')
                    && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
            add_filter('woocommerce_order_data_store_cpt_get_orders_query', 'handle_custom_query_var', 10, 2);
            foreach ($keys as $key) {
                $args = array(
                        $key => $number
                );
                if ($hpos)
                    $args['meta_query'] = array(
                            array(
                                    'key' => $key,
                                    'value' => $number
                            )
                    );
                $orders = wc_get_orders($args);
                foreach ($orders as $order) {
                    if (strcasecmp(trim((string) $order->get_meta($key)), $number) === 0)
                        return $order->get_id();
                }
            }
            return '';
        }

        function handle_custom_query_var($query, $query_vars)
        {
            $keys = array(
                    '_order_number',
                    '_order_number_formatted',
                    '_alg_wc_full_custom_order_number',
                    '_alg_wc_custom_order_number',
                    '_wp_wc_running_invoice_number',
                    '_wcpdf_invoice_number',
                    '_wc_gzdp_latest_invoice_number'
            );
            foreach ($keys as $key) {
                if (! empty($query_vars[$key])) {
                    $query['meta_query'][] = array(
                            'key' => $key,
                            'value' => $query_vars[$key]
                    );
                }
            }

            return $query;
        }
