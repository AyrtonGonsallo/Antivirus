<?php
defined('ABSPATH') || exit;

// Vérifier si l'utilisateur est un revendeur
if (! (current_user_can('customer_revendeur') || current_user_can('customer_particulier')) ) {
    wp_safe_redirect(home_url());
    exit;
}

// Message de validation/soumission (sera géré après)
if (isset($_GET['client_added']) && $_GET['client_added'] == 'true') {
    echo '<div class="woocommerce-message">Client ajouté avec succès </div>';
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
            $status           = get_field('status', $devis->ID)["label"];
            $type_de_devis        = get_field('type_de_devis', $devis->ID)["label"];
            //var_dump($type_de_devis);
            $date_de_creation_formatted = $date_de_creation ? (new DateTime($date_de_creation))->format('d/m/Y \à H\hi') : '';
            $date_expiration_formatted  = $date_expiration ? (new DateTime($date_expiration))->format('d/m/Y \à H\hi') : '';

        ?>
        <tr>
            <td><?php echo esc_html($date_de_creation_formatted); ?></td>
            <td><?php echo esc_html($date_expiration_formatted); ?></td>
            <td><?php echo esc_html($status); ?></td>
            <td><?php echo esc_html($type_de_devis); ?></td>
            <td> 
                <a href="<?php echo esc_url(get_permalink($devis->ID)); ?>" class="devis-btn tbn-see">voir</a> 
                
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

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
    .devis-btn{
        border-width: 2px 2px 2px 2px;
        padding: 6px 14px;
        color: white !important;
        font-weight:bold;
        border-radius: 20px;
    }
   
    .tbn-see{
        background: #FF7800;
    }
</style>
<br><br>