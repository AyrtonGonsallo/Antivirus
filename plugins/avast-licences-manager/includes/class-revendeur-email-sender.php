<?php


class RevendeurEmailSender {

    public static function create_account_and_send_email($post_id) {
        $id = $post_id;
        if (!$id) return;

      

        $new_revendeur_account_nom = get_field('account_nom', $post_id);
        $new_revendeur_account_prenom = get_field('account_prenom', $post_id);
        $new_revendeur_account_email = get_field('account_email', $post_id);
        $new_revendeur_account_societe = get_field('account_societe', $post_id);
        $new_revendeur_account_genre = get_field('account_genre', $post_id);
        $new_revendeur_account_telephone = get_field('account_telephone', $post_id);
        $new_revendeur_account_mot_de_passe = get_field('account_mot_de_passe', $post_id);
        $new_revendeur_account_adresse = get_field('account_adresse', $post_id);
        $new_revendeur_account_ville = get_field('account_ville', $post_id);
        $new_revendeur_account_code_postal = get_field('account_code_postal', $post_id);
        $new_revendeur_account_pays = get_field('account_pays', $post_id);
        $new_revendeur_account_agree_cgr = get_field('account_agree_cgr', $post_id);
        $new_revendeur_account_divulgation = get_field('account_divulgation', $post_id);
        $new_revendeur_account_tva_intra = get_field('account_tva_intra', $post_id);
        $new_revendeur_account_regime_tva = get_field('account_regime_tva', $post_id);
        $new_revendeur_account_prefixe_tva = get_field('account_prefixe_tva', $post_id);

        $civilite = ($new_revendeur_account_genre=="m")?"Monsieur":"Madame";

         $new_revendeur_account_role = 'customer_revendeur';
        //creer avec nom prenom password email et ajouter le role
        $user_id = wp_insert_user([
            'user_login'   => $new_revendeur_account_email,
            'user_email'   => $new_revendeur_account_email,
            'user_pass'    => $new_revendeur_account_mot_de_passe,
            'first_name'   => $new_revendeur_account_prenom,
            'last_name'    => $new_revendeur_account_nom,
            'role'         => $new_revendeur_account_role,
        ]);
        update_user_meta($user_id, 'denomination', $new_revendeur_account_societe);
        update_user_meta($user_id, 'ville', $new_revendeur_account_ville);
        update_user_meta($user_id, 'code_postal', $new_revendeur_account_code_postal);
        update_user_meta($user_id, 'pays', $new_revendeur_account_pays);

        update_user_meta($user_id, 'billing_address_1', $new_revendeur_account_adresse);
        update_user_meta($user_id, 'billing_phone', $new_revendeur_account_telephone);


        $lien_auto_connect_compte = site_url('/mon-compte/');
        $lien_blog = site_url('/blog/');
        $lien_logo_png = site_url().'/wp-content/uploads/2025/11/avast-logo.png';

        // 2️⃣ Construire le contenu de l'email
        $subject = "Votre compte revendeur avast est ouvert !";
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

                  
                
                    
                        Bonjour '.$civilite.' '.$new_revendeur_account_nom.' '.$new_revendeur_account_prenom.',
                    

                    <p style="font-size:15px; color:#555; line-height:1.6;">
                        Félicitations votre compte revendeur avast a été validé par nos services.<br>
                        Il est maintenant opérationnel.<br>
                        Vous pouvez vous connecter à votre compte revendeur ici à l\'aide de l\'adresse email et du mot de passe que vous avez saisi lorsque vous avez rempli le formulaire d\'adhésion :
                    </p>

                    <div class="content-center" style="text-align:center;">
                        <a href="'.$lien_auto_connect_compte.'" target="_blank" style="
                            display:inline-block;
                            margin:20px 0;
                            padding:12px 25px;
                            background:#FF7800;
                            color:white;
                            text-decoration:none;
                            font-size:16px;
                            border-radius:6px;
                        ">Accéder à mon compte revendeur avast</a>
                    </div>

                    <h2 style="margin-top:30px; color:#444;text-transform:uppercase;text-align:center">Votre compte revendeur avast vous permet de bénéficier des avantages suivants</h2>
                    
                     <p style="font-size:15px; color:#555; line-height:1.6; margin-top:20px;">       
                    - une marge revendeur garentie quelque soit le volume d\'affaires que vous faites avec nous.<br>
                    - un compte revendeur fonctionnel qui vous permet d\'etre le plus autonome possible dans vos demandes de devis, demandes de support technique, la gestion de vos commandes, etc...<br>
                    - un espace marketing dédié aux revendeurs avast ou vous pourrez télécharger des affiches, lire nos recommandations pour trouver de nouveaux clients, vous tenir informé des nouveautés avast via notre blog '.$lien_blog.'<br>
                    - des promotions sous forme de rabais ou de cadeaux.<br>
                    - un contact dédié (moi-même) qui centralise vos demandes (commerciales et techniques) et s\'assure de votre satisfaction.<br>
                    </p>
                    <p style="font-size:15px; color:#555; line-height:1.6; margin-top:20px;">
                        Je reste à votre disposition pour toute question.
                    </p>

                    <p style="margin-top:30px; color:#333; font-weight:bold;">
                        Bien cordialement,<br>Maelle Adeire <br>Distribution AVAST
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
            $new_revendeur_account_email,
            $subject,
            $message,
            $headers
        );
        return $sent;
        
    }


}
