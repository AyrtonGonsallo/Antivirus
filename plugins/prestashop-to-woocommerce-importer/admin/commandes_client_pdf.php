<?php

if (!defined('ABSPATH')) {
    exit;
}



function presta_update_commandes_client_final_pdf($page,$per_page) {

    $commandes_mises_a_jour = [];

    $updated = 0;


    /*
     * Pagination des commandes WooCommerce
     */
    /*
    $orders = wc_get_orders([
        'limit'   => $per_page,
        'page'    => $page,
        'status'  => array_keys(wc_get_order_statuses()),
        'orderby' => 'ID',
        'order'   => 'ASC',
        'return'  => 'objects',

        'meta_query' => [
            [
                'key'     => '_presta_id_client_rvd',
                'compare' => 'EXISTS',
            ],
        ],
    ]);
    */
/*
    $order_ids = [
        35572, 35689, 35713, 35901, 35902, 35932, 35956, 36110, 36204, 36208,
        36248, 36315, 36351, 36381, 36522, 36599, 36605, 36619, 36623, 36627,
        36631, 36673, 36905, 37041, 37186, 37200, 37597, 37615, 37724, 37796,
        37820, 37851, 37910, 38006, 38020, 38029, 38042, 38087, 38135, 38180,
        38219, 38263, 38281, 38323, 38324, 38346, 38356, 38506, 38578, 38596,
        38608, 38612, 38626, 38628, 38630, 38646, 38648, 38762, 38810, 38832,
        38856, 38860, 38950, 38956, 38990, 38994, 39188, 39218, 39272, 39419,
        39441, 39443, 39471, 39489, 39519, 39569, 39679, 39737, 39840, 39860,
        39986, 40134, 40166, 40170, 40190, 40248, 40326, 40340, 40348, 40372,
        40424, 40536, 40574, 40580, 40690, 40698, 40704, 40760, 40936, 40952,
        41005, 41033, 41149, 41273, 41385, 41459, 41461, 41463, 41529, 41561,
        41589, 41693, 41759, 41777, 41785, 41789, 41815, 41895, 41969, 41995,
        41999, 42015, 42083, 42087, 42095, 42153, 42213, 42329, 42347, 42363,
        42407, 42443, 42529, 42585, 42603, 42689, 42713, 42811, 42839, 42865,
        42899, 42999, 43069, 43364, 43392, 43442, 43500,
    ];
*/

 $order_ids = [35689, 35713, 35902, 35932, 35956, 36204, 36208, 36315, 36381, 36522,
 36605, 36673, 36905, 37041, 37186, 37200, 37615, 37796, 37820, 37851,
 37910, 38006, 38020, 38087, 38135, 38263, 38281, 38323, 38324, 38506,
 38578, 38596, 38612, 38626, 38628, 38630, 38646, 38648, 38762, 38810,
 38832, 38856, 38860, 38950, 38956, 38990, 38994, 39188, 39218, 39272,
 39419, 39471, 39489, 39519, 39569, 39679, 39737, 39840, 39860, 39986,
 40134, 40166, 40170, 40190, 40326, 40340, 40348, 40372, 40424, 40536,
 40574, 40580, 40690, 40698, 40704, 40760, 41005, 41033, 41149, 41273,
 41529, 41561, 41589, 41693, 41777, 41785, 41789, 41815, 41895, 41969,
 41995, 41999, 42015, 42083, 42087, 42095, 42153, 42213, 42329, 42347,
 42363, 42407, 42443, 42529, 42585, 42603, 42689, 42713, 42811, 42839,
 42865, 42899, 42999, 43069, 43364, 43392];


    $orders = wc_get_orders([
        'limit'   => $per_page,
        'page'    => $page,
        'status'  => array_keys(wc_get_order_statuses()),
        'orderby' => 'ID',
        'order'   => 'ASC',
        'return'  => 'objects',

        'post__in'   => $order_ids,   // ✅ filtre sur ces IDs uniquement
        'meta_query' => [
            [
                'key'     => '_presta_id_client_rvd',
                'compare' => 'EXISTS',
            ],
        ],
    ]);

    if (empty($orders)) {
        echo '<div class="notice notice-warning">';
        echo '<p>Aucune commande trouvée pour la page ' . esc_html($page) . '.</p>';
        echo '</div>';
        return;
    }

    foreach ($orders as $order) {

        $order_id = $order->get_id();

        /*
         * ID commande PrestaShop
         */
        $id_commande = $order->get_meta('_presta_id_commande');
        $id_revendeur = $order->get_meta('_presta_id_revendeur');

        if (!$id_commande) {
            echo "Commande WooCommerce {$order_id} : aucun _presta_id_commande<br>";
            continue;
        }

        /*
         * ID du client final PrestaShop
         */
        $id_client_rvd = $order->get_meta('_presta_id_client_rvd');

        if (!$id_client_rvd) {
            echo "Commande {$order_id} : aucun _presta_id_client_rvd<br>";
            continue;
        }

        /*
         * Recherche du client WooCommerce
         */
     

        #ajouter la cond update_user_meta($user_id, 'revendeur_id', $user_revendeur_id); //le notre  'meta_key'   => 'revendeur_id','meta_value' => $id_revendeur,
        //chercher revendeur 
        $user_revendeur_id = get_users([
            'role'       => 'customer_revendeur',
            'meta_key'   => 'presta_id',
            'meta_value' => $id_revendeur, //leur id
            'number'     => 1,
            'fields'     => 'ids',
        ])[0] ?? 0;

        $client_final_id = get_users([
            'meta_query' => [
                [
                    'key'     => 'presta_id',
                    'value'   => $id_client_rvd,
                    'compare' => '=',
                ],
                [
                    'key'     => 'revendeur_id',
                    'value'   => $user_revendeur_id ,
                    'compare' => '=',
                ],
            ],
            'number' => 1,
            'fields' => 'ID',
        ]);

        if (empty($client_final_id)) {
            echo "Commande {$order_id} : client presta {$id_client_rvd}  revendeur {$id_revendeur} introuvable<br>";
            continue;
        }

        $client_final_id = $client_final_id[0];

        /*
         * Mise à jour client_final
         */
        $order->update_meta_data('client_final', $client_final_id);
        $order->save();

        echo "Commande {$order_id} : client_final_woo = {$id_client_rvd}- client_final = {$client_final_id}<br>";

        /*
         * Régénération du PDF
         */
        $url = add_query_arg([
            'action'        => 'generate_wpo_wcpdf',
            'document_type' => 'invoice',
            'order_ids'     => $order_id,
            'access_key'    => '1a54711486',
        ], admin_url('admin-ajax.php'));
        $response = wp_remote_get($url, [
            'timeout' => 60,
        ]);

        if (is_wp_error($response)) {

            echo "❌ PDF {$order_id} : "
                . esc_html($response->get_error_message())
                . "<br>";

        } else {

            $status = wp_remote_retrieve_response_code($response);

            if ($status >= 200 && $status < 300) {
                echo "✅ PDF régénéré : commande {$order_id}<br>";
            } else {
                echo "❌ PDF commande {$order_id} : HTTP {$status}<br>";
            }
        }

        /*
         * Statistiques
         */
        $commandes_mises_a_jour[$id_commande] = $order_id;

        $updated++;
    }

    /*
     * Résultat
     */
    echo '<div class="notice notice-success">';
    echo '<p>';
    echo '<strong>Mise à jour terminée</strong><br>';
    echo 'Page : ' . esc_html($page) . '<br>';
    echo 'Commandes traitées : ' . count($orders) . '<br>';
    echo 'Commandes mises à jour : ' . $updated . '<br>';
    echo 'NB commandes mises à jour : ' . count($commandes_mises_a_jour) . '<br>';
    echo 'Commandes mises à jour : '
        . esc_html(json_encode($commandes_mises_a_jour))
        . '<br>';
    echo '</p>';
    echo '</div>';
}