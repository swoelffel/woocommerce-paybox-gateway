<?php
	/**
	 * Thankyou Paybox Shortcode
	 *
	 * The thankyou page displays after successful checkout and can be hooked into by payment gateways.
	 *
	 * @author 	SWO (OpenBoutique)
	 * @category 	Shortcodes
	 * @package 	WordPress
	 * @version     0.4.6
	 */

	class WC_Shortcode_Paybox_Thankyou 
	{
		/**
		 * Get the thankyou shortcode content.
		 *
		 * @access public
		 * @param array $atts
		 * @return string
		 */
		public static function get( $atts )
		{
			return WC_Shortcodes::shortcode_wrapper( array( __CLASS__, 'output' ), $atts );
		}

		/**
		 * Outputs the thankyou page
		 *
		 * @access public
		 * @param mixed $atts
		 * @return void
		 */
		public static function output( $atts )
		{
			wc_print_notices();

			// Pay for order after checkout step
			if (isset($_GET['order'])) 
				$order_id = (int)$_GET['order']; 
			else 
				$order_id = 0;

			WC()->cart->empty_cart();

			if ($order_id > 0)
				$order = new WC_Order( $order_id );
			else
				$order = false;

			wc_get_template( 'checkout/thankyou.php', array( 'order' => $order ) );
		}
	}
?>