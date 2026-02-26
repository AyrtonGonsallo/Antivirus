<?php
/**
 * Email Addresses
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-addresses.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 9.8.0
 */

use Automattic\WooCommerce\Utilities\FeaturesUtil;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$address  = $order->get_formatted_billing_address();
$shipping = $order->get_formatted_shipping_address();

$email_improvements_enabled = FeaturesUtil::feature_is_enabled( 'email_improvements' );

?><table id="addresses" cellspacing="0" cellpadding="0" style="width: 100%; vertical-align: top; margin-bottom: <?php echo $email_improvements_enabled ? '0' : '40px'; ?>; padding:0;" border="0">
	<tr>
		<td class="font-family text-align-left" style="border:0; padding:0;" valign="top" width="50%">
			<?php if ( $email_improvements_enabled ) { ?>
				<b class="address-title"><?php esc_html_e( 'Billing address', 'woocommerce' ); ?></b>
			<?php } else { ?>
				<h2><?php esc_html_e( 'Billing address', 'woocommerce' ); ?></h2>
			<?php } ?>

			<address class="address">
				<?php
					$user_id = $order->get_user_id();
					$billing_numero_siret    = (get_user_meta($user_id, 'billing_numero_siret', true));
					$billing_societe    = (get_user_meta($user_id, 'billing_societe', true));
				?>
				<?php if ( !empty( $billing_numero_siret ) ) : ?>
					<br/><?php echo $billing_numero_siret;  ?></td>
				<?php endif; ?>
				<?php if ( !empty( $billing_societe ) ) : ?>
					<br/><?php echo $billing_societe; ?>
				<?php endif; ?>
				<?php echo wp_kses_post( $address ? $address : esc_html__( 'N/A', 'woocommerce' ) ); ?>
				<?php if ( $order->get_billing_phone() ) : ?>
					<br/><?php echo wc_make_phone_clickable( $order->get_billing_phone() ); ?>
				<?php endif; ?>
				<?php if ( $order->get_billing_email() ) : ?>
					<br/><?php echo esc_html( $order->get_billing_email() ); ?>
				<?php endif; ?>
				<?php
				/**
				 * Fires after the core address fields in emails.
				 *
				 * @since 8.6.0
				 *
				 * @param string $type Address type. Either 'billing' or 'shipping'.
				 * @param WC_Order $order Order instance.
				 * @param bool $sent_to_admin If this email is being sent to the admin or not.
				 * @param bool $plain_text If this email is plain text or not.
				 */
				do_action( 'woocommerce_email_customer_address_section', 'billing', $order, $sent_to_admin, false );
				?>
			</address>
		</td>
		
	</tr>
</table>
<?php echo $email_improvements_enabled ? '<br>' : ''; ?>