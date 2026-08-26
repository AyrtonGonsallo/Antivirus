<?php

if (!defined('ABSPATH')) {
    exit;
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

            $presta_id = intval($data['id_devis'] ?? 0);

            // =========================
            // IMPORT DU CLIENT
            // =========================


            /*
            $email = sanitize_email($data['email'] ?? '');

            if (!$email || !is_email($email)) {
                $errors++;
                continue;
            }

            // Client déjà présent
            if (email_exists($email)) {
                $skipped++;
                continue;
            }

            $user_id = wp_create_user(
                $email,
                wp_generate_password(32),
                $email
            );

            if (is_wp_error($user_id)) {
                $errors++;
                continue;
            }

            wp_update_user([
                'ID'         => $user_id,
                'first_name' => sanitize_text_field(
                    $data['prenom'] ?? ''
                ),
                'last_name'  => sanitize_text_field(
                    $data['nom'] ?? ''
                ),
            ]);

            update_user_meta(
                $user_id,
                'billing_address_1',
                sanitize_text_field($data['adresse'] ?? '')
            );

            update_user_meta(
                $user_id,
                'billing_city',
                sanitize_text_field($data['ville'] ?? '')
            );

            update_user_meta(
                $user_id,
                'billing_postcode',
                sanitize_text_field($data['cp'] ?? '')
            );

            update_user_meta(
                $user_id,
                'billing_country',
                sanitize_text_field($data['code_iso'] ?? '')
            );

            update_user_meta(
                $user_id,
                'billing_phone',
                sanitize_text_field($data['tel'] ?? '')
            );

            // Très important pour retrouver le devis Presta
            update_user_meta(
                $user_id,
                'prestashop_id',
                $presta_id
            );
            */

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
        echo 'Erreurs : ' . $errors;
        echo '</p>';
        echo '</div>';
    }