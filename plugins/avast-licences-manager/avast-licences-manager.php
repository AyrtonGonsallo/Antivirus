<?php
/**
 * Plugin Name: Avast Licences Manager
 * Description: Gestion des comptes revendeurs, devis, renouvellements et licences Avast.
 * Version: 1.0.0
 * Author: Generated
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'ALM_PATH', plugin_dir_path(__FILE__) );
define( 'ALM_FILE', __FILE__ );

// Load classes
require_once ALM_PATH . 'includes/class-roles.php';
require_once ALM_PATH . 'includes/class-gestion-de-comptes.php';
require_once ALM_PATH . 'includes/class-commandes-panier.php';
require_once ALM_PATH . 'includes/class-remise-commerciale.php';
require_once ALM_PATH . 'includes/class-devis.php';
require_once ALM_PATH . 'includes/class-revendeur.php';
require_once ALM_PATH . 'includes/class-wcs.php';
require_once ALM_PATH . 'includes/class-statistiques-antivirus.php';

// Activation / Deactivation hooks
register_activation_hook(__FILE__, ['ALM_Roles', 'add_roles']);
register_deactivation_hook(__FILE__, ['ALM_Roles', 'remove_roles']);

// You can extend the plugin by adding more includes and classes in includes/
add_action('plugins_loaded', function() {
    new ALM_Gestion_De_Comptes();
    new ALM_Commandes_Panier();
    new ALM_Remise_Commerciale();
    new ALM_Devis();
    new ALM_Revendeur();
    new ALM_Wcs();
    new ALM_Statistiques_antivirus();
    
});