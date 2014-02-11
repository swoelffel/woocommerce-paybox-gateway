<?php
	/**
	 * Thankyou Paybox Shortcode
	 *
	 * The thankyou page displays after successful checkout and can be hooked into by payment gateways.
	 *
	 * @author 	SWO (OpenBoutique)
	 * @category 	Shortcodes
	 * @package 	WordPress
	 * @version     0.4.2
	 */

	/**
	 * Get the thankyou shortcode content.
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 */
	function get_shortcode_woocommerce_paybox_gateway_thanks( $atts )
	{
		global $woocommerce;
		return $woocommerce->shortcode_wrapper('woocommerce_paybox_gateway_thanks', $atts);
	}

	/**
	 * Outputs the thankyou page
	 *
	 * @access public
	 * @param mixed $atts
	 * @return void
	 */
	function woocommerce_paybox_gateway_thanks( $atts )
	{
		global $woocommerce;

		$woocommerce->nocache();
		$woocommerce->show_messages();

		// Pay for order after checkout step
		if (isset($_GET['order'])) 
			$order_id = (int)$_GET['order']; 
		else 
			$order_id = 0;
		//if (isset($_GET['key'])) $order_key = $_GET['key']; else $order_key = '';

		// Empty awaiting payment session
		unset( $woocommerce->session->order_awaiting_payment );

		if ($order_id > 0)
			$order = new WC_Order( $order_id );
		else
			$order = false;

		woocommerce_get_template( 'checkout/thankyou.php', array( 'order' => $order ) );
	}
?>