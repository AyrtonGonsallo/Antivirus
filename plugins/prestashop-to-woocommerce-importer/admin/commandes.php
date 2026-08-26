<?php

if (!defined('ABSPATH')) {
    exit;
}




function presta_import_commandes_page() {


    if (isset($_POST['upload_commandes'])) {

        check_admin_referer('presta_upload_commandes');

        if (
            empty($_FILES['commandes_csv']['tmp_name']) ||
            $_FILES['commandes_csv']['error'] !== UPLOAD_ERR_OK
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

        $file = $directory . '/commandes.csv';

        if (move_uploaded_file(
            $_FILES['commandes_csv']['tmp_name'],
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


    if (isset($_POST['import_commandes'])) {

        check_admin_referer('presta_import_commandes');

        $line_start = intval($_POST['line_start'] ?? 0);
        $line_end   = intval($_POST['line_end'] ?? 0);

        if ($line_start <= 0 || $line_end < $line_start) {

            echo '<div class="notice notice-error">
                    <p>Plage d\'IDs invalide.</p>
                </div>';

            return;
        }

        presta_import_commandes($line_start, $line_end);
    }


    

    if (!current_user_can('manage_options')) {
        return;
    }

    ?>

    <div class="wrap">

        <h1>Import PrestaShop</h1>

        <h2>Commandes</h2>

        <form method="post" enctype="multipart/form-data">

            <?php wp_nonce_field('presta_upload_commandes'); ?>

            <h2>1. Upload des commandes</h2>

            <input
                type="file"
                name="commandes_csv"
                accept=".csv"
                required
            >

            <button
                type="submit"
                name="upload_commandes"
                class="button button-primary"
            >
                Upload
            </button>

        </form>


        <hr>


        <form method="post">

            <?php wp_nonce_field('presta_import_commandes'); ?>

            <h2>2. Import des commandes</h2>

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
                name="import_commandes"
                class="button button-primary"
            >
                Importer
            </button>

        </form>

       

    </div>

    <?php
}

function presta_import_commandes($line_start, $line_end) {

     error_log('line_start : '  . $line_start.' - line_end : '.$line_end);

        $upload_dir = wp_upload_dir();

        $file = $upload_dir['basedir'] . '/presta-import/commandes.csv';

        if (!file_exists($file)) {

            echo '<div class="notice notice-error">
                    <p>Le fichier commandes.csv est introuvable.</p>
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
        

        error_log('import commandes ');
        error_log('colone 0 '.$headers[0]);
        error_log('colone 3 '.$headers[3]);
        error_log('colone 5 '.$headers[5]);

        $commandes_creees = [];

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

            $id_commande = intval($data['id_commande'] ?? 0);

            // =========================
            // IMPORT DE LA COMMANDE
            // =========================

            $id_produit = intval($data['id_produit'] ?? 0);
            $id_client = intval($data['id_client'] ?? 0);
            $id_client_rvd = intval($data['id_client_rvd'] ?? 0);
            $id_revendeur = intval($data['id_revendeur'] ?? 0);
            $id_produit_id_woocommerce = intval($data['id_woocommerce'] ?? 0);
            $dt_end = sanitize_text_field($data['dt_end'] ?? ''); //2034-02-06
            $dt_commande = sanitize_text_field($data['dt_commande'] ?? ''); //2034-02-06
            $statut_commande = sanitize_text_field($data['statut_commande'] ?? '');
            $mode_paiement = sanitize_text_field($data['mode_paiement'] ?? '');
            $paiement_ok = intval($data['paiement_ok'] ?? 0);
            $qte = intval($data['qte'] ?? 0);
            $duree = intval($data['duree'] ?? 0);
            $nb_pcs = intval($data['nb_pcs'] ?? 0);
            $produit_simple = intval($data['produit_simple'] ?? 0); 
            $appliquer_remise = filter_var(
                $data['appliquer_remise'] ?? false,
                FILTER_VALIDATE_BOOLEAN
            );
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
            $duree = intval($data['duree'] ?? 0);


            //algortithme
            //chaque ligne a produit et commande et remises id_commande	id_produit remise_revendeur	remise_statutaire	remise_renewal	remise_special_1	remise_cumul mode_paiement	paiement_ok	id_client
            //1) pour chaque ligne creer la commande avec id_commande, ajouter le client comme champ caché et ajouter le premier produit avec son prix custom puis ajouter les remises 
            //2) la ligne suivante si id_commande est le meme juste ajouter le produit avec son prix custom mais ne plus ajouter les remises (elles sont les memes que sur le premier) sinon creer une autre commande et passer a 1)
            

            error_log('produit '.$id_produit);
            error_log('is produit simple '.$is_produit_simple);
            error_log('appliquer_remise '.$appliquer_remise);

            if (!isset($commandes_creees[$id_commande])) {

                // ==========================================
                // 1. CREATION DE LA COMMANDE
                // ==========================================

                $order = wc_create_order();

                // Client WooCommerce
                // À adapter selon ta logique de correspondance presta_id
                $user_id = get_users([
                    'meta_key'   => 'presta_id',
                    'meta_value' => $id_client,
                    'number'     => 1,
                    'fields'     => 'ID',
                ]);

                if (!empty($user_id)) {
                    $order->set_customer_id($user_id[0]);
                }

                // ==========================================
                // 2. AJOUT DU PREMIER PRODUIT
                // ==========================================

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

                if ($product) {

                    $item_id = $order->add_product(
                        $product,
                        $qte,
                        [
                            'subtotal' => $total_lig_ht,
                            'total'    => $total_lig_ht,
                        ]
                    );

                    // Informations supplémentaires sur la ligne
                    if ($item_id) {
                        $item = $order->get_item($item_id);

                        $item->add_meta_data('presta_id_produit', $id_produit, true);
                        $item->add_meta_data('duree', $duree, true);
                        $item->add_meta_data('dt_end', $dt_end, true);

                        $item->save();
                    }
                }

                // ==========================================
                // 3. INFORMATIONS DE LA COMMANDE
                // ==========================================

                $order->update_meta_data('_presta_id_commande', $id_commande);
                $order->update_meta_data('_presta_id_client', $id_client);

                if ($id_client_rvd) {
                    $order->update_meta_data('_presta_id_client_rvd', $id_client_rvd);
                }

                if ($id_revendeur) {
                    $order->update_meta_data('_presta_id_revendeur', $id_revendeur);
                }

                if($paiement_ok){
                    $order->set_payment_method('stripe');
                    $order->set_payment_method_title('Carte de crédit/débit');
                    $order->update_meta_data('_mode_paiement', $mode_paiement);
                    $order->update_meta_data('_paiement_ok', $paiement_ok);

                }
                

                
                $order->calculate_totals();
                $order->save();

                // On mémorise la commande
                $commandes_creees[$id_commande] = $order->get_id();

            } else {

                // ==========================================
                // 5. MÊME id_commande :
                //    ON AJOUTE UNIQUEMENT LE PRODUIT
                // ==========================================

                $order_id = $commandes_creees[$id_commande];

                $order = wc_get_order($order_id);

                if ($produit_simple === 1) {

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

                if ($order && $product) {

                    $item_id = $order->add_product(
                        $product,
                        $qte,
                        [
                            'subtotal' => $total_lig_ht,
                            'total'    => $total_lig_ht,
                        ]
                    );

                    if ($item_id) {

                        $item = $order->get_item($item_id);

                        $item->add_meta_data('presta_id_produit', $id_produit, true);
                        $item->add_meta_data('duree', $duree, true);
                        $item->add_meta_data('dt_end', $dt_end, true);

                        $item->save();
                    }

                    $order->calculate_totals();
                    $order->save();
                }
            }

            if ($appliquer_remise) {
                // ==========================================
                // 4. REMISES
                // ==========================================

                $order->update_meta_data('_remise_revendeur', $remise_revendeur);
                $order->update_meta_data('_remise_statutaire', $remise_statutaire);
                $order->update_meta_data('_remise_renewal', $remise_renewal);
                $order->update_meta_data('_remise_special_1', $remise_special_1);
                $order->update_meta_data('_remise_cumul', $remise_cumul);

                // Montant initial de la commande
                $montant = $order->get_subtotal();

                // Remise revendeur
                if ($remise_revendeur != 0) {

                    $montant_remise = $montant * ($remise_revendeur / 100);
                    $montant -= $montant_remise;

                    $fee = new WC_Order_Item_Fee();
                    $fee->set_name('Remise revendeur (' . $remise_revendeur . '%)');
                    $fee->set_amount(-abs($montant_remise));
                    $fee->set_total(-abs($montant_remise));
                    $order->add_item($fee);
                }

                // Remise renouvellement
                if ($remise_renewal != 0) {

                    $montant_remise = $montant * ($remise_renewal / 100);
                    $montant -= $montant_remise;

                    $fee = new WC_Order_Item_Fee();
                    $fee->set_name('Remise renouvellement de licences (' . $remise_renewal . '%)');
                    $fee->set_amount(-abs($montant_remise));
                    $fee->set_total(-abs($montant_remise));
                    $order->add_item($fee);
                }

                // Remise spéciale
                if ($remise_special_1 != 0) {

                    $montant_remise = $montant * ($remise_special_1 / 100);
                    $montant -= $montant_remise;

                    $fee = new WC_Order_Item_Fee();
                    $fee->set_name('Autre remise (' . $remise_special_1 . '%)');
                    $fee->set_amount(-abs($montant_remise));
                    $fee->set_total(-abs($montant_remise));
                    $order->add_item($fee);
                }

                // Remise cumulée
                if ($remise_cumul != 0) {

                    $montant_remise = $montant * ($remise_cumul / 100);
                    $montant -= $montant_remise;

                    $fee = new WC_Order_Item_Fee();
                    $fee->set_name('Remise cumulée (' . $remise_cumul . '%)');
                    $fee->set_amount(-abs($montant_remise));
                    $fee->set_total(-abs($montant_remise));
                    $order->add_item($fee);
                }
                 $order->calculate_totals();
                $order->save();
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
        echo 'Produits Importés : ' . $imported . '<br>';
        echo 'NB Commandes Importées : ' . sizeof($commandes_creees) . '<br>';
        echo 'Commandes importées : ' . json_encode($commandes_creees) . '<br>';
        echo 'Déjà présents : ' . $skipped . '<br>';
        echo 'Erreurs : ' . $errors;
        echo '</p>';
        echo '</div>';
    }