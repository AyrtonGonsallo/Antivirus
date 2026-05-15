<?php
/**
 * Plugin Name: Paiement Différé Revendeurs
 * Description: Méthode de paiement en fin de mois pour les revendeurs
 * Version: 1.0
 */

// Vérifier que WooCommerce est actif
////Payment gateways should be created as additional plugins that hook into WooCommerce. Inside the plugin, you need to create a class after plugins are loaded
add_action('plugins_loaded', 'init_paiement_differe_gateway');



function init_paiement_differe_gateway() {
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    
    // Définir la classe de la gateway
    class WC_Gateway_Paiement_Differe extends WC_Payment_Gateway {
        
        public function __construct() {
            
            $this->id = 'paiement_differe';
            $this->icon = '';
            $this->has_fields = false;
            $this->method_title = 'Paiement Différé';
            $this->method_description = 'Paiement en fin de mois pour les revendeurs';
            $this->supports = [
                'products'
            ];

            // Charger les settings
            $this->init_form_fields();
            $this->init_settings();

            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            error_log('title = ' . $this->get_option('title'));
            error_log('enabled = ' . $this->get_option('enabled'));

            // Actions
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }

        /**
         * Champs de configuration admin
         */
        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title'   => 'Activer/Désactiver',
                    'type'    => 'checkbox',
                    'label'   => 'Activer le paiement différé',
                    'default' => 'yes'
                ),
                'title' => array(
                    'title'       => 'Titre',
                    'type'        => 'text',
                    'description' => 'Le titre que l\'utilisateur voit lors du checkout',
                    'default'     => 'Paiement en fin de mois',
                    'desc_tip'    => true,
                ),
                'description' => array(
                    'title'       => 'Description',
                    'type'        => 'textarea',
                    'description' => 'Description de la méthode de paiement',
                    'default'     => 'Réservé aux revendeurs. Paiement à effectuer en fin de mois.',
                ),
            );
        }

        /**
         * Vérifier si la méthode est disponible
         */
        public function is_available() {

            error_log('check is available');
            // Vérifier que la gateway est activée
            if ('yes' !== $this->enabled) {
                return false;
            }

            // Vérifier si l'utilisateur est connecté
            if (!is_user_logged_in()) {
                return false;
            }

            $user_id = get_current_user_id();
            $user_info = get_userdata($user_id);
            $user_roles = $user_info->roles;

            // Vérifier si l'utilisateur est revendeur
            if (!in_array('customer_revendeur', $user_roles)) {
                return false;
            }

            // Vérifier si l'option est cochée dans le profil
            $paiement_fin_mois = get_user_meta($user_id, 'paiment_en_fin_de_mois', true);
            error_log('check paiement_fin_mois '.$paiement_fin_mois);
            if (!in_array($paiement_fin_mois, ['1', 1, 'yes', true], true)) {
                return false;
            }
            error_log('check paiement_fin_mois pour user '.$user_id.' reussi');

            return true;
        }

        /**
         * Traiter le paiement
         */
        public function process_payment($order_id) {
            $order = wc_get_order($order_id);

            // Mettre la commande en statut "En attente"
            $order->update_status('pending', __('Paiement différé - En attente du règlement de fin de mois.', 'woocommerce'));

            // Ajouter une note à la commande
            $order->add_order_note('Paiement différé activé pour ce revendeur. Paiement attendu en fin de mois.');

            // Ajouter des métadonnées personnalisées
            $order->update_meta_data('_paiement_differe', 'yes');
            $order->update_meta_data('_paiement_differe_date', date('Y-m-d H:i:s'));
            $order->update_meta_data('_user_id_revendeur', get_current_user_id());
            $order->save();

            // Vider le panier
            WC()->cart->empty_cart();

            // Rediriger vers la page de confirmation
            return array(
                'result'   => 'success',
                'redirect' => $this->get_return_url($order)
            );
        }
    }

    /**
     * Ajouter la gateway à WooCommerce tell WooCommerce (WC) that it exists
     */
    function add_paiement_differe_gateway($methods) {
        $methods[] = 'WC_Gateway_Paiement_Differe';
        return $methods;
    }
    add_filter('woocommerce_payment_gateways', 'add_paiement_differe_gateway');


}