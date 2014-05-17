=== Woocommerce PayBox Payment Gateway === Contributors: CASTELIS (SWO) Tags: woocommerce, commerce, e-commerce, ecommerce, payment, payment gateway, paybox Requires at least: 3.5.0 Tested up to: 3.5.0 Stable tag: 0.1.0 License: GPLv2 or later License URI: http://www.gnu.org/licenses/gpl-2.0.html

PayBox payment gateway for woocommerce.

== Description ==

Payment gateway using Paybox System

== Installation ==

Download the appropritate executable at http://www1.paybox.com/telechargement_focus.aspx?cat=3 Please note that Plug-in uses the CGI as a regular executable, not through a browser

Upload to the /wp-content/plugins/ directory

Activate the plugin through the 'Plugins' menu in WordPress

copy Paybox CGI (downloaded at step 0) to a directory visible by Apache on your server

Be careful to setup permissions for this file (readable, executable by Apache)

Fill parameters in the Admin dedicated screen

== Frequently Asked Questions ==

None so far ...

== Upgrade Notice ==

Nothing to do

== Changelog ==

= 0.1.0 = first version.

= 0.2.0 = Check compatibility wih WooCommerce 2.0

= 0.2.1 = Improve autoresponse controls and allow webmaster testing.

= 0.2.2 = Solve an issue blocking parameters saving on Payment Gateways back office.

= 0.2.3 = Add 2 paybox parameters for callback Cancel or Failed (dedicated URL)

= 0.3.0 =   Solve bug when using Paybox testing parameters
            Add automatic landing page creation for cancel and refused transactions
            Add link to send parameters to support servers
            AutoFill parameters with Paybox testing parameters on first install.

= 0.3.2 =   Assets issue

= 0.3.4 =   Restore short-code for received page
            Improve support form
            Add automatic creation for Paybox order received page including dedicated shortcode
            Add a new parameter to allow changing the delay before redirecting to pay box gateway while checkout

= 0.3.5 =   Mise à jour pour tenir compte de la documentation http://www1.paybox.com/telechargements/ManuelIntegrationPayboxSystem_V6.1_FR.pdf du 27/11/2013

= 0.3.6 =   Issue correction while redirection timeout is not set / 3000 ms by default

= 0.4.0 =	Fix links when using multisite with sub-directories
			Fix non-default DB prefix
			Improve checks on pages creation
			Add security when activating plugin
			Add FR & US translations
			Rename shortcode
			Update paybox image
			Code cleanup & Optimizations
			Check compatibility with WordPress 3.8
			
= 0.4.2 = 	Remove unneeded code

= 0.4.3 =	Update to WooCommerce 2.X.X

= 0.4.4 =	Update ShortCodes to WooCommerce 2.X.X / Code cleaning / Optimization

= 0.4.5 =	Fix ShortCode

= 0.4.6 =	Add HMAC capability
