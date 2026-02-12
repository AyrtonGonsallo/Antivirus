<?php
//include_once WC_ABSPATH . 'includes/wc-cart-functions.php';


class DevisPDFGenerator {

    public static function generate_pdf($post_id,$variation_id) {

        // 1. Récupération des données du devis
        $title        = get_the_title($post_id);
        $status       = get_field('status', $post_id)["label"];
        $type_devis   = get_field('type_de_devis', $post_id)["label"];
        $user      = get_field('utilisateur', $post_id);
        $client_final      = get_field('client_final', $post_id);
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
        //$produits_de_la_commande     =    get_field('produits_de_la_variation', $variation_id);
        $formule     =    get_field('formule', $variation_id);
        $variation_title     =    get_the_title( $variation_id);
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

        $client_final_id = $client_final->ID;

        $client_final_prenom = get_user_meta($client_final_id, 'first_name', true);
        $client_final_nom = get_user_meta($client_final_id, 'last_name', true);
        $client_final_ville = get_user_meta($client_final_id, 'ville', true);
		$client_final_code_postal = get_user_meta($client_final_id, 'code_postal', true);
		$client_final_selected_pays = get_user_meta($client_final_id, 'pays', true);
        $client_final_civilite = get_user_meta($client_final_id, 'civilite', true);
        $client_final_denomination = get_user_meta($client_final_id, 'denomination', true);
        $client_final_billing_address_1 = get_user_meta($client_final_id, 'billing_address_1', true);
        $client_final_billing_phone = get_user_meta($client_final_id, 'billing_phone', true);
      

        $today = current_time('d/m/Y g:i a'); // même format que date_de_creation


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
                    <td style="width: 50%;">';
                    if($client_final){
                        $html .='<div>
                            <p>Coordonnées du client :</p>
                            <p><strong>Client :</strong> '.$client_final_civilite.' '.$client_final_nom.' '.$client_final_prenom.'</p>
                            <p><strong>Adresse de facturation :</strong> '.$client_final_billing_address_1.'</p>
                            <p><strong>Téléphone :</strong> '.$client_final_billing_phone.'</p>
                            <p>'.$client_final_code_postal.' '.$client_final_ville.' '.$client_final_selected_pays.'</p>
                        </div>';
                    }
                      
                    $html .='</td>
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
                <div class="section-title">Devis N° '.$variation_id.'</div>';
                    
                $html .= $formule;

            $html .= '
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

        $file_path = $folder . sanitize_title($variation_title).'.pdf';

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
        $file_url  = $upload_dir['baseurl'] . '/devis/' . sanitize_title($variation_title) . '.pdf';
        $attachment_id = self::register_pdf_as_media($file_path, $file_url, $variation_id);

        // 6. Mettre à jour le champ ACF type "File"
        if ($attachment_id) {
            update_field('recapitulatif_pdf', $attachment_id, $variation_id);
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
            'post_title'     => "$variation_title",
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
