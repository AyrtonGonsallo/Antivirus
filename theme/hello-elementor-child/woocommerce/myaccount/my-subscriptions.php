<?php
/**
 * My Subscriptions section on the My Account page
 *
 * @author   Prospress
 * @category WooCommerce Subscriptions/Templates
 * @version  7.2.0 - Migrated from WooCommerce Subscriptions v2.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>


<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />

<style>
	
.dataTables_wrapper .dataTables_filter input,.dataTables_wrapper .dataTables_length select {
    border: 1px solid var(--tables-title-color, #000) !important;
    border-radius: 3px;
    padding: 5px;
}
input:focus-visible, select:focus-visible, textarea:focus-visible {
    box-shadow: none !important;
    outline: none !important;
    outline-offset: 0px !important;
}

 .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
    color:  #ffffff !important;
    border: 1px solid var(--tables-buttons-border-color, #5bc0de)!important;
    background-color: var(--tables-buttons-border-color, #5bc0de)!important;
 
}
.dataTables_wrapper .dataTables_paginate .paginate_button.current{
    color:  #ffffff !important;
    border: 1px solid var(--tables-buttons-border-color, #5bc0de)!important;
    background-color: var(--tables-buttons-border-color, #5bc0de)!important;
 
}
 .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    color: var(--tables-title-color, #000) !important;
    border: 1px solid var(--tables-title-color, #000) !important;
    background-color: rgba(0, 0, 0, 0.05);
 
}
.dataTables_wrapper .dataTables_paginate .paginate_button{
    color: var(--tables-title-color, #000) !important;
    border: 1px solid var(--tables-title-color, #000) !important;
    background-color: rgba(0, 0, 0, 0.05);
 
}
.dataTables_wrapper .dataTables_paginate .paginate_button.disabled, .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover, .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:active {
    color: var(--tables-title-color, #000) !important;
}
.dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_filter, .dataTables_wrapper .dataTables_info, .dataTables_wrapper .dataTables_processing, .dataTables_wrapper .dataTables_paginate {
    color: var(--tables-title-color, #000) !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    color: white !important;
    border: 1px solid var(--tables-buttons-border-color, #5bc0de)!important;
    background-color: var(--tables-buttons-border-color, #5bc0de)!important;
}
</style>


<div class="woocommerce_account_subscriptions">

	<?php if ( ! empty( $subscriptions ) ) : ?>
	
		<table id="tableMesAbonnements" class="my_account_subscriptions my_account_orders woocommerce-orders-table woocommerce-MyAccount-subscriptions shop_table shop_table_responsive woocommerce-orders-table--subscriptions">

		<thead>
			<tr>
				<th class="subscription-id order-number woocommerce-orders-table__header woocommerce-orders-table__header-order-number woocommerce-orders-table__header-subscription-id"><span class="nobr"><?php esc_html_e( 'Subscription', 'woocommerce-subscriptions' ); ?></span></th>
				<th class="subscription-client-final woocommerce-orders-table__header"><span class="nobr"><?php esc_html_e( 'Client final', 'woocommerce-subscriptions' ); ?></span></th>
				<th class="subscription-status order-status woocommerce-orders-table__header woocommerce-orders-table__header-order-status woocommerce-orders-table__header-subscription-status"><span class="nobr"><?php esc_html_e( 'Status', 'woocommerce-subscriptions' ); ?></span></th>
				<th class="subscription-next-payment order-date woocommerce-orders-table__header woocommerce-orders-table__header-order-date woocommerce-orders-table__header-subscription-next-payment"><span class="nobr"><?php echo esc_html_x( 'Next payment', 'table heading', 'woocommerce-subscriptions' ); ?></span></th>
				<th class="subscription-total order-total woocommerce-orders-table__header woocommerce-orders-table__header-order-total woocommerce-orders-table__header-subscription-total"><span class="nobr"><?php echo esc_html_x( 'Total', 'table heading', 'woocommerce-subscriptions' ); ?></span></th>
				<th class="subscription-actions order-actions woocommerce-orders-table__header woocommerce-orders-table__header-order-actions woocommerce-orders-table__header-subscription-actions">&nbsp;</th>
			</tr>
		</thead>

		<tbody>
		<?php /** @var WC_Subscription $subscription */ ?>
		<?php foreach ( $subscriptions as $subscription_id => $subscription ) : ?>
			<tr class="order woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr( $subscription->get_status() ); ?>">
				<td class="subscription-id order-number woocommerce-orders-table__cell woocommerce-orders-table__cell-subscription-id woocommerce-orders-table__cell-order-number" data-title="<?php esc_attr_e( 'ID', 'woocommerce-subscriptions' ); ?>">
					<?php // translators: placeholder is a subscription number. ?>
					<a href="<?php echo esc_url( $subscription->get_view_order_url() ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'View subscription number %s', 'woocommerce-subscriptions' ), $subscription->get_order_number() ) ) ?>">
						<?php echo esc_html( sprintf( _x( '#%s', 'hash before order number', 'woocommerce-subscriptions' ), $subscription->get_order_number() ) ); ?>
					</a>
					<?php do_action( 'woocommerce_my_subscriptions_after_subscription_id', $subscription ); ?>
				</td>
				<td class="subscription-id order-number woocommerce-orders-table__cell woocommerce-orders-table__cell-subscription-id woocommerce-orders-table__cell-order-number" data-title="<?php esc_attr_e( 'ID', 'woocommerce-subscriptions' ); ?>">
					<?php 	
						$order = wc_get_order($subscription->get_parent_id());
						$est_revendeur = current_user_can('customer_revendeur'); // adapte selon ton rôle
						$selected_client_id  = $order->get_meta('client_final');
						$client = get_user_by('id', $selected_client_id);
						$denomination_cf  = get_user_meta($selected_client_id, 'denomination', true);
					?>
					<?php if ( $est_revendeur ) : ?>
						<?php echo $client->display_name.' - '.$denomination_cf; ?> 
					<?php endif; ?>
				</td>
				<td class="subscription-status order-status woocommerce-orders-table__cell woocommerce-orders-table__cell-subscription-status woocommerce-orders-table__cell-order-status" data-title="<?php esc_attr_e( 'Status', 'woocommerce-subscriptions' ); ?>">
					<?php 
						echo esc_attr( wcs_get_subscription_status_name( $subscription->get_status() ) ); 
						$paiement_differe = $order->get_meta('_paiement_differe');
						if ($order->get_meta('_paiement_differe') === 'yes') {
							echo(' ( paiement en fin de mois )');
						}
					?>
				</td>
				<td class="subscription-next-payment order-date woocommerce-orders-table__cell woocommerce-orders-table__cell-subscription-next-payment woocommerce-orders-table__cell-order-date" data-title="<?php echo esc_attr_x( 'Next Payment', 'table heading', 'woocommerce-subscriptions' ); ?>" 
				
				<?php echo 'data-order="' . esc_attr( $subscription->get_time( 'next_payment' ) ) . '"';?>

				>
					<?php echo esc_attr( $subscription->get_date_to_display( 'next_payment' ) ); ?>
					<?php if ( ! $subscription->is_manual() && $subscription->has_status( 'active' ) && $subscription->get_time( 'next_payment' ) > 0 ) : ?>
					<br/><small><?php echo esc_attr( $subscription->get_payment_method_to_display( 'customer' ) ); ?></small>
					<?php endif; ?>
				</td>
				<td class="subscription-total order-total woocommerce-orders-table__cell woocommerce-orders-table__cell-subscription-total woocommerce-orders-table__cell-order-total" data-title="<?php echo esc_attr_x( 'Total', 'Used in data attribute. Escaped', 'woocommerce-subscriptions' ); ?>">
					<?php echo wc_price($subscription->get_total()); ?>
				</td>
				<td class="subscription-actions order-actions woocommerce-orders-table__cell woocommerce-orders-table__cell-subscription-actions woocommerce-orders-table__cell-order-actions">
					<a href="<?php echo esc_url( $subscription->get_view_order_url() ) ?>" class="woocommerce-button button view<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>"><?php echo esc_html_x( 'View', 'view a subscription', 'woocommerce-subscriptions' ); ?></a>
					<?php do_action( 'woocommerce_my_subscriptions_actions', $subscription ); ?>

					<?php do_action( 'woocommerce_subscription_before_actions', $subscription ); ?>
					<?php $actions = wcs_get_all_user_actions_for_subscription( $subscription, get_current_user_id() ); ?>
					<?php if ( ! empty( $actions ) ) : ?>
						<div>
							
							<div>
								<?php foreach ( $actions as $key => $action ) : ?>
									<?php
									$classes   = [ 'woocommerce-button', 'button', sanitize_html_class( $key ) ];
									$classes[] = isset( $action['block_ui'] ) && $action['block_ui'] ? 'wcs_block_ui_on_click' : '';

									if ( wc_wp_theme_get_element_class_name( 'button' ) ) {
										$classes[] = wc_wp_theme_get_element_class_name( 'button' );
									}
									if ( $key!="subscription_renewal_early" ) {
										continue;
									}
									?>
									<a
										href="<?php echo esc_url( $action['url'] ); ?>"
										class="<?php echo esc_attr( trim( implode( ' ', $classes ) ) ); ?>"
									>
										<?php echo esc_html( $action['name'] ); ?>
									</a>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>
					<?php do_action( 'woocommerce_subscription_after_actions', $subscription ); ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>

		</table>

	<?php else : ?>
		<p class="no_subscriptions woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
			<?php if ( 1 < $current_page ) :
				printf( esc_html__( 'You have reached the end of subscriptions. Go to the %sfirst page%s.', 'woocommerce-subscriptions' ), '<a href="' . esc_url( wc_get_endpoint_url( 'subscriptions', 1 ) ) . '">', '</a>' );
				else :
					esc_html_e( 'You have no active subscriptions.', 'woocommerce-subscriptions' );
					?>
					<a class="woocommerce-Button button" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
						<?php esc_html_e( 'Browse products', 'woocommerce-subscriptions' ); ?>
					</a>
				<?php
			endif; ?>
		</p>

	<?php endif; ?>
	
</div>


<!-- jQuery et DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
jQuery(document).ready(function($) {
    $('#tableMesAbonnements').DataTable( {
            language: {
                processing:     "Traitement en cours...",
                search:         "",
                lengthMenu: '<select>'+
                '<option value="10">10 lignes</option>'+
                '<option value="25" >25 lignes</option>'+
                '<option value="50">50 lignes</option>'+
                '<option value="100">100 lignes</option>'+
                '</select>',
                info:           "Affichage des &eacute;lements _START_ &agrave; _END_",
                infoEmpty:      "Affichage de l'&eacute;lement 0 &agrave; 0 sur 0 lignes",
                infoFiltered:   "(filtr&eacute; de _MAX_ lignes au total)",
                infoPostFix:    "",
                loadingRecords: "Chargement en cours...",
                zeroRecords:    "Aucun &eacute;l&eacute;ment &agrave; afficher",
                emptyTable:     "Aucune donnée disponible dans le tableau",
                paginate: {
                    first:      "Premier",
                    previous:   "Pr&eacute;c&eacute;dent",
                    next:       "Suivant",
                    last:       "Dernier"
                },
                aria: {
                    sortAscending:  ": activer pour trier la colonne par ordre croissant",
                    sortDescending: ": activer pour trier la colonne par ordre décroissant"
                }
            },
            "paging": true,
            info: false,
            order: [[1, 'desc'],[2, 'desc'],[3, 'desc'],[4, 'desc']]
        } );
});
</script>