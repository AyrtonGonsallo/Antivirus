<?php
/**
 * Single Product Price
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/price.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product;

?>

<p class="<?php echo esc_attr( apply_filters( 'woocommerce_product_price_class', 'price' ) ); ?>">
	
<?php //echo $product->get_price_html(); ?>

<?php


if ( is_singular('product') ) {
        
    if ( $product->is_type('simple') ) {
        echo ''; // simple → rien
    }

    elseif ( $product->is_type('variable') ) {

        echo '';
    }

    elseif ( $product->is_type('variation') ) {

        $regular_price = $product->get_regular_price();
        $sale_price    = $product->get_sale_price();

        if ( $sale_price && $sale_price < $regular_price ) {
            
            echo '<span style="text-decoration:line-through;color:#888;">' . wc_price($regular_price) . '</span> ';
            echo '<span style="color:#d00;font-weight:bold;">' . wc_price($sale_price) . '</span>';
            
            //echo $product->get_price_html();
        } else {
            echo wc_price($regular_price);
        }
    }
} else {
    echo $product->get_price_html();
}


?>

</p>



<style>
	.regular-price{
		color: #FF1212;
		font-family: "Raleway", Raleway;
		font-size: 30px;
		font-weight: 400;
		text-transform: capitalize;
		font-style: normal;
		line-height: 1.1em;
		letter-spacing: 0px;
		text-decoration:line-through;
	}
	.sale-price{
		color: var(--e-global-color-accent);
		font-family: "Raleway", Raleway;
		font-size: 84px;
		font-weight: 800;
	}
</style>