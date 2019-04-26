=== Komfortkasse for WooCommerce ===
Contributors: komfortkasse
Tags: payment,bacs,banktransfer,sepa,prepayment,cod,invoice,woocommerce, woocommerce payment,woocommerce payment gateway,komfortkasse
Requires at least: 4.4
Tested up to: 5.1
License: CC BY-ND 4.0
Stable tag: 1.3.8
License URI: http://creativecommons.org/licenses/by-nd/4.0/


Automatic assignment of bank wire transfers


== Description ==
Komfortkasse retrieves bank transfers from a bank account and automatically assignes them to unpaid orders. The assignment is done automatically in the background and is fault-tolerant, i.e. an order can still be assigned if the name does not match, the order number is not specified or the amount differs.

In addition, automatic payment reminders can be sent and refunds can be performed directly.

This extension provides the interface for the Komfortkasse online service, transferring information about open orders to Komfortkasse and retrieveing payment status updates from Komfortkasse. Registration on komfortkasse.eu is required (free package available). You even don\'t need a bank account, you can use Komfortkasse\'s omnibus account. This plugin is designed for SEPA countries. It also works if your company is outside SEPA, but serving customers in that area.



== Installation ==
Installation Instructions: https://komfortkasse.eu/anleitungen/42-woocommerce#plugin



== Changelog ==

= 1.0 =
Private beta

= 1.0.1 =
First public release

= 1.0.2 =
Added stable tag

= 1.0.4 =
Added tags

= 1.0.6 =
No changes

= 1.0.7 =
Added changelog

= 1.1.0 =
Recognizes invoice numbers from German Market plugin now

= 1.2.0 = 
Compatibility with WPML

= 1.2.1 =
Added Text Domain

= 1.2.2 =
Added Domain Path

= 1.2.4 =
Changed Text Domain

= 1.2.5 =
Removed non-English texts

= 1.3.0 =
Added custom API endpoints. Requires WP 4.4+ and WC 2.6+. Added PDF and invoice functionality from woocommerce-pdf-invoices-packing-slips.

= 1.3.2 =
Improved stability for wcpdf

= 1.3.3 =
Added instant order notification for on-hold orders

= 1.3.4 =
WooCommerce 3.3 compatibility

= 1.3.5 =
Added instant order notification for processing orders

= 1.3.6 =
WP 5 / WC 3.5 compatibility

= 1.3.7 =
added endpoint for reading order id from order number

= 1.3.8 =
WooCommerce 3.6 compatibility
