<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ALM_Remise_Commerciale {

    public function __construct() {

        // Lors du submit du formulaire → on crée la remise
        add_action('init', [$this, 'handle_demande_remise_creation']);
        add_action('woocommerce_cart_calculate_fees', [$this, 'apply_remises_and_tva_to_user'],5);

        /**
         * Désactive la TVA si l'utilisateur est revendeur HT.
         */
        add_filter( 'woocommerce_tax_rate_class', [$this, 'get_correct_tax_class'], 10, 2 );

        
    }

    /**
     * Sauvegarde des champs quand l'utilisateur soumet le formulaire
     */
    public function handle_demande_remise_creation() {

        if ( isset($_POST['submit_demande_remise']) && is_user_logged_in() ) {
            $user_id = get_current_user_id();

            // Tableau pour associer option → input file
            $file_fields_map = [
                "Changement -25%" => 'justificatif_changement',
                "Renouvellement de licences -30%"        => 'justificatif_text_renouvellement|justificatif_file_renouvellement',
                "Administrations et mairies -30%"       => 'justificatif_admin',
                "Établissements scolaires et associations -50%" => 'justificatif_association'
            ];

            $percentage_fields_map = [
                "Changement -25%" => 25,
                "Renouvellement de licences -30%"        => 30,
                "Administrations et mairies -30%"       => 30,
                "Établissements scolaires et associations -50%" => 50
            ];

            if ( !empty($_POST['remise_type']) ) {

                // Split sur la virgule pour récupérer chaque option
                $options = array_map('trim', explode(',', $_POST['remise_type']));

                foreach ($options as $option) {

                    // 1️⃣ Créer la remise CPT
                    $remise_id = wp_insert_post([
                        'post_type'   => 'remise',
                        'post_title'  => "Demande de remise : $option - Utilisateur $user_id",
                        'post_status' => 'publish',
                        'post_author' => $user_id,
                    ]);

                    if ( is_wp_error($remise_id) ) continue;

                    // 2️⃣ Champs ACF
                    update_field('utilisateur', $user_id, $remise_id);
                    update_field('compte', [$user_id], $remise_id);
                    update_field('statut', 'validee', $remise_id);
                    update_field('pourcentage', $percentage_fields_map[$option], $remise_id);
                    update_field('date_de_creation', current_time('d/m/Y g:i a'), $remise_id);
                    $expiration = date('Y-m-d H:i:s', strtotime('+1 month'));
                    update_field('date_dexpiration', $expiration, $remise_id);

                    // 3️⃣ Upload du fichier correspondant
                    $fields  = $file_fields_map[$option] ?? '';
                    $fieldnames = explode('|', $fields); // permet de gérer plusieurs champs
                    foreach ($fieldnames as $fieldname) {
                        // Champ texte
                        if (isset($_POST[$fieldname]) && !empty($_POST[$fieldname])) {
                            update_field("justificatif_texte", sanitize_text_field($_POST[$fieldname]), $remise_id);
                        }

                        // Champ fichier
                        if (isset($_FILES[$fieldname]) && !empty($_FILES[$fieldname]['name'])) {
                            require_once(ABSPATH . 'wp-admin/includes/file.php');
                            require_once(ABSPATH . 'wp-admin/includes/media.php');
                            require_once(ABSPATH . 'wp-admin/includes/image.php');

                            $file_id = media_handle_upload($fieldname, $remise_id);
                            if (!is_wp_error($file_id)) {
                                update_field("justificatif", $file_id, $remise_id);
                            }
                        }
                    }
                    
                }

                wc_add_notice("Vos demandes de remise ont été envoyées avec succès.", "success");
            }
        }



    }

     private function get_user_remises($user_id) {
        $today = current_time('d/m/Y g:i a'); // même format que date_de_creation

        $args = [
            'post_type' => 'remise',
            'post_status' => 'publish',
            'numberposts' => -1,
            'meta_query' => [
                [
                    'key' => 'utilisateur',
                    'value' => $user_id,
                    'compare' => '='
                ],
                [
                    'key' => 'statut',
                    'value' => 'validee',
                    'compare' => '='
                ],
                [
                    'key' => 'date_dexpiration',
                    'value' => $today,
                    'compare' => '>=',
                    'type' => 'DATETIME'
                ]
            ]
        ];

        return get_posts($args);
    }

    /**
     * Appliquer les remises validées au panier
     */
    public function apply_remises_and_tva_to_user($cart) {
        if ( is_admin() && !defined('DOING_AJAX') ) return;

        $user_id = get_current_user_id();
       

        if($user_id){
             $remises = $this->get_user_remises($user_id);
            if (empty($remises)) return;

            $total_discount_percent = 0;

            foreach ($remises as $remise) {
                $percent = get_field('pourcentage', $remise);
                if ($percent) $total_discount_percent += floatval($percent);
            }

            if ($total_discount_percent > 0) {
                $cart_total = $cart->get_subtotal();
                $discount_amount = ($total_discount_percent / 100) * $cart_total;
                $cart->add_fee("Remises commerciales", -$discount_amount, false); // false = non taxable
            }

            $user = get_user_by( 'id', $user_id );

            if (  in_array( 'customer_revendeur', (array) $user->roles ) ) {
                // Vérifier le régime TVA stocké par ton formulaire
                $regime = get_user_meta( $user_id, 'new_revendeur_account_regime_tva', true );

                // Si le revendeur est en régime HT => pas de TVA
                if ( strtoupper( $regime ) === 'HT' ) {
                    $cart->remove_taxes(  );
                }
                
            }

            


        
        }else{
            return;
        }

    }



    public function get_correct_tax_class($class, $product) {

        // On récupère l'utilisateur connecté
        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            return 'Zero Rate'; // non connecté => normal
        }

        $user = get_user_by( 'id', $user_id );
        
        // Vérifier que c’est bien un revendeur
        if ( ! in_array( 'customer_revendeur', (array) $user->roles ) ) {
            return 'Zero Rate';
        }

        // Vérifier le régime TVA stocké par ton formulaire
        $regime = get_user_meta( $user_id, 'new_revendeur_account_regime_tva', true );

        // Si le revendeur est en régime HT => pas de TVA
        if ( strtoupper( $regime ) === 'HT' ) {
            $tax_class = 'taux-zero';
            return $tax_class;
        }

        // Sinon on laisse WooCommerce faire son job
        return 'Zero Rate';
    }
    



}
