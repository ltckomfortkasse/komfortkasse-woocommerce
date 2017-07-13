<?php
/*
 * Plugin Name: Komfortkasse for WooCommerce
 * Description: Automatic assignment of bank wire transfers | Automatischer Zahlungsabgleich f&uuml;r Zahlungen per &Uuml;berweisung
 * Version: 1.2.3
 * Author: Komfortkasse Integration Team
 * Author URI: http://komfortkasse.eu
 * License: CC BY-SA 4.0
 * License URI: http://creativecommons.org/licenses/by-sa/4.0/
 * Text Domain: woo-komfortkasse
 * Domain Path: /langs
 */
defined('ABSPATH') or die('Komfortkasse Plugin');

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    add_action('woocommerce_thankyou_order_id', 'notifyorder');
    add_action('update_post_metadata', 'notifyinvoice', null, 5);
    load_plugin_textdomain( 'woo-komfortkasse', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );
    __('Komfortkasse', 'woo-komfortkasse');
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
