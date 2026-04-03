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

do_action( 'woocommerce_before_cart' ); 

$has_remise = user_has_remise(get_current_user_id());

?>

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
						//echo wc_get_formatted_cart_item_data( $cart_item ); // PHPCS: XSS ok.

						// Backorder notification.
						if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
							echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>', $product_id ) );
						}
						?>
						<?php 
								$price_regular = $_product->get_regular_price();
								$price_sale    = $_product->get_sale_price();
								$is_promo = $price_sale && $price_sale < $price_regular;
								if ($is_promo && !$has_remise) {
									$pourcentage = round(100 - ($price_sale / $price_regular * 100));
									echo '<br><span style="color:green;font-weight:bold;">Promo -'.$pourcentage.' %</span>';
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
								$price_regular = $_product->get_regular_price();
								$price_sale    = $_product->get_sale_price();
								$is_promo = $price_sale && $price_sale < $price_regular;
								if ($is_promo && !$has_remise) {
									$pourcentage = round(100 - ($price_final / $price_regular * 100));
									echo '<del>'.$price_regular.' €</del><br>';
								}
								?>
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
								$price_regular = $_product->get_regular_price()*$cart_item['quantity'];
								$price_sale    = $_product->get_sale_price();
								$is_promo = $price_sale && $price_sale < $price_regular;
								if ($is_promo && !$has_remise) {
									echo '<del>'.$price_regular.' €</del><br>';
								}
							?>
							<?php
								echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
							?>
						</td>

					

						


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

<?php if ( $est_revendeur ) : ?>
	<?php 
		$args_all_clients = [
			'role'       => 'customer_direct',
			'meta_key'   => 'revendeur_id',
			'meta_value' => $revendeur_id
		];

		$all_clients = get_users($args_all_clients);
		$selected_client_id = WC()->session->get('alm_client_final');
		
		if($selected_client_id){
			$args_selected_client = [
				'include'    => [$selected_client_id],   // chercher cet utilisateur précis
				'role'       => 'customer_direct',
				'meta_key'   => 'revendeur_id',
				'meta_value' => $revendeur_id
			];

			$clients = get_users($args_selected_client);
			$selected_client = !empty($clients) ? $clients[0] : null;

						
			$user_has_disabled_remises = has_user_disabled_remises($selected_client->ID);
			$user_has_enabled_remises = has_user_enabled_remises($selected_client->ID);
			$remise_c = get_user_remise_by_type($selected_client->ID,"Changement -25%");
			$remise_r = get_user_remise_by_type($selected_client->ID,"Renouvellement de licences -30%");
			$remise_a = get_user_remise_by_type($selected_client->ID,"Administrations et mairies -30%");
			$remise_e = get_user_remise_by_type($selected_client->ID,"Établissements scolaires et associations -50%");

			$desactiver1 = $user_has_disabled_remises || $remise_r || $remise_a || $remise_e;
			$desactiver2 = $user_has_disabled_remises || $remise_c;
			$desactiver3 = $user_has_disabled_remises || $remise_c || $remise_e;
			$desactiver4 = $user_has_disabled_remises || $remise_a || $remise_c;

			$current_remises .= ($remise_c)?get_field('type', $remise_c):"";
			$current_remises .= ($remise_r)?get_field('type', $remise_r):"";
			$current_remises .= ($remise_a)?get_field('type', $remise_a):"";
			$current_remises .= ($remise_e)?get_field('type', $remise_e):"";
		}

		
	?>

	<form method="post" class="form-client-final-cart">
		<label>Client final de la commande</label>

		<select name="alm_client_global" required class="automatic-sent2">
			<option value="">Sélectionnez un client</option>

			<?php foreach ($all_clients as $c) : 
				$denomination_cf  = get_user_meta($c->ID, 'denomination', true);
				?>
				<option value="<?php echo $c->ID; ?>" <?php selected($selected_client_id, $c->ID); ?>>
					<?php echo esc_html($c->display_name.' - '.$denomination_cf); ?>
				</option>
			<?php endforeach; ?>

		</select>

		<button type="submit" name="save_client_cart" style='visibility:hidden'>Enregistrer</button>
	</form>

	<?php if ( $selected_client ) : ?>
		<div class="div-remise">
			<form id="demandeRemise" method="post" enctype="multipart/form-data">
				<span style="font-family: 'Raleway';font-weight: 800;text-align:center;"> LE CLIENT PEUX BÉNÉFICIER<br> D'UNE REMISE COMMERCIALE :</span>
				<!-- Option 1 -->
				<label>
					<input type="checkbox" <?php if ($remise_c){ echo 'checked disabled'; }else if($desactiver1){echo 'disabled'; }  ?> name="option_remise[]"  id="option1" class="optionRemise" data-group="1" data-file="file1" data-value="Changement -25%"> Je change d'antivirus pour Avast -25%
				</label>
				<div class="upload hidden" id="file1">
					<input type="file" name="justificatif_changement" accept="application/pdf,image/*">
				</div>

				<!-- Option 2 -->
				<label>
					<input type="checkbox" <?php if ($remise_r){ echo 'checked disabled'; }else if($desactiver2){echo 'disabled'; } ?> name="option_remise[]"  id="option2" class="optionRemise" data-group="2" data-file="file2" data-value="Renouvellement de licences -30%"> Renouvellement de licences -30%
				</label>
				
				<div class="upload hidden" id="file2" style="display: flex;gap: 7px; align-items: center;justify-content: center;">
					<input  style="width: 50%;padding: 0.5rem 0.4rem; height: 30px;" type="text" id="old_key" name="justificatif_text_renouvellement" placeholder="Ancienne licence"> ou
					<input style="width: 50%;" type="file" name="justificatif_file_renouvellement" accept="application/pdf,image/*">
				</div>

				<!-- Option 3 -->
				<label>
					<input type="checkbox" <?php if ($remise_a){ echo 'checked disabled'; }else if($desactiver3){echo 'disabled'; } ?> name="option_remise[]"  id="option3" class="optionRemise" data-group="3" data-file="file3" data-value="Administrations et mairies -30%"> Administrations et mairies -30%
				</label>
				<div class="upload hidden" id="file3">
					<input type="file" name="justificatif_admin" accept="application/pdf,image/*">
				</div>

				<!-- Option 4 -->
				<label>
					<input type="checkbox" <?php if ($remise_e){ echo 'checked disabled'; }else if($desactiver4){echo 'disabled'; } ?> name="option_remise[]"  id="option4" class="optionRemise" data-group="4" data-file="file4" data-value="Établissements scolaires et associations -50%"> Établissements scolaires et associations -50%
				</label>
				<div class="upload hidden" id="file4">
					<input type="file" name="justificatif_association" accept="application/pdf,image/*">
				</div>

				<input type="hidden" name="remise_type" id="remise_type" value="<?php echo $current_remises; ?>">

				<input type="hidden" name="client_id" value="<?php echo esc_attr($selected_client_id); ?>">

			
				<?php if($user_has_enabled_remises  || !($user_has_enabled_remises || $user_has_disabled_remises)){ //si il a des remises activees ou si il n'a aucune remise
				?>
					<button class="btn-remise btn-remise-style"  type="submit" name="submit_demande_remise">Appliquer la remise</button>
					
				<?php }?>

				<?php if($user_has_disabled_remises){ //si il a des remises desactivees lui permettre de les activer
				?>
					<button 
						type="button"
						class=" toggle-remise btn-remise-style"
						data-action="activate">
						Activer les remises
					</button>
				<?php }
				?>

				<?php if($user_has_enabled_remises){ //si il a des remises activees lui permettre de les desactiver
				?>
					<button 
						type="button"
						class="toggle-remise btn-remise-style"
						data-action="deactivate">
						Désactiver les remises
					</button>
				<?php }?>
			</form>
		</div>




		<style>
			.hidden { display:none !important; }
			.block-option { margin-bottom: 12px; }
			label { font-weight: 500; cursor:pointer; }
			.div-remise { 
				background-color: #f5f3f3;
				padding: 15px 28px; 
				display: flex; 
				flex-direction: column;
				gap: 11px;
				max-width: 397px; 
				color:black;
				font-size:16px;
				margin:20px 0px;
			}
			

		</style>

		<script>
		jQuery(document).ready(function($) {
			function apply_reduction(element){
				const $this = element ? $(element) : $('.optionRemise:checked').first();
				const group = $this.length ? parseInt($this.data('group')) : null;
				
				console.log("selected group:",group)

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
			}

			$('.optionRemise').on('change', function () {
				apply_reduction(this);
			});
			apply_reduction(null);


			//  désactiver au départ !
			$(".btn-remise").prop("disabled", true);

			$('.toggle-remise').on('click', function(){

				let actionType = $(this).data('action');

				$.post('<?php echo admin_url('admin-ajax.php'); ?>', {
					action: 'toggle_user_remises',
					mode: actionType
				}, function(response){

					if(response.success){
						location.reload(); // simple
					} else {
						alert('Erreur');
					}

				});

			});

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
			.btn-remise-style {
				font-family:'Raleway';
				font-weight:700;
				margin-top:17px;
				border-style: solid; 
				border-width: 3px 3px 3px 3px; 
				border-radius: 8px 8px 8px 8px; 
				padding: 12px 30px 12px 30px; 
				color: #FFFFFF; 
				background-color: var(--e-global-color-primary); 
				border-color: var(--e-global-color-primary); 
				transition: all 0.2s;
				width: fit-content;
				margin: auto; 
				text-transform: unset;
				margin-block: 10px !important;
			}
			
		</style>
	<?php endif; ?>
<?php endif; ?>


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

	$(document).on('change', 'select.automatic-sent2', function () {

       
        // Déclencher le clic automatiquement
        $('button[name="save_client_cart"]').trigger('click');
    });

	

});
	</script>