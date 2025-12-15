<?php
/**
 * Login Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-login.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

do_action( 'woocommerce_before_customer_login_form' ); ?>

<?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>

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

<div class="u-columns col2-set" id="customer_login">

	<div class="u-column1 col-1">

<?php endif; ?>

		<h2><?php esc_html_e( 'Login', 'woocommerce' ); ?></h2>

		<form class="woocommerce-form woocommerce-form-login login" method="post" novalidate>

			<?php do_action( 'woocommerce_login_form_start' ); ?>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="username"><?php esc_html_e( 'Username or email address', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) && is_string( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required aria-required="true" /><?php // @codingStandardsIgnoreLine ?>
			</p>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
				<input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" required aria-required="true" />
			</p>

			<?php do_action( 'woocommerce_login_form' ); ?>

			<p class="form-row">
				<label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
					<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> <span><?php esc_html_e( 'Remember me', 'woocommerce' ); ?></span>
				</label>
				<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
				<button type="submit" class="woocommerce-button button woocommerce-form-login__submit<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>"><?php esc_html_e( 'Log in', 'woocommerce' ); ?></button>
			</p>
			<p class="woocommerce-LostPassword lost_password">
				<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Lost your password?', 'woocommerce' ); ?></a>
			</p>

			<?php do_action( 'woocommerce_login_form_end' ); ?>

		</form>

<?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>

	</div>

	<div class="u-column2 col-2">

		<h2>Je suis un nouveau client</h2>

		<form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?> >

			<?php do_action( 'woocommerce_register_form_start' ); ?>

			<p class="form-row ">
				<label for="nom">Nom <span class="required">*</span></label>
				<input type="text" maxlength="50" name="nom" id="nom" required class="woocommerce-Input woocommerce-Input--text input-text"/>
			</p>

			<p class="form-row ">
				<label for="prenom">Prénom <span class="required">*</span></label>
				<input type="text" maxlength="50" name="prenom" id="prenom" required class="woocommerce-Input woocommerce-Input--text input-text"/>
			</p>

			<p class="form-row ">
				<label for="billing_phone">Téléphone <span class="required">*</span></label>
				<input type="text" maxlength="20" name="billing_phone" id="billing_phone" required class="woocommerce-Input woocommerce-Input--text input-text"/>
			</p>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="civilite">Civilité <span class="required">*</span></label>
				<select name="civilite" id="civilite" required >
					<option value="">Sélectionnez...</option>
					<option value="Monsieur" >Monsieur</option>
					<option value="Madame" >Madame</option>
					<option value="Mademoiselle">Mademoiselle</option>
				</select>
			</p>

			<p class="form-row ">
				<label for="ville">Ville <span class="required">*</span></label>
				<input type="text" maxlength="30" name="ville" id="ville" required class="woocommerce-Input woocommerce-Input--text input-text"/>
			</p>

			<p class="form-row ">
				<label for="code_postal">Code postal <span class="required">*</span></label>
				<input type="text" maxlength="6" name="code_postal" id="code_postal" required class="woocommerce-Input woocommerce-Input--text input-text"/>
			</p>
			<div class="clear"></div>

			<p class="form-row">
				<label for="pays">Pays <span class="required">*</span></label>
				
				<select name="pays" id="pays" required class="woocommerce-Input woocommerce-Input--text input-text">
					<?php foreach ( $pays_liste as $code => $nom ) : ?>
						<option value="<?php echo esc_attr($code); ?>" >
							<?php echo esc_html($nom); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</p>

			<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>

				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="reg_username"><?php esc_html_e( 'Username', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
					<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required aria-required="true" /><?php // @codingStandardsIgnoreLine ?>
				</p>

			<?php endif; ?>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="reg_email"><?php esc_html_e( 'Email address', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
				<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" required aria-required="true" /><?php // @codingStandardsIgnoreLine ?>
			</p>

			<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>

				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="reg_password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
					<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" required aria-required="true" />
				</p>

			<?php else : ?>

				<p><?php esc_html_e( 'A link to set a new password will be sent to your email address.', 'woocommerce' ); ?></p>

			<?php endif; ?>

			<?php do_action( 'woocommerce_register_form' ); ?>

			<p class="woocommerce-form-row form-row">
				<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
				<button type="submit" class="woocommerce-Button woocommerce-button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?> woocommerce-form-register__submit" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>">Continuer l'inscription</button>
			</p>

			<?php do_action( 'woocommerce_register_form_end' ); ?>

		</form>

	</div>

</div>
<?php endif; ?>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
