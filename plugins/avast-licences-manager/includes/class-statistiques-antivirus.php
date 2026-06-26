<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ALM_Statistiques_antivirus {

    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);

        add_action('wp_ajax_export_orders_xlsx',  [$this, 'export_orders_xlsx']); 

        add_action('woocommerce_admin_order_data_after_order_details', [$this, 'add_margin_fields']);
        add_action('woocommerce_process_shop_order_meta', [$this, 'save_margin_fields']);

        add_action('woocommerce_admin_order_data_after_order_details', [$this, 'add_delete_subscription_fields']);//un champ pour avoir id de la commande de reference sur une commande de renouvellment
        add_action('wp_ajax_delete_subscriptions_from_order', [$this, 'delete_subscriptions_from_order']);

        add_action('admin_head', function () {

            $screen = get_current_screen();

            if (!$screen) {
                return;
            }

            $allowed_screens = [
                'shop_order',
                'woocommerce_page_wc-orders'
            ];

            if (!in_array($screen->id, $allowed_screens)) {
                return;
            }

            ?>
            <style>
                .order_data_column {
                    display: flex;
                    flex-direction: column;
                }
            </style>
            <?php
        });
        
    }

    /**
     * Ajouter menu BO
     */
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            'Statistiques Antivirus',
            'Statistiques Antivirus',
            'manage_woocommerce',
            'alm_antivirus_stats',
            [$this, 'render_page']
        );

    }

    function get_custom_statut($statut, $methode, $is_renewal)
    {
        // Statuts figés
        if ($statut === 'Terminée') {
            return 'Terminée';
        }

        if ($statut === 'Annulée') {
            return 'Annulée';
        }

        // Configuration centralisée
        $mapping = [

            'Attente paiement' => [

                false => [ // commande normale

                    'Carte de crédit/débit' => 'Attente de paiement - Carte',
                    'Paiement en fin de mois' => 'Attente de paiement - fin de mois - pas livré',
                    'Virement bancaire' => 'Attente de paiement - Virement',
                    'Paiement par mandat administratif' => 'Attente de paiement - mandat adm - pas livré',
                    'Paiements par chèque' => 'Attente de paiement - chèque',
                ],

                true => [ // renewal

                    'Carte de crédit/débit' => 'Attente de paiement - BdC automatique',
                    'Paiement en fin de mois' => 'Attente de paiement - BdC automatique',
                    'Virement bancaire' => 'Attente de paiement - BdC automatique',
                    'Paiement par mandat administratif' => 'Attente de paiement - BdC automatique',
                    'Paiements par chèque' => 'Attente de paiement - BdC automatique',
                ]
            ],

            'En attente' => [

                false => [ // commande normale

                    'Carte de crédit/débit' => 'Attente de paiement - Carte',
                    'Paiement en fin de mois' => 'Attente de paiement - fin de mois - pas livré',
                    'Virement bancaire' => 'Attente de paiement - Virement',
                    'Paiement par mandat administratif' => 'Attente de paiement - mandat adm - pas livré',
                    'Paiements par chèque' => 'Attente de paiement - chèque',
                ],

                true => [ // renewal

                    'Carte de crédit/débit' => 'Attente de paiement - BdC automatique',
                    'Paiement en fin de mois' => 'Attente de paiement - BdC automatique',
                    'Virement bancaire' => 'Attente de paiement - BdC automatique',
                    'Paiement par mandat administratif' => 'Attente de paiement - BdC automatique',
                    'Paiements par chèque' => 'Attente de paiement - BdC automatique',
                ]
            ],

            'En cours' => [

                false => [
                    'default' => 'En cours',
                    'Paiement en fin de mois' => 'Attente de paiement - fin de mois - livré',
                    'Paiement par mandat administratif' => 'Attente de paiement - mandat adm - livré',
                ],

                true => [
                    'default' => 'En cours - BdC automatique'
                ]
            ]

        ];

        if (
            isset($mapping[$statut]) &&
            isset($mapping[$statut][$is_renewal])
        ) {

            $config = $mapping[$statut][$is_renewal];

            if (isset($config[$methode])) {
                return $config[$methode];
            }

            if (isset($config['default'])) {
                return $config['default'];
            }
        }

        return $statut;
    }

    /**
     * Affichage page
     */
    public function render_page() {

        if ( ! class_exists('WooCommerce') ) {
            echo '<div class="notice notice-error"><p>WooCommerce non actif.</p></div>';
            return;
        }

        $orders = wc_get_orders([
            'limit' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);

        echo '<div class="wrap">';
        echo '<link rel="stylesheet" href="https://cdn.datatables.net/2.3.7/css/dataTables.dataTables.min.css"/>';
        echo '<script src="https://cdn.datatables.net/2.3.7/js/dataTables.min.js"></script>';
        

        echo '<h1>Statistiques Antivirus</h1>';

        echo '<div id="status-filters">
            <div class="colonnes">
                <div class="colonne">
                    <label>
                        <input type="checkbox" value="Terminée" >
                        Terminée
                    </label>

                    <label>
                        <input type="checkbox" value="Attente paiement - BdC automatique">
                        Attente paiement - BdC automatique
                    </label>

                    <label>
                        <input type="checkbox" value="Attente de paiement - chèque">
                        Attente de paiement - chèque
                    </label>

                    <label>
                        <input type="checkbox" value="Attente de paiement - virement">
                        Attente de paiement - virement
                    </label>

             

                </div>

                <div class="colonne">

                    <label>
                        <input type="checkbox" value="Attente de paiement - mandat adm - pas livré">
                        Attente de paiement - mandat adm - pas livré
                    </label>

                    <label>
                        <input type="checkbox" value="Attente de paiement - mandat adm - livré">
                        Attente de paiement - mandat adm - livré
                    </label>

                    <label>
                        <input type="checkbox" value="Attente de paiement - fin de mois - pas livré">
                        Attente de paiement - fin de mois - pas livré
                    </label>

                    <label>
                        <input type="checkbox" value="Attente de paiement - fin de mois - livré">
                        Attente de paiement - fin de mois - livré
                    </label>
                </div>
            </div>
        </div>
        ';

        echo '<table  id="myTable" class="display">';
        echo '<thead>
                <tr>
                    <th><input type="checkbox" id="checkAll"></th>
                    <th>Commande</th>
                    <th>Status</th>
                    <th>Type de paiement</th>
                    <th>Date de création</th>
                    <th>Clés de licence</th>
                    <th>Total TTC</th>
                    <th>Utilisateur</th>
                    <th>Rôle</th>
                    <th>Renouvellement</th>
                </tr>
              </thead>';
        echo '<tbody>';

        if ($orders) {
            foreach ($orders as $order) {

                $user_id = $order->get_user_id();
                $order_id = $order->get_id();
                $type_commande = '';
                $is_renewal = false;
    
                if ( function_exists( 'wcs_order_contains_renewal' ) && wcs_order_contains_renewal( $order ) ) {
                    $type_commande = 'BdC automatique';
                    $is_renewal = true;
                } else {
                    $is_renewal = false;
                    $type_commande = 'Commande simple';
                }
                $user = $user_id ? get_user_by('id', $user_id) : null;
                $order_link = admin_url('post.php?post=' . $order_id . '&action=edit');
                $user_roles = $user ? $user->roles : ['Invité'];
                $order_date = $order->get_date_created()->date('Y-m-d H:i:s'); 
                $selected_client_id  = $order->get_meta('client_final');

                if($selected_client_id){
                    $client_final = get_user_by('id', $selected_client_id);
                }
                $status = wc_get_order_status_name($order->get_status());
                $methode = $order->get_payment_method_title();
                $custom_statut = $this->get_custom_statut($status,$methode,$is_renewal);
                $cles = '';


                // Retrieve license keys associated with the order ID using the LMFWC Controller
                $license_keys = \LicenseManagerForWooCommerce\Repositories\Resources\License::instance()->findAllBy(['order_id' => $order_id]);

                
                // Loop through the results to print or use the keys
                if (!empty($license_keys)) {
                    foreach ($license_keys as $licenseKey) {
                        // Get the actual decrypted license key string
                        $key_string =  esc_html($licenseKey->getDecryptedLicenseKey());
                        $cles .=  esc_html($key_string) . '<br>--------------<br>';
                    }
                } else {
                    $cles .= 'Aucune licence';
                }
                
                $pdf_url = wp_nonce_url( add_query_arg( array(
                    'action'        => 'generate_wpo_wcpdf',
                    'document_type' => 'invoice',
                    'order_ids'     => $order->get_id(),
                    'my-account'    => true,
                ), admin_url( 'admin-ajax.php' ) ), 'generate_wpo_wcpdf' );

                $invoice        = wcpdf_get_document('invoice', (array) $order->get_id(), true);
                if ($invoice) {
                    $invoice_number = "Facture #{$invoice->get_number()}";
                } else {
                    $invoice_number = "";
                }
                $text = sprintf( '<p><a href="%s" target="_blank">%s</a></p>', esc_attr( $pdf_url ), esc_html( $invoice_number ) );
    
                $billing_type_client_value = get_user_meta($user_id, 'billing_type_client', true);
           
                if (in_array('customer_direct', $user_roles)) {
                    $roles_string = 'Client Direct '.$billing_type_client_value;
                } elseif (in_array('customer_revendeur', $user_roles)) {
                    $roles_string = 'Revendeur';
                } else {
                    $roles_string = ''; // fallback
                }

                
                
                $subscriptions = wcs_get_subscriptions_for_order($order_id, array('order_type' => 'parent'));
                $closest_date = null;
                $closest_subscription = null;

                if (!empty($subscriptions)) {
                    foreach ($subscriptions as $subscription) {
                        $next_payment = $subscription->get_date('next_payment');
                        if (!$next_payment) {
                            continue; // ignore si pas de date
                        }
                        $timestamp = strtotime($next_payment);
                        if (!$closest_date || $timestamp < $closest_date) {
                            $closest_date = $timestamp;
                            $closest_subscription = $subscription;
                        }
                    }
                }
                $order_closest_date = $closest_date ? date('Y-m-d H:i:s', $closest_date) : '';



                echo '<tr>';
                echo '<td><input type="checkbox" class="rowCheck"></td>';
                echo '<td><span style="display:none" class="order_id">'.$order_id.'</span> <a href="' . esc_url($order_link) . '" target="_blank">#'. esc_html($order_id) . '</a><br>'.$text.'</td>';
                echo '<td>' . ($custom_statut) .'</td>';
                echo '<td>' . ($methode) . '</td>';
                echo '<td  data-order="'.esc_attr($order_date).'">' . esc_html($order->get_date_created()->date('d/m/Y H:i')) . '</td>';
                echo '<td class="cles-col">'. $cles. '</td>';
                echo '<td>' . wc_price($order->get_total()) . '</td>';
                echo '<td>' . ($user ? esc_html($user->display_name) : 'Invité') . '<br>';
                echo  ($user ? esc_html($user->user_email) : '')  . '<br>';
                echo  ($selected_client_id ? esc_html('Client final : '.$client_final->display_name) : '') . '</td>';
                echo '<td>' . $roles_string . '</td>';
                echo '<td>';
                    if (($subscriptions)) {
                        foreach ( $subscriptions as $subscription ) {
                            $subscription_id = $subscription->get_id();
                            if(!$subscription->get_requires_manual_renewal()){
                                $subscription->set_requires_manual_renewal(true);
                                $subscription->save();
                            }
                            $subs_link = admin_url('post.php?post=' . $subscription_id . '&action=edit');
                            $subs_total = $subscription->get_total();
                            $user = $subscription->get_user();
                            $next_payment = $subscription->get_date( 'next_payment' );
                            $billing_period = $subscription->get_billing_period();
                            $billing_interval = $subscription->get_billing_interval();
                            $renouvellement_manuel = $subscription->get_requires_manual_renewal();
                            echo '<a href="' . esc_url($subs_link) . '" target="_blank">#' . $subscription_id.'</a>';
                            echo '<br>total : ' . wc_price($subs_total);
                            echo '<br>prochain paiement : ' .  date_i18n('d/m/Y à H:i', strtotime($next_payment));
                            if( $renouvellement_manuel){
                                echo '<br>renouvellement manuel';
                            }else{
                                echo '<br>renouvellement automatique';
                            }
                            
                            echo '<br>--------------<br>';
                        }
                    }else{
                        echo 'pas d\'abonnements<br>';
                        if($is_renewal){
                            echo $type_commande;
                        }
                    }
                echo '</td>';

                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="4">Aucune commande trouvée</td></tr>';
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';

        echo '<input type="hidden" id="ordersToExport" value="">';
        
        echo '<button type="button" id="exportCsv" class="woocommerce-Button button custom-admin-action">📥 Exporter la sélection</button>';

        echo "<style>
        table.dataTable>thead>tr>th, table.dataTable>thead>tr>td{ border: 1px solid rgba(0, 0, 0, 0.3) !important; background-color: #ff7800; color: #fff}
         table.dataTable>tbody>tr>td{ border: 1px solid rgba(255, 255, 255, 0.3) !important; background-color:#fff}
         .custom-admin-action { color: #ffffff !important; border-color: #ff7800 !important; background: #ff7800 !important; border-radius: 13px !important; padding: 6px 23px !important;}
         table.dataTable thead>tr>th.dt-orderable-desc .dt-column-order:after{ opacity: .425 !important; color:black !important}
        .dt-paging-button.current{background: #ff7800 !important;}
        .dt-paging-button{background: #ff77006e !important;color: #fff !important}
        .colonnes{display:flex;gap:30px}.colonne{display: flex;flex-direction: column;}
        .cles-col{width: 100px; max-width:100px; min-width:100px; overflow:hidden; word-break:break-all;overflow-wrap:anywhere;}
        </style>";
        echo "<script>
            const table = new DataTable('#myTable', {
                pageLength: 50,
                info: false,
                language: {
                    search:         'Rechercher',
                    lengthMenu: 'Afficher _MENU_ commandes',
                },
                autoWidth: false,
                columns: [
                    { orderable: false },
                    { orderable: true },
                     { orderable: true },
                    { orderable: true },
                     { orderable: true},
                     { orderable: true, width: '150px'  },
                    { orderable: true },
                    { orderable: true, width: '150px' },
                    { orderable: false, width: '100px' },
                    { orderable: false,width: '200px' }
                ]
            });

            jQuery(document).ready(function($){

                function applyStatusFilter() {
                    let selected = [];

                    $('#status-filters input[type=checkbox]:checked').each(function() {
                        selected.push($(this).val());
                    });

                    if (selected.length === 0) {
                        table.column(2).search('').draw();
                        return;
                    }

                    let regex = '^(' + selected.join('|') + ')$';
                    console.log(regex)

                    table.column(2).search(regex, true, false).draw();
                }

                $('#status-filters input[type=checkbox]').on('change', applyStatusFilter);

                // Appliquer les filtres cochés par défaut
                applyStatusFilter();
               
                $('#checkAll').on('click', function() {
                    $('.rowCheck').prop('checked', this.checked);
                });

                $('#exportCsv').on('click', function(e){
                    e.preventDefault();

                    // Récupérer tous les order_id cochés
                    let orderIds = [];
                    $('.rowCheck:checked').each(function(){
                        let orderId = $(this).closest('tr').find('td:nth-child(2) .order_id').text().trim();
                        if (orderId) {
                            orderIds.push(orderId);
                        }
                    });

                    console.log('orderIds',orderIds)

                    if (orderIds.length === 0) {
                        alert('Veuillez sélectionner au moins une commande');
                        return false;
                    }

                    // Créer un formulaire de soumission
                    let form = $('<form>', {
                        'method': 'POST',
                        'action': '" . admin_url('admin-ajax.php') . "'
                    });

                    form.append($('<input>', {
                        'type': 'hidden',
                        'name': 'action',
                        'value': 'export_orders_xlsx'
                    }));

                    form.append($('<input>', {
                        'type': 'hidden',
                        'name': 'orders',
                        'value': orderIds.join(',')
                    }));

                    $('body').append(form);
                    form.submit();
                    form.remove();
                });
            });
            </script>";
    }


    public function export_orders_csv() {
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error('Pas la permission');
            wp_die();
        }

        if (empty($_POST['orders'])) {
            wp_send_json_error('Aucune commande sélectionnée');
            wp_die();
        }

        error_log('ids : '  . $_POST['orders']);

        // Tableau d'IDs
        $order_ids = array_map('intval', explode(',', $_POST['orders']));

        // Définir l'entête CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=export_commandes.csv');

        $output = fopen('php://output', 'w');

        // Colonnes
        fputcsv($output, ['Commande', 'N° Facture', 'Mode paiement', 'Statut', 'Societe', 'Nom', 'Prenom', 'Email','Client final', 'Rôle', 'Date Commande', 'Date paiement', 'Date prochain paiement',   'Pays', 'Total HT','TVA %','Total TVA','Total TTC', 'Prix réel','Marge HT',  'Abonnements / Renouvellements']);

        foreach ($order_ids as $order_id) {
            $order = wc_get_order($order_id);
            if (!$order) continue;

            $user_id = $order->get_user_id();
            $user = $user_id ? get_user_by('id', $user_id) : null;
            $user_roles = $user ? $user->roles : ['Invité'];
            $societe = get_user_meta($user->ID, 'denomination', true);
            $nom = get_user_meta($user->ID, 'last_name', true);
            $prenom = get_user_meta($user->ID, 'first_name', true);
            $email = $user->user_email;
            $pays = get_user_meta($user->ID, 'pays', true);
            $selected_client_id  = $order->get_meta('client_final');

            if($selected_client_id){
                $client_final = get_user_by('id', $selected_client_id);
            }
            $client_final_name = $selected_client_id ? $client_final->display_name : $user->display_name;
            // Transformer les rôles
            $billing_type_client_value = get_user_meta($user->ID, 'billing_type_client', true);
           
            if (in_array('customer_direct', $user_roles)) {
                $roles_string = 'Client Direct '.$billing_type_client_value;
            } elseif (in_array('customer_revendeur', $user_roles)) {
                $roles_string = 'Revendeur';
            } else {
                $roles_string = ''; // fallback
            }
            $status = wc_get_order_status_name($order->get_status());
            $total_ht = $order->get_total() - $order->get_total_tax();
            $total_ttc = $order->get_total();
            $total_tva = $order->get_total_tax();
            $margin    = get_post_meta($order_id, '_order_margin', true);
            $real_cost = get_post_meta($order_id, '_real_cost', true);
            $invoice = wcpdf_get_document( 'invoice', (array) $order->get_id(), true ); // true sets 'init' which makes sure an invoice number is created;
                $invoice_number = $invoice->get_number();
            
            $tva_percent = 0;
            if ($total_ht > 0) {
                $tva_percent = round(($total_tva / $total_ht) * 100);
            }

            // Abonnements / renouvellements
            $subscriptions_text = '';
            if (function_exists('wcs_get_subscriptions_for_order')) {
                $subscriptions = wcs_get_subscriptions_for_order($order_id, ['order_type'=>'parent']);
                if ($subscriptions) {
                    foreach ($subscriptions as $subscription) {
                        
                        
                        $subs_id = $subscription->get_id();
                        $subs_total = $subscription->get_total();
                        $next_payment = $subscription->get_date('next_payment');
                        $manual = $subscription->get_requires_manual_renewal() ? 'manuel' : 'automatique';

                        $subscriptions_text .= "#$subs_id | total: $subs_total | prochain paiement: " . ($next_payment ? date_i18n('d/m/Y H:i', strtotime($next_payment)) : '—') . " | renouvellement: $manual\n";
                    }
                } else {
                    $subscriptions_text = '';
                }
            }

            $closest_date = null;
            $closest_subscription = null;

            if (!empty($subscriptions)) {
                foreach ($subscriptions as $subscription) {
                    $next_payment = $subscription->get_date('next_payment');
                    if (!$next_payment) {
                        continue; // ignore si pas de date
                    }
                    $timestamp = strtotime($next_payment);
                    if (!$closest_date || $timestamp < $closest_date) {
                        $closest_date = $timestamp;
                        $closest_subscription = $subscription;
                    }
                }
            }
            $order_closest_date = $closest_date ? date('d/m/Y H:i', $closest_date) : '';

            

            

            fputcsv($output, [
                '#' . $order_id,
                $invoice_number,
                $order->get_payment_method_title(),
                $status,
                $societe,
                $nom,
                $prenom,
                $email,
                $client_final_name,
                $roles_string,
                $order->get_date_created()->date('d/m/Y H:i'),
                ($order->get_date_paid())?$order->get_date_paid()->date('d/m/Y H:i'):'',
                $order_closest_date,
                $pays,
                $total_ht,
                $tva_percent,
                $total_tva,
                $total_ttc,
                $real_cost,
                $margin ,
                $subscriptions_text
            ]);
        }

        fclose($output);
        exit;
    }




    public function export_orders_xlsx() {
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error('Pas la permission');
            wp_die();
        }

        if (empty($_POST['orders'])) {
            wp_send_json_error('Aucune commande sélectionnée');
            wp_die();
        }

        $order_ids = array_map('intval', explode(',', $_POST['orders']));

        require_once  __DIR__ . '/../libs/SimpleXLSXGen.php';
        $rows = [];

        $rows[] = [
            'Commande', 'N° Facture','type de commande', 'Mode paiement', 'Statut', 'Societe',
            'Nom', 'Prenom', 'Email', 'Client final', 'Rôle', 'Date Commande',
            'Date paiement', 'Date prochain paiement', 'Pays',
            'Total HT', 'TVA %', 'Total TVA', 'Total TTC',
            'Prix réel', 'Marge HT', 'Abonnements / Renouvellements','Clés de licence'
        ];

        foreach ($order_ids as $order_id) {
            $order = wc_get_order($order_id);
            if (!$order) continue;

            $user_id = $order->get_user_id();
            $user    = $user_id ? get_user_by('id', $user_id) : null;
            if (!$user) continue;

             $is_renewal = false;
    
            if ( function_exists( 'wcs_order_contains_renewal' ) && wcs_order_contains_renewal( $order ) ) {
                $type_commande = 'BdC automatique';
                $is_renewal = true;
            } else {
                $is_renewal = false;
                $type_commande = 'Commande simple';
            }

            $user_roles = $user->roles;
            $societe    = get_user_meta($user->ID, 'denomination', true);
            $nom        = get_user_meta($user->ID, 'last_name', true);
            $prenom     = get_user_meta($user->ID, 'first_name', true);
            $email      = $user->user_email;
            $pays       = get_user_meta($user->ID, 'pays', true);

            $selected_client_id = $order->get_meta('client_final');
            $client_final_name  = $user->display_name;
            if ($selected_client_id) {
                $client_final      = get_user_by('id', $selected_client_id);
                $client_final_name = $client_final ? $client_final->display_name : $client_final_name;
            }

            $billing_type_client_value = get_user_meta($user->ID, 'billing_type_client', true);
            if (in_array('customer_direct', $user_roles)) {
                $roles_string = 'Client Direct ' . $billing_type_client_value;
            } elseif (in_array('customer_revendeur', $user_roles)) {
                $roles_string = 'Revendeur';
            } else {
                $roles_string = '';
            }

            $status    = wc_get_order_status_name($order->get_status());
            $total_ht  = $order->get_total() - $order->get_total_tax();
            $total_ttc = $order->get_total();
            $total_tva = $order->get_total_tax();
            $margin    = get_post_meta($order_id, '_order_margin', true);
            $real_cost = get_post_meta($order_id, '_real_cost', true);

            $invoice        = wcpdf_get_document('invoice', (array) $order->get_id(), true);
            if ($invoice) {
                $invoice_number = "#{$invoice->get_number()}";
            } else {
                $invoice_number = "";
            }

            $cles = '';
            // Retrieve license keys associated with the order ID using the LMFWC Controller
            $license_keys = \LicenseManagerForWooCommerce\Repositories\Resources\License::instance()->findAllBy(['order_id' => $order_id]);

            // Loop through the results to print or use the keys
            if (!empty($license_keys)) {
                foreach ($license_keys as $licenseKey) {
                    // Get the actual decrypted license key string
                    $key_string =  esc_html($licenseKey->getDecryptedLicenseKey());
                    $cles .=  esc_html($key_string) . '  ';
                }
            } else {
                $cles .= 'Aucune licence';
            }

            $tva_percent = ($total_ht > 0) ? round(($total_tva / $total_ht) * 100) : 0;

            $subscriptions_text = '';
            $closest_date       = null;

            if (function_exists('wcs_get_subscriptions_for_order')) {
                $subscriptions = wcs_get_subscriptions_for_order($order_id, ['order_type' => 'parent']);
                foreach ((array) $subscriptions as $subscription) {
                    $subs_id      = $subscription->get_id();
                    $subs_total   = $subscription->get_total();
                    $next_payment = $subscription->get_date('next_payment');
                    $manual       = $subscription->get_requires_manual_renewal() ? 'manuel' : 'automatique';
                    $subscriptions_text .= "#$subs_id | total: $subs_total | prochain paiement: "
                        . ($next_payment ? date_i18n('d/m/Y H:i', strtotime($next_payment)) : '—')
                        . " | renouvellement: $manual\n";

                    if ($next_payment) {
                        $ts = strtotime($next_payment);
                        if (!$closest_date || $ts < $closest_date) {
                            $closest_date = $ts;
                        }
                    }
                }
            }

            $order_closest_date = $closest_date ? date('d/m/Y H:i', $closest_date) : '';

            $rows[] = [
                '#' . $order_id,
                $invoice_number,
                $type_commande,
                $order->get_payment_method_title(),
                $status,
                $societe,
                $nom,
                $prenom,
                $email,
                $client_final_name,
                $roles_string,
                $order->get_date_created()->date('d/m/Y H:i'),
                $order->get_date_paid() ? $order->get_date_paid()->date('d/m/Y H:i') : '',
                $order_closest_date,
                $pays,
                (float) $total_ht,
                (int) $tva_percent,
                (float) $total_tva,
                (float) $total_ttc,
                (float) $real_cost,
                (float) $margin,
                rtrim($subscriptions_text),
                $cles
            ];
        }

        ob_end_clean();
        $xlsx = \Shuchkin\SimpleXLSXGen::fromArray($rows);
        $content = (string) $xlsx;
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="export_commandes.xlsx"');
        header('Content-Length: ' . strlen($content));
        header('Cache-Control: max-age=0');
        echo $content;
        exit;
    }

    


    public function add_margin_fields($order) {

        $order_id  = $order->get_id();
        $real_cost = get_post_meta($order_id, '_real_cost', true);
        $margin    = get_post_meta($order_id, '_order_margin', true);

        echo '<div class="order_margin_section" style="top: 20px;position: relative;">';
        echo '<h3>Calcul de marge</h3>';
         echo '<p>
                Marge = Prix HT - Prix réel
            </p>';

        echo '<p>
                <label>Prix réel (coût) :</label>
                <input type="number" step="0.01" name="real_cost" 
                    value="'.esc_attr($real_cost).'" />
            </p>';

        echo '<p><strong>Marge :</strong> ' 
            . ($margin !== '' ? wc_price($margin) : '—') 
            . '</p>';

        echo '</div>';
    }

    public function save_margin_fields($order_id) {

        if (!isset($_POST['real_cost'])) {
            return;
        }

        $real_cost = floatval($_POST['real_cost']);
        update_post_meta($order_id, '_real_cost', $real_cost);

        $order = wc_get_order($order_id);

        $total_ht = $order->get_total() - $order->get_total_tax();

        // ⚠ adapte ici si tu veux TTC
        $margin = $total_ht - $real_cost;

        update_post_meta($order_id, '_order_margin', $margin);
        
    }


    public function add_delete_subscription_fields($order) {

        $order_id = $order->get_id();
        $reference_order_id = get_post_meta($order_id, '_reference_order_id', true);

        ?>
        <div class="delete_subscription_section" style="top: 20px;position: relative;">
            <h3>Suppression abonnements</h3>
            <p>
            S'il s'agit d'un renouvellement, il faut indiquer dans la commande, le numéro de la commande qui a été renouvelé pour éviter que le client ou revendeur ne reçoive des relances Emails sur une commande qu'il a déjà renouvelée
            </p>
            <p>
                <label>ID commande de référence :</label>
                <input type="number" id="reference_order_id"
                    value="<?php echo esc_attr($reference_order_id); ?>" />
                <input type="hidden" id="order_id"
                    value="<?php echo esc_attr($order_id); ?>" />
            </p>

            <p>
                <button type="button" class="button button-primary"
                        id="delete_subscriptions_btn">
                    Supprimer abonnements
                </button>
            </p>

            <div id="delete_subscriptions_result"
                style="margin-top:10px;font-weight:bold;"></div>
        </div>

        <script>
        jQuery(function($){

            $('#delete_subscriptions_btn').on('click', function(){

                let reference_id = $('#reference_order_id').val();
                let order_id = $('#order_id').val();

                if (!reference_id) {
                    alert('Veuillez saisir un ID valide.');
                    return;
                }

                $('#delete_subscriptions_result').html('Suppression en cours...');

                $.post(ajaxurl, {
                    action: 'delete_subscriptions_from_order',
                    reference_order_id: reference_id,
                    order_id: order_id
                }, function(response){

                    $('#delete_subscriptions_result').html(response);

                });

            });

        });
        </script>
        <?php
    }



    public function delete_subscriptions_from_order() {

        if (!current_user_can('manage_woocommerce')) {
            wp_die('Permission refusée');
        }

        $reference_order_id = intval($_POST['reference_order_id']);
        $order_id = intval($_POST['order_id']);
        update_post_meta($order_id, '_reference_order_id', $reference_order_id);

        if (!$reference_order_id) {
            echo 'ID invalide.';
            wp_die();
        }

        if (!function_exists('wcs_get_subscriptions_for_order')) {
            echo 'WooCommerce Subscriptions non actif.';
            wp_die();
        }

        $subscriptions = wcs_get_subscriptions_for_order(
            $reference_order_id,
            array('order_type' => 'parent')
        );

        if (empty($subscriptions)) {
            echo 'Aucun abonnement trouvé.';
            wp_die();
        }

        $deleted_ids = [];

        foreach ($subscriptions as $subs) {

            $sub_id = $subs->get_id();

            $subs->delete(true); // suppression définitive

            $deleted_ids[] = $sub_id;
        }

        echo 'Abonnements supprimés : ' . implode(', ', $deleted_ids);

        wp_die();
    }




}


