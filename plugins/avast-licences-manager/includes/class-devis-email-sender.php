<?php


class DevisEmailSender {

    public static function send_email_devis_final($post_id) {
        $id = $post_id;
        if (!$id) return;

        // 1️⃣ Récupérer les informations du devis
        $user = get_field('utilisateur', $post_id); // champ ACF user
        $recapitulatif_pdf = get_field('recapitulatif_pdf', $post_id); // champ ACF type "File"

        if (!$user || !$recapitulatif_pdf) {
            wp_safe_redirect(admin_url("edit.php?post_type=devis-en-ligne&mail=error"));
            exit;
        }

        $user_email = $user->user_email;
        $user_id = $user->ID;
        $prenom = get_user_meta($user_id, 'first_name', true);
        $nom = get_user_meta($user_id, 'last_name', true);
        $civilite = get_user_meta($user_id, 'civilite', true); 
        $pdf_id  = $recapitulatif_pdf["ID"];
        $pdf_path = get_attached_file($pdf_id);
        $note_admin     =    get_field('note_admin', $post_id);
        $lien_auto_connect_devis = site_url()."/mon-compte/mes-devis/";
        $date_de_creation   = get_field('date_de_creation', $post_id);
        $date_de_creation_formatted = $date_de_creation ? (new DateTime($date_de_creation))->format('d/m/Y \à H\hi') : '';
        $note_client     =    get_field('note_client', $post_id);
        $software_duration     =    get_field('software_duration', $post_id)["label"];
        $produits_de_la_commande     =    get_field('produits_de_la_commande', $post_id);
         $today = current_time('d/m/Y g:i a'); // même format que date_de_creation

        $args = [
            'post_type' => 'remise',
            'post_status' => 'publish',
            'numberposts' => -1,
            'meta_query' => [
                [
                    'key' => 'utilisateur',
                    'value' => $user_id,
                    'compare' => '='
                ],
                [
                    'key' => 'statut',
                    'value' => 'validee',
                    'compare' => '='
                ],
                [
                    'key' => 'date_dexpiration',
                    'value' => $today,
                    'compare' => '>=',
                    'type' => 'DATETIME'
                ]
            ]
        ];

       

        $remises = get_posts($args);
        $percent_tva = 20;
        // 1. On récupère le pays du client via ACF
        $user_country = get_user_meta($user_id, 'pays', true);
        if (!$user_country) return;

        // 2. On récupère le taux dans le CPT taux_tva
        $args = [
            'post_type' => 'tva_par_pays',
            'meta_query' => [
                [
                    'key' => 'code_pays',
                    'value' => $user_country,
                    'compare' => '='
                ]
            ],
            'posts_per_page' => 1,
        ];

        $tva_posts = get_posts($args);
        if (!empty($tva_posts)){
             $tva_post_id = $tva_posts[0]->ID;

            // 3. On récupère le taux %
            $percent_tva = get_field('pourcentage', $tva_post_id); // ex: 20
            
        }
        $total_produits=0;
        $devis_content_html = "";
        if ($produits_de_la_commande) {
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
                        $duree = $product_obj->get_attribute('pa_software_duration');
                    }
                }
                

                $total = number_format($prix_propose * $quantite, 2) . " €";
                $total_produits+=number_format($prix_propose * $quantite, 2);
                

                $devis_content_html .= "
                <div>
                    $product_name $duree = $prix_propose x $quantite = $total
                </div>";
            }
        }
      

                // Calcul des remises
                    $total_discount_amount = 0;
                    if (!empty($remises)) {
                        foreach ($remises as $remise) {
                            $percent = floatval(get_field('pourcentage', $remise));
                            $total_discount_amount += ($percent / 100) * $total_produits;
                        }
                    }

                    // Sous-total (produits - remises)
                    $sous_total = $total_produits - $total_discount_amount;
                    $tva = ($sous_total * $percent_tva) / 100; // TVA 20%
                    $total_ttc = $sous_total + $tva;

                    // Ligne Total HT
                    $devis_content_html .= '<div style="display:flex;width:50%;padding:6px 0;justify-content: space-between;">';
                    $devis_content_html .= '<div style="padding:0px 6px;"> Total HT</div>';
                    $devis_content_html .= '<div>'.number_format($total_produits, 2, ',', ' ').' €</div>';
                    $devis_content_html .= '</div>';

                    

                    // Ligne Remises
                    if ($total_discount_amount > 0) {
                        $devis_content_html .= '<div style="display:flex;width: 50%;padding:6px 0;justify-content: space-between;">';
                        $devis_content_html .= '<div style="padding:0px 6px;">Remises commerciales</div>';
                        $devis_content_html .= '<div>-'.number_format($total_discount_amount, 2, ',', ' ').' €</div>';
                        $devis_content_html .= '</div>';
                    }

                    // Ligne Sous-total HT
                    $devis_content_html .= '<div style="display:flex;width: 50%;padding:6px 0;justify-content: space-between;">';
                    $devis_content_html .= '<div style="padding:0px 6px;">Sous-total HT</div>';
                    $devis_content_html .= '<div>'.number_format($sous_total, 2, ',', ' ').' €</div>';
                    $devis_content_html .= '</div>';

                    // Ligne TVA
                    $devis_content_html .= '<div style="display:flex;width: 50%;padding:6px 0;justify-content: space-between;">';
                    $devis_content_html .= '<div style="padding:0px 6px;">TVA '.$percent_tva.'%</div>';
                    $devis_content_html .= '<div>'.number_format($tva, 2, ',', ' ').' €</div>';
                    $devis_content_html .= '</div>';

                    // Ligne Total TTC
                    $devis_content_html .= '<div style="display:flex;width:50%;padding:6px 0;justify-content:space-between;">';
                    $devis_content_html .= '<div style="padding:0px 6px;">Total TTC</div>';
                    $devis_content_html .= '<div style="font-weight:bold;">'.number_format($total_ttc, 2, ',', ' ').' €</div>';
                    $devis_content_html .= '</div>';

        $lien_logo_png = site_url().'/wp-content/uploads/2025/11/avast-logo.png';


        // 2️⃣ Construire le contenu de l'email
        $subject = "Merci pour votre demande de devis AVAST";
        $message = '
            <div style="
                width:100%;
                background:#f5f5f5;
                padding:40px 0;
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

                    <div style="width: auto;padding: 20px;text-align: center !important;max-width: 100%;margin-left: auto;margin-right: auto;margin-bottom:30px;">
                        <div>
                            <div style="text-align: center;">
                                <img src="'.$lien_logo_png.'" alt="Logo du site" >      
                            </div>
                        </div>       
                    </div>
                
                    
                        Bonjour '.$civilite.' '.$nom.' '.$prenom.',
                    

                    <p style="font-size:15px; color:#555; line-height:1.6;">
                        Votre demande de devis a été traitée par notre service commercial.<br>
                        Votre devis est désormais disponible dans votre compte client.
                    </p>

                    <div class="content-center" style="text-align:center;">
                        <a href="'.$lien_auto_connect_devis.'" target="_blank" style="
                            display:inline-block;
                            margin:20px 0;
                            padding:12px 25px;
                            background:#FF7800;
                            color:white;
                            text-decoration:none;
                            font-size:16px;
                            border-radius:6px;
                        ">
                            Voir mon devis
                        </a>
                    </div>

                    <h2 style="margin-top:30px; color:#444;text-transform:uppercase;text-align:center">Votre demande de devis</h2>
                    Date : '.$date_de_creation_formatted.'<br><br>
                    Votre demande : '.$note_client.'<br>
                    Durée : '.$software_duration.'<br>
                    <h2 style="margin-top:30px; color:#444;text-transform:uppercase;text-align:center">Contenu de votre devis</h2>
                    '.$devis_content_html.'
                    <h2 style="margin-top:30px; color:#444;text-transform:uppercase;text-align:center">Commentaire de notre service commercial</h2>
                    '.$note_admin.'<br>
                    <p style="font-size:15px; color:#555; line-height:1.6;">
                        Veuillez trouver ci-joint le PDF de votre devis nº '.$id.'.
                    </p>

                    <p style="font-size:15px; color:#555; line-height:1.6; margin-top:20px;">
                        Nous vous remercions pour votre confiance et restons à votre disposition pour toute question.
                    </p>

                    <p style="margin-top:30px; color:#333; font-weight:bold;">
                        Cordialement,<br>L\'équipe Avast
                    </p>

                </div>

            </div>
            ';



        // 3️⃣ Préparer les headers 
        $headers = array(
            'Content-Type: text/html; charset=UTF-8'
        );


        // 4️⃣ Envoyer avec wp_mail et pièce jointe
        $sent = wp_mail(
            $user_email,
            $subject,
            $message,
            $headers,
            array($pdf_path) // tableau de fichiers attachés
        );
        return $sent;
        
    }




    public static function send_email_devis_created($post_id) {
        $id = $post_id;
        if (!$id) return;

        // 1️⃣ Récupérer les informations du devis
        $user = get_field('utilisateur', $post_id); 
        if (!$user) {
            // wp_safe_redirect(admin_url("edit.php?post_type=devis-en-ligne&mail=error"));
            return false;
            exit;
        }

        $user_email = $user->user_email;
        $user_id = $user->ID;
        $prenom = get_user_meta($user_id, 'first_name', true);
        $nom = get_user_meta($user_id, 'last_name', true);
        $civilite = get_user_meta($user_id, 'civilite', true); 
        $lien_auto_connect_devis = site_url()."/mon-compte/mes-devis/";
        $date_de_creation   = get_field('date_de_creation', $post_id);
        $date_de_creation_formatted = $date_de_creation ? (new DateTime($date_de_creation))->format('d/m/Y \à H\hi') : '';
        $software_duration     =    get_field('software_duration', $post_id)["label"];
       $note_client     =    get_field('note_client', $post_id);
      

               

        $lien_logo_png = site_url().'/wp-content/uploads/2025/11/avast-logo.png';


        // 2️⃣ Construire le contenu de l'email
        $subject = "Merci pour votre demande de devis AVAST";
        $message = '
            <div style="
                width:100%;
                background:#f5f5f5;
                padding:40px 0;
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

                    <div style="width: auto;padding: 20px;text-align: center !important;max-width: 100%;margin-left: auto;margin-right: auto;margin-bottom:30px;">
                        <div>
                            <div style="text-align: center;">
                                <img src="'.$lien_logo_png.'" alt="Logo du site" >      
                            </div>
                        </div>       
                    </div>
                
                    
                        Bonjour '.$civilite.' '.$nom.' '.$prenom.',
                    

                    <p style="font-size:15px; color:#555; line-height:1.6;">
                        Merci pour votre demande de devis ! Notre équipe commerciale traite actuellement votre demande de devis.
                    </p>


                    <h2 style="margin-top:30px; color:#444;text-transform:uppercase;text-align:center">Votre demande de devis</h2>
                    Date : '.$date_de_creation_formatted.'<br><br>
                    Votre demande : '.$note_client.'<br>
                    Durée : '.$software_duration.'<br>
                    
                    <div style="border:solid 1px;width:100%"></div>

                    <p style="font-size:15px; color:#555; line-height:1.6; margin-top:20px;">
                        Vous serez averti par Email lorsque votre devis sera prêt (généralement sous 24 heures). 
                        Vous pourrez ensuite transformer ce devis en bon de commande et régler votre commande par le moyen de paiment de votre choix.
                    </p>

                    <p style="font-size:15px; color:#555; line-height:1.6; margin-top:20px;">
                        Vous pouvez suivre le status de votre demande de devis dans votre compte client :
                    </p>
                    
                    <div class="content-center" style="text-align:center;">
                        <a href="'.$lien_auto_connect_devis.'" target="_blank" style="
                            display:inline-block;
                            margin:20px 0;
                            padding:12px 25px;
                            background:#FF7800;
                            color:white;
                            text-decoration:none;
                            font-size:16px;
                            border-radius:6px;
                        ">
                            Voir mon devis
                        </a>
                    </div>

                    <p style="font-size:15px; color:#555; line-height:1.6; margin-top:20px;">
                        Nous vous remercions pour votre confiance et restons à votre disposition pour toute question.
                    </p>

                    <p style="margin-top:30px; color:#333; font-weight:bold;">
                        Cordialement,<br>L\'équipe Avast
                    </p>

                </div>

            </div>
            ';



        // 3️⃣ Préparer les headers 
        $headers = array(
            'Content-Type: text/html; charset=UTF-8'
        );


        // 4️⃣ Envoyer avec wp_mail et pièce jointe
        $sent = wp_mail(
            $user_email,
            $subject,
            $message,
            $headers,
            array($pdf_path) // tableau de fichiers attachés
        );
        return $sent;
        
    }





    public static function send_email_devis_expiring($post_id) {
        $id = $post_id;
        if (!$id) return;

        // 1️⃣ Récupérer les informations du devis
        $user = get_field('utilisateur', $post_id); // champ ACF user
       

        if (!$user) {
            // wp_safe_redirect(admin_url("edit.php?post_type=devis-en-ligne&mail=error"));
            return false;
            exit;
        }

        $user_email = $user->user_email;
        $user_id = $user->ID;
        $prenom = get_user_meta($user_id, 'first_name', true);
        $nom = get_user_meta($user_id, 'last_name', true);
        $civilite = get_user_meta($user_id, 'civilite', true); 
        $lien_auto_connect_devis = site_url()."/mon-compte/mes-devis/";
        $date_de_creation   = get_field('date_de_creation', $post_id);
        $date_de_creation_formatted = $date_de_creation ? (new DateTime($date_de_creation))->format('d/m/Y \à H\hi') : '';
        $date_expiration  = get_field('date_expiration', $post_id);
        $date_expiration_formatted  = $date_expiration ? (new DateTime($date_expiration))->format('d/m/Y \à H\hi') : '';
        $software_duration     =    get_field('software_duration', $post_id)["label"];
        
        $lien_logo_png = site_url().'/wp-content/uploads/2025/11/avast-logo.png';


        // 2️⃣ Construire le contenu de l'email
        $subject = "Votre devis AVAST vous attend dans votre compte AVAST";
        $message = '
            <div style="
                width:100%;
                background:#f5f5f5;
                padding:40px 0;
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

                    <div style="width: auto;padding: 20px;text-align: center !important;max-width: 100%;margin-left: auto;margin-right: auto;margin-bottom:30px;">
                        <div>
                            <div style="text-align: center;">
                                <img src="'.$lien_logo_png.'" alt="Logo du site" >      
                            </div>
                        </div>       
                    </div>
                
                    
                        Bonjour '.$civilite.' '.$nom.' '.$prenom.',
                    

                    <p style="font-size:15px; color:#555; line-height:1.6;">
                        Vous avez formulé une demande de devis pour des licences AVAST le '.$date_de_creation_formatted.' et nous
                        vous en remercions sincèrement.<br>
                    </p>

                    <p style="font-size:15px; color:#555; line-height:1.6;">
                        Nous tenons a vous informer que votre devis est actuellement à votre disposition 
                        dans votre compte client et restera vallable jusqu\'au '.$date_expiration_formatted.'<br>
                    </p>

                    <p style="font-size:15px; color:#555; line-height:1.6;">
                        Vous pouvez à tout moment valider ou refuser ce devis en vous connectant a votre compte client.<br>
                    </p>

                    <div class="content-center" style="text-align:center;">
                        <a href="'.$lien_auto_connect_devis.'" target="_blank" style="
                            display:inline-block;
                            margin:20px 0;
                            padding:12px 25px;
                            background:#FF7800;
                            color:white;
                            text-decoration:none;
                            font-size:16px;
                            border-radius:6px;
                        ">
                            Voir mon devis
                        </a>
                    </div>


                    <p style="font-size:15px; color:#555; line-height:1.6; margin-top:20px;">
                        Vous pouvez procéder au paiment de votre commande par carte bancaire en toute sécurité
                        via le service Monetico du CIC. Vous pouvez aussi choisir de régler votre commande via 
                        PayPal, par chèque, par virement ou par mandat administratif.
                    </p>

                    <p style="font-size:15px; color:#555; line-height:1.6; margin-top:20px;">
                        Si toutefois vous ne souhaitez plus recevoir d\'Email concernant ce devis vous pouvez vous connecter
                        à votre compte, acceder à votre devis et le refuser. Vous ne receverez plus de notifications concernant ce devis.
                    </p>
                   

                    <p style="font-size:15px; color:#555; line-height:1.6; margin-top:20px;">
                        Nous vous remercions pour votre confiance et restons à votre disposition pour toute question.
                    </p>
                    <p style="font-size:15px; color:#555; line-height:1.6; margin-top:20px;">
                        En vous souhaitant bonne reception de ces éléments nous vous remercions pour toute la confiance que vous nous accordez.
                    </p>

                    <p style="margin-top:30px; color:#333; font-weight:bold;">
                        Cordialement,<br>L\'équipe Avast
                    </p>

                </div>

            </div>
            ';



        // 3️⃣ Préparer les headers 
        $headers = array(
            'Content-Type: text/html; charset=UTF-8'
        );


        // 4️⃣ Envoyer avec wp_mail et pièce jointe
        $sent = wp_mail(
            $user_email,
            $subject,
            $message,
            $headers,
            array($pdf_path) // tableau de fichiers attachés
        );
        return $sent;
        
    }


}
