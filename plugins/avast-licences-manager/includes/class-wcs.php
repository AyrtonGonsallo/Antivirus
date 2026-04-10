<?php
if ( ! defined( 'ABSPATH' ) ) exit;

use Automattic\WooCommerce\Client;

class ALM_Wcs {

    public function __construct() {

        add_action( 'rest_api_init', [ __CLASS__, 'register_routes' ] );

        
    }

    //url  https://test.antivirusedition.com/wp-json/alm/v1/subscriptions?test=1&subs_id=30771
    //lien doc  https://wpdavies.dev/how-to-get-woocommerce-subscription-info/


    public static function register_routes() {

        register_rest_route( 'alm/v1', '/subscriptions', [
            'methods'  => 'GET',
            'callback' => [ __CLASS__, 'endpoint_subscriptions' ],
            'permission_callback' => '__return_true', // ⚠️ debug only
        ]);
    }

    public static function endpoint_subscriptions() {

        $subscriptions = wcs_get_subscriptions(['subscriptions_per_page' => -1]);
        $test = isset($_GET['test'])?true:false;
        $subs_id = isset($_GET['subs_id'])?intval($_GET['subs_id']):30772;

        echo '<pre>';
        if($test){
            echo "Sauvegarde désactivée\n";
        }else{
            echo "Sauvegarde activée\n";
        }
        

        // Loop through subscriptions protected objects
        foreach ( $subscriptions as $subscription ) {
            if($subscription->get_id()!= $subs_id){
                continue;
            }
            // Unprotected data in an accessible array
            $data = $subscription->get_data();

            //$subscription->set_requires_manual_renewal(true);
            //$subscription->save();

           



            echo "Subscription #" . $subscription->get_id() . " - " . $subscription->get_user_id() . "\n";

            // ID client
            echo "Customer ID: " . $subscription->get_customer_id() . "\n";

            // Total
            echo "Total: " . $subscription->get_total() . " " . $subscription->get_currency() . "\n";

            // Méthode de payment / renewal
            echo "Payment method: " . $subscription->get_payment_method() . " (" . $subscription->get_payment_method_title() . ")\n";
            echo "Requires manual renewal: " . ($subscription->get_requires_manual_renewal() ? 'Yes' : 'No') . "\n";

            // -----------------------
            // Remises (fees négatifs)
            // -----------------------

         
            $fees = $subscription->get_fees();
            $cron_execute  = $subscription->get_meta('_cron_execute');
            if ($cron_execute==2) {

                echo "Cron déjà exécuté deux fois affichage\n";

                $base_total = (float) $subscription->get_subtotal();
                $current_base_total = $base_total;
                echo "base total avant: ".$base_total."\n";

                $total_discount = 0;

                foreach ($fees as $fee_id => $fee) {

                    $name = $fee->get_name();

                    if (preg_match('/(\d+)\s*%/', $name, $matches)) {

                        $percent = (int) $matches[1];


                        // calcul sur base initiale
                        $current_discount = round($current_base_total * $percent / 100, 2);
                        $current_base_total-=$current_discount;
                        // cumul global
                        $total_discount += $current_discount;

                        echo "$percent % dans $name donne -$current_discount\n";
                    }
                }

                echo "Total des remises: $total_discount\n";

              
            }else if ($cron_execute==1) {

                echo "Cron déjà exécuté une fois recalcul des pourcentages\n";

                $base_total = (float) $subscription->get_subtotal();
                $current_base_total = $base_total;
                echo "base total avant: ".$base_total."\n";

                $total_discount = 0;

                foreach ($fees as $fee_id => $fee) {

                    $name = $fee->get_name();

                    if (preg_match('/(\d+)\s*%/', $name, $matches)) {

                        $percent = (int) $matches[1];


                        // calcul sur base initiale
                        $current_discount = round($current_base_total * $percent / 100, 2);
                        $current_base_total-=$current_discount;
                        // cumul global
                        $total_discount += $current_discount;

                        // appliquer à chaque fee (en négatif)
                        $fee->set_total(-$current_discount);
                        $fee->set_amount(-$current_discount);
                        echo "$percent % dans $name donne -$current_discount\n";
                    }
                }

                echo "Total des remises: $total_discount\n";

                if(!$test){
                    // recalcul WooCommerce
                    $subscription->update_meta_data('_cron_execute', 2);
                    $subscription->calculate_totals(true);
                    $subscription->save();
                }
            }else{

                echo "Cron non execute\n";
                $has_renewal = false;
                $base_total  = (float) $subscription->get_subtotal();
                echo "base total avant: ".$base_total."\n";
                $existing_renewal = null;
                
                $has_renewal = false;
                $has_gouv = false;
                $has_edu = false;

                if (!empty($fees)) {

                    echo "Remises / Fees:\n";

                    foreach ($fees as $fee_id => $fee) {

                        $fee_name = $fee->get_name();
                        $fee_name_lower = mb_strtolower($fee_name, 'UTF-8');

                        echo "- " . $fee_name . " : " . $fee->get_total() . "\n";
                        if($test){
                            echo "- fee_name_lower : " .$fee_name_lower. "\n";
                        }
                        

                        // -----------------------------
                        // 1. Revendeur
                        // -----------------------------
                        if (strpos($fee_name_lower, 'remise revendeur') !== false) {
                            echo "possede remise revendeur\n";
                            $base_total += $fee->get_total();
                            continue;
                        }

                        // -----------------------------
                        // 2. Changement (supprimer)
                        // -----------------------------
                        if (strpos($fee_name_lower, 'remise changement') !== false) {
                            echo "possede changement mais on va le retirer\n";
                            $subscription->remove_item($fee_id);
                            continue;
                        }

                        // -----------------------------
                        // 3. Déjà renouvellement
                        // -----------------------------
                        if (strpos($fee_name_lower, 'remise renouvellement de licences') !== false) {
                            echo "possede déjà renouvellement\n";
                            $existing_renewal = $fee;
                            $has_renewal = true;
                            continue;
                        }

                        // -----------------------------
                        // 4. Cas spéciaux
                        // -----------------------------

                        if (strpos($fee_name_lower, 'établissements scolaires') !== false) {
                            echo "possede remise Établissements scolaires mais on va le retirer\n";

                            $base_total += $fee->get_total();
                            $subscription->remove_item($fee_id);

                            $has_edu = true;
                            continue;
                        }

                        if (strpos($fee_name_lower, 'administrations et mairies') !== false) {
                            echo "possede remise Administrations mais on va le retirer\n";
                            $base_total += $fee->get_total();
                            $subscription->remove_item($fee_id);

                            $has_gouv = true;
                            continue;
                        }

                        if (strpos($fee_name_lower, 'autre remise') !== false) {
                            echo "possede autre remise mais on va le retirer\n";

                            $base_total += $fee->get_total();
                            $subscription->remove_item($fee_id);

                            continue;
                        }
                    }

                    echo "A renouvellement ? : " . $has_renewal . "\n";

                    // =====================================================
                    // DETERMINER LE TAUX FINAL (PRIORITÉ)
                    // =====================================================
                    
                    $rate = 0.30;
                    $label_suffix = "-30%";

                    if ($has_edu) {
                        echo "Taux retenu EDU -60%\n";
                        $rate = 0.60;
                        $label_suffix = "EDU -60%";
                    } elseif ($has_gouv) {
                        echo "Taux retenu GOUV -50%\n";
                        $rate = 0.50;
                        $label_suffix = "GOUV -50%";
                    }

                    echo "Taux retenu : " . $rate . "\n";

                    $discount_amount = round($base_total * $rate, 2);

                    echo "base total apres: " . $base_total . "\n";
                    echo "discount_amount : " . $discount_amount . "\n";

                    // =====================================================
                    // CAS 1 : PAS DE RENOUVELLEMENT
                    // =====================================================
                    if (!$has_renewal) {

                        $renewal_fee = new WC_Order_Item_Fee();
                        $renewal_fee->set_name("Remise Renouvellement de licences " . $label_suffix);
                        $renewal_fee->set_amount(-$discount_amount);
                        $renewal_fee->set_total(-$discount_amount);
                        $renewal_fee->set_tax_status('none');

                        $subscription->add_item($renewal_fee);

                    }
                    // =====================================================
                    // CAS 2 : EXISTE DEJA → ON MET À JOUR
                    // =====================================================
                    elseif ($existing_renewal &&  ($has_edu || $has_gouv) ) {

                        echo "mise à jour de la remise existante\n";

                        $existing_renewal->set_name("Remise Renouvellement de licences " . $label_suffix);
                        $existing_renewal->set_amount(-$discount_amount);
                        $existing_renewal->set_total(-$discount_amount);
                        $existing_renewal->set_tax_status('none');
                    }

                    // =====================================================
                    // RECALCUL
                    // =====================================================
                    if(!$test){
                        $subscription->update_meta_data('_cron_execute', 1);
                        $subscription->calculate_totals(true);
                        $subscription->save();
                        
                    }
                }

            }
            
            



            // -----------------------
            // Coupons
            // -----------------------
            $coupons = $subscription->get_coupons();
            if (!empty($coupons)) {
                echo "Coupons:\n";
                foreach ($coupons as $coupon) {
                    echo "- " . $coupon->get_code() . " : " . $coupon->get_discount() . "\n";
                }
            } else {
                echo "Coupons: None\n";
            }

            echo "---------------------------\n";
        }

        echo '</pre>';

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