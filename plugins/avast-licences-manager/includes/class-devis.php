<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ALM_Devis {

    public function __construct() {

         // Lors du UPDATE CART → on ajoute les données
       // add_action('addify_rfq_after_update_quote_item', [$this, 'sauver_champs_perso_devis'], 10, 2);
       // add_action('wp_ajax_update_quote_items',  [$this, 'alm_save_alm_duree_before_quote_update'], 4, 0);
        //add_action('wp_ajax_nopriv_update_quote_items', [$this, 'alm_save_alm_duree_before_quote_update'], 5, 0);

        //add_filter( 'woocommerce_add_cart_item_data', [$this, 'alm_save_custom_fields_to_cart'], 5, 2);
        //add_action( 'woocommerce_add_to_cart',  [$this, 'alm_force_quantity_after_add_to_cart'], 10, 3);

        add_shortcode('alm_devis_form', [$this, 'alm_render_devis_form']);
        add_action('init', [$this, 'alm_handle_devis_form']);

    }


    function alm_get_products_grouped() {
    $categories = get_terms([
        'taxonomy' => 'product_cat',
        'hide_empty' => true
    ]);

    $output = '';

    foreach ($categories as $cat) {
        $products = wc_get_products([
            'status' => 'publish',
            'limit'  => -1,
            'category' => [$cat->slug],
            'type'   => 'variable'
        ]);

        $variations = [];

        foreach ($products as $parent) {
            $children_ids = $parent->get_children();  // IDs des variations

            foreach ($children_ids as $child_id) {
                $child = wc_get_product($child_id);
                if ($child && $child->is_type('variation')) {
                    $variations[] = $child;  // On stocke seulement les enfants
                }
            }
        }


        $output .= '<h3 class="alm-cat-title">' . $cat->name . '</h3>';
        $output .= '<table class="alm-table"><tr>
                        <th>Image</th>
                        <th>Produit</th>
                        <th>Quantité</th>
                    </tr>';

        foreach ($variations as $prod) {

            $image = wp_get_attachment_image_src( $prod->get_image_id(), 'thumbnail' );
            $imgURL = $image ? $image[0] : '';
            $regular = (float) $prod->get_regular_price();
            $sale    = (float) $prod->get_sale_price();
            $promo_display = "";

            if ($sale && $regular && $sale < $regular) {
                $discount = round((($regular - $sale) / $regular) * 100);
                $promo_display = " <span style='color:green;font-weight:bold;'>Promo -$discount% *</span>";
            }


            $output .= '<tr>
                <td><img src="' . $imgURL . '" style="max-width:50px;"></td>
                <td>' . $prod->get_name() . '<br>'.$promo_display.'</td>
                <td>
                    <input type="number" 
                           min="0" 
                           name="prod_qty[' . $prod->get_id() . ']" 
                           value="0" 
                           class="alm-qty">
                </td>
            </tr>';
        }

        $output .= '</table><br>';
    }

    return $output;
}


    function alm_render_devis_form() {
        ob_start(); ?>

        <?php
            if(isset($_GET["status_demande"]) ){
                if(($_GET["status_demande"])=="success" ){
                    echo "<div class='msg-box success' style='border: solid 1px;text-align: center;color: white;padding: 4px 10px;background: #00d369;'>Demande de devis envoyée avec succès.</div>";
                }else{
                    echo "<div class='msg-box failure' style='border: solid 1px;text-align: center;color: white;padding: 4px 10px;background: #d30b00ff;'>Échec de la demande.</div>";
                }
                
            }

        ?>
        <?php
            if(!isset($_GET["status_demande"]) || (isset($_GET["status_demande"]) && !(($_GET["status_demande"])=="success")) ){
        ?>   
          
            <form method="post" id="alm-devis-form" class="alm-devis-form" enctype="multipart/form-data">
                <div class="line"></div>
                <h2>Choix du produit Avast</h2>
                <div class="">
                    <span class="">
                        <input type="radio" value="idkn" id="idknow" name="choixavis" checked required="required" > 
                        <label for="idknow">Je ne sais pas quelle version choisir</label>
                    </span>
                    <span class="">
                        <input type="radio" value="ikn" id="iknow" name="choixavis" required="required" > 
                        <label for="iknow">Je sais ce dont j’ai besoin</label>
                    </span>
                </div>
                <div id="idknowForm"> 
                    <div class="">
                        <label for="compt2save" class="">
                            Indiquez simplement les ordinateurs à protéger :							
                        </label>
                        <textarea class="" name="compt2save" id="compt2save" rows="4"></textarea>				
                    </div>
                </div>
                <div id="iknowForm">
                    Composez votre demande de devis avec les produits ci dessous
                    <?php echo $this->alm_get_products_grouped(); ?>
                </div>

                <div class="line"></div>


                <label><strong>Durée :</strong></label><br>

                <input type="radio" name="alm_software_duration" id="duration_1" value="1-year" required checked>
                <label for="duration_1">1 an</label><br>

                <input type="radio" name="alm_software_duration" id="duration_2" value="2-years">
                <label for="duration_2">2 ans</label><br>

                <input type="radio" name="alm_software_duration" id="duration_3" value="3-years">
                <label for="duration_3">3 ans</label><br>

                <input type="radio" name="alm_software_duration" id="duration_many" value="many-years">
                <label for="duration_many">
                    Je souhaite obtenir plusieurs devis pour 1, 2 et 3 ans et réaliser ainsi <b>jusqu'à 67% d'économie.</b>
                </label>

                

                <div class="div-remise">
                    <div id="demandeRemise">
                        <span style="font-family: 'Raleway';font-weight: 600;"> JE PEUX BÉNÉFICIER D'UNE REMISE COMMERCIALE :</span>
                        <!-- Option 1 -->
                        <label>
                            <input type="checkbox" name="option_remise[]"  id="option1" class="optionRemise" data-group="1" data-file="file1" data-value="Changement -25%"> Je change d'antivirus pour Avast -25%
                        </label>
                        <div class="upload hidden" id="file1">
                            <input type="file" name="justificatif_changement" accept="application/pdf,image/*">
                        </div>

                        <!-- Option 2 -->
                        <label>
                            <input type="checkbox"  name="option_remise[]"  id="option2" class="optionRemise" data-group="2" data-file="file2" data-value="Renouvellement de licences -30%"> Renouvellement de licences -30%
                        </label>
                        
                        <div class="upload hidden" id="file2" style="display: flex;gap: 7px; align-items: center;justify-content: center;">
                            <input  style="width: 50%;padding: 0.5rem 0.4rem; height: 30px;" type="text" id="old_key" name="justificatif_text_renouvellement" placeholder="Ancienne licence"> ou
                            <input style="width: 50%;" type="file" name="justificatif_file_renouvellement" accept="application/pdf,image/*">
                        </div>

                        <!-- Option 3 -->
                        <label>
                            <input type="checkbox" name="option_remise[]"  id="option3" class="optionRemise" data-group="3" data-file="file3" data-value="Administrations et mairies -30%"> Administrations et mairies -30%
                        </label>
                        <div class="upload hidden" id="file3">
                            <input type="file" name="justificatif_admin" accept="application/pdf,image/*">
                        </div>

                        <!-- Option 4 -->
                        <label>
                            <input type="checkbox" name="option_remise[]"  id="option4" class="optionRemise" data-group="4" data-file="file4" data-value="Établissements scolaires et associations -50%"> Établissements scolaires et associations -50%
                        </label>
                        <div class="upload hidden" id="file4">
                            <input type="file" name="justificatif_association" accept="application/pdf,image/*">
                        </div>

                        <input type="hidden" name="remise_type" id="remise_type">


                    </div>
                </div>


                
                <br><br>

                <div class="">
                    <label for="comment" class="">
                        Ajouter un commentaire à ma demande :							
                    </label>
                    <textarea class="" name="comment" id="comment" rows="4"></textarea>				
                </div>

                <div class="line"></div>

                <?php if ( !is_user_logged_in() ) : ?>
                    <div class="radio">
                        <label>
                            <input type="radio" name="choice_login" value="nouveau" checked>
                            Je suis un nouveau client
                        </label>
                        <label>
                            <input type="radio" name="choice_login" value="existant" >
                            Je suis déjà client
                        </label>
                    </div>

                    <!-- Zone pour nouveau client -->
                    


                    <div id="login_nouveau" style="display: block;"><!--<form action="devis.php?dest=devis#btm" method="post" name="frm_signup" onsubmit="return(Control_SignUp_Client(this,'AJOUT'))">-->
                        <?php 
                            $pays_liste = [
                                'FR' => 'France',
                                'BE' => 'Belgique',
                                'CH' => 'Suisse',
                                'LU' => 'Luxembourg',
                                'DE' => 'Allemagne',
                            ]; 
                            ?>
                            <div class="radio">
                                <label>
                                    <input type="radio" checked="" name="new_account_type_compte" value="PAR">
                                    Particulier 
                                </label>
                                <label>
                                    <input type="radio" name="new_account_type_compte" value="PRO" >
                                    Professionnel, Association ou Institution
                                </label>
                            </div>
                            <div id="dnom_sos">
                                <label>Dénomination sociale : </label>
                                <input  type="text" title="Dénomination sociale" alt="text" name="new_account_societe" size="40" value="" >
                            </div>
                            <label>Genre : <span class="required">*</span></label>
                            <select title="Genre" id="genre" class="input_required" name="new_account_genre"  alt="Genre">
                                <option value="m" alt="Genre">Monsieur</option>
                                <option value="f" alt="Genre">Madame</option>
                                <option value="f" alt="Genre">Mademoiselle</option>
                                <option selected="" value="" alt="Genre">
                                    ----------
                                </option>
                            </select>
                            <label>Nom : <span class="required">*</span></label>
                            <input class="input_required" type="text" title="Nom" alt="text" name="new_account_nom" size="30" value="" >
                            <label>Prénom : <span class="required">*</span></label>
                            <input class="input_required" type="text" title="prenom" alt="text" name="new_account_prenom" size="30" value="" >
                            <label>Téléphone : <span class="required">*</span></label>
                            <input class="input_required" type="text" title="telephone" alt="text" name="new_account_telephone" size="30" value="" >
                            <label>Adresse : <span class="required">*</span></label>
                            <input class="input_required" type="text" title="adresse" alt="text" name="new_account_adresse" size="30" value="" >
                            <label>Ville : <span class="required">*</span></label>
                            <input class="input_required" type="text" title="ville" alt="text" name="new_account_ville" size="30" value="" >
                            <label>Code postal : <span class="required">*</span></label>
                            <input class="input_required" type="text" title="code_postal" alt="text" name="new_account_code_postal" size="30" value="" >
                            <label>Pays : <span class="required">*</span></label>
                            <select name="new_account_pays" id="pays" required class="">
                                <?php foreach ( $pays_liste as $code => $nom ) : ?>
                                    <option value="<?php echo esc_attr($code); ?>" >
                                        <?php echo esc_html($nom); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <label>
                                <input type="hidden" name="new_account_charte" value="Acceptation de la charte de confidentialité">
                                <input type="checkbox" checked name="new_account_divulgation" title="Acceptation de la charte de confidentialité" value="1" class="input_ok"  alt="checkbox" >Je comprends que mes informations ne seront pas divulguées à des tiers, conformément à <a href="charte.php" target="_blank">la charte de confidentialité</a> de ce site.
                            </label><br>   

                            <b>Mes identifiants de connexion :</b>      <br>      
                            <label>Adresse Email : <span class="required">*</span></label>
                            <input class="input_required" type="text" title="Adresse Email" alt="email" name="new_account_email" size="40" value="" >
                            <label>Confirmer Email : <span class="required">*</span></label>
                            <input class="input_required" type="text" title="Confirmer Email" alt="email" name="new_account_confirm_email" size="40" value="" >
                            <label>Mot de passe : <span class="required">*</span></label> 
                            <input id="password_1" class="input_required" type="password" title="Mot de passe" name="new_account_password_1" size="20" value="">     
                            <label>Confirmer Mot de passe : <span class="required">*</span></label> 
                            <input id="password_2" class="input_required" type="password" title="Confirmer Mot de passe" name="new_account_password_2" size="20" value="">      
                        
                    </div>

                    <!-- Zone pour client existant -->
                    <div id="login_existant" style="display:none; margin-top:10px;">
                        <label>Adresse Email :</label>
                        <input class="input_required" type="email" name="existing_account_email">
                        <br>
                        <label>Mot de passe :</label>
                        <input class="input_required" type="password" name="existing_account_password">
                    </div>
                <?php endif; ?>
                <input type="hidden" name="logged" value="<?php if ( is_user_logged_in() ) echo '1'; ?>">
                <input type="hidden" name="goal" value="devis_en_ligne">

                <button type="submit" id="send-button" class="button button-primary">Envoyer la demande de devis</button>
            </form>
            <div id="error-msg" class="error-msg" style='text-align: center;color: #d30b00ff;'></div>


            <script>
                jQuery(document).ready(function($) {

                    // cacher tout au début
                    $('#iknowForm').hide();
                    $('#dnom_sos').hide();

                    // changement au clic sur radio
                    $('input[name="choixavis"]').on('change', function() {

                        if ($('#idknow').is(':checked')) {
                            $('#idknowForm').slideDown();
                            $('#iknowForm').slideUp();
                        }

                        if ($('#iknow').is(':checked')) {
                            $('#iknowForm').slideDown();
                            $('#idknowForm').slideUp();
                        }
                    });

                    $('input[name="choice_login"]').on('change', function() {
                        if ($(this).val() === 'nouveau') {
                            $('#login_nouveau').show();
                            $('#login_existant').hide();
                        } else {
                            $('#login_nouveau').hide();
                            $('#login_existant').show();
                        }
                    });

                    $('input[name="new_account_type_compte"]').on('change', function() {
                        if ($(this).val() === 'PRO') {
                            $('#dnom_sos').show();
                        } else {
                            $('#dnom_sos').hide();
                        }
                    });
                   

                    function checkNewAccountFields() {

                        let choice_login = $('input[name="choice_login"]:checked').val();
                        let register = (choice_login=="nouveau")?true:false;
                        let sign_in = (choice_login=="existant")?true:false;
                        let allFilled = true;
                        let emailsMatch = true;
                        let passwordsMatch = true;

                        console.log("choice_login",choice_login)
                        console.log("register",register)
                        console.log("sign_in",sign_in)
                        if(register){
                            // 1️⃣ Tous les champs requis remplis ?
                            $('#login_nouveau:visible .input_required').each(function(){
                                if($(this).val().trim() === '') {
                                    allFilled = false;
                                    return false; // stop each
                                }
                            });
                        }

                        if(sign_in){
                            // 1️⃣ Tous les champs requis remplis ?
                            $('#login_existant:visible .input_required').each(function(){
                                if($(this).val().trim() === '') {
                                    allFilled = false;
                                    return false; // stop each
                                }
                            });
                        }
                        
                        
                        if(register){
                            // 2️⃣ Email correspond ?
                            let email = $('input[name="new_account_email"]').val().trim();
                            let confirmEmail = $('input[name="new_account_confirm_email"]').val().trim();
                            if(email !== confirmEmail) emailsMatch = false;

                            // 3️⃣ Mot de passe correspond ?
                            let pass1 = $('#password_1').val();
                            let pass2 = $('#password_2').val();
                            if(pass1 !== pass2) passwordsMatch = false;
                        }
                        

                        // Affichage éventuel d'un message d'erreur (optionnel)
                        
                        let msg = '';
                        if(!allFilled) msg += 'Tous les champs obligatoires doivent être remplis.<br>';
                        if(!emailsMatch) msg += 'Les adresses email ne correspondent pas.<br>';
                        if(!passwordsMatch) msg += 'Les mots de passe ne correspondent pas.<br>';
                        $('#error-msg').html(msg);
                        console.log("allFilled",allFilled)
                        console.log("emailsMatch",emailsMatch)
                        console.log("passwordsMatch",passwordsMatch)
                        console.log("msg",msg)
                        // 4️⃣ Activer/désactiver le bouton
                        $('button[type="submit"]').prop('disabled', !(allFilled && emailsMatch && passwordsMatch));
                    }

                    var logged = $('input[name="logged"]').val();
                    console.log("logged",logged)
                    if(!logged){
                        // Vérification à chaque saisie
                        $('button[type="submit"]').prop('disabled', true);
                        $('#login_nouveau .input_required, #password_1, #password_2, input[name="new_account_confirm_email"]').on('input', function(){
                            checkNewAccountFields();
                        });
                        $('#login_existant .input_required').on('input', function(){
                            checkNewAccountFields();
                        });
                        $('input[name="choice_login"]').on('change', function(){
                            checkNewAccountFields();
                        });
                    }
                   


                    $('.optionRemise').on('change', function() {

                    const $this = $(this);
                    const group = parseInt($this.data('group'));

                    // logique de combinaison
                    if (group === 1) {
                        // Option 1 seule → décocher toutes les autres
                        $('.optionRemise').not($this).prop('checked', false);
                    } else if (group === 2) {
                        // Option 2 peut être combinée avec 3 ou 4 → décocher 1
                        $('.optionRemise').each(function() {
                            if (parseInt($(this).data('group')) === 1) $(this).prop('checked', false);
                        });
                    } else if (group === 3) {
                        // décocher 1 et 4
                        $('.optionRemise').each(function() {
                            const g = parseInt($(this).data('group'));
                            if (g === 1 || g === 4) $(this).prop('checked', false);
                        });
                    } else if (group === 4) {
                        // décocher 1 et 3
                        $('.optionRemise').each(function() {
                            const g = parseInt($(this).data('group'));
                            if (g === 1 || g === 3) $(this).prop('checked', false);
                        });
                    }

                    // afficher tous les uploads correspondant aux cases cochées
                    $('.upload').addClass('hidden');
                    $('.optionRemise:checked').each(function() {
                        const fileId = $(this).data('file');
                        $('#' + fileId).removeClass('hidden');
                    });

                    // reconstruire hidden
                    let values = [];
                    $('.optionRemise:checked').each(function() {
                        values.push($(this).data('value'));
                    });
                    $('#remise_type').val(values.join(', '));
                    console.log("remises courantes",values.length,values.join(', '))
                   
                    
                });

                

                
                });


            </script>
            <style>
                .div-remise {
                    background-color: #f5f3f3;
                    padding: 15px 30px;
                    display: flex;
                    flex-direction: column;
                    gap: 11px;
                    max-width:400px;
                }
                .btn-remise:disabled {
                    background-color: #999 !important;
                    border-color: #777 !important;
                    cursor: not-allowed;
                    opacity: 0.5;
                }
                .hidden { display:none !important; }
                .block-option { margin-bottom: 12px; }
                label { font-weight: 500; cursor:pointer; }
                 span.required{
                    color: red;
                    font-size: 34px;
                }
                #alm-devis-form .line {
                    margin: 10px 5%;
                    border-radius: 10px;
                    border: 1px solid #23232342;
                }
                #alm-devis-form > button[disabled] {
                    background-color: #ccc;       /* gris clair */
                    color: #666;                  /* texte plus pâle */
                    cursor: not-allowed;          /* curseur interdit */
                    opacity: 0.6;                 /* un peu transparent */
                    pointer-events: none;         /* désactive tout clic */
                }

                /* Optionnel : bouton activé */
                #alm-devis-form > button:not([disabled]) {
                    color: #fff;
                    cursor: pointer;
                    opacity: 1;
                    transition: background-color 0.3s, opacity 0.3s;
                }

                
            </style>
        <?php      
            }

        ?>

        <?php
        return ob_get_clean();
    }


    function alm_handle_devis_form() {
        if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) return;
        if ( $_POST['goal'] !== 'devis_en_ligne' ) return;
        //var_dump($_POST); 
       // return;
        /*

        array(23) {
            ["choixavis"] => string(37) "Je ne sais pas quelle version choisir"
            ["compt2save"] => string(13) "erwrrwerewrew"
            ["prod_qty"] => array(21) {
                [4682] => string(1) "2"
                [4683] => string(1) "3"
                [4684] => string(1) "5"
                [4685] => string(1) "6"
                [4686] => string(1) "0"
                [4704] => string(1) "0"
                [4687] => string(1) "0"
                [4691] => string(1) "0"
                [4692] => string(1) "0"
                [4654] => string(1) "8"
                [4655] => string(1) "0"
                [4656] => string(1) "0"
                [4695] => string(1) "0"
                [4696] => string(1) "0"
                [4693] => string(1) "0"
                [4694] => string(1) "0"
                [4699] => string(1) "0"
                [4700] => string(1) "0"
                [4688] => string(1) "0"
                [4689] => string(1) "0"
                [4690] => string(1) "0"
            }
            ["alm_software_duration"] => string(10) "many-years"
            ["comment"] => string(14) "rewerewwrewrew"
            ["choice_login"] => string(7) "nouveau"
            ["new_account_type_compte"] => string(3) "PRO"
            ["new_account_societe"] => string(9) "rewrerewr"
            ["new_account_genre"] => string(3) "MME"
            ["new_account_nom"] => string(6) "fwerer"
            ["new_account_prenom"] => string(8) "werwerwe"
            ["new_account_telephone"] => string(12) "443424234242"
            ["new_account_adresse"] => string(9) "fewrewrew"
            ["new_account_ville"] => string(7) "werrwer"
            ["new_account_code_postal"] => string(6) "213212"
            ["new_account_pays"] => string(2) "FR"
            ["new_account_charte"] => string(44) "Acceptation de la charte de confidentialité"
            ["new_account_email"] => string(22) "afdfdsfdsfdf@gmail.com"
            ["new_account_confirm_email"] => string(22) "afdfdsfdsfdf@gmail.com"
            ["new_account_password_1"] => string(12) "afdfdsfdsfdf"
            ["new_account_password_2"] => string(12) "afdfdsfdsfdf"
            ["existing_account_email"] => string(22) "afdfdsfdsfdf@gmail.com"
            ["existing_account_password"] => string(12) "afdfdsfdsfdf"
            }

        */

        // Connexion WordPress
        if ( !function_exists('wp_get_current_user') ) return;

        $user_id = null;

        // 1️⃣ Utilisateur connecté
        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id();
        } else {
            // 2️⃣ Non connecté
            $choice_login = sanitize_text_field($_POST['choice_login'] ?? '');

            if ( $choice_login === 'existant' ) {
                // chercher l'utilisateur par email et password
                $email = sanitize_email($_POST['existing_account_email'] ?? '');
                $password = $_POST['existing_account_password'] ?? '';

                if ( $email && $password ) {
                    $user = wp_authenticate( $email, $password );
                    if ( !is_wp_error($user) ) {
                        $user_id = $user->ID;
                    } else {
                        // erreur auth
                        // return ou message d'erreur
                        wp_redirect(home_url('/devis/?status_demande=failed'));
                        exit;
                    }
                }
            } elseif ( $choice_login === 'nouveau' ) {
                // créer un compte
                $new_account_email = sanitize_email($_POST['new_account_email'] ?? '');
                $new_account_password_1 = $_POST['new_account_password_1'] ?? '';
                $new_account_password_2 = $_POST['new_account_password_2'] ?? '';
                if($new_account_password_1 != $new_account_password_2){
                    wc_add_notice('Erreur lors de la création de la demande : Les mots de passe ne coincident pas', 'error');
                    wp_redirect(home_url('/devis/?status_demande=failed'));
                        exit;
                }

                $new_account_nom = sanitize_text_field($_POST['new_account_nom'] ?? '');
                $new_account_prenom = sanitize_text_field($_POST['new_account_prenom'] ?? '');
                $new_account_type_compte = sanitize_text_field($_POST['new_account_type_compte'] ?? '');
                $type_client = ($new_account_type_compte=="PAR")?"Particulier ":"Professionnel, Association ou Institution";
                $new_account_societe = sanitize_text_field($_POST['new_account_societe'] ?? '');
                $new_account_genre = sanitize_text_field($_POST['new_account_genre'] ?? '');
                $new_account_telephone = sanitize_text_field($_POST['new_account_telephone'] ?? '');
                $new_account_adresse = sanitize_text_field($_POST['new_account_adresse'] ?? '');
                $new_account_ville = sanitize_text_field($_POST['new_account_ville'] ?? '');
                $new_account_code_postal = sanitize_text_field($_POST['new_account_code_postal'] ?? '');
                $new_account_pays = sanitize_text_field($_POST['new_account_pays'] ?? '');
                

                if ( $new_account_email && $new_account_password_1 && $new_account_nom && $new_account_prenom ) {
                    $userdata = [
                        'user_login' => $new_account_email,
                        'user_pass'  => $new_account_password_1,
                        'user_email' => $new_account_email,
                        'first_name' => $new_account_prenom,
                        'last_name'  => $new_account_nom,
                        'role'       => 'customer_particulier',
                    ];

                    $user_id = wp_insert_user($userdata);

                    if ( is_wp_error($user_id) ) {
                        wc_add_notice('Erreur lors de la création du client : ' . $user_id->get_error_message(), 'error');
                        wp_redirect(home_url('/devis/?status_demande=failed'));
                        exit;
                    }

                    // Ajout des meta
                    update_user_meta($user_id, 'type_client', $type_client);
                    update_user_meta($user_id, 'denomination', $new_account_societe);
                    update_user_meta($user_id, 'genre', $new_account_genre);
                    update_user_meta($user_id, 'billing_phone', $new_account_telephone);
                    update_user_meta($user_id, 'billing_address_1', $new_account_adresse);
                    update_user_meta($user_id, 'ville', $new_account_ville);
                    update_user_meta($user_id, 'code_postal', $new_account_code_postal);
                    update_user_meta($user_id, 'pays', $new_account_pays);
                }
            }
        }

        if(!$user_id){
            wc_add_notice('Erreur lors de la création de la demande : pas d\'utilisateur trouvé', 'error');
            wp_redirect(home_url('/devis/?status_demande=failed'));
            exit;
        }

        // 3️⃣ Gestion du devis
        $choix_av = sanitize_text_field($_POST['choixavis'] ?? '');
        
        $duration  = sanitize_text_field($_POST['alm_software_duration'] ?? '');
        $comment   = sanitize_textarea_field($_POST['comment'] ?? '');

        if ( $choix_av === 'idkn' ) {
            $compt2save = sanitize_textarea_field($_POST['compt2save'] ?? '');
            // Enregistrer $compt2save dans la table devis ou post meta
        } elseif ( $choix_av === 'ikn' ) {
            $prod_qty = $_POST['prod_qty'] ?? []; // tableau [product_id => qty]
            // Boucler et enregistrer chaque produit + quantité pour ce user/devis
        }

        /*

        creer l'objet devis en ligne post_type=devis-en-ligne  
        champs date_de_creation Sélecteur de date et heure, 
        option bouton radio(valeur/libellé retourne la valeur), 
        compt2save Zone de texte, produits_de_la_commande est un Répéteur et il a les sous champs 
        produit Relation retourne l'objet, 
        et quantite nombre,
        software_duration bouton radio(valeur/libellé retourne la valeur), 
        comment Zone de texte, 
        utilisateur Compte retourne objet du compte 

        */

                /* ------------------------------------------------------------------
           Création du devis post_type=devis-en-ligne  (structure ACF)
        ------------------------------------------------------------------*/

        // 1) Créer le post "devis en ligne"
        $post_id = wp_insert_post([
            'post_type'  => 'devis-en-ligne',
            'post_status'=> 'publish',
            'post_author'=> $user_id,
            'post_title' => 'Devis du ' . date('d/m/Y H:i'),
        ]);

        if ( is_wp_error($post_id) ) {
            wp_die("Erreur lors de la création du devis : " . $post_id->get_error_message());
        }

        // 2) Champs standard
        update_post_meta($post_id, 'date_de_creation', current_time('mysql'));
        update_post_meta($post_id, 'option', $choix_av);  
        update_post_meta($post_id, 'software_duration', $duration);
        update_field('status', 'en_attente', $remise_id);
        update_post_meta($post_id, 'comment', $comment);
        update_post_meta($post_id, 'utilisateur', $user_id);

        // 3) choix → "je ne sais pas quelle version"
        if ( $choix_av === 'idkn' ) {
            update_post_meta($post_id, 'compt2save', $compt2save);
        }

        // 4) choix → "je sais ce dont j’ai besoin" (RÉPÉTEUR ACF)
        if ( $choix_av === 'ikn' && !empty($prod_qty)) {

            // reset le repeater
            delete_post_meta($post_id, 'produits_de_la_commande');

            foreach ($prod_qty as $product_id => $qty) {
                $qty = intval($qty);
                if ($qty > 0) {

                    // crée une rangée dans le répéteur ACF
                    add_row('produits_de_la_commande', [
                        'produit'  => $product_id,   // relation vers objet produit 
                        'quantite' => $qty,
                    ], $post_id);
                }
            }
        }


        // 5) remises

        // Tableau pour associer option → input file
        $file_fields_map = [
            "Changement -25%" => 'justificatif_changement',
            "Renouvellement de licences -30%"        => 'justificatif_text_renouvellement|justificatif_file_renouvellement',
            "Administrations et mairies -30%"       => 'justificatif_admin',
            "Établissements scolaires et associations -50%" => 'justificatif_association'
        ];

        $percentage_fields_map = [
            "Changement -25%" => 25,
            "Renouvellement de licences -30%"        => 30,
            "Administrations et mairies -30%"       => 30,
            "Établissements scolaires et associations -50%" => 50
        ];

        if ( !empty($_POST['remise_type']) ) {

            // Split sur la virgule pour récupérer chaque option
            $options = array_map('trim', explode(',', $_POST['remise_type']));

            foreach ($options as $option) {

                // 1️⃣ Créer la remise CPT
                $remise_id = wp_insert_post([
                    'post_type'   => 'remise',
                    'post_title'  => "Demande de remise : $option - Utilisateur $user_id",
                    'post_status' => 'publish',
                    'post_author' => $user_id,
                ]);

                if ( is_wp_error($remise_id) ) continue;

                // 2️⃣ Champs ACF
                update_field('utilisateur', $user_id, $remise_id);
                update_field('compte', [$user_id], $remise_id);
                update_field('statut', 'en attente', $remise_id);
                update_field('pourcentage', $percentage_fields_map[$option], $remise_id);
                update_field('date_de_creation', current_time('d/m/Y g:i a'), $remise_id);
                $expiration = date('Y-m-d H:i:s', strtotime('+1 month'));
                update_field('date_dexpiration', $expiration, $remise_id);

                // 3️⃣ Upload du fichier correspondant
                $fields  = $file_fields_map[$option] ?? '';
                $fieldnames = explode('|', $fields); // permet de gérer plusieurs champs
                foreach ($fieldnames as $fieldname) {
                    // Champ texte
                    if (isset($_POST[$fieldname]) && !empty($_POST[$fieldname])) {
                        update_field("justificatif_texte", sanitize_text_field($_POST[$fieldname]), $remise_id);
                    }

                    // Champ fichier
                    if (isset($_FILES[$fieldname]) && !empty($_FILES[$fieldname]['name'])) {
                        require_once(ABSPATH . 'wp-admin/includes/file.php');
                        require_once(ABSPATH . 'wp-admin/includes/media.php');
                        require_once(ABSPATH . 'wp-admin/includes/image.php');

                        $file_id = media_handle_upload($fieldname, $remise_id);
                        if (!is_wp_error($file_id)) {
                            update_field("justificatif", $file_id, $remise_id);
                        }
                    }
                }
                
            }

            wc_add_notice("Vos demandes de remise ont été envoyées avec succès.", "success");
        }

        // 6) fin → redirection
        wp_redirect(home_url('/devis/?status_demande=success'));
        exit;

    }



    function alm_force_quantity_after_add_to_cart( $cart_item_key, $product_id, $quantity ) {
        $cart_item = WC()->cart->get_cart_item( $cart_item_key );
        if ( isset($cart_item['alm_quantity2']) ) {
            $new_quantity = intval($cart_item['alm_quantity2']);
            WC()->cart->set_quantity( $cart_item_key, $new_quantity, true );
        }
    }


    function alm_save_custom_fields_to_cart( $cart_item_data, $product_id ) {

        if ( isset($_POST['alm_duree2']) ) {
            $cart_item_data['alm_duree'] = sanitize_text_field($_POST['alm_duree2']);
        }

        if ( isset($_POST['alm_quantity2']) ) {
            $cart_item_data['alm_quantity2'] = sanitize_text_field($_POST['alm_quantity2']);

            $cart_item_data['quantity'] = intval($_POST['alm_quantity2']);
             $new_quantity = intval($_POST['alm_quantity2']);
        }

        return $cart_item_data;
    }

    function alm_save_alm_duree_before_quote_update() {

    // sécurité : pas de sortie
    ob_start();

    // sécurisation session
    $quotes = WC()->session->get('quotes');

    if (!is_array($quotes)) {
        WC()->session->set('quotes', []);
        return;
    }

    // récupération des données formulaire
    if ( isset( $_POST['form_data'] ) ) {
        if ( isset( $_POST['form_data'] ) ) {
			parse_str( sanitize_meta( '', wp_unslash( $_POST['form_data'] ), '' ), $form_data );
		} else {
			$form_data = '';
		}
    }

    // ajout du champ
    foreach ($quotes as $quote_item_key => $quote_item) {
        
        if (isset($form_data['alm_duree'][$quote_item_key])) {
            
            $quotes[$quote_item_key]['alm_duree'] =
                sanitize_text_field($form_data['alm_duree'][$quote_item_key]);
        }
        $quotes[ $quote_item_key ]['alm_duree'] = ( $form_data['alm_duree'][ $quote_item_key ] );
    }

    // resauvegarde
    WC()->session->set('quotes', $quotes);

    // vide le buffer — pour éviter toute sortie
    ob_end_clean();
}






}
