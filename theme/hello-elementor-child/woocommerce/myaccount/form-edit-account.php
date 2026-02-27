<?php
/**
 * Edit account form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-edit-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.7.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Hook - woocommerce_before_edit_account_form.
 *
 * @since 2.6.0
 */
do_action( 'woocommerce_before_edit_account_form' );
?>


<form class="woocommerce-EditAccountForm edit-account" action="" method="post" <?php do_action( 'woocommerce_edit_account_form_tag' ); ?> >

	<?php do_action( 'woocommerce_edit_account_form_start' ); 
	$user_id = get_current_user_id();
		$user = wp_get_current_user();
		$billing_address = get_user_meta($user_id, 'billing_address_1', true);
		$billing_phone   = get_user_meta($user_id, 'billing_phone', true);
		$civilite   = get_user_meta($user_id, 'civilite', true);
		$optin_promos    = get_user_meta($user_id, 'optin_promos', true);
		$optin_expiration = get_user_meta($user_id, 'optin_expiration', true);
		$account_regime_tva = get_user_meta($user->ID, 'new_revendeur_account_regime_tva', true);
		$selected_regime=($account_regime_tva=="HT")?1:2;
        $account_prefixe_tva = get_user_meta($user->ID, 'new_revendeur_account_prefixe_tva', true);
        $account_tva_intra = get_user_meta($user->ID, 'new_revendeur_account_tva_intra', true);

		$type_client = get_user_meta($user_id, 'type_client', true);
       

		$denomination = get_user_meta($user_id, 'denomination', true);
		$ville = get_user_meta($user_id, 'ville', true);
		$code_postal = get_user_meta($user_id, 'code_postal', true);
		$selected_pays = get_user_meta($user_id, 'pays', true);

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
		
	 
	
		$user_id = get_current_user_id();
		$user = wp_get_current_user();
		$user_roles = $user->roles; // array de tous les rôles
		if (in_array('customer_particulier', $user_roles)) {
			$role = 'customer_particulier';
		} elseif (in_array('customer_revendeur', $user_roles)) {
			$role = 'customer_revendeur';
		} else {
			$role = ''; // fallback
		}
	?>
	<p class="form-row">
		<label for="type_client">Type de client</label>

		<select id="type_client" disabled>
			<option value="">-- Sélectionner --</option>
			<option value="particulier" <?php selected($type_client, 'particulier'); ?>>Particulier</option>
			<option value="professionnel" <?php selected($type_client, 'professionnel'); ?>>Professionnel</option>
			<option value="association_ou_institution" <?php selected($type_client, 'association_ou_institution'); ?>>Association ou Institution</option>
		</select>

		<!--  valeur envoyée quand même -->
		<input type="hidden" name="type_client" value="<?php echo esc_attr($type_client); ?>">
	</p>
	<p class="form-row ">
        <label for="denomination">Dénomination sociale <span class="required">*</span></label>
        <input type="text" maxlength="100" name="denomination" id="denomination" class="woocommerce-Input woocommerce-Input--text input-text" required value="<?php echo esc_attr($denomination); ?>"/>
    </p>
	
	<div class="clear"></div>
	<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
		<label for="account_first_name"><?php esc_html_e( 'First name', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
		<input type="text" maxlength="50" class="woocommerce-Input woocommerce-Input--text input-text" name="account_first_name" id="account_first_name" autocomplete="given-name" value="<?php echo esc_attr( $user->first_name ); ?>" aria-required="true" />
	</p>
	<p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
		<label for="account_last_name"><?php esc_html_e( 'Last name', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
		<input type="text" maxlength="50" class="woocommerce-Input woocommerce-Input--text input-text" name="account_last_name" id="account_last_name" autocomplete="family-name" value="<?php echo esc_attr( $user->last_name ); ?>" aria-required="true" />
	</p>
	<div class="clear"></div>
 	<p class="woocommerce-form-row form-row">
        <label for="billing_address_1">Adresse</label>
		<input type="text" maxlength="100" name="billing_address_1" id="billing_address_1" class="woocommerce-Input woocommerce-Input--text input-text" value="<?php echo esc_attr($billing_address); ?>">
    </p>

    <p class="woocommerce-form-row  form-row">
        <label for="billing_phone">Téléphone</label>
		<input type="text" maxlength="20" name="billing_phone" id="billing_phone" class="woocommerce-Input woocommerce-Input--text input-text" value="<?php echo esc_attr($billing_phone); ?>">
    </p>
	<div class="clear"></div>
	 <p class="form-row ">
        <label for="ville">Ville <span class="required">*</span></label>
        <input type="text" name="ville" maxlength="50" id="ville" class="woocommerce-Input woocommerce-Input--text input-text" required value="<?php echo esc_attr($ville); ?>"/>
    </p>
<div class="clear"></div>
    <p class="form-row ">
        <label for="code_postal">Code postal <span class="required">*</span></label>
        <input type="text" name="code_postal" maxlength="6" id="code_postal" class="woocommerce-Input woocommerce-Input--text input-text" required value="<?php echo esc_attr($code_postal); ?>"/>
    </p>
    <div class="clear"></div>

    <p class="form-row">
		<label for="pays">Pays <span class="required">*</span></label>
		<select name="pays" id="pays" required class="woocommerce-Input woocommerce-Input--text input-text">
			<?php foreach ( $pays_liste as $code => $nom ) : ?>
				<option value="<?php echo esc_attr($code); ?>" <?php selected($selected_pays, $code); ?>>
					<?php echo esc_html($nom); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</p>
<div class="clear"></div>
	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
		<label for="account_display_name"><?php esc_html_e( 'Display name', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
		<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_display_name" id="account_display_name" aria-describedby="account_display_name_description" value="<?php echo esc_attr( $user->display_name ); ?>" aria-required="true" /> <span id="account_display_name_description"><em><?php esc_html_e( 'This will be how your name will be displayed in the account section and in reviews', 'woocommerce' ); ?></em></span>
	</p>
	<div class="clear"></div>

	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
		<label for="account_email"><?php esc_html_e( 'Email address', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
		<input type="email" maxlength="70" class="woocommerce-Input woocommerce-Input--email input-text" name="account_email" id="account_email" autocomplete="email" value="<?php echo esc_attr( $user->user_email ); ?>" aria-required="true" />
	</p>
	
  	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="role">Type de compte</label>
        <select  id="role" required disabled>
			<option value="">Sélectionnez...</option>
			<option value="customer_particulier" <?php selected($role,'customer_particulier'); ?>>Particulier</option>
			<option value="customer_revendeur" <?php selected($role,'customer_revendeur'); ?>>Revendeur</option>
		</select>
    </p>

	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="civilite">Civilité <span class="required">*</span></label>
        <select name="civilite" id="civilite" required >
			<option value="">Sélectionnez...</option>
			<option value="Monsieur" <?php selected($civilite,'Monsieur'); ?>>Monsieur</option>
			<option value="Madame" <?php selected($civilite,'Madame'); ?>>Madame</option>
			<option value="Mademoiselle" <?php selected($civilite,'Mademoiselle'); ?>>Mademoiselle</option>
		</select>
    </p>

	<?php 
		if($role == 'customer_revendeur'){
			$account_regime_tva = get_user_meta($user->ID, 'new_revendeur_account_regime_tva', true);
			$selected_regime=($account_regime_tva=="HT")?1:2;
			$account_prefixe_tva = get_user_meta($user->ID, 'new_revendeur_account_prefixe_tva', true);
			$account_tva_intra = get_user_meta($user->ID, 'new_revendeur_account_tva_intra', true);
		}else{

			$account_regime_tva = get_user_meta($user->ID, 'new_account_regime_tva', true);
			$selected_regime=($account_regime_tva=="HT")?1:2;
			$account_prefixe_tva = get_user_meta($user->ID, 'new_account_prefixe_tva', true);
			$account_tva_intra = get_user_meta($user->ID, 'new_account_tva_intra', true);
		}
		?>
		<div id="boxtva" name="boxtva" >            
			<b>Régime de TVA applicable :<span class="required">*</span></b>
			<br><br>
			
			
			<div style="width: 100%; display: flex; align-items: flex-start; gap: 7px;">
				<input type="radio" id="regime_2"  <?php echo (isset($selected_regime) && $selected_regime == 2) ? 'checked' : ''; ?> name="new_revendeur_account_regime_tva" value="2" style="width: auto" >
				<label style="line-height: 1.5;"><b>Facturation TTC faisant ressortir la TVA</b> (pays de l'union) Facturation avec TVA de 20%</label>
			</div>
			<br>

			<div style="">
				<div style="display: flex;align-items: flex-start;justify-content: flex-start;gap: 7px;">
				<input type="radio" id="regime_1"  <?php echo (isset($selected_regime) && $selected_regime == 1) ? 'checked' : ''; ?> name="new_revendeur_account_regime_tva" value="1" style="width: auto" >
				<label style="line-height: 1.5;"><b>Facturation HT</b> (pour les pays de l'union Européenne, hors France) Merci de justifier ci dessous d'un numéro de TVA Intra valide :</label>
			</div>
				<div id="tva_regime_1_box">
					<div id="tva_regime_1_box2" class="w-100">
					N° TVA intracommunautaire:
					<select title="Prefixe TVA" id="prefixe_tva" name="new_revendeur_account_prefixe_tva" alt="Prefixe TVA">
						<option <?php echo empty($account_prefixe_tva) ? 'selected' : ''; ?> value="" alt="Prefixe TVA">--</option>
						<option value="AT"  <?php echo ($account_prefixe_tva === 'AT') ? 'selected' : ''; ?> alt="Prefixe TVA">AT</option>
						<option value="BE"  <?php echo ($account_prefixe_tva === 'BE') ? 'selected' : ''; ?> alt="Prefixe TVA">BE</option>
						<option value="BG"  <?php echo ($account_prefixe_tva === 'BG') ? 'selected' : ''; ?> alt="Prefixe TVA">BG</option>
						<option value="CY"  <?php echo ($account_prefixe_tva === 'CY') ? 'selected' : ''; ?> alt="Prefixe TVA">CY</option>
						<option value="CZ"  <?php echo ($account_prefixe_tva === 'CZ') ? 'selected' : ''; ?> alt="Prefixe TVA">CZ</option>
						<option value="DE"  <?php echo ($account_prefixe_tva === 'DE') ? 'selected' : ''; ?> alt="Prefixe TVA">DE</option>
						<option value="DK"  <?php echo ($account_prefixe_tva === 'DK') ? 'selected' : ''; ?> alt="Prefixe TVA">DK</option>
						<option value="EE"  <?php echo ($account_prefixe_tva === 'EE') ? 'selected' : ''; ?> alt="Prefixe TVA">EE</option>
						<option value="EL"  <?php echo ($account_prefixe_tva === 'EL') ? 'selected' : ''; ?> alt="Prefixe TVA">EL</option>
						<option value="ES"  <?php echo ($account_prefixe_tva === 'ES') ? 'selected' : ''; ?> alt="Prefixe TVA">ES</option>
						<option value="FI"  <?php echo ($account_prefixe_tva === 'FI') ? 'selected' : ''; ?> alt="Prefixe TVA">FI</option>
						<option value="FR"  <?php echo ($account_prefixe_tva === 'FR') ? 'selected' : ''; ?> alt="Prefixe TVA">FR</option>
						<option value="HU"  <?php echo ($account_prefixe_tva === 'HU') ? 'selected' : ''; ?> alt="Prefixe TVA">HU</option>
						<option value="IE"  <?php echo ($account_prefixe_tva === 'IE') ? 'selected' : ''; ?> alt="Prefixe TVA">IE</option>
						<option value="IT"  <?php echo ($account_prefixe_tva === 'IT') ? 'selected' : ''; ?> alt="Prefixe TVA">IT</option>
						<option value="LT"  <?php echo ($account_prefixe_tva === 'LT') ? 'selected' : ''; ?> alt="Prefixe TVA">LT</option>
						<option value="LU"  <?php echo ($account_prefixe_tva === 'LU') ? 'selected' : ''; ?> alt="Prefixe TVA">LU</option>
						<option value="LV"  <?php echo ($account_prefixe_tva === 'LV') ? 'selected' : ''; ?> alt="Prefixe TVA">LV</option>
						<option value="MT"  <?php echo ($account_prefixe_tva === 'MT') ? 'selected' : ''; ?> alt="Prefixe TVA">MT</option>
						<option value="NL"  <?php echo ($account_prefixe_tva === 'NL') ? 'selected' : ''; ?> alt="Prefixe TVA">NL</option>
						<option value="PL"  <?php echo ($account_prefixe_tva === 'PL') ? 'selected' : ''; ?> alt="Prefixe TVA">PL</option>
						<option value="PT"  <?php echo ($account_prefixe_tva === 'PT') ? 'selected' : ''; ?> alt="Prefixe TVA">PT</option>
						<option value="RO"  <?php echo ($account_prefixe_tva === 'RO') ? 'selected' : ''; ?> alt="Prefixe TVA">RO</option>
						<option value="SE"  <?php echo ($account_prefixe_tva === 'SE') ? 'selected' : ''; ?> alt="Prefixe TVA">SE</option>
						<option value="SI"  <?php echo ($account_prefixe_tva === 'SI') ? 'selected' : ''; ?> alt="Prefixe TVA">SI</option>
						<option value="SK"  <?php echo ($account_prefixe_tva === 'SK') ? 'selected' : ''; ?> alt="Prefixe TVA">SK</option>
					</select>
					<input  style="width: auto" maxlength="60" type="text" name="new_revendeur_account_tva_intra" value="<?php echo $account_tva_intra;?>" size="25" onblur="IsRequiredOk(this)">
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
	

	 <p class="form-row">
        <label>
			<input type="checkbox" name="optin_promos" value="yes" <?php checked($optin_promos, 'yes'); ?>>
            Je souhaite recevoir par mail les informations et promotions sur Avast
        </label>
    </p>

    <p class="form-row particulier-only" style="display:none;">
        <label>
            <input type="checkbox" name="optin_expiration" value="yes" <?php checked($optin_expiration,'yes'); ?>>
            Je souhaite être informé de l’expiration de mes licences
        </label>
    </p>

	<?php
		/**
		 * Hook where additional fields should be rendered.
		 *
		 * @since 8.7.0
		 */
		do_action( 'woocommerce_edit_account_form_fields' );
	?>

	<fieldset>
		<legend><?php esc_html_e( 'Password change', 'woocommerce' ); ?></legend>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="password_current"><?php esc_html_e( 'Current password (leave blank to leave unchanged)', 'woocommerce' ); ?></label>
			<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_current" id="password_current" autocomplete="off" />
		</p>
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="password_1"><?php esc_html_e( 'New password (leave blank to leave unchanged)', 'woocommerce' ); ?></label>
			<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_1" id="password_1" autocomplete="off" />
		</p>
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="password_2"><?php esc_html_e( 'Confirm new password', 'woocommerce' ); ?></label>
			<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_2" id="password_2" autocomplete="off" />
		</p>
	</fieldset>
	<div class="clear"></div>
 
 
	<script>
		jQuery(document).ready(function($) {
			const $roleField = $('#role');
			const $expField = $('.particulier-only');

			function toggleOptinExpiration() {
				if ($roleField.val() === 'customer_particulier') {
					$expField.show();
				} else {
					$expField.hide();
				}
			}

			// Au chargement initial
			toggleOptinExpiration();

			// À chaque changement
			$roleField.on('change', toggleOptinExpiration);


			$('#tva_regime_1_box').hide();
			if ($('#regime_1').is(':checked')) {
				$('#tva_regime_1_box').show();
			}

			$('input[name="new_revendeur_account_regime_tva"]').on('change', function() {

			if ($('#regime_1').is(':checked')) {
				$('#tva_regime_1_box').show();
			}

			if ($('#regime_2').is(':checked')) {
				$('#tva_regime_1_box').hide();
			}
		});
		});
	</script>

	<?php
		/**
		 * My Account edit account form.
		 *
		 * @since 2.6.0
		 */
		do_action( 'woocommerce_edit_account_form' );
	?>

	<p>
		<?php wp_nonce_field( 'save_account_details', 'save-account-details-nonce' ); ?>
		<button type="submit" class="woocommerce-Button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="save_account_details" value="<?php esc_attr_e( 'Save changes', 'woocommerce' ); ?>"><?php esc_html_e( 'Save changes', 'woocommerce' ); ?></button>
		<input type="hidden" name="action" value="save_account_details" />
	</p>

	<?php do_action( 'woocommerce_edit_account_form_end' ); ?>
</form>

<?php do_action( 'woocommerce_after_edit_account_form' ); ?>
