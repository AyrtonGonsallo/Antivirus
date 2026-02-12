<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if ( !function_exists( 'chld_thm_cfg_locale_css' ) ):
    function chld_thm_cfg_locale_css( $uri ){
        if ( empty( $uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter( 'locale_stylesheet_uri', 'chld_thm_cfg_locale_css' );
         
if ( !function_exists( 'child_theme_configurator_css' ) ):
    function child_theme_configurator_css() {
        wp_enqueue_style( 'chld_thm_cfg_child', trailingslashit( get_stylesheet_directory_uri() ) . 'style.css', array( 'hello-elementor','hello-elementor','hello-elementor-theme-style','hello-elementor-header-footer' ) );
    }
endif;
add_action( 'wp_enqueue_scripts', 'child_theme_configurator_css', 10 );

function load_fontawesome_icons() {
    wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css' );
}
add_action( 'wp_enqueue_scripts', 'load_fontawesome_icons' ); 


function mon_theme_enqueue_scripts() {
    // Charger jQuery inclus avec WordPress
    wp_enqueue_script('jquery');
}
add_action('wp_enqueue_scripts', 'mon_theme_enqueue_scripts');


// END ENQUEUE PARENT ACTION
function theme_enfant_enqueue_scripts() {
    // Charger ton JS
    wp_enqueue_script(
        'theme-enfant-script',
        get_stylesheet_directory_uri() . '/js/scripty.js',
        array(), // dépendances, ex: array('jquery')
        null,
        true // true = charge dans le footer
    );
}
add_action('wp_enqueue_scripts', 'theme_enfant_enqueue_scripts');
/*
// === Custom Post Type : FAQ ===
function create_faq_post_type() {
    $labels = array(
        'name'               => 'FAQ',
        'singular_name'      => 'Question',
        'menu_name'          => 'FAQ',
        'name_admin_bar'     => 'FAQ',
        'add_new'            => 'Ajouter une question',
        'add_new_item'       => 'Ajouter une nouvelle question',
        'new_item'           => 'Nouvelle question',
        'edit_item'          => 'Modifier la question',
        'view_item'          => 'Voir la question',
        'all_items'          => 'Toutes les questions',
        'search_items'       => 'Rechercher une question',
        'not_found'          => 'Aucune question trouvée.',
        'not_found_in_trash' => 'Aucune question trouvée dans la corbeille.'
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'faq'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'posts_per_page' => 21,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-editor-help',
        'supports'           => array('title', 'editor', 'excerpt', 'thumbnail'),
        'show_in_rest'       => true, // pour qu'il apparaisse dans Elementor
    );

    register_post_type('faq', $args);
}
add_action('init', 'create_faq_post_type');

function pre_handle_404($preempt, $wp_query)
{
    if (isset($wp_query->query['page']) && $wp_query->query['page']) {
        return true;
    }

    return $preempt;
}
add_filter( 'pre_handle_404', 'pre_handle_404', 10, 2 );

*/


// Tableau de langues exemple : ['en_GB', 'fr_FR', 'es_ES']
// Le lien doit être le site traduit, généralement généré par TranslatePress

// --- Shortcode : drapeaux côte à côte avec liens corrigés ---
function trp_flags_inline_shortcode($atts) {

    // Ordre demandé : France, USA, Spain, Italy, Portugal, Germany
    $default_langs = 'fr_FR,en_US,es_ES,it_IT,pt_PT,de_DE';

    $atts = shortcode_atts([
        'langs' => $default_langs,
    ], $atts, 'trp_flags_inline');

    $langs = array_map('trim', explode(',', $atts['langs']));

    // Vérifier TranslatePress
    if ( ! class_exists('TRP_Translate_Press') ) return '';

    $trp = TRP_Translate_Press::get_trp_instance();
    $url_converter = $trp->get_component('url_converter');

    // URL de la page actuelle
    $current_url = $url_converter->cur_page_url();

    $html = '<div class="trp-flags-inline" style="display:flex; gap:6px; align-items:center;">';

    foreach ($langs as $lang) {

        // Extrait le code simple : fr_FR → fr
        $code = substr($lang, 0, 2);

        // 🔥 Correction : si FR → URL racine "/"
        if ( $code === 'fr' ) {
            $translated_url = home_url('/');
        } else {
            $translated_url = $url_converter->get_url_for_language($code, $current_url);
        }

        // Flag TranslatePress
        $flag_url = plugins_url("translatepress-multilingual/assets/flags/4x3/{$lang}.svg");

        $html .= '<a class="trp-language-item" href="' . esc_url($translated_url) . '" role="option" tabindex="-1">';
        $html .= '<img src="' . esc_url($flag_url) . '" class="trp-flag-image" alt="' . esc_attr($lang) . '" loading="lazy" style="width:22px; height:auto;">';
        $html .= '</a>';
    }

    $html .= '</div>';

    return $html;
}

add_shortcode('trp_flags_inline', 'trp_flags_inline_shortcode');




// --- Shortcode 2 : icône avec hover affichant les liens ---
function trp_flags_hover_shortcode($atts) {
    $atts = shortcode_atts([
        'langs' => 'fr_FR,en_US,es_ES,it_IT,pt_PT,de_DE',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512" width="18" height="18" fill="#000"><path d="M336.5 160C322 70.7 287.8 8 248 8s-74 62.7-88.5 152h177zM152 256c0 22.2 1.2 43.5 3.3 64h185.3c2.1-20.5 3.3-41.8 3.3-64s-1.2-43.5-3.3-64H155.3c-2.1 20.5-3.3 41.8-3.3 64zm324.7-96c-28.6-67.9-86.5-120.4-158-141.6 24.4 33.8 41.2 84.7 50 141.6h108zM177.2 18.4C105.8 39.6 47.8 92.1 19.3 160h108c8.7-56.9 25.5-107.8 49.9-141.6zM487.4 192H372.7c2.1 21 3.3 42.5 3.3 64s-1.2 43-3.3 64h114.6c5.5-20.5 8.6-41.8 8.6-64s-3.1-43.5-8.5-64zM120 256c0-21.5 1.2-43 3.3-64H8.6C3.2 212.5 0 233.8 0 256s3.2 43.5 8.6 64h114.6c-2-21-3.2-42.5-3.2-64zm39.5 96c14.5 89.3 48.7 152 88.5 152s74-62.7 88.5-152h-177zm159.3 141.6c71.4-21.2 129.4-73.7 158-141.6h-108c-8.8 56.9-25.6 107.8-50 141.6zM19.3 352c28.6 67.9 86.5 120.4 158 141.6-24.4-33.8-41.2-84.7-50-141.6h-108z"/></svg>'
    ], $atts, 'trp_flags_hover');

    $langs = array_map('trim', explode(',', $atts['langs']));
    
    $html = '<div class="trp-flags-hover" style="position: relative; display: inline-block;">';
    $html .= '<span class="trp-hover-icon" style="cursor:pointer;">' . $atts['icon'] . '</span>';
    $html .= '<div class="trp-hover-menu222" style="display:none; position:absolute;width:50px; top:100%; left:0; background:#fff; border:1px solid #ccc; padding:5px; z-index:999;">';

    foreach ($langs as $lang) {

        if ( ! class_exists('TRP_Translate_Press') ) return '';
        $trp = TRP_Translate_Press::get_trp_instance();
        $url_converter = $trp->get_component('url_converter');

        $current_url = $url_converter->cur_page_url();

        // Extrait le code simple
        $code = substr($lang, 0, 2);

        // 🔥 Correction : langue FR renvoie "/"
        if ( $code === 'fr' ) {
            $translated_url = home_url('/');
        } else {
            $translated_url = $url_converter->get_url_for_language($code, $current_url);
        }

        // Flag TranslatePress
        $flag_url = plugins_url("translatepress-multilingual/assets/flags/4x3/{$lang}.svg");

        $html .= '<a class="trp-language-item" href="' . esc_url($translated_url) . '" role="option" tabindex="-1" style="display:block; margin:2px 0;">';
        $html .= '<img src="' . esc_url($flag_url) . '" alt="' . esc_attr($lang) . '">';
        $html .= '</a>';
    }

    $html .= '</div></div>';

// JS toggle
$html .= "<script>
jQuery(document).ready(function($){
    $('.trp-flags-hover').each(function(){
        var \$container = $(this);
        var \$menu = \$container.find('.trp-hover-menu222');
        var \$icon = \$container.find('.trp-hover-icon');

        // Ouvrir le menu au clic sur l'icône
        \$icon.on('click', function(e){
            e.stopPropagation();
            \$menu.css('display', 'block');
        });

        // Fermer quand la souris sort du sous-menu
        \$menu.on('mouseleave', function(){
            \$menu.hide();
        });

        // Fermer en cliquant ailleurs
        $(document).on('click', function(e){
            if (!\$container.is(e.target) && \$container.has(e.target).length === 0) {
                \$menu.hide();
            }
        });
    });
});
</script>";


    return $html;
}
add_shortcode('trp_flags_hover', 'trp_flags_hover_shortcode');



function shortcode_upsell_produit() {
    if (!is_product()) return '';

    global $product;
    $upsell_ids = $product->get_upsell_ids();

    // si vide → ne rien afficher
    if (empty($upsell_ids)) return '';

    ob_start();
    ?>

                <div class="elementor-element elementor-element-e4246f2 e-flex e-con-boxed e-con e-parent e-lazyloaded" data-id="e4246f2" data-element_type="container">
					<div class="e-con-inner">
		<div class="elementor-element elementor-element-6ff704f e-con-full e-flex e-con e-child" data-id="6ff704f" data-element_type="container">
				<div class="elementor-element elementor-element-9939b02 elementor-widget elementor-widget-heading" data-id="9939b02" data-element_type="widget" data-widget_type="heading.default">
					<h2 class="elementor-heading-title elementor-size-default">Produits suggérés</h2>				</div>
				<div class="elementor-element elementor-element-25ec2da elementor-widget-divider--view-line elementor-widget elementor-widget-divider" data-id="25ec2da" data-element_type="widget" data-widget_type="divider.default">
							<div class="elementor-divider">
			<span class="elementor-divider-separator">
						</span>
		</div>
						</div>
				</div>
				<div class="elementor-element elementor-element-8da8c26 elementor-grid-mobile-1 elementor-product-loop-item--align-center elementor-widget-mobile__width-inherit elementor-product-loop-item--align-center prod-shop elementor-grid-4 elementor-grid-tablet-3 elementor-products-grid elementor-wc-products elementor-widget elementor-widget-woocommerce-product-related" data-id="8da8c26" data-element_type="widget" data-widget_type="woocommerce-product-related.default">
					
	            <section class="related products">

					
                    <ul class="products elementor-grid columns-4">
                    <?php 
                    $old_global_product = $product;

                    foreach ($upsell_ids as $id):

                        $p = wc_get_product($id);
                        
                        if (!$p) continue;

                        // --- SI PRODUIT VARIABLE : récupérer ses variations enfants ---
                        if ($p->is_type('variable')) {

                            $children = $p->get_children(); // IDs des variations

                            foreach ($children as $child_id) {
                                $child = wc_get_product($child_id);
                                if (!$child || !$child->is_type('variation')) continue;

                                global $product;
                                $product = $child; // obligatoire pour le bouton add-to-cart
                                ?>
                                
                                <li class="product">
                                    <a href="<?php echo get_permalink($child_id); ?>">
                                        <?php echo $child->get_image('medium_large'); ?>
                                        <h3 style="font-size: 22px;font-weight: 700;"><?php echo $child->get_name(); ?></h3>
                                        <span class="price"><?php echo $child->get_price_html(); ?></span>
                                    </a>
                                    <?php woocommerce_template_loop_add_to_cart(); ?>
                                </li>

                                <?php
                            }
                            continue;
                        }

                        // --- SI PRODUIT FILS DIRECT ---
                        if ($p->is_type('variation') || $p->is_type('subscription')) {
                            global $product;
                            $product = $p;
                            ?>

                            <li class="product">
                                <a href="<?php echo get_permalink($id); ?>">
                                    <?php echo $p->get_image('medium_large'); ?>
                                    <h3 style="font-size: 22px;font-weight: 700;"><?php echo $p->get_name(); ?></h3>
                                    <span class="price"><?php echo $p->get_price_html(); ?></span>
                                </a>
                                <?php woocommerce_template_loop_add_to_cart(); ?>
                            </li>

                            <?php
                        }

                    endforeach;

                    $product = $old_global_product;
                    ?>
                    </ul>


	            </section>
					</div>
					</div>
				</div>

    <?php
    return ob_get_clean();
}
add_shortcode('upsell_produit', 'shortcode_upsell_produit');


function shortcode_cross_sell() {
    if (!is_product()) return '';  // évite pollution hors page produit

    global $product;
    $cross_sell_ids = $product->get_cross_sell_ids();

    // ❗ si vide → ne rien afficher
    if (empty($cross_sell_ids)) return '';
   // var_dump($cross_sell_ids);
    ob_start();
    ?>







<div class="elementor-element elementor-element-e4246f2 e-flex e-con-boxed e-con e-parent e-lazyloaded" data-id="e4246f2" data-element_type="container">
					<div class="e-con-inner">
		<div class="elementor-element elementor-element-6ff704f e-con-full e-flex e-con e-child" data-id="6ff704f" data-element_type="container">
				<div class="elementor-element elementor-element-9939b02 elementor-widget elementor-widget-heading" data-id="9939b02" data-element_type="widget" data-widget_type="heading.default">
					<h2 class="elementor-heading-title elementor-size-default">Ventes croisées</h2>				</div>
				<div class="elementor-element elementor-element-25ec2da elementor-widget-divider--view-line elementor-widget elementor-widget-divider" data-id="25ec2da" data-element_type="widget" data-widget_type="divider.default">
							<div class="elementor-divider">
			<span class="elementor-divider-separator">
						</span>
		</div>
						</div>
				</div>
				<div class="elementor-element elementor-element-8da8c26 elementor-grid-mobile-1 elementor-product-loop-item--align-center elementor-widget-mobile__width-inherit elementor-product-loop-item--align-center prod-shop elementor-grid-4 elementor-grid-tablet-3 elementor-products-grid elementor-wc-products elementor-widget elementor-widget-woocommerce-product-related" data-id="8da8c26" data-element_type="widget" data-widget_type="woocommerce-product-related.default">
					
	<section class="related products">

					
			<ul class="products elementor-grid columns-4">

			    <?php 
                $old_global_product = $product;

                foreach ($cross_sell_ids as $id):

                    $p = wc_get_product($id);
                    if (!$p) continue;

                    // 1) SI PRODUIT VARIABLE → récupérer tous ses fils
                    if ($p->is_type('variable')) {
                        $child_ids = $p->get_children(); // toutes les variations (IDs)

                        foreach ($child_ids as $child_id) {
                            $child = wc_get_product($child_id);
                            if (!$child || !$child->is_type('variation')) continue;

                            global $product;
                            $product = $child;
                            ?>
                            <li class="product type-product">
                                <a href="<?php echo get_permalink($child_id); ?>">
                                    <?php echo $child->get_image('medium_large'); ?>
                                    <h3 style="font-size: 22px;font-weight: 700;"><?php echo $child->get_name(); ?></h3>
                                    <span class="price"><?php echo $child->get_price_html(); ?></span>
                                </a>
                                <?php woocommerce_template_loop_add_to_cart(['product' => $child]); ?>
                            </li>
                            <?php
                        }
                        continue; // évite d’afficher aussi le parent
                    }

                    // 2) SI PRODUIT FILS DIRECT → l’afficher
                    if ($p->is_type('variation') || $p->is_type('subscription')) {
                        global $product;
                        $product = $p;
                        ?>
                        <li class="product type-product">
                            <a href="<?php echo get_permalink($id); ?>">
                                <?php echo $p->get_image('medium_large'); ?>
                                <h3 style="font-size: 22px;font-weight: 700;"><?php echo $p->get_name(); ?></h3>
                                <span class="price"><?php echo $p->get_price_html(); ?></span>
                            </a>
                            <?php woocommerce_template_loop_add_to_cart(['product' => $p]); ?>
                        </li>
                        <?php
                        continue;
                    }

                    // Sinon (simple product) → ignorer
                endforeach;

                $product = $old_global_product;
                ?>

				
			
		</ul>

	</section>
					</div>
					</div>
				</div>


                   
                   


    
    
    <?php
    return ob_get_clean();
}
add_shortcode('cross_sell', 'shortcode_cross_sell');

/*



add_filter( 'wp_calculate_image_srcset', '__return_false' );



add_action('wp_footer', function () {
?>
<script>
jQuery(function($){

    function replaceWithOriginalSize(){

        // Toutes les images WooCommerce dans panier + checkout + widget
        const selectors = `
            .woocommerce-cart img,
            .woocommerce-checkout img,
            .wc-block-components-order-summary-item__image img,
            .widget_shopping_cart img,
            .shop_table img
        `;

        $(selectors).each(function(){

            let img = $(this);
            let src = img.attr('src');

            if (!src) return;

            // Supprime -100x100, -300x300, etc.
            let cleanSrc = src.replace(/-\d+x\d+(?=\.(jpg|jpeg|png|webp))/i, '');

            // Si l’URL change → remplace l'image
            if (cleanSrc !== src) {
                img.attr('src', cleanSrc);
            }

            // Applique le style uniformisé et propre
            img.css({
                'width': '48px',
                'height': '48px',
                'object-fit': 'contain',
                'display': 'block'
            });
        });
    }

    // Exécution initiale
    setTimeout(replaceWithOriginalSize, 300);
    setTimeout(replaceWithOriginalSize, 800);

    // Re-exécuter après AJAX WooCommerce (changement qty, remove item…)
    $(document).on('updated_wc_div updated_cart_totals updated_cart_widget', function () {
        replaceWithOriginalSize();
    });

    // MutationObserver pour les blocs Checkout (WooCommerce Blocks)
    const observer = new MutationObserver(() => replaceWithOriginalSize());
    observer.observe(document.body, { childList: true, subtree: true });

});
</script>
<?php
});


add_action('admin_enqueue_scripts', 'ae_enqueue_admin_js_for_specific_post');
function ae_enqueue_admin_js_for_specific_post($hook) {

    // Charger uniquement sur la page d’édition (pas de création)
    if ($hook !== 'post.php') {
        return;
    }

    // Sécuriser la présence du paramètre ?post=
    if (empty($_GET['post'])) {
        return;
    }

    $post_id = intval($_GET['post']);
    
    $post_type = get_post_type($post_id);
    if ($post_type !== 'devis-en-ligne') {
        return;
    }
  

    // Charger ton fichier JS du thème enfant
    wp_enqueue_script(
        'ae-custom-admin-js',
        get_stylesheet_directory_uri() . '/js/custom-admin.js',
        ['jquery'],
        false,
        true
    );
}


add_filter( 'woocommerce_product_data_store_cpt_get_products_query', 'handle_custom_product_meta_query', 10, 3 );

function handle_custom_product_meta_query( $wp_query_args, $query_vars, $data_store_cpt ) {
    if ( ! empty( $query_vars['meta_query'] ) ) {
        $wp_query_args['meta_query'] = $query_vars['meta_query'];
    }
    return $wp_query_args;
}



*/

function get_revendeur_remise($user_id) {
    $today = current_time('Y-m-d H:i:s');

    $args = [
        'post_type'      => 'remise',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'meta_query'     => [
            [
                'key'   => 'utilisateur',
                'value' => $user_id,
            ],
            [
                'key'   => 'statut',
                'value' => 'validee',
            ],
            [
                'key'   => 'type',
                'value' => 'revendeur - 25 %',
            ],
            [
                'key'     => 'date_dexpiration',
                'value'   => $today,
                'compare' => '>=',
                'type'    => 'DATETIME',
            ],
        ],
    ];

    $posts = get_posts($args);
    return $posts[0] ?? null;
}


function get_user_remise_by_type($user_id,$type) {
    $today = current_time('Y-m-d H:i:s');

    $args = [
        'post_type'      => 'remise',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'meta_query'     => [
            [
                'key'   => 'utilisateur',
                'value' => $user_id,
            ],
            [
                'key'   => 'statut',
                'value' => 'validee',
            ],
            [
                'key'   => 'type',
                'value' => $type,
            ],
            [
                'key'     => 'date_dexpiration',
                'value'   => $today,
                'compare' => '>=',
                'type'    => 'DATETIME',
            ],
        ],
    ];

    $posts = get_posts($args);
    return $posts[0] ?? null;
}

add_filter('woocommerce_available_variation', function ($variation_data, $product, $variation) {

    $user_id = get_current_user_id();
    $bloquer_remise_revendeur = get_field('bloquer_remise_revendeur', $product->get_id());       // true / false
    $bloquer_remise_commerciale = get_field('bloquer_remise_commerciale', $product->get_id());

    if ($variation->is_on_sale()) {

        $regular = (float) $variation->get_regular_price();
        $sale    = (float) $variation->get_sale_price();

        if ($regular > 0 && $sale > 0 && $sale < $regular) {
            $percent = round((($regular - $sale) / $regular) * 100);

            

        $variation_data['discount_percent'] =  "<span class='variation-reduction-percentage'>- ".$percent." %</span>"; 
        }

        $date = $variation->get_date_on_sale_to();

        if ($date) {

            $res_string = '<p class="promo-end">';
            $res_string .= 'Promotion valable jusqu’au <strong>' . wc_format_datetime($date) . '</strong>';
            $res_string .= '</p>';

            $variation_data['sale_end_date'] = $res_string;
        } else {
            $variation_data['sale_end_date'] = '';
        }

        $variation_data['prix_remise_depart'] = $regular;//toujour sur le prix regulier

    }else{
        $regular = (float) $variation->get_regular_price();
        $variation_data['prix_remise_depart'] = $regular;
    }
    $remise = get_revendeur_remise($user_id);
    if (!empty($remise) && !$bloquer_remise_revendeur){
        $percent = (float) get_field('pourcentage', $remise->ID);

        $variation_data['class_remise_revendeur'] = "has-remise-revendeur";
        $variation_data['pourcentage_remise_revendeur'] = $percent;
        $variation_data['remise_revendeur_txt'] = "Remise revendeur - ".$percent." %";
        $prix_base = $variation->is_on_sale() ? $regular : $regular;  //remise toujours sur prix de base
        $prix_remise_revendeur = $prix_base - ($prix_base * $percent / 100);
        $variation_data['prix_remise_revendeur'] = round($prix_remise_revendeur, 2);
        $variation_data['prix_remise_depart'] = $prix_remise_revendeur;
        $variation_data['prix_base'] = $prix_base;

    }else{
        $variation_data['class_hide_remise_revendeur'] = 'hide_remise_revendeur';
    }

  return $variation_data;

}, 10, 3);


add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script(
        'faq-load-more',
        get_stylesheet_directory_uri() . '/js/faq.js',
        ['jquery'],
        null,
        true
    );

    wp_localize_script('faq-load-more', 'faqAjax', [
        'ajaxurl' => admin_url('admin-ajax.php')
    ]);
});

add_action('wp_ajax_faq_load_more', 'faq_load_more');
add_action('wp_ajax_nopriv_faq_load_more', 'faq_load_more');

function faq_load_more() {
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $keyword = isset($_POST['keyword']) ? sanitize_text_field($_POST['keyword']) : '';

    $args = [
        'post_type' => 'faq',
        'posts_per_page' => 21, // nombre par bloc
        'paged' => $page,
        'orderby'        => 'title',   // ✅
        'order'          => 'ASC', 
    ];

    if($keyword) $args['s'] = $keyword;

    $query = new WP_Query($args);

    if($query->have_posts()):
        while($query->have_posts()): $query->the_post(); ?>
           <article class="faq-item">
                <h4><?php the_title(); ?></h4>
                <a class="" href="<?php the_permalink();?>">
                    <span class="">En savoir plus</span>
                    
                </a>
            </article>
            <?php endwhile;
    endif;

    wp_reset_postdata();
    wp_die();
}


add_action('wp_ajax_faq_search', 'faq_search');
add_action('wp_ajax_nopriv_faq_search', 'faq_search');

function faq_search() {
    $keyword = isset($_POST['keyword']) ? sanitize_text_field($_POST['keyword']) : '';

    if(!$keyword) wp_die(''); // sécurité

    $query = new WP_Query([
        'post_type'      => 'faq',
        'posts_per_page' => -1, // toutes les correspondances
        's'              => $keyword, // recherche WP standard
        'orderby'        => 'title',   // ✅
        'order'          => 'ASC', 
    ]);

    if($query->have_posts()) :
        while($query->have_posts()): $query->the_post(); ?>
           <article class="faq-item">
                <h4><?php the_title(); ?></h4>
                <a class="" href="<?php the_permalink();?>">
                    <span class="">En savoir plus</span>
                    
                </a>
            </article>
        <?php endwhile;
    else:
        echo '<p>Aucune question trouvée pour "<strong>'.esc_html($keyword).'</strong>"</p>';
    endif;

    wp_die();
}


function user_has_remise($user_id) {
    $today = current_time('Y-m-d H:i:s');

    $args = [
        'post_type'      => 'remise',
        'post_status'    => 'publish',
        'posts_per_page' => 1, // 1 suffit
        'meta_query'     => [
            [
                'key'   => 'utilisateur',
                'value' => $user_id,
            ],
            [
                'key'   => 'statut',
                'value' => 'validee',
            ],
            [
                'key'     => 'date_dexpiration',
                'value'   => $today,
                'compare' => '>=',
                'type'    => 'DATETIME',
            ],
        ],
    ];

    return !empty(get_posts($args)); // 🔥 true si au moins une
}

function traiter_chaines_menu($chaine) {
    //$res =  esc_html($chaine); avec span dans le texte
    //$res =  ($chaine); avec variations lues

     // On coupe avant le premier <span>
    $avant_span = preg_split('/<span.*?>/i', $chaine)[0];
    $res = $avant_span;
    return $res;

}


function shortcode_menu_entreprise($atts) {
    $atts = shortcode_atts(['categories' => '', 'page' => 'default'], $atts, 'menu_entreprise');
    
    $requested_slugs = array_map('trim', explode(',', $atts['categories']));
    $slugs_str = preg_replace('/[^a-zA-Z0-9_]/', '_', implode('_', $requested_slugs));
    $page_clean = preg_replace('/[^a-zA-Z0-9_]/', '_', $atts['page']);
    
    $cache_key = "cache_shortcode_menu_{$page_clean}_{$slugs_str}";
    $cached = get_transient($cache_key);
    if ($cached !== false) return $cached;
    
    error_log("MENU ENTREPRISE : génération lourde en cours...");
    
    $product_cats = get_terms([
        'taxonomy' => 'product_cat',
        'hide_empty' => true,
        'slug' => $requested_slugs
    ]);
    if (empty($product_cats)) return '';
    
    // Réordonner selon l'ordre des slugs
    if (!empty($requested_slugs)) {
        $ordered_cats = [];
        foreach ($requested_slugs as $slug) {
            foreach ($product_cats as $cat) {
                if ($cat->slug === $slug) {
                    $ordered_cats[] = $cat;
                    break;
                }
            }
        }
        $product_cats = $ordered_cats;
    }
    
    $class_col = $atts['page'] == "particulier" ? "col-mega-menu1" : "col-mega-menu2";
    
    // 🎯 Construction de l'output
    $output = '<div class="menu-entreprise-wrapper">';
    $output .= "<div class=\"categories-row {$class_col}\" style=\"display:grid;grid-template-columns:1.5fr 1fr 1fr; gap:15px;\">";
    
    foreach ($product_cats as $cat) {
        $products = wc_get_products([
            'limit' => -1,
            'status' => 'publish',
            'category' => [$cat->slug],
            'return' => 'ids',
            'type' => ['variable-subscription', 'subscription'],
            'meta_query' => [[
                'key' => 'afficher_dans_le_menu',
                'value' => '1',
                'compare' => '='
            ]]
        ]);
        
        if (empty($products)) continue;
        
        $marques = [];
        foreach ($products as $pid) {
            $ordre = (int)(get_field('ordre', $pid) ?: 9999);
            $terms = wp_get_post_terms($pid, 'product_brand');
            
            if (!empty($terms)) {
                foreach ($terms as $t) $marques[$t->name][] = ['id' => $pid, 'ordre' => $ordre];
            } else {
                $marques['Sans marque'][] = ['id' => $pid, 'ordre' => $ordre];
            }
        }
        
        ksort($marques, SORT_NATURAL | SORT_FLAG_CASE);
        foreach ($marques as &$liste) usort($liste, fn($a, $b) => $a['ordre'] <=> $b['ordre']);
        unset($liste);
        
        $output .= "<div class=\"col-categorie col1\" style=\"margin-bottom:30px;\">";
        $output .= "<span style=\"color:#ff7700;font-family:'Raleway';font-weight:800;font-size:18px;text-transform:uppercase\">";
        $output .= "<a href=\"" . get_category_link($cat->term_id) . "\" style=\"color:#ff7700;font-family:'Raleway';font-weight:800;font-size:17px;text-transform:uppercase\">";
        $output .= esc_html($cat->name) . "</a></span>";
        
        $output .= "<div class=\"row-marques\" style=\"display:grid;grid-template-columns:repeat(2,1fr);gap:20px;\">";
        
        foreach ($marques as $marque => $liste_produits) {
            $term = get_term_by('name', $marque, 'product_brand');
            $marque_link = $term ? get_term_link($term) : '#';
            
            $output .= "<div class=\"col-marque\">";
            
            if ($cat->slug !== "antivirus-pour-android") {
                $output .= "<a href=\"" . esc_url($marque_link) . "\" style=\"text-decoration:none;color:#000;font-family:'Raleway';font-weight:800;font-size:17px;\">";
                $output .= esc_html($marque) . "</a>";
            }
            
            foreach ($liste_produits as $item) {
                $pid = $item['id'];
                $parent = wc_get_product($pid);
                if (!$parent) continue;
                
                if ($parent->is_type('variable-subscription')) {
                    $variations = array_slice($parent->get_available_variations(), 0, 1);
                    foreach ($variations as $v) {
                        $variation = wc_get_product($v['variation_id']);
                        if (!$variation) continue;
                        
                        $output .= "<div class=\"produit-item\" style=\"margin-bottom:5px;\">";
                        $output .= "<a href=\"" . get_permalink($v['variation_id']) . "\" class=\"link-mega-menu\" style=\"text-decoration:none;color:#000;\">";
                        
                        if ($cat->slug !== "antivirus-pour-android") {
                            $output .= traiter_chaines_menu($variation->get_name());
                        } else {
                            $output .= "<span style=\"font-family:'Raleway';font-weight:800;font-size:17px;\">" . traiter_chaines_menu($variation->get_name()) . "</span>";
                        }
                        $output .= "</a></div>";
                    }
                } elseif ($parent->is_type('subscription')) {
                    $output .= "<div class=\"produit-item\" style=\"margin-bottom:5px;\">";
                    $output .= "<a href=\"" . get_permalink($parent->get_id()) . "\" class=\"link-mega-menu\" style=\"text-decoration:none;color:#000;\">";
                    
                    if ($cat->slug !== "antivirus-pour-android") {
                        $output .= traiter_chaines_menu($parent->get_name());
                    } else {
                        $output .= "<span style=\"font-family:'Raleway';font-weight:800;font-size:17px;\">" . traiter_chaines_menu($parent->get_name()) . "</span>";
                    }
                    $output .= "</a></div>";
                }
            }
            $output .= "</div>"; // .col-marque
        }
        $output .= "</div></div>"; // .row-marques, .col-categorie
    }
    
    $output .= "</div></div>"; // .categories-row, .menu-entreprise-wrapper
    
    set_transient($cache_key, $output, 300);
    return $output;
}
add_shortcode('menu_entreprise', 'shortcode_menu_entreprise');
