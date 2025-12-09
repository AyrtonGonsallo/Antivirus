<?php
/**
 * The site's entry point.
 *
 * Loads the relevant template part,
 * the loop is executed (when needed) by the relevant template part.
 *
 * @package HelloElementor
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


$id = get_the_ID();
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // bouton Accepter cliqué
    if (isset($_POST['accept-devis'])) {
        update_field('status', 'acceptee', $id);
        $user      = get_field('utilisateur', $devis_id);
        $user_id = $user->ID;
        $customer = new WC_Customer( $user_id );
        $tax_rates = WC_Tax::get_rates("",$customer );
        $first_rate = reset($tax_rates);
        $percent_tva = $first_rate['rate'];
        $title_tva = $first_rate['label'];
        update_field('tva', $title_tva, $id);
        update_field('taux_tva', $percent_tva, $id);

        wc_add_notice("Devis accepté avec succès.", "success");
        wp_safe_redirect('/mon-compte/mes-devis/');
        exit;
    }

    // bouton Refuser cliqué (affiche formulaire)
    if (isset($_POST['refuse-devis'])) {
        $show_refuse_form = true;
    }

    // envoi du motif de refus
    if (isset($_POST['send-refuse'])) {
        $motif = sanitize_textarea_field($_POST['motif']);
        update_field('status', 'rejetee', $id);
        update_field('motif_de_refus_client', $motif, $id);
        wc_add_notice("Refus envoyé avec succès.", "success");
        wp_safe_redirect('/mon-compte/mes-devis/');
        exit;
    }
}


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "convert_devis_to_cart") {

    $devis_id = intval($_POST['devis_id']);
    $produits_de_la_commande = get_field('produits_de_la_commande', $devis_id);

    if ($produits_de_la_commande) {

        if (WC()->cart) {
            WC()->cart->empty_cart();
        }

        foreach ($produits_de_la_commande as $ligne) {
            
            $prod_obj = $ligne['produit'][0]; // ACF relation
            $qty      = (int)$ligne['quantite'];
            $prix     = (float)$ligne['prix_propose'];

            WC()->cart->add_to_cart(
                $prod_obj->ID,
                $qty,
                0,
                [],
                [
                    'prix_force' => $prix,
                    'source_devis' => $devis_id
                ]
            );
        }

        
        update_field('status', 'convertie', $id);
        wc_add_notice("Votre devis à été converti en panier.", "success");
        wp_safe_redirect(wc_get_cart_url());
        exit;
    }
}
$devis_id=get_the_ID();
$status = get_field('status', $devis_id);



get_header();
?>


<div class="elementor-element page-compte " data-id="10efee7b" data-element_type="container">
	

    <div class="layout-account">
        <?php
            $menu_items = apply_filters( 'woocommerce_account_menu_items', wc_get_account_menu_items() );

            echo '<nav class="woocommerce-MyAccount-navigation"><ul>';

            foreach ( $menu_items as $endpoint => $label ) {
                $active=($endpoint=="mes-devis")?"active":"";
                
                $url = wc_get_account_endpoint_url( $endpoint );
                echo '<li class="' . esc_attr($endpoint) . ' '.$active.'">';
                echo '<a href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
                echo '</li>';
            }

            echo '</ul></nav>';
        ?>
        <div class="page-content">
            <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

            <?php

            $date_de_creation   = get_field('date_de_creation', $post->ID);
            $date_expiration  = get_field('date_expiration', $post->ID);
            $status_label           = get_field('status', $post->ID)["label"];
            $status_value           = get_field('status', $post->ID)["value"];
            $type_de_devis_label        = get_field('type_de_devis', $post->ID)["label"];
            $type_de_devis_value        = get_field('type_de_devis', $post->ID)["value"];
            $compt2save     =    get_field('compt2save', $post->ID);
            $software_duration     =    get_field('software_duration', $post->ID)["label"];
            $note_client     =    get_field('note_client', $post->ID);
            $note_admin     =    get_field('note_admin', $post->ID);
            $motif_de_refus_client     =    get_field('motif_de_refus_client', $post->ID);
            $produits_de_la_commande     =    get_field('produits_de_la_commande', $post->ID);
            $option        = get_field('option', $post->ID);
            $date_de_creation_formatted = $date_de_creation ? (new DateTime($date_de_creation))->format('d/m/Y \à H\hi') : '';
            $date_expiration_formatted  = $date_expiration ? (new DateTime($date_expiration))->format('d/m/Y \à H\hi') : '';
            
          

            /*affficher le reste juste les infos pas de formulaire pour l'instant

            champs date_de_creation Sélecteur de date et heure, 
            option bouton radio(valeur/libellé retourne la valeur), 
            compt2save Zone de texte, produits_de_la_commande est un Répéteur et il a les sous champs 
            produit Relation retourne l'objet, prix_propose nombre,
            et quantite nombre,
            software_duration bouton radio(valeur/libellé retourne la valeur), 
            comment Zone de texte, 
            on affichera la section produits_de_la_commande si le option est ikn sinon on affiche compt2save

            */

            echo "<p><strong>Date de création:</strong> $date_de_creation_formatted</p>";
            echo "<p><strong>Date d'expiration:</strong> $date_expiration_formatted</p>";
            echo "<p><strong>Status:</strong> $status_label</p>";
            echo "<p><strong>Type de devis:</strong> $type_de_devis_label</p>";
            echo "<p><strong>Durée du logiciel:</strong> $software_duration</p>";
            echo "<p><strong>Note client:</strong> $note_client</p>";
            echo "<p><strong>Note admin:</strong> $note_admin</p>";
            echo "<p><strong>Motif de refus client:</strong> $motif_de_refus_client</p>";
            if ($option === 'ikn') {
                // Afficher le répéteur produits_de_la_commande
                if ($produits_de_la_commande) {
                    echo '<table class="table-devis">';
                    echo '<thead>
                            <tr>
                                <th>Produit</th>
                                <th>Quantité</th>
                                <th>Prix actuel</th>
                                <th>Prix proposé</th>
                            </tr>
                        </thead>';
                    echo '<tbody>';

                    foreach ($produits_de_la_commande as $ligne) {
                        
                        $produit_relation = $ligne['produit'];
                        $prix_propose = ($ligne['prix_propose'])?($ligne['prix_propose'].'€'):"en attente";
                        $quantite = $ligne['quantite'];

                        if (is_array($produit_relation) && isset($produit_relation[0])) {
                            $produit_post = $produit_relation[0];
                            $product_id = $produit_post->ID;
                            $product_obj = wc_get_product($product_id);

                            if ($product_obj) {

                                $product_name = $product_obj->get_name();
                                $product_price = $product_obj->get_price();
                                $product_img = wp_get_attachment_image_src( $product_obj->get_image_id(), 'thumbnail' );
                                $product_img_url = $product_img ? $product_img[0] : '';

                                echo '<tr>';
                                
                                echo '<td style="display:flex;align-items:center;gap:10px;">';
                                echo '<a href="'.get_permalink($product_id).'">';
                                    if($product_img_url)
                                        echo '<img src="'.esc_url($product_img_url).'" width="50" height="50" style="border-radius:4px;">';
                                    echo '<span>'.wp_kses_post($product_name).'</span>';
                                echo '</a>';
                                echo '</td>';

                                echo '<td>'.esc_html($quantite).'</td>';
                                echo '<td>'.esc_html($product_price).'€</td>';
                                echo '<td>'.esc_html($prix_propose).'</td>';
                                
                                echo '</tr>';
                            }
                        }
                    }

                    echo '</tbody></table>';
                }


            } else {
                // Afficher compt2save
                echo "<p><strong>Ordinateurs à protéger:</strong> $compt2save</p>";
            }
           
            
            
            

            ?>
           
            <?php if($type_de_devis_value=="admin" || $type_de_devis_value=="corrige"){?>

                <form method="POST">

                    <?php if ($status_value === 'en_attente') : ?>

                        <!-- Si bouton refuser n’a pas encore été cliqué -->
                        <?php if (empty($show_refuse_form)) : ?>

                            <button type="submit" name="accept-devis" class="devis-btn tbn-yes">Accepter</button>
                            <button type="submit" name="refuse-devis" class="devis-btn tbn-no">Refuser</button>

                        <?php else : ?>

                            <textarea name="motif" required placeholder="Motif du refus"></textarea>
                            <br>
                            <button type="submit" name="send-refuse">Confirmer le refus</button>

                        <?php endif; ?>

                    <?php endif; ?>
                    <?php if ($status_value === 'acceptee') : ?>
                        <form method="post">
                            <input type="hidden" name="action" value="convert_devis_to_cart">
                            <input type="hidden" name="devis_id" value="'.$post->ID.'">
                            <button type="submit" class="btn-cart">🛒 Transformer en panier</button>
                        </form>
                    <?php endif; ?>

                </form>


            <?php }?>
        </div>
    </div>

	


</div>
<style>
    a{
        text-decoration:none !important;
    }
    .elementor-element.page-compte {
    display: flex;
    padding-top: 4%;
    padding-bottom: 4%;
    padding-left: 2%;
    padding-right: 2%;
}
    .layout-account nav {
    float: inline-start;
}
.layout-account .page-content{
    float: inline-end;
    padding: 0;
    padding-inline-start: var(--tab-content-spacing, 6%);
 
}
.layout-account nav li {
    margin: 3px 3px;
    display: inline-block;
    list-style-type: none;
        width: var(--tab-width, 100%);
}
.layout-account nav ul {
    padding-inline-start: 0;
}
.layout-account nav li a{
   background: #C0EBFA66;
    border-color: #C0EBFA;
    border-radius: 17px 17px 17px 17px;
    border-style:  solid;
    border-width: 2px;
    color: #000000;
    display: block;
    font-size: 14px;
    font-style: normal;
    font-weight: 700;
    padding: var(--tabs-padding, 12px 20px);
    text-align: var(--tabs-alignment, start);
}
.layout-account nav li.active a{
   background: #FF7800 !important;
    border-color: #FF7800 !important;
    color: #ffffffff !important;
}
.layout-account nav li a:hover{
   background: #FF7800 !important;
    border-color: #FF7800 !important;
    color: #ffffffff !important;
}
.devis-btn{
        border-width: 2px 2px 2px 2px;
        border:none !important;
        padding: 6px 14px;
        color: white !important;
        font-weight:bold;
        border-radius: 20px;
        text-decoration:none !important;
    }
    .tbn-yes{
        background-color: #42b30a !important;
    }
    .tbn-no{
        background-color: red !important;
    }


 @media (max-width:1024px){
    .layout-account .page-content,.layout-account nav{
        width:100%;
    }
}

 @media (min-width:1024px){
    .layout-account .page-content{
        width:75%;
    }
    .layout-account nav{
        width:25%;
    }
}
    </style>
    
	<?php

get_footer();
