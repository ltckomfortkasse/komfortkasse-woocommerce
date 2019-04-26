<?php
/*
 * Plugin Name: Komfortkasse for WooCommerce
 * Plugin URI: https://komfortkasse.eu/woocommerce
 * Description: Automatic assignment of bank wire transfers | Automatischer Zahlungsabgleich f&uuml;r Zahlungen per &Uuml;berweisung
 * Version: 1.3.8
 * Author: Komfortkasse Integration Team
 * Author URI: https://komfortkasse.eu
 * License: CC BY-SA 4.0
 * License URI: http://creativecommons.org/licenses/by-sa/4.0/
 * Text Domain: komfortkasse-for-woocommerce
 * Domain Path: /langs
 * WC requires at least: 2.4
 * WC tested up to: 3.6
 */
defined('ABSPATH') or die('Komfortkasse Plugin');

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    add_action('woocommerce_thankyou_order_id', 'notifyorder');
    add_action('woocommerce_order_status_on-hold', 'notifyorderstatus');
    add_action('woocommerce_order_status_processing', 'notifyorderstatus');
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



    load_plugin_textdomain('woo-komfortkasse', false, dirname(plugin_basename(__FILE__)) . '/langs/');
    __('Komfortkasse', 'komfortkasse-for-woocommerce');
}


function getversion()
{
    $ret = array ();
    $ret ['version'] = '1.3.8';
    return $ret;

}


function notifyinvoice($check, $object_id, $meta_key, $meta_value, $prev_value)
{
    if ($meta_key == '_wp_wc_running_invoice_number') {
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
    if (in_array('woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php', apply_filters('active_plugins', get_option('active_plugins')))) {
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
    return count($orders) < 1 ? '' : $orders [0]->get_id();

}


function handle_custom_query_var($query, $query_vars)
{
    if (!empty($query_vars ['_order_number'])) {
        $query ['meta_query'] [] = array ('key' => '_order_number','value' => esc_attr($query_vars ['_order_number'])
        );
    }

    return $query;

}
