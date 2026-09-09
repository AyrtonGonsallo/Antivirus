<?php
/**
 * Plugin Name: Prestashop to woocommerce Importer
 * Description: Import des clients PrestaShop vers WordPress/WooCommerce.
 * Version: 1.0.0
 * Author: Ayrton
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'admin/clients_revendeurs.php';
require_once plugin_dir_path(__FILE__) . 'admin/clients.php';
require_once plugin_dir_path(__FILE__) . 'admin/commandes.php';
require_once plugin_dir_path(__FILE__) . 'admin/commandes_mode_paiements.php';
require_once plugin_dir_path(__FILE__) . 'admin/devis.php';
require_once plugin_dir_path(__FILE__) . 'admin/revendeurs.php';
require_once plugin_dir_path(__FILE__) . 'admin/fonction_du_cron.php';


add_action('admin_menu', function () {

    // Menu principal
    add_menu_page(
        'PrestaShop Importer',
        'PrestaShop Importer',
        'manage_options',
        'presta-importer',
        'presta_import_page',
        'dashicons-database-import',
        30
    );

    // Sous-menu Clients revendeurs
    add_submenu_page(
        'presta-importer',       // slug du parent
        'Clients des revendeurs',               // titre de la page
        'Clients des revendeurs',               // nom affiché
        'manage_options',
        'presta-import-clients-revendeurs', // slug
        'presta_import_clients_revendeurs_page'
    );


     // Sous-menu Clients
    add_submenu_page(
        'presta-importer',       // slug du parent
        'Clients',               // titre de la page
        'Clients',               // nom affiché
        'manage_options',
        'presta-import-clients', // slug
        'presta_import_clients_page'
    );


     // Sous-menu Commandes
    add_submenu_page(
        'presta-importer',       // slug du parent
        'Commandes',               // titre de la page
        'Commandes',               // nom affiché
        'manage_options',
        'presta-import-commandes', // slug
        'presta_import_commandes_page'
    );

     // Sous-menu Commandes
    add_submenu_page(
        'presta-importer',       // slug du parent
        'Commandes mode paiement',               // titre de la page
        'Commandes mode paiement',               // nom affiché
        'manage_options',
        'presta-import-commandes-mode-paiements', // slug
        'presta_import_commandes_mode_paiements_page'
    );

     // Sous-menu Devis
    add_submenu_page(
        'presta-importer',       // slug du parent
        'Devis',               // titre de la page
        'Devis',               // nom affiché
        'manage_options',
        'presta-import-devis', // slug
        'presta_import_devis_page'
    );


     // Sous-menu Revendeurs
    add_submenu_page(
        'presta-importer',       // slug du parent
        'Revendeurs',               // titre de la page
        'Revendeurs',               // nom affiché
        'manage_options',
        'presta-import-revendeurs', // slug
        'presta_import_revendeurs_page'
    );





  

    

});