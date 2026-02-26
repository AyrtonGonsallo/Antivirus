<?php
/**
 * Customer new account email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-new-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 10.0.0
 */

use Automattic\WooCommerce\Utilities\FeaturesUtil;

defined( 'ABSPATH' ) || exit;

$email_improvements_enabled = FeaturesUtil::feature_is_enabled( 'email_improvements' );

/**
 * Fires to output the email header.
 *
 * @hooked WC_Emails::email_header()
 *
 * @since 3.7.0
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); 

$user = get_user_by('login', $user_login);
$user_id = $user->ID;

$civilite    = strtolower(get_user_meta($user_id, 'civilite', true));
$prenom     = $user->first_name;
$nom        = $user->last_name;

?>


<?php echo '<p class="email-logo-text">Bienvenue chez Avast !</p>'; ?>

<p><span style="font-weight: 400;">Bonjour <?php echo $civilite ;?> <?php echo $nom ;?> <?php echo $prenom ;?>,</span></p>
<p><span style="font-weight: 400;">Vous venez de cr&eacute;er votre compte Avast et nous vous en remercions&nbsp;!</span></p>
<p>&nbsp;</p>
<p style="text-align:center;"> 
	<a style="color:#000000;padding: 16px 20px;background-color:#ff7800;font-size: 13px;font-weight: bold;text-decoration:none" href=<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) );?> target="_blank">
		Accéder à mon compte client
	</a>
</p>

<p>&nbsp;</p>
<p><strong>Votre compte Avast vous permet de&nbsp;:</strong></p>
<ul>
<li style="font-weight: 400;"><span style="font-weight: 400;">- Demander un devis</span></li>
<li style="font-weight: 400;"><span style="font-weight: 400;">- Suivre vos commandes en cours</span></li>
<li style="font-weight: 400;"><span style="font-weight: 400;">- Acc&eacute;der &agrave; l&rsquo;historique de vos factures</span></li>
<li style="font-weight: 400;"><span style="font-weight: 400;">- Mettre &agrave; jour vos coordonn&eacute;es</span></li>
<li style="font-weight: 400;"><span style="font-weight: 400;">- G&eacute;rer vos informations personnelles</span></li>
</ul>
<p>&nbsp;</p>
<p><span style="font-weight: 400;">Merci de votre confiance et &agrave; tr&egrave;s bient&ocirc;t,</span></p>
<p><span style="font-weight: 400;">L&rsquo;Equipe AVAST</span></p>
<?php

/**
 * Fires to output the email footer.
 *
 * @hooked WC_Emails::email_footer()
 *
 * @since 3.7.0
 */
do_action( 'woocommerce_email_footer', $email );