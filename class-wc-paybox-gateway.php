<?php
	/**
	 * Plugin Name: WooCommerce Paybox Payment Gateway
	 * Plugin URI: http://www.openboutique.fr/
	 * Description: Gateway e-commerce pour Paybox.
	 * Version: 0.3.6
	 * Author: SWO (Open Boutique)
	 * Author URI: http://www.openboutique.fr/
	 * License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
	 *
	 * @package WordPress
	 * @author SWO (Open Boutique)
	 * @since 0.1.0
	 */

	add_action('plugins_loaded', 'woocommerce_paybox_init', 0);

	function woocommerce_paybox_init()
	{
		if (!class_exists('WC_Payment_Gateway'))
			return;

		DEFINE('PLUGIN_DIR', plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__)));
		DEFINE('VERSION', '0.3.6');
		DEFINE('THANKS_SHORTCODE', 'openboutique_thankyou');
		
		// Ajout des traductions
		load_plugin_textdomain('openboutique_paybox_gateway', false, dirname(plugin_basename(__FILE__)).'/lang/');

		/*
		 * Paybox Commerce Gateway Class
		 */

		class WC_Paybox extends WC_Payment_Gateway
		{
			function __construct()
			{
				global $woocommerce;
				$this->id = 'paybox';
				$this->icon = PLUGIN_DIR . '/images/paybox.png';
				$this->has_fields = false;
				$this->method_title = 'PayBox';
				// Load the form fields
				$this->init_form_fields();
				// Load the settings.
				$this->init_settings();
				// Get setting values
				foreach ($this->settings as $key => $val)
					$this->$key = $val;
				// Logs
				if ($woocommerce->debug == 'yes')
					$this->log = $woocommerce->logger();
				// Ajout des Hooks
				add_action('woocommerce_update_options_payment_gateways', array(&$this, 'process_admin_options'));
				add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
			}

			/*
			 * Admin tools.
			 */

			public function admin_options()
			{
				echo '<h3>'.__('OpenBoutique PayBox Gateway', 'openboutique_paybox_gateway').'</h3>
					<div id="wc-ob-pbx-admin">
						<div id="ob-paybox_baseline">
							'.__('PayBox Gateway is an', 'openboutique_paybox_gateway').' <a href="http://www.openboutique.fr/?wcpbx='.VERSION.'" target="_blank">OpenBoutique</a> '.__('technology', 'openboutique_paybox_gateway').'
						</div>
						<div>';
				wp_enqueue_style('custom_openboutique_paybox_css', PLUGIN_DIR . '/css/style.css', false, VERSION);
				wp_enqueue_script('custom_openboutique_paybox_js', PLUGIN_DIR . '/js/script.js', false, VERSION);
				$install_url = '';
				if (!get_option('woocommerce_pbx_order_received_page_id')) {
					$install_url .= '&install_pbx_received_page=true';
				}
				if (!get_option('woocommerce_pbx_order_refused_page_id')) {
					$install_url .= '&install_pbx_refused_page=true';
				}
				if (!get_option('woocommerce_pbx_order_canceled_page_id')) {
					$install_url .= '&install_pbx_canceled_page=true';
				}
				if ($install_url != '' && !isset($_GET['install_pbx_received_page']) && !isset($_GET['install_pbx_refused_page']) && !isset($_GET['install_pbx_canceled_page']))
				{
					echo 
						'<p>'.
							__('We have detected that Paybox return pages are not currently installed on your system', 'openboutique_paybox_gateway').'<br/>'.__('Press the install button to prevent 404 from users whom transaction would have been received, canceled or refused.', 'openboutique_paybox_gateway').
						'</p>
						<p>
							<a class="button" target="_self" href="./admin.php?page=woocommerce_settings&tab=payment_gateways&section=WC_Paybox'.$install_url.'">'.__('Install return pages', 'openboutique_paybox_gateway').'</a>
						</p>';
				} else { 
					echo
						'<p>'.
							__('Paybox return pages are installed', 'openboutique_paybox_gateway').' : 
							<a target="_self" href="./post.php?post='.get_option('woocommerce_pbx_order_received_page_id').'&action=edit">'.__('received', 'openboutique_paybox_gateway').'</a> | 
							<a target="_self" href="./post.php?post='.get_option('woocommerce_pbx_order_canceled_page_id').'&action=edit">'.__('canceled', 'openboutique_paybox_gateway').'</a> | 
							<a target="_self" href="./post.php?post='.get_option('woocommerce_pbx_order_refused_page_id').'&action=edit">'.__('refused', 'openboutique_paybox_gateway').'</a>
						</p>';
				}
				echo '
						</div>
						<div>
							<p>
								<a class="button-primary" id="ob-paybox_show_help" href="#">
									'.__('Need help ?', 'openboutique_paybox_gateway').'
								</a>
							</p>
						</div>
						<div id="ob-paybox_help_div">
							<p>
								'.__('Press', 'openboutique_paybox_gateway').' "'.__('Send report', 'openboutique_paybox_gateway').'" '.__('button and fill your email in order to post your', 'openboutique_paybox_gateway').' <b>'.__('Paybox Gateway parameters', 'openboutique_paybox_gateway').'</b> '.__('to OpenBoutique support team', 'openboutique_paybox_gateway').'<br/>
								'.__('Your email', 'openboutique_paybox_gateway').' : <input type="text" name="email" placeholder="'.__('Your email', 'openboutique_paybox_gateway').'" /><br/>
								'.__('Your message', 'openboutique_paybox_gateway').' :<br/><textarea name="help_text" rows="4" cols="80"></textarea>
								<input type="hidden" name="website" value="'.$_SERVER['SERVER_NAME'].'" />
								<input type="hidden" name="WCPBX_version" value="'.VERSION.'" />
								<input type="hidden" name="woocommerce_pbx_order_received_page_id" value="'.get_option('woocommerce_pbx_order_received_page_id').'" />
								<input type="hidden" name="woocommerce_pbx_order_refused_page_id" value="'.get_option('woocommerce_pbx_order_refused_page_id').'" />
								<input type="hidden" name="woocommerce_pbx_order_canceled_page_id" value="'.get_option('woocommerce_pbx_order_canceled_page_id').'" />
								<br/><a class="button" id="ob-paybox_send_report" href="#">'.__('Send report', 'openboutique_paybox_gateway').'</a>
							</p>
							<iframe name="myOB_iframe" id="myOB_iframe" style="display: none"></iframe>
						</div>
					</div>
					<table class="form-table">';
						$this->generate_settings_html();
				echo '</table><!--/.form-table-->';

				if (!empty($_GET['install_pbx_received_page'])) {
					// Page paiement refusé -> A venir short code pour interpretation du code retour
					$this->create_page(esc_sql('order-pbx-received'), 'woocommerce_pbx_order_received_page_id', __('Order PBX Received', 'openboutique_paybox_gateway'), '['.THANKS_SHORTCODE.']', woocommerce_get_page_id('checkout'));
				}
				if (!empty($_GET['install_pbx_refused_page'])) {
					// Page paiement refusé -> A venir short code pour interpretation du code retour
					$this->create_page(esc_sql('order-pbx-refused'), 'woocommerce_pbx_order_refused_page_id', __('Order PBX Refused', 'openboutique_paybox_gateway'), __('Your order has been refused', 'openboutique_paybox_gateway'), woocommerce_get_page_id('checkout'));
				}
				if (!empty($_GET['install_pbx_canceled_page'])) {
					// Page paiement annulé par le client
					$this->create_page(esc_sql('order-pbx-canceled'), 'woocommerce_pbx_order_canceled_page_id', __('Order PBX Canceled', 'openboutique_paybox_gateway'), __('Your order has been cancelled', 'openboutique_paybox_gateway'), woocommerce_get_page_id('checkout'));
				}
			}

			function create_page($slug, $option, $page_title = '', $page_content = '', $post_parent = 0)
			{
				global $wpdb;
				$option_value = get_option($option);
				if ($option_value > 0 && get_post($option_value))
					return;

				$page_found = $wpdb->get_var($wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE post_name = %s LIMIT 1;", $slug));
				if ($page_found && !$option_value)
				{
					update_option($option, $page_found);
					return;
				}
				$page_data = array(
					'post_status' => 'publish',
					'post_type' => 'page',
					'post_author' => 1,
					'post_name' => $slug,
					'post_title' => $page_title,
					'post_content' => $page_content,
					'post_parent' => $post_parent,
					'comment_status' => 'closed'
				);
				$page_id = wp_insert_post($page_data);
				update_option($option, $page_id);
			}

			/*
			 * Initialize Gateway Settings Form Fields.
			 */

			function init_form_fields() 
			{
				$this->form_fields = array(
					'enabled' => array(
						'title' => __('Enable/Disable', 'openboutique_paybox_gateway'),
						'type' => 'checkbox',
						'label' => __('Enable Paybox Payment', 'openboutique_paybox_gateway'),
						'default' => 'yes'
					),
					'title' => array(
						'title' => __('Title', 'openboutique_paybox_gateway'),
						'type' => 'text',
						'description' => __('This controls the title which the user sees during checkout.', 'openboutique_paybox_gateway'),
						'default' => __('Paybox Payment', 'openboutique_paybox_gateway')
					),
					'description' => array(
						'title' => __('Customer Message', 'openboutique_paybox_gateway'),
						'type' => 'textarea',
						'description' => __('Let the customer know the payee and where they should be sending the Paybox to and that their order won\'t be shipping until you receive it.', 'openboutique_paybox_gateway'),
						'default' => __('Credit card payment by PayBox.', 'openboutique_paybox_gateway')
					),
					'paybox_site_id' => array(
						'title' => __('Site ID Paybox', 'openboutique_paybox_gateway'),
						'type' => 'text',
						'description' => __('Please enter you ID Site provided by PayBox.', 'openboutique_paybox_gateway'),
						'default' => '1999888'
					),
					'paybox_identifiant' => array(
						'title' => __('Paybox ID', 'openboutique_paybox_gateway'),
						'type' => 'text',
						'description' => __('Please enter you Paybox ID provided by PayBox.', 'openboutique_paybox_gateway'),
						'default' => '2'
					),
					'paybox_rang' => array(
						'title' => __('Paybox Rank', 'openboutique_paybox_gateway'),
						'type' => 'text',
						'description' => __('Please enter Paybox Rank provided by PayBox.', 'openboutique_paybox_gateway'),
						'default' => '99'
					),
					'paybox_wait_time' => array(
						'title' => __('Paybox Checkout waiting time', 'openboutique_paybox_gateway'),
						'type' => 'text',
						'description' => __('Time to wait before to redirect to Paybox gateway (in milliseconds).', 'openboutique_paybox_gateway'),
						'default' => '3000'
					),
					'return_url' => array(
						'title' => __('Paybox return URL', 'openboutique_paybox_gateway'),
						'type' => 'text',
						'description' => __('Please enter the autoreponse URL for PayBox.', 'openboutique_paybox_gateway'),
						'default' => '/paybox_autoresponse'
					),
					'callback_success_url' => array(
						'title' => __('Successful Return Link', 'openboutique_paybox_gateway'),
						'type' => 'text',
						'description' => __('Please enter callback link from PayBox when transaction succeed', 'openboutique_paybox_gateway').' ('.__('where you need to put the', 'openboutique_paybox_gateway').' ['.THANKS_SHORTCODE.']'.__('shortcode', 'openboutique_paybox_gateway').')',
						'default' => '/checkout/order-pbx-received/'
					),
					'callback_refused_url' => array(
						'title' => __('Failed Return Link', 'openboutique_paybox_gateway'),
						'type' => 'text',
						'description' => __('Please enter callback link from PayBox when transaction is refused by gateway.', 'openboutique_paybox_gateway'),
						'default' => '/checkout/order-pbx-refused/'
					),
					'callback_cancel_url' => array(
						'title' => __('Cancel Return Link', 'openboutique_paybox_gateway'),
						'type' => 'text',
						'description' => __('Please enter back link from PayBox when enduser cancel transaction.', 'openboutique_paybox_gateway'),
						'default' => '/checkout/order-pbx-canceled/'
					),
					'paybox_url' => array(
						'title' => __('Paybox URL', 'openboutique_paybox_gateway'),
						'type' => 'text',
						'description' => __('Please enter the posting URL for paybox Form', 'openboutique_paybox_gateway').'<br/>'.__('For testing', 'openboutique_paybox_gateway').' : https://preprod-tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi<br/>'.__('For production', 'openboutique_paybox_gateway').' : https://tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi',
						'default' => 'https://preprod-tpeweb.paybox.com/cgi/MYpagepaiement.cgi'
					),
					'prepost_message' => array(
						'title' => __('Customer Message', 'openboutique_paybox_gateway'),
						'type' => 'textarea',
						'description' => __('Message to the user before redirecting to PayBox.', 'openboutique_paybox_gateway'),
						'default' => __('You will be redirect to Paybox System payment gatway in a few seconds ... Please wait ...', 'openboutique_paybox_gateway')
					),
					'paybox_exe' => array(
						'title' => __('Complete path to PayBox CGI', 'openboutique_paybox_gateway'),
						'type' => 'textarea',
						'description' => __('Location for Paybox executable', 'openboutique_paybox_gateway').' (http://www1.paybox.com/telechargement_focus.aspx?cat=3)',
						'default' => '/the/path/to/paybox.cgi'
					)
				);
			}

			/**
			 * Process the payment and return the result
			 *
			 * @access public
			 * @param int $order_id
			 * @return array
			 */
			function process_payment($order_id)
			{
				//error_log('Call : process_payment');
				$order = new WC_Order($order_id);
				$paybox_form = $this->getParamPaybox($order);
				//error_log($paybox_form);
				$retour = '<p>' . $this->prepost_message . '</p>' . $paybox_form . "\r\n" . '
					<script>
						function launchPaybox()
						{
							document.PAYBOX.submit();
						}
						t=setTimeout("launchPaybox()",'.((isset($this->paybox_wait_time) && is_numeric($this->paybox_wait_time)) ? $this->paybox_wait_time : '3000').');
					</script>';
				wp_die($retour);
			}

			function getParamPaybox(WC_Order $order)
			{
				$param = 'PBX_MODE=4'; 	// Envoi en ligne de commande
				$param .= ' PBX_OUTPUT=B';
				$param .= ' PBX_SITE=' . $this->paybox_site_id;
				$param .= ' PBX_IDENTIFIANT=' . $this->paybox_identifiant;
				$param .= ' PBX_RANG=' . $this->paybox_rang;
				$param .= ' PBX_TOTAL=' . 100 * $order->get_total();
				$param .= ' PBX_CMD=' . $order->id;
				$param .= ' PBX_REPONDRE_A=http://' . str_replace('//', '/', $_SERVER['HTTP_HOST'] . '/' . $this->return_url);
				$param .= ' PBX_EFFECTUE=http://' . str_replace('//', '/', $_SERVER['HTTP_HOST'] . '/' . $this->callback_success_url);
				$param .= ' PBX_REFUSE=http://' . str_replace('//', '/', $_SERVER['HTTP_HOST'] . '/' . $this->callback_refused_url);
				$param .= ' PBX_ANNULE=http://' . str_replace('//', '/', $_SERVER['HTTP_HOST'] . '/' . $this->callback_cancel_url);
				$param .= ' PBX_DEVISE=978'; // Euro (à paramétriser)

				if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
					$param .= ' PBX_RETOUR=order:R;erreur:E;carte:C;numauto:A;numtrans:S;numabo:B;montantbanque:M;sign:K';
				else // Pour linux 
					$param .= ' PBX_RETOUR=order:R\\;erreur:E\\;carte:C\\;numauto:A\\;numtrans:S\\;numabo:B\\;montantbanque:M\\;sign:K';

				$email_address = $order->billing_email;
				if (empty($email_address) || !is_email($email_address))
				{
					$ids = $wpdb->get_result("
						SELECT 
							".$wpdb->base_prefix."users.ID 
						FROM 
							".$wpdb->base_prefix."users 
						WHERE (
							SELECT 
								".$wpdb->base_prefix."usermeta.meta_value 
							FROM 
								".$wpdb->base_prefix."usermeta 
							WHERE 
								".$wpdb->base_prefix."usermeta.user_id = ".$wpdb->base_prefix."users.ID AND 
								".$wpdb->base_prefix."usermeta.meta_key = '".$wpdb->base_prefix."capabilities'
						) 
						LIKE '%administrator%'");

					if ($ids)
					{
						$current_user = get_user_by('id', $ids[0]);
						$email_address = $current_user->user_mail;
					}
				}
				$param .= ' PBX_PORTEUR=' . $email_address; //. $order->customer_user;
				$exe = $this->paybox_exe;
				if (file_exists($exe))
				{
					//error_log($exe . ' ' . $param);
					$retour = shell_exec($exe . ' ' . $param);
					if ($retour != '')
						return str_replace('https://tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi', $this->paybox_url, $retour);
					return _('Permissions are not correctly set for file', 'openboutique_paybox_gateway').' '.$exe;
				}
				return _('Paybox CGI module can not be found', 'openboutique_paybox_gateway');
			}

			static function getRealIpAddr() 
			{
				if (!empty($_SERVER['HTTP_CLIENT_IP'])) 			//check ip from share internet
					$ip = $_SERVER['HTTP_CLIENT_IP'];
				elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))	//to check ip is pass from proxy
					$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
				else
					$ip = $_SERVER['REMOTE_ADDR'];
				return $ip;
			}

			static function getErreurMsg($code_erreur)
			{
				switch( $code_erreur )
				{
					case '00000':
						$erreur_msg = __('Opération réussie.', 'openboutique_paybox_gateway');
						break;
					case '00001':
						$erreur_msg = __('La connexion au centre d\'autorisation a échoué. Vous pouvez dans ce cas là effectuer les redirections des internautes vers le FQDN', 'openboutique_paybox_gateway').' tpeweb1.paybox.com.';
						break;
					case '00002':
						$erreur_msg = __('Une erreur de cohérence est survenue.', 'openboutique_paybox_gateway');
						break;
					case '00003':
						$erreur_msg = __('Erreur Paybox.', 'openboutique_paybox_gateway');
						break;
					case '00004':
						$erreur_msg = __('Numéro de porteur ou crytogramme visuel invalide.', 'openboutique_paybox_gateway');
						break;
					case '00006':
						$erreur_msg = __('Accès refusé ou site/rang/identifiant incorrect.', 'openboutique_paybox_gateway');
						break;
					case '00008':
						$erreur_msg = __('Date de fin de validité incorrecte.', 'openboutique_paybox_gateway');
						break;
					case '00009':
						$erreur_msg = __('Erreur de création d\'un abonnement.', 'openboutique_paybox_gateway');
						break;
					case '00010':
						$erreur_msg = __('Devise inconnue.', 'openboutique_paybox_gateway');
						break;
					case '00011':
						$erreur_msg = __('Montant incorrect.', 'openboutique_paybox_gateway');
						break;
					case '00015':
						$erreur_msg = __('Paiement déjà effectué', 'openboutique_paybox_gateway');
						break;
					case '00016':
						$erreur_msg = __('Abonné déjà existant (inscription nouvel abonné). Valeur \'U\' de la variable PBX_RETOUR.', 'openboutique_paybox_gateway');
						break;
					case '00021':
						$erreur_msg = __('Carte non autorisée.', 'openboutique_paybox_gateway');
						break;
					case '00029':
						$erreur_msg = __('Carte non conforme. Code erreur renvoyé lors de la documentation de la variable « PBX_EMPREINTE ».', 'openboutique_paybox_gateway');
						break;
					case '00030':
						$erreur_msg = __('Temps d\'attente > 15 mn par l\'internaute/acheteur au niveau de la page de paiements.', 'openboutique_paybox_gateway');
						break;
					case '00031':
					case '00032':
						$erreur_msg = __('Réservé', 'openboutique_paybox_gateway');
						break;
					case '00033':
						$erreur_msg = __('Code pays de l\'adresse IP du navigateur de l\'acheteur non autorisé.', 'openboutique_paybox_gateway');
						break;
					// Nouveaux codes : 11/2013 (v6.1)
					case '00040':
						$erreur_msg = __('Opération sans authentification 3-DSecure, bloquée par le filtre', 'openboutique_paybox_gateway');
						break;
					case '99999':
						$erreur_msg = __('Opération en attente de validation par l\'emmetteur du moyen de paiement.', 'openboutique_paybox_gateway');
						break;
					default:
						if (substr($code_erreur, 0, 3) == '001')
							$erreur_msg = __('Paiement refusé par le centre d\'autorisation. En cas d\'autorisation de la transaction par le centre d\'autorisation de la banque, le code erreur \'00100\' sera en fait remplacé directement par \'00000\'.', 'openboutique_paybox_gateway');
						else
							$erreur_msg = __('Pas de message', 'openboutique_paybox_gateway');
						break;
				}
				return $erreur_msg;
			}
		} // Fin de la classe

		/*
		 * Ajout de la "gateway" Paybox à woocommerce
		 */
		function add_paybox_commerce_gateway($methods)
		{
			$methods[] = 'WC_Paybox';
			return $methods;
		}

		include_once('shortcode-openboutique-thankyou.php');

		add_shortcode(THANKS_SHORTCODE, 'get_openboutique_thankyou');
		add_filter('woocommerce_payment_gateways', 'add_paybox_commerce_gateway');
		add_action('init', 'woocommerce_paybox_check_response');
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
			global $woocommerce;
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
									$order->add_order_note('<p style="color:green"><b>'.__('vPaybox Return OK', 'openboutique_paybox_gateway').'</b></p><br/>' . $std_msg);
									$order->payment_complete();
									unset($woocommerce->session->order_awaiting_payment);
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