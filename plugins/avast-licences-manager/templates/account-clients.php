<?php
defined('ABSPATH') || exit;

// Vérifier si l'utilisateur est un revendeur
if (!current_user_can('customer_revendeur')) {
    wp_safe_redirect(home_url());
    exit;
}

// Message de validation/soumission (sera géré après)
if (isset($_GET['client_added']) && $_GET['client_added'] == 'true') {
    echo '<div class="woocommerce-message">Client ajouté avec succès </div>';
}
?>

<h2>Mes Clients</h2>
<p>Ici, le revendeur pourra gérer les clients finaux.</p>


<?php
$revendeur_id = get_current_user_id();

$args = [
    'role'       => 'customer_particulier',
    'meta_key'   => 'revendeur_id',
    'meta_value' => $revendeur_id
];

$clients = get_users($args);
?>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />

<!-- Tableau -->
<table id="myTable" class="display">
    <thead>
        <tr>
            <th>Type Client</th>
            <th>Dénomination</th>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Email</th>
            <th>Téléphone</th>
            <th>Fax</th>
            <th>Adresse</th>
            <th>Ville</th>
            <th>Code Postal</th>
            <th>Pays</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($clients as $client) : 
            $type_client   = get_user_meta($client->ID, 'type_client', true);
            $denomination  = get_user_meta($client->ID, 'denomination', true);
            $nom           = get_user_meta($client->ID, 'last_name', true);
            $prenom        = get_user_meta($client->ID, 'first_name', true);
            $email         = $client->user_email;
            $billing_phone     = get_user_meta($client->ID, 'billing_phone', true);
            $fax           = get_user_meta($client->ID, 'fax', true);
            $billing_address_1       = get_user_meta($client->ID, 'billing_address_1', true);
            $ville         = get_user_meta($client->ID, 'ville', true);
            $code_postal   = get_user_meta($client->ID, 'code_postal', true);
            $pays          = get_user_meta($client->ID, 'pays', true);
        ?>
        <tr>
            <td><?php echo esc_html($type_client); ?></td>
            <td><?php echo esc_html($denomination); ?></td>
            <td><?php echo esc_html($nom); ?></td>
            <td><?php echo esc_html($prenom); ?></td>
            <td><?php echo esc_html($email); ?></td>
            <td><?php echo esc_html($billing_phone); ?></td>
            <td><?php echo esc_html($fax); ?></td>
            <td><?php echo esc_html($billing_address_1); ?></td>
            <td><?php echo esc_html($ville); ?></td>
            <td><?php echo esc_html($code_postal); ?></td>
            <td><?php echo esc_html($pays); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- jQuery et DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
jQuery(document).ready(function($) {
    $('#myTable').DataTable( {
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

<br><br>
<h2>Ajouter un client</h2>
<?php
    $pays_liste = [
        'AL' => 'Albanie',
        'DE' => 'Allemagne',
        'AD' => 'Andorre',
        'AT' => 'Autriche',
        'BE' => 'Belgique',
        'BY' => 'Biélorussie',
        'BA' => 'Bosnie-Herzégovine',
        'BG' => 'Bulgarie',
        'HR' => 'Croatie',
        'DK' => 'Danemark',
        'ES' => 'Espagne',
        'EE' => 'Estonie',
        'FI' => 'Finlande',
        'FR' => 'France',
        'GR' => 'Grèce',
        'HU' => 'Hongrie',
        'IE' => 'Irlande',
        'IS' => 'Islande',
        'IT' => 'Italie',
        'XK' => 'Kosovo',
        'LV' => 'Lettonie',
        'LI' => 'Liechtenstein',
        'LT' => 'Lituanie',
        'LU' => 'Luxembourg',
        'MK' => 'Macédoine du Nord',
        'MT' => 'Malte',
        'MD' => 'Moldavie',
        'MC' => 'Monaco',
        'ME' => 'Montenegro',
        'NO' => 'Norvège',
        'NL' => 'Pays-Bas',
        'PL' => 'Pologne',
        'PT' => 'Portugal',
        'CZ' => 'République Tchèque',
        'RO' => 'Roumanie',
        'GB' => 'Royaume-Uni (UK)',
        'RU' => 'Russie',
        'SM' => 'San Marino',
        'RS' => 'Serbie',
        'SK' => 'Slovaquie',
        'SI' => 'Slovénie',
        'SE' => 'Suède',
        'CH' => 'Suisse',
        'UA' => 'Ukraine',
        'VA' => 'Vatican',
        'AX' => 'Åland Islands',
        'GG' => 'Guernesey',
        'JE' => 'Jersey',
        'IM' => 'Île de Man',
        'FO' => 'Îles Féroé',
        'GI' => 'Gibraltar',
        'SJ' => 'Svalbard et Jan Mayen',
    ];
?>
<form method="post" class="woocommerce-EditAccountForm">
    
    <p class="form-row ">
        <label for="type_client">Type de client <span class="required">*</span></label>
        <select name="type_client" id="type_client" required>
            <option value="">-- Sélectionner --</option>
            <option value="particulier">Particulier</option>
            <option value="professionnel">Professionnel</option>
            <option value="association_ou_institution">Association ou Institution</option>
        </select>
    </p>

    <p class="form-row ">
        <label for="denomination">Dénomination sociale <span class="required">*</span></label>
        <input type="text" maxlength="100" name="denomination" id="denomination" required class="woocommerce-Input woocommerce-Input--text input-text"/>
    </p>
    <div class="clear"></div>

    <p class="form-row ">
        <label for="genre">Genre</label>
        <select name="genre" id="genre" class="woocommerce-Input woocommerce-Input--text input-text">
            <option value="">--</option>
            <option value="m">Masculin</option>
            <option value="f">Féminin</option>
        </select>
    </p>

    <p class="form-row ">
        <label for="nom">Nom <span class="required">*</span></label>
        <input type="text" maxlength="50" name="nom" id="nom" required class="woocommerce-Input woocommerce-Input--text input-text"/>
    </p>

    <p class="form-row ">
        <label for="prenom">Prénom <span class="required">*</span></label>
        <input type="text" maxlength="50" name="prenom" id="prenom" required class="woocommerce-Input woocommerce-Input--text input-text"/>
    </p>
    <div class="clear"></div>

    <p class="form-row">
        <label for="email">Adresse email <span class="required">*</span></label>
        <input type="email" maxlength="70" name="email" id="email" required class="woocommerce-Input woocommerce-Input--text input-text"/>
    </p>

    <p class="form-row ">
        <label for="billing_phone">Téléphone <span class="required">*</span></label>
        <input type="text" maxlength="20" name="billing_phone" id="billing_phone" required class="woocommerce-Input woocommerce-Input--text input-text"/>
    </p>

    <p class="form-row ">
        <label for="fax">Fax</label>
        <input type="text" name="fax" id="fax" class="woocommerce-Input woocommerce-Input--text input-text"/>
    </p>
    <div class="clear"></div>

    <p class="form-row">
        <label for="billing_address_1">Adresse <span class="required">*</span></label>
        <input type="text" maxlength="70" name="billing_address_1" id="billing_address_1" required class="woocommerce-Input woocommerce-Input--text input-text"/>
    </p>

    <p class="form-row ">
        <label for="ville">Ville <span class="required">*</span></label>
        <input type="text" maxlength="30" name="ville" id="ville" required class="woocommerce-Input woocommerce-Input--text input-text"/>
    </p>

    <p class="form-row ">
        <label for="code_postal">Code postal <span class="required">*</span></label>
        <input type="text" maxlength="6" name="code_postal" id="code_postal" required class="woocommerce-Input woocommerce-Input--text input-text"/>
    </p>
    <div class="clear"></div>

    <p class="form-row">
        <label for="pays">Pays <span class="required">*</span></label>
        
        <select name="pays" id="pays" required class="woocommerce-Input woocommerce-Input--text input-text">
			<?php foreach ( $pays_liste as $code => $nom ) : ?>
				<option value="<?php echo esc_attr($code); ?>" >
					<?php echo esc_html($nom); ?>
				</option>
			<?php endforeach; ?>
		</select>
    </p>

    <?php wp_nonce_field('ajout_client_nonce', 'ajout_client_nonce_field'); ?>

    <p>
        <button type="submit" name="submit_ajout_client" class="woocommerce-Button button">
            Ajouter le client
        </button>
    </p>

</form>