<?php
/*
 * Plugin Name: Komfortkasse for WooCommerce
 * Plugin URI: https://komfortkasse.eu/woocommerce
 * Description: Automatic assignment of bank wire transfers | Automatischer Zahlungsabgleich f&uuml;r Zahlungen per &Uuml;berweisung
 * Version: 1.3.13
 * Author: Komfortkasse Integration Team
 * Author URI: https://komfortkasse.eu
 * License: CC BY-SA 4.0
 * License URI: http://creativecommons.org/licenses/by-sa/4.0/
 * Text Domain: komfortkasse-for-woocommerce
 * Domain Path: /langs
 * WC requires at least: 2.4
 * WC tested up to: 4.1
 */
defined('ABSPATH') or die('Komfortkasse Plugin');

$woocommerce_active = false;
$germanized_active = false;
if (is_multisite()) {
    if (!function_exists('is_plugin_active_for_network')) {
        require_once (ABSPATH . '/wp-admin/includes/plugin.php');
    }
    $woocommerce_active = is_plugin_active_for_network('woocommerce/woocommerce.php');
    $germanized_active = is_plugin_active_for_network('woocommerce-germanized/woocommerce-germanized.php');
}
if (!$woocommerce_active)
    $woocommerce_active = in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')));
if (!$germanized_active)
    $germanized_active = in_array('woocommerce-germanized/woocommerce-germanized.php', apply_filters('active_plugins', get_option('active_plugins')));

if ($woocommerce_active) {

    add_action('woocommerce_thankyou_order_id', 'notifyorder');
    add_action('woocommerce_order_status_on-hold', 'notifyorderstatus');
    add_action('woocommerce_order_status_processing', 'notifyorderstatus');
    add_action('woocommerce_refund_created', 'notifyrefund');
    add_action('update_post_metadata', 'notifyinvoice', null, 5);

    // add custom endpoints for version number, invoice pdfs
    add_action('rest_api_init', function ()
    {
        register_rest_route('komfortkasse/v1', '/invoicepdf/(?P<id>\d+)', array ('methods' => 'GET','callback' => 'getinvoicepdf'
        ));
        register_rest_route('komfortkasse/v1', '/version', array ('methods' => 'GET','callback' => 'getversion'
        ));
        register_rest_route('komfortkasse/v1', '/orderid/(?P<number>.+)', array ('methods' => 'GET','callback' => 'getorderid'
        ));
    });

    // Save latest invoice number of an order as meta, see https://gist.github.com/vendidero/23de8d9baa10c4c01b4982650c54c334
    if ($germanized_active) {
        add_action( 'woocommerce_gzdp_before_invoice_refresh', 'germanized_store_latest_invoice_number', 10, 1 );
    }

    load_plugin_textdomain('woo-komfortkasse', false, dirname(plugin_basename(__FILE__)) . '/langs/');
    __('Komfortkasse', 'komfortkasse-for-woocommerce');
}


function germanized_store_latest_invoice_number( $invoice ) {
    if ( 'invoice' === $invoice->content_type && 'simple' === $invoice->type ) {
        if ( $order = wc_get_order( $invoice->order ) ) {
            update_post_meta( $order->get_id(), '_wc_gzdp_latest_invoice_number', $invoice->get_title() );
        }
    }
}


function getversion()
{
    $ret = array ();
    $ret ['version'] = '1.3.13';
    return $ret;

}


function notifyinvoice($check, $object_id, $meta_key, $meta_value, $prev_value)
{
    if ($meta_key == '_wp_wc_running_invoice_number' || $meta_key == '_wcpdf_invoice_number') {
        $query = http_build_query(array ('id' => $object_id,'url' => site_url(),'invoice_number' => $meta_value
        ));
        $contextData = array ('method' => 'POST','timeout' => 2,'header' => "Connection: close\r\n" . 'Content-Length: ' . strlen($query) . "\r\n",'content' => $query
        );

        $context = stream_context_create(array ('http' => $contextData
        ));

        $result = @file_get_contents('http://api.komfortkasse.eu/api/shop/invoice.jsf', false, $context);
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
    notifyorder($refund->get_parent_id());

}


function notifyorder($id)
{
    $query = http_build_query(array ('id' => $id,'url' => site_url()
    ));

    $contextData = array ('method' => 'POST','timeout' => 2,'header' => "Connection: close\r\n" . 'Content-Length: ' . strlen($query) . "\r\n",'content' => $query
    );

    $context = stream_context_create(array ('http' => $contextData
    ));

    $result = @file_get_contents('http://api.komfortkasse.eu/api/shop/neworder.jsf', false, $context);

    return $id;

}


function getinvoicepdf($data)
{
    $woopdf_active = false;
    if (is_multisite()) {
        if (!function_exists('is_plugin_active_for_network')) {
            require_once (ABSPATH . '/wp-admin/includes/plugin.php');
        }
        $woopdf_active = is_plugin_active_for_network('woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php');
    }
    if (!$woopdf_active)
        $woopdf_active = in_array('woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php', apply_filters('active_plugins', get_option('active_plugins')));

    if ($woopdf_active) {
        if (class_exists('WPO\WC\PDF_Invoices\Compatibility\WC_Core') && function_exists('wcpdf_get_invoice')) {
            $orderid = $data ['id'];
            $order = WPO\WC\PDF_Invoices\Compatibility\WC_Core::get_order($orderid);
            if ($invoice = wcpdf_get_invoice($order)) {
                if ($invoice->get_number()) {
                    $ret = array ();
                    $ret ['invoice_number'] = $invoice->get_number()->formatted_number;
                    $ret ['pdf_base64'] = base64_encode($invoice->get_pdf());
                    return $ret;
                }
            }
        }
    }

}


function getorderid($data)
{
    add_filter('woocommerce_order_data_store_cpt_get_orders_query', 'handle_custom_query_var', 10, 2);
    $orders = wc_get_orders(array ('_order_number' => $data ['number']
    ));
    if (count($orders) < 1)
        $orders = wc_get_orders(array ('_order_number_formatted' => $data ['number']
        ));
    if (count($orders) < 1)
        $orders = wc_get_orders(array ('_alg_wc_custom_order_number' => $data ['number']
        ));

    return count($orders) < 1 ? '' : $orders [0]->get_id();

}


function handle_custom_query_var($query, $query_vars)
{
    if (!empty($query_vars ['_order_number'])) {
        $query ['meta_query'] [] = array ('key' => '_order_number','value' => esc_attr($query_vars ['_order_number'])
        );
    }
    if (!empty($query_vars ['_order_number_formatted'])) {
        $query ['meta_query'] [] = array ('key' => '_order_number_formatted','value' => esc_attr($query_vars ['_order_number_formatted'])
        );
    }
    if (!empty($query_vars ['_alg_wc_custom_order_number'])) {
        $query ['meta_query'] [] = array ('key' => '_alg_wc_custom_order_number','value' => esc_attr($query_vars ['_alg_wc_custom_order_number'])
        );
    }

    return $query;

}
