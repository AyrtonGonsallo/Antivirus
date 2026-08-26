<?php

if (!defined('ABSPATH')) {
    exit;
}




function presta_import_clients_revendeurs_page() {


    if (isset($_POST['upload_clients_revendeurs'])) {

        check_admin_referer('presta_upload_clients_revendeurs');

        if (
            empty($_FILES['clients_revendeurs_csv']['tmp_name']) ||
            $_FILES['clients_revendeurs_csv']['error'] !== UPLOAD_ERR_OK
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

        $file = $directory . '/clients_revendeurs.csv';

        if (move_uploaded_file(
            $_FILES['clients_revendeurs_csv']['tmp_name'],
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


    if (isset($_POST['import_clients_revendeurs'])) {

        check_admin_referer('presta_import_clients_revendeurs');

        $line_start = intval($_POST['line_start'] ?? 0);
        $line_end   = intval($_POST['line_end'] ?? 0);

        if ($line_start <= 0 || $line_end < $line_start) {

            echo '<div class="notice notice-error">
                    <p>Plage d\'IDs invalide.</p>
                </div>';

            return;
        }

        presta_import_clients_revendeurs($line_start, $line_end);
    }


    

    if (!current_user_can('manage_options')) {
        return;
    }

    ?>

    <div class="wrap">

        <h1>Import PrestaShop</h1>

        <h2>Clients des revendeurs</h2>

        <form method="post" enctype="multipart/form-data">

            <?php wp_nonce_field('presta_upload_clients_revendeurs'); ?>

            <h2>1. Upload des clients_revendeurs</h2>

            <input
                type="file"
                name="clients_revendeurs_csv"
                accept=".csv"
                required
            >

            <button
                type="submit"
                name="upload_clients_revendeurs"
                class="button button-primary"
            >
                Upload
            </button>

        </form>


        <hr>


        <form method="post">

            <?php wp_nonce_field('presta_import_clients_revendeurs'); ?>

            <h2>2. Import des clients_revendeurs</h2>

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
                name="import_clients_revendeurs"
                class="button button-primary"
            >
                Importer
            </button>

        </form>

       

    </div>

    <?php
}

function presta_import_clients_revendeurs($line_start, $line_end) {

     error_log('line_start : '  . $line_start.' - line_end : '.$line_end);

        $upload_dir = wp_upload_dir();

        $file = $upload_dir['basedir'] . '/presta-import/clients_revendeurs.csv';

        if (!file_exists($file)) {

            echo '<div class="notice notice-error">
                    <p>Le fichier clients_revendeurs.csv est introuvable.</p>
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


         error_log('import clients revendeurs');
         error_log('colone 0 '.$headers[0]);
        error_log('colone 3 '.$headers[3]);
        error_log('colone 5 '.$headers[5]);

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

    

            // =========================
            // IMPORT DU CLIENT de revendeur
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
            $id_revendeur = intval($data['id_revendeur'] ?? 0);

            $presta_id = intval($data['id_client_rvd'] ?? 0);

            

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
                'role'       => 'customer_direct',
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
              update_user_meta($user_id, 'first_name', $first_name);
            update_user_meta($user_id, 'last_name', $last_name);
            update_user_meta($user_id, 'billing_postcode', $code_postal);
            update_user_meta($user_id, 'billing_city', $ville);
            update_user_meta($user_id, 'billing_type_client', $type_client);

            update_user_meta($user_id, 'presta_id', $presta_id );

            error_log('recherche revendeur presta '.$id_revendeur);
           // update_user_meta($user_id, 'revendeur_id', $id_revendeur); cherche l'id de l'user woocomerce par ce champ  update_user_meta($user_id, 'presta_id', $presta_id );
            $user_revendeur_id = get_users([
                'meta_key'   => 'presta_id',
                'meta_value' => $id_revendeur, //leur id
                'number'     => 1,
                'fields'     => 'ids',
            ])[0] ?? 0;

            update_user_meta($user_id, 'revendeur_id', $user_revendeur_id); //le notre
           

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