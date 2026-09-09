<?php

if (!defined('ABSPATH')) {
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

        
    }


function presta_import_devis_page() {


    if (isset($_POST['upload_devis'])) {

        check_admin_referer('presta_upload_devis');

        if (
            empty($_FILES['devis_csv']['tmp_name']) ||
            $_FILES['devis_csv']['error'] !== UPLOAD_ERR_OK
        ) {
            echo '<div class="notice notice-error">
                    <p>Erreur lors de l\'upload.</p>
                </div>';
            return;
        }

        $upload_dir = wp_upload_dir();

        $directory = $upload_dir['basedir'] . '/presta-import';

        if (!file_exists($directory)) {
            wp_mkdir_p($directory);
        }

        $file = $directory . '/devis.csv';

        if (move_uploaded_file(
            $_FILES['devis_csv']['tmp_name'],
            $file
        )) {

            echo '<div class="notice notice-success">
                    <p>CSV uploadé avec succès.</p>
                </div>';

        } else {

            echo '<div class="notice notice-error">
                    <p>Impossible de sauvegarder le CSV.</p>
                </div>';
        }
    }


    if (isset($_POST['import_devis'])) {

        check_admin_referer('presta_import_devis');

        $line_start = intval($_POST['line_start'] ?? 0);
        $line_end   = intval($_POST['line_end'] ?? 0);

        if ($line_start <= 0 || $line_end < $line_start) {

            echo '<div class="notice notice-error">
                    <p>Plage d\'IDs invalide.</p>
                </div>';

            return;
        }

        presta_import_devis($line_start, $line_end);
    }


    

    if (!current_user_can('manage_options')) {
        return;
    }

    ?>

    <div class="wrap">

        <h1>Import PrestaShop</h1>

        <h2>Devis</h2>

        <form method="post" enctype="multipart/form-data">

            <?php wp_nonce_field('presta_upload_devis'); ?>

            <h2>1. Upload des devis</h2>

            <input
                type="file"
                name="devis_csv"
                accept=".csv"
                required
            >

            <button
                type="submit"
                name="upload_devis"
                class="button button-primary"
            >
                Upload
            </button>

        </form>


        <hr>


        <form method="post">

            <?php wp_nonce_field('presta_import_devis'); ?>

            <h2>2. Import des devis</h2>

            <p>
                <label>ID ligne début</label><br>
                <input type="number" name="line_start" value="1" min="1">
            </p>

            <p>
                <label>ID ligne fin</label><br>
                <input type="number" name="line_end" value="30" min="2">
            </p>

            <button
                type="submit"
                name="import_devis"
                class="button button-primary"
            >
                Importer
            </button>

        </form>

       

    </div>

    <?php
}

function presta_import_devis($line_start, $line_end) {

     error_log('line_start : '  . $line_start.' - line_end : '.$line_end);

        $upload_dir = wp_upload_dir();

        $file = $upload_dir['basedir'] . '/presta-import/devis.csv';

        if (!file_exists($file)) {

            echo '<div class="notice notice-error">
                    <p>Le fichier devis.csv est introuvable.</p>
                </div>';

            return;
        }

        $handle = fopen($file, 'r');

        if (!$handle) {
            return;
        }

         // Première ligne = colonnes
        $headers = fgetcsv($handle, 0, ';', '"', '\\');

        foreach ($headers as &$header) {
            $header = trim($header, " \t\n\r\0\x0B\xEF\xBB\xBF\"");
        }
        unset($header);
        

        error_log('import devis ');
        error_log('colone 0 '.$headers[0]);
        error_log('colone 3 '.$headers[3]);
        error_log('colone 5 '.$headers[5]);


        $variations_devis_creees = [];

        $line = 1;

        $imported = 0;
        $skipped  = 0;
        $errors   = 0;

        while (($row = fgetcsv($handle, 0, ';', '"', '\\')) !== false) {

            // Avant la ligne de départ
            if ($line < $line_start) {
                $line++;
                continue;
            }

            // Après la ligne de fin
            if ($line > $line_end) {
                break;
            }

            $data = array_combine($headers, $row);

            if ($data === false) {
                $errors++;
                $line++;
                continue;
            }

            $id_devis = intval($data['id_devis'] ?? 0);

            // =========================
            // IMPORT DU DEVIS
            // =========================


            $id_produit = intval($data['id_produit'] ?? 0);
            $id_client = intval($data['id_client'] ?? 0);
            $id_client_rvd = intval($data['id_client_rvd'] ?? 0);
            $id_revendeur = intval($data['id_revendeur'] ?? 0);
            $id_produit_id_woocommerce = intval($data['id_woocommerce'] ?? 0);
            $dt_expire = sanitize_text_field($data['dt_expire'] ?? ''); //2034-02-06
            $dt_end = sanitize_text_field($data['dt_end'] ?? ''); //2034-02-06
            $dt_devis = sanitize_text_field($data['dt_devis'] ?? ''); //2034-02-06
            $statut_devis = sanitize_text_field($data['statut_devis'] ?? '');
            $commentaire_client = sanitize_text_field($data['commentaire_client'] ?? '');
            $commentaire_admin = sanitize_text_field($data['commentaire'] ?? '');
            $qte = intval($data['qte'] ?? 0);
            $duree = intval($data['duree'] ?? 0);
            $nb_pcs = intval($data['nb_pcs'] ?? 0);
            $produit_simple = intval($data['produit_simple'] ?? 0); 
           
            $is_produit_simple = $produit_simple === 1;
            //si 1 prendre le produit
            //$product = wc_get_product($id_produit_id_woocommerce);
            //si 0 chercher le produit fils par duree nb_pcs les produits on des attribus number_of_computers et software_duration
            //SELECT v.ID FROM antied_posts v INNER JOIN antied_postmeta m1 ON m1.post_id = v.ID AND m1.meta_key = 'attribute_pa_software_duration' INNER JOIN antied_postmeta m2 ON m2.post_id = v.ID AND m2.meta_key = 'attribute_pa_number_of_computers' WHERE v.post_parent = 29555 AND v.post_type = 'product_variation' AND m1.meta_value like '1%' AND m2.meta_value = 10 LIMIT 1;
            $pu = floatval($data['pu'] ?? 0);
            $total_lig_ht = floatval($data['total_lig_ht'] ?? 0);
            
            $remise_revendeur = floatval($data['remise_revendeur'] ?? 0);
            $remise_renewal = floatval($data['remise_renewal'] ?? 0);
            $remise_special_1 = floatval($data['remise_special_1'] ?? 0);
            $remise_cumul = floatval($data['remise_cumul'] ?? 0);
            $remise_statutaire = floatval($data['remise_statutaire'] ?? 0);

            $calculer_formule = filter_var(
                $data['calculer_formule'] ?? false,
                FILTER_VALIDATE_BOOLEAN
            );


            //algortithme
            //chaque ligne a produit et commande et remises id_commande	id_produit remise_revendeur	remise_statutaire	remise_renewal	remise_special_1	remise_cumul mode_paiement	paiement_ok	id_client
            //1) pour chaque ligne creer la commande avec id_commande, ajouter le client comme champ caché et ajouter le premier produit avec son prix custom puis ajouter les remises 
            //2) la ligne suivante si id_commande est le meme juste ajouter le produit avec son prix custom mais ne plus ajouter les remises (elles sont les memes que sur le premier) sinon creer une autre commande et passer a 1)
            

            error_log('produit '.$id_produit);
            error_log('is produit simple '.$is_produit_simple);
            error_log('nb_pcs '.$nb_pcs);
            error_log('duree '.$duree);


            if ($id_client_rvd) {
                // Client final WooCommerce
                // À adapter selon ta logique de correspondance presta_id
                $client_final_id = get_users([
                    'meta_key'   => 'presta_id',
                    'meta_value' => $id_client_rvd,
                    'number'     => 1,
                    'fields'     => 'ID',
                ]);

                if (!empty($client_final_id)) {
                    $woo_id_client_final = $client_final_id[0];
                }
                
            
            }

            $user_id = get_users([
                'meta_key'   => 'presta_id',
                'meta_value' => $id_client,
                'number'     => 1,
                'fields'     => 'ID',
            ]);

            $woo_id_client = ($user_id)?$user_id[0]:null;


            if (!isset($variations_devis_creees[$id_devis])) {
                 // ==========================================
                // 1. CREATION DU DEVIS
                // ==========================================

                

                /* ------------------------------------------------------------------
                Création du devis post_type=devis-en-ligne  (structure ACF)
                ------------------------------------------------------------------*/

                // 1) Rechercher ou  Créer le post "devis en ligne"
                $devis_existants = get_posts([
                    'post_type'      => 'devis-en-ligne',
                    'post_status'    => 'any',
                    'posts_per_page' => 1,
                    'meta_key'       => 'id_prestashop',
                    'meta_value'     => $id_devis,
                ]);

                if (!empty($devis_existants)) {

                    // Devis déjà existant
                    $post_id = $devis_existants[0]->ID;

                } else {

                    // Créer le post "devis en ligne"
                    $post_id = wp_insert_post([
                        'post_type'   => 'devis-en-ligne',
                        'post_status' => 'publish',
                        'post_author' => $woo_id_client,
                        'post_title'  => 'Devis du ' . $dt_devis,
                    ]);

                    // Enregistrer l'ID PrestaShop
                    update_field('id_prestashop', $id_devis, $post_id);
                }

                if ( is_wp_error($post_id) ) {
                    wp_die("Erreur lors de la création du devis : " . $post_id->get_error_message());
                }

                $date_creation = date('Y-m-d H:i:s', strtotime($dt_devis));
                $date_expiration = date('Y-m-d H:i:s', strtotime($dt_expire));
                switch ($duree) {
                    case 1:
                        $software_duration = "1-year";
                        break;
                    case 2:
                        $software_duration = "2-years";
                        break;
                    case 3:
                        $software_duration = "3-years";
                        break;
                    
                    default:
                        $software_duration = "many-years";
                        break;
                }

                switch ($statut_devis) {
                    case 'Devis chiffré et envoyé':
                        $statut_devis_key = '1';
                        break;
                    case 'Devis chiffré et envoyé (client import Avast)':
                        $statut_devis_key = '2';
                        break;
                    case 'Nouveau devis chiffré et envoyé':
                        $statut_devis_key = '3';
                        break;
                    case 'Création de la demande de devis':
                        $statut_devis_key = '0';
                        break;
                    
                    default:
                        $statut_devis_key = '0';
                        break;
                }

                update_field('date_de_creation', $date_creation, $post_id);
                update_field('date_expiration', $date_expiration, $post_id);
                update_field('option', 'ikn', $post_id);
                update_field('software_duration', $software_duration, $post_id);
                update_field('status', $statut_devis_key, $post_id);
                update_field('field_692ec6324ed14', $statut_devis_key, $post_id);
                update_field('note_client', $commentaire_client, $post_id);
                update_field('field_692eaafe3985a', $commentaire_client, $post_id);
                update_field('type_de_devis', 'corrige', $post_id);
                update_field('utilisateur', $woo_id_client, $post_id);
                update_field('field_692eab163985b', $woo_id_client, $post_id);
                if (!empty($client_final_id)) {
                    update_field('client_final', $woo_id_client_final, $post_id);
                    update_field('field_698c460ac6d81', $woo_id_client_final, $post_id);
                }
                


                $variations_existantes = get_posts([
                    'post_type'      => 'variation-devis',
                    'post_status'    => 'any',
                    'posts_per_page' => 1,
                    'meta_key'       => 'id_prestashop',
                    'meta_value'     => $id_devis,
                ]);

                if (!empty($variations_existantes)) {

                    // Variation déjà existante
                    $variation_devis_id = $variations_existantes[0]->ID;

                } else {

                    // Créer la variation
                    $variation_devis_id = wp_insert_post([
                        'post_type'   => 'variation-devis',
                        'post_status' => 'publish',
                        'post_author' => $woo_id_client,
                        'post_title'  => 'Variation ' . $software_duration . ' - Devis #' . $post_id,
                    ]);

                    // Enregistrer l'ID PrestaShop
                    update_field('id_prestashop', $id_devis, $variation_devis_id);
                }
               



                if ($is_produit_simple) {

                    // Produit simple
                    $product = wc_get_product($id_produit_id_woocommerce);

                } else {

                    // Produit variable : chercher la variation
                    global $wpdb;

                    $variation_id = $wpdb->get_var(
                        $wpdb->prepare(
                            "
                            SELECT v.ID
                            FROM {$wpdb->posts} v

                            INNER JOIN {$wpdb->postmeta} m1
                                ON m1.post_id = v.ID
                                AND m1.meta_key = 'attribute_pa_software_duration'

                            INNER JOIN {$wpdb->postmeta} m2
                                ON m2.post_id = v.ID
                                AND m2.meta_key = 'attribute_pa_number_of_computers'

                            WHERE v.post_parent = %d
                            AND v.post_type = 'product_variation'
                            AND m1.meta_value LIKE %s
                            AND m2.meta_value = %s

                            LIMIT 1
                            ",
                            $id_produit_id_woocommerce,
                            $duree . '%',
                            (string) $nb_pcs
                        )
                    );

                    if ($variation_id) {
                        $product = wc_get_product($variation_id);
                    } else {
                        $product = false;
                    }
                }

                add_row('produits_de_la_variation', [
                    'produit'  => $product->get_id(),
                    'quantite' => $qte,
                     'prix_propose' => $pu,
                    'duree' => $duree,
                ], $variation_devis_id);

                // Remise revendeur
                if ($remise_revendeur != 0) {

                    update_field('remise_revendeur', $remise_revendeur, $variation_devis_id);
                }

                // Remise renouvellement
                if ($remise_renewal != 0) {

                    update_field('remise_renewal', $remise_renewal, $variation_devis_id);
                }

                // Remise spéciale
                if ($remise_special_1 != 0) {

                   update_field('remise_commerciale', $remise_special_1, $variation_devis_id);
                }

                // Remise cumulée
                if ($remise_cumul != 0) {

                    update_field('remise_cumulee', $remise_cumul, $variation_devis_id);
                }

                 // Remise remise_statutaire
                if ($remise_statutaire != 0) {

                   update_field('remise_statutaire', $remise_statutaire, $variation_devis_id);
                }

                $tax_rate = get_user_meta($woo_id_client, 'tax_rate', true);
                $tax_rate_name = get_user_meta($woo_id_client, 'tax_rate_name', true);

                
                

                update_field('tva', $tax_rate_name, $variation_devis_id);
                update_field('taux_tva', $tax_rate, $variation_devis_id);
                update_field('note_admin', $commentaire_admin, $variation_devis_id);


                $variations_devis_creees[$id_devis] = $variation_devis_id;

                $variations_devis_ids = [];
                $variations_devis_ids[] = $variation_devis_id;

                update_field('variations', $variations_devis_ids, $post_id);


            } else {

                // ==========================================
                // 5. MÊME variation :
                //    ON AJOUTE UNIQUEMENT LE PRODUIT
                // ==========================================

                if ($produit_simple === 1) {

                    // Produit simple
                    $product = wc_get_product($id_produit_id_woocommerce);

                } else {

                    $variation_devis_id = $variations_devis_creees[$id_devis];
                    // Produit variable : chercher la variation
                    global $wpdb;

                    $variation_id = $wpdb->get_var(
                        $wpdb->prepare(
                            "
                            SELECT v.ID
                            FROM {$wpdb->posts} v

                            INNER JOIN {$wpdb->postmeta} m1
                                ON m1.post_id = v.ID
                                AND m1.meta_key = 'attribute_pa_software_duration'

                            INNER JOIN {$wpdb->postmeta} m2
                                ON m2.post_id = v.ID
                                AND m2.meta_key = 'attribute_pa_number_of_computers'

                            WHERE v.post_parent = %d
                            AND v.post_type = 'product_variation'
                            AND m1.meta_value LIKE %s
                            AND m2.meta_value = %s

                            LIMIT 1
                            ",
                            $id_produit_id_woocommerce,
                            $duree . '%',
                            (string) $nb_pcs
                        )
                    );

                    if ($variation_id) {
                        $product = wc_get_product($variation_id);
                    } else {
                        $product = false;
                    }

                    add_row('produits_de_la_variation', [
                        'produit'  => $product->get_id(),
                        'quantite' => $qte,
                         'prix_propose' => $pu,
                        'duree' => $duree,
                    ], $variation_devis_id);

                    
                }



            }


            if ($calculer_formule) {

                alm_calcul_variation($variation_devis_id);
            }
                


            $imported++;

            $line++;
        }

        fclose($handle);

        echo '<div class="notice notice-success">';
        echo '<p>';
        echo '<strong>Import terminé</strong><br>';
        echo 'Lignes demandées : ' . $line_start . ' → ' . $line_end . '<br>';
        echo 'Dernière ligne lue : ' . ($line - 1) . '<br>';
        echo 'Importés : ' . $imported . '<br>';
        echo 'Déjà présents : ' . $skipped . '<br>';
        echo 'NB devis Importées : ' . sizeof($variations_devis_creees) . '<br>';
        echo 'devis importées : ' . json_encode($variations_devis_creees) . '<br>';
        echo 'Erreurs : ' . $errors;
        echo '</p>';
        echo '</div>';
    }