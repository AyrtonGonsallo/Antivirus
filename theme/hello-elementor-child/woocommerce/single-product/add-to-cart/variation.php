<?php
/**
 * Single variation display
 *
 * This is a javascript-based template for single variations (see https://codex.wordpress.org/Javascript_Reference/wp.template).
 * The values will be dynamically replaced after selecting attributes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;

?>

<script type="text/template" id="tmpl-variation-template">
	
	<div class="woocommerce-variation-description">{{{ data.variation.variation_description }}}</div>
	<div class="prix-remise-variable-subs">
		<div class="woocommerce-variation-price remisable hide-if-rem-comm {{{ data.variation.class_remise_revendeur }}} {{{ data.variation.class_remise_revendeur }}}">{{{ data.variation.price_html }}}</div>
		<div class="woocommerce-variation-percent hide-if-rem-comm {{{ data.variation.class_remise_revendeur }}}">{{{ data.variation.discount_percent }}}</div>
	</div>
	<div class="woocommerce-variation-availability ">{{{ data.variation.availability_html }}}</div>
	<div class="woocommerce-variation-my-custom-data hide-if-rem-comm {{{ data.variation.class_remise_revendeur }}}">{{{ data.variation.sale_end_date }}}</div>
	
	
	<span class="variation-reduction-percentage has-remise-revendeur {{{ data.variation.class_hide_remise_revendeur }}}">{{{ data.variation.remise_revendeur_txt }}}</span>
	<span class="prix-remise-revendeur {{{ data.variation.class_hide_remise_revendeur }}}">
		<div class="woocommerce-variation-price remisable ">
			<span class="price">
				<span class="woocommerce-Price-amount amount">
					<bdi>{{{ data.variation.prix_remise_revendeur }}}&nbsp;
						<span class="woocommerce-Price-currencySymbol">€</span>
					</bdi>
				</span> 
			</span>
		</div>
	</span>

	<span class="prix-remise-depart">{{{ data.variation.prix_remise_depart }}}</span>
	<span class="prix-remise">prix remisé</span>
	<span class="prix-total">{{{ data.variation.prix_base }}}</span>
	<span class="pourcentage-remise-depart">{{{ data.variation.pourcentage_remise_revendeur }}}</span>

</script>
<script type="text/template" id="tmpl-unavailable-variation-template">
	<p role="alert"><?php esc_html_e( 'Sorry, this product is unavailable. Please choose a different combination.', 'woocommerce' ); ?></p>
</script>

