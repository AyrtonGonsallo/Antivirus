<?php
/**
 * Plugin Name: Paiement par mandat administratif 
 * Description: Méthode de paiement par mandat administratif pour les administrations
 * Version: 1.1.0
 * Author: Ayrton
 */

// Vérifier que WooCommerce est actif
////Payment gateways should be created as additional plugins that hook into WooCommerce. Inside the plugin, you need to create a class after plugins are loaded
add_action('plugins_loaded', 'init_paiement_mandat_administratif_gateway');



function init_paiement_mandat_administratif_gateway() {
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    
    // Définir la classe de la gateway
    class WC_Gateway_Paiement_Mandat_Administratif extends WC_Payment_Gateway {
        
        public function __construct() {
            
            $this->id = 'paiement_mandat_administratif';
            $this->icon = '';
            $this->has_fields = false;
            $this->method_title = 'Paiement Par Mandat Administratif';
            $this->method_description = 'Méthode de paiement par mandat administratif pour les administrations';
            $this->supports = array(
                'products',
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

            // Charger les settings
            $this->init_form_fields();
            $this->init_settings();

            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            error_log('title = ' . $this->get_option('title'));
            error_log('enabled = ' . $this->get_option('enabled'));

            // Actions
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

            if (class_exists('WC_Subscriptions_Order')) {
                add_action('woocommerce_scheduled_subscription_payment_' . $this->id, array($this, 'scheduled_subscription_payment'), 10, 2);
                add_action('wcs_resubscribe_order_created', array($this, 'delete_resubscribe_meta'), 10);
                add_action('wcs_renewal_order_created', array($this, 'delete_renewal_meta'), 10);
            }
        }

        /**
         * Champs de configuration admin
         */
        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title'   => 'Activer/Désactiver',
                    'type'    => 'checkbox',
                    'label'   => 'Activer le paiement par mandat administratif',
                    'default' => 'yes'
                ),
                'title' => array(
                    'title'       => 'Titre',
                    'type'        => 'text',
                    'description' => 'Le titre que l\'utilisateur voit lors du checkout',
                    'default'     => 'Paiement par mandat administratif',
                    'desc_tip'    => true,
                ),
                'description' => array(
                    'title'       => 'Description',
                    'type'        => 'textarea',
                    'description' => 'Description de la méthode de paiement',
                    'default'     => 'Réservé aux administrations. Paiement à effectuer par mandat administratif.',
                ),
            );
        }

        /**
         * Vérifier si la méthode est disponible
         */
        public function is_available() {

            
            // Vérifier que la gateway est activée
            if ('yes' !== $this->enabled) {
                return false;
            }
            error_log('passerelle mandat administratif cond 1 :  disponible');

            // Vérifier si l'utilisateur est connecté
            if (!is_user_logged_in()) {
                return false;
            }
            

            $user_id = get_current_user_id();
            $user_info = get_userdata($user_id);
            $user_roles = $user_info->roles;
            error_log('passerelle mandat administratif cond 2 : user '.$user_id.' connecte');

            // Vérifier si l'utilisateur est revendeur  est association ou instit
            $type_client = get_user_meta($user_id, 'type_client', true);
            if (!(in_array('customer_revendeur', $user_roles) || $type_client=="association_ou_institution")) {
                return false;
            }
            error_log('passerelle mandat administratif cond 3 : user revendeur ou association institution');  

            
                  
            error_log('check paiement mandat administratif pour user '.$user_id.' reussi');

            return true;
        }

        /**
         * Traiter le paiement
         */
        public function process_payment($order_id) {

            error_log('debut traitement paiement mandat administratif commande ');
            
            $order = wc_get_order($order_id);

            // Vérifier si c'est une commande d'abonnement
            $is_subscription = false;
            if (function_exists('wcs_order_contains_subscription')) {
                $is_subscription = wcs_order_contains_subscription($order_id);
            }
            error_log('debut traitement paiement par mandat administratif commande '.$order_id.' is_subscription '.$is_subscription);
           

            // Mettre la commande en statut "En attente"
            $order->update_status('pending', __('Paiement par mandat administratif - En attente du règlement.', 'woocommerce'));

            // Note appropriée selon le type de commande
            if ($is_subscription) {
                $order->add_order_note('Paiement par mandat administratif activé pour cet abonnement administration. Paiements attendus plus tard.');
            } else {
                $order->add_order_note('Paiement par mandat administratif activé pour cette administration. Paiement attendu attendu plus tard.');
            }

            // Ajouter des métadonnées personnalisées
            $order->update_meta_data('_paiement_mandat_administratif', 'yes');
            $order->update_meta_data('_paiement_mandat_administratif_date', date('Y-m-d H:i:s'));
            $order->update_meta_data('_user_id_administration', get_current_user_id());
            
            if ($is_subscription) {
                $order->update_meta_data('_is_subscription_order', 'yes');
            }
            
            $order->save();

            // ✅ IMPORTANT : Pour les abonnements, marquer le paiement comme complet manuellement
            if ($is_subscription) {
                // Ceci permet d'activer l'abonnement même si le paiement est en attente
                //$order->payment_complete();
            }

            // Vider le panier
            WC()->cart->empty_cart();

            return array(
                'result'   => 'success',
                'redirect' => $this->get_return_url($order)
            );
        }

        /**
         * ✅ AJOUT : Traiter les paiements récurrents d'abonnement
         * Cette fonction est appelée automatiquement par WooCommerce Subscriptions
         */
        public function scheduled_subscription_payment($amount_to_charge, $renewal_order) {
            error_log('Paiement récurrent déclenché pour la commande #' . $renewal_order->get_id());
            
            // Mettre la commande de renouvellement en attente
            $renewal_order->update_status('pending', sprintf(
                __('Paiement par mandat administratif récurrent de %s - En attente du règlement de fin de mois.', 'woocommerce'),
                wc_price($amount_to_charge)
            ));

            $renewal_order->add_order_note('Paiement récurrent en attente pour cet abonnement revendeur.');
            
            // Ajouter les métadonnées
            $renewal_order->update_meta_data('_paiement_mandat_administratif', 'yes');
            $renewal_order->update_meta_data('_paiement_mandat_administratif_date', date('Y-m-d H:i:s'));
            $renewal_order->update_meta_data('_is_renewal_order', 'yes');
            $renewal_order->save();
        }

        /**
         * ✅ AJOUT : Nettoyer les métadonnées pour les ré-abonnements
         */
        public function delete_resubscribe_meta($resubscribe_order) {
            delete_post_meta($resubscribe_order->get_id(), '_paiement_mandat_administratif');
        }

        /**
         * ✅ AJOUT : Nettoyer les métadonnées pour les renouvellements
         */
        public function delete_renewal_meta($renewal_order) {
            delete_post_meta($renewal_order->get_id(), '_paiement_mandat_administratif_date');
        }
    }

    /**
     * Ajouter la gateway à WooCommerce tell WooCommerce (WC) that it exists
     */
    function add_paiement_mandat_administratif_gateway($methods) {
        $methods[] = 'WC_Gateway_Paiement_Mandat_Administratif';
        return $methods;
    }
    add_filter('woocommerce_payment_gateways', 'add_paiement_mandat_administratif_gateway');


}

