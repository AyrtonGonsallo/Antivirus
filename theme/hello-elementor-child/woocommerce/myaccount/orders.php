<?php
/**
 * Orders
 *
 * Shows orders on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/orders.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.5.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_orders', $has_orders ); ?>



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
.elementor-widget-woocommerce-my-account .e-my-account-tab__orders .woocommerce-MyAccount-content-wrapper, .elementor-widget-woocommerce-my-account .e-my-account-tab__payment-methods .woocommerce-MyAccount-content-wrapper {
    border: none !important;
}

#tableCommandes {
    border-radius: 15px !important;
    padding: 16px 30px !important;
    border: solid 1px #c0ebfa !important;
}
</style>



<?php if ( $has_orders ) : ?>

	<table id="tableCommandes" class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
		<thead>
			<tr>
				<?php foreach ( wc_get_account_orders_columns() as $column_id => $column_name ) : ?>
					<th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo esc_html( $column_name ); ?></span></th>
				<?php endforeach; ?>
			</tr>
		</thead>

		<tbody>
			<?php
			foreach ( $customer_orders->orders as $customer_order ) {
				$order      = wc_get_order( $customer_order ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				$item_count = $order->get_item_count() - $order->get_item_count_refunded();
				?>
				<tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr( $order->get_status() ); ?> order">
					<?php foreach ( wc_get_account_orders_columns() as $column_id => $column_name ) :
						$is_order_number = 'order-number' === $column_id;
					?>
						<?php if ( $is_order_number ) : ?>
							<th class="woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>" scope="row">
						<?php else : ?>
							<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>"
							<?php
							if ( 'order-date' === $column_id ) {
								echo 'data-order="' . esc_attr( $order->get_date_created()->getTimestamp() ) . '"';
							}
							?>
							>
						<?php endif; ?>

							<?php if ( has_action( 'woocommerce_my_account_my_orders_column_' . $column_id ) ) : ?>
								<?php do_action( 'woocommerce_my_account_my_orders_column_' . $column_id, $order ); ?>

							<?php elseif ( $is_order_number ) : ?>
								<?php /* translators: %s: the order number, usually accompanied by a leading # */ ?>
								<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'View order number %s', 'woocommerce' ), $order->get_order_number() ) ); ?>">
									<?php echo esc_html( _x( '#', 'hash before order number', 'woocommerce' ) . $order->get_order_number() ); ?>
								</a>

							<?php elseif ( 'order-date' === $column_id ) : ?>
								<time datetime="<?php echo esc_attr( $order->get_date_created()->date( 'c' ) ); ?>"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></time>

							<?php elseif ( 'order-status' === $column_id ) : ?>
								<?php 
									echo esc_html( wc_get_order_status_name( $order->get_status() ) ); 
									$paiement_differe = $order->get_meta('_paiement_differe');
									if ($order->get_meta('_paiement_differe') === 'yes') {
										echo(' ( paiement en fin de mois )');
									}
								?>

							<?php elseif ( 'order-total' === $column_id ) : ?>
								<?php
								/* translators: 1: formatted order total 2: total order items */
								echo wp_kses_post( sprintf( _n( '%1$s for %2$s item', '%1$s for %2$s items', $item_count, 'woocommerce' ), $order->get_formatted_order_total(), $item_count ) );
								?> 

								<?php
									$est_revendeur = current_user_can('customer_revendeur'); // adapte selon ton rôle
									$selected_client_id  = $order->get_meta('client_final');
									$client = get_user_by('id', $selected_client_id);
								?>
								<?php if ( $est_revendeur ) : ?>
										<?php esc_html_e( 'Client final', 'woocommerce' ); ?>
										: <strong><?php echo $client->display_name; ?></strong>
								<?php endif; ?>

								

							<?php elseif ( 'order-actions' === $column_id ) : ?>
								
								<?php
								$actions = wc_get_account_orders_actions( $order );

								if ( ! empty( $actions ) ) {
									foreach ( $actions as $key => $action ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
									
									if($key=="cancel" && $order->get_meta('_paiement_differe') === 'yes'){
										continue;
									}
									if ( empty( $action['aria-label'] ) ) {
											// Generate the aria-label based on the action name.
											/* translators: %1$s Action name, %2$s Order number. */
											$action_aria_label = sprintf( __( '%1$s order number %2$s', 'woocommerce' ), $action['name'], $order->get_order_number() );
										} else {
											$action_aria_label = $action['aria-label'];
										}
										echo '<a href="' . esc_url( $action['url'] ) . '" class="woocommerce-button' . esc_attr( $wp_button_class ) . ' button ' . sanitize_html_class( $key ) . '" aria-label="' . esc_attr( $action_aria_label ) . '">' . esc_html( $action['name'] ) . '</a>';
										unset( $action_aria_label );
									}
								}
								?>
							<?php endif; ?>

						<?php if ( $is_order_number ) : ?>
							</th>
						<?php else : ?>
							</td>
						<?php endif; ?>
					<?php endforeach; ?>
				</tr>
				<?php
			}
			?>
		</tbody>
	</table>

	<?php do_action( 'woocommerce_before_account_orders_pagination' ); ?>



<?php else : ?>

	<?php wc_print_notice( esc_html__( 'No order has been made yet.', 'woocommerce' ) . ' <a class="woocommerce-Button wc-forward button' . esc_attr( $wp_button_class ) . '" href="' . esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ) . '">' . esc_html__( 'Browse products', 'woocommerce' ) . '</a>', 'notice' ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment ?>

<?php endif; ?>

<?php do_action( 'woocommerce_after_account_orders', $has_orders ); ?>




<!-- jQuery et DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
jQuery(document).ready(function($) {
    $('#tableCommandes').DataTable( {
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