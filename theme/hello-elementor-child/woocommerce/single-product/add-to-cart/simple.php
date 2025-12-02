<?php
/**
 * Simple product add to cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/simple.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.2.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product->is_purchasable() ) {
	return;
}

echo wc_get_stock_html( $product ); // WPCS: XSS ok.

if ( $product->is_in_stock() ) : ?>

	<?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>

	<form style="display: flex; flex-direction: column;gap:7px;" class="cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data'>
	<div class="duree-annee" style="display: flex;gap: 8px;">
		<div class="" style="display: flex; justify-content: center;align-items: center;gap: 8px;">
        <select style="background: #DDDDDD; padding: 4px 17px; color: #666666; border: 1px solid #666666; border-radius: 2px;" name="alm_quantity2" id="alm_quantity2">
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
        </select>
		<label style="color:#878787;" for="alm_quantity2"><?php _e( 'poste(s)', 'woocommerce' ); ?></label>
    </div>
		
	<div class="" style="display:flex;">
        <label for="alm_duree2"><?php _e( '', 'woocommerce' ); ?></label>
        <select style="background: #DDDDDD; padding: 4px 17px; color: #666666; border: 1px solid #666666; border-radius: 2px;" name="alm_duree2" id="alm_duree2">
            <option value="1">1 an</option>
            <option value="2">2 ans</option>
            <option value="3">3 ans</option>
        </select>
    </div>
	</div>
	<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

    <?php
    do_action( 'woocommerce_before_add_to_cart_quantity' );

    woocommerce_quantity_input([
        'min_value'   => $product->get_min_purchase_quantity(),
        'max_value'   => $product->get_max_purchase_quantity(),
        'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(),
    ]);

    do_action( 'woocommerce_after_add_to_cart_quantity' );
    ?>

    
<div style="margin-top:17px;">
    <button  type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="single_add_to_cart_button button alt"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>
</div>
    <?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
</form>


<form id="demandeRemise" method="post" enctype="multipart/form-data">

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
    <div class="upload hidden" id="file2">
        <input type="file" name="justificatif_renouvellement" accept="application/pdf,image/*">
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

    <button class="btn-remise" style="font-family:'Raleway';font-weight:700;margin-top:17px;border-style: solid; border-width: 3px 3px 3px 3px; border-radius: 8px 8px 8px 8px; padding: 12px 30px 12px 30px; color: #FFFFFF; background-color: var(--e-global-color-primary); border-color: var(--e-global-color-primary); transition: all 0.2s;" class="button product_type_simple" type="submit" name="submit_demande_remise">Demander la remise</button>

</form>


<style>
    .hidden { display:none; }
    .block-option { margin-bottom: 12px; }
    label { font-weight: 500; cursor:pointer; }
</style>
<script>
jQuery(document).ready(function($) {

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
        console.log("remises courantes",values.join(', '))
    });

});
</script>





	<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>

<?php endif; ?>
