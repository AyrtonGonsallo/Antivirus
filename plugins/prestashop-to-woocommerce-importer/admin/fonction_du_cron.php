<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . '/devis.php';

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

    presta_import_devis($id_courant, $id_fin);

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



