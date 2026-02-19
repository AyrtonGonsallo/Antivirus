<?php
require_once __DIR__ . '/class-devis-pdf-generator.php';
require_once __DIR__ . '/class-devis-email-sender.php';

if ( ! defined( 'ABSPATH' ) ) exit;

class ALM_Devis {

    public function __construct() {

         // Lors du UPDATE CART → on ajoute les données
       // add_action('addify_rfq_after_update_quote_item', [$this, 'sauver_champs_perso_devis'], 10, 2);

        //add_filter( 'woocommerce_add_cart_item_data', [$this, 'alm_save_custom_fields_to_cart'], 5, 2);
        //add_action( 'woocommerce_add_to_cart',  [$this, 'alm_force_quantity_after_add_to_cart'], 10, 3);

        add_shortcode('alm_devis_form', [$this, 'alm_render_devis_form']);
        add_action('init', [$this, 'alm_handle_devis_form']);

        //convertir le devis en panier
        
        add_action( 'woocommerce_before_calculate_totals', function($cart) {

            if ( is_admin() && !defined('DOING_AJAX') ) 
                return;

            foreach ( $cart->get_cart() as $item ) {
                if ( isset($item['prix_force']) && isset($item['source_devis']) ) {
                    $item['data']->set_price( $item['prix_force'] );
                }
            }
        });
        

        // 1. Ajouter les colonnes
        add_filter( 'manage_devis-en-ligne_posts_columns', [$this, 'alm_devis_en_ligne_posts_columns'], 10, 1);
        add_action( 'manage_devis-en-ligne_posts_custom_column', [$this, 'alm_devis_en_ligne_posts_datas'], 10, 2);
         add_filter( 'manage_variation-devis_posts_columns', [$this, 'alm_variation_devis_posts_columns'], 10, 1);
        add_action( 'manage_variation-devis_posts_custom_column', [$this, 'alm_variation_devis_posts_datas'], 10, 2);
        add_action('admin_post_generer_pdf_devis', function() {

            if (!current_user_can('manage_options')) {
                wp_die("Permissions insuffisantes.");
            }

            if (!isset($_GET['id'])) {
                wp_die("ID manquant.");
            }

            $id = intval($_GET['id']);
            $variations  = get_field('variations', $id);

            foreach ( $variations as $variation ) : 
                // Générer le PDF et l'enregistrer dans le champ ACF de chqaue variation
                DevisPDFGenerator::generate_pdf($id,$variation->ID);
            endforeach; 

            // Rediriger avec un message
            wp_safe_redirect(admin_url("edit.php?post_type=devis-en-ligne&pdf=ok"));
            exit;
        });
        add_action('admin_post_envoyer_mail_devis', [$this, 'envoyer_email_avec_devis']);

      

        add_action('add_meta_boxes', [$this, 'meta_boxex_variation_details']);

        add_action('admin_post_calculer_variation_total', [$this, 'calculer_variation_total']);




    }

    function meta_boxex_variation_details(){
        add_meta_box(
            'alm_calcul_variation',
            'Calcul personnalisé',
            [$this, 'alm_render_calcul_total_devis_variation_button'],
            'variation-devis',
            'side',
            'low'
        );
    }


    function alm_render_calcul_total_devis_variation_button($post) {

        $url = wp_nonce_url(
            admin_url('admin-post.php?action=calculer_variation_total&post_id=' . $post->ID),
            'calcul_variation_' . $post->ID
        );

        echo '<a href="'.$url.'" class="button button-primary" style="width:100%; text-align:center;">
                Calculer le total
            </a>';
    }

    public function calculer_variation_total() {

        if (!current_user_can('edit_posts')) {
            wp_die('Permission refusée');
        }

        if (!isset($_GET['post_id'])) {
            wp_die('ID manquant');
        }

        $post_id = intval($_GET['post_id']);

        check_admin_referer('calcul_variation_' . $post_id);

        // 🔥 On lance ton calcul
        $this->alm_calcul_variation($post_id);

        // 🔄 Redirection propre vers la page édition
        wp_safe_redirect(
            admin_url('post.php?post=' . $post_id . '&action=edit&calcul=ok')
        );

        exit;
    }


    function alm_calcul_variation($post_id)
    {
        $formule = "";
        $formule_html = '<table class="produits" style="border-spacing: 0px;">
                    <thead>
                        <tr>
                            <th style="border:1px solid #ccc;padding:5px;;">Désignation</th>
                            <th style="border:1px solid #ccc;padding:5px;;">Quantité</th>
                            <th style="border:1px solid #ccc;padding:5px;;">Durée</th>
                            <th style="border:1px solid #ccc;padding:5px;;">PU HT</th>
                            <th style="border:1px solid #ccc;padding:5px;;">Total HT</th>
                        </tr>
                    </thead>
                    <tbody>';
        $sous_total_ht = 0;
        $total_ht = 0;

        $rows = get_field('produits_de_la_variation', $post_id);

        if (!$rows) {
            return [
                'formule' => "Aucun produit\n",
                'ht'      => 0,
                'ttc'     => 0
            ];
        }

        /**
         * 1️⃣ Calcul total HT initial
         */
        foreach ($rows as $row) {

            $product_id = $row['produit'];
            if (is_array($product_id) && isset($product_id[0])) {
                $produit_post = $product_id[0];
                $product_id = $produit_post->ID;
                $product_obj = wc_get_product($product_id);

                if ($product_obj) {

                    $product_name = $product_obj->get_name();
                    $product_price = $product_obj->get_price();
                    $product_img = wp_get_attachment_image_src( $product_obj->get_image_id(), 'thumbnail' );
                    $product_img_url = $product_img ? $product_img[0] : '';
                    $duree = $row['duree'];
                     $nom = '
                    <div class= style="white-space:nowrap;">
                        <img src="'.esc_url($product_img_url).'" width="50" height="50" style="vertical-align:middle; border-radius:4px; margin-right:10px;">
                        <a href="'.get_permalink($product_id).'" class="product-link"><span style="vertical-align:middle;">'.wp_kses_post($product_name).'</span></a>
                    </div>';
                }
            }
            $quantite   = (float) $row['quantite'];

            
            $prix = (float) ($row['prix_propose'])?$row['prix_propose']:0;

            $ligne_total = $prix * $quantite;

           // $formule .= "{$prix} x {$quantite} = {$ligne_total}\n";
           $formule .= "Total HT : {$sous_total_ht}\n";
           $formule_html .="
            <tr>
                <td style='display:flex;align-items:center;gap:10px;border:1px solid #ccc;padding:5px;;'>
                $nom
                </td>
                <td style='border:1px solid #ccc;padding:5px;;'>$quantite</td>
                <td style='border:1px solid #ccc;padding:5px;;'>$duree</td>
                <td style='border:1px solid #ccc;padding:5px;;'>".$prix." €</td>
                <td style='border:1px solid #ccc;padding:5px;;'>$ligne_total €</td>
            </tr>";

            $sous_total_ht += $ligne_total;
        }
        $total_ht =$sous_total_ht;

        $formule .= "Total HT : {$sous_total_ht}\n";

        $formule_html .= '<tr>';
        $formule_html .= '<td colspan="4" style="text-align:right;border:1px solid #ccc;padding:5px;"> Total HT</td>';
        $formule_html .= '<td style="border:1px solid #ccc;padding:5px;">'.number_format($sous_total_ht, 2, ',', ' ').' €</td>';
        $formule_html .= '</tr>';
        

        /**
         * 2️⃣ Application des remises successives
         */
        $remise_fields = [
            'remise_renewal',
            'remise_cumulee',
            'remise_statutaire',
            'remise_commerciale',
            'remise_revendeur',
            'remise_administration_mairie',
            'remise_etalissements',
            'remise_changer_avast'
        ];

        $remise_fields_map = [
            "remise_changer_avast" => 'Remise changement',
            "remise_renewal"        => 'Remise renouvellement de licences',
            "remise_administration_mairie"       => 'Remise administrations et mairies',
            "remise_etalissements" => 'Remise établissements scolaires et associations',
            "remise_cumulee" => 'Autre remise',
            "remise_statutaire" => 'Autre remise',
            "remise_commerciale" => 'Autre remise',
            "remise_revendeur" => 'Remise revendeur',
        ];

        foreach ($remise_fields as $field) {

            $percent = (float) get_field($field, $post_id);

            if ($percent > 0) {

                $montant_remise = ($percent / 100) * $sous_total_ht;

                $titre_remise = $remise_fields_map[$field];

                $formule .= "{$titre_remise} {$percent}% = -{$montant_remise}\n";
                $formule_html .= '<tr>';
                $formule_html .= '<td colspan="4" style="text-align:right;border:1px solid #ccc;padding:5px;">'.$titre_remise.' '.$percent.'%</td>';
                $formule_html .= '<td style="border:1px solid #ccc;padding:5px;">-'.number_format($montant_remise, 2, ',', ' ').' €</td>';
                $formule_html .= '</tr>';

                $sous_total_ht -= $montant_remise;

                
                $formule .= "Sous-total HT : {$sous_total_ht}\n";
                $formule_html .= '<tr>';
                $formule_html .= '<td colspan="4" style="text-align:right;border:1px solid #ccc;padding:5px;">Sous-total HT</td>';
                $formule_html .= '<td style="border:1px solid #ccc;padding:5px;">'.number_format($sous_total_ht, 2, ',', ' ').' €</td>';
                $formule_html .= '</tr>';
            }
        }

        /**
         * 3️⃣ TVA
         */
        $taux_tva = (float) get_field('taux_tva', $post_id);

        $montant_tva = ($taux_tva / 100) * $sous_total_ht;

        $total_ttc = $sous_total_ht + $montant_tva;

        $formule .= "TVA {$taux_tva}% : {$montant_tva}\n";
        $formule .= "Total TTC : {$total_ttc}\n";

        $formule_html .= '<tr>';
        $formule_html .= '<td colspan="4" style="text-align:right;border:1px solid #ccc;padding:5px;">TVA '.$taux_tva.'% </td>';
        $formule_html .= '<td style="border:1px solid #ccc;padding:5px;">'.number_format($montant_tva, 2, ',', ' ').' €</td>';
        $formule_html .= '</tr>';

        $formule_html .= '<tr>';
        $formule_html .= '<td colspan="4" style="text-align:right;border:1px solid #ccc;padding:5px;">Total TTC</td>';
        $formule_html .= '<td style="border:1px solid #ccc;padding:5px;">'.number_format($total_ttc, 2, ',', ' ').' €</td>';
        $formule_html .= '</tr></tbody></table>';

        $prix_public_alwil = (float) get_field('prix_public_alwil', $post_id);
        
        $marge = $sous_total_ht - $prix_public_alwil;
        update_field('marge', $marge, $post_id);

        update_field('sous_total_ht', $sous_total_ht, $post_id);
        update_field('total_ht', $total_ht, $post_id);
        update_field('formule', $formule_html, $post_id);
        update_field('total_ttc', $total_ttc, $post_id);

        return [
            'formule' => $formule,
            'ht'      => $sous_total_ht,
            'ttc'     => $total_ttc
        ];
    }




    function alm_get_products_grouped() {

    $categories = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => true
    ]);

    $output = '';

    foreach ($categories as $cat) {

        $products = wc_get_products([
            'status'   => 'publish',
            'limit'    => -1,
            'category' => [$cat->slug],
            'type'     => ['subscription', 'variable-subscription'],
        ]);

        if (empty($products)) {
            continue;
        }

        $output .= '<h3 class="alm-cat-title">' . esc_html($cat->name) . '</h3>';
        $output .= '<table class="alm-table">
            <tr>
                <th>Image</th>
                <th>Produit</th>
                <th>Quantité</th>
            </tr>';

        foreach ($products as $prod) {

            // 🔒 Sécurité : on ignore toute variation si jamais elle passe
            if ($prod->is_type('variation')) {
                continue;
            }

            $image   = wp_get_attachment_image_src($prod->get_image_id(), 'thumbnail');
            $imgURL  = $image ? $image[0] : '';
            $regular = (float) $prod->get_regular_price();
            $sale    = (float) $prod->get_sale_price();

            $promo_display = '';
            if ($sale && $regular && $sale < $regular) {
                $discount = round((($regular - $sale) / $regular) * 100);
                $promo_display = " <span style='color:green;font-weight:bold;'>Promo -{$discount}% *</span>";
            }

            $output .= '<tr>
                <td><img src="' . esc_url($imgURL) . '" style="max-width:50px;"></td>
                <td>' . esc_html($prod->get_name()) . '<br>' . $promo_display . '</td>
                <td>
                    <input type="number"
                        min="0"
                        name="prod_qty[' . esc_attr($prod->get_id()) . ']"
                        value="0"
                        class="alm-qty">
                </td>
            </tr>';
        }

        $output .= '</table><br>';
    }

    return $output;
}




    function alm_devis_en_ligne_posts_columns($columns) {

        $new = [];

        // On garde le titre avant d’insérer nos colonnes
        foreach( $columns as $key => $label ) {

            $new[$key] = $label;

            if ($key === 'title') {
                $new['status']        = 'Status';
                $new['utilisateur']   = 'Utilisateur';
                $new['type_de_devis'] = 'Type de devis';
                $new['actions']       = 'Actions';
            }
        }

        return $new;
    }

    function alm_variation_devis_posts_columns($columns) {

        $new = [];

        // On garde le titre avant d’insérer nos colonnes
        foreach( $columns as $key => $label ) {

            $new[$key] = $label;

            if ($key === 'title') {
                $new['sous_total_ht']       = 'Sous total HT';
                $new['prix_ttc']       = 'Prix TTC';
                $new['actions']       = 'Actions';
            }
        }

        return $new;
    }

    // 2. Remplir les colonnes
    function alm_devis_en_ligne_posts_datas( $column, $post_id ) {

        switch ( $column ) {

            case 'status':
                $status = get_field('status', $post_id);
                echo esc_html( $status['label'] ?? '—' );
                break;


            case 'utilisateur':
                $user = get_field('utilisateur', $post_id);
                if ($user) {
                    $prenom = get_user_meta($user->ID, 'first_name', true);
                    $nom = get_user_meta($user->ID, 'last_name', true);
                    echo $user ? esc_html($nom." ".$prenom) : '—';
                } else {
                    echo '—';
                }
                break;

            case 'type_de_devis':
                $type = get_field('type_de_devis', $post_id);
                echo esc_html( $type['label'] ?? '—' );
                break;


            case 'actions':
                $variations  = get_field('variations', $post_id);
                $type_value = get_field('type_de_devis', $post_id)['value'];
                $pdf_url   = admin_url("admin-post.php?action=generer_pdf_devis&id=$post_id");
                $mail_url  = admin_url("admin-post.php?action=envoyer_mail_devis&id=$post_id");
                $status_value = get_field('status', $post_id)['value'];
                echo '<div style="display:flex; gap:8px; flex-wrap:wrap;">';

                foreach ( $variations as $variation ) : 
                    echo '<a class="button"  target="_blank" href="/wp-admin/post.php?post='.$variation->ID.'&action=edit">'.$variation->post_title.'</a>';
                endforeach; 
                echo '<a class="button button-primary" href="'.esc_url($pdf_url).'">Générer les PDF des variations</a>';
                echo '<a class="button" href="'.esc_url($mail_url).'">Envoyer le mail</a>';
                
                echo '</div>';
                break;
        }

    }


    function alm_variation_devis_posts_datas( $column, $post_id ) {

        switch ( $column ) {
            case 'actions':
                $recapitulatif_pdf  = get_field('recapitulatif_pdf', $post_id);
                $lien_fichier = ($recapitulatif_pdf)?$recapitulatif_pdf["link"]:null;
                
                echo '<div style="display:flex; gap:8px; flex-wrap:wrap;">';
                
                if ($lien_fichier) {
                    echo '<a class="button" href="'.esc_url($lien_fichier).'" target="_blank">Télécharger le pdf</a>';
                }
                echo '</div>';
                break;
            case 'prix_ttc':
                $total_ttc  = (float) get_field('total_ttc', $post_id);
                
                echo '<div style="display:flex; gap:8px; flex-wrap:wrap;">';
                
                echo $total_ttc.' €';
                
                echo '</div>';
                break;
            case 'sous_total_ht':
                $sous_total_ht  = (float) get_field('sous_total_ht', $post_id);
                
                echo '<div style="display:flex; gap:8px; flex-wrap:wrap;">';
                
                echo $sous_total_ht.' €';
                
                echo '</div>';
                break;
        }

    }

    function envoyer_email_avec_devis() {
        if (!current_user_can('manage_options')) {
                wp_die("Permissions insuffisantes.");
            }

        if (!isset($_GET['id'])) {
            wp_die("ID manquant.");
        }

        $id = intval($_GET['id']);

        // Générer le PDF et l'enregistrer dans le champ ACF
        $sent = DevisEmailSender::send_email_devis_final($id);
        

        // 5️⃣ Redirection avec info
        if ($sent) {
            update_field('status', '3', $id);
            wp_safe_redirect(admin_url("edit.php?post_type=devis-en-ligne&mail=ok"));
        } else {
            wp_safe_redirect(admin_url("edit.php?post_type=devis-en-ligne&mail=error"));
        }
        exit;
    }



    function alm_render_devis_form() {
        ob_start(); ?>

        <?php
            if(isset($_GET["status_demande"]) ){?>
            <div id="auto-popup" role="alert" aria-hidden="true" style="display:none;">
                <div class="auto-popup-inner">
                    <a href="#" class="auto-popup-close">&times;</a>
                    <div class="auto-popup-content">
                        <?php if(($_GET["status_demande"])=="success_and_email" ){
                            echo "<div class='msg-box success' style='text-align: center;font-weight: bolder;padding: 4px 10px;color: #00d369;'>Votre demande a été envoyée. Vous allez recevoir un email de confirmation.</div>";
                        }else if(($_GET["status_demande"])=="success_without_email"){
                            echo "<div class='msg-box success' style='text-align: center;font-weight: bolder;padding: 4px 10px;color: #00d369;'>Votre demande a été envoyée.</div>";
                        }
                        else{
                            echo "<div class='msg-box failure' style='text-align: center;font-weight: bolder;padding: 4px 10px;color: #d30b00ff;'>Échec de la demande.</div>";
                        }?>
                    </div>
                </div>
            </div>
        <?php }?>
        <?php
            if(!isset($_GET["status_demande"]) || (isset($_GET["status_demande"]) && !(($_GET["status_demande"])=="success")) ){
        ?>   
          
            <form method="post" id="alm-devis-form" class="alm-devis-form" enctype="multipart/form-data">
                
            
                <h2>Choix du produit Avast</h2>
                <div class="flx-radio-devis">
                    <span class="">
                        <input type="radio" style="margin-right: 16px;" value="idkn" id="idknow" name="choixavis" checked required="required" > 
                        <label for="idknow">Je ne sais pas quelle version choisir</label>
                    </span>
                    <span class="">
                        <input type="radio" style="margin-right: 16px;" value="ikn" id="iknow" name="choixavis" required="required" > 
                        <label for="iknow">Je sais ce dont j’ai besoin</label>
                    </span>
                </div>
                <div id="idknowForm"> 
                    <div class="">
                        <label for="compt2save" class="" style="margin-bottom: 12px;">
                            Indiquez simplement les ordinateurs à protéger :							
                        </label>
                        <textarea class="" name="compt2save" maxlength="200" id="compt2save" rows="4"></textarea>				
                    </div>
                </div>

                <?php 
                $user_id = get_current_user_id();
                $est_revendeur = current_user_can('customer_revendeur');
                if ( $est_revendeur ) : 
                    $args = [
                        'role'       => 'customer_particulier',
                        'meta_key'   => 'revendeur_id',
                        'meta_value' => $user_id
                    ];

                    $clients = get_users($args);
                ?>
                    <div class="">
                        Client final
                        <select name="client_final" required class="automatic-sent">
                            <!-- Option par défaut -->
                            <option value="" disabled>Sélectionnez un client</option>

                            <?php foreach ( $clients as $c ) : ?>
                                <option value="<?php echo $c->ID; ?>" >
                                    <?php echo esc_html($c->display_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                <?php endif; ?>


                        <?php
                        
                            $customer = new WC_Customer( $user_id );
                            $tax_rates = WC_Tax::get_rates("",$customer );
                            $first_rate = reset($tax_rates);
                            $percent_tva = $first_rate['rate'];
                            $title_tva = $first_rate['label'];

                            echo '<input type="hidden" name="title_tva" id="title_tva" value="'.$title_tva.'">';
                            echo '<input type="hidden" name="percent_tva" id="percent_tva" value="'.$percent_tva.'">';

                        ?>



                <div id="iknowForm">
                    Composez votre demande de devis avec les produits ci dessous
                    <?php echo $this->alm_get_products_grouped(); ?>
                </div>

               

            <div class="duree-devis-form">
                <h2>Durée</h2><br>
                <div style="display: flex; flex-direction: row; flex-wrap: wrap; gap: 15px; align-items: center; align-content: center;">
                   <input type="radio" name="alm_software_duration" id="duration_1" value="1-year" required checked>
                   <label for="duration_1">1 an</label><br>

                   <input type="radio" name="alm_software_duration" id="duration_2" value="2-years">
                   <label for="duration_2">2 ans</label><br>

                   <input type="radio" name="alm_software_duration" id="duration_3" value="3-years">
                   <label for="duration_3">3 ans</label><br>
                </div>
                <div style="display: flex; align-items: flex-start;margin-top:15px;">
                <input type="radio" name="alm_software_duration" id="duration_many" value="many-years">
                <label for="duration_many">
                    Je souhaite obtenir plusieurs devis pour 1, 2 et 3 ans et réaliser ainsi <b>jusqu'à 67% d'économie.</b>
                </label>
                </div>
            </div>
                

                <div class="div-remise">
                    <div id="demandeRemise">
                        <span style="font-family: 'Raleway';font-weight: 600;"> JE PEUX BÉNÉFICIER D'UNE REMISE COMMERCIALE :</span>
                        <!-- Option 1 -->
                        <label>
                            <input type="checkbox" name="option_remise[]"  id="option1" class="optionRemise" data-group="1" data-file="file1" data-value="Changement -25%"> Je change d'antivirus pour Avast -25%
                        </label>
                        <div class="upload hidden" id="file1">
                            <input type="file" name="justificatif_changement" accept="application/pdf,image/*">
                        </div>

                        <!-- Option 2 -->
                        <label>
                            <input type="checkbox"  name="option_remise[]"  id="option2" class="optionRemise" data-group="2" data-file="file2" data-value="Renouvellement de licences -30%"> Renouvellement de licences -30%
                        </label>
                        
                        <div class="upload hidden" id="file2" style="display: flex;gap: 7px; align-items: center;justify-content: center;">
                            <input  style="width: 50%;padding: 0.5rem 0.4rem; height: 30px;" type="text" id="old_key" name="justificatif_text_renouvellement" placeholder="Ancienne licence"> ou
                            <input style="width: 50%;" type="file" name="justificatif_file_renouvellement" accept="application/pdf,image/*">
                        </div>

                        <!-- Option 3 -->
                        <label>
                            <input type="checkbox" name="option_remise[]"  id="option3" class="optionRemise" data-group="3" data-file="file3" data-value="Administrations et mairies -30%"> Administrations et mairies -30%
                        </label>
                        <div class="upload hidden" id="file3">
                            <input type="file" name="justificatif_admin" accept="application/pdf,image/*">
                        </div>

                        <!-- Option 4 -->
                        <label>
                            <input type="checkbox" name="option_remise[]"  id="option4" class="optionRemise" data-group="4" data-file="file4" data-value="Établissements scolaires et associations -50%"> Établissements scolaires et associations -50%
                        </label>
                        <div class="upload hidden" id="file4">
                            <input type="file" name="justificatif_association" accept="application/pdf,image/*">
                        </div>

                        <input type="hidden" name="remise_type" id="remise_type">


                    </div>
                </div>


                <div class="">
                    <label for="comment" class="" style="margin-bottom: 12px;">
                        Ajouter un commentaire à ma demande :<span class="required">*</span>							
                    </label>
                    <textarea class="" name="comment" id="comment" maxlength="200" rows="4" required></textarea>				
                </div>

                <div class="div-form2">
                
                <?php if ( !is_user_logged_in() ) : ?>
                    <h2>Recevoir mon devis par email</h2><br>
                    <div class="radio" style="display: flex; flex-direction: row; gap: 40px;">
                        <label>
                            <input style="margin-right:20px;" type="radio" name="choice_login" value="nouveau" checked>
                            Je suis un nouveau client
                        </label>
                        <label>
                            <input style="margin-right:20px;" type="radio" name="choice_login" value="existant" >
                            Je suis déjà client
                        </label>
                    </div>

                    <!-- Zone pour nouveau client -->
                    

                    <br>
                    <div id="login_nouveau" style="display: block;"><!--<form action="devis.php?dest=devis#btm" method="post" name="frm_signup" onsubmit="return(Control_SignUp_Client(this,'AJOUT'))">-->
                        <?php 
                            $pays_liste = [
                                'FR' => 'France',
                                'BE' => 'Belgique',
                                'CH' => 'Suisse',
                                'LU' => 'Luxembourg',
                                'DE' => 'Allemagne',
                            ]; 
                            ?>
                            <div class="radio" style="display: flex; flex-direction: row; gap: 40px;">
                                <label>
                                    <input style="margin-right:20px;" type="radio" checked="" name="new_account_type_compte" value="PAR">
                                    Particulier 
                                </label>
                                <label>
                                    <input style="margin-right:20px;" type="radio" name="new_account_type_compte" value="PRO" >
                                    Professionnel, Association ou Institution
                                </label>
                            </div>
                            <br>
                            <div id="dnom_sos" style="">
                                <div class="count-clmn" style="">
                                    <div>
                                        <label>Dénomination sociale : </label>
                                        <input style="padding: 4px 1rem;height: 28px;" type="text" title="Dénomination sociale" alt="text" name="new_account_societe" size="40" value="" >
                                    </div>
                                </div>
                            </div>
                            <br>
                            <div class="count-clmn" style="">
                            <div>
                            <label>Genre : <span class="required">*</span></label>
                            <select title="Genre" id="genre" class="input_required" name="new_account_genre"  alt="Genre">
                                <option value="m" alt="Genre">Monsieur</option>
                                <option value="f" alt="Genre">Madame</option>
                                <option value="f" alt="Genre">Mademoiselle</option>
                                <option selected="" value="" alt="Genre">
                                    ----------
                                </option>
                            </select>
                            </div>
                            <div>
                            <label>Nom : <span class="required">*</span></label>
                            <input class="input_required" type="text" title="Nom" alt="text" name="new_account_nom" size="30" maxlength="50" value="" >
                            </div>
                            <div>
                            <label>Prénom : <span class="required">*</span></label>
                            <input class="input_required" type="text" title="prenom" alt="text" name="new_account_prenom" size="30" maxlength="50" value="" >
                            </div>
                            <div>
                            <label>Téléphone : <span class="required">*</span></label>
                            <input class="input_required" type="text" title="telephone" alt="text" name="new_account_telephone" size="30" maxlength="20" value="" >
                            </div>
                            <div>
                            <label>Adresse : <span class="required">*</span></label>
                            <input class="input_required" type="text" title="adresse" alt="text" name="new_account_adresse" size="30" maxlength="100" value="" >
                            </div>
                            <div>
                            <label>Ville : <span class="required">*</span></label>
                            <input class="input_required" type="text" title="ville" alt="text" name="new_account_ville" size="30" maxlength="50" value="" >
                            </div>
                            <div>
                            <label>Code postal : <span class="required">*</span></label>
                            <input class="input_required" type="text" title="code_postal" alt="text" name="new_account_code_postal" size="30" maxlength="6" value="" >
                            </div>
                            <div>
                            <label>Pays : <span class="required">*</span></label>
                            <select name="new_account_pays" id="pays" required class="">
                                <?php foreach ( $pays_liste as $code => $nom ) : ?>
                                    <option value="<?php echo esc_attr($code); ?>" >
                                        <?php echo esc_html($nom); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            </div>
                            
                            <div>
                            <!-- <b>Mes identifiants de connexion :</b>   <br> -->  
                            <label>Adresse Email : <span class="required">*</span></label>
                            <input class="input_required" type="text" title="Adresse Email" alt="email" name="new_account_email" maxlength="60" size="40" value="" >
                            </div>
                            <div>
                            <label>Confirmer Email : <span class="required">*</span></label>
                            <input class="input_required" type="text" title="Confirmer Email" alt="email" name="new_account_confirm_email" maxlength="60" size="40" value="" >
                            </div>
                            <div>
                            <label>Mot de passe : <span class="required">*</span></label> 
                            <input id="password_1" class="input_required" type="password" title="Mot de passe" name="new_account_password_1" maxlength="50" size="20" value="">     
                            </div>
                            <div>
                            <label>Confirmer Mot de passe : <span class="required">*</span></label> 
                            <input id="password_2" class="input_required" type="password" title="Confirmer Mot de passe" name="new_account_password_2" maxlength="50" size="20" value="">      
                            </div>
                        </div>
                        <br>
                        <div>
                            <label style="line-height: 1.5;">
                                <input type="hidden" name="new_account_charte" value="Acceptation de la charte de confidentialité">
                                <input style="margin-right:15px;" type="checkbox" checked name="new_account_divulgation" title="Acceptation de la charte de confidentialité" value="1" class="input_ok"  alt="checkbox" >Je comprends que mes informations ne seront pas divulguées à des tiers, conformément à <a href="/charte-de-confidentialite/" target="_blank" style="text-decoration:underline;">la charte de confidentialité</a> de ce site.
                            </label><br>   
                            </div><br><br>
                    </div>

                    <!-- Zone pour client existant -->
                    <div id="login_existant" style="display:none; margin-top:10px;">
                        <div style="display: grid !important; grid-template-columns: 0.6fr 1fr; gap: 20px; margin-bottom: 10px;"><label>Adresse Email :</label>
                        <input style="padding: 4px 1rem;height: 28px;" class="input_required" type="email" maxlength="60" name="existing_account_email">
                        </div>
                        <div style="display: grid !important; grid-template-columns: 0.6fr 1fr; gap: 20px; margin-bottom: 10px;">
                        <label>Mot de passe :</label>
                        <input style="padding: 4px 1rem;height: 28px;" class="input_required" type="password" maxlength="50" name="existing_account_password">
                        </div>
                    </div>
                    <br>
                <?php endif; ?>
                <input type="hidden" name="logged" value="<?php if ( is_user_logged_in() ) echo '1'; ?>">
                <input type="hidden" name="goal" value="devis_en_ligne">

                <button type="submit" id="send-button" class="elementor-element elementor-align-center elementor-widget elementor-widget-button">Envoyer la demande de devis</button>
                </div>
            </form>
            <div id="error-msg" class="error-msg" style='text-align: center;color: #d30b00ff;'></div>

        <?php  }?>
            <script>
                jQuery(document).ready(function($) {

                    // cacher tout au début
                    $('#iknowForm').hide();
                    $('#dnom_sos').hide();

                    // changement au clic sur radio
                    $('input[name="choixavis"]').on('change', function() {

                        if ($('#idknow').is(':checked')) {
                            $('#idknowForm').slideDown();
                            $('#iknowForm').slideUp();
                        }

                        if ($('#iknow').is(':checked')) {
                            $('#iknowForm').slideDown();
                            $('#idknowForm').slideUp();
                        }
                    });

                    $('input[name="choice_login"]').on('change', function() {
                        if ($(this).val() === 'nouveau') {
                            $('#login_nouveau').show();
                            $('#login_existant').hide();
                        } else {
                            $('#login_nouveau').hide();
                            $('#login_existant').show();
                        }
                    });

                    $('input[name="new_account_type_compte"]').on('change', function() {
                        if ($(this).val() === 'PRO') {
                            $('#dnom_sos').show();
                        } else {
                            $('#dnom_sos').hide();
                        }
                    });
                   

                    function checkofflogfields() {
                        
                        let offlogfieldsFilled = true;
                        let msg = '';
                        if($('#comment').val().trim() === '') {
                            offlogfieldsFilled = false;
                        }
                        //console.log("check",$('#comment').val().trim())
                        $('button[type="submit"]').prop('disabled', !(offlogfieldsFilled ));
                        if(!offlogfieldsFilled) msg += 'Les champs par défaut doivent êtres saisis.<br>';
                        $('#error-msg').html(msg);
                        //console.log("offlogfieldsFilled",offlogfieldsFilled)
                    }

                    function checkNewAccountFields() {

                        let choice_login = $('input[name="choice_login"]:checked').val();
                        let register = (choice_login=="nouveau")?true:false;
                        let sign_in = (choice_login=="existant")?true:false;
                        let allFilled = true;
                        let emailsMatch = true;
                        let passwordsMatch = true;
                        

                        console.log("choice_login",choice_login)
                        console.log("register",register)
                        console.log("sign_in",sign_in)
                        if(register){
                            // 1️⃣ Tous les champs requis remplis ?
                            $('#login_nouveau:visible .input_required').each(function(){
                                if($(this).val().trim() === '') {
                                    allFilled = false;
                                    return false; // stop each
                                }
                            });
                        }

                        if(sign_in){
                            // 1️⃣ Tous les champs requis remplis ?
                            $('#login_existant:visible .input_required').each(function(){
                                if($(this).val().trim() === '') {
                                    allFilled = false;
                                    return false; // stop each
                                }
                            });
                        }
                        
                        
                        if(register){
                            // 2️⃣ Email correspond ?
                            let email = $('input[name="new_account_email"]').val().trim();
                            let confirmEmail = $('input[name="new_account_confirm_email"]').val().trim();
                            if(email !== confirmEmail) emailsMatch = false;

                            // 3️⃣ Mot de passe correspond ?
                            let pass1 = $('#password_1').val();
                            let pass2 = $('#password_2').val();
                            if(pass1 !== pass2) passwordsMatch = false;
                        }
                        

                        // Affichage éventuel d'un message d'erreur (optionnel)
                        
                        let msg = '';
                        if(!allFilled) msg += 'Tous les champs obligatoires doivent être remplis.<br>';
                        if(!emailsMatch) msg += 'Les adresses email ne correspondent pas.<br>';
                        if(!passwordsMatch) msg += 'Les mots de passe ne correspondent pas.<br>';
                        $('#error-msg').html(msg);
                        console.log("allFilled",allFilled)
                        console.log("emailsMatch",emailsMatch)
                        console.log("passwordsMatch",passwordsMatch)
                        console.log("msg",msg)
                        // 4️⃣ Activer/désactiver le bouton
                        $('button[type="submit"]').prop('disabled', !(allFilled && emailsMatch && passwordsMatch));
                    }

                    var logged = $('input[name="logged"]').val();
                    console.log("logged",logged)
                    if(!logged){
                        console.log("remplir info connexion")
                        // Vérification à chaque saisie
                        $('button[type="submit"]').prop('disabled', true);
                        $('#login_nouveau .input_required, #password_1, #password_2, input[name="new_account_confirm_email"]').on('input', function(){
                            checkNewAccountFields();
                        });
                        $('#login_existant .input_required').on('input', function(){
                            checkNewAccountFields();
                        });
                        $('input[name="choice_login"]').on('change', function(){
                            checkNewAccountFields();
                        });
                    }else{
                        console.log("remplir champs standards")
                        $('button[type="submit"]').prop('disabled', true);
                        $('#comment').on('input', function() {
                            checkofflogfields();
                        });

                    }
                   


                    $('.optionRemise').on('change', function() {

                        const $this = $(this);
                        const group = parseInt($this.data('group'));

                        // logique de combinaison
                        if (group === 1) {
                            // Option 1 seule → décocher toutes les autres
                            $('.optionRemise').not($this).prop('checked', false);
                        } else if (group === 2) {
                            // Option 2 peut être combinée avec 3 ou 4 → décocher 1
                            $('.optionRemise').each(function() {
                                if (parseInt($(this).data('group')) === 1) $(this).prop('checked', false);
                            });
                        } else if (group === 3) {
                            // décocher 1 et 4
                            $('.optionRemise').each(function() {
                                const g = parseInt($(this).data('group'));
                                if (g === 1 || g === 4) $(this).prop('checked', false);
                            });
                        } else if (group === 4) {
                            // décocher 1 et 3
                            $('.optionRemise').each(function() {
                                const g = parseInt($(this).data('group'));
                                if (g === 1 || g === 3) $(this).prop('checked', false);
                            });
                        }

                        // afficher tous les uploads correspondant aux cases cochées
                        $('.upload').addClass('hidden');
                        $('.optionRemise:checked').each(function() {
                            const fileId = $(this).data('file');
                            $('#' + fileId).removeClass('hidden');
                        });

                        // reconstruire hidden
                        let values = [];
                        $('.optionRemise:checked').each(function() {
                            values.push($(this).data('value'));
                        });
                        $('#remise_type').val(values.join(', '));
                        console.log("remises courantes",values.length,values.join(', '))
                    
                        
                    });

                    const $popup = $('#auto-popup');

                    // Si la popup est présente dans le DOM → on l'affiche
                    if ($popup.length) {
                        console.log("afficher popup")
                        $popup.fadeIn(200);

                        // la cacher dans 5 secondes
                        setTimeout(function(){
                            $popup.fadeOut(300);
                        }, 15000);

                        // bouton fermer
                        $popup.on('click', '.auto-popup-close', function(){
                            $popup.fadeOut(200);
                        });
                    }else{
                        console.log("non afficher popup")
                    }

                
                });


            </script>
            <style>
                .div-remise {
                    background-color: #f5f3f3;
                    padding: 15px 30px;
                    display: flex;
                    flex-direction: column;
                    gap: 11px;
                    max-width:400px;
                }
                #send-button:disabled {
                    background-color: #999 !important;
                    border-color: #777 !important;
                    cursor: not-allowed;
                    opacity: 0.5;
                }
                .hidden { display:none !important; }
                .block-option { margin-bottom: 12px; }
                label { font-weight: 500; cursor:pointer; }
                 span.required{
                    color: red;
                    font-size: 34px;
                }
            
                #alm-devis-form > button[disabled] {
                    background-color: #ccc;       /* gris clair */
                    color: #666;                  /* texte plus pâle */
                    cursor: not-allowed;          /* curseur interdit */
                    opacity: 0.6;                 /* un peu transparent */
                    pointer-events: none;  
                    border:none       /* désactive tout clic */
                }

                /* Optionnel : bouton activé */
                #alm-devis-form > button:not([disabled]) {
                    color: #fff;
                    cursor: pointer;
                    opacity: 1;
                    transition: background-color 0.3s, opacity 0.3s;
                }

                /* CSS : popup centrée, simple et responsive */
                #auto-popup{
                position: fixed;
                left: 50%;
                top: 50%;
                transform: translate(-50%, -50%);
                z-index: 99999;
                display: none;
                width: calc(100% - 40px);
                max-width: 520px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.25);
                border-radius: 10px;
                background: transparent;
                pointer-events: none; /* évite d'intercepter les clics en dehors de la box */
                }

                #auto-popup .auto-popup-inner{
                pointer-events: auto; /* permet l'interaction à l'intérieur */
                background: #ffffff;
                padding: 18px 18px 14px 18px;
                border-radius: 8px;
                text-align: center;
                font-family: Arial, sans-serif;
                color: #222;
                }

                .auto-popup-close{
                position: absolute;
                right: 8px;
                top: 6px;
                background: transparent;
                border: none;
                font-size: 30px;
                line-height: 1;
                color: #666;
                cursor: pointer;
                }

                .auto-popup-content{
                font-size: 15px;
                line-height: 1.4;
                }

                /* petite animation d'entrée */
                .auto-popup-show {
                animation: popup-in 280ms ease-out;
                }
                @keyframes popup-in {
                from { transform: translate(-50%, -45%) scale(0.98); opacity: 0; }
                to   { transform: translate(-50%, -50%) scale(1); opacity: 1; }
                }

                
            </style>
        

        <?php
        return ob_get_clean();
    }


     function alm_handle_devis_form() {
        if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) return;
        if ( !isset($_POST['goal']) ) return;
        if ( isset($_POST['goal']) && $_POST['goal'] !== 'devis_en_ligne' ) return;
        
       

        // Connexion WordPress
        if ( !function_exists('wp_get_current_user') ) return;

        $user_id = null;

        // 1️⃣ Utilisateur connecté
        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id();
            if(isset($_POST['client_final'])){
                $client_final_id = $_POST['client_final'];                   
            }
             
            
        } else {
            // 2️⃣ Non connecté
            $choice_login = sanitize_text_field($_POST['choice_login'] ?? '');

            if ( $choice_login === 'existant' ) {
                // chercher l'utilisateur par email et password
                $email = sanitize_email($_POST['existing_account_email'] ?? '');
                $password = $_POST['existing_account_password'] ?? '';

                if ( $email && $password ) {
                    $user = wp_authenticate( $email, $password );
                    if ( !is_wp_error($user) ) {
                        $user_id = $user->ID;
                    } else {
                        // erreur auth
                        // return ou message d'erreur
                        wp_redirect(home_url('/devis/?status_demande=failed'));
                        exit;
                    }
                }
            } elseif ( $choice_login === 'nouveau' ) {
                // créer un compte
                $new_account_email = sanitize_email($_POST['new_account_email'] ?? '');
                $new_account_password_1 = $_POST['new_account_password_1'] ?? '';
                $new_account_password_2 = $_POST['new_account_password_2'] ?? '';
                if($new_account_password_1 != $new_account_password_2){
                    wc_add_notice('Erreur lors de la création de la demande : Les mots de passe ne coincident pas', 'error');
                    wp_redirect(home_url('/devis/?status_demande=failed'));
                        exit;
                }

                $new_account_nom = sanitize_text_field($_POST['new_account_nom'] ?? '');
                $new_account_prenom = sanitize_text_field($_POST['new_account_prenom'] ?? '');
                $new_account_type_compte = sanitize_text_field($_POST['new_account_type_compte'] ?? '');
                $type_client = ($new_account_type_compte=="PAR")?"Particulier ":"Professionnel, Association ou Institution";
                $new_account_societe = sanitize_text_field($_POST['new_account_societe'] ?? '');
                $new_account_genre = sanitize_text_field($_POST['new_account_genre'] ?? '');
                $new_account_telephone = sanitize_text_field($_POST['new_account_telephone'] ?? '');
                $new_account_adresse = sanitize_text_field($_POST['new_account_adresse'] ?? '');
                $new_account_ville = sanitize_text_field($_POST['new_account_ville'] ?? '');
                $new_account_code_postal = sanitize_text_field($_POST['new_account_code_postal'] ?? '');
                $new_account_pays = sanitize_text_field($_POST['new_account_pays'] ?? '');
               
             
                

                if ( $new_account_email && $new_account_password_1 && $new_account_nom && $new_account_prenom ) {
                    $userdata = [
                        'user_login' => $new_account_email,
                        'user_pass'  => $new_account_password_1,
                        'user_email' => $new_account_email,
                        'first_name' => $new_account_prenom,
                        'last_name'  => $new_account_nom,
                        'role'       => 'customer_particulier',
                    ];

                    $user_id = wp_insert_user($userdata);

                    if ( is_wp_error($user_id) ) {
                        wc_add_notice('Erreur lors de la création du client : ' . $user_id->get_error_message(), 'error');
                        wp_redirect(home_url('/devis/?status_demande=failed'));
                        exit;
                    }

                    // Ajout des meta
                    update_user_meta($user_id, 'type_client', $type_client);
                    update_user_meta($user_id, 'denomination', $new_account_societe);
                    update_user_meta($user_id, 'genre', $new_account_genre);
                    update_user_meta($user_id, 'billing_phone', $new_account_telephone);
                    update_user_meta($user_id, 'billing_address_1', $new_account_adresse);
                    update_user_meta($user_id, 'ville', $new_account_ville);
                    update_user_meta($user_id, 'code_postal', $new_account_code_postal);
                    update_user_meta($user_id, 'pays', $new_account_pays);
                }
            }
        }

        if(!$user_id){
            wc_add_notice('Erreur lors de la création de la demande : pas d\'utilisateur trouvé', 'error');
            wp_redirect(home_url('/devis/?status_demande=failed'));
            exit;
        }

        // 3️⃣ Gestion du devis
        $choix_av = sanitize_text_field($_POST['choixavis'] ?? '');
        
        $duration  = sanitize_text_field($_POST['alm_software_duration'] ?? '');
        $comment   = sanitize_textarea_field($_POST['comment'] ?? '');

        if ( $choix_av === 'idkn' ) {
            $compt2save = sanitize_textarea_field($_POST['compt2save'] ?? '');
            // Enregistrer $compt2save dans la table devis ou post meta
        } elseif ( $choix_av === 'ikn' ) {
            $prod_qty = $_POST['prod_qty'] ?? []; // tableau [product_id => qty]
            // Boucler et enregistrer chaque produit + quantité pour ce user/devis
        }

        /*

        creer l'objet devis en ligne post_type=devis-en-ligne  
        champs date_de_creation Sélecteur de date et heure, 
        option bouton radio(valeur/libellé retourne la valeur), 
        compt2save Zone de texte, produits_de_la_commande est un Répéteur et il a les sous champs 
        produit Relation retourne l'objet, 
        et quantite nombre,
        software_duration bouton radio(valeur/libellé retourne la valeur), 
        comment Zone de texte, 
        utilisateur Compte retourne objet du compte 

        */

                /* ------------------------------------------------------------------
           Création du devis post_type=devis-en-ligne  (structure ACF)
        ------------------------------------------------------------------*/

        // 1) Créer le post "devis en ligne"
        $post_id = wp_insert_post([
            'post_type'  => 'devis-en-ligne',
            'post_status'=> 'publish',
            'post_author'=> $user_id,
            'post_title' => 'Devis du ' . date('d/m/Y H:i'),
        ]);

       

        

        if ( is_wp_error($post_id) ) {
            wp_die("Erreur lors de la création du devis : " . $post_id->get_error_message());
        }

        // 2) Champs standard
        $expiration_timestamp = strtotime('+1 month', current_time('timestamp'));
        $expiration_mysql = date('Y-m-d H:i:s', $expiration_timestamp);

        update_field('date_de_creation', current_time('mysql'), $post_id);
        update_field('date_expiration', $expiration_mysql, $post_id);
        update_field('option', $choix_av, $post_id);
        update_field('software_duration', $duration, $post_id);
        update_field('status', '0', $post_id);
        update_field('field_692ec6324ed14', '0', $post_id);
        update_field('note_client', $comment, $post_id);
        update_field('field_692eaafe3985a', $comment, $post_id);
        update_field('type_de_devis', 'client', $post_id);
        update_field('utilisateur', $user_id, $post_id);
        update_field('client_final', $client_final_id, $post_id);
        update_field('field_698c460ac6d81', $client_final_id, $post_id);
        update_field('field_692eab163985b', $user_id, $post_id);

        // 3) choix → "je ne sais pas quelle version"
        if ( $choix_av === 'idkn' ) {
            update_field('compt2save', $compt2save, $post_id);
        }

        // 4) choix → "je sais ce dont j’ai besoin" (RÉPÉTEUR ACF)
        if ( $choix_av === 'ikn' && !empty($prod_qty)) {

            // 5) remises

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

            $remise_fields_map = [
                "Changement -25%" => 'remise_changer_avast',
                "Renouvellement de licences -30%"        => 'remise_renewal',
                "Administrations et mairies -30%"       => 'remise_administration_mairie',
                "Établissements scolaires et associations -50%" => 'remise_etalissements'
            ];

            if ( !empty($_POST['remise_type']) ) {

                // Split sur la virgule pour récupérer chaque option
                $remises_options = array_map('trim', explode(',', $_POST['remise_type']));

                foreach ($remises_options as $option) {

                    // 1️⃣ Créer la remise CPT
                    $remise_id = wp_insert_post([
                        'post_type'   => 'remise',
                        'post_title'  => "Demande de remise sur devis : $option - Utilisateur $user_id",
                        'post_status' => 'publish',
                        'post_author' => $user_id,
                    ]);

                    if ( is_wp_error($remise_id) ) continue;

                    // 2️⃣ Champs ACF
                    update_field('utilisateur', $user_id, $remise_id);
                    update_field('compte', [$user_id], $remise_id);
                    update_field('statut', 'appliquee au devis', $remise_id);
                    update_field('pourcentage', $percentage_fields_map[$option], $remise_id);
                    update_field('date_de_creation', current_time('d/m/Y g:i a'), $remise_id);
                    $expiration = date('Y-m-d H:i:s', strtotime('-1 month'));
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

                
            }

            $percent_tva = $_POST['percent_tva'];
            $title_tva = $_POST['title_tva'];
            $variations_ids = [];

            
            switch ($duration) {
                case '1-year':
                    $variation_id = wp_insert_post([
                        'post_type'  => 'variation-devis',
                        'post_status'=> 'publish',
                        'post_author'=> $user_id,
                        'post_title' => 'Variation 1 an - Devis #'.$post_id,
                    ]);
                    // reset propre ACF
                    delete_field('produits_de_la_variation', $variation_id);

                    foreach ($prod_qty as $product_id => $qty) {
                        $qty = intval($qty);
                        if ($qty > 0) {

                            add_row('produits_de_la_variation', [
                                'produit'  => $product_id,
                                'quantite' => $qty,
                                'duree' => 1,
                            ], $variation_id);
                        }
                    }
                    foreach ($remises_options as $option) {
                        update_field($remise_fields_map[$option], $percentage_fields_map[$option], $variation_id);
                    }
                    update_field('tva', $title_tva, $variation_id);
                    update_field('taux_tva', $percent_tva, $variation_id);
                    $variations_ids[] = $variation_id;
                    break;
                case '2-years':
                    $variation_id = wp_insert_post([
                        'post_type'  => 'variation-devis',
                        'post_status'=> 'publish',
                        'post_author'=> $user_id,
                        'post_title' => 'Variation 2 ans - Devis #'.$post_id,
                    ]);
                    // reset propre ACF
                    delete_field('produits_de_la_variation', $variation_id);

                    foreach ($prod_qty as $product_id => $qty) {
                        $qty = intval($qty);
                        if ($qty > 0) {

                            add_row('produits_de_la_variation', [
                                'produit'  => $product_id,
                                'quantite' => $qty,
                                'duree' => 2,
                            ], $variation_id);
                        }
                    }
                    foreach ($remises_options as $option) {
                        update_field($remise_fields_map[$option], $percentage_fields_map[$option], $variation_id);
                    }
                    update_field('tva', $title_tva, $variation_id);
                    update_field('taux_tva', $percent_tva, $variation_id);
                    $variations_ids[] = $variation_id;
                    break;
                case '3-years':
                    $variation_id = wp_insert_post([
                        'post_type'  => 'variation-devis',
                        'post_status'=> 'publish',
                        'post_author'=> $user_id,
                        'post_title' => 'Variation 3 ans - Devis #'.$post_id,
                    ]);
                    // reset propre ACF
                    delete_field('produits_de_la_variation', $variation_id);

                    foreach ($prod_qty as $product_id => $qty) {
                        $qty = intval($qty);
                        if ($qty > 0) {

                            add_row('produits_de_la_variation', [
                                'produit'  => $product_id,
                                'quantite' => $qty,
                                'duree' => 3,
                            ], $variation_id);
                        }
                    }
                    foreach ($remises_options as $option) {
                        update_field($remise_fields_map[$option], $percentage_fields_map[$option], $variation_id);
                    }
                    update_field('tva', $title_tva, $variation_id);
                    update_field('taux_tva', $percent_tva, $variation_id);
                    $variations_ids[] = $variation_id;
                    break;
                case 'many-years':
                    $variation1_id = wp_insert_post([
                        'post_type'  => 'variation-devis',
                        'post_status'=> 'publish',
                        'post_author'=> $user_id,
                        'post_title' => 'Variation 1 an - Devis #'.$post_id,
                    ]);
                    // reset propre ACF
                    delete_field('produits_de_la_variation', $variation1_id);
                    foreach ($prod_qty as $product_id => $qty) {
                        $qty = intval($qty);
                        if ($qty > 0) {

                            add_row('produits_de_la_variation', [
                                'produit'  => $product_id,
                                'quantite' => $qty,
                                'duree' => 1,
                            ], $variation1_id);
                        }
                    }
                    foreach ($remises_options as $option) {
                        update_field($remise_fields_map[$option], $percentage_fields_map[$option], $variation1_id);
                    }
                    update_field('tva', $title_tva, $variation1_id);
                    update_field('taux_tva', $percent_tva, $variation1_id);

                    $variation2_id = wp_insert_post([
                        'post_type'  => 'variation-devis',
                        'post_status'=> 'publish',
                        'post_author'=> $user_id,
                        'post_title' => 'Variation 2 ans - Devis #'.$post_id,
                    ]);
                    // reset propre ACF
                    delete_field('produits_de_la_variation', $variation2_id);
                    foreach ($prod_qty as $product_id => $qty) {
                        $qty = intval($qty);
                        if ($qty > 0) {

                            add_row('produits_de_la_variation', [
                                'produit'  => $product_id,
                                'quantite' => $qty,
                                'duree' => 2,
                            ], $variation2_id);
                        }
                    }
                    foreach ($remises_options as $option) {
                        update_field($remise_fields_map[$option], $percentage_fields_map[$option], $variation2_id);
                    }
                    update_field('tva', $title_tva, $variation2_id);
                    update_field('taux_tva', $percent_tva, $variation2_id);

                    $variation3_id = wp_insert_post([
                        'post_type'  => 'variation-devis',
                        'post_status'=> 'publish',
                        'post_author'=> $user_id,
                        'post_title' => 'Variation 3 ans - Devis #'.$post_id,
                    ]);
                    // reset propre ACF
                    delete_field('produits_de_la_variation', $variation3_id);
                    foreach ($prod_qty as $product_id => $qty) {
                        $qty = intval($qty);
                        if ($qty > 0) {

                            add_row('produits_de_la_variation', [
                                'produit'  => $product_id,
                                'quantite' => $qty,
                                'duree' => 3,
                            ], $variation3_id);
                        }
                    }
                    foreach ($remises_options as $option) {
                        update_field($remise_fields_map[$option], $percentage_fields_map[$option], $variation3_id);
                    }
                    update_field('tva', $title_tva, $variation3_id);
                    update_field('taux_tva', $percent_tva, $variation3_id);
                    $variations_ids[] = $variation1_id;
                    $variations_ids[] = $variation2_id;
                    $variations_ids[] = $variation3_id;
                    break;
                
                default:
                    # code...
                    break;
            }

            update_field('variations', $variations_ids, $post_id);

        
        }


        wc_add_notice("Votre demande a été envoyée. Vous allez recevoir un email de confirmation.", "success");

        $sent = DevisEmailSender::send_email_devis_created($post_id);

        if ($sent) {
            // Mail envoyé avec succès
            wp_redirect(home_url('/devis/?status_demande=success_and_email'));
        } else {
            // Échec de l'envoi
            wp_redirect(home_url('/devis/?status_demande=success_without_email'));
        }
        exit;
        

    }



    function alm_force_quantity_after_add_to_cart( $cart_item_key, $product_id, $quantity ) {
        $cart_item = WC()->cart->get_cart_item( $cart_item_key );
        if ( isset($cart_item['alm_quantity2']) ) {
            $new_quantity = intval($cart_item['alm_quantity2']);
            WC()->cart->set_quantity( $cart_item_key, $new_quantity, true );
        }
    }


    function alm_save_custom_fields_to_cart( $cart_item_data, $product_id ) {

        if ( isset($_POST['alm_duree2']) ) {
            $cart_item_data['alm_duree'] = sanitize_text_field($_POST['alm_duree2']);
        }

        if ( isset($_POST['alm_quantity2']) ) {
            $cart_item_data['alm_quantity2'] = sanitize_text_field($_POST['alm_quantity2']);

            $cart_item_data['quantity'] = intval($_POST['alm_quantity2']);
             $new_quantity = intval($_POST['alm_quantity2']);
        }

        return $cart_item_data;
    }






}
