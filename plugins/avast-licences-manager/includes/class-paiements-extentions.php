<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Étendre les gateways existantes pour supporter les abonnements
 * À ajouter dans votre fichier functions.php ou dans un plugin
 */


class ALM_Paiements_extentions {

    public function __construct() {

        // ce filtre est execute dans panier et checkout
        add_filter('woocommerce_payment_gateway_supports', [$this, 'add_subscription_support_to_gateways'], 10, 3);


    }


    //cette fonction etend les gateway moneticopaiement_cb_5467 bacs cheque
    function add_subscription_support_to_gateways($is_supported, $feature, $gateway) {
    
        // Liste des gateways à modifier
        $gateways_to_extend = array('cheque', 'bacs', 'moneticopaiement_cb_5467');
        
        // Log pour débuggage
        error_log(sprintf(
            'Gateway: %s | Feature: %s | Originally supported: %s',
            $gateway->id,
            $feature,
            $is_supported ? 'YES' : 'NO'
        ));
        
        // Vérifier si c'est une des gateways concernées
        if (!in_array($gateway->id, $gateways_to_extend)) {
            return $is_supported;
        }
        
        // Liste des fonctionnalités d'abonnement
        $subscription_features = array(
            'subscriptions',
            'subscription_cancellation',
            'subscription_suspension',
            'subscription_reactivation',
            'subscription_amount_changes',
            'subscription_date_changes',
            'subscription_payment_method_change',
            'subscription_payment_method_change_customer',
            'subscription_payment_method_change_admin',
            'multiple_subscriptions',
        );
        
        if (in_array($feature, $subscription_features)) {
            error_log(sprintf(' Forcing support for %s on gateway %s', $feature, $gateway->id));
            return true;
        }
        
        return $is_supported;
    }



}
