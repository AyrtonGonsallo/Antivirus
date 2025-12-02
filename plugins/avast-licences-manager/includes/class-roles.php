<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ALM_Roles {

    public static function add_roles() {
        // Ensure 'customer' role exists
        $customer = get_role('customer');
        $caps = array();
        if ( $customer ) {
            $caps = $customer->capabilities;
        } else {
            // fallback: typical customer capabilities
            $caps = array(
                'read' => true,
            );
        }

        // Rôle : Client Particulier
        if ( ! get_role('customer_particulier') ) {
            add_role(
                'customer_particulier',
                'Client Particulier',
                $caps
            );
        }

        // Rôle : Client Revendeur
        if ( ! get_role('customer_revendeur') ) {
            $caps_revendeur = $caps;
            // capability custom for future features
            $caps_revendeur['manage_clients'] = true;
            add_role(
                'customer_revendeur',
                'Client Revendeur',
                $caps_revendeur
            );
        }
    }

    public static function remove_roles() {
        // On ne retire pas les rôles si d'autres utilisateurs les utilisent — ici on supprime quand le plugin est désactivé
        remove_role('customer_particulier');
        remove_role('customer_revendeur');
    }
}
