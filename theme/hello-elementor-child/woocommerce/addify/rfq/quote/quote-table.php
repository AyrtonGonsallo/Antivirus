<?php
/**
 * Customer information table for email.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/addify/rfq/quote/quote-table.php.
 *
 * @package addify-request-a-quote
 * @version 1.6.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $af_quote ) ) {
	$af_quote = new AF_R_F_Q_Quote();
}

$allowed_user_roles = get_option('afrfq_enable_for_specific_user_role');

if (is_user_logged_in()) {
	$user = wp_get_current_user(); // object.
	$user_role = $user->roles;
} else {
	$user_role = array( 'guest' );
}

$price_display    = 'yes' === get_option( 'afrfq_enable_pro_price' ) && ( empty($allowed_user_roles) || !empty(array_intersect($user_role, $allowed_user_roles)) ) ? true : false;
$of_price_display = 'yes' === get_option( 'afrfq_enable_off_price' ) && ( empty($allowed_user_roles) || !empty(array_intersect($user_role, $allowed_user_roles)) ) ? true : false;
$tax_display      = 'yes' === get_option( 'afrfq_enable_tax' ) && ( empty($allowed_user_roles) || !empty(array_intersect($user_role, $allowed_user_roles)) ) ? true : false;

$colspan          = 4;
$colspan          = $price_display ? $colspan + 2 : $colspan;
$colspan          = $of_price_display ? $colspan + 2 : $colspan;

do_action( 'addify_before_quote_table' );

$af_page_template = !empty(get_option('afrfq_select_page_template')) ? get_option('afrfq_select_page_template') : 'template_one';
if ( 'template_one' == $af_page_template ) {
	?>
	<table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents  addify-quote-form__contents" cellspacing="0">
		<thead>
			<tr>
				<th class="product-remove">&nbsp;</th>
				<th class="product-thumbnail"><?php esc_html_e( 'Image', 'addify_rfq' ); ?></th>
				<th class="product-name"><?php esc_html_e( 'Product', 'addify_rfq' ); ?></th>
				<?php if ( $price_display ) : ?>
					<th class="product-price"><?php esc_html_e( 'Price', 'addify_rfq' ); ?></th>
				<?php endif; ?>
				<?php if ( $of_price_display ) : ?>
					<th class="product-price"><?php esc_html_e( 'Offered Price', 'addify_rfq' ); ?></th>
				<?php endif; ?>
				<th class="product-duree">
					<?php esc_html_e( 'Durée', 'woocommerce' ); ?>
				</th>
				<th class="product-quantity"><?php esc_html_e( 'Quantity', 'addify_rfq' ); ?></th>
				<?php if ( $price_display ) : ?>
					<th class="product-subtotal"><?php esc_html_e( 'Subtotal', 'addify_rfq' ); ?></th>
				<?php endif; ?>
				<?php if ( $of_price_display ) : ?>
					<th class="product-subtotal"><?php esc_html_e( 'Offered Subtotal', 'addify_rfq' ); ?></th>
				<?php endif; ?>
			</tr>
		</thead>
		<tbody>
			<?php do_action( 'addify_before_quote_contents' ); ?>

			<?php
			foreach ( WC()->session->get( 'quotes' ) as $quote_item_key => $quote_item ) {

				if ( ! isset( $quote_item['data'] ) || ! is_object( $quote_item['data'] ) ) {
					continue;
				}

				$_product      = apply_filters( 'addify_quote_item_product', $quote_item['data'], $quote_item, $quote_item_key );
				$product_id    = apply_filters( 'addify_quote_item_product_id', $quote_item['product_id'], $quote_item, $quote_item_key );
				$price         = empty( $quote_item['addons_price'] ) ? $_product->get_price() : $quote_item['addons_price'];
				$price         = empty( $quote_item['role_base_price'] ) ? $_product->get_price() : $quote_item['role_base_price'];
				$price         = empty( $quote_item['price_calculator_price'] ) ? $price : $quote_item['price_calculator_price'];
				$price         = isset( $quote_item['composite_product_price'] ) ? $quote_item['composite_product_price'] : $price;
				$offered_price = isset( $quote_item['offered_price'] ) ? floatval( $quote_item['offered_price'] ) : $price;

				$component_name = isset($quote_item['component_name']) ? $quote_item['component_name'] : '';

				if ( $_product && $_product->exists() && $quote_item['quantity'] > 0 && apply_filters( 'addify_quote_item_visible', true, $quote_item, $quote_item_key ) ) {
					$product_permalink = apply_filters( 'addify_quote_item_permalink', $_product->is_visible() ? $_product->get_permalink( $quote_item ) : '', $quote_item, $quote_item_key );
					?>
					<tr class=" <?php echo esc_attr( apply_filters( 'addify_quote_item_class', 'cart_item', $quote_item, $quote_item_key ) ); ?>">

						<?php if (!isset($quote_item['composite_child_products']) || empty($quote_item['composite_child_products'])) : ?>
							<td class="product-remove">
								<?php
									// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									echo wp_kses_post(
										apply_filters(
											'addify_quote_item_remove_link',
											sprintf(
												'<a href="%s" class="remove remove-cart-item remove-quote-item" aria-label="%s" data-cart_item_key="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
												esc_attr( $quote_item_key ),
												esc_html__( 'Remove this item', 'addify_rfq' ),
												esc_attr( $quote_item_key ),
												esc_attr( $product_id ),
												esc_attr( $_product->get_sku() )
											),
											$quote_item_key
										)
									);
								?>
							</td>
						<?php else : ?>
							<td></td>
						<?php endif; ?>

						<td class="product-thumbnail">
						<?php
						$thumbnail = apply_filters( 'addify_quote_item_thumbnail', $_product->get_image(), $quote_item, $quote_item_key );

						if ( ! $product_permalink ) {
							echo wp_kses_post( $thumbnail ); // phpcs:ignore WordPress.Security.EscapeOutput
						} else {
							printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), wp_kses_post( $thumbnail ) ); // phpcs:ignore WordPress.Security.EscapeOutput
						}
						?>
						</td>

						<td class="product-name" data-title="<?php esc_attr_e( 'Product', 'addify_rfq' ); ?>">
							<?php
							if ($component_name) {
								echo esc_html($component_name) . '<br>';
							}
							if ( ! $product_permalink ) {
								echo wp_kses_post( apply_filters( 'addify_quote_item_name', $_product->get_name(), $quote_item, $quote_item_key ) . '&nbsp;' );
							} else {
								echo wp_kses_post( apply_filters( 'addify_quote_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $quote_item, $quote_item_key ) );
							}

						do_action( 'addify_after_quote_item_name', $quote_item, $quote_item_key );

						// Meta data.
						echo wp_kses_post( wc_get_formatted_cart_item_data( $quote_item ) ); // phpcs:ignore WordPress.Security.EscapeOutput

						// Backorder notification.
							if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $quote_item['quantity'] ) ) {
								echo wp_kses_post( apply_filters( 'addify_quote_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'addify_rfq' ) . '</p>', $product_id ) );
							}
							if (!empty($_product->get_sku())) {
								echo wp_kses_post( sprintf( '<p><small><b>SKU:</b>%s</small></p>', esc_attr( $_product->get_sku() ) ) );
							}
							?>
						</td>

						<?php if ( $price_display ) : ?>
							<td class="product-price" data-title="<?php esc_attr_e( 'Price', 'addify_rfq' ); ?>">
								<?php
									$args['qty']   = 1;
									$args['price'] = empty( $quote_item['addons_price'] ) ? $_product->get_price() : $quote_item['addons_price'];
									$args['price'] = empty( $quote_item['role_base_price'] ) ? $_product->get_price() : $quote_item['role_base_price'];
									$args['price'] = empty( $quote_item['price_calculator_price'] ) ? $args['price'] : $quote_item['price_calculator_price'];
									$args['price'] = isset( $quote_item['composite_product_price'] ) ? $quote_item['composite_product_price'] : $args['price'];
									echo wp_kses_post( apply_filters( 'addify_quote_item_price', $af_quote->get_product_price( $_product, $args ), $quote_item, $quote_item_key ) ); // phpcs:ignore WordPress.Security.EscapeOutput
								?>
							</td>
						<?php endif; ?>
						
						<?php if ( $of_price_display && ( !isset($quote_item['composite_child_products']) || empty($quote_item['composite_child_products']) ) ) : ?>
							<td class="product-price offered-price" data-title="<?php esc_attr_e( 'Offered Price', 'addify_rfq' ); ?>">
								<input type="number" class="input-text offered-price-input text" step="any" name="offered_price[<?php echo esc_attr( $quote_item_key ); ?>]" value="<?php echo esc_attr( $offered_price ); ?>">
							</td>
						<?php elseif ($of_price_display) : ?>
							<td></td>
						<?php endif; ?>
						<td class="product-duree">
							<?php 
							//var_dump($quote_item);
								$selected_duree = isset($quote_item['alm_duree']) ? $quote_item['alm_duree'] : '';
								echo "alm_duree ".$quote_item['alm_duree'];
							?>
							<select name="alm_duree[<?php echo $quote_item_key; ?>]">
								<option value="1" <?php selected($selected_duree, '1'); ?>>1 an</option>
								<option value="2" <?php selected($selected_duree, '2'); ?>>2 ans</option>
								<option value="3" <?php selected($selected_duree, '3'); ?>>3 ans</option>
							</select>
						</td>
						<?php 
						
						
						// current product is composite child product
						$allow_edit_in_quote = true;
						if (isset($quote_item['composite_child_products']) && !empty($quote_item['composite_child_products'])) {
							$allow_edit_in_quote = false;

							$parent_product_id = isset($quote_item['composite_child_products']['parent_product_id']) && !empty($quote_item['composite_child_products']['parent_product_id']) ? $quote_item['composite_child_products']['parent_product_id'] : '';
							$comp_key = isset($quote_item['composite_child_products']['comp_key']) && !empty($quote_item['composite_child_products']['comp_key']) ? $quote_item['composite_child_products']['comp_key'] : '';
							if ($parent_product_id && $comp_key) {
								$allow_edit = get_post_meta($parent_product_id, 'af_cp_allow_edit_in_cart', true) ;

								if ('yes' == $allow_edit) {
									$is_component_range_enabled = get_post_meta($parent_product_id, 'af_composite_product_quantity_op', true);
									
									if ('range' == $is_component_range_enabled[ $comp_key ]) {
										$allow_edit_in_quote = true;
									}
								} else {
									$allow_edit_in_quote = false;
								}
							}

						}
						
						if ($allow_edit_in_quote) :
							?>
							<td class="product-quantity" data-title="<?php esc_attr_e( 'Quantity', 'addify_rfq' ); ?>">
							<?php
							if ( $_product->is_sold_individually() ) {
								$product_quantity = sprintf( '<input type="hidden" name="quote_qty[%s]" value="1" />', $quote_item_key );
							} else {
								$max_product_quantity = apply_filters('addify_quote_product_quantity_maximum', $_product->get_max_purchase_quantity(), $_product, $quote_item);
								woocommerce_quantity_input(
									array(
										'input_name'   => "quote_qty[{$quote_item_key}]",
										'input_value'  => $quote_item['quantity'],
										'max_value'    => $max_product_quantity,
										'min_value'    => '0',
										'product_name' => $_product->get_name(),
									),
									$_product,
									true
								);
							}
							?>
							</td>
						<?php else : ?>
							<td class="product-quantity" data-title="<?php esc_attr_e( 'Quantity', 'addify_rfq' ); ?>">
							<?php
							echo esc_attr( $quote_item['quantity'] );
							?>
							</td>
						<?php endif; ?>

						<?php if ( $price_display && ( !isset($quote_item['composite_child_products']) || empty($quote_item['composite_child_products']) ) ) : ?>
							<td class="product-subtotal" data-title="<?php esc_attr_e( 'Subtotal', 'addify_rfq' ); ?>">
								<?php
									$args['qty']   = $quote_item['quantity'];
									$args['price'] = empty( $quote_item['addons_price'] ) ? $_product->get_price() : $quote_item['addons_price'];
									$args['price'] = empty( $quote_item['role_base_price'] ) ? $args['price'] : $quote_item['role_base_price'];
									$args['price'] = empty( $quote_item['price_calculator_price'] ) ? $args['price'] : $quote_item['price_calculator_price'];
									$args['price'] = isset( $quote_item['composite_product_price'] ) ? $quote_item['composite_product_price'] : $args['price'];
									echo wp_kses_post( apply_filters( 'addify_quote_item_subtotal', $af_quote->get_product_subtotal( $_product, $quote_item['quantity'], $args ), $quote_item, $quote_item_key ) ); // phpcs:ignore WordPress.Security.EscapeOutput
								?>
							</td>
						<?php endif; ?>	

						<?php if ( $of_price_display && ( !isset($quote_item['composite_child_products']) || empty($quote_item['composite_child_products']) ) ) : ?>
							<td class="product-subtotal" data-title="<?php esc_attr_e( 'Offered Subtotal', 'addify_rfq' ); ?>">
								<?php
									echo wp_kses_post( apply_filters( 'addify_quote_item_subtotal', wc_price( $offered_price * $quote_item['quantity'] ), $quote_item, $quote_item_key ) ); // phpcs:ignore WordPress.Security.EscapeOutput
								?>
							</td>
						<?php endif; ?>
						
					</tr>
					<?php
				}
			}
			?>
			<td colspan="<?php echo esc_attr( $colspan ); ?>" class="actions" >

					<?php
					$afrfq_update_button_text     = get_option( 'afrfq_update_button_text' );
					$afrfq_update_button_bg_color = get_option( 'afrfq_update_button_bg_color' );
					$afrfq_update_button_fg_color = get_option( 'afrfq_update_button_fg_color' );
					$afrfq_update_button_text     = empty( $afrfq_update_button_text ) ? __( 'Update Quote', 'addify_rfq' ) : $afrfq_update_button_text;
					?>

					<style type="text/css">
						.afrfq_update_quote_btn{
							color: <?php echo esc_html( $afrfq_update_button_fg_color ); ?> !important;
							background-color: <?php echo esc_html( $afrfq_update_button_bg_color ); ?> !important;
						}
					</style>

					<button type="button" type="submit" id="afrfq_update_quote_btn" class="button afrfq_update_quote_btn" name="update_quote" value="<?php esc_html( $afrfq_update_button_text ); ?>"><?php echo esc_html( $afrfq_update_button_text ); ?></button> 

					<?php do_action( 'addify_quote_actions' ); ?>

					<?php wp_nonce_field( 'addify-cart', 'addify-cart-nonce' ); ?>
				</td>
			</tbody>
			<?php do_action( 'addify_quote_contents' ); ?>
			</tbody>
		</table>

<?php } elseif ('template_two' == $af_page_template) { ?>
	<style type="text/css">
		.addify-quote-form__contents.template-two th {
			background: none!important;
			color: #000!important;
			padding: 13px 8px !important;
			border-bottom: 1px solid #a9a2a28a!important;
		}
		.addify-quote-form__contents.template-two, .addify-quote-form.template_two .cart_totals table{
			border: none!important;
		}
		.addify-quote-form__contents.template-two td{
			padding: 13px 8px !important;
		}
		.addify-quote-form__contents.template-two .product-name p{
			margin-bottom: 7px !important;
			margin-top: 6px;
			font-size: 13px;
			line-height: 22px;
		}

		.addify-quote-form__contents.template-two .product-quantity{
			margin: 10px 0 !important;
		}
		.template-two .product-quantity input{
			background: no-repeat;
			box-shadow: none;
			border: 1px solid #5555556e;
			border-radius: 1px;
			font-size: 12px;
			line-height: 23px;
			text-align: center;
			border-radius: 3px;
			min-height: auto!important;
			width: 44px !important;
			max-width: 44px !important;
			padding: 0px 0 0 4px !important;
		}
		.template-two .offered-price .offered-price-input, .product-quantity input{
			border-radius: 2px;
			border: 1px solid #5555556e;
		}
		.addify-quote-form.template_two .af_quote_fields .addify-option-field label{
			font-size: 13px;
			line-height: 17px;
			font-weight: 600;
		}
		.addify-quote-form.template_two div.af_quote_fields input[type="text"], .addify-quote-form.template_two div.af_quote_fields input[type="email"], .addify-quote-form.template_two div.af_quote_fields input[type="time"], .addify-quote-form.template_two div.af_quote_fields input[type="date"], .addify-quote-form.template_two div.af_quote_fields input[type="datetime-local"], .addify-quote-form.template_two div.af_quote_fields select, .addify-quote-form.template_two div.af_quote_fields textarea, .addify-quote-form.template_two div.af_quote_fields input[type="number"]{
			font-size: 13px;
			line-height: 23px;
			padding: 7px;
			box-sizing: border-box;
		}
		.addify-quote-form.template_two div.af_quote_fields select{
			height: 42px;
		}
		.addify-quote-form.template_two .addify-option-field input[type="file"] {
			font-size: 12px;
		}
		.addify-quote-form.template_two .cart_totals h2{
			border-bottom: 1px solid #a9a2a28a;
			padding: 0 0 16px 0!important;
			font-size: 20px;
			line-height: 30px;
			margin-bottom: 9px;
		}
		.addify-quote-form.template_two .cart_totals table tr th,
		.addify-quote-form__contents.template-two th:first-child,
		.addify-quote-form__contents.template-two td:first-child{
			padding-left: 0!important;
		}
		.addify-quote-form__contents.template-two th:last-child,
		.addify-quote-form__contents.template-two td:last-child{
			padding-right: 0!important;	
		}
		.addify-quote-form.template_two .cart_totals table tr td{
			font-size: 14px;
			line-height: 24px;
			background: #fff0!important;
		}
		.template-two .product-name a{
			/* removed color it is causing issue with some themes */
			/* color: #000;  */
			display: block;
			font-size: 14px;
			line-height: 21px;
			margin-bottom: 0px;
		}
		.template_two .adf-radio-btn, .template_two .adf-chekboxes{
			font-size: 13px!important;
			line-height: 23px!important;
			margin-bottom: 4px!important;
			font-weight: normal!important;
		}
		/*.template_two .product-subtotal{
			min-width: 80px;
		}
		.template_two .product-subtotal.offered-subtotal{
			min-width: 122px;
		}
		.template_two .product-price.offered-price{
			min-width: 107px;
		}*/
		.template_two .af-quote-field-table .addify-option-field  .select2-selection__rendered li.select2-selection__choice{
			font-size: 12px;
			line-height: 18px;
			background-color: #e0e0e061;
			padding: 2px 7px;
		}
		.template_two .af-quote-field-table .addify-option-field .select2-container--default .select2-selection--multiple{
			border: 1px solid #d3d3d373;
			min-height: 42px;
		}
		
	</style>
	<table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents  addify-quote-form__contents template-two" cellspacing="0">
		<thead>
			<tr>
				<th class="product-thumbnail">&nbsp;</th>
				<th class="product-name"><?php esc_html_e( 'Product', 'addify_rfq' ); ?></th>
				<?php if ( $price_display ) : ?>
					<th class="product-price"><?php esc_html_e( 'Price', 'addify_rfq' ); ?></th>
				<?php endif; ?>
				<?php if ( $of_price_display ) : ?>
					<th class="product-price offered-price"><?php esc_html_e( 'Offered Price', 'addify_rfq' ); ?></th>
				<?php endif; ?>
				<?php if ( $price_display ) : ?>
					<th class="product-subtotal"><?php esc_html_e( 'Subtotal', 'addify_rfq' ); ?></th>
				<?php endif; ?>
				<?php if ( $of_price_display ) : ?>
					<th class="product-subtotal offered-subtotal"><?php esc_html_e( 'Offered Subtotal', 'addify_rfq' ); ?></th>
				<?php endif; ?>
			</tr>
		</thead>
		<tbody>
			<?php do_action( 'addify_before_quote_contents' ); ?>

			<?php
			foreach ( WC()->session->get( 'quotes' ) as $quote_item_key => $quote_item ) {

				if ( ! isset( $quote_item['data'] ) || ! is_object( $quote_item['data'] ) ) {
					continue;
				}

				$_product      = apply_filters( 'addify_quote_item_product', $quote_item['data'], $quote_item, $quote_item_key );
				$product_id    = apply_filters( 'addify_quote_item_product_id', $quote_item['product_id'], $quote_item, $quote_item_key );
				$price         = empty( $quote_item['addons_price'] ) ? $_product->get_price() : $quote_item['addons_price'];
				$price         = empty( $quote_item['role_base_price'] ) ? $_product->get_price() : $quote_item['role_base_price'];
				$price         = empty( $quote_item['price_calculator_price'] ) ? $price : $quote_item['price_calculator_price'];
				$price         = isset( $quote_item['composite_product_price'] ) ? $quote_item['composite_product_price'] : $price;
				$offered_price = isset( $quote_item['offered_price'] ) ? floatval( $quote_item['offered_price'] ) : $price;

				$component_name = isset($quote_item['component_name']) ? $quote_item['component_name'] : '';

				if ( $_product && $_product->exists() && $quote_item['quantity'] > 0 && apply_filters( 'addify_quote_item_visible', true, $quote_item, $quote_item_key ) ) {
					$product_permalink = apply_filters( 'addify_quote_item_permalink', $_product->is_visible() ? $_product->get_permalink( $quote_item ) : '', $quote_item, $quote_item_key );
					?>
					<tr class=" <?php echo esc_attr( apply_filters( 'addify_quote_item_class', 'cart_item', $quote_item, $quote_item_key ) ); ?>">


						<td class="product-thumbnail">
						<?php
						$thumbnail = apply_filters( 'addify_quote_item_thumbnail', $_product->get_image(), $quote_item, $quote_item_key );

						if ( ! $product_permalink ) {
							echo wp_kses_post( $thumbnail ); // phpcs:ignore WordPress.Security.EscapeOutput
						} else {
							printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), wp_kses_post( $thumbnail ) ); // phpcs:ignore WordPress.Security.EscapeOutput
						}
						
						if (!isset($quote_item['composite_child_products']) || empty($quote_item['composite_child_products'])) :
							?>
							<div class="adf-product-remove">
								<?php
									// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									echo wp_kses_post(
										apply_filters(
											'addify_quote_item_remove_link',
											sprintf(
												'<a href="%s" class=" remove-cart-item remove-quote-item" aria-label="%s" data-cart_item_key="%s" data-product_id="%s" data-product_sku="%s">Remove</a>',
												esc_attr( $quote_item_key ),
												esc_html__( 'Remove this item', 'addify_rfq' ),
												esc_attr( $quote_item_key ),
												esc_attr( $product_id ),
												esc_attr( $_product->get_sku() )
											),
											$quote_item_key
										)
									);
								?>
							</div>
						</td>
						<?php endif; ?>

						<td class="product-name" data-title="<?php esc_attr_e( 'Product', 'addify_rfq' ); ?>" style="vertical-align: top !important;">
							<?php
							if ($component_name) {
								echo esc_html($component_name) . '<br>';
							}
							if ( ! $product_permalink ) {
								echo wp_kses_post( apply_filters( 'addify_quote_item_name', $_product->get_name(), $quote_item, $quote_item_key ) . '&nbsp;' );
							} else {
								echo wp_kses_post( apply_filters( 'addify_quote_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $quote_item, $quote_item_key ) );
							}

							do_action( 'addify_after_quote_item_name', $quote_item, $quote_item_key );

							// Meta data.
							echo wp_kses_post( wc_get_formatted_cart_item_data( $quote_item ) ); // phpcs:ignore WordPress.Security.EscapeOutput

							// Backorder notification.
							if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $quote_item['quantity'] ) ) {
								echo wp_kses_post( apply_filters( 'addify_quote_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'addify_rfq' ) . '</p>', $product_id ) );
							}
							if (!empty($_product->get_sku())) {
								echo wp_kses_post( sprintf( '<p><small><b>SKU:</b>%s</small></p>', esc_attr( $_product->get_sku() ) ) );
							}
							

							$allow_edit_in_quote = true;
							if (isset($quote_item['composite_child_products']) && !empty($quote_item['composite_child_products'])) {
								$allow_edit_in_quote = false;

								$parent_product_id = isset($quote_item['composite_child_products']['parent_product_id']) && !empty($quote_item['composite_child_products']['parent_product_id']) ? $quote_item['composite_child_products']['parent_product_id'] : '';
								$comp_key = isset($quote_item['composite_child_products']['comp_key']) && !empty($quote_item['composite_child_products']['comp_key']) ? $quote_item['composite_child_products']['comp_key'] : '';
								if ($parent_product_id && $comp_key) {
									$allow_edit = get_post_meta($parent_product_id, 'af_cp_allow_edit_in_cart', true) ;

									if ('yes' == $allow_edit) {
										$is_component_range_enabled = get_post_meta($parent_product_id, 'af_composite_product_quantity_op', true);
										
										if ('range' == $is_component_range_enabled[ $comp_key ]) {
											$allow_edit_in_quote = true;
										}
									} else {
										$allow_edit_in_quote = false;
									}
								}

							}

							if ($allow_edit_in_quote) :
								?>
								<div class="product-quantity" data-title="<?php esc_attr_e( 'Quantity', 'addify_rfq' ); ?>">
									<?php
									if ( $_product->is_sold_individually() ) {
										$product_quantity = sprintf( '<input type="hidden" name="quote_qty[%s]" value="1" />', $quote_item_key );
									} else {
										$max_product_quantity = apply_filters('addify_quote_product_quantity_maximum', $_product->get_max_purchase_quantity(), $_product, $quote_item);
										woocommerce_quantity_input(
											array(
												'input_name'   => "quote_qty[{$quote_item_key}]",
												'input_value'  => $quote_item['quantity'],
												'max_value'    => $max_product_quantity,
												'min_value'    => '0',
												'product_name' => $_product->get_name(),
											),
											$_product,
											true
										);
									}
									?>
								</div>
							<?php else : ?>
								<div class="product-quantity" data-title="<?php esc_attr_e( 'Quantity', 'addify_rfq' ); ?>">
									<?php
									echo esc_attr( $quote_item['quantity'] );
									?>
								</div>
							<?php endif; ?>
						</td>

						<?php if ( $price_display ) : ?>
							<td class="product-price" data-title="<?php esc_attr_e( 'Price', 'addify_rfq' ); ?>">
								<?php
									$args['qty']   = 1;
									$args['price'] = empty( $quote_item['addons_price'] ) ? $_product->get_price() : $quote_item['addons_price'];
									$args['price'] = empty( $quote_item['role_base_price'] ) ? $_product->get_price() : $quote_item['role_base_price'];
									$args['price'] = empty( $quote_item['price_calculator_price'] ) ? $args['price'] : $quote_item['price_calculator_price'];
									$args['price'] = isset( $quote_item['composite_product_price'] ) ? $quote_item['composite_product_price'] : $args['price'];
									echo wp_kses_post( apply_filters( 'addify_quote_item_price', $af_quote->get_product_price( $_product, $args ), $quote_item, $quote_item_key ) ); // phpcs:ignore WordPress.Security.EscapeOutput
								?>
							</td>
						<?php endif; ?>
						
						<?php if ( $of_price_display && ( !isset($quote_item['composite_child_products']) || empty($quote_item['composite_child_products']) ) ) : ?>
							<td class="product-price offered-price" data-title="<?php esc_attr_e( 'Offered Price', 'addify_rfq' ); ?>">
								<input type="number" class="input-text offered-price-input text" step="any" name="offered_price[<?php echo esc_attr( $quote_item_key ); ?>]" value="<?php echo esc_attr( $offered_price ); ?>">
							</td>
						<?php elseif ($of_price_display) : ?>
							<td></td>
						<?php endif; ?>	

						<?php if ( $price_display && ( !isset($quote_item['composite_child_products']) || empty($quote_item['composite_child_products']) ) ) : ?>
							<td class="product-subtotal" data-title="<?php esc_attr_e( 'Subtotal', 'addify_rfq' ); ?>">
								<?php
									$args['qty']   = $quote_item['quantity'];
									$args['price'] = empty( $quote_item['addons_price'] ) ? $_product->get_price() : $quote_item['addons_price'];
									$args['price'] = empty( $quote_item['role_base_price'] ) ? $args['price'] : $quote_item['role_base_price'];
									$args['price'] = empty( $quote_item['price_calculator_price'] ) ? $args['price'] : $quote_item['price_calculator_price'];
									$args['price'] = isset( $quote_item['composite_product_price'] ) ? $quote_item['composite_product_price'] : $args['price'];

									echo wp_kses_post( apply_filters( 'addify_quote_item_subtotal', $af_quote->get_product_subtotal( $_product, $quote_item['quantity'], $args ), $quote_item, $quote_item_key ) ); // phpcs:ignore WordPress.Security.EscapeOutput
								?>
							</td>
						<?php endif; ?>	

						<?php if ( $of_price_display && ( !isset($quote_item['composite_child_products']) || empty($quote_item['composite_child_products']) ) ) : ?>
							<td class="product-subtotal" data-title="<?php esc_attr_e( 'Offered Subtotal', 'addify_rfq' ); ?>">
								<?php
									echo wp_kses_post( apply_filters( 'addify_quote_item_subtotal', wc_price( $offered_price * $quote_item['quantity'] ), $quote_item, $quote_item_key ) ); // phpcs:ignore WordPress.Security.EscapeOutput
								?>
							</td>
						<?php endif; ?>
						
					</tr>
					<?php
				}
			}
			?>
			<td colspan="<?php echo esc_attr( $colspan ); ?>" class="actions" style="display: none;">

				<?php
				$afrfq_update_button_text     = get_option( 'afrfq_update_button_text' );
				$afrfq_update_button_bg_color = get_option( 'afrfq_update_button_bg_color' );
				$afrfq_update_button_fg_color = get_option( 'afrfq_update_button_fg_color' );
				$afrfq_update_button_text     = empty( $afrfq_update_button_text ) ? __( 'Update Quote', 'addify_rfq' ) : $afrfq_update_button_text;
				?>

				<style type="text/css">
					.afrfq_update_quote_btn{
						color: <?php echo esc_html( $afrfq_update_button_fg_color ); ?> !important;
						background-color: <?php echo esc_html( $afrfq_update_button_bg_color ); ?> !important;
					}
				</style>

				<button type="button" type="submit" id="afrfq_update_quote_btn" class="button afrfq_update_quote_btn" name="update_quote" value="<?php esc_html( $afrfq_update_button_text ); ?>"><?php echo esc_html( $afrfq_update_button_text ); ?></button> 

				<?php do_action( 'addify_quote_actions' ); ?>

				<?php wp_nonce_field( 'addify-cart', 'addify-cart-nonce' ); ?>
			</td>
		</tbody>
		<?php do_action( 'addify_quote_contents' ); ?>
		</tbody>
	</table>

<?php } ?>
	
	<?php do_action( 'addify_after_quote_contents' ); ?>

	<?php do_action( 'addify_after_quote_table' ); ?>
