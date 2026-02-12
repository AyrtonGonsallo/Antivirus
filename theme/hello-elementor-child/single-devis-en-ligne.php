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

$variation_refuser_id = 0;
$devis_id = get_the_ID();//devis
if ($_SERVER["REQUEST_METHOD"] === "POST") {


    // bouton Refuser cliqué (affiche formulaire)
    if (isset($_POST['refuse-devis'])) {
        $show_refuse_form = true;
        $variation_refuser_id =  intval($_POST['variation_refuser_id']);
    }

    // envoi du motif de refus
    if (isset($_POST['send-refuse'])) {
        $variation_id = intval($_POST['variation_id']);
        $motif = sanitize_textarea_field($_POST['motif']);
        update_field('statut', 'rejete', $variation_id);
        update_field('status', '5', $devis_id);
        update_field('motif_de_refus_client', $motif, $variation_id);
        wc_add_notice("Refus envoyé avec succès.", "success");
        wp_safe_redirect('/mon-compte/mes-devis/');
        exit;
    }
}



if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "accept_and_convert_variation_to_cart") {
        
        // ---------- accepté
        $user      = get_field('utilisateur', $devis_id);
        $variation_id = intval($_POST['variation_id']);
        $user_id = $user->ID;
        $total_ht  = (float) get_field('total_ht', $variation_id);
       
        $remise_fields_map = [
            "remise_changer_avast" => 'Remise changement',
            "remise_renewal"        => 'Remise renouvellement de licences',
            "remise_administration_mairie"       => 'Remise administrations et mairies',
            "remise_etalissements" => 'Remise établissements scolaires et associations',
            "remise_cumulee" => 'Autre remise',
            "remise_statutaire" => 'Autre remise',
            "remise_commerciale" => 'Autre remise',
            "remise_revendeur" => 'Remise revendeur',
        ];

         $remise_fields = [
            'remise_renewal',
            'remise_cumulee',
            'remise_statutaire',
            'remise_commerciale',
            'remise_revendeur',
            'remise_administration_mairie',
            'remise_etalissements',
            'remise_changer_avast'
        ];
       
        
        //--convertir en panier

        
        $produits_de_la_variation = get_field('produits_de_la_variation', $variation_id);

        if ($produits_de_la_variation) {

            if (WC()->cart) {
                WC()->cart->empty_cart();
            }

            foreach ($produits_de_la_variation as $ligne) {
                
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

            $remises_devis = [];
            $base_calcul = $total_ht; // on démarre sur le total initial
            foreach ($remise_fields as $field) {

                $percent = (float) get_field($field, $variation_id);

                if ($percent > 0) {

                    $label = $remise_fields_map[$field];

                    // 🔥 calcul sur la base actuelle, pas sur le total initial
                    $montant_remise = ($percent / 100) * $base_calcul;

                    error_log("remise $label $percent % = $montant_remise sur $base_calcul");

                    $remises_devis[] = [
                        'label'  => "$label $percent %",
                        'amount' => $montant_remise
                    ];

                    // 🔥 on diminue la base pour la prochaine remise
                    $base_calcul -= $montant_remise;
                }
            }


            WC()->session->set('devis_remises', $remises_devis);
            WC()->session->set('from_devis', true);

            
            update_field('statut', 'accepte', $variation_id);
            update_field('status', '4', $devis_id);
            wc_add_notice("Votre devis à été converti en panier.", "success");
            
            wp_safe_redirect(wc_get_cart_url());
            exit;
        }




        //wp_safe_redirect('/mon-compte/mes-devis/');
        exit;
    }
$devis_id=get_the_ID();
$status = get_field('status', $devis_id);



get_header();
?>


<div class="elementor-element page-compte " data-id="10efee7b" data-element_type="container">
	

    <div class="layout-account">
        <nav class="woocommerce-MyAccount-navigation" aria-label="<?php esc_html_e( 'Account pages', 'woocommerce' ); ?>">
            <ul>
                <?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : 
                    $active=($endpoint=="mes-devis")?"active":"";
                    ?>
                    <li class="<?php echo wc_get_account_menu_item_classes( $endpoint ); ?> <?php echo $active; ?>">
                        <a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>" <?php echo wc_is_current_account_menu_item( $endpoint ) ? 'aria-current="page"' : ''; ?>>
                            <?php echo esc_html( $label ); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        
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
            $variations  = get_field('variations', $post->ID);
           
            $option        = get_field('option', $post->ID);
            $date_de_creation_formatted = $date_de_creation ? (new DateTime($date_de_creation))->format('d/m/Y \à H\hi') : '';
            $date_expiration_formatted  = $date_expiration ? (new DateTime($date_expiration))->format('d/m/Y \à H\hi') : '';
            
          

            /*affficher le reste juste les infos pas de formulaire pour l'instant

            champs date_de_creation Sélecteur de date et heure, 
            option bouton radio(valeur/libellé retourne la valeur), 
            compt2save Zone de texte, produits_de_la_variation est un Répéteur et il a les sous champs 
            produit Relation retourne l'objet, prix_propose nombre,
            et quantite nombre,
            software_duration bouton radio(valeur/libellé retourne la valeur), 
            comment Zone de texte, 
            on affichera la section produits_de_la_variation si le option est ikn sinon on affiche compt2save

            */

            echo "<p><strong>Date de création:</strong> $date_de_creation_formatted</p>";
            echo "<p><strong>Date d'expiration:</strong> $date_expiration_formatted</p>";
            echo "<p><strong>Status:</strong> $status_label</p>";
            echo "<p><strong>Type de devis:</strong> $type_de_devis_label</p>";
            echo "<p><strong>Durée du logiciel:</strong> $software_duration</p>";
            echo "<p><strong>Note client:</strong> $note_client</p>";
            if ($option === 'ikn') {

                foreach ( $variations as $variation ) : 
                    
                    $variation_title     =    get_the_title( $variation->ID);
                    $produits_de_la_variation     =    get_field('produits_de_la_variation', $variation->ID);
                    $formule     =    get_field('formule', $variation->ID);
                    $note_admin     =    get_field('note_admin', $variation->ID);
                    $statut_variation           = get_field('statut', $variation->ID);
                     //$motif_de_refus_client     =    get_field('motif_de_refus_client', $variation->ID);

                    if ($statut_variation === 'rejete')
                        continue;

                    echo '<h3 class="titre-variation-devis">'.$variation_title.'</h3>' ;
                    echo '<div class="table-devis-details">'.$formule.'</div>' ;
                    echo "<p><strong>Note admin:</strong> $note_admin</p>";
                        
                    // Afficher les options
                    if($type_de_devis_value=="admin" || $type_de_devis_value=="corrige"){

                

                        if ($statut_variation === 'en_attente') : 

                            // Si bouton refuser n’a pas encore été cliqué 
                            if (empty($show_refuse_form) || ($variation_refuser_id != $variation->ID)) : ?>
                                <div class="action-btns">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="accept_and_convert_variation_to_cart">
                                        <input type="hidden" name="variation_id" value="<?php echo $variation->ID;?>">
                                        <button type="submit" class="btn-cart">Accepter et convertir en panier</button>
                                    </form>
                                    <form method="POST">
                                        <input type="hidden" name="variation_refuser_id" value="<?php echo $variation->ID;?>">
                                        <button type="submit" name="refuse-devis" class="devis-btn tbn-no">Refuser</button>
                                    </form>
                                </div>
                            <?php elseif (!empty($show_refuse_form) && $variation_refuser_id == $variation->ID) : ?>
                                <form method="POST">
                                    <textarea name="motif" required placeholder="Motif du refus"></textarea>
                                    <input type="hidden" name="variation_id" value="<?php echo $variation->ID;?>">
                                    <br>
                                    <button type="submit" name="send-refuse">Confirmer le refus</button>
                                </form>
                            <?php endif; ?>

                        <?php endif; ?>
                       

                


            <?php }
                    

                endforeach; 


            } else {
                // Afficher compt2save
                echo "<p><strong>Ordinateurs à protéger:</strong> $compt2save</p>";
            }
           
            
            
            

            ?>
           
            
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
.btn-cart{ font-weight: 600!important; padding: 15px 30px 15px 30px !important;border-width: 3px 3px 3px 3px;}
.btn-cart:hover{ background-color:transparent!important;color:#FF7800!important;}
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
		border-width: 3px 3px 3px 3px;
		padding: 15px 30px 15px 30px !important;
        background-color: #42b30a !important;
    }
    .tbn-no{
		border-width: 3px 3px 3px 3px !important;
		padding: 15px 30px 15px 30px !important;
        background-color: red !important;
		border:2px solid red !important;
    }
	.tbn-no:hover{
		opacity:0.6;
    }
    .action-btns{
        display:flex;
        gap:20px;
        align-items: center;
    }




/* En-têtes */
.table-devis th {
  font-weight: 600;
  text-align: left;
}

/* Colonnes des prix en gras (3, 4 et 5) */
.table-devis td:nth-child(2),
.table-devis td:nth-child(3),
.table-devis td:nth-child(4),
.table-devis td:nth-child(5) {
  font-weight: bold;
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
/*
    
if ( ! is_user_logged_in() ) {
    echo 'Accès refusé';
    return;
}

$batch_size = 50; // nombre d’articles par chargement
$paged      = isset($_GET['p']) ? (int) $_GET['p'] : 1;

$args = array(
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'posts_per_page' => $batch_size,
    'paged'          => $paged,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'meta_query'     => array(
        array(
            'key'     => '_thumbnail_id',
            'compare' => 'NOT EXISTS',
        ),
    ),
);

$query = new WP_Query($args);

if ( ! $query->have_posts() ) {
    echo '<strong>✔ Terminé, plus aucun article à traiter.</strong>';
    return;
}

foreach ( $query->posts as $post ) {

    if ( empty($post->post_content) ) {
        continue;
    }

    if ( preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $post->post_content, $m) ) {

        $img_url = $m[1];

        // enlever les tailles -90x90, -300x200, etc.
        $img_url = preg_replace(
            '/-\d+x\d+(?=\.(jpg|jpeg|png|gif|webp))/i',
            '',
            $img_url
        );
        
        $attachment_id = attachment_url_to_postid($img_url);
        
        echo '✔ Image trouvée pour '.$post->post_title.' : <strong>' . esc_html($img_url) . '</strong><br>';
        echo  'attahc id '.$attachment_id;
        if ( $attachment_id ) {
            set_post_thumbnail($post->ID, $attachment_id);
            echo '✔ Image ajoutée pour : <strong>' . esc_html($post->post_title) . '</strong><br>';
        }
    }
}

wp_reset_postdata();

$next = $paged + 1;
echo '<hr>';
echo '<a href="?p=' . $next . '">▶ Continuer (page ' . $next . ')</a>';
*/

?>
	<?php

get_footer();
