=== Woocommerce PayBox Payment Gateway ===
Contributors: CASTELIS (SWO)
Tags: woocommerce, commerce, e-commerce, ecommerce, payment, payment gateway, paybox
Requires at least: 3.5.0
Tested up to: 3.5.0
Stable tag: 0.1.0
License: GPLv2 or later 
License URI: http://www.gnu.org/licenses/gpl-2.0.html

PayBox payment gateway for woocommerce.

== Description ==

Payment gateway using Paybox System

== Installation ==

0. Download the appropritate executable at http://www1.paybox.com/telechargement_focus.aspx?cat=3
    Please note that Plug-in uses the CGI as a regular executable, not through a browser

1. Upload to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. copy Paybox CGI (downloaded at step 0) to a directory visible by Apache on your server
4. Be careful to setup permissions for this file (readable, executable by Apache)
5. Fill parameters in the Admin dedicated screen

== Frequently Asked Questions ==

None so far ...

== Upgrade Notice ==

Nothing to do

== Changelog ==

= 0.1.0 =
first version.

= 0.2.0 =
Check compatibility wih WooCommerce 2.0

= 0.2.1 =
Improve autoresponse controls and allow webmaster testing.

= 0.2.2 =
Solve an issue blocking parameters saving on Payment Gateways back office.

= 0.2.3 =
Add 2 paybox parameters for callback Cancel or Failed (dedicated URL)