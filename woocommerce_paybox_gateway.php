<?php
	/**
	 * Plugin Name: WooCommerce Paybox Payment Gateway
	 * Plugin URI: http://www.openboutique.fr/
	 * Description: Gateway e-commerce pour Paybox.
	 * Version: 0.4.6
	 * Author: SWO (Open Boutique)
	 * Author URI: http://www.openboutique.fr/
	 * License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
	 *
	 * @package WordPress
	 * @author SWO (Open Boutique)
	 * @since 0.1.0
	 */

	if(!defined('ABSPATH'))
		exit;

	function activate_paybox_gateway()
	{
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if( !is_plugin_active('woocommerce/woocommerce.php') )
		{
			_e('Le plugin WooCommerce doit être activé pour l\'activation de l\'extension', 'openboutique_paybox_gateway');
			exit;
		}
		if( !class_exists('WC_Payment_Gateway') )
		{
			_e('Une erreur est survenue concernant WooCommerce : Les méthodes de paiement semblent introuvables', 'openboutique_paybox_gateway');
			exit;
		}
	}
	register_activation_hook(__FILE__, 'activate_paybox_gateway');
	add_action('plugins_loaded', 'woocommerce_paybox_init', 0);

	function woocommerce_paybox_init()
	{
		if( class_exists('WC_Payment_Gateway') )
		{
			include_once( plugin_dir_path( __FILE__ ).'woocommerce_paybox_gateway.class.php' );
			include_once( plugin_dir_path( __FILE__ ).'shortcode_woocommerce_paybox_gateway.php' );
		} else
			exit;

		DEFINE('PLUGIN_DIR', plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__)));
		DEFINE('OB_VERSION', '0.4.6');
		DEFINE('THANKS_SHORTCODE', 'woocommerce_paybox_gateway_thanks');

		// Chargement des traductions
		load_plugin_textdomain('openboutique_paybox_gateway', false, dirname(plugin_basename(__FILE__)).'/lang/');

		add_shortcode( THANKS_SHORTCODE, 'WC_Shortcode_Paybox_Thankyou::get' );
		add_filter('woocommerce_payment_gateways', 'add_paybox_commerce_gateway');
		add_action('init', 'woocommerce_paybox_check_response');
	}

	/*
	 * Ajout de la "gateway" Paybox à woocommerce
	 */
	function add_paybox_commerce_gateway($methods)
	{
		$methods[] = 'WC_Paybox';
		return $methods;
	}

	/**
	 * Reponse Paybox (Pour le serveur Paybox)
	 *
	 * @access public
	 * @return void
	 */
	function woocommerce_paybox_check_response()
	{
		if (isset($_GET['order']) && isset($_GET['sign']))
		{ // On a bien un retour ave une commande et une signature
			$order = new WC_Order((int) $_GET['order']); // On récupère la commande
			$pos_qs = strpos($_SERVER['REQUEST_URI'], '?');
			$pos_sign = strpos($_SERVER['REQUEST_URI'], '&sign=');
			$return_url = substr($_SERVER['REQUEST_URI'], 1, $pos_qs - 1);
			$data = substr($_SERVER['REQUEST_URI'], $pos_qs + 1, $pos_sign - $pos_qs - 1);
			$sign = substr($_SERVER['REQUEST_URI'], $pos_sign + 6);
			// Est-on en réception d'un retour PayBox
			$my_WC_Paybox = new WC_Paybox();
			if (str_replace('//', '/', '/' . $return_url) == str_replace('//', '/', $my_WC_Paybox->return_url))
			{
				$std_msg = __('Paybox Return IP', 'openboutique_paybox_gateway').' : '.WC_Paybox::getRealIpAddr().'<br/>'.$data.'<br/><div style="word-wrap:break-word;">'.__('PBX Sign', 'openboutique_paybox_gateway').' : '. $sign . '<div>';
				@ob_clean();
				// Traitement du retour PayBox
				// PBX_RETOUR=order:R;erreur:E;carte:C;numauto:A;numtrans:S;numabo:B;montantbanque:M;sign:K
				if (isset($_GET['erreur']))
				{
					switch ($_GET['erreur'])
					{
						case '00000':
							// OK Pas de pb
							// On vérifie la clef
							// recuperation de la cle publique
							$fp = $filedata = $key = FALSE;
							$fsize = filesize(dirname(__FILE__) . '/lib/pubkey.pem');
							$fp = fopen(dirname(__FILE__) . '/lib/pubkey.pem', 'r');
							$filedata = fread($fp, $fsize);
							fclose($fp);
							$key = openssl_pkey_get_public($filedata);
							$decoded_sign = base64_decode(urldecode($sign));
							$verif_sign = openssl_verify($data, $decoded_sign, $key);
							if ($verif_sign == 1) 
							{	// La commande est bien signé par PayBox
								// Si montant ok
								if ((int) (100 * $order->get_total()) == (int) $_GET['montantbanque']) 
								{
									$order->add_order_note('<p style="color:green"><b>'.__('Paybox Return OK', 'openboutique_paybox_gateway').'</b></p><br/>' . $std_msg);
									$order->payment_complete();
									wp_die(__('OK', 'openboutique_paybox_gateway'), '', array('response' => 200));
								}
								$order->add_order_note('<p style="color:red"><b>'.__('ERROR', 'openboutique_paybox_gateway').'</b></p> '.__('Order Amount', 'openboutique_paybox_gateway').'.<br/>' . $std_msg);
								wp_die(__('KO Amount modified', 'openboutique_paybox_gateway').' : ' . $_GET['montantbanque'] . ' / ' . (100 * $order->get_total()), '', array('response' => 406));
							}
							$order->add_order_note('<p style="color:red"><b>'.__('ERROR', 'openboutique_paybox_gateway').'</b></p> '.__('Signature Rejected', 'openboutique_paybox_gateway').'.<br/>' . $std_msg);
							wp_die(__('KO Signature', 'openboutique_paybox_gateway'), '', array('response' => 406));
						default:
							$order->add_order_note('<p style="color:red"><b>'.__('PBX ERROR', 'openboutique_paybox_gateway').' ' . $_GET['erreur'] . '</b> ' . WC_Paybox::getErreurMsg($_GET['erreur']) . '</p><br/>' . $std_msg);
							wp_die(__('OK received', 'openboutique_paybox_gateway'), '', array('response' => 200));
					}
				} else
					wp_die(__('Test AutoResponse OK', 'openboutique_paybox_gateway'), '', array('response' => 200));
			}
		}
	}
?>