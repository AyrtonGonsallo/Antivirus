<?php

require_once __DIR__ . '/class-devis-email-sender.php';

if ( ! defined( 'ABSPATH' ) ) exit;

class ALM_Crons_Urls {

    public function __construct() {

       
        add_action('init', [$this, 'alm_set_crons_urls']);




    }



    function alm_set_crons_urls() {
        
        $this->url_devis_expired();

    }

    function url_devis_expired(){

        

        if (!isset($_GET['send_devis_expiring'])) {
            return;
        }

        // 🔐 sécurité simple
        if (!current_user_can('manage_options')) {
            wp_die('Accès refusé');
        }

        if (!isset($_GET['key']) || $_GET['key'] !== 'ma_clef_ultra_secrete_123') {
            wp_die('Clé invalide');
        }

        $today = current_time('Y-m-d H:i:s');

        $devis_liste = get_posts([
            'post_type'      => 'devis-en-ligne',
            'posts_per_page' => -1,
            'meta_query'     => [
                [
                    'key'     => 'date_expiration',
                    'value'   => $today,
                    'compare' => '<=',
                    'type'    => 'DATETIME'
                ],
            ]
        ]);

        foreach ($devis_liste as $devis) {
            DevisEmailSender::send_email_devis_expiring($devis->ID);
            $user = get_field('utilisateur', $devis->ID);
            $user_email = $user->user_email;
            $date_expiration  = get_field('date_expiration', $devis->ID);
            $date_expiration_formatted  = $date_expiration ? (new DateTime($date_expiration))->format('d/m/Y \à H\hi') : '';

            echo "Devis {$devis->ID} expirant le {$date_expiration_formatted} envoyé à {$user_email}<br>";
        } 

        
        exit;
    }




}
