<?php
/**
 * Customer completed order email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-completed-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 9.9.0
 */

use Automattic\WooCommerce\Utilities\FeaturesUtil;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$email_improvements_enabled = FeaturesUtil::feature_is_enabled( 'email_improvements' );

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); 
$user = get_user_by('login', $user_login);
$user_id = $user->ID;
$civilite    = strtolower(get_user_meta($user_id, 'civilite', true));
$prenom     = $user->first_name;
$nom        = $user->last_name;
?>

<?php echo $email_improvements_enabled ? '<div class="email-introduction">' : ''; ?>
<p>
	<?php echo '<p class="email-logo-text">Merci !</p>'; ?>
<p><span style="font-weight: 400;">Bonjour <?php echo $civilite ;?> <?php echo $nom ;?> <?php echo $prenom ;?>,</span></p>

	<p>Le paiement de votre commande Avast a bien été accepté et nous vous en remercions !</p>
	<p>
		Notre équipe commerciale via très rapidement livrer votre commande. 
		Vous recevrez sous peu un Email avec votre code d’activation Avast et la procédure d’installation du logiciel.
		Vous pouvez accéder à tout moment au suivi de votre commande en vous connectant à votre compte Avast :
	</p>
	<p>&nbsp;</p>
	<p style="text-align:center;"> 
		<a style="color:#000000;padding: 16px 20px;background-color:#ff7800;font-size: 13px;font-weight: bold;text-decoration:none" href=<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) );?> target="_blank">
			Me connecter à mon compte client AVAST
		</a>
	</p>
	<p>&nbsp;</p>
<?php if ( $email_improvements_enabled ) : ?>
	<p><?php esc_html_e( 'Here’s a reminder of what you’ve ordered:', 'woocommerce' ); ?></p>
<?php endif; ?>
<?php echo $email_improvements_enabled ? '</div>' : ''; ?>

<?php

/*
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @hooked WC_Structured_Data::generate_order_data() Generates structured data.
 * @hooked WC_Structured_Data::output_structured_data() Outputs structured data.
 * @since 2.5.0
 */
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo $email_improvements_enabled ? '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td class="email-additional-content">' : '';
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
	echo $email_improvements_enabled ? '</td></tr></table>' : '';
}

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
