<?php
if ( ! defined( 'ABSPATH' ) ) exit;

use Automattic\WooCommerce\Client;

class ALM_Wcs {

    public function __construct() {

        add_action( 'rest_api_init', [ __CLASS__, 'register_routes' ] );

        
    }

    //url  https://test.antivirusedition.com/wp-json/alm/v1/subscriptions
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

        echo '<pre>';

        // Loop through subscriptions protected objects
        foreach ( $subscriptions as $subscription ) {
            if($subscription->get_id()!=13940){
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

            $has_renewal = false;
            $base_total  = (float) $subscription->get_subtotal();
            echo "base total avant: ".$base_total."\n";

            if (!empty($fees)) {
                echo "Remises / Fees:\n";
                foreach ($fees as $fee_id => $fee) {
                    echo "- " . $fee->get_name() . " : " . $fee->get_total() . "\n";

                    $fee_name = $fee->get_name();

                    // Supprimer la remise -25%
                    if ($fee_name === "Remise Changement -25%") {
                        echo "possede changement mais on va le retirer\n";
                        $subscription->remove_item($fee_id);
                    }

                    // Vérifier si déjà -30%
                    if ($fee_name === "Remise Renouvellement de licences -30%") {
                        echo "possede déja Renouvellement de licences \n";
                        $has_renewal = true;
                    }

                    // Si autre remise spécifique → on stoppe tout
                    if (
                        $fee_name === "Remise Établissements scolaires et associations -50%"
                    ) {
                        echo "possede remise Établissements scolaires et associations\n";
                        $base_total  += $fee->get_total();
                    }

                    if (
                        $fee_name === "Remise Administrations et mairies -30%" 
                    ) {
                        echo "possede remise Administrations et mairies\n";
                        $base_total  += $fee->get_total();
                    }

                    if (
                        $fee_name === "Remise revendeur - 25 %" 
                    ) {
                        echo "possede remise revendeur\n";
                        $base_total  += $fee->get_total();
                    }
                }
            }
            echo "A renouvellement ? : ".$has_renewal."\n";

            /**
             * Ajouter -30% si pas déjà présent
             */
            if (!$has_renewal) {
                echo "base total apres: ".$base_total."\n";

                $discount_amount = round($base_total * 0.30, 2);
                echo "discount_amount : ".$discount_amount."\n";

                $renewal_fee = new WC_Order_Item_Fee();
                $renewal_fee->set_name("Remise Renouvellement de licences -30%");
                $renewal_fee->set_amount(-$discount_amount);
                $renewal_fee->set_total(-$discount_amount);
                $renewal_fee->set_tax_status('none');

                $subscription->add_item($renewal_fee);
            }

            /**
             * Recalcul obligatoire
             */
            $subscription->calculate_totals(true);
            $subscription->save();



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