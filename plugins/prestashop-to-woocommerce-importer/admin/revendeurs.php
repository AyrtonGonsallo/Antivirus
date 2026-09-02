<?php

if (!defined('ABSPATH')) {
    exit;
}




function presta_import_revendeurs_page() {


    if (isset($_POST['upload_revendeurs'])) {

        check_admin_referer('presta_upload_revendeurs');

        if (
            empty($_FILES['revendeurs_csv']['tmp_name']) ||
            $_FILES['revendeurs_csv']['error'] !== UPLOAD_ERR_OK
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

        $file = $directory . '/revendeurs.csv';

        if (move_uploaded_file(
            $_FILES['revendeurs_csv']['tmp_name'],
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


    if (isset($_POST['import_revendeurs'])) {

        check_admin_referer('presta_import_revendeurs');

        $line_start = intval($_POST['line_start'] ?? 0);
        $line_end   = intval($_POST['line_end'] ?? 0);

        if ($line_start <= 0 || $line_end < $line_start) {

            echo '<div class="notice notice-error">
                    <p>Plage d\'IDs invalide.</p>
                </div>';

            return;
        }

        presta_import_revendeurs($line_start, $line_end);
    }


    

    if (!current_user_can('manage_options')) {
        return;
    }

    ?>

    <div class="wrap">

        <h1>Import PrestaShop</h1>

        <h2>Revendeurs</h2>

        <form method="post" enctype="multipart/form-data">

            <?php wp_nonce_field('presta_upload_revendeurs'); ?>

            <h2>1. Upload des revendeurs</h2>

            <input
                type="file"
                name="revendeurs_csv"
                accept=".csv"
                required
            >

            <button
                type="submit"
                name="upload_revendeurs"
                class="button button-primary"
            >
                Upload
            </button>

        </form>


        <hr>


        <form method="post">

            <?php wp_nonce_field('presta_import_revendeurs'); ?>

            <h2>2. Import des revendeurs</h2>

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
                name="import_revendeurs"
                class="button button-primary"
            >
                Importer
            </button>

        </form>

       

    </div>

    <?php
}

function presta_import_revendeurs($line_start, $line_end) {

     error_log('line_start : '  . $line_start.' - line_end : '.$line_end);

        $upload_dir = wp_upload_dir();

        $file = $upload_dir['basedir'] . '/presta-import/revendeurs.csv';

        if (!file_exists($file)) {

            echo '<div class="notice notice-error">
                    <p>Le fichier revendeurs.csv est introuvable.</p>
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

         error_log('import revendeurs ');
         error_log('colone 0 '.$headers[0]);
        error_log('colone 3 '.$headers[3]);
        error_log('colone 5 '.$headers[5]);

        $line = 1;

        $imported = 0;
        $skipped  = 0;
        $errors   = 0;

        $lignes = [];

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

       

             // =========================
            // IMPORT DU revendeur
            // =========================


            $email = sanitize_email($data['email'] ?? '');
            $role = sanitize_text_field($data['role'] ?? '');
            $type_client = sanitize_text_field($data['type_client'] ?? '');
            $denomination = sanitize_text_field($data['denomination'] ?? '');

            $genre = sanitize_text_field($data['genre'] ?? '');
            $civilite = sanitize_text_field($data['civilite'] ?? '');
            $first_name = sanitize_text_field($data['first_name'] ?? '');
            $last_name = sanitize_text_field($data['last_name'] ?? '');
            $display_name = sanitize_text_field($data['display_name'] ?? '');

            $billing_address_1 = sanitize_text_field($data['billing_address_1'] ?? '');
            $ville = sanitize_text_field($data['ville'] ?? '');
            $code_postal = sanitize_text_field($data['code_postal'] ?? '');
            $pays = sanitize_text_field($data['pays'] ?? '');
            $billing_phone = sanitize_text_field($data['billing_phone'] ?? '');
            $fax = sanitize_text_field($data['fax'] ?? '');
            $new_account_regime_tva = sanitize_text_field($data['new_account_regime_tva'] ?? '');
            $new_account_tva_intra_full = sanitize_text_field($data['new_account_tva_intra'] ?? '');//SI64053148
            $new_account_prefixe_tva = substr($new_account_tva_intra_full, 0, 2);//SI
            $new_account_tva_intra = substr($new_account_tva_intra_full, 2);//64053148

            $paiement_en_fin_de_mois = intval($data['paiement_en_fin_de_mois'] ?? 0);
            $remise_rvd = intval($data['remise_rvd'] ?? 0);
           

            $presta_id = intval($data['id_client'] ?? 0);

            $lignes[$presta_id] = $email;

            if (!$email || !is_email($email)) {
                $errors++;
                continue;
            }

            // Client déjà présent
            if (email_exists($email)) {
                $skipped++;
                continue;
            }

            // Création du compte client
            $password = wp_generate_password( 12, false ); // mot de passe aléatoire
            $userdata = [
                'user_login' => $email,
                'user_pass'  => $password,
                'user_email' => $email,
                'first_name' => $first_name,
                'last_name'  => $last_name,
                'role'       => 'customer_revendeur',
            ];

            $user_id = wp_insert_user($userdata);

            if (is_wp_error($user_id)) {
                $errors++;
                continue;
            }

    
            update_user_meta($user_id, 'type_client', $type_client);
            update_user_meta($user_id, 'denomination', $denomination);
            update_user_meta($user_id, 'billing_societe', $denomination);
            update_user_meta($user_id, 'genre', $genre);
            update_user_meta($user_id, 'billing_phone', $billing_phone);
            update_user_meta($user_id, 'civilite', $civilite);
            update_user_meta($user_id, 'fax', $fax);
            update_user_meta($user_id, 'billing_address_1', $billing_address_1);
            update_user_meta($user_id, 'ville', $ville);
            update_user_meta($user_id, 'code_postal', $code_postal);
            update_user_meta($user_id, 'pays', $pays);
            update_user_meta($user_id, 'billing_country', ( $pays ) );
            update_user_meta($user_id, 'shipping_country', ( $pays ) );
            update_user_meta($user_id, 'billing_first_name', $first_name);
            update_user_meta($user_id, 'billing_last_name', $last_name);
            update_user_meta($user_id, 'billing_postcode', $code_postal);
            update_user_meta($user_id, 'billing_city', $ville);
            update_user_meta($user_id, 'billing_type_client', $type_client);
            update_user_meta($user_id, 'first_name', $first_name);
            update_user_meta($user_id, 'last_name', $last_name);
            update_user_meta( $user_id, 'new_revendeur_account_regime_tva', $new_account_regime_tva );
            update_user_meta($user_id, 'new_revendeur_account_tva_intra', ($new_account_tva_intra));
            update_user_meta($user_id, 'new_revendeur_account_prefixe_tva', ($new_account_prefixe_tva));
            update_user_meta($user_id, 'paiement_en_fin_de_mois', $paiement_en_fin_de_mois );
            update_user_meta($user_id, 'presta_id', $presta_id );


            if( isset($new_account_regime_tva)){
                $regime=$new_account_regime_tva;
                if($regime=="HT_UE"){
                   
                    //ht ue taxe
                    update_user_meta($user_id, 'tefw_exempt', 1);
                    update_user_meta($user_id, 'tefw_exempt_name', $first_name.' '.$last_name);
                    update_user_meta($user_id, 'tefw_exempt_reason', 'Exonération automatique compte "Professionnel, Association ou Institution" pour un pays dans l\'ue');
                    update_user_meta($user_id, 'tefw_exempt_status', 'approved');
                    update_user_meta($user_id, 'tax_rate_name', 'Pas de Tva');
                    update_user_meta($user_id, 'tax_rate', 0);

                }else if ($regime=="HT"){
                
                    //ht taxe
                    update_user_meta($user_id, 'tefw_exempt', 1);
                    update_user_meta($user_id, 'tefw_exempt_name', $first_name.' '.$last_name);
                    update_user_meta($user_id, 'tefw_exempt_reason', 'Exonération automatique compte "Professionnel, Association ou Institution" pour un pays hors UE');
                    update_user_meta($user_id, 'tefw_exempt_status', 'approved');
                    update_user_meta($user_id, 'tax_rate_name', 'Pas de Tva');
                    update_user_meta($user_id, 'tax_rate', 0);

                }
                else if ($regime=="TVA"){
                
                    //trouver taxe

                    global $wpdb;

                    $taxe = $wpdb->get_row(
                        $wpdb->prepare(
                            "
                            SELECT tax_rate, tax_rate_name
                            FROM antied_woocommerce_tax_rates
                            WHERE tax_rate_country LIKE %s
                            ORDER BY tax_rate_id DESC
                            LIMIT 1
                            ",
                            $pays
                        )
                    );

                    $tax_rate = $taxe->tax_rate ?? null;
                    $tax_rate_name = $taxe->tax_rate_name ?? null;

                 
                    update_user_meta($user_id, 'tax_rate_name', $tax_rate_name);
                    update_user_meta($user_id, 'tax_rate', $tax_rate);
                   
                }

            }

            
            // 1️⃣ Créer la remise CPT
            $remise_id = wp_insert_post([
                'post_type'   => 'remise',
                'post_title'  => "Demande de remise : revendeur - Utilisateur $user_id",
                'post_status' => 'publish',
                'post_author' => $user_id,
            ]);

        

            // 2️⃣ Champs ACF
            update_field('utilisateur', $user_id, $remise_id);
            update_field('compte', [$user_id], $remise_id);
            update_field('statut', 'validee', $remise_id);
            update_field('type', 'revendeur - '.(string)$remise_rvd.' %', $remise_id);
            update_field('pourcentage', $remise_rvd, $remise_id);
            update_field('date_de_creation', current_time('d/m/Y g:i a'), $remise_id);
            $expiration = date('Y-m-d H:i:s', strtotime('+1 year'));
            update_field('date_dexpiration', $expiration, $remise_id);


           
           

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
        echo 'nb clients importées : ' . sizeof($lignes) . '<br>';
        echo 'clients importées : ' . json_encode($lignes) . '<br>';
        echo 'Déjà présents : ' . $skipped . '<br>';
        echo 'Erreurs : ' . $errors;
        echo '</p>';
        echo '</div>';
    }