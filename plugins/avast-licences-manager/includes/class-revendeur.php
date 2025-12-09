<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ALM_Revendeur {

    public function __construct() {

     

        add_shortcode('alm_revendeur_form', [$this, 'alm_render_revendeur_form']);
        add_action('init', [$this, 'alm_handle_revendeur_form']);

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


    function alm_render_revendeur_form() {
        ob_start(); ?>

        <?php
            if(isset($_GET["status_demande"]) ){?>
            <div id="auto-popup" role="alert" aria-hidden="true" style="display:none;">
                <div class="auto-popup-inner">
                    <a href="#" class="auto-popup-close">&times;</a>
                    <div class="auto-popup-content">
                        <?php if(($_GET["status_demande"])=="success" ){
                            echo "<div class='msg-box success' style='font-weight: bolder;text-align: center;padding: 4px 10px;color: #00d369;'>Demande de création de compte revendeur envoyée avec succès.</div>";
                        }else{
                            echo "<div class='msg-box failure' style='font-weight: bolder;text-align: center;padding: 4px 10px;color: #d30b00ff;'>Échec de la demande.</div>";
                        }?>
                    </div>
                </div>
            </div>
               
                
        <?php }?>


        <?php
            if(!isset($_GET["status_demande"]) || (isset($_GET["status_demande"]) && !(($_GET["status_demande"])=="success")) ){
        ?>   
          
            <form method="post" id="alm-revendeur-form" class="alm-revendeur-form" enctype="multipart/form-data">
                
                    


                    <div id="revendeur_form" >
                        <?php 
                            $pays_liste = [
                                'AL' => 'Albanie',
                                'DE' => 'Allemagne',
                                'AD' => 'Andorre',
                                'AT' => 'Autriche',
                                'BE' => 'Belgique',
                                'BY' => 'Biélorussie',
                                'BA' => 'Bosnie-Herzégovine',
                                'BG' => 'Bulgarie',
                                'HR' => 'Croatie',
                                'DK' => 'Danemark',
                                'ES' => 'Espagne',
                                'EE' => 'Estonie',
                                'FI' => 'Finlande',
                                'FR' => 'France',
                                'GR' => 'Grèce',
                                'HU' => 'Hongrie',
                                'IE' => 'Irlande',
                                'IS' => 'Islande',
                                'IT' => 'Italie',
                                'XK' => 'Kosovo',
                                'LV' => 'Lettonie',
                                'LI' => 'Liechtenstein',
                                'LT' => 'Lituanie',
                                'LU' => 'Luxembourg',
                                'MK' => 'Macédoine du Nord',
                                'MT' => 'Malte',
                                'MD' => 'Moldavie',
                                'MC' => 'Monaco',
                                'ME' => 'Montenegro',
                                'NO' => 'Norvège',
                                'NL' => 'Pays-Bas',
                                'PL' => 'Pologne',
                                'PT' => 'Portugal',
                                'CZ' => 'République Tchèque',
                                'RO' => 'Roumanie',
                                'GB' => 'Royaume-Uni (UK)',
                                'RU' => 'Russie',
                                'SM' => 'San Marino',
                                'RS' => 'Serbie',
                                'SK' => 'Slovaquie',
                                'SI' => 'Slovénie',
                                'SE' => 'Suède',
                                'CH' => 'Suisse',
                                'UA' => 'Ukraine',
                                'VA' => 'Vatican',
                                'AX' => 'Åland Islands',
                                'GG' => 'Guernesey',
                                'JE' => 'Jersey',
                                'IM' => 'Île de Man',
                                'FO' => 'Îles Féroé',
                                'GI' => 'Gibraltar',
                                'SJ' => 'Svalbard et Jan Mayen',
                            ];
                            ?>
                            <div class="count-clmn" style="margin-bottom:20px;">
                            <div id="dnom_sos">
                                <label>Dénomination sociale : </label>
                                <input  type="text" title="Dénomination sociale" alt="text" name="new_revendeur_account_societe" size="40" value="" >
                            </div>
                            <div>
                            <label>Genre : <span class="required">*</span></label>
                            <select title="Genre" id="genre" class="input_required" name="new_revendeur_account_genre"  alt="Genre">
                                <option value="m" alt="Genre">Monsieur</option>
                                <option value="f" alt="Genre">Madame</option>
                                <option value="f" alt="Genre">Mademoiselle</option>
                                <option selected="" value="" alt="Genre">
                                    ----------
                                </option>
                            </select>
                           </div>
                           <div>
                            <label>Nom : <span class="required">*</span></label>
                            <input class="input_required" type="text" title="Nom" alt="text" name="new_revendeur_account_nom" size="30" value="" >
                        </div>
                        <div>
                            <label>Prénom : <span class="required">*</span></label>
                            <input class="input_required" type="text" title="prenom" alt="text" name="new_revendeur_account_prenom" size="30" value="" >
                            </div>
                            <div>
                            <label>Téléphone : <span class="required">*</span></label>
                            <input class="input_required" type="text" title="telephone" alt="text" name="new_revendeur_account_telephone" size="30" value="" >
                            </div>
                        <div>
                            <label>Adresse : <span class="required">*</span></label>
                            <input class="input_required" type="text" title="adresse" alt="text" name="new_revendeur_account_adresse" size="30" value="" >
                            </div>
                        <div>
                            <label>Ville : <span class="required">*</span></label>
                            <input class="input_required" type="text" title="ville" alt="text" name="new_revendeur_account_ville" size="30" value="" >
                            </div>
                        <div>
                            <label>Code postal : <span class="required">*</span></label>
                            <input class="input_required" type="text" title="code_postal" alt="text" name="new_revendeur_account_code_postal" size="30" value="" >
                            </div>
                        <div>
                            <label>Pays : <span class="required">*</span></label>
                            <select name="new_revendeur_account_pays" id="pays" required class="">
                                <?php foreach ( $pays_liste as $code => $nom ) : ?>
                                    <option value="<?php echo esc_attr($code); ?>" >
                                        <?php echo esc_html($nom); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            </div>
                            <div>
                        <label>
                            Justificatif d'immatriculation : <span class="required">*</span>
                        </label>
                        <div class="upload" >
                            <input type="file" name="new_revendeur_account_justificatif_immatriculation" accept="application/pdf/*">  
                        </div>
                        </div>
                        <span>
                                Le document envoyé doit impérativement être fourni au format pdf.
                         </span>
                        <br>
                        Document obligatoire justifiant de l'identité de votre entreprise ou de votre structure commerciale.<br><br>
                        </div>
                        <br>
                        Pour la France :<br>
                        extrait K-bis datant de moins de 6 mois<br><br>
                        Pour les autres pays :<br>
                        Justificatif d'immatriculation au registre des entreprises, ou tout autre document pouvant nous permettre de valider l'identité de votre structure commerciale. Ce document devra être daté de moins de 6 mois.<br><br>
                        Vous pouvez enregistrer votre demande maintenant et fournir ulterieurement votre justificatif. Votre compte revendeur ne pourra cependant être activé qu'après réception et validation de votre justificatif par notre équipe.<br>
                    <br>
                    <div id="boxtva" name="boxtva" style="display: block;">            
                        <b>Régime de TVA applicable :</b>
                        <br><br>
                       
                        
                        <div style="width: 100%; display: flex; align-items: flex-start; gap: 7px;">
                            <input type="radio" id="regime_2" checked="" name="new_revendeur_account_regime_tva" value="2" style="width: auto" >
                            <label style="line-height: 1.5;"><b>Facturation TTC faisant ressortir la TVA</b> (pays de l'union) Facturation avec TVA de 20%</label>
                                </div>
                        <br>
                    
                        <div style="">
                            <div style="display: flex;align-items: flex-start;justify-content: flex-start;gap: 7px;">
                            <input type="radio" id="regime_1" name="new_revendeur_account_regime_tva" value="1" style="width: auto" >
                            <label style="line-height: 1.5;"><b>Facturation HT</b> (pour les pays de l'union Européenne, hors France) Merci de justifier ci dessous d'un numéro de TVA Intra valide :</label>
                            </div>
                            <div id="tva_regime_1_box">
                                <div id="tva_regime_1_box2">
                                N° TVA intracommunautaire:
                                <select title="Prefixe TVA" id="prefixe_tva" name="new_revendeur_account_prefixe_tva" alt="Prefixe TVA">
                                    <option selected="" value="" alt="Prefixe TVA">--</option>
                                    <option value="AT" alt="Prefixe TVA">AT</option>
                                    <option value="BE" alt="Prefixe TVA">BE</option>
                                    <option value="BG" alt="Prefixe TVA">BG</option>
                                    <option value="CY" alt="Prefixe TVA">CY</option>
                                    <option value="CZ" alt="Prefixe TVA">CZ</option>
                                    <option value="DE" alt="Prefixe TVA">DE</option>
                                    <option value="DK" alt="Prefixe TVA">DK</option>
                                    <option value="EE" alt="Prefixe TVA">EE</option>
                                    <option value="EL" alt="Prefixe TVA">EL</option>
                                    <option value="ES" alt="Prefixe TVA">ES</option>
                                    <option value="FI" alt="Prefixe TVA">FI</option>
                                    <option value="FR" alt="Prefixe TVA">FR</option>
                                    <option value="GB" alt="Prefixe TVA">GB</option>
                                    <option value="HU" alt="Prefixe TVA">HU</option>
                                    <option value="IE" alt="Prefixe TVA">IE</option>
                                    <option value="IT" alt="Prefixe TVA">IT</option>
                                    <option value="LT" alt="Prefixe TVA">LT</option>
                                    <option value="LU" alt="Prefixe TVA">LU</option>
                                    <option value="LV" alt="Prefixe TVA">LV</option>
                                    <option value="MT" alt="Prefixe TVA">MT</option>
                                    <option value="NL" alt="Prefixe TVA">NL</option>
                                    <option value="PL" alt="Prefixe TVA">PL</option>
                                    <option value="PT" alt="Prefixe TVA">PT</option>
                                    <option value="RO" alt="Prefixe TVA">RO</option>
                                    <option value="SE" alt="Prefixe TVA">SE</option>
                                    <option value="SI" alt="Prefixe TVA">SI</option>
                                    <option value="SK" alt="Prefixe TVA">SK</option>
                                </select>
                                <input  style="width: auto" type="text" name="new_revendeur_account_tva_intra" value="" size="25" onblur="IsRequiredOk(this)">
                                </div>
                                <br>
                                <span style="font-size:11px;position: relative;top: -24px;"> 
                                    Obligatoire pour facturation Hors TVA pour les sociétés situées dans un pays de l'Union Européenne et hors de France.
                                </span>
                            </div>
                           
                                </div>
                        <br>
                
                        <b>Franchise de TVA</b><p>
                        Contactez-nous pour que nous puissions paramétrer spécifiquement votre compte, sur présentation d'un justificatif de situation, et vous permettre de passer vos commandes avec le taux de TVA qui vous est applicable.</p></td>
                
                    </div>




                    <br>  <br>

                         <b>Mes identifiants de connexion :</b>      <br>  <br>
                         <div class="count-clmn" style="">
                            <div>    
                            <label>Adresse Email : <span class="required">*</span></label>
                            <input class="input_required" type="text" title="Adresse Email" alt="email" name="new_revendeur_account_email" size="40" value="" >
                            </div>
                            <div>
                            <label>Confirmer Email : <span class="required">*</span></label>
                            <input class="input_required" type="text" title="Confirmer Email" alt="email" name="new_revendeur_account_confirm_email" size="40" value="" >
                            </div>
                            <div>
                            <label>Mot de passe : <span class="required">*</span></label> 
                            <input id="password_1" class="input_required" type="password" title="Mot de passe" name="new_revendeur_account_password_1" size="20" value="">     
                            </div>
                            <div>
                            <label>Confirmer Mot de passe : <span class="required">*</span></label> 
                            <input id="password_2" class="input_required" type="password" title="Confirmer Mot de passe" name="new_revendeur_account_password_2" size="20" value="">      
                            </div>
                            </div>



                            <label  style="line-height: 1.5; margin-block:20px 15px;">
                                <input type="hidden" name="new_revendeur_account_charte" value="Acceptation de la charte de confidentialité">
                                <input type="checkbox" style="line-height: 1.5;" checked name="new_revendeur_account_divulgation" title="Acceptation de la charte de confidentialité" value="1" class="input_ok"  alt="checkbox" >
                                Je comprends que mes informations ne seront pas divulguées à des tiers, conformément à <a href="charte.php" target="_blank">la charte de confidentialité</a> de ce site.
                            </label> 
                            <label  style="line-height: 1.5;">
                                <input type="checkbox" name="new_revendeur_account_agree_cgr" value="1">
                                Je reconnais avoir pris connaissance et accepter pleinement les Conditions Générales Revendeur, <a href="#" onclick="openWin(&quot;cgr.php&quot;);">disponibles ici</a>.
                            </label>
                            <br> 
     
                            Vous recevrez rapidement un email confirmant l'enregistrement de votre demande d'ouverture de compte
                           
                           
                           
                    </div>

                <input type="hidden" name="goal" value="devenir_revendeur">

                <button type="submit" id="send-button" style="margin-top:15px;" class="elementor-element elementor-align-center elementor-widget elementor-widget-button">Confirmer ma demande d'ouverture de compte revendeur</button>
            </form>
            <div id="error-msg" class="error-msg" style='text-align: center;color: #d30b00ff;margin-top: 20px;font-size: 15px;'></div>

        <?php   }?>

            <script>
                jQuery(document).ready(function($) {

                    
                    $('#tva_regime_1_box').hide();

                     $('input[name="new_revendeur_account_regime_tva"]').on('change', function() {

                        if ($('#regime_1').is(':checked')) {
                            $('#tva_regime_1_box').show();
                        }

                        if ($('#regime_2').is(':checked')) {
                            $('#tva_regime_1_box').hide();
                        }
                    });
                   

                    function checkNewAccountFields() {

                        let allFilled = true;
                        let emailsMatch = true;
                        let passwordsMatch = true;

                        // 1️⃣ Tous les champs requis remplis ?
                        $('#revendeur_form:visible .input_required').each(function(){
                            if($(this).val().trim() === '') {
                                allFilled = false;
                                return false; // stop each
                            }
                        });
                        
                        // 2️⃣ Email correspond ?
                        let email = $('input[name="new_revendeur_account_email"]').val().trim();
                        let confirmEmail = $('input[name="new_revendeur_account_confirm_email"]').val().trim();
                        if(email !== confirmEmail) emailsMatch = false;

                        // 3️⃣ Mot de passe correspond ?
                        let pass1 = $('#password_1').val();
                        let pass2 = $('#password_2').val();
                        if(pass1 !== pass2) passwordsMatch = false;
                        
                        

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

                   
                    // Vérification à chaque saisie
                    $('button[type="submit"]').prop('disabled', true);
                    $('#revendeur_form .input_required, #password_1, #password_2, input[name="new_revendeur_account_confirm_email"]').on('input', function(){
                        checkNewAccountFields();
                    });


                    const $popup = $('#auto-popup');

                    // Si la popup est présente dans le DOM → on l'affiche
                    if ($popup.length) {
                        console.log("afficher popup")
                        $popup.fadeIn(200);

                        // la cacher dans 5 secondes
                        setTimeout(function(){
                            $popup.fadeOut(300);
                        }, 15000);

                        // bouton fermer
                        $popup.on('click', '.auto-popup-close', function(){
                            $popup.fadeOut(200);
                        });
                    }else{
                        console.log("non afficher popup")
                    }
                
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
                #alm-revendeur-form > button[disabled] {
                    background-color: #ccc;       /* gris clair */
                    color: #666;                  /* texte plus pâle */
                    cursor: not-allowed;          /* curseur interdit */
                    opacity: 0.6;                 /* un peu transparent */
                    pointer-events: none;   
                    border:none;      /* désactive tout clic */
                }

                /* Optionnel : bouton activé */
                #alm-revendeur-form > button:not([disabled]) {
                    color: #fff;
                    cursor: pointer;
                    opacity: 1;
                    transition: background-color 0.3s, opacity 0.3s;
                }

                /* CSS : popup centrée, simple et responsive */
                #auto-popup{
                position: fixed;
                left: 50%;
                top: 50%;
                transform: translate(-50%, -50%);
                z-index: 99999;
                display: none;
                width: calc(100% - 40px);
                max-width: 520px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.25);
                border-radius: 10px;
                background: transparent;
                pointer-events: none; /* évite d'intercepter les clics en dehors de la box */
                }

                #auto-popup .auto-popup-inner{
                pointer-events: auto; /* permet l'interaction à l'intérieur */
                background: #ffffff;
                padding: 18px 18px 14px 18px;
                border-radius: 8px;
                text-align: center;
                font-family: Arial, sans-serif;
                color: #222;
                }

                .auto-popup-close{
                position: absolute;
                right: 8px;
                top: 6px;
                background: transparent;
                border: none;
                font-size: 30px;
                line-height: 1;
                color: #666;
                cursor: pointer;
                }

                .auto-popup-content{
                font-size: 15px;
                line-height: 1.4;
                }

                /* petite animation d'entrée */
                .auto-popup-show {
                animation: popup-in 280ms ease-out;
                }
                @keyframes popup-in {
                from { transform: translate(-50%, -45%) scale(0.98); opacity: 0; }
                to   { transform: translate(-50%, -50%) scale(1); opacity: 1; }
                }

                
            </style>
       

        <?php
        return ob_get_clean();
    }


    function alm_handle_revendeur_form() {
        if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) return;
        if ( $_POST['goal'] !== 'devenir_revendeur' ) return;
        
        /*

        array(20) {
            ["new_revendeur_account_societe"] => string(4) "test"
            ["new_revendeur_account_genre"] => string(1) "f"
            ["new_revendeur_account_nom"] => string(4) "test"
            ["new_revendeur_account_prenom"] => string(4) "test"
            ["new_revendeur_account_telephone"] => string(9) "123312121"
            ["new_revendeur_account_adresse"] => string(4) "test"
            ["new_revendeur_account_ville"] => string(4) "test"
            ["new_revendeur_account_code_postal"] => string(5) "12221"
            ["new_revendeur_account_pays"] => string(2) "FR"
            ["new_revendeur_account_regime_tva"] => string(1) "2"
            ["prefixe_tva"] => string(0) ""
            ["tva_intra"] => string(0) ""
            ["new_revendeur_account_email"] => string(14) "test@gmail.com"
            ["new_revendeur_account_confirm_email"] => string(14) "test@gmail.com"
            ["new_revendeur_account_password_1"] => string(12) "testtesttest"
            ["new_revendeur_account_password_2"] => string(12) "testtesttest"
            ["new_revendeur_account_charte"] => string(44) "Acceptation de la charte de confidentialité"
            ["new_revendeur_account_divulgation"] => string(1) "1"
            ["new_revendeur_account_agree_cgr"] => string(1) "1"
            ["goal"] => string(17) "devenir_revendeur"
            }

        */

        // Connexion WordPress
        if ( !function_exists('wp_get_current_user') ) return;

        $user_id = null;

        
        // créer un compte
        $new_revendeur_account_email = sanitize_email($_POST['new_revendeur_account_email'] ?? '');
        $new_revendeur_account_password_1 = $_POST['new_revendeur_account_password_1'] ?? '';
        $new_revendeur_account_password_2 = $_POST['new_revendeur_account_password_2'] ?? '';
        if($new_revendeur_account_password_1 != $new_revendeur_account_password_2){
            wc_add_notice('Erreur lors de la création de la demande : Les mots de passe ne coincident pas', 'error');
            wp_redirect(home_url('/devenir-revendeur-avast/?status_demande=failed'));
                exit;
        }

        $new_revendeur_account_nom = sanitize_text_field($_POST['new_revendeur_account_nom'] ?? '');
        $new_revendeur_account_prenom = sanitize_text_field($_POST['new_revendeur_account_prenom'] ?? '');
        $new_revendeur_account_societe = sanitize_text_field($_POST['new_revendeur_account_societe'] ?? '');
        $new_revendeur_account_genre = sanitize_text_field($_POST['new_revendeur_account_genre'] ?? '');
        $new_revendeur_account_telephone = sanitize_text_field($_POST['new_revendeur_account_telephone'] ?? '');
        $new_revendeur_account_adresse = sanitize_text_field($_POST['new_revendeur_account_adresse'] ?? '');
        $new_revendeur_account_ville = sanitize_text_field($_POST['new_revendeur_account_ville'] ?? '');
        $new_revendeur_account_code_postal = sanitize_text_field($_POST['new_revendeur_account_code_postal'] ?? '');
        $new_revendeur_account_pays = sanitize_text_field($_POST['new_revendeur_account_pays'] ?? '');
        $new_revendeur_account_agree_cgr = isset($_POST['new_revendeur_account_agree_cgr']) ? 1 : 0;
        $new_revendeur_account_divulgation = isset($_POST['new_revendeur_account_divulgation']) ? 1 : 0;
        $new_revendeur_account_tva_intra = sanitize_text_field($_POST['new_revendeur_account_tva_intra'] ?? '');
        $new_revendeur_account_regime_tva = sanitize_text_field($_POST['new_revendeur_account_regime_tva'] ?? '');
        $new_revendeur_account_prefixe_tva = sanitize_text_field($_POST['new_revendeur_account_prefixe_tva'] ?? '');

        

        
                // 1️⃣ Créer la remise CPT
                $demande_id = wp_insert_post([
                    'post_type'   => 'demande_revendeur',
                    'post_title'  => "Demande de création de compte revendeur ". date('d/m/Y H:i'),
                    'post_status' => 'publish',
                ]);

                if ( is_wp_error($demande_id) ) return;

                // 2️⃣ Champs ACF
                update_field('account_nom', $new_revendeur_account_nom, $demande_id);
                update_field('account_prenom', $new_revendeur_account_prenom, $demande_id);
                update_field('account_societe', $new_revendeur_account_societe, $demande_id);
                update_field('account_genre', $new_revendeur_account_genre, $demande_id);
                update_field('account_telephone', $new_revendeur_account_telephone, $demande_id);
                update_field('account_adresse', $new_revendeur_account_adresse, $demande_id);
                update_field('account_ville', $new_revendeur_account_ville, $demande_id);
                update_field('account_code_postal', $new_revendeur_account_code_postal, $demande_id);
                update_field('account_pays', $new_revendeur_account_pays, $demande_id);
                update_field('account_divulgation', $new_revendeur_account_divulgation, $demande_id);
                update_field('account_agree_cgr', $new_revendeur_account_agree_cgr, $demande_id);
                update_field('account_email', $new_revendeur_account_email, $demande_id);
                update_field('account_mot_de_passe', $new_revendeur_account_password_1, $demande_id);
                update_field('account_tva_intra', $new_revendeur_account_tva_intra, $demande_id);
                update_field('account_regime_tva', $new_revendeur_account_regime_tva, $demande_id);
                update_field('account_prefixe_tva', $new_revendeur_account_prefixe_tva, $demande_id);

                
                if (isset($_FILES["new_revendeur_account_justificatif_immatriculation"]) && !empty($_FILES["new_revendeur_account_justificatif_immatriculation"]['name'])) {
                    require_once(ABSPATH . 'wp-admin/includes/file.php');
                    require_once(ABSPATH . 'wp-admin/includes/media.php');
                    require_once(ABSPATH . 'wp-admin/includes/image.php');

                    $file_id = media_handle_upload("new_revendeur_account_justificatif_immatriculation", $demande_id);
                    if (!is_wp_error($file_id)) {
                        update_field("account_justificatif_immatriculation", $file_id, $demande_id);
                    }
                }
                
         

        if(!$demande_id){
            wc_add_notice('Erreur lors de la création de la demande', 'error');
            wp_redirect(home_url('/devenir-revendeur-avast/?status_demande=failed'));
            exit;
        }


        // 6) fin → redirection
        wp_redirect(home_url('/devenir-revendeur-avast/?status_demande=success'));
        exit;

    }







}
