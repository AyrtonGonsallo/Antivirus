<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ALM_Remise_Commerciale {

    public function __construct() {

        // Lors du submit du formulaire → on crée la remise
        add_action('init', [$this, 'handle_demande_remise_creation']);
        add_action('woocommerce_cart_calculate_fees', [$this, 'apply_remises_and_tva_to_user'],5);
        add_action( 'woocommerce_before_calculate_totals', [$this, 'force_regular_price_if_user_has_remise'],  5  );

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

                $userdata = get_userdata( $user_id );
                $prenom = get_user_meta($user_id, 'first_name', true);
                $nom = get_user_meta($user_id, 'last_name', true);
                $email =  $userdata->user_email;
                $civilite = get_user_meta($user_id, 'civilite', true); 
                $lien_commandes = site_url()."/mon-compte/orders/";

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
                    update_field('type', $option, $remise_id);
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

                    $percent = $percentage_fields_map[$option];
                    $subject = 'Votre remise statutaire Avast est approuvée';

                    $message = '
                    <html>
                    <body style="font-family: Arial, sans-serif; line-height:1.6; color:#333;">
                        <p>Bonjour '.$civilite.' '.$nom.' '.$prenom.', </p>

                        <p>
                            Nous avons le plaisir de vous informer que la remise statutaire applicable à votre compte Avast a été validée par notre service commercial.
                        </p>

                        <p>
                            Vous pouvez dès à présent passer vos commandes en ligne en bénéficiant d’une remise de <strong>' . esc_html($percent) . '%</strong> sur l’ensemble des produits Avast.
                        </p>

                        <p style="text-align:center;">
                            <a href="' . esc_url($lien_commandes) . '" target="_blank" style="
                                display:inline-block;
                                margin:20px 0;
                                padding:12px 25px;
                                background:#FF7800;
                                color:#ffffff;
                                text-decoration:none;
                                text-transform:uppercase;
                                font-size:16px;
                                border-radius:6px;
                            ">Commander mon logiciel avast</a>
                        </p>

                        <p>
                            Si vous rencontrez une quelconque difficulté pour commander votre logiciel Avast, n’hésitez pas à nous en faire part en répondant à cet email.
                            Nous vous répondrons dans les plus brefs délais.
                        </p>

                        <p>
                            Merci pour votre confiance et à très bientôt.<br>
                            <strong>L’équipe Avast</strong>
                        </p>
                    </body>
                    </html>
                    ';

                    // Headers pour email HTML
                    $headers = array('Content-Type: text/html; charset=UTF-8');

                    wp_mail($email, $subject, $message, $headers);

                    
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


    public function force_regular_price_if_user_has_remise($cart) {

        if (is_admin() && !defined('DOING_AJAX')) return;
        if (!$cart || did_action('woocommerce_before_calculate_totals') >= 2) return;

        $user_id = get_current_user_id();
        if (!$user_id) return;

        $remises = $this->get_user_remises($user_id);
        if (empty($remises)) return; //  PAS DE REMISE → PRIX PROMO NORMAL

        foreach ($cart->get_cart() as $cart_item) {
            if ( isset($item['source_devis']) ) {
                continue;
            }
            $product = $cart_item['data'];

            $regular_price = (float) $product->get_regular_price();

            if ($regular_price > 0) {
                $product->set_price($regular_price); //  clé ici
            }
        }
    }

   

    /**
     * Appliquer les remises validées au panier
     */
    public function apply_remises_and_tva_to_user($cart) {

        if ( is_admin() && !defined('DOING_AJAX') ) return;

        if (!WC()->session) return;

        $remises = WC()->session->get('devis_remises');

        /*
        * ===============================
        * CAS 1 : Panier issu d’un devis
        * ===============================
        */
        if (!empty($remises)) {

            foreach ($remises as $remise) {

                $label   = $remise['label'];
                $montant = $remise['amount'];

                error_log("application de la remise $label = $montant");

                $cart->add_fee(
                    $label,
                    -$montant,
                    false
                );
            }

            // On supprime le flag et les remises pour les prochains ajouts normaux
            WC()->session->__unset('from_devis');
            WC()->session->__unset('devis_remises');

            return; // 🔥 on bloque les remises classiques
        }

        /*
        * ===============================
        * CAS 2 : Panier normal
        * ===============================
        */
        $user_id = get_current_user_id();
        if (!$user_id) return;

        $remises = $this->get_user_remises($user_id);
        if (empty($remises)) return;

        $base_total = $cart->get_subtotal();

        foreach ($remises as $remise) {

            $percent = (float) get_field('pourcentage', $remise);
            $type    = get_field('type', $remise);

            if ($percent > 0 && $base_total > 0) {

                $discount_amount = ($percent / 100) * $base_total;

                $cart->add_fee(
                    $type ?: __('Remise', 'woocommerce'),
                    -$discount_amount,
                    false
                );
            }
        }

        /*
        * ===============================
        * TVA revendeur
        * ===============================
        */
        $user = get_user_by('id', $user_id);

        if ($user && in_array('customer_revendeur', (array) $user->roles)) {

            $regime = get_user_meta($user_id, 'new_revendeur_account_regime_tva', true);

            if (strtoupper($regime) === 'HT') {
                $cart->remove_taxes();
            }
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
