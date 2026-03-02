<?php
/**
 * Edit account form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-edit-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.7.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Hook - woocommerce_before_edit_account_form.
 *
 * @since 2.6.0
 */
do_action( 'woocommerce_before_edit_account_form' );
?>


<form class="woocommerce-EditAccountForm edit-account" action="" method="post" <?php do_action( 'woocommerce_edit_account_form_tag' ); ?> >

	<?php do_action( 'woocommerce_edit_account_form_start' ); 
	$user_id = get_current_user_id();
		$user = wp_get_current_user();
		$billing_address = get_user_meta($user_id, 'billing_address_1', true);
		$billing_phone   = get_user_meta($user_id, 'billing_phone', true);
		$civilite   = get_user_meta($user_id, 'civilite', true);
		$optin_promos    = get_user_meta($user_id, 'optin_promos', true);
		$optin_expiration = get_user_meta($user_id, 'optin_expiration', true);
		$account_regime_tva = get_user_meta($user->ID, 'new_revendeur_account_regime_tva', true);
		$selected_regime=($account_regime_tva=="HT")?1:2;
        $account_prefixe_tva = get_user_meta($user->ID, 'new_revendeur_account_prefixe_tva', true);
        $account_tva_intra = get_user_meta($user->ID, 'new_revendeur_account_tva_intra', true);

		$type_client = get_user_meta($user_id, 'type_client', true);
       

		$denomination = get_user_meta($user_id, 'denomination', true);
		$ville = get_user_meta($user_id, 'ville', true);
		$code_postal = get_user_meta($user_id, 'code_postal', true);
		$selected_pays = get_user_meta($user_id, 'pays', true);

		$pays_par_groupe = [
			'Pays de l\'Europe' => [
				['value' => 'DE', 'nom' => 'Allemagne'],
				['value' => 'AT', 'nom' => 'Autriche'],
				['value' => 'BE', 'nom' => 'Belgique'],
				['value' => 'BG', 'nom' => 'Bulgarie'],
				['value' => 'CY', 'nom' => 'Chypre'],
				['value' => 'DK', 'nom' => 'Danemark'],
				['value' => 'ES', 'nom' => 'Espagne'],
				['value' => 'EE', 'nom' => 'Estonie'],
				['value' => 'FI', 'nom' => 'Finlande'],
				['value' => 'FR', 'nom' => 'France'],
				['value' => 'GR', 'nom' => 'Grèce'],
				['value' => 'HU', 'nom' => 'Hongrie'],
				['value' => 'IE', 'nom' => 'Irlande'],
				['value' => 'IT', 'nom' => 'Italie'],
				['value' => 'LV', 'nom' => 'Lettonie'],
				['value' => 'LT', 'nom' => 'Lituanie'],
				['value' => 'LU', 'nom' => 'Luxembourg'],
				['value' => 'MT', 'nom' => 'Malte'],
				['value' => 'MC', 'nom' => 'Monaco'],
				['value' => 'NL', 'nom' => 'Pays-Bas'],
				['value' => 'PL', 'nom' => 'Pologne'],
				['value' => 'PT', 'nom' => 'Portugal'],
				['value' => 'RO', 'nom' => 'Roumanie'],
				['value' => 'GB', 'nom' => 'Royaume-Uni'],
				['value' => 'CZ', 'nom' => 'République tchèque'],
				['value' => 'SK', 'nom' => 'Slovaquie'],
				['value' => 'SI', 'nom' => 'Slovénie'],
				['value' => 'SE', 'nom' => 'Suède'],
			],
			'Les DOM-TOM' => [
				['value' => 'TF', 'nom' => 'Antarctique'], // Terres Australes Françaises
				['value' => 'GP', 'nom' => 'Guadeloupe'],
				['value' => 'GF', 'nom' => 'Guyane francaise'],
				['value' => 'MQ', 'nom' => 'Martinique'],
				['value' => 'YT', 'nom' => 'Mayotte'],
				['value' => 'NC', 'nom' => 'Nouvelle Calédonie'],
				['value' => 'PF', 'nom' => 'Polynésie francaise'],
				['value' => 'RE', 'nom' => 'Réunion (La)'],
				['value' => 'PM', 'nom' => 'Saint Pierre et Miquelon'],
				['value' => 'WF', 'nom' => 'Wallis et Futuna (Iles)'],
			],
			'Les pays Hors UE' => [
				['value' => 'AF', 'nom' => 'Afghanistan'],
				['value' => 'ZA', 'nom' => 'Afrique du sud'],
				['value' => 'AL', 'nom' => 'Albanie'],
				['value' => 'DZ', 'nom' => 'Algérie'],
				['value' => 'AD', 'nom' => 'Andorre'],
				['value' => 'AO', 'nom' => 'Angola'],
				['value' => 'AI', 'nom' => 'Anguilla'],
				['value' => 'AQ', 'nom' => 'Antarctique'],
				['value' => 'AG', 'nom' => 'Antigua et Barbuda'],
				['value' => 'AW', 'nom' => 'Aruba'],
				['value' => 'SA', 'nom' => 'Arabie saoudite'],
				['value' => 'AR', 'nom' => 'Argentine'],
				['value' => 'AM', 'nom' => 'Arménie'],
				['value' => 'AW', 'nom' => 'Aruba'],
				['value' => 'AU', 'nom' => 'Australie'],
				['value' => 'AZ', 'nom' => 'Azerbaïdjan'],
				['value' => 'BS', 'nom' => 'Bahamas'],
				['value' => 'BH', 'nom' => 'Bahreïn'],
				['value' => 'BD', 'nom' => 'Bangladesh'],
				['value' => 'BB', 'nom' => 'Barbades'],
				['value' => 'BZ', 'nom' => 'Belize'],
				['value' => 'BM', 'nom' => 'Bermudes (Les)'],
				['value' => 'BT', 'nom' => 'Bhoutan'],
				['value' => 'BO', 'nom' => 'Bolivie'],
				['value' => 'BA', 'nom' => 'Bosnie-Herzégovine'],
				['value' => 'BW', 'nom' => 'Botswana'],
				['value' => 'BV', 'nom' => 'Bouvet (Iles)'],
				['value' => 'BN', 'nom' => 'Brunei Darussalam'],
				['value' => 'BR', 'nom' => 'Brésil'],
				['value' => 'BF', 'nom' => 'Burkina Faso'],
				['value' => 'BI', 'nom' => 'Burundi'],
				['value' => 'BY', 'nom' => 'Bélarus (Biélorussie)'],
				['value' => 'BJ', 'nom' => 'Bénin'],
				['value' => 'KH', 'nom' => 'Cambodge'],
				['value' => 'CM', 'nom' => 'Cameroun'],
				['value' => 'CA', 'nom' => 'Canada'],
				['value' => 'CV', 'nom' => 'Cap Vert'],
				['value' => 'KY', 'nom' => 'Cayman (Iles)'],
				['value' => 'CL', 'nom' => 'Chili'],
				['value' => 'CN', 'nom' => 'Chine'],
				['value' => 'CX', 'nom' => 'Christmas (Ile)'],
				['value' => 'CC', 'nom' => 'Cocos (Iles)'],
				['value' => 'CO', 'nom' => 'Colombie'],
				['value' => 'KM', 'nom' => 'Comores'],
				['value' => 'CK', 'nom' => 'Cook (Iles)'],
				['value' => 'KP', 'nom' => 'Corée du Nord'],
				['value' => 'KR', 'nom' => 'Corée du Sud'],
				['value' => 'CR', 'nom' => 'Costa Rica'],
				['value' => 'CI', 'nom' => 'Cote d\'Ivoire'],
				['value' => 'HR', 'nom' => 'Croatie'],
				['value' => 'CU', 'nom' => 'Cuba'],
				['value' => 'CW', 'nom' => 'Curaçao'],
				['value' => 'DJ', 'nom' => 'Djibouti'],
				['value' => 'DM', 'nom' => 'Dominique'],
				['value' => 'EG', 'nom' => 'Egypte'],
				['value' => 'SV', 'nom' => 'El Salvador'],
				['value' => 'AE', 'nom' => 'Emirats Arabes Unis'],
				['value' => 'EC', 'nom' => 'Equateur'],
				['value' => 'ER', 'nom' => 'Erythrée'],
				['value' => 'US', 'nom' => 'Etats-Unis'],
				['value' => 'ET', 'nom' => 'Ethiopie'],
				['value' => 'FK', 'nom' => 'Falkland (Ile)'],
				['value' => 'FJ', 'nom' => 'Fidji (République des)'],
				['value' => 'GA', 'nom' => 'Gabon'],
				['value' => 'GM', 'nom' => 'Gambie'],
				['value' => 'GH', 'nom' => 'Ghana'],
				['value' => 'GI', 'nom' => 'Gibraltar'],
				['value' => 'GD', 'nom' => 'Grenade'],
				['value' => 'GL', 'nom' => 'Groenland'],
				['value' => 'GU', 'nom' => 'Guam'],
				['value' => 'GT', 'nom' => 'Guatemala'],
				['value' => 'GG', 'nom' => 'Guernesey'],
				['value' => 'GN', 'nom' => 'Guinée'],
				['value' => 'GQ', 'nom' => 'Guinée Equatoriale'],
				['value' => 'GW', 'nom' => 'Guinée-Bissau'],
				['value' => 'GE', 'nom' => 'Géorgie'],
				['value' => 'GS', 'nom' => 'Géorgie du Sud et Sandwich du Sud (Iles)'],
				['value' => 'HT', 'nom' => 'Haïti'],
				['value' => 'HM', 'nom' => 'Heard et McDonald (Iles)'],
				['value' => 'HN', 'nom' => 'Honduras'],
				['value' => 'HK', 'nom' => 'Hong Kong'],
				['value' => 'UM', 'nom' => 'Iles Mineures éloignées des Etats-Unis'],
				['value' => 'IN', 'nom' => 'Inde'],
				['value' => 'ID', 'nom' => 'Indonésie'],
				['value' => 'IQ', 'nom' => 'Irak'],
				['value' => 'IR', 'nom' => 'Iran'],
				['value' => 'IS', 'nom' => 'Islande'],
				['value' => 'IL', 'nom' => 'Israel'],
				['value' => 'JM', 'nom' => 'Jamaïque'],
				['value' => 'JP', 'nom' => 'Japon'],
				['value' => 'JE', 'nom' => 'Jersey'],
				['value' => 'JO', 'nom' => 'Jordanie'],
				['value' => 'KZ', 'nom' => 'Kazakhstan'],
				['value' => 'KE', 'nom' => 'Kenya'],
				['value' => 'KG', 'nom' => 'Kirghizistan'],
				['value' => 'KI', 'nom' => 'Kiribati'],
				['value' => 'KW', 'nom' => 'Koweït'],
				['value' => 'BB', 'nom' => 'La Barbade'],
				['value' => 'LA', 'nom' => 'Laos'],
				['value' => 'LS', 'nom' => 'Lesotho'],
				['value' => 'LB', 'nom' => 'Liban'],
				['value' => 'LY', 'nom' => 'Libye'],
				['value' => 'LR', 'nom' => 'Libéria'],
				['value' => 'LI', 'nom' => 'Liechtenstein'],
				['value' => 'MO', 'nom' => 'Macao'],
				['value' => 'MK', 'nom' => 'Macédoine du Nord'],
				['value' => 'MG', 'nom' => 'Madagascar'],
				['value' => 'MY', 'nom' => 'Malaisie'],
				['value' => 'MW', 'nom' => 'Malawi'],
				['value' => 'MV', 'nom' => 'Maldives (Iles)'],
				['value' => 'ML', 'nom' => 'Mali'],
				['value' => 'MP', 'nom' => 'Mariannes du Nord (Iles)'],
				['value' => 'MA', 'nom' => 'Maroc'],
				['value' => 'MH', 'nom' => 'Marshall (Iles)'],
				['value' => 'MU', 'nom' => 'Maurice'],
				['value' => 'MR', 'nom' => 'Mauritanie'],
				['value' => 'MX', 'nom' => 'Mexique'],
				['value' => 'MD', 'nom' => 'Moldavie'],
				['value' => 'MN', 'nom' => 'Mongolie'],
				['value' => 'ME', 'nom' => 'Montenegro'],
				['value' => 'MS', 'nom' => 'Montserrat'],
				['value' => 'MZ', 'nom' => 'Mozambique'],
				['value' => 'MM', 'nom' => 'Myanmar'],
				['value' => 'NA', 'nom' => 'Namibie'],
				['value' => 'NR', 'nom' => 'Nauru'],
				['value' => 'NP', 'nom' => 'Népal'],
				['value' => 'NI', 'nom' => 'Nicaragua'],
				['value' => 'NE', 'nom' => 'Niger'],
				['value' => 'NG', 'nom' => 'Nigeria'],
				['value' => 'NU', 'nom' => 'Niué'],
				['value' => 'NF', 'nom' => 'Norfolk (Iles)'],
				['value' => 'NO', 'nom' => 'Norvège'],
				['value' => 'NZ', 'nom' => 'Nouvelle-Zélande'],
				['value' => 'OM', 'nom' => 'Oman'],
				['value' => 'UG', 'nom' => 'Ouganda'],
				['value' => 'UZ', 'nom' => 'Ouzbékistan'],
				['value' => 'PK', 'nom' => 'Pakistan'],
				['value' => 'PW', 'nom' => 'Palau'],
				['value' => 'PA', 'nom' => 'Panama'],
				['value' => 'PG', 'nom' => 'Papouasie Nouvelle-Guinée'],
				['value' => 'PY', 'nom' => 'Paraguay'],
				['value' => 'PH', 'nom' => 'Philippines'],
				['value' => 'PN', 'nom' => 'Pitcairn (Iles)'],
				['value' => 'PR', 'nom' => 'Porto Rico'],
				['value' => 'PE', 'nom' => 'Pérou'],
				['value' => 'QA', 'nom' => 'Qatar'],
				['value' => 'RU', 'nom' => 'Russie'],
				['value' => 'RW', 'nom' => 'Rwanda'],
				['value' => 'DO', 'nom' => 'République Dominicaine'],
				['value' => 'CD', 'nom' => 'République Démocratique du Congo'],
				['value' => 'CF', 'nom' => 'République centrafricaine'],
				['value' => 'EH', 'nom' => 'Sahara Occidental'],
				['value' => 'VC', 'nom' => 'Saint Vincent et les Grenadines'],
				['value' => 'KN', 'nom' => 'Saint-Kitts et Nevis'],
				['value' => 'SM', 'nom' => 'Saint-Marin'],
				['value' => 'VA', 'nom' => 'Saint-Siège (Cité du Vatican)'],
				['value' => 'SH', 'nom' => 'Sainte Hélène'],
				['value' => 'LC', 'nom' => 'Sainte Lucie'],
				['value' => 'WS', 'nom' => 'Samoa'],
				['value' => 'ST', 'nom' => 'Sao Tomé et Principe (Rép.)'],
				['value' => 'RS', 'nom' => 'Serbie'],
				['value' => 'SC', 'nom' => 'Seychelles'],
				['value' => 'SL', 'nom' => 'Sierra Leone'],
				['value' => 'SG', 'nom' => 'Singapour'],
				['value' => 'SO', 'nom' => 'Somalie'],
				['value' => 'SD', 'nom' => 'Soudan'],
				['value' => 'LK', 'nom' => 'Sri Lanka'],
				['value' => 'CH', 'nom' => 'Suisse'],
				['value' => 'SR', 'nom' => 'Suriname'],
				['value' => 'SJ', 'nom' => 'Svalbard et Jan Mayen (Iles)'],
				['value' => 'SZ', 'nom' => 'Swaziland (Eswatini)'],
				['value' => 'SY', 'nom' => 'Syrie'],
				['value' => 'SN', 'nom' => 'Sénégal'],
				['value' => 'TJ', 'nom' => 'Tadjikistan'],
				['value' => 'TZ', 'nom' => 'Tanzanie'],
				['value' => 'TW', 'nom' => 'Taïwan'],
				['value' => 'TD', 'nom' => 'Tchad'],
				['value' => 'IO', 'nom' => 'Territoire britannique de l\'océan Indien'],
				['value' => 'PS', 'nom' => 'Territoires Palestiniens'],
				['value' => 'TH', 'nom' => 'Thaïlande'],
				['value' => 'TL', 'nom' => 'Timor-Leste'],
				['value' => 'TG', 'nom' => 'Togo'],
				['value' => 'TK', 'nom' => 'Tokelau'],
				['value' => 'TO', 'nom' => 'Tonga'],
				['value' => 'TT', 'nom' => 'Trinité et Tobago'],
				['value' => 'TN', 'nom' => 'Tunisie'],
				['value' => 'TM', 'nom' => 'Turkménistan'],
				['value' => 'TC', 'nom' => 'Turks et Caïques (Iles)'],
				['value' => 'TR', 'nom' => 'Turquie'],
				['value' => 'TV', 'nom' => 'Tuvalu'],
				['value' => 'UA', 'nom' => 'Ukraine'],
				['value' => 'UY', 'nom' => 'Uruguay'],
				['value' => 'VU', 'nom' => 'Vanuatu'],
				['value' => 'VG', 'nom' => 'Vierges britanniques (Iles)'],
				['value' => 'VI', 'nom' => 'Vierges américaines (Iles)'],
				['value' => 'VN', 'nom' => 'Viet Nam'],
				['value' => 'VE', 'nom' => 'Vénézuela'],
				['value' => 'YE', 'nom' => 'Yémen'],
				['value' => 'ZM', 'nom' => 'Zambie'],
				['value' => 'ZW', 'nom' => 'Zimbabwe'],
				['value' => 'FM', 'nom' => 'États Fédérés de Micronésie'],
				['value' => 'IM', 'nom' => 'Île de Man'],
				['value' => 'SB', 'nom' => 'Îles Salomon'],
			]
		];
		
	 
	
		$user_id = get_current_user_id();
		$user = wp_get_current_user();
		$user_roles = $user->roles; // array de tous les rôles
		if (in_array('customer_particulier', $user_roles)) {
			$role = 'customer_particulier';
		} elseif (in_array('customer_revendeur', $user_roles)) {
			$role = 'customer_revendeur';
		} else {
			$role = ''; // fallback
		}
	?>
	<p class="form-row">
		<label for="type_client">Type de client</label>

		<select id="type_client" disabled>
			<option value="">-- Sélectionner --</option>
			<option value="particulier" <?php selected($type_client, 'particulier'); ?>>Particulier</option>
			<option value="professionnel" <?php selected($type_client, 'professionnel'); ?>>Professionnel</option>
			<option value="entreprise">Entreprise</option>
			<option value="association_ou_institution" <?php selected($type_client, 'association_ou_institution'); ?>>Association ou Institution</option>
		</select>

		<!--  valeur envoyée quand même -->
		<input type="hidden" name="type_client" value="<?php echo esc_attr($type_client); ?>">
	</p>
	<p class="form-row ">
        <label for="denomination">Dénomination sociale <span class="required">*</span></label>
        <input type="text" maxlength="100" name="denomination" id="denomination" class="woocommerce-Input woocommerce-Input--text input-text" required value="<?php echo esc_attr($denomination); ?>"/>
    </p>
	
	<div class="clear"></div>
	<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
		<label for="account_first_name"><?php esc_html_e( 'First name', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
		<input type="text" maxlength="50" class="woocommerce-Input woocommerce-Input--text input-text" name="account_first_name" id="account_first_name" autocomplete="given-name" value="<?php echo esc_attr( $user->first_name ); ?>" aria-required="true" />
	</p>
	<p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
		<label for="account_last_name"><?php esc_html_e( 'Last name', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
		<input type="text" maxlength="50" class="woocommerce-Input woocommerce-Input--text input-text" name="account_last_name" id="account_last_name" autocomplete="family-name" value="<?php echo esc_attr( $user->last_name ); ?>" aria-required="true" />
	</p>
	<div class="clear"></div>
 	<p class="woocommerce-form-row form-row">
        <label for="billing_address_1">Adresse</label>
		<input type="text" maxlength="100" name="billing_address_1" id="billing_address_1" class="woocommerce-Input woocommerce-Input--text input-text" value="<?php echo esc_attr($billing_address); ?>">
    </p>

    <p class="woocommerce-form-row  form-row">
        <label for="billing_phone">Téléphone</label>
		<input type="text" maxlength="20" name="billing_phone" id="billing_phone" class="woocommerce-Input woocommerce-Input--text input-text" value="<?php echo esc_attr($billing_phone); ?>">
    </p>
	<div class="clear"></div>
	 <p class="form-row ">
        <label for="ville">Ville <span class="required">*</span></label>
        <input type="text" name="ville" maxlength="50" id="ville" class="woocommerce-Input woocommerce-Input--text input-text" required value="<?php echo esc_attr($ville); ?>"/>
    </p>
<div class="clear"></div>
    <p class="form-row ">
        <label for="code_postal">Code postal <span class="required">*</span></label>
        <input type="text" name="code_postal" maxlength="6" id="code_postal" class="woocommerce-Input woocommerce-Input--text input-text" required value="<?php echo esc_attr($code_postal); ?>"/>
    </p>
    <div class="clear"></div>

    <p class="form-row">
		<label for="pays">Pays <span class="required">*</span></label>
		<select name="pays" id="pays" required class="woocommerce-Input woocommerce-Input--text input-text">
			<?php foreach ( $pays_par_groupe as $groupe => $pays ) : ?>
				<optgroup label="<?php echo esc_attr($groupe); ?>">
					<?php foreach ( $pays as $pays_data ) : ?>
						<option value="<?php echo esc_attr($pays_data['value']); ?>" 
							<?php selected($selected_pays, $pays_data['value']); ?>>
							<?php echo esc_html($pays_data['nom']); ?>
						</option>
					<?php endforeach; ?>
				</optgroup>
			<?php endforeach; ?>
		</select>
	</p>
<div class="clear"></div>
	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
		<label for="account_display_name"><?php esc_html_e( 'Display name', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
		<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_display_name" id="account_display_name" aria-describedby="account_display_name_description" value="<?php echo esc_attr( $user->display_name ); ?>" aria-required="true" /> <span id="account_display_name_description"><em><?php esc_html_e( 'This will be how your name will be displayed in the account section and in reviews', 'woocommerce' ); ?></em></span>
	</p>
	<div class="clear"></div>

	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
		<label for="account_email"><?php esc_html_e( 'Email address', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
		<input type="email" maxlength="70" class="woocommerce-Input woocommerce-Input--email input-text" name="account_email" id="account_email" autocomplete="email" value="<?php echo esc_attr( $user->user_email ); ?>" aria-required="true" />
	</p>
	
  	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="role">Type de compte</label>
        <select  id="role" required disabled>
			<option value="">Sélectionnez...</option>
			<option value="customer_particulier" <?php selected($role,'customer_particulier'); ?>>Particulier</option>
			<option value="customer_revendeur" <?php selected($role,'customer_revendeur'); ?>>Revendeur</option>
		</select>
    </p>

	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="civilite">Civilité <span class="required">*</span></label>
        <select name="civilite" id="civilite" required >
			<option value="">Sélectionnez...</option>
			<option value="Monsieur" <?php selected($civilite,'Monsieur'); ?>>Monsieur</option>
			<option value="Madame" <?php selected($civilite,'Madame'); ?>>Madame</option>
			<option value="Mademoiselle" <?php selected($civilite,'Mademoiselle'); ?>>Mademoiselle</option>
		</select>
    </p>

	<?php 
		if($role == 'customer_revendeur'){
			$account_regime_tva = get_user_meta($user->ID, 'new_revendeur_account_regime_tva', true);
			$selected_regime=($account_regime_tva=="HT")?3:2;
			$selected_regime=($account_regime_tva=="HT_UE")?1:$selected_regime;
			$account_prefixe_tva = get_user_meta($user->ID, 'new_revendeur_account_prefixe_tva', true);
			$account_tva_intra = get_user_meta($user->ID, 'new_revendeur_account_tva_intra', true);
		}else{

			$account_regime_tva = get_user_meta($user->ID, 'new_account_regime_tva', true);
			$selected_regime=($account_regime_tva=="HT")?3:2;
			$selected_regime=($account_regime_tva=="HT_UE")?1:$selected_regime;
			$account_prefixe_tva = get_user_meta($user->ID, 'new_account_prefixe_tva', true);
			$account_tva_intra = get_user_meta($user->ID, 'new_account_tva_intra', true);
		}
		?>
		<div id="boxtva" name="boxtva" >            
			<b>Régime de TVA applicable :<span class="required">*</span></b>
			<br><br>
			
			
			<div id="fact_tva" style="width: 100%; display: flex; align-items: flex-start; gap: 7px;">
				<input type="radio" id="regime_2"  <?php echo (isset($selected_regime) && $selected_regime == 2) ? 'checked' : ''; ?> name="new_revendeur_account_regime_tva" value="2" style="width: auto" >
				<label style="line-height: 1.5;"><b>Facturation TTC faisant ressortir la TVA</b> (pays de l'union) Facturation avec TVA de 20%</label>
			</div>

			<div id="fact_ht_ue_hf">
				<div style="display: flex;align-items: flex-start;justify-content: flex-start;gap: 7px;">
					<input type="radio" id="regime_1"  <?php echo (isset($selected_regime) && $selected_regime == 1) ? 'checked' : ''; ?> name="new_revendeur_account_regime_tva" value="1" style="width: auto" >
					<label style="line-height: 1.5;"><b>Facturation HT</b> (pour les pays de l'union Européenne, hors France) Merci de justifier ci dessous d'un numéro de TVA Intra valide :</label>
				</div>
				<div id="tva_regime_1_box">
					<div id="tva_regime_1_box2" class="w-100">
						N° TVA intracommunautaire:
						<select title="Prefixe TVA" id="prefixe_tva" name="new_revendeur_account_prefixe_tva" alt="Prefixe TVA">
							<option <?php echo empty($account_prefixe_tva) ? 'selected' : ''; ?> value="" alt="Prefixe TVA">--</option>
							<option value="AT"  <?php echo ($account_prefixe_tva === 'AT') ? 'selected' : ''; ?> alt="Prefixe TVA">AT</option>
							<option value="BE"  <?php echo ($account_prefixe_tva === 'BE') ? 'selected' : ''; ?> alt="Prefixe TVA">BE</option>
							<option value="BG"  <?php echo ($account_prefixe_tva === 'BG') ? 'selected' : ''; ?> alt="Prefixe TVA">BG</option>
							<option value="CY"  <?php echo ($account_prefixe_tva === 'CY') ? 'selected' : ''; ?> alt="Prefixe TVA">CY</option>
							<option value="CZ"  <?php echo ($account_prefixe_tva === 'CZ') ? 'selected' : ''; ?> alt="Prefixe TVA">CZ</option>
							<option value="DE"  <?php echo ($account_prefixe_tva === 'DE') ? 'selected' : ''; ?> alt="Prefixe TVA">DE</option>
							<option value="DK"  <?php echo ($account_prefixe_tva === 'DK') ? 'selected' : ''; ?> alt="Prefixe TVA">DK</option>
							<option value="EE"  <?php echo ($account_prefixe_tva === 'EE') ? 'selected' : ''; ?> alt="Prefixe TVA">EE</option>
							<option value="EL"  <?php echo ($account_prefixe_tva === 'EL') ? 'selected' : ''; ?> alt="Prefixe TVA">EL</option>
							<option value="ES"  <?php echo ($account_prefixe_tva === 'ES') ? 'selected' : ''; ?> alt="Prefixe TVA">ES</option>
							<option value="FI"  <?php echo ($account_prefixe_tva === 'FI') ? 'selected' : ''; ?> alt="Prefixe TVA">FI</option>
							<option value="FR"  <?php echo ($account_prefixe_tva === 'FR') ? 'selected' : ''; ?> alt="Prefixe TVA">FR</option>
							<option value="HU"  <?php echo ($account_prefixe_tva === 'HU') ? 'selected' : ''; ?> alt="Prefixe TVA">HU</option>
							<option value="IE"  <?php echo ($account_prefixe_tva === 'IE') ? 'selected' : ''; ?> alt="Prefixe TVA">IE</option>
							<option value="IT"  <?php echo ($account_prefixe_tva === 'IT') ? 'selected' : ''; ?> alt="Prefixe TVA">IT</option>
							<option value="LT"  <?php echo ($account_prefixe_tva === 'LT') ? 'selected' : ''; ?> alt="Prefixe TVA">LT</option>
							<option value="LU"  <?php echo ($account_prefixe_tva === 'LU') ? 'selected' : ''; ?> alt="Prefixe TVA">LU</option>
							<option value="LV"  <?php echo ($account_prefixe_tva === 'LV') ? 'selected' : ''; ?> alt="Prefixe TVA">LV</option>
							<option value="MT"  <?php echo ($account_prefixe_tva === 'MT') ? 'selected' : ''; ?> alt="Prefixe TVA">MT</option>
							<option value="NL"  <?php echo ($account_prefixe_tva === 'NL') ? 'selected' : ''; ?> alt="Prefixe TVA">NL</option>
							<option value="PL"  <?php echo ($account_prefixe_tva === 'PL') ? 'selected' : ''; ?> alt="Prefixe TVA">PL</option>
							<option value="PT"  <?php echo ($account_prefixe_tva === 'PT') ? 'selected' : ''; ?> alt="Prefixe TVA">PT</option>
							<option value="RO"  <?php echo ($account_prefixe_tva === 'RO') ? 'selected' : ''; ?> alt="Prefixe TVA">RO</option>
							<option value="SE"  <?php echo ($account_prefixe_tva === 'SE') ? 'selected' : ''; ?> alt="Prefixe TVA">SE</option>
							<option value="SI"  <?php echo ($account_prefixe_tva === 'SI') ? 'selected' : ''; ?> alt="Prefixe TVA">SI</option>
							<option value="SK"  <?php echo ($account_prefixe_tva === 'SK') ? 'selected' : ''; ?> alt="Prefixe TVA">SK</option>
						</select>
						<input  style="width: auto" maxlength="60" type="text" name="new_revendeur_account_tva_intra" value="<?php echo $account_tva_intra;?>" size="25" onblur="IsRequiredOk(this)">
					</div>
					<br>
					<span style="font-size:11px;position: relative;top: -24px;"> 
						Obligatoire pour facturation Hors TVA pour les sociétés situées dans un pays de l'Union Européenne et hors de France.
					</span>
				</div>
				
			</div>
			<div id="fact_ht">
				<div style="display: flex;align-items: flex-start;justify-content: flex-start;gap: 7px;">
				<input type="radio" id="regime_3" name="account_regime_tva" <?php echo (isset($selected_regime) && $selected_regime == 3) ? 'checked' : ''; ?> value="3" style="width: auto" >
				<label style="line-height: 1.5;"><b>Facturation HT</b> </label>
				</div>
			</div>	
			<br>
			<div id="text-franchise-tva">
				<b>Franchise de TVA</b><p>
				Contactez-nous pour que nous puissions paramétrer spécifiquement votre compte, sur présentation d'un justificatif de situation, et vous permettre de passer vos commandes avec le taux de TVA qui vous est applicable.</p></td>
			</div>
		</div>
	

	 <p class="form-row">
        <label>
			<input type="checkbox" name="optin_promos" value="yes" <?php checked($optin_promos, 'yes'); ?>>
            Je souhaite recevoir par mail les informations et promotions sur Avast
        </label>
    </p>

    <p class="form-row particulier-only" style="display:none;">
        <label>
            <input type="checkbox" name="optin_expiration" value="yes" <?php checked($optin_expiration,'yes'); ?>>
            Je souhaite être informé de l’expiration de mes licences
        </label>
    </p>

	<?php
		/**
		 * Hook where additional fields should be rendered.
		 *
		 * @since 8.7.0
		 */
		do_action( 'woocommerce_edit_account_form_fields' );
	?>

	<fieldset>
		<legend><?php esc_html_e( 'Password change', 'woocommerce' ); ?></legend>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="password_current"><?php esc_html_e( 'Current password (leave blank to leave unchanged)', 'woocommerce' ); ?></label>
			<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_current" id="password_current" autocomplete="off" />
		</p>
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="password_1"><?php esc_html_e( 'New password (leave blank to leave unchanged)', 'woocommerce' ); ?></label>
			<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_1" id="password_1" autocomplete="off" />
		</p>
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="password_2"><?php esc_html_e( 'Confirm new password', 'woocommerce' ); ?></label>
			<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_2" id="password_2" autocomplete="off" />
		</p>
	</fieldset>
	<div class="clear"></div>
 
 
	<script>
		jQuery(document).ready(function($) {
			const $roleField = $('#role');
			const $expField = $('.particulier-only');
			const $selectPAYS = $('#pays');
			const $fact_tvaField = $('#fact_tva');
			const $fact_htField = $('#fact_ht');
			const $fact_ht_ue_hfField = $('#fact_ht_ue_hf');


			function toggleOptinExpiration() {
				if ($roleField.val() === 'customer_particulier') {
					$expField.show();
				} else {
					$expField.hide();
				}
			}
			function get_selected_pays_or_group(){
				let selectedOption = $selectPAYS.find('option:selected');
				let paysValue = selectedOption.val();

				// 🔥 récupérer le label du optgroup parent
				let groupe = selectedOption.parent('optgroup').attr('label');

				console.log('Pays : ' + paysValue );
				console.log('Groupe : ' + groupe);

				// Exemple condition
				if(groupe === "Pays de l'Europe") {
					if(paysValue=="FR"){
						console.log('france choisie');
						$fact_tvaField.show();
						$fact_htField.hide();
						$fact_ht_ue_hfField.hide();
					}else{
						console.log('Pays Européen choisi');
						$fact_tvaField.show();
						$fact_htField.hide();
						$fact_ht_ue_hfField.show();
					}
					$('#regime_2').prop('checked', true);
					$('#text-franchise-tva').show()
				}

				if(groupe === "Les DOM-TOM") {
					console.log('DOM-TOM choisi');
					$fact_tvaField.show();
					$fact_htField.hide();
					$fact_ht_ue_hfField.show();
					$('#regime_2').prop('checked', true);
					$('#text-franchise-tva').show()
				}

				if(groupe === "Les pays Hors UE") {
					console.log('Hors UE choisi');
					$fact_tvaField.hide();
					$fact_htField.show();
					$fact_ht_ue_hfField.hide();
					$('#regime_3').prop('checked', true);
					$('#text-franchise-tva').hide()
				}
			}

			//get_selected_pays_or_group();
			$selectPAYS.on('change', get_selected_pays_or_group);

			// Au chargement initial
			toggleOptinExpiration();

			// À chaque changement
			$roleField.on('change', toggleOptinExpiration);


			$('#tva_regime_1_box').hide();
			if ($('#regime_1').is(':checked')) {
				$('#tva_regime_1_box').show();
			}

			$('input[name="new_revendeur_account_regime_tva"]').on('change', function() {

			if ($('#regime_1').is(':checked')) {
				$('#tva_regime_1_box').show();
			}

			if ($('#regime_2').is(':checked')) {
				$('#tva_regime_1_box').hide();
			}
		});
		});
	</script>

	<?php
		/**
		 * My Account edit account form.
		 *
		 * @since 2.6.0
		 */
		do_action( 'woocommerce_edit_account_form' );
	?>

	<p>
		<?php wp_nonce_field( 'save_account_details', 'save-account-details-nonce' ); ?>
		<button type="submit" class="woocommerce-Button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="save_account_details" value="<?php esc_attr_e( 'Save changes', 'woocommerce' ); ?>"><?php esc_html_e( 'Save changes', 'woocommerce' ); ?></button>
		<input type="hidden" name="action" value="save_account_details" />
	</p>

	<?php do_action( 'woocommerce_edit_account_form_end' ); ?>
</form>

<?php do_action( 'woocommerce_after_edit_account_form' ); ?>
