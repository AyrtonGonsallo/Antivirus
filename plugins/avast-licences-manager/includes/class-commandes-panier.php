<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ALM_Commandes_Panier {

    public function __construct() {

         // Lors du UPDATE CART → on ajoute les données
       add_action( 'woocommerce_update_cart_action_cart_updated', [$this, 'sauver_champs_perso_panier'] );

        // Réafficher dans le panier
        add_filter( 'woocommerce_get_item_data', [$this, 'afficher_donnees_panier'], 10, 2 );

        // Sauvegarde dans la commande lors du checkout
       add_action( 'woocommerce_checkout_create_order_line_item', [$this, 'sauver_donnees_dans_commande'], 10, 4 );

       
       // add_action( 'woocommerce_update_cart_action_cart_updated', [$this, 'save_custom_cart_item_data']);


    }


    function alm_select_variation($product_id, $duration = null, $pc = null) {
        $product = wc_get_product($product_id);
        if (!$product || !$product->is_type('variable')) return false;

        foreach ($product->get_children() as $variation_id) {
            $variation = wc_get_product($variation_id);
            if (!$variation) continue;

            $attr = $variation->get_attributes();

            $match = true;

            // comparer seulement si l'attribut existe pour le produit
            if ($duration !== null && isset($attr['pa_software_duration'])) {
                if ($attr['pa_software_duration'] != $duration) {
                    $match = false;
                }
            }

            if ($pc !== null && isset($attr['pa_number_of_computers'])) {
                if ($attr['pa_number_of_computers'] != $pc) {
                    $match = false;
                }
            }

            if ($match) return $variation_id;
        }

        return false;
    }

    function sauver_champs_perso_panier() {

        foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

            $duration = isset($_POST['alm_Software_duration'][$cart_item_key]) ? sanitize_text_field($_POST['alm_Software_duration'][$cart_item_key]) : null;
            $pc       = isset($_POST['alm_Number_of_computers'][$cart_item_key]) ? sanitize_text_field($_POST['alm_Number_of_computers'][$cart_item_key]) : null;

            if ($duration === null && $pc === null) continue; // rien à faire

            $product_id = $cart_item['product_id'];

            // retrouver la variation correspondant aux attributs fournis
            $variation_id = $this->alm_select_variation($product_id, $duration, $pc);

            if ($variation_id) {
                $qty = $cart_item['quantity'];

                // conserver les méta si besoin
                $cart_item_data = [];
                if (!empty($cart_item['alm_client'])) $cart_item_data['alm_client'] = $cart_item['alm_client'];

                WC()->cart->remove_cart_item($cart_item_key);

                WC()->cart->add_to_cart(
                    $product_id,
                    $qty,
                    $variation_id,
                    array_filter([
                        'attribute_pa_software_duration' => $duration,
                        'attribute_pa_number_of_computers' => $pc
                    ]),
                    $cart_item_data
                );
            }

            
            // Client perso
            if ( isset($_POST['alm_client'][$cart_item_key]) ) {
                $client_id = intval($_POST['alm_client'][$cart_item_key]);
                WC()->cart->cart_contents[$cart_item_key]['alm_client'] = $client_id;
            }
            
        }

        WC()->cart->set_session();
    }



    /**
     * Affichage dans le panier sous le nom du produit
     */
    function afficher_donnees_panier( $item_data, $cart_item ) {

         // ATTRIBUTS DE VARIATION
    if ( isset( $cart_item['variation'] ) && ! empty( $cart_item['variation'] ) ) {

        foreach ( $cart_item['variation'] as $attr_name => $attr_value ) {

            // on enlève le prefixe pa_ 
            $clean_name = str_replace('attribute_pa_', '', $attr_name);

            // On récupère le nom réel de l'attribut
            $taxonomy = get_taxonomy( $clean_name );
            $label = $taxonomy ? $taxonomy->labels->singular_name : ucfirst($clean_name);

            // On récupère le terme (pas le slug)
            $term = get_term_by('slug', $attr_value, $clean_name);
            $display_value = $term ? $term->name : $attr_value;

            // Ajout dans l’affichage panier
            $item_data[] = [
                'key'   => $label,
                'value' => $display_value,
            ];
        }
    }
/*
        if ( isset($cart_item['alm_duree']) ) {
            $item_data[] = [
                'key'   => 'Durée',
                'value' => $cart_item['alm_duree'] . " an(s)"
            ];
        }
            */
        /*
        if ( isset($cart_item['alm_quantity2']) ) {
            $item_data[] = [
                'key'   => 'nombre de pc',
                'value' => $cart_item['alm_quantity2'] . " PC"
            ];
        }*/

        if ( isset($cart_item['alm_client']) ) {
            $client = get_user_by('id', $cart_item['alm_client']);
            if ($client) {
                $item_data[] = [
                    'key'   => 'Client',
                    'value' => $client->display_name
                ];
            }
        }

        return $item_data;
    }

    /**
     * Sauvegarde dans la commande WooCommerce
     */
    function sauver_donnees_dans_commande($item, $cart_item_key, $values, $order) {

        if ( isset( $values['alm_duree'] ) ) {
            $item->add_meta_data('Durée', $values['alm_duree']);
        }

        if ( isset( $values['alm_client'] ) ) {
            $client = get_user_by('id', $values['alm_client']);
            if ($client) {
                $item->add_meta_data('Client', $client->display_name);
            } else {
                $item->add_meta_data('Client', $values['alm_client']);
            }
        }
    }


}
