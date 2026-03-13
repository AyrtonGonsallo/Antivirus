<?php
defined('ABSPATH') || exit;

global $wp;

// Vérifier si l'utilisateur est un revendeur
if (!current_user_can('customer_revendeur')) {
    wp_safe_redirect(home_url());
    exit;
}

// Message de validation/soumission (sera géré après)
if (isset($_GET['client_edited']) && $_GET['client_edited'] == 'true') {
    echo '<div class="woocommerce-message">Client modifié avec succès </div>';
}
?>



<?php
$pays_par_groupe = include __DIR__ . '/../includes/countries.php';
$revendeur_id = get_current_user_id();
$client_id = intval($wp->query_vars['client']);
$args = [
    'include'    => [$client_id],   // chercher cet utilisateur précis
    'role'       => 'customer_direct',
    'meta_key'   => 'revendeur_id',
    'meta_value' => $revendeur_id
];

$clients = get_users($args);
$client = !empty($clients) ? $clients[0] : null;


$type_client   = get_user_meta($client->ID, 'type_client', true);
$denomination  = get_user_meta($client->ID, 'denomination', true);
$nom           = get_user_meta($client->ID, 'last_name', true);
$prenom        = get_user_meta($client->ID, 'first_name', true);
$email         = $client->user_email;
$billing_phone     = get_user_meta($client->ID, 'billing_phone', true);
$fax           = get_user_meta($client->ID, 'fax', true);
$billing_address_1       = get_user_meta($client->ID, 'billing_address_1', true);
$ville         = get_user_meta($client->ID, 'ville', true);
$code_postal   = get_user_meta($client->ID, 'code_postal', true);
$selected_pays          = get_user_meta($client->ID, 'pays', true);
$civilite          = get_user_meta($client->ID, 'civilite', true);



$user_has_disabled_remises = has_user_disabled_remises($client->ID);
$user_has_enabled_remises = has_user_enabled_remises($client->ID);
$remise_c = get_user_remise_by_type($client->ID,"Changement -25%");
$remise_r = get_user_remise_by_type($client->ID,"Renouvellement de licences -30%");
$remise_a = get_user_remise_by_type($client->ID,"Administrations et mairies -30%");
$remise_e = get_user_remise_by_type($client->ID,"Établissements scolaires et associations -50%");

$desactiver1 = $user_has_disabled_remises || $remise_r || $remise_a || $remise_e;
$desactiver2 = $user_has_disabled_remises || $remise_c;
$desactiver3 = $user_has_disabled_remises || $remise_c || $remise_e;
$desactiver4 = $user_has_disabled_remises || $remise_a || $remise_c;

$current_remises .= ($remise_c)?get_field('type', $remise_c):"";
$current_remises .= ($remise_r)?get_field('type', $remise_r):"";
$current_remises .= ($remise_a)?get_field('type', $remise_a):"";
$current_remises .= ($remise_e)?get_field('type', $remise_e):"";
?>


<h2>modifier un client</h2>

    
<form method="post" class="woocommerce-EditAccountForm">

    <input type="hidden" name="client_id" value="<?php echo esc_attr($client_id); ?>">
    
    <p class="form-row ">
        <label for="type_client">Type de client <span class="required">*</span></label>
        <select name="type_client" id="type_client" required>
            <option value="">-- Sélectionner --</option>
            <option value="particulier" <?php selected($type_client, 'particulier'); ?>>Particulier</option>
            <option value="professionnel" <?php selected($type_client, 'professionnel'); ?>>Professionnel</option>
            <option value="association_ou_institution" <?php selected($type_client, 'association_ou_institution'); ?>>Association ou Institution</option>
        </select>
    </p>

    <p class="form-row ">
        <label for="denomination">Dénomination sociale </label>
        <input value="<?php echo esc_attr($denomination); ?>" type="text" maxlength="100" name="denomination" id="denomination" class="woocommerce-Input woocommerce-Input--text input-text"/>
    </p>
    <div class="clear"></div>
    <p class="form-row ">
        <label for="civilite">Civilité <span class="required">*</span></label>
        <select name="civilite" id="civilite" required >
            <option value="">Sélectionnez...</option>
            <option value="Monsieur" <?php selected($civilite, 'Monsieur'); ?>>Monsieur</option>
            <option value="Madame" <?php selected($civilite, 'Madame'); ?>>Madame</option>
            <option value="Mademoiselle" <?php selected($civilite, 'Mademoiselle'); ?>>Mademoiselle</option>
        </select>
    </p>

    <p class="form-row ">
        <label for="nom">Nom <span class="required">*</span></label>
        <input value="<?php echo esc_attr($nom); ?>" type="text" maxlength="50" name="nom" id="nom" required class="woocommerce-Input woocommerce-Input--text input-text"/>
    </p>

    <p class="form-row ">
        <label for="prenom">Prénom <span class="required">*</span></label>
        <input value="<?php echo esc_attr($prenom); ?>" type="text" maxlength="50" name="prenom" id="prenom" required class="woocommerce-Input woocommerce-Input--text input-text"/>
    </p>
    <div class="clear"></div>

    <p class="form-row">
        <label for="email">Adresse email <span class="required">*</span></label>
        <input value="<?php echo esc_attr($email); ?>" type="email" maxlength="70" name="email" id="email" required class="woocommerce-Input woocommerce-Input--text input-text"/>
    </p>

    <p class="form-row ">
        <label for="billing_phone">Téléphone <span class="required">*</span></label>
        <input value="<?php echo esc_attr($billing_phone); ?>" type="text" maxlength="20" name="billing_phone" id="billing_phone" required class="woocommerce-Input woocommerce-Input--text input-text"/>
    </p>

    <p class="form-row ">
        <label for="fax">Fax</label>
        <input value="<?php echo esc_attr($fax); ?>" type="text" name="fax" id="fax" class="woocommerce-Input woocommerce-Input--text input-text"/>
    </p>
    <div class="clear"></div>

    <p class="form-row">
        <label for="billing_address_1">Adresse <span class="required">*</span></label>
        <input value="<?php echo esc_attr($billing_address_1); ?>" type="text" maxlength="70" name="billing_address_1" id="billing_address_1" required class="woocommerce-Input woocommerce-Input--text input-text"/>
    </p>

    <p class="form-row ">
        <label for="ville">Ville <span class="required">*</span></label>
        <input value="<?php echo esc_attr($ville); ?>" type="text" maxlength="30" name="ville" id="ville" required class="woocommerce-Input woocommerce-Input--text input-text"/>
    </p>

    <p class="form-row ">
        <label for="code_postal">Code postal <span class="required">*</span></label>
        <input value="<?php echo esc_attr($code_postal); ?>" type="text" maxlength="6" name="code_postal" id="code_postal" required class="woocommerce-Input woocommerce-Input--text input-text"/>
    </p>
    <div class="clear"></div>

    <p class="form-row">
        <label for="pays">Pays <span class="required">*</span></label>
        
        <select name="pays" id="pays" required class="woocommerce-Input woocommerce-Input--text input-text">
			
             <?php foreach ( $pays_par_groupe as $groupe => $pays ) : ?>
                <optgroup label="<?php echo esc_attr($groupe); ?>">
                    <?php foreach ( $pays as $pays_data ) : ?>
                        <option value="<?php echo esc_attr($pays_data['value']); ?>" 
                            <?php selected($selected_pays, $pays_data['value']); ?>>
                            <?php echo esc_html($pays_data['nom']); ?>
                        </option>
                    <?php endforeach; ?>
                </optgroup>
            <?php endforeach; ?>
		</select>
    </p>

    <?php wp_nonce_field('edit_client_nonce', 'edit_client_nonce_field'); ?>

    <p>
        <button type="submit" name="submit_edit_client" class="woocommerce-Button button">
            Modifier le client
        </button>
    </p>

</form>




<div class="div-remise">
    <form id="demandeRemise" method="post" enctype="multipart/form-data">
        <span style="font-family: 'Raleway';font-weight: 800;text-align:center;"> LE CLIENT PEUX BÉNÉFICIER<br> D'UNE REMISE COMMERCIALE :</span>
        <!-- Option 1 -->
        <label>
            <input type="checkbox" <?php if ($remise_c){ echo 'checked disabled'; }else if($desactiver1){echo 'disabled'; }  ?> name="option_remise[]"  id="option1" class="optionRemise" data-group="1" data-file="file1" data-value="Changement -25%"> Je change d'antivirus pour Avast -25%
        </label>
        <div class="upload hidden" id="file1">
            <input type="file" name="justificatif_changement" accept="application/pdf,image/*">
        </div>

        <!-- Option 2 -->
        <label>
            <input type="checkbox" <?php if ($remise_r){ echo 'checked disabled'; }else if($desactiver2){echo 'disabled'; } ?> name="option_remise[]"  id="option2" class="optionRemise" data-group="2" data-file="file2" data-value="Renouvellement de licences -30%"> Renouvellement de licences -30%
        </label>
        
        <div class="upload hidden" id="file2" style="display: flex;gap: 7px; align-items: center;justify-content: center;">
            <input  style="width: 50%;padding: 0.5rem 0.4rem; height: 30px;" type="text" id="old_key" name="justificatif_text_renouvellement" placeholder="Ancienne licence"> ou
            <input style="width: 50%;" type="file" name="justificatif_file_renouvellement" accept="application/pdf,image/*">
        </div>

        <!-- Option 3 -->
        <label>
            <input type="checkbox" <?php if ($remise_a){ echo 'checked disabled'; }else if($desactiver3){echo 'disabled'; } ?> name="option_remise[]"  id="option3" class="optionRemise" data-group="3" data-file="file3" data-value="Administrations et mairies -30%"> Administrations et mairies -30%
        </label>
        <div class="upload hidden" id="file3">
            <input type="file" name="justificatif_admin" accept="application/pdf,image/*">
        </div>

        <!-- Option 4 -->
        <label>
            <input type="checkbox" <?php if ($remise_e){ echo 'checked disabled'; }else if($desactiver4){echo 'disabled'; } ?> name="option_remise[]"  id="option4" class="optionRemise" data-group="4" data-file="file4" data-value="Établissements scolaires et associations -50%"> Établissements scolaires et associations -50%
        </label>
        <div class="upload hidden" id="file4">
            <input type="file" name="justificatif_association" accept="application/pdf,image/*">
        </div>

        <input type="hidden" name="remise_type" id="remise_type" value="<?php echo $current_remises; ?>">

        <input type="hidden" name="client_id" value="<?php echo esc_attr($client_id); ?>">

       
        <?php if($user_has_enabled_remises  || !($user_has_enabled_remises || $user_has_disabled_remises)){ //si il a des remises activees ou si il n'a aucune remise
        ?>
            <button class="btn-remise btn-remise-style"  type="submit" name="submit_demande_remise">Appliquer la remise</button>
            
        <?php }?>

        <?php if($user_has_disabled_remises){ //si il a des remises desactivees lui permettre de les activer
        ?>
            <button 
                type="button"
                class=" toggle-remise btn-remise-style"
                data-action="activate">
                Activer les remises
            </button>
        <?php }
        ?>

        <?php if($user_has_enabled_remises){ //si il a des remises activees lui permettre de les desactiver
        ?>
            <button 
                type="button"
                class="toggle-remise btn-remise-style"
                data-action="deactivate">
                Désactiver les remises
            </button>
        <?php }?>
    </form>
</div>




<style>
    .hidden { display:none !important; }
    .block-option { margin-bottom: 12px; }
    label { font-weight: 500; cursor:pointer; }
    .div-remise { 
        background-color: #f5f3f3;
        padding: 15px 28px; 
        display: flex; 
        flex-direction: column;
        gap: 11px;
        max-width: 397px; 
        color:black;
        font-size:16px;
        margin:50px 0px;
    }

</style>

<script>
jQuery(document).ready(function($) {
    function apply_reduction(element){
        const $this = element ? $(element) : $('.optionRemise:checked').first();
        const group = $this.length ? parseInt($this.data('group')) : null;
        
        console.log("selected group:",group)

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
        if(values.length<1 || $(".single_add_to_cart_button").hasClass("disabled")){
            console.log("pas de remise")
            $(".btn-remise").prop("disabled", true);
        }else{
            $(".btn-remise").prop("disabled", false);
            
        }
    }

    $('.optionRemise').on('change', function () {
        apply_reduction(this);
    });
    apply_reduction(null);


    //  désactiver au départ !
    $(".btn-remise").prop("disabled", true);

});
</script>

<style>
    .btn-remise:disabled {
		background-color: #999 !important;
		border-color: #777 !important;
		cursor: not-allowed;
		opacity: 0.5;
		margin-block: 10px !important;
	}
	.btn-remise-style {
		font-family:'Raleway';
		font-weight:700;
		margin-top:17px;
		border-style: solid; 
		border-width: 3px 3px 3px 3px; 
		border-radius: 8px 8px 8px 8px; 
		padding: 12px 30px 12px 30px; 
		color: #FFFFFF; 
		background-color: var(--e-global-color-primary); 
		border-color: var(--e-global-color-primary); 
		transition: all 0.2s;
		width: fit-content;
		margin: auto; 
		text-transform: unset;
		margin-block: 10px !important;
	}
	
</style>