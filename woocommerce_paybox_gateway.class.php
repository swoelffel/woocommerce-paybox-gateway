<?php

/*
 * Paybox Commerce Gateway Class
 */

class WC_Paybox extends WC_Payment_Gateway {

    function __construct() {
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
        // Ajout des Hooks
        add_action('woocommerce_update_options_payment_gateways', array(&$this, 'process_admin_options'));
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }

    /*
     * Admin tools.
     */

    public function admin_options() {
        echo '<h3>' . __('OpenBoutique PayBox Gateway', 'openboutique_paybox_gateway') . '</h3>
				<div id="wc-ob-pbx-admin">
					<div id="ob-paybox_baseline">
						' . __('PayBox Gateway is an', 'openboutique_paybox_gateway') . ' <a href="http://www.openboutique.fr/?wcpbx=' . OB_VERSION . '" target="_blank">OpenBoutique</a> ' . __('technology', 'openboutique_paybox_gateway') . '
                                                ' . __('Need payment by installments, subscriptions, Paybox Direct ... <a href="http://openboutique.fr/contact/?wcpbx=' . OB_VERSION . '" target="_blank">Contact us</a>', 'openboutique_paybox_gateway') . ' 
                                        </div>
				<div id="ob-paybox_help_main1">';
        wp_enqueue_style('custom_openboutique_paybox_css', PLUGIN_DIR . '/css/style.css', false, OB_VERSION);
        wp_enqueue_script('custom_openboutique_paybox_js', PLUGIN_DIR . '/js/script.js', false, OB_VERSION);
        $install_url = '';
        if (!get_option('woocommerce_pbx_order_received_page_id') || !get_page(get_option('woocommerce_pbx_order_received_page_id')))
            $install_url .= '&install_pbx_received_page=true';
        if (!get_option('woocommerce_pbx_order_refused_page_id') || !get_page(get_option('woocommerce_pbx_order_refused_page_id')))
            $install_url .= '&install_pbx_refused_page=true';
        if (!get_option('woocommerce_pbx_order_canceled_page_id') || !get_page(get_option('woocommerce_pbx_order_canceled_page_id')))
            $install_url .= '&install_pbx_canceled_page=true';

        if ($install_url != '' && !isset($_GET['install_pbx_received_page']) && !isset($_GET['install_pbx_refused_page']) && !isset($_GET['install_pbx_canceled_page'])) {
            echo
            '<p>' .
            __('We have detected that Paybox return pages are not currently installed on your system', 'openboutique_paybox_gateway') . '<br/>' . __('Press the install button to prevent 404 from users whom transaction would have been received, canceled or refused.', 'openboutique_paybox_gateway') .
            '</p>
					<p>
						<a class="button" target="_self" href="./admin.php?page=wc-settings&tab=checkout&section=wc_paybox' . $install_url . '">' . __('Install return pages', 'openboutique_paybox_gateway') . '</a>
					</p>';
        } else {
            echo
            '<p>' .
            __('Paybox return pages are installed', 'openboutique_paybox_gateway') . ' : 
						<a target="_self" href="./post.php?post=' . get_option('woocommerce_pbx_order_received_page_id') . '&action=edit">' . __('received', 'openboutique_paybox_gateway') . '</a> | 
						<a target="_self" href="./post.php?post=' . get_option('woocommerce_pbx_order_canceled_page_id') . '&action=edit">' . __('canceled', 'openboutique_paybox_gateway') . '</a> | 
						<a target="_self" href="./post.php?post=' . get_option('woocommerce_pbx_order_refused_page_id') . '&action=edit">' . __('refused', 'openboutique_paybox_gateway') . '</a>
					</p>';
        }
        echo ('
					</div>
					<div id="ob-paybox_help_main2">
						<p>
							<a class="button-primary" id="ob-paybox_show_help" href="#">
								' . __('Need help ?', 'openboutique_paybox_gateway') . '
							</a>');
        if (empty($this->customer_id) || ($this->customer_id == '-')) {
            echo ('&nbsp;You not have a valid cutomer ID : Thanks to buy ticket <a href="http://shop.openboutique.fr/boutique/assistance/ticket-de-support-openboutique-forfait-heure/?wcpbx=' . OB_VERSION . '">here</a> before requesting support.');
        } else {
            echo ('&nbsp; Please, be sure your Customer ID "#' . $this->customer_id . '" is valid before requesting support.');
        }
        echo ('					</p>
					</div>
					<div id="ob-paybox_help_div">
						<p>
							' . __('Press', 'openboutique_paybox_gateway') . ' "' . __('Send report', 'openboutique_paybox_gateway') . '" ' . __('button and fill your email in order to post your', 'openboutique_paybox_gateway') . ' <b>' . __('Paybox Gateway parameters', 'openboutique_paybox_gateway') . '</b> ' . __('to OpenBoutique support team', 'openboutique_paybox_gateway') . '<br/>
							' . __('Your email', 'openboutique_paybox_gateway') . ' : <input type="text" name="email" placeholder="' . __('Your email', 'openboutique_paybox_gateway') . '" /><br/>
							' . __('Your message', 'openboutique_paybox_gateway') . ' :<br/><textarea name="help_text" rows="4" cols="80"></textarea>
							<input type="hidden" name="website" value="' . $_SERVER['SERVER_NAME'] . '" />
							<input type="hidden" name="WCPBX_version" value="' . OB_VERSION . '" />
							<input type="hidden" name="woocommerce_pbx_order_received_page_id" value="' . get_option('woocommerce_pbx_order_received_page_id') . '" />
							<input type="hidden" name="woocommerce_pbx_order_refused_page_id" value="' . get_option('woocommerce_pbx_order_refused_page_id') . '" />
							<input type="hidden" name="woocommerce_pbx_order_canceled_page_id" value="' . get_option('woocommerce_pbx_order_canceled_page_id') . '" />
							<br/><a class="button" id="ob-paybox_send_report" href="#">' . __('Send report', 'openboutique_paybox_gateway') . '</a>
						</p>
						<iframe name="myOB_iframe" id="myOB_iframe" style="display: none"></iframe>
					</div>
				</div>
				<table class="form-table">');
        $this->generate_settings_html();
        echo '</table><!--/.form-table-->';

        // Page paiement reçu -> Shortcode
        if (!empty($_GET['install_pbx_received_page']) && !get_page(get_option('woocommerce_pbx_order_received_page_id')))
            $this->create_page(esc_sql('order-pbx-received'), 'woocommerce_pbx_order_received_page_id', __('Order PBX Received', 'openboutique_paybox_gateway'), '[' . THANKS_SHORTCODE . ']', woocommerce_get_page_id('checkout'));
        // Page paiement refusé -> A venir shortcode pour interpretation du code retour
        if (!empty($_GET['install_pbx_refused_page']) && !get_page(get_option('woocommerce_pbx_order_refused_page_id')))
            $this->create_page(esc_sql('order-pbx-refused'), 'woocommerce_pbx_order_refused_page_id', __('Order PBX Refused', 'openboutique_paybox_gateway'), __('Your order has been refused', 'openboutique_paybox_gateway'), woocommerce_get_page_id('checkout'));
        // Page paiement annulé par le client
        if (!empty($_GET['install_pbx_canceled_page']) && !get_page(get_option('woocommerce_pbx_order_canceled_page_id')))
            $this->create_page(esc_sql('order-pbx-canceled'), 'woocommerce_pbx_order_canceled_page_id', __('Order PBX Canceled', 'openboutique_paybox_gateway'), __('Your order has been cancelled', 'openboutique_paybox_gateway'), woocommerce_get_page_id('checkout'));
    }

    function create_page($slug, $option, $page_title = '', $page_content = '', $post_parent = 0) {
        global $wpdb;
        $option_value = get_option($option);
        if ($option_value > 0 && get_post($option_value))
            return;

        $page_found = $wpdb->get_var($wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE post_name = %s LIMIT 1;", $slug));
        if ($page_found && !$option_value) {
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

    function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', 'openboutique_paybox_gateway'),
                'type' => 'checkbox',
                'label' => __('Enable Paybox Payment', 'openboutique_paybox_gateway'),
                'default' => 'yes'
            ),
            'customer_id' => array(
                'title' => __('Customer ID', 'openboutique_paybox_gateway'),
                'type' => 'text',
                'description' => __('Your OpenBoutique cutomer ID (Mandatory for support request).', 'openboutique_paybox_gateway'),
                'default' => __('-', 'openboutique_paybox_gateway')
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
                'default' => '32'
            ),
            'paybox_wait_time' => array(
                'title' => __('Paybox Checkout waiting time', 'openboutique_paybox_gateway'),
                'type' => 'text',
                'description' => __('Time to wait before to redirect to Paybox gateway (in milliseconds).', 'openboutique_paybox_gateway'),
                'default' => '2000'
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
                'description' => __('Please enter callback link from PayBox when transaction succeed', 'openboutique_paybox_gateway') . ' (' . __('where you need to put the', 'openboutique_paybox_gateway') . ' [' . THANKS_SHORTCODE . ']' . __('shortcode', 'openboutique_paybox_gateway') . ')',
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
                'description' => __('Please enter the posting URL for paybox Form', 'openboutique_paybox_gateway') . '<br/>' . __('For testing', 'openboutique_paybox_gateway') . ' : https://preprod-tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi<br/>' . __('For production', 'openboutique_paybox_gateway') . ' : https://tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi',
                'default' => 'https://preprod-tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi'
            ),
            'prepost_message' => array(
                'title' => __('Customer Message', 'openboutique_paybox_gateway'),
                'type' => 'textarea',
                'description' => __('Message to the user before redirecting to PayBox.', 'openboutique_paybox_gateway'),
                'default' => __('You will be redirect to Paybox System payment gatway in a few seconds ... Please wait ...', 'openboutique_paybox_gateway')
            ),
            'paybox_key' => array(
                'title' => __('Paybox Key for HMAC (optional if you use CGI)', 'openboutique_paybox_gateway'),
                'type' => 'textarea',
                'description' => __('Please enter the private secret Key generated at PayBox Backoffice.', 'openboutique_paybox_gateway'),
                'default' => '0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF'
            ),
            'paybox_exe' => array(
                'title' => __('Complete path to PayBox CGI (optional if you use HMAC)', 'openboutique_paybox_gateway'),
                'type' => 'textarea',
                'description' => __('Location for Paybox executable', 'openboutique_paybox_gateway') . ' (http://www1.paybox.com/telechargement_focus.aspx?cat=3)',
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
    function process_payment($order_id) {
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
					t=setTimeout("launchPaybox()",' . ((isset($this->paybox_wait_time) && is_numeric($this->paybox_wait_time)) ? $this->paybox_wait_time : '3000') . ');
				</script>';
        wp_die($retour);
    }

    function getParamPaybox(WC_Order $order) {
        $exe = $this->paybox_exe;
        if (!empty($exe) && file_exists($exe)) {
            $param = 'PBX_MODE=4';  // Envoi en ligne de commande
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
            $param .= ' PBX_DEVISE=978';
            $param .= ' PBX_DEVISE=SHA512'; // SHA512 (à paramétriser avec hash_algos() qd j'ai 2 min)
            $param .= ' PBX_TIME=' . date('c');
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
                $param .= ' PBX_RETOUR=order:R;erreur:E;carte:C;numauto:A;numtrans:S;numabo:B;montantbanque:M;sign:K';
            else // Pour linux 
                $param .= ' PBX_RETOUR=order:R\\;erreur:E\\;carte:C\\;numauto:A\\;numtrans:S\\;numabo:B\\;montantbanque:M\\;sign:K';

            $param .= ' PBX_PORTEUR=' . $order->billing_email; //. $order->customer_user;
            //error_log($exe . ' ' . $param);
            $retour = shell_exec($exe . ' ' . $param);
            if ($retour != '')
                return str_replace('https://tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi', $this->paybox_url, $retour);
            return __('Permissions are not correctly set for file', 'openboutique_paybox_gateway') . ' ' . $exe;
        } else { // No CGI -> Let's try HMAC
            if (!empty($this->paybox_key)) { // Test à afiner ...
                $param = 'PBX_SITE=' . $this->paybox_site_id;
                $param .= '&PBX_RANG=' . $this->paybox_rang;
                $param .= '&PBX_IDENTIFIANT=' . $this->paybox_identifiant;
                $param .= '&PBX_TOTAL=' . 100 * $order->get_total();
                $param .= '&PBX_DEVISE=978';
                $param .= '&PBX_TYPEPAIEMENT=CARTE';
                $param .= '&PBX_TYPECARTE=CB';
                $param .= '&PBX_REPONDRE_A=http://' . str_replace('//', '/', $_SERVER['HTTP_HOST'] . '/' . $this->return_url);
                $param .= '&PBX_EFFECTUE=http://' . str_replace('//', '/', $_SERVER['HTTP_HOST'] . '/' . $this->callback_success_url);
                $param .= '&PBX_REFUSE=http://' . str_replace('//', '/', $_SERVER['HTTP_HOST'] . '/' . $this->callback_refused_url);
                $param .= '&PBX_ANNULE=http://' . str_replace('//', '/', $_SERVER['HTTP_HOST'] . '/' . $this->callback_cancel_url);
                $param .= '&PBX_CMD=' . $order->id;
                $param .= '&PBX_PORTEUR=' . $order->billing_email; //. $order->customer_user;
                $param .= '&PBX_RETOUR=order:R;erreur:E;carte:C;numauto:A;numtrans:S;numabo:B;montantbanque:M;sign:K';
                $param .= '&PBX_HASH=SHA512'; // SHA512 (à paramétriser avec hash_algos() qd j'ai 2 min)
                $param .= '&PBX_TIME=' . date('c');

                $binKey = pack("H*", $this->paybox_key);
                $hmac = strtoupper(hash_hmac('sha512', $param, $binKey));
                $retour = '<form method="POST" action="' . $this->paybox_url . '" name="PAYBOX"> 
                            <input type="hidden" name="PBX_SITE" value="' . $this->paybox_site_id . '">
                            <input type="hidden" name="PBX_RANG" value="' . $this->paybox_rang . '">
                            <input type="hidden" name="PBX_IDENTIFIANT" value="' . $this->paybox_identifiant . '">
                            <input type="hidden" name="PBX_TOTAL" value="' . (100 * $order->get_total()) . '">
                            <input type="hidden" name="PBX_DEVISE" value="978">
                            <input type="hidden" name="PBX_TYPEPAIEMENT" value="CARTE">
                            <input type="hidden" name="PBX_TYPECARTE" value="CB">
                            <input type="hidden" name="PBX_REPONDRE_A" value="http://' . str_replace('//', '/', $_SERVER['HTTP_HOST'] . '/' . $this->return_url) .'">
                            <input type="hidden" name="PBX_EFFECTUE" value="http://' . str_replace('//', '/', $_SERVER['HTTP_HOST'] . '/' . $this->callback_success_url) .'">
                            <input type="hidden" name="PBX_REFUSE" value="http://' . str_replace('//', '/', $_SERVER['HTTP_HOST'] . '/' . $this->callback_refused_url) .'">
                            <input type="hidden" name="PBX_ANNULE" value="http://' . str_replace('//', '/', $_SERVER['HTTP_HOST'] . '/' . $this->callback_cancel_url) .'">
                            <input type="hidden" name="PBX_CMD" value="' . $order->id . '">  
                            <input type="hidden" name="PBX_PORTEUR" value="' . $order->billing_email . '">
                            <input type="hidden" name="PBX_RETOUR" value="order:R;erreur:E;carte:C;numauto:A;numtrans:S;numabo:B;montantbanque:M;sign:K">
                            <input type="hidden" name="PBX_HASH" value="SHA512">
                            <input type="hidden" name="PBX_TIME" value="' . date('c') . '">
                            <input type="hidden" name="PBX_HMAC" value="'. $hmac. '">
                            <input type="submit" value="Envoyer">
                           </form>';
                return $retour;
            } else {
                return __('Paybox Key must be setup for HMAC', 'openboutique_paybox_gateway');
            }
        }
        return __('Paybox encrytpion can not be found', 'openboutique_paybox_gateway');
    }

    static function getRealIpAddr() {
        if (!empty($_SERVER['HTTP_CLIENT_IP']))    //check ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) //to check ip is pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else
            $ip = $_SERVER['REMOTE_ADDR'];
        return $ip;
    }

    static function getErreurMsg($code_erreur) {
        switch ($code_erreur) {
            case '00000':
                $erreur_msg = __('Opération réussie.', 'openboutique_paybox_gateway');
                break;
            case '00001':
                $erreur_msg = __('La connexion au centre d\'autorisation a échoué. Vous pouvez dans ce cas là effectuer les redirections des internautes vers le FQDN', 'openboutique_paybox_gateway') . ' tpeweb1.paybox.com.';
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

}

// Fin de la classe
?>