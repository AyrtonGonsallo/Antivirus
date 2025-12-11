<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ALM_Gestion_De_Comptes {

    public function __construct() {
        /*
            add_action('init', [$this, 'register_clients_endpoint']);
            → Déclare un nouvel endpoint URL pour le compte client (ex: /mon-compte/clients)

            add_filter('query_vars', [$this, 'add_clients_query_var'], 0);
            → Ajoute la variable clients à WordPress pour qu’elle soit reconnue dans l’URL.

            add_filter('woocommerce_account_menu_items', [$this, 'add_clients_menu_link']);
            → Ajoute un nouveau lien dans le menu du compte WooCommerce (ex: “Mes clients”).

            add_action('woocommerce_account_clients_endpoint', [$this, 'render_clients_page']);
            → Affiche la page correspondante quand l’URL /mon-compte/clients est visitée.
        */
            
        // Hooks WooCommerce
        add_action('woocommerce_save_account_details', [$this, 'save_account_fields']);

        // Back Office WordPress
        add_action('show_user_profile', [$this, 'show_admin_user_fields']);
        add_action('edit_user_profile', [$this, 'show_admin_user_fields']);

        add_action('init', [$this, 'register_clients_endpoint']);
        add_filter('query_vars', [$this, 'add_clients_query_var'], 0);
        add_filter('woocommerce_account_menu_items', [$this, 'add_clients_menu_link']);
        add_action('woocommerce_account_clients_endpoint', [$this, 'render_clients_page']);

        add_action('init', [$this, 'register_mes_devis_endpoint']);
        add_filter('query_vars', [$this, 'add_mes_devis_query_var']);
        add_filter('woocommerce_account_menu_items', [$this, 'add_devis_menu_link']);
        add_action('woocommerce_account_mes-devis_endpoint', [$this, 'render_devis_page']);
        

        // Nouveau : gestion formulaire client revendeur
        add_action('template_redirect', [$this, 'handle_client_submission']);


        add_filter('wp_mail',  [$this, 'log_source'], 1, 1);

    }

    
    private function detect_email_source(string $file) : string {

        $relative = str_replace(ABSPATH, '', $file);

        // 1) WooCommerce (toutes les extensions WooCommerce)
        if (strpos($relative, 'woocommerce') !== false) {
            return 'WooCommerce';
        }

        // 2) WordPress Core
        if (strpos($relative, 'wp-admin') === 0 || strpos($relative, 'wp-includes') === 0) {
            return 'WordPress Core';
        }
        if (preg_match('#^wp-.*\.php$#', $relative)) {
            return 'WordPress Core';
        }

        // 3) Plugins tiers
        if (strpos($relative, 'wp-content/plugins') === 0) {
            return 'Plugin tiers';
        }

        // 4) Thème
        if (strpos($relative, 'wp-content/themes') === 0) {
            return 'Thème';
        }

        return 'Inconnu';
    }
   
  private function get_wp_mail_caller() : array {
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

    foreach ($trace as $call) {
        if ($call['function'] === 'wp_mail') {
            return $call;
        }
    }

    throw new Exception("Impossible de détecter l’appelant wp_mail().");
}


    public function log_source($args) {
        try {
            $caller = $this->get_wp_mail_caller();
            $file   = $caller['file'];
            $line   = $caller['line'];
        } catch (Exception $e) {
            error_log('[MAIL LOGGER] Impossible d’identifier l’appelant.');
            return $args;
        }

        $source = $this->detect_email_source($file);

        // 🎯 Personnalisation uniquement des emails WordPress Core
        if ($source === "WordPress Core") {
            $args = $this->override_mails($args);
        }

        // 📄 Log
        $log_entry = sprintf(
            "[%s] Source: %s | To: %s | Subject: %s\n",
            date('Y-m-d H:i:s'),
            $source,
            is_array($args['to']) ? implode(',', $args['to']) : $args['to'],
            $args['subject']
        );

        error_log($log_entry, 3, WP_CONTENT_DIR . '/email-log.txt');

        return $args;
    }



    public function override_mails($args) {

        // ✔ Flag anti-template
        if (!empty($args['_template_applied'])) {
            return $args;
        }
        $args['_template_applied'] = true;

        $subject = $args['subject'];
        $message = $args['message'];
        $lien_logo_png = site_url().'/wp-content/uploads/2025/11/avast-logo.png';

        $html  = '<html><body style="font-family: Arial; padding:40px 80px; background:#f5f5f5;">';
            $html .= '<div style="max-width:600px;margin:0 auto;background:white;padding:30px;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,0.08);">';
                $html .= '<div style="text-align:center;margin-bottom:25px;">
                            <img src="'.$lien_logo_png.'" alt="Logo" >
                        </div>';
            
                $html .= '<div id="contenu" style="font-size:15px;color:#333;line-height:1.6;">'.wpautop($message).'</div>';
                $html .= '<hr style="margin-top:30px;">';
                $html .= '<p style="font-size:13px;color:#888;text-align:center;">Cet email a été envoyé automatiquement par '.get_bloginfo("name").'.</p>';
            $html .= '</div>';
        $html .= '</body></html>';
        // Forcer HTML
        add_filter('wp_mail_content_type', function() {
            return 'text/html';
        });

        $args['message'] = $html;

        return $args;
    }


    public function save_account_fields($user_id) {
         
        update_user_meta($user_id, 'denomination', sanitize_text_field($_POST['denomination']));
        update_user_meta($user_id, 'ville', sanitize_text_field($_POST['ville']));
        update_user_meta($user_id, 'code_postal', sanitize_text_field($_POST['code_postal']));
        update_user_meta($user_id, 'pays', sanitize_text_field($_POST['pays']));
        update_user_meta( $user_id, 'billing_country', sanitize_text_field( $_POST['pays'] ) );
        update_user_meta( $user_id, 'shipping_country', sanitize_text_field( $_POST['pays'] ) );
        update_user_meta($user_id, 'civilite', sanitize_text_field($_POST['civilite']));
        update_user_meta($user_id, 'billing_address_1', sanitize_text_field($_POST['billing_address_1']));
        update_user_meta($user_id, 'billing_phone', sanitize_text_field($_POST['billing_phone']));
        update_user_meta($user_id, 'optin_promos', isset($_POST['optin_promos']) ? 'yes' : 'no');
        update_user_meta($user_id, 'optin_expiration', isset($_POST['optin_expiration']) ? 'yes' : 'no');
        if( isset($_POST['new_revendeur_account_regime_tva'])){
            $regime=$_POST['new_revendeur_account_regime_tva'];
            if($regime==1){
                update_user_meta( $user_id, 'new_revendeur_account_regime_tva', "HT" );
                update_user_meta($user_id, 'new_revendeur_account_prefixe_tva', sanitize_text_field($_POST['new_revendeur_account_prefixe_tva']));
                update_user_meta($user_id, 'new_revendeur_account_tva_intra', sanitize_text_field($_POST['new_revendeur_account_tva_intra']));
            }else{
                update_user_meta( $user_id, 'new_revendeur_account_regime_tva', "TVA" );

            }

        }
    }

     public function show_admin_user_fields($user) {
        $billing_address_1 = get_user_meta($user->ID, 'billing_address_1', true);
        $billing_phone   = get_user_meta($user->ID, 'billing_phone', true);
        $optin_promos    = get_user_meta($user->ID, 'optin_promos', true);
        $optin_expiration = get_user_meta($user->ID, 'optin_expiration', true);
        $new_revendeur_account_regime_tva = get_user_meta($user->ID, 'new_revendeur_account_regime_tva', true);
        $new_revendeur_account_prefixe_tva = get_user_meta($user->ID, 'new_revendeur_account_prefixe_tva', true);
        $new_revendeur_account_tva_intra = get_user_meta($user->ID, 'new_revendeur_account_tva_intra', true);
        $optin_expiration = get_user_meta($user->ID, 'optin_expiration', true);


        $type_client    = get_user_meta($user->ID, 'type_client', true);
        $denomination   = get_user_meta($user->ID, 'denomination', true);
        $genre          = get_user_meta($user->ID, 'genre', true);
        $fax            = get_user_meta($user->ID, 'fax', true);
        $ville          = get_user_meta($user->ID, 'ville', true);
        $code_postal    = get_user_meta($user->ID, 'code_postal', true);
        $pays           = get_user_meta($user->ID, 'pays', true);
        $revendeur_id   = get_user_meta($user->ID, 'revendeur_id', true);

        ?>
        <h2>Préférences Avast</h2>
        <table class="form-table">
            <tr>
                <th><label for="billing_address_1">Adresse</label></th>
                <td><input type="text" name="billing_address_1" id="billing_address_1" value="<?php echo esc_attr($billing_address); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="billing_phone">Téléphone</label></th>
                <td><input type="text" name="billing_phone" id="billing_phone" value="<?php echo esc_attr($billing_phone); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="optin_promos">Recevoir promotions</label></th>
                <td><input type="checkbox" name="optin_promos" id="optin_promos" value="yes" <?php checked($optin_promos, 'yes'); ?> /></td>
            </tr>

            <?php if (in_array('customer_particulier', $user->roles)) : ?>
                <tr>
                    <th><label for="optin_expiration">Informer de l’expiration des licences</label></th>
                    <td><input type="checkbox" name="optin_expiration" id="optin_expiration" value="yes" <?php checked($optin_expiration, 'yes'); ?> /></td>
                </tr>

                <tr>
                <th><label for="type_client">Type de client</label></th>
                <td>
                    <input type="text" name="type_client" id="type_client" value="<?php echo esc_attr($type_client); ?>" class="regular-text" />
                </td>
            </tr>

            <tr>
                <th><label for="denomination">Dénomination sociale</label></th>
                <td>
                    <input type="text" name="denomination" id="denomination" value="<?php echo esc_attr($denomination); ?>" class="regular-text" />
                </td>
            </tr>

            <tr>
                <th><label for="genre">Genre</label></th>
                <td>
                    <input type="text" name="genre" id="genre" value="<?php echo esc_attr($genre); ?>" class="regular-text" />
                </td>
            </tr>

        
            <tr>
                <th><label for="fax">Fax</label></th>
                <td>
                    <input type="text" name="fax" id="fax" value="<?php echo esc_attr($fax); ?>" class="regular-text" />
                </td>
            </tr>


            <tr>
                <th><label for="ville">Ville</label></th>
                <td>
                    <input type="text" name="ville" id="ville" value="<?php echo esc_attr($ville); ?>" class="regular-text" />
                </td>
            </tr>

            <tr>
                <th><label for="code_postal">Code Postal</label></th>
                <td>
                    <input type="text" name="code_postal" id="code_postal" value="<?php echo esc_attr($code_postal); ?>" class="regular-text" />
                </td>
            </tr>

            <tr>
                <th><label for="pays">Pays</label></th>
                <td>
                    <input type="text" name="pays" id="pays" value="<?php echo esc_attr($pays); ?>" class="regular-text" />
                </td>
            </tr>
            
            <tr>
                <th><label for="revendeur_id">Revendeur associé</label></th>
                <td>
                    <input type="text" name="revendeur_id" id="revendeur_id" value="<?php echo esc_attr($revendeur_id); ?>" class="regular-text" readonly />
                </td>
            </tr>
            <?php endif; ?>

            <?php if($new_revendeur_account_regime_tva){?>
                <tr>
                    <th><label for="new_revendeur_account_regime_tva">Régime tva</label></th>
                    <td>
                        <input type="text" name="new_revendeur_account_regime_tva" id="new_revendeur_account_regime_tva" value="<?php echo esc_attr($new_revendeur_account_regime_tva); ?>" class="regular-text" />
                    </td>
                </tr>
            <?php }?>
            <?php if($new_revendeur_account_prefixe_tva){?>
            <tr>
                <th><label for="new_revendeur_account_prefixe_tva">Préfixe tva intracommunautaire</label></th>
                <td>
                    <input type="text" name="new_revendeur_account_prefixe_tva" id="new_revendeur_account_prefixe_tva" value="<?php echo esc_attr($new_revendeur_account_prefixe_tva); ?>" class="regular-text" />
                </td>
            </tr>
            <tr>
                <th><label for="new_revendeur_account_tva_intra">Numéro tva intracommunautaire</label></th>
                <td>
                    <input type="text" name="new_revendeur_account_tva_intra" id="new_revendeur_account_tva_intra" value="<?php echo esc_attr($new_revendeur_account_tva_intra); ?>" class="regular-text" />
                </td>
            </tr>

            <?php }?>
        </table>
        <?php
    }


    /**
     * Déclarer l’endpoint clients/
     */
    public function register_clients_endpoint() {
        add_rewrite_endpoint('clients', EP_ROOT | EP_PAGES);
    }

    /**
     * Ajouter la variable de requête
     */
    public function add_clients_query_var($vars) {
        $vars[] = 'clients';
        return $vars;
    }

    /* creer la page mes devis */
    public function register_mes_devis_endpoint() {
        add_rewrite_endpoint('mes-devis', EP_ROOT | EP_PAGES);
    }

    /* creer la page mes devis */
    public function add_mes_devis_query_var($vars) {
        $vars[] = 'mes-devis';
        return $vars;
    }

    /**
     * Ajouter le lien “Mes Clients” dans le menu Mon Compte
     * Seulement pour le rôle customer_revendeur
     */
    public function add_clients_menu_link($items) {
        $user = wp_get_current_user();
        
        if (in_array('customer_revendeur', $user->roles)) {
            $logout = $items['customer-logout'];
            unset($items['customer-logout']);
            $items['clients'] = __('Mes Clients', 'alm');
            $items['customer-logout'] = $logout;
        }

        return $items;
    }

/**
     * Ajouter le lien “Mes Devis” dans le menu Mon Compte
     * Seulement pour les rôles customer_revendeur,customer_particulier
     */
    public function add_devis_menu_link($items) {
        $user = wp_get_current_user();

        if (in_array('customer_revendeur', $user->roles) || in_array('customer_particulier', $user->roles)) {

            // Insérer avant "clients" ou autre
            $new = [];

            foreach($items as $key => $label) {
                if ($key === 'customer-logout') { 
                    $new['mes-devis'] = __('Mes Devis', 'alm'); 
                }
                $new[$key] = $label;
            }

            return $new;
        }

        return $items;
    }


    /**
     * Rend la page via un template dédié dans le plugin
     */
    public function render_clients_page() {
        $template = plugin_dir_path(dirname(__FILE__)) . 'templates/account-clients.php';
        if (file_exists($template)) {
            include $template;
        } else {
            echo "<p>Template introuvable.</p>";
        }
    }


    /**
     * Rend la page via un template dédié dans le plugin
     */
    public function render_devis_page() {
        $template = plugin_dir_path(dirname(__FILE__)) . 'templates/account-devis.php';
        if (file_exists($template)) {
            include $template;
        } else {
            echo "<p>Template introuvable.</p>";
        }
    }

    /**
     * Gestion de l'ajout client revendeur depuis le formulaire
     */
    public function handle_client_submission() {
        if ( ! is_account_page() || ! isset($_POST['submit_ajout_client']) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_POST['ajout_client_nonce_field'] ?? '', 'ajout_client_nonce' ) ) {
            wc_add_notice('Erreur de sécurité, veuillez réessayer.', 'error');
            return;
        }

        if ( ! current_user_can('customer_revendeur') ) {
            wc_add_notice('Vous n’êtes pas autorisé à ajouter un client.', 'error');
            return;
        }

        // Récupération et nettoyage des champs
        $type_client   = sanitize_text_field($_POST['type_client'] ?? '');
        $denomination  = sanitize_text_field($_POST['denomination'] ?? '');
        $genre         = sanitize_text_field($_POST['genre'] ?? '');
        $nom           = sanitize_text_field($_POST['nom'] ?? '');
        $prenom        = sanitize_text_field($_POST['prenom'] ?? '');
        $email         = sanitize_email($_POST['email'] ?? '');
        $billing_phone     = sanitize_text_field($_POST['billing_phone'] ?? '');
        $fax           = sanitize_text_field($_POST['fax'] ?? '');
        $billing_address_1       = sanitize_text_field($_POST['billing_address_1'] ?? '');
        $ville         = sanitize_text_field($_POST['ville'] ?? '');
        $code_postal   = sanitize_text_field($_POST['code_postal'] ?? '');
        $pays          = sanitize_text_field($_POST['pays'] ?? 'FR');

        // Vérification des champs obligatoires
        $errors = [];
        foreach ( ['type_client','denomination','nom','prenom','email','billing_address_1','billing_phone','ville','code_postal','pays'] as $field ) {
            if ( empty( ${$field} ) ) {
                $errors[] = ucfirst(str_replace('_',' ',$field)).' est obligatoire.';
            }
        }

        if ( ! empty($errors) ) {
            foreach ( $errors as $err ) {
                wc_add_notice($err, 'error');
            }
            return;
        }

        // Vérifier si l'email existe déjà
        if ( email_exists( $email ) ) {
            wc_add_notice('Cet email est déjà utilisé par un autre client.', 'error');
            return;
        }

        // Création du compte client
        $password = wp_generate_password( 12, false ); // mot de passe aléatoire
        $userdata = [
            'user_login' => $email,
            'user_pass'  => $password,
            'user_email' => $email,
            'first_name' => $prenom,
            'last_name'  => $nom,
            'role'       => 'customer_particulier',
        ];

        $user_id = wp_insert_user($userdata);

        if ( is_wp_error($user_id) ) {
            wc_add_notice('Erreur lors de la création du client : ' . $user_id->get_error_message(), 'error');
            return;
        }

        // Ajout des meta
        update_user_meta($user_id, 'revendeur_id', get_current_user_id());
        update_user_meta($user_id, 'type_client', $type_client);
        update_user_meta($user_id, 'denomination', $denomination);
        update_user_meta($user_id, 'genre', $genre);
        update_user_meta($user_id, 'billing_phone', $billing_phone);
        update_user_meta($user_id, 'fax', $fax);
        update_user_meta($user_id, 'billing_address_1', $billing_address_1);
        update_user_meta($user_id, 'ville', $ville);
        update_user_meta($user_id, 'code_postal', $code_postal);
        update_user_meta($user_id, 'pays', $pays);
        update_user_meta( $user_id, 'billing_country', sanitize_text_field( $pays ) );
        update_user_meta( $user_id, 'shipping_country', sanitize_text_field( $pays ) );

        // Envoi email au client avec ses identifiants
        $subject = 'Votre compte client';
        $message = "Bonjour $prenom,\n\nVotre compte a été créé par votre revendeur.\n\nEmail : $email\nMot de passe : $password\n\nVous pouvez vous connecter ici : " . wp_login_url();
        wp_mail( $email, $subject, $message );

        // Redirection vers la même page avec paramètre pour afficher le message
        wp_safe_redirect( add_query_arg( 'client_added', 'true', wc_get_account_endpoint_url('clients') ) );
        exit;
    }



}
