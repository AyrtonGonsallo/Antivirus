<?php
if ( ! defined( 'ABSPATH' ) ) exit;

use Automattic\WooCommerce\Client;

class ALM_Wcs {

    public function __construct() {

        add_action( 'rest_api_init', [ __CLASS__, 'register_routes' ] );

        
    }


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
            // Unprotected data in an accessible array
            $data = $subscription->get_data();

            //$subscription->set_requires_manual_renewal(true);
            //$subscription->save();

            $renewal_coupon_code = 'renewal_discount'; // ton coupon à appliquer

            
                // Vérifie si le coupon est déjà appliqué
                $has_coupon = false;
                $coupons = $subscription->get_coupons();

                    
               


                foreach ( $coupons as $coupon_item ) {
                    if ( $coupon_item->get_code() === $renewal_coupon_code ) {
                        $has_coupon = true;
                        break;
                    }
                }

                // Si pas encore appliqué, ajoute le coupon
                if ( ! $has_coupon ) {
                    echo "Pas de coupon de renouvellement trouvé " . $renewal_coupon_code . "\n";
                    $subscription->apply_coupon( $renewal_coupon_code );
                    $subscription->calculate_totals(); // recalculer le total après l’ajout
                    $subscription->save();
                }
                
            


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
            if (!empty($fees)) {
                echo "Remises / Fees:\n";
                foreach ($fees as $fee) {
                    echo "- " . $fee->get_name() . " : " . $fee->get_total() . "\n";
                }
            } else {
                echo "Remises / Fees: None\n";
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


