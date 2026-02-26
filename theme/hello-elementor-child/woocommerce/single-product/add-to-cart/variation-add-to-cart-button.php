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
$bloquer_remise_commerciale = get_field('bloquer_remise_commerciale', $product->get_id());
$current_remises="";
$user_id = get_current_user_id();

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
}

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

<?php 
    
    if(!$bloquer_remise_commerciale){
?>
    <div class="div-remise">
        <form id="demandeRemise" method="post" enctype="multipart/form-data">
            <span style="font-family: 'Raleway';font-weight: 600;text-align:center;"> JE PEUX BÉNÉFICIER<br> D'UNE REMISE COMMERCIALE :</span>
            <!-- Option 1 -->
            <label>
                <input type="checkbox" <?php if ($remise_c) echo 'checked disabled'; ?>  name="option_remise[]"  id="option1" class="optionRemise" data-group="1" data-file="file1" data-value="Changement -25%"> Je change d'antivirus pour Avast -25%
            </label>
            <div class="upload hidden" id="file1">
                <input type="file" name="justificatif_changement" accept="application/pdf,image/*">
            </div>

            <!-- Option 2 -->
            <label>
                <input type="checkbox" <?php if ($remise_r) echo 'checked disabled'; ?>  name="option_remise[]"  id="option2" class="optionRemise" data-group="2" data-file="file2" data-value="Renouvellement de licences -30%"> Renouvellement de licences -30%
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
                <input type="checkbox" <?php if ($remise_e) echo 'checked disabled'; ?>  name="option_remise[]"  id="option4" class="optionRemise" data-group="4" data-file="file4" data-value="Établissements scolaires et associations -50%"> Établissements scolaires et associations -50%
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
<?php 
    }
?>
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
                $(".remise_initiale").show();
                $(".hide-if-rem-comm").show();
                $(".remisable bdi").css("text-decoration", "none");
                $(".hide-if-rem-comm  .variation-reduction-percentage").show();
                $(".hide-if-rem-comm ins").show();
                $(".hide-if-rem-comm  .promo-end").show();
            }else{
                $(".btn-remise").prop("disabled", false);
                $(".remise_initiale").hide();
                

                let prixFinal = 0

                var hasRemise = $(".pourcentage-remise-depart").filter(function() {
                    return $(this).text().trim() !== "";
                }).length > 0;

                // Vérifier si le prix total contient un texte
                var hasPrix = $(".prix-total2").filter(function() {//prix de base du produit
                    return $(this).text().trim() !== "";
                }).length > 0;

                console.log("Contient remise :", hasRemise);
                console.log("Contient prix :", hasPrix);

                // Exemple de condition
                if (hasRemise && hasPrix) {//reevendeur
					let prixDepart = parseFloat($(".prix-total2").text().replace(',', '.'));
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
				}else{//client normal
					let prixDepart = parseFloat($(".prix-total2").text().replace(',', '.'));
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
                

                $(".remisable bdi").css("text-decoration", "line-through");
                $(".hide-if-rem-comm  .variation-reduction-percentage").hide();
                $(".hide-if-rem-comm  .promo-end").hide();
                $(".hide-if-rem-comm ins").hide();
                $(".prix-remise").show();
            }

        }


        $('.optionRemise').on('change', function() {
            apply_reduction(this);
		});
        $('#pa_software_duration').on('change', function() {
            setTimeout(() => {
                apply_reduction(null);
            }, 2000); 
		});
        $('#pa_number_of_computers').on('change', function() {
		    setTimeout(() => {
                apply_reduction(null);
            }, 2000); 
		});
         setTimeout(() => {
                apply_reduction(null);
            }, 2000); 

        

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
        .prix-remise-depart,.prix-total,.prix-total2,.pourcentage-remise-depart{
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
