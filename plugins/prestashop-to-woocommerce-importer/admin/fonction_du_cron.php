<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . '/commandes_mode_paiements.php';
require_once plugin_dir_path(__FILE__) . '/send_welcome_email.php';

function job_du_cron() {

    if (!isset($_GET['presta_import'])) {
        return;
    }

    global $wpdb;

    $table = $wpdb->prefix . 'import_cron';

    // Récupérer l'import actif
    $import = $wpdb->get_row("
        SELECT *
        FROM $table
        WHERE actif = 1
        ORDER BY id ASC
        LIMIT 1
    ");

    if (!$import) {
        exit('Aucun import actif');
    }

    $id_courant = (int) $import->id_courant;
    $range      = (int) $import->range;

    $id_fin = $id_courant + $range - 1;

    // ===============================
    // JOB
    // ===============================

    $file = WP_CONTENT_DIR . '/import-test.txt';

    file_put_contents(
        $file,
        date('Y-m-d H:i:s') .
        ' - Traitement ID ' . $id_courant .
        ' -> ' . $id_fin .
        PHP_EOL,
        FILE_APPEND
    );

    echo 'Traitement ID ' . $id_courant . ' -> ' . $id_fin;

    $offset = $id_courant;
    $limit  = $id_fin - $id_courant + 1;


    $users = get_users([
        'orderby' => 'login',
        'order'   => 'DESC',
        'number'  => $limit,
        'offset'  => $offset,
    ]);

    foreach ($users as $user) {
        if (empty($user->user_email) || !is_email($user->user_email)) {
            continue;
        }

       
        $pays = get_user_meta($user->ID, 'pays', true) ?: 'FR';

        

        $sent = avast_send_welcome_email(
            //$user->user_email,
            "ayrtongonsalloheroku@gmail.com",
            $pays
        );

        error_log(
            "Utilisateur {$user->ID} ({$user->user_login}) : "
            . ($sent ? 'e-mail envoyé' : 'échec de l’envoi')
        );

        echo (
            "Utilisateur : {$user->ID} ({$user->user_login}) <br>".
            "Pays : {$pays} <br>".
            ($sent ? 'e-mail envoyé' : 'échec de l’envoi')."<br>"
        );
    }


    /*
    
    $order_ids = [ 
        65129, 65127, 65125, 65123, 65121, 65119, 65109, 65105, 65101, 65099, 65097, 65095, 65093, 65091, 65087, 65085, 65083, 65081, 65079, 65077, 65073, 65069, 65067, 65065, 65061
     ]; 
     
    foreach ($order_ids as $order_id) { 
        $order = wc_get_order($order_id); 
        if (!$order) { 
            echo "Commande #{$order_id} introuvable.<br>"; 
            continue; 
        } 
        
        if ($order->get_type() !== 'shop_order') { 
            echo "Commande #{$order_id} ignorée : type incorrect.<br>"; continue;
        } 

        if ($order->get_status() === 'completed') { 
            echo "Commande #{$order_id} déjà terminée.<br>"; continue;
        } 

        $result = $order->update_status( 'completed', 'Statut modifié par script.' ); 
        echo "Commande #{$order_id} : statut terminé.<br>"; 
    }

    */








    // ===============================
    // PASSER AU LOT SUIVANT
    // ===============================

    $wpdb->update(
        $table,
        [
            'id_courant' => $id_fin + 1
        ],
        [
            'id' => $import->id
        ]
    );

    exit;
}



// ===============================
// URL IMPORT
// ===============================

add_action('init', 'job_du_cron');



