<?php
if ( ! defined( 'ABSPATH' ) ) exit;

use Automattic\WooCommerce\Client;

class ALM_Expiration_renewal_cron {

    public function __construct() {

        add_action( 'rest_api_init', [ __CLASS__, 'register_routes' ] );

        
    }

    //url pour sauvegarder  https://test.antivirusedition.com/wp-json/alm/v1/cron_expiration_renouvellement?sauvegarder=1
    //url sans sauvegarder  https://test.antivirusedition.com/wp-json/alm/v1/cron_expiration_renouvellement


    public static function register_routes() {


        register_rest_route( 'alm/v1', '/cron_expiration_renouvellement', [
            'methods'  => 'GET',
            'callback' => [ __CLASS__, 'endpoint_cron_expiration_renouvellement' ],
            'permission_callback' => '__return_true', // ⚠️ debug only
        ]);
    }

    



    public static function endpoint_cron_expiration_renouvellement() {

        $subscriptions = wcs_get_subscriptions(array(
            'subscriptions_per_page' => -1,
            'subscription_status'    => array( 'on-hold'),
        ));

        $sans_sauvegarder = isset($_GET['sauvegarder'])?false:true;

        // Buffer pour capturer tout l'affichage
        ob_start();
        
        echo '<pre>';
        if($sans_sauvegarder){
            echo "Sauvegarde désactivée\n";
        }else{
            echo "Sauvegarde activée\n";
        }

        echo "Nombre d'abonnements trouvés : " . count($subscriptions) . "\n";
        echo "===========================\n";
        
        foreach ($subscriptions as $subscription_id => $subscription) {

            // 2. Récupérer les commandes de renouvellement de cet abonnement
            $renewal_orders = $subscription->get_related_orders('ids', 'renewal');

            if (empty($renewal_orders)) {
                continue;
            }

            // 3. Trouver la DERNIÈRE commande de renouvellement
            // get_related_orders retourne des IDs, on prend le plus récent
            $last_renewal_id = max($renewal_orders);
            $last_renewal    = wc_get_order($last_renewal_id);

            if (!$last_renewal) {
                continue;
            }

            $date_created = $last_renewal->get_date_created();

            // Formatage de la date
            $date = $date_created
                ? $date_created->date('Y-m-d H:i:s')
                : '';

            $dateCommande = new DateTime($date);
            $dateExpiration = clone $dateCommande;
            $dateExpiration->modify('+15 days');
            $dateExpirationString = $dateExpiration->format('Y-m-d H:i:s');

            $maintenant = new DateTime();

            $last_renewal_is_expired = false;
            $expiration_statut = 'derniere commande pas expirée';
            if ($maintenant > $dateExpiration) {
                $last_renewal_is_expired = true;
                $expiration_statut = 'derniere commande expirée';
            } 

            // 4. Récupérer la date de fin de l'abonnement
            $end_date     = $subscription->get_date('end');       // date de fin programmée
            $next_payment = $subscription->get_date('next_payment');
            $status       = $subscription->get_status();
            $renewal_status = $last_renewal->get_status();

            // Formater les dates
            $end_date_fmt     = $end_date     ? date('Y-m-d H:i:s', strtotime($end_date))     : 'Aucune';
            $next_payment_fmt = $next_payment ? date('Y-m-d H:i:s', strtotime($next_payment)) : 'Aucun';

            echo "Abonnement #$subscription_id | Statut : $status\n";
            echo "  → Prochain paiement       : $next_payment_fmt\n";
            echo "  → Dernière commande renouvellement : #$last_renewal_id | Statut : $renewal_status\n";
            echo "  → Date de la commande : $date\n";
            echo "  → Date d'expiration de la commande : $dateExpirationString\n";
            echo "  → Etat de la commande : $expiration_statut\n";

            // 5. Vérifier si la dernière commande de renouvellement est expirée/échouée
           
            if ($last_renewal_is_expired) {
                echo "  ⚠️  Denière commande de renouvellement expiré + abonnement en attente → ACTION REQUISE\n";

                if (!$sans_sauvegarder) {
                    // Supprimer la dernière commande de renouvellement
                    $last_renewal->delete(true); // true = suppression définitive
                    echo "  ✅ Commande #$last_renewal_id supprimée\n";

                    // Terminer/annuler l'abonnement si pas déjà fait
                    if (!in_array($status, array('expired', 'cancelled', 'trash'))) {
                        $subscription->update_status('cancelled', 'Annulé automatiquement par cron expiration renouvellement.');
                        echo "  ✅ Abonnement #$subscription_id annulé\n";
                    }
                } else {
                    echo "  ℹ️  (simulation) Commande #$last_renewal_id aurait été supprimée\n";
                    echo "  ℹ️  (simulation) Abonnement #$subscription_id aurait été annulé\n";
                }
            }

            echo "---------------------------\n";
        }

       
        echo '</pre>';

        // Récupérer le contenu du buffer
        $output = ob_get_clean();
        
        // Afficher à l'écran (pour le cron)
        echo $output;
        
        // Envoyer par email
        $to = 'ayrtongonsallo444@gmail.com'; // Remplacez par votre email
        $subject = 'Récapitulatif du cron expiration des renouvellments antivirus - ' . date('Y-m-d H:i:s');
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        // Formater le contenu pour l'email (conserver le format pre)
        $email_content = '<html><body>' . nl2br(htmlspecialchars($output)) . '</body></html>';
        
        wp_mail($to, $subject, $email_content, $headers);

        exit;

    }
    
    


}



/**
 *
 * 	Available Methods from WC_Subscriptions object
 * 
 *
 **/

// Subscription Meta
/*
$subscription->get_id(); // Subscription ID
$subscription->get_parent_id(); // Order ID of the original order when subscription was placed
$subscription->get_type();
$subscription->get_status();
$subscription->get_date( string $date, string $timezone ); // This is a useful function for fetching dates, Accepts: 'start', 'trial_end', 'next_payment', 'last_payment' or 'end'
$subscription->get_date_paid();
$subscription->get_date_completed();
$subscription->get_date_created();
$subscription->get_date_modified();
$subscription->get_currency();
$subscription->get_created_via();
$subscription->get_customer_note();
$subscription->get_recorded_sales();
$subscription->get_customer_order_notes();
$subscription->get_items();
$subscription->get_item_count();
$subscription->get_download_url();
$subscription->get_downloadable_items();
$subscription->get_shipping_methods();

// URLs
$subscription->get_checkout_payment_url();
$subscription->get_checkout_order_received_url();
$subscription->get_cancel_order_url();
$subscription->get_cancel_order_url_raw();
$subscription->get_cancel_endpoint();
$subscription->get_edit_order_url();

// Subscription Payment Info
$subscription->get_billing_period();
$subscription->get_billing_interval();
$subscription->get_trial_period();
$subscription->get_payment_count();
$subscription->get_failed_payment_count();
$subscription->get_total_initial_payment();
$subscription->get_suspension_count();
$subscription->get_requires_manual_renewal();
$subscription->get_switch_data();
$subscription->get_sign_up_fee();
$subscription->get_payment_method();
$subscription->get_payment_method_title();

// Subscription Helper Functions
$subscription->get_related_orders();
$subscription->get_last_order();
$subscription->get_payment_method_to_display();
$subscription->get_view_order_url();
$subscription->get_items_sign_up_fee();
$subscription->get_item_downloads();
$subscription->get_change_payment_method_url();
$subscription->get_payment_method_meta();
$subscription->get_completed_payment_count();

// Customer Data
$subscription->get_customer_id();
$subscription->get_user_id();
$subscription->get_user();
$subscription->get_billing_first_name();
$subscription->get_billing_last_name();
$subscription->get_billing_company();
$subscription->get_billing_address_1();
$subscription->get_billing_address_2();
$subscription->get_billing_city();
$subscription->get_billing_state();
$subscription->get_billing_postcode();
$subscription->get_billing_country();
$subscription->get_billing_email();
$subscription->get_billing_phone();
$subscription->get_shipping_first_name();
$subscription->get_shipping_last_name();
$subscription->get_shipping_company();
$subscription->get_shipping_address_1();
$subscription->get_shipping_address_2();
$subscription->get_shipping_city();
$subscription->get_shipping_state();
$subscription->get_shipping_postcode();
$subscription->get_shipping_country();
$subscription->get_shipping_phone();
$subscription->get_customer_ip_address();
$subscription->get_customer_user_agent();
$subscription->get_shipping_address_map_url();
$subscription->get_formatted_billing_full_name();
$subscription->get_formatted_shipping_full_name();
$subscription->get_formatted_billing_address();
$subscription->get_formatted_shipping_address();

// Sales Data
$subscription->get_total();
$subscription->get_total_tax();
$subscription->get_total_discount();
$subscription->get_subtotal();
$subscription->get_tax_totals();
$subscription->get_discount_total();
$subscription->get_discount_tax();
$subscription->get_shipping_total();
$subscription->get_shipping_tax();
$subscription->get_fees();
$subscription->get_total_fees();
$subscription->get_taxes();

// Refund Data
$subscription->get_total_tax_refunded();
$subscription->get_total_shipping_refunded();
$subscription->get_item_count_refunded();
$subscription->get_total_qty_refunded();
$subscription->get_refunds();
$subscription->get_total_refunded();
$subscription->get_qty_refunded_for_item();
$subscription->get_total_refunded_for_item();
$subscription->get_tax_refunded_for_item();

// Coupon Data
$subscription->get_coupon_codes();
$subscription->get_recorded_coupon_usage_counts();
$subscription->get_coupons();
$subscription->get_used_coupons();

apply_coupon

*/