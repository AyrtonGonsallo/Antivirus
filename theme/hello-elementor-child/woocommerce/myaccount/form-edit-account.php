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

		$denomination = get_user_meta($user_id, 'denomination', true);
		$ville = get_user_meta($user_id, 'ville', true);
		$code_postal = get_user_meta($user_id, 'code_postal', true);
		$selected_pays = get_user_meta($user_id, 'pays', true);

		$pays_liste = [
			'FR' => 'France',
			'BE' => 'Belgique',
			'CH' => 'Suisse',
			'LU' => 'Luxembourg',
			'DE' => 'Allemagne',
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
	<?php if($role == 'customer_revendeur'){?>
	<p class="form-row ">
        <label for="denomination">Dénomination sociale <span class="required">*</span></label>
        <input type="text" name="denomination" id="denomination" class="woocommerce-Input woocommerce-Input--text input-text" required value="<?php echo esc_attr($denomination); ?>"/>
    </p>
	<?php }?>
	<div class="clear"></div>
	<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
		<label for="account_first_name"><?php esc_html_e( 'First name', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
		<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_first_name" id="account_first_name" autocomplete="given-name" value="<?php echo esc_attr( $user->first_name ); ?>" aria-required="true" />
	</p>
	<p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
		<label for="account_last_name"><?php esc_html_e( 'Last name', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
		<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_last_name" id="account_last_name" autocomplete="family-name" value="<?php echo esc_attr( $user->last_name ); ?>" aria-required="true" />
	</p>
	<div class="clear"></div>
 	<p class="woocommerce-form-row form-row">
        <label for="billing_address_1">Adresse</label>
		<input type="text" name="billing_address_1" id="billing_address_1" class="woocommerce-Input woocommerce-Input--text input-text" value="<?php echo esc_attr($billing_address); ?>">
    </p>

    <p class="woocommerce-form-row  form-row">
        <label for="billing_phone">Téléphone</label>
		<input type="text" name="billing_phone" id="billing_phone" class="woocommerce-Input woocommerce-Input--text input-text" value="<?php echo esc_attr($billing_phone); ?>">
    </p>
	<div class="clear"></div>
	 <p class="form-row ">
        <label for="ville">Ville <span class="required">*</span></label>
        <input type="text" name="ville" id="ville" class="woocommerce-Input woocommerce-Input--text input-text" required value="<?php echo esc_attr($ville); ?>"/>
    </p>
<div class="clear"></div>
    <p class="form-row ">
        <label for="code_postal">Code postal <span class="required">*</span></label>
        <input type="text" name="code_postal" id="code_postal" class="woocommerce-Input woocommerce-Input--text input-text" required value="<?php echo esc_attr($code_postal); ?>"/>
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
		<input type="email" class="woocommerce-Input woocommerce-Input--email input-text" name="account_email" id="account_email" autocomplete="email" value="<?php echo esc_attr( $user->user_email ); ?>" aria-required="true" />
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
			<option value="Mademoiseille" <?php selected($civilite,'Mademoiseille'); ?>>Mademoiseille</option>
		</select>
    </p>

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
