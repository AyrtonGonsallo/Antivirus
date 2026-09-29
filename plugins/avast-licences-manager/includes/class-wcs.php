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

        register_rest_route( 'alm/v1', '/cron_subscriptions', [
            'methods'  => 'GET',
            'callback' => [ __CLASS__, 'endpoint_cron_subscriptions' ],
            'permission_callback' => '__return_true', // ⚠️ debug only
        ]);
    }

    public static function endpoint_subscriptions() {

        //$subscriptions = wcs_get_subscriptions(['subscriptions_per_page' => -1]);
        
        $subscriptions = wcs_get_subscriptions([
            'subscriptions_per_page' => -1,
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key'     => '_cron_execute',
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key'     => '_cron_execute',
                    'value'   => '2',
                    'compare' => '!=',
                ],
            ],
        ]);
        

/*
        $subscription_ids = [
            52929, 52933, 52945, 52949, 52956, 62165, 52974, 52983, 62175, 53000, 53009, 53049, 53051, 53058, 53060, 53083, 53087, 62189, 53112, 53116, 53158, 53210, 53252, 53265, 53269, 53277, 53283, 53287, 53291, 62216, 53299, 53303, 53305, 53315, 53326, 62222, 53340, 62228, 62234, 53372, 53381, 53383, 62245, 53420, 53425, 53454, 53461, 53464, 53468, 53470, 53479, 53492, 53505, 53509, 53511, 53526, 53533, 53535, 53542, 62268, 62270, 53559, 53562, 62272, 53582, 53591, 53600, 62284, 53613, 62286, 53617, 53619, 53621, 62292, 53636, 53644, 53657, 62299, 53663, 53665, 53682, 53687, 53695, 53703, 53715, 62308, 53729, 53735, 53742, 53748, 53750, 62312, 62314, 53785, 53813, 53854, 53877, 53881, 53901, 62340, 62342, 53943, 53965, 53978, 53980, 53982, 53986, 53988, 53996, 54004, 54006, 54008, 54012, 54025, 62350, 54055, 54063, 54081, 54099, 54112, 54120, 54162, 54164, 54166, 54212, 54226, 62377, 62383, 54252, 54262, 54266, 54280, 54301, 62392, 54311, 54327, 54331, 54335, 54358, 62397, 62399, 54382, 54384, 54402, 54412, 54420, 54428, 54442, 54444, 54446, 54448, 54452, 54454, 54486, 54488, 54502, 62413, 54526, 54547, 54561, 62418, 54571, 54573, 62420, 62422, 54595, 54597, 54606, 54610, 62428, 62430, 54650, 54675, 54677, 54681, 54693, 62437, 54710, 62443, 54749, 54751, 54796, 62462, 54811, 54825, 54841, 54851, 54854, 54872, 54891, 54893, 62474, 54907, 54910, 54919, 55021, 55048, 55210, 55215, 55298, 55313, 55318, 55330, 55368, 55439, 55445, 55453, 55483, 55520, 62527, 62533, 55560, 55568, 62538, 55602, 55696, 55726, 55733, 55743, 55810, 55872, 55924, 55972, 56061, 56069, 56077, 56223, 56234, 56253, 56291, 56389, 56464, 56471, 56535, 62653, 56631, 56688, 56690, 62676, 62678, 62680, 56787, 56845, 56849, 56881, 56949, 57011, 57018, 57036, 57048, 57056, 62699, 57180, 57184, 62705, 57202, 57212, 57228, 57302, 57316, 57318, 57641, 57671, 57701, 57725, 57741, 57743, 57749, 57789, 57795, 57801, 57923, 57925, 57927, 57941, 57951, 57965, 57997, 58019, 58155, 62757, 58171, 58173, 58175, 58203, 58205, 62761, 58227, 58249, 58357, 58375, 58411, 58417, 58419, 58555, 58635, 58673, 58679, 58701, 58703, 58709, 62821, 58725, 58727, 58737, 58791, 58835, 58849, 58865, 58915, 58925, 58958, 58974, 58978, 59011, 59067, 59105, 59115, 59157, 59163, 59221, 59239, 59327, 59539, 59569, 59575, 59595, 59631, 62881, 62883, 59724, 59744, 59764, 59770, 59774, 59785, 59817, 59821, 59847, 59849, 59875, 59879, 59951, 59953, 59969, 60043, 62903, 60077, 60088, 60131, 60155, 60157, 60159, 60177, 60203, 60205, 60239, 60251, 60259, 60319, 60366, 60452, 60460, 60472, 60498, 60580, 60582, 60592, 60622, 60624, 60626, 60638, 60656, 60698, 60700, 60706, 60714, 60726, 60776, 60868, 60882, 60898, 60980, 61000, 61004, 61020, 62979, 61042, 61054, 61058, 61250, 61268, 61378, 61566, 61574, 61590, 61676, 61774, 61780, 61846, 62006, 62098, 61038, 63035, 61904, 62054, 63057
        ];
        $subscriptions = wcs_get_subscriptions([
            'subscriptions_per_page' => -1,
            'post__in'               => $subscription_ids,
            
        ]);
        */

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
                        // 2. commerciale (supprimer)
                        // -----------------------------
                        if (strpos($fee_name_lower, 'remise commerciale') !== false) {
                            echo "possede commerciale mais on va le retirer\n";
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
                }else {

                    echo "Remises / Fees:\n";
                    $has_renewal = false;

                    
                    echo "A renouvellement ? : " . $has_renewal . "\n";

                    // =====================================================
                    // DETERMINER LE TAUX FINAL (PRIORITÉ)
                    // =====================================================
                    
                    $rate = 0.30;
                    $label_suffix = "-30%";

                   
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





    public static function endpoint_cron_subscriptions() {

        //$subscriptions = wcs_get_subscriptions(['subscriptions_per_page' => -1]);
        $subscriptions = wcs_get_subscriptions([
            'subscriptions_per_page' => -1,
            
        ]);
        $sans_sauvegarder = isset($_GET['sauvegarder'])?false:true;

        // Buffer pour capturer tout l'affichage
        ob_start();
        
        echo '<pre>';
        if($sans_sauvegarder){
            echo "Sauvegarde désactivée\n";
        }else{
            echo "Sauvegarde activée\n";
        }
        

        // Loop through subscriptions protected objects
        foreach ( $subscriptions as $subscription ) {
            
            // Unprotected data in an accessible array
            $data = $subscription->get_data();

        
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
                if(!$sans_sauvegarder){
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
                        if($sans_sauvegarder){
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
                        // 2. commerciale (supprimer)
                        // -----------------------------
                        if (strpos($fee_name_lower, 'remise commerciale') !== false) {
                            echo "possede commerciale mais on va le retirer\n";
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
                    if(!$sans_sauvegarder){
                        $subscription->set_requires_manual_renewal(true);
                        $subscription->save();
                        $subscription->update_meta_data('_cron_execute', 1);
                        $subscription->calculate_totals(true);
                        $subscription->save();
                    }
                }else {
                    echo "Remises / Fees:\n";
                    
                    echo "A renouvellement ? : " . $has_renewal . "\n";

                    // =====================================================
                    // DETERMINER LE TAUX FINAL (PRIORITÉ)
                    // =====================================================
                    
                    $rate = 0.30;
                    $label_suffix = "-30%";

                 

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
                    if(!$sans_sauvegarder){
                        $subscription->set_requires_manual_renewal(true);
                        $subscription->save();
                        $subscription->update_meta_data('_cron_execute', 1);
                        $subscription->calculate_totals(true);
                        $subscription->save();
                    }
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
        $subject = 'Récapitulatif du cron abonnments antivirus - ' . date('Y-m-d H:i:s');
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