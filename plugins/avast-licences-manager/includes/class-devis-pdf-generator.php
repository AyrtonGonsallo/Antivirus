<?php
//include_once WC_ABSPATH . 'includes/wc-cart-functions.php';


class DevisPDFGenerator {

    public static function generate_pdf($post_id) {

        // 1. Récupération des données du devis
        $title        = get_the_title($post_id);
        $status       = get_field('status', $post_id)["label"];
        $type_devis   = get_field('type_de_devis', $post_id)["label"];
        $user      = get_field('utilisateur', $post_id);
        $date_de_creation   = get_field('date_de_creation', $post_id);
        $date_expiration  = get_field('date_expiration', $post_id);
        $status_label           = get_field('status', $post_id)["label"];
        $status_value           = get_field('status', $post_id)["value"];
        $type_de_devis_label        = get_field('type_de_devis', $post_id)["label"];
        $type_de_devis_value        = get_field('type_de_devis', $post_id)["value"];
        $compt2save     =    get_field('compt2save', $post_id);
        $software_duration     =    get_field('software_duration', $post_id)["label"];
        $note_client     =    get_field('note_client', $post_id);
        $note_admin     =    get_field('note_admin', $post_id);
        $motif_de_refus_client     =    get_field('motif_de_refus_client', $post_id);
        $produits_de_la_commande     =    get_field('produits_de_la_commande', $post_id);
        $option        = get_field('option', $post_id);
        $date_de_creation_formatted = $date_de_creation ? (new DateTime($date_de_creation))->format('d/m/Y \à H\hi') : '';
        $date_expiration_formatted  = $date_expiration ? (new DateTime($date_expiration))->format('d/m/Y \à H\hi') : '';
        
        $user_id = $user->ID;
        $prenom = get_user_meta($user_id, 'first_name', true);
        $nom = get_user_meta($user_id, 'last_name', true);
        $user_name =  $user_id ? esc_html($nom." ".$prenom):"";
        $billing_address = get_user_meta($user_id, 'billing_address_1', true);
		$billing_phone   = get_user_meta($user_id, 'billing_phone', true);
		$denomination = get_user_meta($user_id, 'denomination', true);
		$ville = get_user_meta($user_id, 'ville', true);
		$code_postal = get_user_meta($user_id, 'code_postal', true);
		$selected_pays = get_user_meta($user_id, 'pays', true);

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
        $has_tva=true;

        if (  in_array( 'customer_revendeur', (array) $user->roles ) ) {
            // Vérifier le régime TVA stocké par ton formulaire
            $regime = get_user_meta( $user_id, 'new_revendeur_account_regime_tva', true );

            // Si le revendeur est en régime HT => pas de TVA
            if ( strtoupper( $regime ) === 'HT' ) {
                //$cart->remove_taxes( );
                $has_tva=false;
            }else{
                //trouver sa taxe sur le pays
                $percent_tva  = get_field('taux_tva', $post_id);
                $tva  = get_field('tva', $post_id);
            }
            
        }else{
            $percent_tva  = get_field('taux_tva', $post_id);
            $tva  = get_field('tva', $post_id);
        }

        

       
    
        

        /*
        tu lira les produits comme ca
        if ($option === 'ikn') {
            // Afficher le répéteur produits_de_la_commande
            if ($produits_de_la_commande) {
                echo '<table class="table-devis">';
                echo '<thead>
                        <tr>
                            <th>Produit</th>
                            <th>Quantité</th>
                            <th>Prix actuel</th>
                            <th>Prix proposé</th>
                        </tr>
                    </thead>';
                echo '<tbody>';

                foreach ($produits_de_la_commande as $ligne) {
                    
                    $produit_relation = $ligne['produit'];
                    $prix_propose = ($ligne['prix_propose'])?($ligne['prix_propose'].'€'):"en attente";
                    $quantite = $ligne['quantite'];

        */

        // 2. Générer le contenu HTML du PDF
        $logo_url = plugin_dir_url(dirname(__FILE__)) . 'assets/logo-pdf.png';

        $html = '
        <style>
            @page {
                margin-top: 110px; 
                margin-bottom: 110px; 
            }
            main{
                margin-top: 0px; 
                margin-bottom: 50px; 
                /* border:solid orange;*/
            }
            body {
                font-family: DejaVu Sans, sans-serif;
                font-size: 12px;
                color: #333;
            }

            /* ---------- entete ---------- */
           
            header {
                position: fixed;
                top: -110px; 
                left: 0px;
                right: 0px;
                height: 100px; 
                margin-bottom: 45px;
                /* border:solid red;*/
            }
            header .bandeau img {
                height: 60px; 
            }

            
            .bandeau {
                margin-bottom: 20px; 
                margin-top: 10px;  
                /* border:solid blue;*/
            }

            /* ---------- SECTION 2 : Société / Dates ---------- */
            .table-presentation{
                width: 100%;
                margin:10px 0px 20px 0px;
            }
            .table-presentation div,.table-presentation  p{
                line-height:1.05;
            } 

            /* ---------- SECTION 4 : Tableau Produits ---------- */
            table.produits {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 25px;
                margin-top: 25px;
            }
            table.produits th {
                background: #FF7800;
                font-weight: bold;
                padding: 8px;
                border: 1px solid #CCC;
                color:white;
            }
            table.produits td {
                padding: 8px;
                border: 1px solid #CCC;
                font-size: 12px;
            }

            /* ---------- SECTION 5 : Message dans une box ---------- */
            .message-box {
                border: 2px solid #555;
                padding: 12px;
                margin-bottom: 25px;
                background: #fafafa;
            }
            .section-title{
                font-weight: bold;
                margin-bottom: 8px;
                font-size: 14px;
                border-bottom: 1px solid #000;
                padding-bottom: 4px;
            }

            /* ---------- FOOTER ---------- */
            .footer-div {
                text-align: center;
                margin-top: 40px;
                font-size: 11px;
                line-height: 1.4;
                color: #666;
            }
             footer {
                position: fixed;
                bottom: -60px; 
                left: 0px;
                right: 0px;
                height: 100px; 
                
            }   

           
            .product-link {
                color:black;
                font-weight:bolder;
                text-decoration:none;
            }
            .product-link span{
                vertical-align:middle;
            }



        </style>
        <body>
        <header>
            <div class="bandeau">
                <img src="'.$logo_url.'" alt="'.$logo_url.'">
            </div>
        </header>
        <main>
           

            <table class="table-presentation" >
                <tr>
                    <td style="width: 50%;">
                        <p> EU4CMS - AntivirusEdition.com</p>
                        <p>6 rue des Ecoliers </p>
                        <p>56140 Caro - France</p>
                    </td>
                    <td style="width: 50%;">
                        <p><strong>Date du devis :</strong> '.$date_de_creation_formatted.'</p>
                        <p><strong>Date expiration :</strong> '.$date_expiration_formatted.'</p>
                        <p><strong>Status :</strong> '.$status_label.'</p>
                    </td>
                </tr>
            </table>

            <table class="table-presentation" >
                <tr>
                    <td style="width: 50%;">
                      
                    </td>
                    <td style="width: 50%;">
                        <div>
                            <p>Coordonnées de facturation :</p>
                            <p><strong>Client :</strong> '.$user_name.' ('.$denomination.')</p>
                            <p><strong>Adresse de facturation :</strong> '.$billing_address.'</p>
                            <p><strong>Téléphone :</strong> '.$billing_phone.'</p>
                            <p>'.$code_postal.' '.$ville.' '.$selected_pays.'</p>
                        </div>
                    </td>
                </tr>
            </table>

           

            <div class="section4">
                <div class="section-title">Devis N° '.$post_id.'</div>
                <table class="produits">
                    <thead>
                        <tr>
                            <th>Désignation</th>
                            <th>Quantité</th>
                            <th>Durée</th>
                            <th>PU proposé</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>';
                    $total_produits=0;
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
                                    $product_price = $product_obj->get_price();
                                    $product_img = wp_get_attachment_image_src( $product_obj->get_image_id(), 'thumbnail' );
                                    $product_img_url = $product_img ? $product_img[0] : '';
                                    $duree = $product_obj->get_attribute('pa_software_duration');
                                }
                            }
                            

                            $total = number_format($prix_propose * $quantite, 2) . " €";
                            $total_produits+=number_format($prix_propose * $quantite, 2);
                            $nom = '
                            <div class= style="white-space:nowrap;">
                                <img src="'.esc_url($product_img_url).'" width="50" height="50" style="vertical-align:middle; border-radius:4px; margin-right:10px;">
                                <a href="'.get_permalink($product_id).'" class="product-link"><span style="vertical-align:middle;">'.wp_kses_post($product_name).'</span></a>
                            </div>';

                            $html .= "
                            <tr>
                                <td style='display:flex;align-items:center;gap:10px;'>
                                $nom
                                </td>
                                <td>$quantite</td>
                                <td>$duree</td>
                                <td>".$prix_propose." €</td>
                                <td>$total</td>
                            </tr>";
                        }
                    }


                    /*
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
                                    $product_price = $product_obj->get_price();
                                    $product_img = wp_get_attachment_image_src( $product_obj->get_image_id(), 'thumbnail' );
                                    $product_img_url = $product_img ? $product_img[0] : '';
                                    $duree = $product_obj->get_attribute('pa_software_duration');
                                }
                            }
                            

                            $total = number_format($prix_propose * $quantite, 2) . " €";
                            $total_produits+=number_format($prix_propose * $quantite, 2);
                            $nom = '
                            <div class= style="white-space:nowrap;">
                                <img src="'.esc_url($product_img_url).'" width="50" height="50" style="vertical-align:middle; border-radius:4px; margin-right:10px;">
                                <a href="'.get_permalink($product_id).'" class="product-link"><span style="vertical-align:middle;">'.esc_html($product_name).'</span></a>
                            </div>';

                            $html .= "
                            <tr>
                                <td style='display:flex;align-items:center;gap:10px;'>
                                $nom
                                </td>
                                <td>$quantite</td>
                                <td>$duree</td>
                                <td>".$prix_propose." €</td>
                                <td>$total</td>
                            </tr>";
                        }
                    }


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
                                    $product_price = $product_obj->get_price();
                                    $product_img = wp_get_attachment_image_src( $product_obj->get_image_id(), 'thumbnail' );
                                    $product_img_url = $product_img ? $product_img[0] : '';
                                    $duree = $product_obj->get_attribute('pa_software_duration');
                                }
                            }
                            

                            $total = number_format($prix_propose * $quantite, 2) . " €";
                            $total_produits+=number_format($prix_propose * $quantite, 2);
                            $nom = '
                            <div class= style="white-space:nowrap;">
                                <img src="'.esc_url($product_img_url).'" width="50" height="50" style="vertical-align:middle; border-radius:4px; margin-right:10px;">
                                <a href="'.get_permalink($product_id).'" class="product-link"><span style="vertical-align:middle;">'.esc_html($product_name).'</span></a>
                            </div>';

                            $html .= "
                            <tr>
                                <td style='display:flex;align-items:center;gap:10px;'>
                                $nom
                                </td>
                                <td>$quantite</td>
                                <td>$duree</td>
                                <td>".$prix_propose." €</td>
                                <td>$total</td>
                            </tr>";
                        }
                    }

                    */
                    
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
                    $tva = 0; 
                    if($has_tva){
                        $tva = ($sous_total * $percent_tva) / 100;
                    }
                    
                    $total_ttc = $sous_total + $tva;

                    // Ligne Total HT
                    $html .= '<tr>';
                    $html .= '<td colspan="4" style="text-align:right;"> Total HT</td>';
                    $html .= '<td>'.number_format($total_produits, 2, ',', ' ').' €</td>';
                    $html .= '</tr>';

                    

                    // Ligne Remises
                    if ($total_discount_amount > 0) {
                        $html .= '<tr>';
                        $html .= '<td colspan="4" style="text-align:right;">Remises commerciales</td>';
                        $html .= '<td>-'.number_format($total_discount_amount, 2, ',', ' ').' €</td>';
                        $html .= '</tr>';
                    }

                    // Ligne Sous-total HT
                    $html .= '<tr>';
                    $html .= '<td colspan="4" style="text-align:right;">Sous-total HT</td>';
                    $html .= '<td>'.number_format($sous_total, 2, ',', ' ').' €</td>';
                    $html .= '</tr>';
                    
                    if($has_tva){
                        // Ligne TVA
                        $html .= '<tr>';
                        $html .= '<td colspan="4" style="text-align:right;">TVA '.$percent_tva.'%</td>';
                        $html .= '<td>'.number_format($tva, 2, ',', ' ').' €</td>';
                        $html .= '</tr>';
                    }
                    
                    

                    // Ligne Total TTC
                    $html .= '<tr>';
                    $html .= '<td colspan="4" style="text-align:right; font-weight:bold;">Total TTC</td>';
                    $html .= '<td style="font-weight:bold;">'.number_format($total_ttc, 2, ',', ' ').' €</td>';
                    $html .= '</tr>';


            $html .= '
                    </tbody>
                </table>
            </div>

            <div class="section5">
                <div class="section-title">Notes</div>
                <div class="message-box">
                    Pour valider ce devis et passer votre commande, connectez-vous à votre espace client sur notre site à cette adresse:
                    http://www.AntivirusEdition.com/login.php
                    Utilisez votre adresse email travelerchek@gmail.com ainsi que votre mot de passe pour vous y connecter.
                    Vous pourrez choisir de régler votre commande parmi les modes de paiement suivants:
                </div>
            </div>

            
        </main>
        <footer>
            <div class="footer-div">
                <p>EU4CMS - Siège social : 6 rue des Ecoliers - 56140 Caro - France</p>
                <p> SARL au capital de 9832 € - Immatriculé au RCS de Vannes - Siret: 44480024700033 - Nº TVA intracommunautaire: FR77444800247</p>
            </div>
        </footer>
    </body>
        ';


        // 3. Définir le chemin d'upload
        $upload_dir = wp_upload_dir();
        $folder = $upload_dir['basedir'] . '/devis/';

        if (!file_exists($folder)) {
            wp_mkdir_p($folder);
        }

        $file_path = $folder . "devis-$post_id.pdf";

        // 4. Générer le PDF (DOMPDF ou mPDF)
        require_once __DIR__ . '/../libs/dompdf/autoload.inc.php';
        $options = new Dompdf\Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', true); // ← indispensable pour charger des images externes
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);

        $dompdf = new Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        file_put_contents($file_path, $dompdf->output());

        // 5. Importer dans la médiathèque WordPress
        $file_url  = $upload_dir['baseurl'] . '/devis/devis-' . $post_id . '.pdf';
        $attachment_id = self::register_pdf_as_media($file_path, $file_url, $post_id);

        // 6. Mettre à jour le champ ACF type "File"
        if ($attachment_id) {
            update_field('recapitulatif_pdf', $attachment_id, $post_id);
        }

        return $attachment_id;
    }


    /**
     * Enregistre le fichier PDF dans la médiathèque WordPress
     */
    private static function register_pdf_as_media($file_path, $file_url, $post_id) {

        $filetype = wp_check_filetype(basename($file_path), null);

        $attachment = [
            'guid'           => $file_url,
            'post_mime_type' => $filetype['type'],
            'post_title'     => "Devis PDF #$post_id",
            'post_content'   => '',
            'post_status'    => 'inherit'
        ];

        // insertion
        $attachment_id = wp_insert_attachment($attachment, $file_path);

        if (!is_wp_error($attachment_id)) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_data = wp_generate_attachment_metadata($attachment_id, $file_path);
            wp_update_attachment_metadata($attachment_id, $attach_data);
            return $attachment_id;
        }

        return false;
    }

}
