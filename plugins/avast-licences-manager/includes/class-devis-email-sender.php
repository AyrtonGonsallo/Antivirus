<?php
require_once __DIR__ . '/class-gestion-de-comptes.php';

class DevisEmailSender {

    public static function send_email_devis_final($post_id) {
        $id = $post_id;
        if (!$id) return;

        // 1️⃣ Récupérer les informations du devis
        $user = get_field('utilisateur', $post_id); // champ ACF user
        
        if (!$user) {
            wp_safe_redirect(admin_url("edit.php?post_type=devis-en-ligne&mail=error"));
            exit;
        }

        $variations  = get_field('variations', $post_id);
        $pdf_files = [];
        $user_email = $user->user_email;
        $user_id = $user->ID;
        $prenom = get_user_meta($user_id, 'first_name', true);
        $nom = get_user_meta($user_id, 'last_name', true);
        $civilite = get_user_meta($user_id, 'civilite', true); 
        
        $note_admin     =    get_field('note_admin', $post_id);
        $lien_auto_connect_devis = site_url()."/mon-compte/mes-devis/";
        $date_de_creation   = get_field('date_de_creation', $post_id);
        $date_de_creation_formatted = $date_de_creation ? (new DateTime($date_de_creation))->format('d/m/Y \à H\hi') : '';
        $note_client     =    get_field('note_client', $post_id);
        $software_duration     =    get_field('software_duration', $post_id)["label"];
        $produits_de_la_commande     =    get_field('produits_de_la_commande', $post_id);
         $today = current_time('d/m/Y g:i a'); // même format que date_de_creation

        $total_produits=0;
        $devis_content_html = "";

        $total_variations = sizeof($variations) ;
        $ids = [];
       
        foreach ($variations as $variation) {
            $variation_id = $variation->ID;
            $ids[] = $variation->ID;
            $formule = get_field('formule', $variation_id);

            

            $note_admin     =    get_field('note_admin', $variation_id);
            $variation_title     =    get_the_title( $variation_id);
            $devis_content_html .= '<h3>'.$variation_title.'</h3>' ;
            $devis_content_html .= '<div>'.$formule.'</div>' ;
           // $devis_content_html .= ' <h2 style="margin-top:30px; color:#444;text-transform:uppercase;text-align:center">Commentaire de notre service commercial</h2> '.$note_admin.'<br>';

            $recapitulatif_pdf = get_field('recapitulatif_pdf', $variation_id); // champ ACF type "File"
            if ($recapitulatif_pdf && !empty($recapitulatif_pdf['ID'])) {
                $pdf_id   = $recapitulatif_pdf['ID'];
                $pdf_path = get_attached_file($pdf_id);
                if ($pdf_path) {
                    $pdf_files[] = $pdf_path; // ajoute au tableau
                }
            }

        }
        $ids_var = implode(' - ', $ids);
        $lien_auto_connect_devis = ALM_Gestion_De_Comptes::generate_auto_login_link($user_id);
        
      

               
        $lien_logo_png = site_url().'/wp-content/uploads/2025/11/avast-logo.png';


        // 2️⃣ Construire le contenu de l'email
        $subject = "Merci pour votre demande de devis AVAST";
        $message = '
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

                  
                
                    
                        Bonjour '.$civilite.' '.$nom.' '.$prenom.',
                    

                    

                     <p style="font-size:15px; color:#555; line-height:1.6;">
                        Votre demande de tarifs pour des licences Avast a été traitée, et vos '.$total_variations.' devis sont maintenant disponibles dans votre compte client.
                    </p>

                  

                    <h3 style="margin-top:30px; color:#444;text-transform:uppercase;text-align:start">Voici le récapitulatif de votre demande :</h3><br><br>
------------------------------------------------------<br>
                    Numéro de votre demande de devis multiples : '.$post_id.'
                    Date de la demande : '.$date_de_creation_formatted.'<br>
                    Numéros de vos devis : '.$ids_var.':<br>
                    Votre demande : '.$note_client.'<br>
                    Durée : '.$software_duration.'<br>
------------------------------------------------------<br>

                    
                    <h3 style="margin-top:30px; color:#444;text-transform:uppercase;text-align:start">Contenu de votre devis :</h3><br>
                    '.$devis_content_html.'

                    <h3 style="margin-top:30px; color:#444;text-transform:uppercase;text-align:start">Commentaire de l\'équipe commerciale :</h3><br>
------------------------------------------------------<br>
'.$note_admin.'
------------------------------------------------------<br>
                   
                    <p style="font-size:15px; color:#555; line-height:1.6;">
                        Veuillez trouver ci-joint le PDF de votre devis nº '.$id.'.
                    </p>
                    <p style="font-size:15px; color:#555; line-height:1.6;">
                    Pour accepter ou refuser ce devis, connectez vous à votre compte client.
                    </p>
                    <p style="font-size:15px; color:#555; line-height:1.6;">
                    Vous pouvez régler votre commande par carte bancaire en ligne (traitement accéléré de votre commande), par chèque, par virement bancaire ou par mandat administratif.
                    </p>
                    <p style="font-size:15px; color:#555; line-height:1.6;">
                    Pour vous connecter à votre compte client, cliquez sur ce lien:
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
                        ">Voir mon devis</a>
                    </div>

                    <p style="font-size:15px; color:#555; line-height:1.6; margin-top:20px;">
                        Nous vous remercions d\'avoir effectué votre demande sur notre site et vous souhaitons bonne réception de vos devis.
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
            $pdf_files // tableau de fichiers attachés
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
       $variations  = get_field('variations', $post_id);
        $pdf_files = [];
        
      
        foreach ($variations as $variation) {
            $variation_id = $variation->ID;
            $formule     =    get_field('formule', $variation_id);
            $variation_title     =    get_the_title( $variation_id);
            $devis_content_html .= '<h3 class="titre-variation-devis">'.$variation_title.'</h3>' ;
            $devis_content_html .= '<div class="table-devis-details">'.$formule.'</div>' ;

            $recapitulatif_pdf = get_field('recapitulatif_pdf', $variation_id); // champ ACF type "File"
            if ($recapitulatif_pdf && !empty($recapitulatif_pdf['ID'])) {
                $pdf_id   = $recapitulatif_pdf['ID'];
                $pdf_path = get_attached_file($pdf_id);
                if ($pdf_path) {
                    $pdf_files[] = $pdf_path; // ajoute au tableau
                }
            }

        }
               

        $lien_logo_png = site_url().'/wp-content/uploads/2025/11/avast-logo.png';


        // 2️⃣ Construire le contenu de l'email
        $subject = "Merci pour votre demande de devis AVAST";
        $message = '
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
                        Vous pouvez suivre le statut de votre demande de devis dans votre compte client :
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
                        ">Voir mon devis</a>
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
            $pdf_files // tableau de fichiers attachés
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
        $variations  = get_field('variations', $post_id);
        $pdf_files = [];

         foreach ($variations as $variation) {
            $variation_id = $variation->ID;
            $formule     =    get_field('formule', $variation_id);
            $variation_title     =    get_the_title( $variation_id);
            $devis_content_html .= '<h3 class="titre-variation-devis">'.$variation_title.'</h3>' ;
            $devis_content_html .= '<div class="table-devis-details">'.$formule.'</div>' ;

            $recapitulatif_pdf = get_field('recapitulatif_pdf', $variation_id); // champ ACF type "File"
            if ($recapitulatif_pdf && !empty($recapitulatif_pdf['ID'])) {
                $pdf_id   = $recapitulatif_pdf['ID'];
                $pdf_path = get_attached_file($pdf_id);
                if ($pdf_path) {
                    $pdf_files[] = $pdf_path; // ajoute au tableau
                }
            }

        }
        
        $lien_logo_png = site_url().'/wp-content/uploads/2025/11/avast-logo.png';


        // 2️⃣ Construire le contenu de l'email
        $subject = "Votre devis AVAST vous attend dans votre compte AVAST";
        $message = '
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
                        ">Voir mon devis</a>
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
            $pdf_files // tableau de fichiers attachés
        );
        return $sent;
        
    }


}
