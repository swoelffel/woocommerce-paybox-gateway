<?php

/**
 * Plugin Name: WooCommerce Paybox Payment Gateway
 * Plugin URI: http://www.castelis.com/woocommerce/
 * Description: Gateway e-commerce pour Paybox.
 * Version: 0.2.2
 * Author: Castelis
 * Author URI: http://www.castelis.com/
 * License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * @package WordPress
 * @author Castelis
 * @since 0.1.0
 */
add_action('plugins_loaded', 'woocommerce_paybox_init', 0);

function woocommerce_paybox_init() {
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    };

    DEFINE('PLUGIN_DIR', plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__)) . '/');

    /*
     * Paybox Commerce Gateway Class
     */

    class WC_Paybox extends WC_Payment_Gateway {

        function __construct() {
            $this->id = 'paybox';
            $this->icon = PLUGIN_DIR . 'images/paybox.png';
            $this->has_fields = false;
            $this->method_title = __('PayBox', 'woocommerce');
            // Load the form fields
            $this->init_form_fields();
            // Load the settings.
            $this->init_settings();
            // Get setting values
            foreach ($this->settings as $key => $val)
                $this->$key = $val;
            // Logs
            if ($this->debug == 'yes')
                $this->log = $woocommerce->logger();

            // Ajout des Hooks
            add_action('woocommerce_update_options_payment_gateways', array(&$this, 'process_admin_options'));
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ));
            add_action('woocommerce_thankyou_paybox', array(&$this, 'thankyou_page'));
        }

        /**
         * Reponse Paybox (Pour le serveur Paybox)
         *
         * @access public
         * @return void
         */
        function check_response() {
            // Code move to woocommerce_paybox_check_response
            // Instance WC_Paybox not load at init with version 2.0
        }

        /**
         * Retour Paybox
         *
         * @access public
         * @param array $posted
         * @return void
         */
        function thankyou_page($posted) {
            global $woocommerce;
            //error_log('thankyou_page');
            // Pour le moment on ne fait rien
        }

        /*
         * Initialize Gateway Settings Form Fields.
         */

        function init_form_fields() {

            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Enable/Disable', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Enable Paybox Payment', 'woocommerce'),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => __('Title', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
                    'default' => __('Paybox Payment', 'woocommerce')
                ),
                'description' => array(
                    'title' => __('Customer Message', 'woocommerce'),
                    'type' => 'textarea',
                    'description' => __('Let the customer know the payee and where they should be sending the Paybox to and that their order won\'t be shipping until you receive it.', 'woocommerce'),
                    'default' => __('Credit card payment by PayBox.', 'woocommerce')
                ),
                'paybox_site_id' => array(
                    'title' => __('Site ID Paybox', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Please enter you ID Site provided by PayBox.', 'woocommerce'),
                    'default' => ''
                ),
                'paybox_identifiant' => array(
                    'title' => __('Paybox ID', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Please enter you Paybox ID provided by PayBox.', 'woocommerce'),
                    'default' => ''
                ),
                'paybox_rang' => array(
                    'title' => __('Paybox Rank', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Please enter Paybox Rank provided by PayBox.', 'woocommerce'),
                    'default' => ''
                ),
                'paybox_key' => array(
                    'title' => __('Paybox Key', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Please enter Paybox Key provided by PayBox.', 'woocommerce'),
                    'default' => ''
                ),
                'return_url' => array(
                    'title' => __('Paybox return URL', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Please enter the autoreponse URL for PayBox.', 'woocommerce'),
                    'default' => '/paybox_autoresponse'
                ),
                'callback_success_url' => array(
                    'title' => __('Successful Return Link', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Please enter callback link from PayBox when transaction succeed (where you need to put the [im4woo_thankyou] shortcode).', 'woocommerce'),
                    'default' => '/checkout/order-received/'
                ),
                'callback_refused_url' => array(
                    'title' => __('Failed Return Link', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Please enter callback link from PayBox when transaction is refused by gateway.', 'woocommerce'),
                    'default' => '/checkout/order-refused/'
                ),
                'callback_cancel_url' => array(
                    'title' => __('Cancel Return Link', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Please enter back link from PayBox when enduser cancel transaction.', 'woocommerce'),
                    'default' => '/checkout/order-canceled/'
                ),
                'paybox_url' => array(
                    'title' => __('Paybox URL', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Please enter the posting URL for paybox Form <br/>For testing : https://preprod-tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi<br/>For production : https://tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi', 'woocommerce'),
                    'default' => 'https://tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi'
                ),
                'prepost_message' => array(
                    'title' => __('Customer Message', 'woocommerce'),
                    'type' => 'textarea',
                    'description' => __('Message to the user before redirecting to PayBox.', 'woocommerce'),
                    'default' => __('You will be redirect to Paybox System payment gatway in a few seconds ... Please wait ...', 'woocommerce')
                ),
                'paybox_exe' => array(
                    'title' => __('Complete path to PayBox CGI', 'woocommerce'),
                    'type' => 'textarea',
                    'description' => __('Location for Paybox executable (http://www1.paybox.com/telechargement_focus.aspx?cat=3).', 'woocommerce'),
                    'default' => __('/the/path/to/paybox.cgi', 'woocommerce')
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
                    function launchPaybox() {
                        document.PAYBOX.submit();
                    }
                    t=setTimeout("launchPaybox()",5000);
                </script>
                ';
            wp_die($retour);
        }

        function getParamPaybox(WC_Order $order) {
            $param = '';
            $param .= 'PBX_MODE=4'; //envoi en ligne de commande
            $param .= ' PBX_OUTPUT=B';

            $param .= ' PBX_SITE=' . $this->paybox_site_id;
            $param .= ' PBX_IDENTIFIANT=' . $this->paybox_identifiant;
            $param .= ' PBX_RANG=' . $this->paybox_rang;
            $param .= ' PBX_TOTAL=' . 100 * $order->get_total();
            $param .= ' PBX_CMD=' . $order->id;
            $param .= ' PBX_CMD=' . $order->paybox_url;
            //$param .= ' PBX_CLE=' . $this->paybox_key;
            $param .= ' PBX_REPONDRE_A=http://' . str_replace('//', '/', $_SERVER['HTTP_HOST'] . '/' . $this->return_url);
            $param .= ' PBX_EFFECTUE=http://' . str_replace('//', '/', $_SERVER['HTTP_HOST'] . '/' . $this->callback_success_url);
            $param .= ' PBX_REFUSE=http://' . str_replace('//', '/', $_SERVER['HTTP_HOST'] . '/' . $this->callback_refused_url);
            $param .= ' PBX_ANNULE=http://' . str_replace('//', '/', $_SERVER['HTTP_HOST'] . '/' . $this->callback_cancel_url);
            $param .= ' PBX_DEVISE=978'; // Euro (à paramétriser)
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $param .= ' PBX_RETOUR=order:R;erreur:E;carte:C;numauto:A;numtrans:S;numabo:B;montantbanque:M;sign:K';
            } else { //pour linux 
                $param .= ' PBX_RETOUR=order:R\\;erreur:E\\;carte:C\\;numauto:A\\;numtrans:S\\;numabo:B\\;montantbanque:M\\;sign:K';
            }
            $email_address = $order->billing_email;
            if (empty($email_address) || !is_email($email_address)) {
                $ids = $wpdb->get_result("SELECT wp_users.ID FROM wp_users WHERE (SELECT wp_usermeta.meta_value FROM wp_usermeta WHERE wp_usermeta.user_id = wp_users.ID AND wp_usermeta.meta_key = 'wp_capabilities') LIKE '%administrator%'");
                if ($ids) {
                    $current_user = get_user_by('id', $ids[0]);
                    $email_address = $current_user->user_mail;
                }
            }
            $param .= ' PBX_PORTEUR=' . $email_address; //. $order->customer_user;
            $exe = $this->paybox_exe;
            if (file_exists($exe)) {
                error_log($exe . ' ' . $param);
                $retour = shell_exec($exe . ' ' . $param);
                if ($retour != '') {
                    return $retour;
                } else {
                    return _('Permissions are not correctly set for file ' . $exe);
                }
            } else {
                return _('Paybox CGI module can not be found');
            }
        }

        static function getRealIpAddr() {
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {   //check ip from share internet
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {   //to check ip is pass from proxy
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
            return $ip;
        }

        static function getErreurMsg($code_erreur) {
            $ErreurMsg = _('No Message');
            if ($code_erreur == '00000')
                $ErreurMsg = 'Opération réussie.';
            if ($code_erreur == '00011')
                $ErreurMsg = 'Montant incorrect.';
            if ($code_erreur == '00001')
                $ErreurMsg = 'La connexion au centre d\'autorisation a échoué. Vous pouvez dans ce cas là effectuer les redirections des internautes vers le FQDN tpeweb1.paybox.com.';
            if ($code_erreur == '00015')
                $ErreurMsg = 'Paiement déjà effectué.';
            if ($code_erreur == '001')
                $ErreurMsg = 'Paiement refusé par le centre d\'autorisation. En cas d\'autorisation de la transaction par le centre d\'autorisation de la banque, le code erreur \'00100\' sera en fait remplacé directement par \'00000\'.';
            if ($code_erreur == '00016')
                $ErreurMsg = 'Abonné déjà existant (inscription nouvel abonné). Valeur \'U\' de la variable PBX_RETOUR.';
            if ($code_erreur == '00003')
                $ErreurMsg = 'Erreur Paybox.';
            if ($code_erreur == '00021')
                $ErreurMsg = 'Carte non autorisée.';
            if ($code_erreur == '00004')
                $ErreurMsg = 'Numéro de porteur ou cryptogramme visuel invalide.';
            if ($code_erreur == '00029')
                $ErreurMsg = 'Carte non conforme. Code erreur renvoyé lors de la documentation de la variable « PBX_EMPREINTE ».';
            if ($code_erreur == '00006')
                $ErreurMsg = 'Accès refusé ou site/rang/identifiant incorrect.';
            if ($code_erreur == '00030')
                $ErreurMsg = 'Temps d\'attente > 15 mn par l\'internaute/acheteur au niveau de la page de paiements.';
            if ($code_erreur == '00008')
                $ErreurMsg = 'Date de fin de validité incorrecte.';
            if ($code_erreur == '00031')
                $ErreurMsg = 'Réservé';
            if ($code_erreur == '00009')
                $ErreurMsg = 'Erreur de création d\'un abonnement.';
            if ($code_erreur == '00032')
                $ErreurMsg = 'Réservé';
            if ($code_erreur == '00010')
                $ErreurMsg = 'Devise inconnue.';
            if ($code_erreur == '00033')
                $ErreurMsg = 'Code pays de l\'adresse IP du navigateur de l\'acheteur non autorisé.';
            return $ErreurMsg;
        }

    }

    // Fin de la classe
    /*
     * Ajout de la "gateway" Paybox à woocommerce
     */
    function add_paybox_commerce_gateway($methods) {
        $methods[] = 'WC_Paybox';
        return $methods;
    }

    include_once('shortcode-im4woo-thankyou.php');

    add_shortcode('im4woo_thankyou', 'get_im4woo_thankyou');
    add_filter('woocommerce_payment_gateways', 'add_paybox_commerce_gateway');
    add_action('init', 'woocommerce_paybox_check_response');
}

/**
 * Reponse Paybox (Pour le serveur Paybox)
 *
 * @access public
 * @return void
 */
function woocommerce_paybox_check_response() {
    if (isset($_GET['order']) && isset($_GET['sign'])) { // On a bien un retour ave une commande et une signature
        $order = new WC_Order((int) $_GET['order']); // On récupère la commande
        $pos_qs = strpos($_SERVER['REQUEST_URI'], '?');
        $pos_sign = strpos($_SERVER['REQUEST_URI'], '&sign=');
        $return_url = substr($_SERVER['REQUEST_URI'], 1, $pos_qs - 1);
        $data = substr($_SERVER['REQUEST_URI'], $pos_qs + 1, $pos_sign - $pos_qs - 1);
        $sign = substr($_SERVER['REQUEST_URI'], $pos_sign + 6);
        // Est-on en réception d'un retour PayBox
        $my_WC_Paybox = new WC_Paybox();
        if (str_replace('//', '/', '/' . $return_url) == str_replace('//', '/', $my_WC_Paybox->return_url)) {
            $std_msg = 'Paybox Return IP:' . WC_Paybox::getRealIpAddr() . '<br/>' . $data . '<br/><div style="word-wrap:break-word;">PBX Sign : ' . $sign . '<div>';
            @ob_clean();
            // Traitement du retour PayBox
            // PBX_RETOUR=order:R;erreur:E;carte:C;numauto:A;numtrans:S;numabo:B;montantbanque:M;sign:K
            if (isset($_GET['erreur'])) {
                switch ($_GET['erreur']) {
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
                        if ($verif_sign == 1) { // La commande est bien signé par PayBox
                            // Si montant ok
                            if ((int) (100 * $order->get_total()) == (int) $_GET['montantbanque']) {
                                $order->add_order_note('<p style="color:green"><b>Paybox Return OK</b></p><br/>' . $std_msg);
                                $order->payment_complete();
                                header('HTTP/1.1 200 OK');
                                wp_die('OK');
                            } else {
                                $order->add_order_note('<p style="color:red"><b>ERROR</b></p> Order Amount<br/>' . $std_msg);
                                header('HTTP/1.1 406');
                                wp_die('KO Amount modified : ' . $_GET['montantbanque'] . ' / ' . (100 * $order->get_total()));
                            }
                        } else {
                            $order->add_order_note('<p style="color:red"><b>ERROR</b></p> Signature Rejected<br/>' . $std_msg);
                            header('HTTP/1.1 406');
                            wp_die('KO Signature');
                        }
                        break;
                    default:
                        $order->add_order_note('<p style="color:red"><b>PBX ERROR ' . $_GET['erreur'] . '</b> ' . WC_Paybox::getErreurMsg($_GET['erreur']) . '</p><br/>' . $std_msg);
                        header('HTTP/1.1 200 OK');
                        wp_die('OK received');
                        break;
                }
            } else {
                header('HTTP/1.1 200 OK');
                wp_die('Test AutoResponse OK');
            }
        }
    }
}
