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
        $headers = fgetcsv($handle, 0, ';', '"', '');

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

            $id_commande = intval($data['id_commande'] ?? 0);

            // =========================
            // IMPORT DE LA COMMANDE
            // =========================

            $renew_in = intval($data['renew_in'] ?? 0);
            $renew_out = intval($data['renew_out'] ?? 0);
            $id_produit = intval($data['id_produit'] ?? 0);
            $id_client = intval($data['id_client'] ?? 0);
            $id_client_rvd = intval($data['id_client_rvd'] ?? 0);
            $id_revendeur = intval($data['id_revendeur'] ?? 0);
            $id_produit_id_woocommerce = intval($data['id_woocommerce'] ?? 0);
            $dt_end = sanitize_text_field($data['dt_end'] ?? ''); //2034-02-06
            $dt_commande = sanitize_text_field($data['dt_commande'] ?? ''); //2034-02-06
            $dt_expire = sanitize_text_field($data['dt_expire'] ?? ''); //2026-09-30 00:00:00
            $statut_commande = sanitize_text_field($data['statut_commande'] ?? '');
            $mode_paiement = sanitize_text_field($data['mode_paiement'] ?? '');
            $type_compte = sanitize_text_field($data['type_compte'] ?? '');
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
            $remise_statutaire = floatval($data['remise_statutaire'] ?? 0);
            $remise_renewal = floatval($data['remise_renewal'] ?? 0);
            $remise_commercial = floatval($data['remise_commercial'] ?? 0);
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
            error_log('commande id '.$id_commande);
            error_log('renew_in '.$renew_in);
            error_log('renew_out '.$renew_out);

            if (!isset($commandes_creees[$id_commande])) {

                // ==========================================
                // 1. CREATION DE LA COMMANDE SI ELLE EXISTE PAS DEJA
                // ==========================================

                $orders = wc_get_orders([
                    'limit'      => 1,
                    'meta_key'   => '_presta_id_commande',
                    'meta_value' => $id_commande,
                ]);

                if (!empty($orders)) {
                    $order = $orders[0];
                } else {
                    $order = wc_create_order();
                }

                if ($dt_commande) {
                    $date = new WC_DateTime($dt_commande);
                    error_log('date commande '.$date->format('Y-m-d H:i:s'));
                    $order->set_date_created($date);
                }

                // Client WooCommerce
                // À adapter selon ta logique de correspondance presta_id
                $user_id = get_users([
                    'meta_key'   => 'presta_id',
                    'meta_value' => $id_client,
                    'number'     => 1,
                    'fields'     => 'ID',
                ]);

                if (!empty($user_id)) {
                    $customer_id = (int) $user_id[0];

                    $order->set_customer_id($customer_id);

                    $order->set_billing_company(
                        get_user_meta($customer_id, 'billing_societe', true)
                    );

                    $order->set_billing_phone(
                        get_user_meta($customer_id, 'billing_phone', true)
                    );

                    $order->set_billing_address_1(
                        get_user_meta($customer_id, 'billing_address_1', true)
                    );

                    $order->set_billing_city(
                        get_user_meta($customer_id, 'billing_city', true)
                    );

                    $order->set_billing_postcode(
                        get_user_meta($customer_id, 'billing_postcode', true)
                    );

                    $order->set_billing_country(
                        get_user_meta($customer_id, 'billing_country', true)
                    );

                    $order->set_billing_first_name(
                        get_user_meta($customer_id, 'billing_first_name', true)
                    );

                    $order->set_billing_last_name(
                        get_user_meta($customer_id, 'billing_last_name', true)
                    );

                    // Si tu as également l'email du client
                    $user = get_userdata($customer_id);

                    if ($user) {
                        $order->set_billing_email($user->user_email);
                    }
                    
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

                $user_revendeur_id = get_users([
                     'role'       => 'customer_revendeur',
                    'meta_key'   => 'presta_id',
                    'meta_value' => $id_revendeur, //leur id
                    'number'     => 1,
                    'fields'     => 'ids',
                ])[0] ?? 0;
                
                if ($id_client_rvd) {
                    // Client final WooCommerce
                    // À adapter selon ta logique de correspondance presta_id
                    $client_final_id = get_users([
                        'meta_query' => [
                            [
                                'key'     => 'presta_id',
                                'value'   => $id_client_rvd,
                                'compare' => '=',
                            ],
                            [
                                'key'     => 'revendeur_id',
                                'value'   => $user_revendeur_id,
                                'compare' => '=',
                            ],
                        ],
                        'number'     => 1,
                        'fields'     => 'ID',
                    ]);

                    if (!empty($client_final_id)) {
                        $order->update_meta_data('client_final', $client_final_id[0]);
                    }
                    
                
                    $order->update_meta_data('_presta_id_client_rvd', $id_client_rvd);
                }

                switch ($statut_commande) {
                    case 'Annulée':
                        $order->update_status('cancelled');
                        break;
                    case 'Terminée':
                        $order->update_status('completed');
                        break;
                    case 'En attente':
                        $order->update_status('pending');
                        break;
                    
                    default:
                        $order->update_status('pending');
                        break;
                }

               

                if ($id_revendeur) {
                    $order->update_meta_data('_presta_id_revendeur', $id_revendeur);
                }

               
                   
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
                $order->update_meta_data('_remise_commercial', $remise_commercial);
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

                if ($remise_commercial != 0) {

                    $montant_remise = $montant * ($remise_commercial / 100);
                    $montant -= $montant_remise;

                    $fee = new WC_Order_Item_Fee();
                    $fee->set_name('Remise commerciale (' . $remise_commercial . '%)');
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

                if ($remise_statutaire != 0) {

                    $montant_remise = $montant * ($remise_statutaire / 100);
                    $montant -= $montant_remise;

                    // Détermination du libellé de la remise statutaire
                    if ((float) $remise_statutaire === 50.0 && $type_compte === 'PRO') {
                        $nom_remise = 'Remise Établissements scolaires et associations -50%';
                    } elseif ((float) $remise_statutaire === 30.0 && $type_compte === 'PRO') {
                        $nom_remise = 'Remise Administrations et mairies -30%';
                    } else {
                        $nom_remise = 'Remise liée au statut -' . $remise_statutaire . '%';
                    }

                    $fee = new WC_Order_Item_Fee();
                    $fee->set_name($nom_remise);
                    $fee->set_amount(-abs($montant_remise));
                    $fee->set_total(-abs($montant_remise));
                    $order->add_item($fee);
                }


                // Remise cumulée
                if ($remise_cumul != 0) {

                    $montant_remise = $montant * ($remise_cumul / 100);
                    $montant -= $montant_remise;

                    // Détermination du libellé de la remise cumulée
                    if ((float) $remise_cumul === 50.0) {
                        $nom_remise = 'Remise Renouvellement de licences GOUV -50%';
                    } elseif ((float) $remise_cumul === 60.0) {
                        $nom_remise = 'Remise Renouvellement de licences EDU -60%';
                    } else {
                        $nom_remise = 'Remise cumulée -' . $remise_cumul . '%';
                    }

                    $fee = new WC_Order_Item_Fee();
                    $fee->set_name($nom_remise);
                    $fee->set_amount(-abs($montant_remise));
                    $fee->set_total(-abs($montant_remise));
                    $order->add_item($fee);
                }

                $order->calculate_totals();
                $order->save();
            

                $order_id = $commandes_creees[$id_commande];

                //sequence abonements
                //l'objet $order existe deja

                $date_now = new WC_DateTime();
                error_log('date du jour '.$date_now->format('Y-m-d H:i:s'));

                if ($dt_commande) {
                    //$duree est celle de l'abonnement 1,2,3 il faut l'ajouter en aneee a la date de la commande
                    $date_debut = new WC_DateTime($dt_commande);
                    error_log('date_debut commande '.$date_debut->format('Y-m-d H:i:s'));
                }

                if ($dt_expire) {//celle de la licence
                    //$duree est celle de l'abonnement 1,2,3 il faut l'ajouter en aneee a la date de la commande
                    $date_expiration = new WC_DateTime($dt_expire);
                    $date_prochain_paiement = clone $date_expiration;
                   // $date_prochain_paiement->modify('+' . $duree . ' years');
                    error_log('date_prochain_paiement_licence '.$date_prochain_paiement->format('Y-m-d H:i:s'));
                    $dateLimite = clone $date_expiration;
                    $dateLimite->modify('+3 months');
                }else{///prendre la date de commande plus la duree
                    $date_expiration = new WC_DateTime($dt_commande);
                    $date_prochain_paiement = clone $date_expiration;
                    $date_prochain_paiement->modify('+' . $duree . ' years');
                    error_log('date_prochain_paiement_commande '.$date_prochain_paiement->format('Y-m-d H:i:s'));
                    $dateLimite = clone $date_prochain_paiement;
                }
                
             
                

                error_log('dateLimite '.$dateLimite->format('Y-m-d H:i:s'));
                error_log('date_now '.$date_now->format('Y-m-d H:i:s'));

                if (($date_now <= $dateLimite) && ($renew_out==0)) { 
                    //on créé un abonement en comparant $date_prochain_paiement a la date du jour
                    error_log('la dateLimite est supérieure a la date du jour donc on crée l\'abonnement');
                   

                    $subscription = wcs_create_subscription([
                        'order_id' => $order_id,
                        'status'   => 'active',
                        'billing_period'    => 'year',   // Obligatoire : day, week, month, year
                        'billing_interval'  => $duree, 
                        'start_date'         => $date_debut->format('Y-m-d H:i:s'),
                        // Optionnel : si vous voulez définir une date de fin
                        'end_date'           => '', // Laisser vide pour pas de fin
                    ]);

                    if (!empty($user_id)) {
                        $customer_id = (int) $user_id[0];

                        $subscription->set_customer_id($customer_id);

                        $subscription->set_billing_company(
                            get_user_meta($customer_id, 'billing_societe', true)
                        );

                        $subscription->set_billing_phone(
                            get_user_meta($customer_id, 'billing_phone', true)
                        );

                        $subscription->set_billing_address_1(
                            get_user_meta($customer_id, 'billing_address_1', true)
                        );

                        $subscription->set_billing_city(
                            get_user_meta($customer_id, 'billing_city', true)
                        );

                        $subscription->set_billing_postcode(
                            get_user_meta($customer_id, 'billing_postcode', true)
                        );

                        $subscription->set_billing_country(
                            get_user_meta($customer_id, 'billing_country', true)
                        );

                        $subscription->set_billing_first_name(
                            get_user_meta($customer_id, 'billing_first_name', true)
                        );

                        $subscription->set_billing_last_name(
                            get_user_meta($customer_id, 'billing_last_name', true)
                        );

                        // Si tu as également l'email du client
                        $user = get_userdata($customer_id);

                        if ($user) {
                            $subscription->set_billing_email($user->user_email);
                        }
                        
                    }

                    if (is_wp_error($subscription)) {
                        error_log(
                            'Erreur création abonnement - commande : ' . $order_id .
                            ' - ' . $subscription->get_error_message()
                        );
                    } else {

                     

                    error_log('AVANT update_dates');
                    
                        
                        $dates_to_update = array(
                            'last_payment' => $date_debut->format('Y-m-d H:i:s'),
                            'next_payment' => $date_prochain_paiement->format('Y-m-d H:i:s'),
                        );

                        try {
                            $subscription->update_dates( $dates_to_update, 'gmt' );
                        } catch ( Exception $e ) {
                            // Handle schedule error constraint exceptions here
                            error_log(
                                'Erreur update dates: ' . $order_id .
                                ' - ' . $e->getMessage()
                            );
                        }
                        
                       // $subscription->set_date('last_payment', $date_debut->format('Y-m-d H:i:s'));
                        error_log('APRES update_dates');

                        // Lier la commande initiale à l'abonnement
                        $subscription->add_order_note(
                            'Commande initiale : ' . $order_id
                        );
                        $subscription->set_requires_manual_renewal(true);

                        // ID de l'abonnement
                        $subscription_id = $subscription->get_id();
                        $subscription->save();

                        // Sauvegarder la relation commande → abonnement
                        
                        $order->update_meta_data('_subscription_id', $subscription_id);
                        $order->save();

                        error_log(
                            'Abonnement créé - commande : ' . $order_id .
                            ' - abonnement : ' . $subscription_id
                        );
                    }


                    // Copier les produits de la commande dans l'abonnement
                    foreach ($order->get_items('line_item') as $item) {

                        $product = $item->get_product();

                        if (!$product) {
                            continue;
                        }

                        $subscription->add_product(
                            $product,
                            $item->get_quantity(),
                            [
                                'subtotal' => $item->get_subtotal(),
                                'total'    => $item->get_total(),
                            ]
                        );
                    }

                     // Copier les frais de la commande
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
                        $subscription->add_item($fee);
                    }

                    if ($remise_commercial != 0) {

                        $montant_remise = $montant * ($remise_commercial / 100);
                        $montant -= $montant_remise;

                        $fee = new WC_Order_Item_Fee();
                        $fee->set_name('Remise commerciale (' . $remise_commercial . '%)');
                        $fee->set_amount(-abs($montant_remise));
                        $fee->set_total(-abs($montant_remise));
                        $subscription->add_item($fee);
                    }


                    // Remise renouvellement
                    if ($remise_renewal != 0) {

                        $montant_remise = $montant * ($remise_renewal / 100);
                        $montant -= $montant_remise;

                        $fee = new WC_Order_Item_Fee();
                        $fee->set_name('Remise renouvellement de licences (' . $remise_renewal . '%)');
                        $fee->set_amount(-abs($montant_remise));
                        $fee->set_total(-abs($montant_remise));
                        $subscription->add_item($fee);
                    }

               
                    // Remise spéciale
                    if ($remise_special_1 != 0) {

                        $montant_remise = $montant * ($remise_special_1 / 100);
                        $montant -= $montant_remise;

                        $fee = new WC_Order_Item_Fee();
                        $fee->set_name('Autre remise (' . $remise_special_1 . '%)');
                        $fee->set_amount(-abs($montant_remise));
                        $fee->set_total(-abs($montant_remise));
                        $subscription->add_item($fee);
                    }

                    if ($remise_statutaire != 0) {

                        $montant_remise = $montant * ($remise_statutaire / 100);
                        $montant -= $montant_remise;

                        // Détermination du libellé de la remise statutaire
                        if ((float) $remise_statutaire === 50.0 && $type_compte === 'PRO') {
                            $nom_remise = 'Remise Établissements scolaires et associations -50%';
                        } elseif ((float) $remise_statutaire === 30.0 && $type_compte === 'PRO') {
                            $nom_remise = 'Remise Administrations et mairies -30%';
                        } else {
                            $nom_remise = 'Remise liée au statut -' . $remise_statutaire . '%';
                        }

                        $fee = new WC_Order_Item_Fee();
                        $fee->set_name($nom_remise);
                        $fee->set_amount(-abs($montant_remise));
                        $fee->set_total(-abs($montant_remise));
                        $subscription->add_item($fee);
                    }


                    // Remise cumulée
                    if ($remise_cumul != 0) {

                        $montant_remise = $montant * ($remise_cumul / 100);
                        $montant -= $montant_remise;

                        // Détermination du libellé de la remise cumulée
                        if ((float) $remise_cumul === 50.0) {
                            $nom_remise = 'Remise Renouvellement de licences GOUV -50%';
                        } elseif ((float) $remise_cumul === 60.0) {
                            $nom_remise = 'Remise Renouvellement de licences EDU -60%';
                        } else {
                            $nom_remise = 'Remise cumulée -' . $remise_cumul . '%';
                        }

                        $fee = new WC_Order_Item_Fee();
                        $fee->set_name($nom_remise);
                        $fee->set_amount(-abs($montant_remise));
                        $fee->set_total(-abs($montant_remise));
                        $subscription->add_item($fee);
                    }

                    
                    
                    

                    // Copier les frais/taxes si nécessaire
                    $subscription->calculate_totals();
                    $subscription->save();


                }

                //dans une autre iteration $id_commande = 40174  $renew_out=44340  $renew_in=35348
                if($renew_in>0 && $renew_out>0){
                    //rechercher la commande d'id $renew_in 35348
                    //marquer celle ci $id_commande 40174 comme renewal
                    // rechercher l'abonnement marqué avec l'id de celle ci $id_commande 40174 et ajouter  $id_commande ( 40174) si possible avec sequence d'id asc sachant que cet abonnement pourra etre lié a plusieurs commandes deja


                    //$renew_in id sur prestashop il faut plyutot chercher par $order->update_meta_data('_presta_id_commande', $id_commande);

                    $orders = wc_get_orders([
                        'limit' => 1,
                        'meta_key' => '_presta_id_commande',
                        'meta_value' => $renew_in,
                        'return' => 'objects',
                    ]);

                    if (!empty($orders)) {
                        $previous_order = $orders[0];
                        error_log('Commande WooCommerce trouvée : ' . $previous_order->get_id());
                    } else {
                        error_log('Aucune commande WooCommerce trouvée pour l\'ID PrestaShop : ' . $renew_in);
                    }


                    if (!$previous_order) {

                        error_log(
                            'Commande précédente introuvable - renew_in : ' . $renew_in .
                            ' - commande actuelle : ' . $order_id
                        );

                    } else {

                        
                        // -------------------------------------------------
                        // Marquer la commande actuelle comme renewal
                        // -------------------------------------------------

                        update_post_meta($order_id, '_reference_order_id', $previous_order->get_id());


                        // -------------------------------------------------
                        // Ajouter la commande à l'abonnement
                        // -------------------------------------------------

       
                        $order->set_parent_id($previous_order->get_id());
                        $order->add_order_note(sprintf(__('Cette commande est le renouvellement de la commande #%d.', 'woocommerce'), $previous_order->get_id()));
                        $order->save();


                        error_log(
                            'Commande woo : ' . $order_id .
                            ' - Commande preta : ' . $id_commande .
                            ' - Commande parente presta : ' . $renew_in .
                            ' - Commande parente woo : ' .$previous_order->get_id() 
                        );
                          
                    }

                }

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