<?php

if (!defined('ABSPATH')) {
    exit;
}




function presta_import_commandes_mode_paiements_page() {


    if (isset($_POST['upload_commandes_mode_paiements'])) {

        check_admin_referer('presta_upload_commandes_mode_paiements');

        if (
            empty($_FILES['commandes_mode_paiements_csv']['tmp_name']) ||
            $_FILES['commandes_mode_paiements_csv']['error'] !== UPLOAD_ERR_OK
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

        $file = $directory . '/commandes_mode_paiements.csv';

        if (move_uploaded_file(
            $_FILES['commandes_mode_paiements_csv']['tmp_name'],
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


    if (isset($_POST['import_commandes_mode_paiements'])) {

        check_admin_referer('presta_import_commandes_mode_paiements');

        $line_start = intval($_POST['line_start'] ?? 0);
        $line_end   = intval($_POST['line_end'] ?? 0);

        if ($line_start <= 0 || $line_end < $line_start) {

            echo '<div class="notice notice-error">
                    <p>Plage d\'IDs invalide.</p>
                </div>';

            return;
        }

        presta_import_commandes_mode_paiements($line_start, $line_end);
    }


    

    if (!current_user_can('manage_options')) {
        return;
    }

    ?>

    <div class="wrap">

        <h1>Import PrestaShop</h1>

        <h2>Commandes mode de paiement</h2>

        <form method="post" enctype="multipart/form-data">

            <?php wp_nonce_field('presta_upload_commandes_mode_paiements'); ?>

            <h2>1. Upload des commandes avec mode de paiement</h2>

            <input
                type="file"
                name="commandes_mode_paiements_csv"
                accept=".csv"
                required
            >

            <button
                type="submit"
                name="upload_commandes_mode_paiements"
                class="button button-primary"
            >
                Upload
            </button>

        </form>


        <hr>


        <form method="post">

            <?php wp_nonce_field('presta_import_commandes_mode_paiements'); ?>

            <h2>2. Mise à jour des mode de paiements</h2>

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
                name="import_commandes_mode_paiements"
                class="button button-primary"
            >
                Mettre à jour
            </button>

        </form>

       

    </div>

    <?php
}

function presta_import_commandes_mode_paiements($line_start, $line_end) {


     error_log('line_start : '  . $line_start.' - line_end : '.$line_end);

        $upload_dir = wp_upload_dir();

        $file = $upload_dir['basedir'] . '/presta-import/commandes_mode_paiements.csv';

        if (!file_exists($file)) {

            echo '<div class="notice notice-error">
                    <p>Le fichier commandes_mode_paiements.csv est introuvable.</p>
                </div>';

            return;
        }

        $handle = fopen($file, 'r');

        if (!$handle) {
            return;
        }

        // Première ligne = colonnes
        $headers = fgetcsv($handle, 0, ';', '"', '');

        foreach ($headers as &$header) {
            $header = trim($header, " \t\n\r\0\x0B\xEF\xBB\xBF\"");
        }
        unset($header);
        

        error_log('import commandes ');
        error_log('colone 0 '.$headers[0]);
        error_log('colone 3 '.$headers[3]);
        error_log('colone 5 '.$headers[5]);

        $commandes_mises_a_jour = [];

        $line = 1;

        $updated = 0;
        $skipped  = 0;
        $errors   = 0;

        while (($row = fgetcsv($handle, 0, ';', '"', '')) !== false) {

            // Avant la ligne de départ
            if ($line < $line_start) {
                $line++;
                continue;
            }

            // Après la ligne de fin
            if ($line > $line_end) {
                break;
            }

            try {
                $data = array_combine($headers, $row);
            } catch (Throwable $e) {

                restore_error_handler();

                error_log('Erreur CSV ligne ' . $line . ' : ' . $e->getMessage());
                error_log('Headers : ' . print_r($headers, true));
                error_log('Row : ' . print_r($row, true));
                

                $errors++;
                $line++;
                continue;
            }

            if ($data === false) {
                $errors++;
                $line++;
                continue;
            }

            

            // =========================
            // IMPORT DE LA COMMANDE
            // =========================

            $id_commande = intval($data['id_commande'] ?? 0);
            $id_client = intval($data['id_client'] ?? 0);
            $id_client_rvd = intval($data['id_client_rvd'] ?? 0);
            $id_revendeur = intval($data['id_revendeur'] ?? 0);
            $statut_commande = sanitize_text_field($data['statut_commande'] ?? '');
            $mode_paiement = sanitize_text_field($data['mode_paiement'] ?? '');
            $paiement_ok = intval($data['paiement_ok'] ?? 0);
           
        

            error_log('id_client '.$id_client);
            error_log('id_client_rvd '.$id_client_rvd);
            error_log('id_revendeur '.$id_revendeur);
            error_log('statut_commande '.$statut_commande);
            error_log('mode_paiement '.$mode_paiement);
            error_log('paiement_ok '.$paiement_ok);

            

            $orders = wc_get_orders([
                'limit'      => 1,
                'meta_key'   => '_presta_id_commande',
                'meta_value' => $id_commande,
            ]);

            if (!empty($orders)) {
                $order = $orders[0];
                $order->update_meta_data('_mode_paiement', $mode_paiement);
                $order->update_meta_data('_paiement_ok', $paiement_ok);
                                
                //Carte de crédit/débit - stripe,Virement bancaire - bacs,	Paiements par chèque - cheque,Paiement par mandat administratif - paiement_mandat_administratif,Paiement en fin de mois - paiement_differe
                $payment_method = '';
                $payment_method_title = '';

                switch ($mode_paiement) {
                    case 'Carte Bancaire':
                        # code...
                        $payment_method = 'stripe';
                        $payment_method_title = 'Carte de crédit/débit';
                        break;
                    case 'Paiement fin de mois':
                        # code...
                        $payment_method = 'paiement_differe';
                        $payment_method_title = 'Paiement en fin de mois';
                        $order->update_meta_data('_paiement_differe', 'yes');
                        $order->update_meta_data('_paiement_differe_date', date('Y-m-d H:i:s'));
                        break;
                    case 'PayPal':
                        # code...
                        $payment_method = 'stripe';
                        $payment_method_title = 'Carte de crédit/débit';
                        break;
                    case 'Virement Bancaire':
                        # code...
                        $payment_method = 'bacs';
                        $payment_method_title = 'Virement bancaire';
                        break;
                    case 'Chèque Bancaire':
                        # code...
                        $payment_method = 'cheque';
                        $payment_method_title = 'Paiements par chèque';
                        break;
                    case 'Mandat administratif':
                        # code...
                        $payment_method = 'paiement_mandat_administratif';
                        $payment_method_title = 'Paiement par mandat administratif';
                        break;
                    
                    
                    default:
                        # code...
                        break;
                }

                $order->set_payment_method($payment_method);
                $order->set_payment_method_title($payment_method_title);
                $order->save();

                $subscriptions = wcs_get_subscriptions_for_order($order->get_id(), array('order_type' => 'parent'));
 

                if (!empty($subscriptions)) {
                    foreach ($subscriptions as $subscription) {
                        $subscription->set_payment_method($payment_method);
                        $subscription->set_payment_method_title($payment_method_title);
                        $subscription->save();
                    }
                }


                $commandes_mises_a_jour[$id_commande] = $order->get_id();
                $updated++;


            } else {
                //faire un truc areter ?
                $errors++;
            }

                

      

            $line++;
        }

        fclose($handle);

        echo '<div class="notice notice-success">';
        echo '<p>';
        echo '<strong>Mise a jour terminée</strong><br>';
        echo 'Lignes demandées : ' . $line_start . ' → ' . $line_end . '<br>';
        echo 'Dernière ligne lue : ' . ($line - 1) . '<br>';
        echo 'Commandes mises à jour : ' . $updated . '<br>';
        echo 'NB Commandes mises à jour : ' . sizeof($commandes_mises_a_jour) . '<br>';
        echo 'Commandes mises à jour : ' . json_encode($commandes_mises_a_jour) . '<br>';
        echo 'Erreurs : ' . $errors;
        echo '</p>';
        echo '</div>';
    }