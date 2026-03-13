<?php
/**
 * Checkout billing information form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-billing.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 * @global WC_Checkout $checkout
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="woocommerce-billing-fields">
	<?php if ( wc_ship_to_billing_address_only() && WC()->cart->needs_shipping() ) : ?>

		<h3><?php esc_html_e( 'Billing &amp; Shipping', 'woocommerce' ); ?></h3>

	<?php else : ?>

		<h3><?php esc_html_e( 'Billing details', 'woocommerce' ); ?></h3>

	<?php endif; ?>

	<?php do_action( 'woocommerce_before_checkout_billing_form', $checkout ); ?>

	<div class="woocommerce-billing-fields__field-wrapper">
		<?php
		$fields = $checkout->get_checkout_fields( 'billing' );

		foreach ( $fields as $key => $field ) {
			woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
		}
		?>
	</div>

	<?php do_action( 'woocommerce_after_checkout_billing_form', $checkout ); ?>
</div>

<?php if ( ! is_user_logged_in() && $checkout->is_registration_enabled() ) : ?>
	<div class="woocommerce-account-fields">
		<?php if ( ! $checkout->is_registration_required() ) : ?>

			<p class="form-row form-row-wide create-account">
				<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
					<input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="createaccount" <?php checked( ( true === $checkout->get_value( 'createaccount' ) || ( true === apply_filters( 'woocommerce_create_account_default_checked', false ) ) ), true ); ?> type="checkbox" name="createaccount" value="1" /> <span><?php esc_html_e( 'Create an account?', 'woocommerce' ); ?></span>
				</label>
			</p>

		<?php endif; ?>

		<?php do_action( 'woocommerce_before_checkout_registration_form', $checkout ); ?>

		<?php if ( $checkout->get_checkout_fields( 'account' ) ) : ?>

			<div class="create-account">
				<?php foreach ( $checkout->get_checkout_fields( 'account' ) as $key => $field ) : ?>
					<?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
				<?php endforeach; ?>
				<div class="clear"></div>
			</div>

		<?php endif; ?>

		<?php do_action( 'woocommerce_after_checkout_registration_form', $checkout ); ?>
	</div>
<?php endif; ?>



<script>
    jQuery(function ($) {

		$('#billing_type_client_field input[type="radio"]').prop('disabled', true);
        function toggleDenominationSiretFields() {
			console.log("billing_type_client",$('input[name="billing_type_client"]:checked').val())
            if ($('input[name="billing_type_client"]:checked').val() === 'professionnel') {
				// ou 
                $('#billing-denomination-field').slideDown();
				$('#billing-siret-field').slideUp();
				$('#billing_societe_field .optional')
        			.html('<span style="color:red;">*</span>');
            }
			else if ($('input[name="billing_type_client"]:checked').val() === 'association_ou_institution') {
				// ou 
                $('#billing-denomination-field').slideDown();
				$('#billing-siret-field').slideUp();
				$('#billing_societe_field .optional')
        			.html('<span style="color:red;">*</span>');
            } 
			else if ($('input[name="billing_type_client"]:checked').val() === 'revendeur') {
				console.log("revendeur")
				// ou 
				$('#billing-denomination-field').slideDown();
                $('#billing-siret-field').slideDown();
				$('#billing_societe_field .optional, #billing_numero_siret_field .optional')
        			.html('<span style="color:red;">*</span>');
            }
			else {
				$('#billing-denomination-field').slideUp();
                $('#billing-siret-field').slideUp();
            }
        }

        toggleDenominationSiretFields();
        $('input[name="billing_type_client"]').change(function(){
			toggleDenominationSiretFields();
		});

    });
    </script>

	<style>
		#billing_type_client_field .woocommerce-input-wrapper .required{
			display:none;
		}
		#billing_type_client_field .woocommerce-input-wrapper {
			display: flex;
    		gap: 5px;
		}
		#billing_type_client_field{
			margin-top: -5px;
		}
	</style>