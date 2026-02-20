<?php
/**
 * Subscription Product Add to Cart
 *
 * @package WooCommerce-Subscriptions/Templates
 * @version 1.0.0 - Migrated from WooCommerce Subscriptions v2.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Bail if the product isn't purchasable and that's not because it's limited.
if ( ! $product->is_purchasable() && ( ! is_user_logged_in() || 'no' === wcs_get_product_limitation( $product ) ) ) {
	return;
}



$user_id = get_current_user_id();
$bloquer_remise_revendeur = get_field('bloquer_remise_revendeur', $product->get_id());       // true / false
$bloquer_remise_commerciale = get_field('bloquer_remise_commerciale', $product->get_id());







echo wp_kses_post( wc_get_stock_html( $product ) );

$regular_price = $product->get_regular_price();
$sale_price    = $product->get_sale_price();
$current_price = $product->get_price();
$product_id   = $product->get_id();
$product_type = get_field('type', $product_id);
$class_remise_revendeur = null;
$remise_revendeur_txt = null;
$prix_remise_depart = null;
$class_hide_remise_revendeur = 'hide_remise_revendeur';
$pourcentage_remise_revendeur = null;
$current_remises="";
$user_has_disabled_remises = false;
$user_has_enabled_remises = false;

if($user_id){
	$user_has_disabled_remises = has_user_disabled_remises($user_id);
	$user_has_enabled_remises = has_user_enabled_remises($user_id);
	$remise_c = get_user_remise_by_type($user_id,"Changement -25%");
	$remise_r = get_user_remise_by_type($user_id,"Renouvellement de licences -30%");
	$remise_a = get_user_remise_by_type($user_id,"Administrations et mairies -30%");
	$remise_e = get_user_remise_by_type($user_id,"Établissements scolaires et associations -50%");
	
	$current_remises .= ($remise_c)?get_field('type', $remise_c):"";
	$current_remises .= ($remise_r)?get_field('type', $remise_r):"";
	$current_remises .= ($remise_a)?get_field('type', $remise_a):"";
	$current_remises .= ($remise_e)?get_field('type', $remise_e):"";

	$remise = get_revendeur_remise($user_id);
	if (!empty($remise) && !$bloquer_remise_revendeur){
		$percent = (float) get_field('pourcentage', $remise->ID);

		$class_remise_revendeur = "has-remise-revendeur";
		$pourcentage_remise_revendeur = $percent;
		$remise_revendeur_txt = "Remise revendeur - ".$percent." %";
		$prix_base = $product->is_on_sale() ? $regular_price : $regular_price;//remise toujours sur prix de base
		$prix_remise_revendeur = $prix_base - ($prix_base * $percent / 100);
		$prix_remise_revendeur = round($prix_remise_revendeur, 2);
		$prix_remise_depart = $prix_remise_revendeur;
		$class_hide_remise_revendeur = '';

	}else{
		$class_hide_remise_revendeur = 'hide_remise_revendeur';
		$prix_base_promo =  $regular_price;//toujour sur le prix regulier
		$prix_remise_revendeur = $prix_base_promo;
		$prix_remise_revendeur = round($prix_remise_revendeur, 2);
		$prix_remise_depart = $prix_remise_revendeur;
	}
}



// Champs ACF
$show_pcs     = get_field('afficher_selecteur_pcs', $product_id);
$max_pcs      = (int) get_field('valeur_maximale_pcs', $product_id);

$show_years   = get_field('afficher_selecteur_annees', $product_id);
$years        = get_field('annees', $product_id);

if ( $product->is_in_stock() ) : ?>

	<?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>

	<?php if ( ! $product->is_purchasable() && 0 !== $user_id && 'no' !== wcs_get_product_limitation( $product ) && wcs_is_product_limited_for_user( $product, $user_id ) ) : ?>
		<?php
		if ( 'any' === wcs_get_product_limitation( $product )
			&& wcs_user_has_subscription( $user_id, $product->get_id() )
			&& ! wcs_user_has_subscription( $user_id, $product->get_id(), 'active' )
			&& ! wcs_user_has_subscription( $user_id, $product->get_id(), 'on-hold' )
		) :
			?>
			<?php
			$reactivate_link  = wcs_get_user_reactivate_link_for_product( $user_id, $product );
			$resubscribe_link = wcs_get_users_resubscribe_link_for_product( $product->get_id() );
			?>
			<?php if ( ! empty( $reactivate_link ) ) : // customer has a pending cancellation subscription, maybe offer the reactivate button. ?>
				<a href="<?php echo esc_url( $reactivate_link ); ?>" class="button product-resubscribe-link<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>"><?php esc_html_e( 'Reactivate', 'woocommerce-subscriptions' ); ?></a>
			<?php elseif ( ! empty( $resubscribe_link ) ) : // customer has an inactive subscription, maybe offer the renewal button. ?>
				<a href="<?php echo esc_url( $resubscribe_link ); ?>" class="button product-resubscribe-link<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>"><?php esc_html_e( 'Resubscribe', 'woocommerce-subscriptions' ); ?></a>
			<?php endif; ?>
		<?php else : ?>
			<p class="limited-subscription-notice notice"><?php esc_html_e( 'You have an active subscription to this product already.', 'woocommerce-subscriptions' ); ?></p>
		<?php endif; ?>
	<?php else : ?>


	<?php if ( $product_type=="variable" ) : ?>
		<div >
			<?php if ( $show_years && ! empty($years) && is_array($years) ) : ?>
				<div class="duree-annee" style="display: flex;gap: 8px;justify-content: flex-end;margin-bottom:20px" >
					<label for="duree">Durée : </label>
					<select name="duree" id="duree" required>
						<?php foreach ( $years as $year ) : ?>
							<option value="<?php echo esc_attr($year); ?>">
								<?php echo esc_html($year); ?> 
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			<?php endif; ?>


			<?php if ( $show_pcs && $max_pcs > 0 ) : ?>
				<div class="duree-annee" style="display: flex;gap: 8px;justify-content: flex-end;">
					<label for="nb_pc">Nbre de postes(s) : </label>
					<select name="nb_pc" id="nb_pc" required>
						<?php for ( $i = 1; $i <= $max_pcs; $i++ ) : ?>
							<option value="<?php echo esc_attr($i); ?>">
								<?php echo esc_html($i); ?> 
							</option>
						<?php endfor; ?>
					</select>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

		
	<?php if ( $sale_price > 0 && $regular_price > 0 && $sale_price < $regular_price ) : ?>
		<div class="woocommerce-variation-price">
			<span class="price">
				<del aria-hidden="true">
					<span class="woocommerce-Price-amount amount">
						<bdi><?php echo wc_price( $regular_price ); ?></bdi>
					</span>
				</del>
				<ins aria-hidden="true" class="hide-if-rem-comm <?php echo $class_remise_revendeur;?>">
					<span class="woocommerce-Price-amount amount remisable  <?php echo $class_remise_revendeur;?>">
						<bdi><?php echo wc_price( $sale_price ); ?></bdi>
						<?php 
							$reduction = (($regular_price - $sale_price) / $regular_price) * 100;
							// arrondi à l'entier
							$reduction = round($reduction);

						echo "<span class='reduction-percentage'>- ".$reduction." %</span>"; ?>
					</span>
				</ins>
				<?php
				 $date = $product->get_date_on_sale_to();

				if ($date) {
					echo '<p class="promo-end '.$class_remise_revendeur.'">';
					echo 'Promotion valable jusqu’au <strong>' . wc_format_datetime($date) . '</strong>';
					echo '</p>';
				}

				echo '<span class="variation-reduction-percentage has-remise-revendeur'.$class_hide_remise_revendeur.'">';
				echo $remise_revendeur_txt;
				echo '</span>';

				?>

				<ins aria-hidden="true" class="<?php echo $class_hide_remise_revendeur;?>">
					<span class="woocommerce-Price-amount amount remisable ">
						<bdi><?php echo wc_price( $prix_remise_revendeur ); ?></bdi>
					</span>
				</ins>
				
				<?php if (!empty($remise) && !$bloquer_remise_revendeur){?>
					<span class="pourcentage-remise-depart"><?php echo $pourcentage_remise_revendeur;?></span>
					<span class="prix-total"><?php echo $prix_base;?></span>
				<?php }?>
				<span class="prix-remise-depart"><?php echo $prix_remise_revendeur;?></span>
				<span class="prix-remise">prix remisé</span>

				
			</span>
		</div>
	<?php endif; ?>
	<?php if ( $regular_price > 0 && ( ! $sale_price || $sale_price >= $regular_price ) ) : ?>
		<div class="woocommerce-variation-price ">
			<span class="price">
				<ins aria-hidden="true">
					<span class="woocommerce-Price-amount amount remisable <?php //echo $class_remise_revendeur;?>">
						<bdi><?php echo wc_price( $regular_price ); ?></bdi>
					</span>
				</ins>

				
			</span>
		</div>

		<span class="variation-reduction-percentage has-remise-revendeur <?php echo $class_hide_remise_revendeur;?>">
			<?php echo $remise_revendeur_txt;?>
		</span>

		<div class="woocommerce-variation-price ">
			<span class="price">
				<ins aria-hidden="true" class="<?php echo $class_hide_remise_revendeur;?>" >
					<span class="woocommerce-Price-amount amount remisable ">
						<bdi><?php echo wc_price( $prix_remise_revendeur ); ?></bdi>
					</span>
				</ins>

				<?php if (!empty($remise) && !$bloquer_remise_revendeur){?>
					<span class="pourcentage-remise-depart"><?php echo $pourcentage_remise_revendeur;?></span>
					<span class="prix-total"><?php echo $prix_base;?></span>
				<?php }?>

				<span class="prix-remise-depart"><?php echo $prix_remise_revendeur;?></span>
				<span class="prix-remise">prix remisé</span>
			</span>
		</div>

	<?php endif; ?>
	<?php if ( ! $regular_price && ! $sale_price ) : ?>
		<div class="woocommerce-variation-price">
			<span class="price">Prix sur devis</span>
		</div>
	<?php endif; ?>





	<form class="cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data'>

		<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

		<?php
		do_action( 'woocommerce_before_add_to_cart_quantity' );

		woocommerce_quantity_input(
			[
				'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
				'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
				'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), // phpcs:ignore WordPress.Security.NonceVerification.Missing -- input var ok.
			]
		);

		do_action( 'woocommerce_after_add_to_cart_quantity' );
		?>


		<button type="submit" style="margin-block:20px!important;" class="single_add_to_cart_button button alt<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>

		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>

	</form>
	<?php endif; ?>

	<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>

<?php endif; ?>


<?php 
    
    if(!$bloquer_remise_commerciale){
?>
	<div class="div-remise">
		<form id="demandeRemise" method="post" enctype="multipart/form-data">
			<span style="font-family: 'Raleway';font-weight: 600;text-align:center;"> JE PEUX BÉNÉFICIER<br> D'UNE REMISE COMMERCIALE :</span>
			<!-- Option 1 -->
			<label>
				<input type="checkbox" <?php if ($remise_c) echo 'checked disabled'; ?> name="option_remise[]"  id="option1" class="optionRemise" data-group="1" data-file="file1" data-value="Changement -25%"> Je change d'antivirus pour Avast -25%
			</label>
			<div class="upload hidden" id="file1">
				<input type="file" name="justificatif_changement" accept="application/pdf,image/*">
			</div>

			<!-- Option 2 -->
			<label>
				<input type="checkbox" <?php if ($remise_r) echo 'checked disabled'; ?> name="option_remise[]"  id="option2" class="optionRemise" data-group="2" data-file="file2" data-value="Renouvellement de licences -30%"> Renouvellement de licences -30%
			</label>
			
			<div class="upload hidden" id="file2" style="display: flex;gap: 7px; align-items: center;justify-content: center;">
				<input  style="width: 50%;padding: 0.5rem 0.4rem; height: 30px;" type="text" id="old_key" name="justificatif_text_renouvellement" placeholder="Ancienne licence"> ou
				<input style="width: 50%;" type="file" name="justificatif_file_renouvellement" accept="application/pdf,image/*">
			</div>

			<!-- Option 3 -->
			<label>
				<input type="checkbox" <?php if ($remise_a) echo 'checked disabled'; ?> name="option_remise[]"  id="option3" class="optionRemise" data-group="3" data-file="file3" data-value="Administrations et mairies -30%"> Administrations et mairies -30%
			</label>
			<div class="upload hidden" id="file3">
				<input type="file" name="justificatif_admin" accept="application/pdf,image/*">
			</div>

			<!-- Option 4 -->
			<label>
				<input type="checkbox" <?php if ($remise_e) echo 'checked disabled'; ?> name="option_remise[]"  id="option4" class="optionRemise" data-group="4" data-file="file4" data-value="Établissements scolaires et associations -50%"> Établissements scolaires et associations -50%
			</label>
			<div class="upload hidden" id="file4">
				<input type="file" name="justificatif_association" accept="application/pdf,image/*">
			</div>

			<input type="hidden" name="remise_type" id="remise_type" value="<?php echo $current_remises; ?>">

			<?php if($user_has_enabled_remises  || !($user_has_enabled_remises || $user_has_disabled_remises)){ //si il a des remises activees ou si il n'a aucune remise
			?>
			<button class="btn-remise btn-remise-style"  type="submit" name="submit_demande_remise">Appliquer ma remise</button>
			<?php }
			?>

			<?php if($user_has_disabled_remises){ //si il a des remises desactivees lui permettre de les activer
			?>
				<button 
					type="button"
					class=" toggle-remise btn-remise-style"
					data-action="activate">
					Activer mes remises
				</button>
			<?php }
			?>

			<?php if($user_has_enabled_remises){ //si il a des remises activees lui permettre de les desactiver
			?>
				<button 
					type="button"
					class="toggle-remise btn-remise-style"
					data-action="deactivate">
					Désactiver mes remises
				</button>
			<?php }
			?>


		</form>
	</div>
<?php } ?>
<style>
    .hidden { display:none !important; }
    .block-option { margin-bottom: 12px; }
    label { font-weight: 500; cursor:pointer; }
</style>
<?php 
    if(!$bloquer_remise_commerciale){
?>
	<script>
	jQuery(document).ready(function($) {

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


		function  apply_reduction(element){
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
				$(".prix-remise").hide();
				$(".promo-end").show();
				$(".hide-if-rem-comm").show();
				$(".remisable bdi").css("text-decoration", "none");
			}else{
				$(".btn-remise").prop("disabled", false);

				
				let prixFinal = 0

				 var hasRemise = $(".pourcentage-remise-depart").filter(function() {
                    return $(this).text().trim() !== "";
                }).length > 0;

                // Vérifier si le prix total contient un texte
                var hasPrix = $(".prix-total").filter(function() {//prix de base du produit
                    return $(this).text().trim() !== "";
                }).length > 0;

                console.log("Contient remise :", hasRemise);
                console.log("Contient prix :", hasPrix);

                // Exemple de condition
                if (hasRemise && hasPrix) {
					let prixDepart = parseFloat($(".prix-total").text().replace(',', '.'));
					let pourcentageRemise = parseFloat($(".pourcentage-remise-depart").text().replace(',', '.'));
					let prixRemiseRevendeur =  (prixDepart * pourcentageRemise / 100); //conserver prixDepart - prixRemiseRevendeur et cummulee a partir de la
					// Appliquer la réduction
					 let prixApresRR = prixDepart - prixRemiseRevendeur;
                     let prixActuel = prixApresRR;
					 console.log("prixDepart",prixDepart)
					 console.log("pourcentageRemiseR",pourcentageRemise)
					 console.log("prixRemiseRevendeur",prixRemiseRevendeur)
					 console.log("prixApresRR",prixApresRR)
                      values.forEach(v => {
                        const match = v.match(/-([0-9]+)%/);
                        if (match) {
                            const pourcentage = parseInt(match[1], 10);
                            const remise = prixActuel * pourcentage / 100;
                            prixActuel = prixActuel - remise;
                            
                            console.log(`  ${v}: -${pourcentage}% = -${remise.toFixed(2)} EUR → ${prixActuel.toFixed(2)} EUR`);
                        }
                    });

                    prixFinal = prixActuel;
                    console.log(`💰 Prix final: ${prixFinal.toFixed(2)} EUR`);
				}else{
					let prixDepart = parseFloat($(".prix-remise-depart").text().replace(',', '.'));
					let prixActuel = prixDepart;

                    console.log(`Prix départ: ${prixActuel.toFixed(2)} EUR`);

                    values.forEach(v => {
                        const match = v.match(/-([0-9]+)%/);
                        if (match) {
                            const pourcentage = parseInt(match[1], 10);
                            const remise = prixActuel * pourcentage / 100;
                            prixActuel = prixActuel - remise;
                            
                            console.log(`  ${v}: -${pourcentage}% = -${remise.toFixed(2)} EUR → ${prixActuel.toFixed(2)} EUR`);
                        }
                    });

                    prixFinal = prixActuel;
                    console.log(`💰 Prix final: ${prixFinal.toFixed(2)} EUR`);
				}
				
				// Sécurité (pas négatif)
				prixFinal = Math.max(0, prixFinal);
				// Arrondi (à l’entier ou 2 décimales selon ton besoin)
				prixFinal = (prixFinal.toFixed(2)); // ou toFixed(2)
				// Affichage
				$(".prix-remise").text(prixFinal+" €");
				
				$(".promo-end").hide();

				$(".remisable bdi").css("text-decoration", "line-through");

				$(".prix-remise").show();
				
				$(".hide-if-rem-comm").hide();
			}
		}

		$('.optionRemise').on('change', function () {
			apply_reduction(this);
		});
		apply_reduction(null);

		

		//  désactiver au départ !
		$(".btn-remise").prop("disabled", true);

	});
	</script>
<?php 
    }
?>
<style>
    .btn-remise:disabled {
		background-color: #999 !important;
		border-color: #777 !important;
		cursor: not-allowed;
		opacity: 0.5;
		margin-block: 10px !important;
	}
	.prix-remise-depart,.prix-total,.pourcentage-remise-depart{
		display:none;
	}
	.prix-remise{
		display:none;
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