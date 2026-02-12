<?php
defined('ABSPATH') || exit;

// Vérifier si l'utilisateur est un revendeur
if (! (current_user_can('customer_revendeur') || current_user_can('customer_particulier')) ) {
    wp_safe_redirect(home_url());
    exit;
}

$show_refuse_form = false;
$devis_id = 0;

if (isset($_POST['refuse-devis'])) {
    $show_refuse_form = true;
    $devis_id = intval($_POST['devis_id']);
   
}


if (isset($_POST['send-refuse'])) {
    $devis_id = intval($_POST['devis_id']);
    $motif = sanitize_textarea_field($_POST['motif']);
    update_field('status', 'rejetee', $devis_id);
    update_field('motif_de_refus_client', $motif, $devis_id);
    $user      = get_field('utilisateur', $devis_id);
    $user_id = $user->ID;
    $customer = new WC_Customer( $user_id );
    $tax_rates = WC_Tax::get_rates("",$customer );
    $first_rate = reset($tax_rates);
    $percent_tva = $first_rate['rate'];
    $title_tva = $first_rate['label'];
    update_field('tva', $title_tva, $devis_id);
    update_field('taux_tva', $percent_tva, $devis_id);

    wc_add_notice("Refus envoyé avec succès.", "success");
    wp_safe_redirect('/mon-compte/mes-devis/');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "accept_and_convert_devis_to_cart") {
         
    $devis_id = intval($_POST['devis_id']);

    // ---------- accepté
    $user      = get_field('utilisateur', $devis_id);
    $user_id = $user->ID;
    $customer = new WC_Customer( $user_id );
    $tax_rates = WC_Tax::get_rates("",$customer );
    $first_rate = reset($tax_rates);
    $percent_tva = $first_rate['rate'];
    $title_tva = $first_rate['label'];
    update_field('tva', $title_tva, $devis_id);
    update_field('taux_tva', $percent_tva, $devis_id);

    wc_add_notice("Devis accepté avec succès.", "success");
    
    //--convertir en panier

    
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

        
        update_field('status', 'accepte_par_le_client', $devis_id);
        wc_add_notice("Votre devis à été converti en panier.", "success");
        wp_safe_redirect(wc_get_cart_url());
        exit;
    }




    //wp_safe_redirect('/mon-compte/mes-devis/');
    exit;
}
?>

<h2>Mes Devis</h2>


<?php
    $user_id = get_current_user_id();

    $devis_liste = 
    get_posts([
        'post_type'      => 'devis-en-ligne',
        'posts_per_page' => -1,
        'meta_query'     => [
            [
                'key'     => 'utilisateur',
                'value'   => $user_id,
                'compare' => '=',
            ]
        ]

    ]);
    //echo "Total ".count($devis_liste);
?>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />

<!-- Tableau -->
<table id="tableMesDevis" class="display">
    <thead>
        <tr>
            <th>Nº</th>
            <th>Date de création</th>
            <th>Date d’expiration</th>
            <th>Status</th>
            <th>Type de devis</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($devis_liste as $devis) : 
            $date_de_creation   = get_field('date_de_creation', $devis->ID);
            $date_expiration  = get_field('date_expiration', $devis->ID);
            $variations  = get_field('variations', $devis->ID);
           
          // var_dump($recapitulatif_pdf);
            $status           = get_field('status', $devis->ID)["label"];
            $type_de_devis        = get_field('type_de_devis', $devis->ID)["label"];
            $status_value           = get_field('status', $devis->ID)["value"];
            $type_de_devis_value        = get_field('type_de_devis', $devis->ID)["value"];
            //var_dump($type_de_devis);
            $date_de_creation_formatted = $date_de_creation ? (new DateTime($date_de_creation))->format('d/m/Y \à H\hi') : '';
            $date_expiration_formatted  = $date_expiration ? (new DateTime($date_expiration))->format('d/m/Y \à H\hi') : '';

        ?>
        <tr>
            <td><?php echo $devis->ID; ?></td>
            <td><?php echo esc_html($date_de_creation_formatted); ?></td>
            <td><?php echo esc_html($date_expiration_formatted); ?></td>
            <td><?php echo esc_html($status); ?></td>
            <td><?php echo esc_html($type_de_devis); ?></td>
            <td> 
                <div class="flex-btns">
                    <a href="<?php echo esc_url(get_permalink($devis->ID)); ?>" class="devis-btn tbn-see">voir</a> 
                    <?php

                    foreach ( $variations as $variation ) : 
                        $recapitulatif_pdf  = get_field('recapitulatif_pdf', $variation->ID);
                        $lien_fichier = $recapitulatif_pdf["link"];
                        $nom_fichier = $recapitulatif_pdf['filename'];;
                        $parts_name = explode("-", $nom_fichier);
                        //var_dump($recapitulatif_pdf);

                        if($lien_fichier){ ?>
                            <a href="<?php echo $lien_fichier; ?>" target="_blank" class="devis-btn tbn-see">Télécharger fichier devis <?php echo $parts_name[1].' '.$parts_name[2];?></a> 
                        <?php }
                    endforeach; 

                     ?>
                        
                    <?php if($type_de_devis_value=="admin" || $type_de_devis_value=="corrige"){?>
                        <?php if ($status_value === 4454464) : //jamais?>
                            
                        
                            <form method="POST">
                                <input type="hidden" name="action" value="accept_and_convert_devis_to_cart">
                                <input type="hidden" name="devis_id" value="<?php echo $devis->ID;?>">
                                <button type="submit" class="devis-btn">Accepter et convertir en panier</button>
                            </form>

                            <form method="POST">
                                <input type="hidden" name="devis_id" value="<?php echo $devis->ID;?>">
                                <button type="submit" name="refuse-devis" class="devis-btn">Refuser</button>
                            </form>
                        <?php endif; ?>
                    <?php } ?>
                </div>
                
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>




<?php if ( $show_refuse_form ) : ?>
<div class="popup-overlay">
    <div class="popup-refuse">

        <h3>Refuser le devis</h3>

        <form method="POST">
            <input type="hidden" name="devis_id" value="<?php echo esc_attr($devis_id); ?>">

            <textarea
                name="motif"
                required
                placeholder="Motif du refus"
                rows="5"
            ></textarea>

            <div class="popup-actions">
                <button type="submit" name="send-refuse" class="btn-confirm">
                    Confirmer le refus
                </button>
            </div>
        </form>

    </div>
</div>
<?php endif; ?>




<!-- jQuery et DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
jQuery(document).ready(function($) {
    $('#tableMesDevis').DataTable( {
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
<style>
    .flex-btns{
        display: flex;
        gap: 6px;
        max-width: 250px;
        flex-direction: row;
        flex-wrap: wrap;
    }
    .devis-btn{
        border: 2px solid #FF7800;
        padding: 6px 14px;
        color: white !important;
        font-weight:bold;
        font-size: 1em;
        line-height: 1em;
        border-radius: 7px;
    }
    button.devis-btn{
        border: 2px solid #FF7800;
        padding: 6px 14px;
        color: white !important;
        font-weight: bold;
        font-size: 1em;
        line-height: 1em;
        border-radius: 7px;
    }
    button.devis-btn:hover{
        border: 2px solid #FF7800;
        background-color: transparent !important;
        color: #FF7800 !important;
    }
    .devis-btn:hover{
        border: 2px solid #FF7800;
        background-color: transparent !important;
        color: #FF7800 !important;
    }
    
    .tbn-see{
        background: #FF7800;
    }

    .popup-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.popup-refuse {
    background: #fff;
    padding: 25px;
    max-width: 400px;
    width: 100%;
    border-radius: 8px;
    box-shadow: 0 10px 40px rgba(0,0,0,.3);
}

.popup-refuse textarea {
    width: 100%;
    margin: 15px 0;
}

.popup-actions {
    text-align: right;
}

</style>
<br><br>