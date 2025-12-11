<?php
/**
 * Single variation cart button
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.2.0
 */

defined( 'ABSPATH' ) || exit;

global $product;
?>
<div style="display: flex; flex-direction: column;gap:7px;align-items: center;" class="woocommerce-variation-add-to-cart variations_button">
	<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

	<?php
	do_action( 'woocommerce_before_add_to_cart_quantity' );

	woocommerce_quantity_input(
		array(
			'min_value'   => $product->get_min_purchase_quantity(),
			'max_value'   => $product->get_max_purchase_quantity(),
			'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), // WPCS: CSRF ok, input var ok.
		)
	);

	do_action( 'woocommerce_after_add_to_cart_quantity' );
	?>

	<button style="margin-block:20px!important;" type="submit" class="single_add_to_cart_button button alt<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>

	<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>

	<input type="hidden" name="add-to-cart" value="<?php echo absint( $product->get_id() ); ?>" />
	<input type="hidden" name="product_id" value="<?php echo absint( $product->get_id() ); ?>" />
	<input type="hidden" name="variation_id" class="variation_id" value="0" />
	
</div>


<div class="div-remise">
<form id="demandeRemise" method="post" enctype="multipart/form-data">
    <span style="font-family: 'Raleway';font-weight: 600;text-align:center;"> JE PEUX BÉNÉFICIER<br> D'UNE REMISE COMMERCIALE :</span>
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

    <button class="btn-remise" style="font-family:'Raleway';font-weight:700;margin-top:17px;border-style: solid; border-width: 3px 3px 3px 3px; border-radius: 8px 8px 8px 8px; padding: 12px 30px 12px 30px; color: #FFFFFF; background-color: var(--e-global-color-primary); border-color: var(--e-global-color-primary); transition: all 0.2s;width: fit-content;margin: auto; text-transform: unset;" class="button product_type_simple" type="submit" name="submit_demande_remise">Appliquer ma remise</button>

</form>
</div>

<style>
    .hidden { display:none !important; }
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
        console.log("remises courantes",values.length,values.join(', '))
        if(values.length<1 || $(".single_add_to_cart_button").hasClass("disabled")){
            console.log("pas de remise")
            $(".btn-remise").prop("disabled", true);
        }else{
            $(".btn-remise").prop("disabled", false);
        }
        
    });

    

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
</style>