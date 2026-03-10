<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ALM_Login_Register_Popup {

    public function __construct() {

        add_shortcode('wc_ajax_login_register', [$this, 'wc_ajax_login_register_shortcode']);

        add_action('wp_footer', [$this, 'wc_ajax_login_register_js']);

        add_action('wp_ajax_nopriv_wc_ajax_login', [$this, 'wc_ajax_login']);

        add_action('wp_ajax_nopriv_wc_ajax_register', [$this, 'wc_ajax_register']);

    }

  

    public function wc_ajax_login_register_shortcode() {
        ob_start();
        ?>
        <div id="ajax-login-register">

            <!-- Login -->
            <form id="ajax-login-form" method="post">
                <h3>Connexion</h3>
                <p>
                    <label>Email ou Nom d’utilisateur<br>
                        <input type="text" name="username" required>
                    </label>
                </p>
                <p>
                    <label>Mot de passe<br>
                        <input type="password" name="password" required>
                    </label>
                </p>
                <p>
                    <button type="submit">Se connecter</button>
                </p>
                <div class="login-message"></div>
            </form>

            <hr>

            <!-- Register -->
            <form id="ajax-register-form" method="post">
                <h3>Inscription</h3>
                <p>
                    <label>Nom d’utilisateur<br>
                        <input type="text" name="username" required>
                    </label>
                </p>
                <p>
                    <label>Email<br>
                        <input type="email" name="email" required>
                    </label>
                </p>
                <p>
                    <label>Mot de passe<br>
                        <input type="password" name="password" required>
                    </label>
                </p>
                <p>
                    <button type="submit">S’inscrire</button>
                </p>
                <div class="register-message"></div>
            </form>

        </div>
        <?php
        return ob_get_clean();
    }


    public function wc_ajax_login_register_js() {
        ?>
        <script>
        jQuery(document).ready(function($){

            // login ajax
            $('#ajax-login-form').on('submit', function(e){
                e.preventDefault();
                var form = $(this);
                $.ajax({
                    url: '<?php echo admin_url("admin-ajax.php"); ?>',
                    type: 'POST',
                    data: {
                        action: 'wc_ajax_login',
                        username: form.find('[name="username"]').val(),
                        password: form.find('[name="password"]').val(),
                    },
                    success: function(response){
                        form.find('.login-message').html(response.data || response);
                    }
                });
            });

            // register ajax
            $('#ajax-register-form').on('submit', function(e){
                e.preventDefault();
                var form = $(this);
                $.ajax({
                    url: '<?php echo admin_url("admin-ajax.php"); ?>',
                    type: 'POST',
                    data: {
                        action: 'wc_ajax_register',
                        username: form.find('[name="username"]').val(),
                        email: form.find('[name="email"]').val(),
                        password: form.find('[name="password"]').val(),
                    },
                    success: function(response){
                        form.find('.register-message').html(response.data || response);
                    }
                });
            });

        });
        </script>
        <?php
    }



    function wc_ajax_login() {
        $creds = array(
            'user_login'    => $_POST['username'],
            'user_password' => $_POST['password'],
            'remember'      => true
        );
        $user = wp_signon($creds, false);

        if ( is_wp_error($user) ) {
            wp_send_json_error($user->get_error_message());
        } else {
            wp_send_json_success('Connexion réussie ! Vous êtes redirigé...');
        }
    }


    function wc_ajax_register() {
        $username = sanitize_user($_POST['username']);
        $email    = sanitize_email($_POST['email']);
        $password = $_POST['password'];

        if(username_exists($username) || email_exists($email)) {
            wp_send_json_error('Nom d’utilisateur ou email déjà utilisé.');
        }

        $user_id = wc_create_new_customer($email, $username, $password);

        if(is_wp_error($user_id)){
            wp_send_json_error($user_id->get_error_message());
        }

        wp_send_json_success('Inscription réussie ! Vous pouvez vous connecter.');
    }


}