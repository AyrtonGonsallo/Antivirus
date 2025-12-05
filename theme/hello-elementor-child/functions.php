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
                    foreach ($upsell_ids as $id): ?>
                        <?php $p = wc_get_product($id); 
                        if (!$p) continue; 
                        global $product;
                        $product = $p; // important !
                        ?>
                        <li class="product">
                            <a href="<?php echo get_permalink($id); ?>">
                                <?php echo $p->get_image('medium_large'); ?>
                                <h3 style="font-size: 22px;font-weight: 700;"><?php echo $p->get_name(); ?></h3>
                                <span class="price"><?php echo $p->get_price_html(); ?></span>
                            </a>
                            <?php woocommerce_template_loop_add_to_cart(); ?>
                        </li>
                    <?php endforeach; 
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

			 <?php $old_global_product = $product;
              foreach ($cross_sell_ids as $id): ?>
                        <li class="product type-product post-2743 status-publish first instock product_cat-entreprise has-post-thumbnail shipping-taxable product-type-simple">
                            <?php
                            $p = wc_get_product($id);
                            if (!$p) continue;
                            global $product;
                            $product = $p; // important !
                            ?>
                            <a href="<?php echo get_permalink($id); ?>">
                                <?php echo $p->get_image('medium_large'); ?>
                                <h3 style="font-size: 22px;font-weight: 700;"><?php echo $p->get_name(); ?></h3>
                                <span class="price"><?php echo $p->get_price_html(); ?></span>
                            </a>
                            <?php woocommerce_template_loop_add_to_cart(['product' => $p]); ?>
                        </li>
                    <?php endforeach; 
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

function shortcode_menu_entreprise($atts) {
    ob_start();

    $atts = shortcode_atts([
        'categories' => '', // liste de slugs séparés par des virgules
        'page'        => 'default',
    ], $atts, 'menu_entreprise');

   

    // Slugs envoyés
    $requested_slugs = array_map('trim', explode(',', $atts['categories']));
    $page=$atts['page'];
    $args = [
        'taxonomy'   => 'product_cat',
        'hide_empty' => true,
        'slug'       => $requested_slugs,
    ];

    $product_cats = get_terms($args);
    if (empty($product_cats)) return '';

    // Réordonner selon l'ordre des slugs passés
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
    $class_col_mega_menu=($page=="particulier")?"col-mega-menu1":"col-mega-menu2";

    ?>
    <div class="menu-entreprise-wrapper">
        <div class="categories-row <?php echo $class_col_mega_menu;?>" style="display:grid;grid-template-columns: repeat(3, 1fr); gap:15px;">
            <?php foreach ($product_cats as $cat): ?>

                <?php 
                $products = wc_get_products([
                    'limit'    => -1,
                    'status'   => 'publish',
                    'category' => [$cat->slug],
                    'return'   => 'ids'
                ]);
                if (empty($products)) continue;

                // Regrouper par marque
                $marques = [];
                foreach ($products as $pid) {
                    $terms = wp_get_post_terms($pid, 'product_brand');
                    if (!empty($terms)) {
                        foreach ($terms as $t) {
                            $marques[$t->name][] = $pid;
                        }
                    } else {
                        $marques['Sans marque'][] = $pid;
                    }
                }
                ksort($marques, SORT_NATURAL | SORT_FLAG_CASE);

                ?>
                
                    <div class="col-categorie col1" style="margin-bottom:30px;">
                        <span style="color:#ff7700;font-family: 'Raleway'; font-weight: 700; font-size: 13px;text-transform:uppercase">
                            <a href="<?php echo get_category_link($cat->term_id); ?>" style="color:#ff7700;font-family: 'Raleway'; font-weight: 700; font-size: 13px;text-transform:uppercase"> 
                                <?php echo $cat->name; ?>
                            </a>
                        </span>

                        <div class="row-marques" style="display:grid;grid-template-columns: repeat(2, 1fr); gap:20px;">
                            
                            <?php foreach ($marques as $marque => $liste_produits): 
                                $term = get_term_by('name', $marque, 'product_brand');
                                $marque_link = $term ? get_term_link($term) : '#';
                            ?>
                                <div class="col-marque">
                                    <?php if($cat->slug!="antivirus-pour-android"){?>
                                        <a href="<?php echo esc_url($marque_link); ?>" style="text-decoration:none;color:#000;">
                                            <b><?php echo $marque; ?></b>
                                        </a>
                                    <?php }?>
                                    <?php foreach ($liste_produits as $pid): 
                                        $p = wc_get_product($pid); ?>
                                        <div class="produit-item" style="margin-bottom:5px;">
                                            <a href="<?php echo get_permalink($pid); ?>" style="text-decoration:none;color:#000;">
                                                <?php if($cat->slug!="antivirus-pour-android"){?>
                                                    <?php echo $p->get_name(); ?>
                                                <?php }else{?>
                                                    <b><?php echo $p->get_name(); ?></b>
                                                <?php }?>
                                            </a>
                                        </div>
                                    <?php endforeach; ?>

                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                

            <?php endforeach; ?>
        </div>
    </div>
    <?php

    return ob_get_clean();
}
add_shortcode('menu_entreprise', 'shortcode_menu_entreprise');

add_filter( 'wp_calculate_image_srcset', '__return_false' );
/******************************************************************************************/
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










