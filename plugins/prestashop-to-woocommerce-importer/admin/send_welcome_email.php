<?php

if (!defined('ABSPATH')) {
    exit;
}


function avast_get_country_languages(): array
{
    return [
        // ==================== FRANÇAIS (fr) ====================
        // Europe francophone
        'FR' => 'fr', // France
        'BE' => 'fr', // Belgique
        'CH' => 'fr', // Suisse (multilingue, mais fr fréquent)
        'LU' => 'fr', // Luxembourg
        'MC' => 'fr', // Monaco
        'AD' => 'fr', // Andorre (catalan officiel, mais fr courant)
        
        // Afrique francophone
        'MA' => 'fr', // Maroc
        'DZ' => 'fr', // Algérie
        'TN' => 'fr', // Tunisie
        'SN' => 'fr', // Sénégal
        'CI' => 'fr', // Côte d'Ivoire
        'CM' => 'fr', // Cameroun
        'BF' => 'fr', // Burkina Faso
        'ML' => 'fr', // Mali
        'NE' => 'fr', // Niger
        'TD' => 'fr', // Tchad
        'CF' => 'fr', // République centrafricaine
        'CG' => 'fr', // Congo
        'CD' => 'fr', // République Démocratique du Congo
        'GA' => 'fr', // Gabon
        'BJ' => 'fr', // Bénin
        'TG' => 'fr', // Togo
        'GN' => 'fr', // Guinée
        'GW' => 'fr', // Guinée-Bissau (portugais officiel, mais fr courant)
        'ML' => 'fr', // Mali
        'MR' => 'fr', // Mauritanie (arabe officiel, mais fr courant)
        'MG' => 'fr', // Madagascar
        'KM' => 'fr', // Comores
        'DJ' => 'fr', // Djibouti
        'RW' => 'fr', // Rwanda
        'BI' => 'fr', // Burundi
        'SC' => 'fr', // Seychelles
        'MU' => 'fr', // Maurice
        'RE' => 'fr', // Réunion (La)
        'YT' => 'fr', // Mayotte
        'NC' => 'fr', // Nouvelle Calédonie
        'PF' => 'fr', // Polynésie francaise
        'WF' => 'fr', // Wallis et Futuna (Iles)
        'GF' => 'fr', // Guyane francaise
        'GP' => 'fr', // Guadeloupe
        'MQ' => 'fr', // Martinique
        'PM' => 'fr', // Saint Pierre et Miquelon
        'HT' => 'fr', // Haïti (créole et fr)
        'VU' => 'fr', // Vanuatu
        'NC' => 'fr', // Nouvelle Calédonie
        
        // ==================== ANGLAIS (en) ====================
        // Amérique du Nord
        'US' => 'en', // Etats-Unis
        'CA' => 'en', // Canada
        'GB' => 'en', // Royaume-Uni
        'IE' => 'en', // Irlande
        'AU' => 'en', // Australie
        'NZ' => 'en', // Nouvelle-Zélande
        'ZA' => 'en', // Afrique du sud
        'IN' => 'en', // Inde
        'SG' => 'en', // Singapour
        'PH' => 'en', // Philippines
        'MY' => 'en', // Malaisie
        'HK' => 'en', // Hong Kong
        'PR' => 'en', // Porto Rico
        'GU' => 'en', // Guam
        'AI' => 'en', // Anguilla
        'AG' => 'en', // Antigua et Barbuda
        'BS' => 'en', // Bahamas
        'BB' => 'en', // La Barbade
        'BZ' => 'en', // Belize
        'BM' => 'en', // Bermudes (Les)
        'BW' => 'en', // Botswana
        'BN' => 'en', // Brunei Darussalam
        'KY' => 'en', // Cayman (Iles)
        'CK' => 'en', // Cook (Iles)
        'CY' => 'en', // Chypre
        'DM' => 'en', // Dominique
        'FK' => 'en', // Falkland (Ile)
        'FJ' => 'en', // Fidji (République des)
        'GM' => 'en', // Gambie
        'GH' => 'en', // Ghana
        'GI' => 'en', // Gibraltar
        'GD' => 'en', // Grenade
        'GY' => 'en', // Guyana
        'JM' => 'en', // Jamaïque
        'KE' => 'en', // Kenya
        'KI' => 'en', // Kiribati
        'LS' => 'en', // Lesotho
        'LR' => 'en', // Libéria
        'MW' => 'en', // Malawi
        'MT' => 'en', // Malte
        'MH' => 'en', // Marshall (Iles)
        'MU' => 'en', // Maurice
        'MS' => 'en', // Montserrat
        'NA' => 'en', // Namibie
        'NR' => 'en', // Nauru
        'NG' => 'en', // Nigeria
        'NU' => 'en', // Niué
        'NF' => 'en', // Norfolk (Iles)
        'MP' => 'en', // Mariannes du Nord (Iles)
        'PK' => 'en', // Pakistan
        'PW' => 'en', // Palau
        'PG' => 'en', // Papouasie Nouvelle-Guinée
        'PN' => 'en', // Pitcairn (Iles)
        'KN' => 'en', // Saint-Kitts et Nevis
        'LC' => 'en', // Sainte Lucie
        'VC' => 'en', // Saint Vincent et les Grenadines
        'WS' => 'en', // Samoa
        'SC' => 'en', // Seychelles
        'SL' => 'en', // Sierra Leone
        'SB' => 'en', // Îles Salomon
        'SH' => 'en', // Sainte Hélène
        'SZ' => 'en', // Swaziland
        'TZ' => 'en', // Tanzanie
        'TK' => 'en', // Tokelau
        'TO' => 'en', // Tonga
        'TT' => 'en', // Trinité et Tobago
        'TC' => 'en', // Turks et Caïques (Iles)
        'TV' => 'en', // Tuvalu
        'UG' => 'en', // Ouganda
        'UM' => 'en', // Iles Mineures éloignées des Etats-Unis
        'VU' => 'en', // Vanuatu
        'VG' => 'en', // Vierges britanniques (Iles)
        'VI' => 'en', // Vierges américaines (Iles)
        'ZM' => 'en', // Zambie
        'ZW' => 'en', // Zimbabwe
        'JE' => 'en', // Jersey
        'GG' => 'en', // Guernesey
        'IM' => 'en', // Île de Man
        'IO' => 'en', // Territoire britannique de l'océan Indien
        
        // ==================== ESPAGNOL (es) ====================
        'ES' => 'es', // Espagne
        'MX' => 'es', // Mexique
        'AR' => 'es', // Argentine
        'CO' => 'es', // Colombie
        'CL' => 'es', // Chili
        'PE' => 'es', // Pérou
        'VE' => 'es', // Vénézuela
        'EC' => 'es', // Equateur
        'GT' => 'es', // Guatemala
        'CU' => 'es', // Cuba
        'DO' => 'es', // République Dominicaine
        'HN' => 'es', // Honduras
        'SV' => 'es', // El Salvador
        'NI' => 'es', // Nicaragua
        'CR' => 'es', // Costa Rica
        'PA' => 'es', // Panama
        'PY' => 'es', // Paraguay
        'UY' => 'es', // Uruguay
        'BO' => 'es', // Bolivie
        'GQ' => 'es', // Guinée Equatoriale
        
        // ==================== ALLEMAND (de) ====================
        'DE' => 'de', // Allemagne
        'AT' => 'de', // Autriche
        'LI' => 'de', // Liechtenstein
        
        // ==================== ITALIEN (it) ====================
        'IT' => 'it', // Italie
        'SM' => 'it', // Saint-Marin
        'VA' => 'it', // Saint-Siège (Cité du Vatican)
        
        // ==================== PORTUGAIS (pt) ====================
        'PT' => 'pt', // Portugal
        'BR' => 'pt', // Brésil
        'AO' => 'pt', // Angola
        'MZ' => 'pt', // Mozambique
        'CV' => 'pt', // Cap Vert
        'GW' => 'pt', // Guinée-Bissau
        'ST' => 'pt', // Sao Tomé et Principe (Rép.)
        'TL' => 'pt', // Timor-Leste
        'MO' => 'pt', // Macao
        
        // ==================== AUTRES (fallback en) ====================
        // Pays dont la langue principale n'est pas dans les 6
        // On utilise l'anglais par défaut
        'AF' => 'en', // Afghanistan
        'AL' => 'en', // Albanie
        'SA' => 'en', // Arabie saoudite
        'AM' => 'en', // Arménie
        'AW' => 'en', // Aruba
        'AZ' => 'en', // Azerbaïdjan
        'BH' => 'en', // Bahreïn
        'BD' => 'en', // Bangladesh
        'BY' => 'en', // Bélarus (Biélorussie)
        'BT' => 'en', // Bhoutan
        'BA' => 'en', // Bosnie-Herzégovine
        'BV' => 'en', // Bouvet (Iles)
        'BG' => 'en', // Bulgarie
        'KH' => 'en', // Cambodge
        'CX' => 'en', // Christmas (Ile)
        'CC' => 'en', // Cocos (Iles)
        'HR' => 'en', // Croatie
        'DK' => 'en', // Danemark
        'EG' => 'en', // Egypte
        'AE' => 'en', // Emirats Arabes Unis
        'ER' => 'en', // Erythrée
        'EE' => 'en', // Estonie
        'ET' => 'en', // Ethiopie
        'FI' => 'en', // Finlande
        'GE' => 'en', // Géorgie
        'GR' => 'en', // Grèce
        'GL' => 'en', // Groenland
        'HU' => 'en', // Hongrie
        'IS' => 'en', // Islande
        'ID' => 'en', // Indonésie
        'IR' => 'en', // Iran
        'IQ' => 'en', // Irak
        'IL' => 'en', // Israel
        'JP' => 'en', // Japon
        'JO' => 'en', // Jordanie
        'KZ' => 'en', // Kazakhstan
        'KP' => 'en', // Corée du Nord
        'KR' => 'en', // Corée du Sud
        'KW' => 'en', // Koweït
        'KG' => 'en', // Kirghizistan
        'LA' => 'en', // Laos
        'LV' => 'en', // Lettonie
        'LB' => 'en', // Liban
        'LY' => 'en', // Libye
        'LT' => 'en', // Lituanie
        'MK' => 'en', // Macédoine
        'MV' => 'en', // Maldives (Iles)
        'MN' => 'en', // Mongolie
        'ME' => 'en', // Monténégro
        'MM' => 'en', // Myanmar
        'NP' => 'en', // Népal
        'NL' => 'en', // Pays-Bas (néerlandais, mais en très courant)
        'AN' => 'en', // Antilles néerlandaises
        'OM' => 'en', // Oman
        'QA' => 'en', // Qatar
        'RO' => 'en', // Roumanie
        'RU' => 'en', // Russie
        'SA' => 'en', // Arabie saoudite
        'RS' => 'en', // Serbie
        'SK' => 'en', // Slovaquie
        'SI' => 'en', // Slovénie
        'SO' => 'en', // Somalie
        'SD' => 'en', // Soudan
        'LK' => 'en', // Sri Lanka
        'SR' => 'en', // Suriname
        'SJ' => 'en', // Svalbard et Jan Mayen (Iles)
        'SE' => 'en', // Suède
        'SY' => 'en', // Syrie
        'TW' => 'en', // Taïwan
        'TJ' => 'en', // Tadjikistan
        'TH' => 'en', // Thaïlande
        'TR' => 'en', // Turquie
        'TM' => 'en', // Turkménistan
        'UA' => 'en', // Ukraine
        'UZ' => 'en', // Ouzbékistan
        'VN' => 'en', // Viet Nam
        'EH' => 'en', // Sahara Occidental
        'YE' => 'en', // Yémen
        'YU' => 'en', // Yougoslavie
        'AQ' => 'en', // Antarctique
        'HM' => 'en', // Heard et McDonald (Iles)
        'PS' => 'en', // Territoires Palestiniens
        'CZ' => 'en', // République tchèque
        'NO' => 'en', // Norvège
        'PL' => 'en', // Pologne
        'TR' => 'en', // Turquie
    ];
}


function avast_get_welcome_templates(): array
{
    $templates = [];

     $reset_url_fr = site_url().'/mon-compte/lost-password/';
     $reset_url_en = site_url().'/en/my-account/forgotten-password/';
     $reset_url_es = site_url().'/es/mi-cuenta/contrasena-olvidada/';
     $reset_url_it = site_url().'/it/il-mio-account/password-smarrita/';
     $reset_url_pt = site_url().'/pt/a-minha-conta/palavra-passe-perdida/';
     $reset_url_de = site_url().'/de/mein-konto/passwort-vergessen/';
     $lien_logo_png = site_url().'/wp-content/uploads/2025/11/avast-logo.png';

    $templates['fr'] = [
        'subject' => 'Votre compte AVAST fait peau neuve !',
        'body' => '

            <div style="
                width:100%;
                font-family:Arial, sans-serif;
            ">

                <div class="content1" style="
                    max-width:600px;
                    margin:0 auto;
                    background:white;
                    padding:30px;
                    border-radius:8px;
                    text-align:start;
                    box-shadow:0 0 10px rgba(0,0,0,0.08);
                ">

                    <div style="text-align:center;">
                    <img src="'.$lien_logo_png.'" style="border:none;display:inline-block;font-size:14px;font-weight:bold;height:auto;outline:none;text-decoration:none;text-transform:capitalize;vertical-align:middle;margin-right:24px;max-width:100%">
                    </div>

                    <h4 style="margin:20px 0;color:#FF7800;font-weight:bolder;text-align:center;font-size:18px;">Votre compte AVAST fait peau neuve !</h4>

    

                    <p>Nous sommes heureux de vous présenter la toute nouvelle version de votre boutique en ligne AVAST, conçue pour vous offrir une expérience d’achat plus simple, plus rapide et plus intuitive.</p>

                    <h3>Découvrez les nouveautés :</h3>
                    
                    <strong>Navigation simplifiée et plus rapide :</strong> accédez à vos commandes et à vos logiciels préférés en un clin d’œil.
                    <strong>Moteur de recherche optimisé :</strong> trouvez les informations sur les produits AVAST en un seul clic.
                    <strong>Expérience mobile améliorée :</strong> naviguez depuis votre smartphone et commandez où que vous soyez.
                

                    <p>Et bien plus encore...</p>

                    <h3>Comment accéder à votre nouveau compte ?</h3>
                    <p>Connectez-vous dès maintenant à votre espace client pour retrouver vos commandes, vos devis et vos abonnements.</p>
                    <p>Pour des raisons de sécurité, nous vous invitons à réinitialiser votre mot de passe lors de votre première connexion.</p>

                    <div class="content-center" style="text-align:center;">
                        <a href="'.$reset_url_fr.'" target="_blank" style="
                            display:inline-block;
                            margin:20px 0;
                            padding:12px 25px;
                            background:#FF7800;
                            color:white;
                            text-decoration:none;
                            font-size:16px;
                            border-radius:6px;
                            >
                            
                            Réinitialiser mon mot de passe
                        </a>
                    </div>

                    <p>Pour toute question, contactez votre chargée de compte, Maelle Adeire, au 02 55 99 31 37 ou à l’adresse contact@avastedition.com.</p>
            
                </div>

            </div>
            ',
    ];

    $templates['en'] = [
        'subject' => 'Your AVAST account has a fresh new look!',
        'body' => '
            <h2>Your AVAST account has a fresh new look!</h2>

            <p>We are delighted to introduce the brand-new version of your AVAST online store, designed to make your shopping experience simpler, faster and more intuitive.</p>

            <h3>Discover what’s new:</h3>
            <ul>
                <li><strong>Simpler, faster navigation:</strong> access your orders and favorite software in the blink of an eye.</li>
                <li><strong>Improved search engine:</strong> find information about AVAST products with just one click.</li>
                <li><strong>Enhanced mobile experience:</strong> browse on your smartphone and place orders wherever you are.</li>
            </ul>

            <p>And much more...</p>

            <h3>How do you access your new account?</h3>
            <p>Sign in to your customer account to find your orders, quotes and subscriptions.</p>
            <p>For security reasons, we invite you to reset your password the first time you log in.</p>

            <p><a href="'.$reset_url_en.'">Reset my password</a></p>

            <p>If you have any questions, contact your account manager, Maelle Adeire, on +33 2 55 99 31 37 or at contact@avastedition.com.</p>
        ',
    ];

    $templates['es'] = [
        'subject' => '¡Tu cuenta de AVAST estrena imagen!',
        'body' => '
            <h2>¡Tu cuenta de AVAST estrena imagen!</h2>

            <p>Nos complace presentarte la nueva versión de tu tienda online de AVAST, diseñada para ofrecerte una experiencia de compra más sencilla, rápida e intuitiva.</p>

            <h3>Descubre las novedades:</h3>
            <ul>
                <li><strong>Navegación más sencilla y rápida:</strong> accede fácilmente a tus pedidos y programas favoritos.</li>
                <li><strong>Buscador mejorado:</strong> encuentra información sobre los productos AVAST con un solo clic.</li>
                <li><strong>Experiencia móvil mejorada:</strong> navega desde tu smartphone y realiza pedidos desde cualquier lugar.</li>
            </ul>

            <p>¡Y mucho más!</p>

            <h3>¿Cómo acceder a tu nueva cuenta?</h3>
            <p>Inicia sesión en tu área de cliente para consultar tus pedidos, presupuestos y suscripciones.</p>
            <p>Por motivos de seguridad, te recomendamos restablecer tu contraseña la primera vez que accedas.</p>

            <p><a href="'.$reset_url_es.'">Restablecer mi contraseña</a></p>

            <p>Si tienes alguna pregunta, contacta con tu responsable de cuenta, Maelle Adeire, en el +33 2 55 99 31 37 o en contact@avastedition.com.</p>
        ',
    ];

    $templates['de'] = [
        'subject' => 'Ihr AVAST-Konto in neuem Design!',
        'body' => '
            <h2>Ihr AVAST-Konto in neuem Design!</h2>

            <p>Wir freuen uns, Ihnen die neue Version Ihres AVAST-Onlineshops vorzustellen. Sie wurde entwickelt, um Ihnen ein einfacheres, schnelleres und intuitiveres Einkaufserlebnis zu bieten.</p>

            <h3>Entdecken Sie die Neuerungen:</h3>
            <ul>
                <li><strong>Einfachere und schnellere Navigation:</strong> Greifen Sie schnell auf Ihre Bestellungen und bevorzugte Software zu.</li>
                <li><strong>Optimierte Suche:</strong> Finden Sie Informationen zu AVAST-Produkten mit nur einem Klick.</li>
                <li><strong>Verbesserte mobile Nutzung:</strong> Nutzen Sie den Shop auf Ihrem Smartphone und bestellen Sie von überall.</li>
            </ul>

            <p>Und vieles mehr...</p>

            <h3>Wie greifen Sie auf Ihr neues Konto zu?</h3>
            <p>Melden Sie sich in Ihrem Kundenkonto an, um Ihre Bestellungen, Angebote und Abonnements einzusehen.</p>
            <p>Aus Sicherheitsgründen empfehlen wir Ihnen, Ihr Passwort bei Ihrer ersten Anmeldung zurückzusetzen.</p>

            <p><a href="'.$reset_url_de.'">Passwort zurücksetzen</a></p>

            <p>Bei Fragen wenden Sie sich bitte an Ihre Kundenbetreuerin Maelle Adeire unter +33 2 55 99 31 37 oder an contact@avastedition.com.</p>
        ',
    ];

    $templates['it'] = [
        'subject' => 'Il tuo account AVAST si rinnova!',
        'body' => '
            <h2>Il tuo account AVAST si rinnova!</h2>

            <p>Siamo lieti di presentarti la nuova versione del negozio online AVAST, progettata per offrirti un’esperienza di acquisto più semplice, veloce e intuitiva.</p>

            <h3>Scopri le novità:</h3>
            <ul>
                <li><strong>Navigazione più semplice e veloce:</strong> accedi rapidamente ai tuoi ordini e ai tuoi software preferiti.</li>
                <li><strong>Motore di ricerca migliorato:</strong> trova informazioni sui prodotti AVAST con un solo clic.</li>
                <li><strong>Esperienza mobile migliorata:</strong> naviga dal tuo smartphone ed effettua ordini ovunque ti trovi.</li>
            </ul>

            <p>E molto altro ancora...</p>

            <h3>Come accedere al tuo nuovo account?</h3>
            <p>Accedi alla tua area clienti per consultare ordini, preventivi e abbonamenti.</p>
            <p>Per motivi di sicurezza, ti invitiamo a reimpostare la password al primo accesso.</p>

            <p><a href="'.$reset_url_it.'">Reimposta la password</a></p>

            <p>Per qualsiasi domanda, contatta la tua referente commerciale Maelle Adeire al numero +33 2 55 99 31 37 oppure all’indirizzo contact@avastedition.com.</p>
        ',
    ];

    $templates['pt'] = [
        'subject' => 'A sua conta AVAST está de cara nova!',
        'body' => '
            <h2>A sua conta AVAST está de cara nova!</h2>

            <p>Temos o prazer de apresentar a nova versão da loja online AVAST, concebida para lhe proporcionar uma experiência de compra mais simples, rápida e intuitiva.</p>

            <h3>Descubra as novidades:</h3>
            <ul>
                <li><strong>Navegação mais simples e rápida:</strong> aceda facilmente às suas encomendas e aos seus programas favoritos.</li>
                <li><strong>Pesquisa otimizada:</strong> encontre informações sobre os produtos AVAST com apenas um clique.</li>
                <li><strong>Experiência móvel melhorada:</strong> navegue no seu smartphone e faça encomendas onde quer que esteja.</li>
            </ul>

            <p>E muito mais...</p>

            <h3>Como aceder à sua nova conta?</h3>
            <p>Inicie sessão na sua área de cliente para consultar as suas encomendas, propostas e subscrições.</p>
            <p>Por motivos de segurança, recomendamos que redefina a sua palavra-passe no primeiro acesso.</p>

            <p><a href="'.$reset_url_pt.'">Redefinir a minha palavra-passe</a></p>

            <p>Se tiver alguma dúvida, contacte a sua gestora de conta, Maelle Adeire, através do número +33 2 55 99 31 37 ou do endereço contact@avastedition.com.</p>
        ',
    ];

    return $templates;
}



function avast_send_welcome_email(
    string $email,
    string $country_code
): bool {
    $country_languages = avast_get_country_languages();
    $templates = avast_get_welcome_templates();

    $country_code = strtoupper(trim($country_code));

    $language = $country_languages[$country_code] ?? 'fr';
    $template = $templates[$language] ?? $templates['fr'];

    $subject = $template['subject'];

    $body = $template['body'];

    $headers = [
    'Content-Type: text/html; charset=UTF-8',
    'From: AVAST <contact@avastedition.com>',
];

    return wp_mail($email, $subject, $body, $headers);
}