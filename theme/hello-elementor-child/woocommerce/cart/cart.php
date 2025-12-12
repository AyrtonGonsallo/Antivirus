<?php
/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.1.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' ); ?>

<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
	<?php do_action( 'woocommerce_before_cart_table' ); ?>
	
	<?php
		$current_user = wp_get_current_user();
		$revendeur_id = $current_user->ID;
		


		$est_revendeur = current_user_can('customer_revendeur'); // adapte selon ton rôle
	?>
	<table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
		<thead>
			<tr>
				<th class="product-remove"><span class="screen-reader-text"><?php esc_html_e( 'Remove item', 'woocommerce' ); ?></span></th>
				<th class="product-thumbnail"><span class="screen-reader-text"><?php esc_html_e( 'Thumbnail image', 'woocommerce' ); ?></span></th>
				<th scope="col" class="product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
				<th scope="col" class="software_duration"><?php esc_html_e( 'Durée', 'woocommerce' ); ?></th>
				<th scope="col" class="number_of_computers"><?php esc_html_e( 'PC', 'woocommerce' ); ?></th>
				
				<th scope="col" class="product-price"><?php esc_html_e( 'Price', 'woocommerce' ); ?> unitaire HT</th>
				<th scope="col" class="product-quantity"><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></th>
				<th scope="col" class="product-subtotal"><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?> HT</th>
				<!-- ✔️ Nouvelle colonne Durée -->
			

				<!-- ✔️ Colonne Client si revendeur -->
				<?php if ( $est_revendeur ) : ?>
					<th scope="col" class="product-client">
						<?php esc_html_e( 'Client', 'woocommerce' ); ?>
					</th>
				<?php endif; ?>
			</tr>
		</thead>
		<tbody>
			<?php do_action( 'woocommerce_before_cart_contents' ); ?>

			<?php
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				
				$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
				$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
				/**
				 * Filter the product name.
				 *
				 * @since 2.1.0
				 * @param string $product_name Name of the product in the cart.
				 * @param array $cart_item The product in the cart.
				 * @param string $cart_item_key Key for the product in the cart.
				 */
				$product_name = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );

				if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
					$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
					?>
					<tr class="woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

						<td class="product-remove">
							<?php
								echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									'woocommerce_cart_item_remove_link',
									sprintf(
										'<a role="button" href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
										esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
										/* translators: %s is the product name */
										esc_attr( sprintf( __( 'Remove %s from cart', 'woocommerce' ), wp_strip_all_tags( $product_name ) ) ),
										esc_attr( $product_id ),
										esc_attr( $_product->get_sku() )
									),
									$cart_item_key
								);
							?>
						</td>

						<td class="product-thumbnail">
						<?php
						/**
						 * Filter the product thumbnail displayed in the WooCommerce cart.
						 *
						 * This filter allows developers to customize the HTML output of the product
						 * thumbnail. It passes the product image along with cart item data
						 * for potential modifications before being displayed in the cart.
						 *
						 * @param string $thumbnail     The HTML for the product image.
						 * @param array  $cart_item     The cart item data.
						 * @param string $cart_item_key Unique key for the cart item.
						 *
						 * @since 2.1.0
						 */
						$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

						if ( ! $product_permalink ) {
							echo $thumbnail; // PHPCS: XSS ok.
						} else {
							printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail ); // PHPCS: XSS ok.
						}
						?>
						</td>

						<td scope="row" role="rowheader" class="product-name" data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>">
						<?php
						if ( ! $product_permalink ) {
							echo wp_kses_post( $product_name . '&nbsp;' );
						} else {
							/**
							 * This filter is documented above.
							 *
							 * @since 2.1.0
							 */
							echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) );
						}

						do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );

						// Meta data.
						echo wc_get_formatted_cart_item_data( $cart_item ); // PHPCS: XSS ok.

						// Backorder notification.
						if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
							echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>', $product_id ) );
						}
						?>
						</td>

						<td class="product-software_duration product-name" data-title="software_duration">
    
							
							<?php 



								$variation = $cart_item['data'];

								if ($variation->is_type('variation')) {

									$current_attr = $variation->get_attributes();  // attributs actuels
									$parent = wc_get_product($variation->get_parent_id());
									$attributes = $parent->get_attributes();

									// ===============================
									// SELECT Software Duration
									// ===============================

									if (isset($attributes['pa_software_duration'])) {

										$attr = $attributes['pa_software_duration'];
										$options = $attr->get_options(); // liste de term IDs
										$selected = $current_attr['pa_software_duration'] ?? '';
										
										echo '<select name="alm_Software_duration[' . $cart_item_key . ']" class="automatic-sent">';

										foreach ($options as $term_id) {

											$term = get_term($term_id);
											$value = $term->slug;
											$label = $term->name;

											echo '<option value="' . esc_attr($value) . '" '
													. selected($selected, $value, false) . '>'
													. esc_html($label)
												. '</option>';
										}

										echo '</select>';
									}

									
								}

							?>

							


							

						</td>

						<td class="product-number_of_computers product-name" data-title="number_of_computers">
    
							
							<?php 


								$variation = $cart_item['data'];

								if ($variation->is_type('variation')) {

									$current_attr = $variation->get_attributes();  // attributs actuels
									$parent = wc_get_product($variation->get_parent_id());
									$attributes = $parent->get_attributes();

				
									// ===============================
									// SELECT Number of computers il les prends dans les valeurs de l'attribut nombre de pcs et pas dans les stocks
									// ===============================

									if (isset($attributes['pa_number_of_computers'])) {

										$attr = $attributes['pa_number_of_computers'];
										$options = $attr->get_options();
										$selected = $current_attr['pa_number_of_computers'] ?? '';
										//var_dump($options);
										echo '<select name="alm_Number_of_computers[' . $cart_item_key . ']" class="automatic-sent">';

										foreach ($options as $term_id) {

											$term = get_term($term_id);
											$value = $term->slug;
											$label = $term->name;

											echo '<option value="' . esc_attr($value) . '" '
													. selected($selected, $value, false) . '>'
													. esc_html($label)
												. '</option>';
										}

										echo '</select>';
									}
								}

							?>

							


							

						</td>


						<td class="product-price" data-title="<?php esc_attr_e( 'Price', 'woocommerce' ); ?>">
							<?php
								echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
							?>
						</td>

						<td class="product-quantity" data-title="<?php esc_attr_e( 'Quantity', 'woocommerce' ); ?>">
						<?php
						if ( $_product->is_sold_individually() ) {
							$min_quantity = 1;
							$max_quantity = 1;
						} else {
							$min_quantity = 0;
							$max_quantity = $_product->get_max_purchase_quantity();
						}

						$product_quantity = woocommerce_quantity_input(
							array(
								'input_name'   => "cart[{$cart_item_key}][qty]",
								'input_value'  => $cart_item['quantity'],
								'max_value'    => $max_quantity,
								'min_value'    => $min_quantity,
								'product_name' => $product_name,
							),
							$_product,
							false
						);

						echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // PHPCS: XSS ok.
						?>
						</td>

						<td class="product-subtotal" data-title="<?php esc_attr_e( 'Subtotal', 'woocommerce' ); ?>">
							<?php
								echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
							?>
						</td>

					

						<?php if ( $est_revendeur ) : ?>
							<?php 
								$args = [
									'role'       => 'customer_particulier',
									'meta_key'   => 'revendeur_id',
									'meta_value' => $revendeur_id
								];

								$clients = get_users($args);
								$selected_client = isset($cart_item['alm_client']) ? $cart_item['alm_client'] : '';
							?>
							<td class="product-client product-name">
								<select name="alm_client[<?php echo $cart_item_key; ?>]" required class="automatic-sent">
									<!-- Option par défaut -->
									<option value="" disabled <?php selected( empty($selected_client) ); ?>>Sélectionnez un client</option>

									<?php foreach ( $clients as $c ) : ?>
										<option value="<?php echo $c->ID; ?>" <?php selected( $selected_client, $c->ID ); ?>>
											<?php echo esc_html($c->display_name); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</td>

						<?php endif; ?>


					</tr>
					<?php
				}
			}
			?>

			<?php do_action( 'woocommerce_cart_contents' ); ?>

			<tr>
				<td colspan="6" class="actions">

					<?php if ( wc_coupons_enabled() ) { ?>
						<div class="coupon">
							<label for="coupon_code" class="screen-reader-text"><?php esc_html_e( 'Coupon:', 'woocommerce' ); ?></label> <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" /> <button type="submit" class="button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_html_e( 'Apply coupon', 'woocommerce' ); ?></button>
							<?php do_action( 'woocommerce_cart_coupon' ); ?>
						</div>
					<?php } ?>

					<button type="submit" class="button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>"><?php esc_html_e( 'Update cart', 'woocommerce' ); ?></button>

					<?php do_action( 'woocommerce_cart_actions' ); ?>

					<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
				</td>
			</tr>

			<?php do_action( 'woocommerce_after_cart_contents' ); ?>
		</tbody>
	</table>
	<?php do_action( 'woocommerce_after_cart_table' ); ?>
</form>

<?php do_action( 'woocommerce_before_cart_collaterals' ); ?>

<div class="cart-collaterals">
	<?php
		/**
		 * Cart collaterals hook.
		 *
		 * @hooked woocommerce_cross_sell_display
		 * @hooked woocommerce_cart_totals - 10
		 */
		do_action( 'woocommerce_cart_collaterals' );
	?>
</div>

<?php do_action( 'woocommerce_after_cart' ); ?>


<script>
jQuery(document).ready(function($) {

    // Au changement d'un select personnalisé
    $(document).on('change', 'select.automatic-sent', function () {

        // Activer le bouton (WooCommerce le désactive souvent)
        //$('button[name="update_cart"]').prop('disabled', false);

        // Déclencher le clic automatiquement
        $('button[name="update_cart"]').trigger('click');
    });

});
	</script>