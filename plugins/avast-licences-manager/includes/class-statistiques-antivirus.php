<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ALM_Statistiques_antivirus {

    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);

        add_action('wp_ajax_export_orders_csv',  [$this, 'export_orders_csv']); 
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

    function format_role($role) {
        // Exemple : majuscule et remplacement de "_" par " "
        $res = ucwords(str_replace('customer_', '', $role));
        return $res;
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
            'limit' => 50,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);

        echo '<div class="wrap">';
        echo '<link rel="stylesheet" href="https://cdn.datatables.net/2.3.7/css/dataTables.dataTables.min.css"/>';
        echo '<script src="https://cdn.datatables.net/2.3.7/js/dataTables.min.js"></script>';
        

        echo '<h1>Statistiques Antivirus</h1>';

        echo '<table  id="myTable" class="display">';
        echo '<thead>
                <tr>
                    <th><input type="checkbox" id="checkAll"></th>
                    <th>Commande</th>
                    <th>Date</th>
                    <th>Total</th>
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
                $user = $user_id ? get_user_by('id', $user_id) : null;
                $order_link = admin_url('post.php?post=' . $order_id . '&action=edit');
                $user_roles = $user ? $user->roles : ['Invité'];
                $order_date = $order->get_date_created()->date('Y-m-d H:i:s'); 

                // Appliquer les transformations sur chaque rôle
                $user_roles_formatted = array_map(function($role) {

                    // Exemple : remplacer 'subscriber' par 'abonné'
                    if ($role === 'subscriber') $role = 'abonné';

                    // Supprimer 'customer_' si présent
                    $role = str_replace('customer_', '', $role);

                    // Mettre en majuscule première lettre
                    return ucwords($role);

                }, $user_roles);
                
                $subscriptions = wcs_get_subscriptions_for_order($order_id, array('order_type' => 'parent'));

                echo '<tr>';
                echo '<td><input type="checkbox" class="rowCheck"></td>';
                echo '<td><a href="' . esc_url($order_link) . '" target="_blank">#' . esc_html($order_id) . '</a></td>';
                echo '<td  data-order="'.esc_attr($order_date).'">' . esc_html($order->get_date_created()->date('d/m/Y H:i')) . '</td>';
                echo '<td>' . wc_price($order->get_total()) . '</td>';
                echo '<td>' . ($user ? esc_html($user->display_name) : 'Invité') . '<br>';
                echo  ($user ? esc_html($user->user_email) : '') . '</td>';
                echo '<td>' . implode('<br>', $user_roles_formatted) . '</td>';
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
                        echo 'pas d\'abonnements';
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
        </style>";
        echo "<script>
            new DataTable('#myTable', {
                pageLength: 50,
                info: false,
                language: {
                    search:         'Rechercher',
                    lengthMenu: 'Afficher _MENU_ commandes',
                },
                columns: [
                    { orderable: false },
                    { orderable: true },
                    { orderable: true },
                    { orderable: true },
                    { orderable: true },
                    { orderable: false },
                    { orderable: false }
                ]
            });

            jQuery(document).ready(function($){
                $('#checkAll').on('click', function() {
                    $('.rowCheck').prop('checked', this.checked);
                });

                $('#exportCsv').on('click', function(e){
                    e.preventDefault();

                    // Récupérer tous les order_id cochés
                    let orderIds = [];
                    $('.rowCheck:checked').each(function(){
                        let orderId = $(this).closest('tr').find('td:nth-child(2) a').text().replace('#','').trim();
                        if (orderId) {
                            orderIds.push(orderId);
                        }
                    });

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
                        'value': 'export_orders_csv'
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

        // Tableau d'IDs
        $order_ids = array_map('intval', explode(',', $_POST['orders']));

        // Définir l'entête CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=export_commandes.csv');

        $output = fopen('php://output', 'w');

        // Colonnes
        fputcsv($output, ['Commande', 'Date', 'Total', 'Utilisateur', 'Email', 'Rôle', 'Abonnements / Renouvellements']);

        foreach ($order_ids as $order_id) {
            $order = wc_get_order($order_id);
            if (!$order) continue;

            $user_id = $order->get_user_id();
            $user = $user_id ? get_user_by('id', $user_id) : null;
            $user_roles = $user ? $user->roles : ['Invité'];

            // Transformer les rôles
            $user_roles_formatted = array_map(function($role){
                if ($role === 'subscriber') $role = 'abonné';
                $role = str_replace('customer_', '', $role);
                return ucwords($role);
            }, $user_roles);

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
                    $subscriptions_text = 'pas d\'abonnements';
                }
            }

            fputcsv($output, [
                '#' . $order_id,
                $order->get_date_created()->date('d/m/Y H:i'),
                $order->get_total(),
                $user ? $user->display_name : 'Invité',
                $user ? $user->user_email : '',
                implode(', ', $user_roles_formatted),
                $subscriptions_text
            ]);
        }

        fclose($output);
        exit;
    }

}


