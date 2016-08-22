<?php
/*
 * Plugin Name: Komfortkasse for WooCommerce
 * Description: Automatic assignment of bank wire transfers | Automatischer Zahlungsabgleich f&uuml;r Zahlungen per &Uuml;berweisung
 * Version: 1.0.6
 * Author: Komfortkasse Integration Team
 * Author URI: http://komfortkasse.eu
 * License: CC BY-SA 4.0
 * License URI: http://creativecommons.org/licenses/by-sa/4.0/
 */
defined('ABSPATH') or die('Komfortkasse Plugin');

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    add_action('woocommerce_thankyou_order_id', 'notifyorder');
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
