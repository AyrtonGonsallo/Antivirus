<?php


class DevisEmailSender {

    public static function send_email($post_id) {
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
        $lien_auto_connect_devis = "https://test.antivirusedition.com/mon-compte/mes-devis/";
        $date_de_creation   = get_field('date_de_creation', $post_id);
        $date_de_creation_formatted = $date_de_creation ? (new DateTime($date_de_creation))->format('d/m/Y \à H\hi') : '';
        $note_client     =    get_field('note_client', $post_id);
        $software_duration     =    get_field('software_duration', $post_id)["label"];
        $produits_de_la_commande     =    get_field('produits_de_la_commande', $post_id);
        $total_produits=0;
        $devis_content_html = "";
        if ($produits_de_la_commande) {
            foreach ($produits_de_la_commande as $ligne) {
                $produit_relation = $ligne['produit'];
                $prix_propose = ($ligne['prix_propose'])?($ligne['prix_propose'].'€'):"en attente";
                $quantite = $ligne['quantite'];

                if (is_array($produit_relation) && isset($produit_relation[0])) {
                    $produit_post = $produit_relation[0];
                    $product_id = $produit_post_id;
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
        $devis_content_html .= "
                <div>
                    total = $total_produits  €
                </div>";


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


}
