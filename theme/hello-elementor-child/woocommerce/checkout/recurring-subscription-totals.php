<?php
/**
 * Recurring cart subtotals totals
 *
 * @author  WooCommerce
 * @package WooCommerce Subscriptions/Templates
 * @version 1.0.0 - Migrated from WooCommerce Subscriptions v3.1.0
 */

function get_next_pay_date($next_payment_date){
		$date = new DateTime($next_payment_date);
		$formatted_date = $date->format('j F Y');
		
		// Tableau de traduction des mois en français
		$months_fr = [
			'January' => 'janvier',
			'February' => 'février',
			'March' => 'mars',
			'April' => 'avril',
			'May' => 'mai',
			'June' => 'juin',
			'July' => 'juillet',
			'August' => 'août',
			'September' => 'septembre',
			'October' => 'octobre',
			'November' => 'novembre',
			'December' => 'décembre'
		];
		
		// Remplacer le mois anglais par français
		foreach ($months_fr as $en => $fr) {
			$formatted_date = str_replace($en, $fr, $formatted_date);
		}
		
		return 'Premier renouvellement: ' . $formatted_date;
	
}

defined( 'ABSPATH' ) || exit;
$display_heading = true;

foreach ( $recurring_carts as $recurring_cart_key => $recurring_cart ) { ?>
	<tr class="order-total recurring-total">

	<?php if ( $display_heading ) { ?>
		<?php $display_heading = false; ?>
		<th rowspan="<?php echo esc_attr( count( $recurring_carts ) ); ?>"><?php esc_html_e( 'Renouvellement', 'woocommerce-subscriptions' ); ?></th>
		<td data-title="<?php esc_attr_e( 'Recurring total', 'woocommerce-subscriptions' ); ?>">
			<?php //wcs_cart_totals_order_total_html( $recurring_cart ); ?>
			<?php 
			//var_dump($recurring_cart);
			$next_payment = $recurring_cart->next_payment_date;
			$montant = $recurring_cart->total;
			echo wc_price($montant);
			echo '<br>';
			echo get_next_pay_date($next_payment);
			?>
		</td>
	<?php } else { ?>
		<td>
			<?php //wcs_cart_totals_order_total_html( $recurring_cart ); ?>
			<?php 
			$next_payment = $recurring_cart->next_payment_date;
			$montant = $recurring_cart->total;
			echo wc_price($montant);
			echo '<br>';
			echo get_next_pay_date($next_payment);
			?>
	</td>
	<?php } ?>
	</tr> <?php
}
