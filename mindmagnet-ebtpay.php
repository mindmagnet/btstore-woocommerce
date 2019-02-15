<?php

/*
  Plugin Name: MindMagnet_BTPay
  Plugin URI: http://www.mmecommerce.com
  Description: MindMagnet eBTpay module for Woocommerce
  Version: 0.1.0
  Author: MindMagnet Software
  Author URI: http://www.mindmagnetsoftware.com

  Copyright: MindMagnet Software 2014
  License:
  License URI:
 */

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

session_start();
/**
 * Required functions
 */
if (!function_exists('woothemes_queue_update'))
    require_once dirname(__FILE__) . '/woo-includes/woo-functions.php' ;

/**
 * Plugin updates
 */
add_action('plugins_loaded', 'wc_mindmagnet_btpay_standard_init', 0);
add_action('add_meta_boxes', 'adding_custom_meta_boxes', 10, 2);

function adding_custom_meta_boxes() {

    if (isset($_GET['post'])) {
        $order = new WC_Order(intval($_GET['post']));
        $order->get_data($_GET['post']);
        $order_id = $order->get_id();
    } else {
        $order = new WC_Order(intval($p['DESC']));
        $order->get_data(intval($p['DESC']));
        $order_id = $order->get_id();
    }

    $meta = get_post_meta($order->get_id());

    if ($_SERVER['PHP_SELF'] == '/wp-admin/post.php' && strstr(strtolower($meta['_payment_method']['0']), 'btpay'))
        add_meta_box('metabox', 'Transaction History', 'generateTable', 'shop_order', 'normal', 'core');
}

function generateTable() {
    include_once dirname(__FILE__).'/includes/TransactionDB.php';

    global $wpdb;
    $db = new TransactionDB($wpdb);

    if (isset($_GET['post'])) {
        $order = new WC_Order(intval($_GET['post']));
        $order->get_data($_GET['post']);
        $order_id = $order->get_id();
    } else {
        $order = new WC_Order(intval($p['DESC']));
        $order->get_data(intval($p['DESC']));
        $order_id = $order->get_id();
    }

    $transactions = $db->get($order_id);

    if (!empty($transactions)) {
        $table = '<br><center>
                <table class="wp-list-table widefat fixed posts">
        <thead>
        <tr>
            <th>Date</th>
            <th>Transaction Type</th>
            <th>Status</th>
            <th>Amount</th>
            <th>Order</th>
            <th>RRN</th>
            <th>IntRef</th>
            <th>Payment Method Code</th>
            <th>Gateway Response Message</th>
        </tr>
    </thead>
    <tbody>';

        foreach ($transactions as $tr) {
            $table .= '<tr>'
                    . '<td>' . $tr->created_at . '</td>'
                    . '<td>' . $tr->transaction_type . '</td>'
                    . '<td>' . '<img src="' . plugin_dir_url(__FILE__) . '/assets/images/' . ($tr->transaction_status == '1' ? 'enabled' : 'disabled') . '.gif"></img>' . '</td>'
                    . '<td>' . number_format((float) $tr->amount_processed, 2, '.', '') . ' ' . $tr->currency_code . '</td>'
                    . '<td>' . $tr->order_name . '</td>'
                    . '<td>' . $tr->rrn . '</td>'
                    . '<td>' . $tr->int_ref . '</td>'
                    . '<td>' . $tr->payment_method . '</td>'
                    . '<td>' . $tr->response_message . '</td>'
                    . '</tr>';
        }

        $table .='</tbody>
                    </table>
                    </center>
                    <h2>BT Pay API Calls</h2>
                    <a style="display: block; position: relative; top: -250px; visibility: hidden;" name="api"></a>
                    ';

        echo $table;
    } else
        echo 'No transactions yet.';

    $args = array(
        'post_type' => 'shop_order',
        'post_status' => 'publish',
        'meta_key' => '_customer_user',
        'posts_per_page' => '-1'
    );
    $my_query = new WP_Query($args);
    $customer_orders = $my_query->posts;

    foreach ($customer_orders as $customer_order) {
        $ordert = new WC_Order();
        $ordert->populate($customer_order);
        if ($ordert->get_id() == $_GET['post'])
            $orderdata = (array) $ordert;
    }

    $meta = get_post_meta($order->get_id());

    $theorder = new WC_Order($_GET['post']);
    $totals = floatval($theorder->get_total());
    $currency = $theorder->get_currency();
    $desc = $theorder->get_id();


    $settings = get_option('woocommerce_MindMagnet_BTPay_Standard_settings');
    $_SESSION['action']['total'] = $totals;
    $_SESSION['action']['desc'] = $desc;
    $_SESSION['action']['testmode'] = $settings['payment_btpay_api_test'];
    $_SESSION['action']['encryptionkey'] = $settings['payment_btpay_api_encryption_key'];
    $_SESSION['action']['merchantname'] = $settings['payment_btpay_api_merchant_name'];
    $_SESSION['action']['merchanturl'] = $settings['payment_btpay_api_merchant_url'];
    $_SESSION['action']['merchantemail'] = $settings['payment_btpay_api_merchant_email'];
    $_SESSION['action']['terminal'] = $settings['payment_btpay_api_terminal'];
    $_SESSION['action']['rambursare'] = '0';
    $_SESSION['method'] = $tr->payment_method;

    $html = '<br>';
    $html .= '<script>'
            . 'function showhide(){'
            . ' if(document.getElementById(\'btpay_extra_fields\').style.display ==\'none\')'
            . ' document.getElementById(\'btpay_extra_fields\').style.display =\'block\';'
            . ' else'
            . ' document.getElementById(\'btpay_extra_fields\').style.display =\'none\'; '
            . '} '
            . '</script>';

    $html .= '<script>'
            . 'function apicall(){'
            . 'var amount = document.getElementById("btpay_amount").value;'
            . 'var currency = document.getElementById("btpay_currency").value;'
            . 'var order = document.getElementById("btpay_order").value;'
            . 'var rrn = document.getElementById("btpay_rrn").value;'
            . 'var intref = document.getElementById("btpay_int_ref").value;'
            . ' if(document.getElementById(\'btpay_api_call_action\').value == \'capture\')'
            . ' window.location = "../wp-content/plugins/woocommerce-mindmagnet-ebtpay/includes/lib/btpaygate/capture.php?amount="+amount+"&currency="+currency+"&order="+order+"&rrn="+rrn+"&intref="+intref;'
            . 'else '
            . 'window.location = "../wp-content/plugins/woocommerce-mindmagnet-ebtpay/includes/lib/btpaygate/void.php?amount="+amount+"&currency="+currency+"&order="+order+"&rrn="+rrn+"&intref="+intref;'
            . ' } '
            . '</script>';

    $html .= '<label for="btpay_api_call_action">API Call Action:</label><br>
               <select id="btpay_api_call_action" name="btpay_api_call_action">';
    $html .= '<option value="capture">Încasare plată</option>' . "\n";
    $html .= '<option value="void">Anulare plată</option>' . "\n";
    $html .='</select><br>';

    $html .= '<label for="btpay_amount">Suma de plată:</label><br>';
    $html .= '<input type="text" size="20" id="btpay_amount" name="btpay_amount" value="' . number_format($totals, 2, '.', '') . '" />';
    $html .= '<p style="display:inline; margin-left:25px">Suma totala de plata. Trebuie sa contina 2 zecimale separate prin punct (Ex: 10.23 )</p>';

    $html .= '<div id="btpay_extra_fields" style="display: none">';

    $html .= '<label for="btpay_currency">Currency ISO Code:</label><br>';
    $html .= '<input type="text" size="20" id="btpay_currency" name="btpay_currency" value="' . $currency . '" />';
    $html .= '<p style="display:inline; margin-left:25px">Moneda in care se face tranzactia. In functie de banca poate fi: RON, USD, EUR</p>';

    $html .= '<br><label for="btpay_order">Order:</label><br>';
    $html .= '<input type="text" size="20" id="btpay_order" name="btpay_order" value="' . '1' . str_pad($order->get_id(), 6, "0", STR_PAD_LEFT) . '" />';
    $html .= '<p style="display:inline; margin-left:25px">Nu modificați acest câmp. Numarul comenzii generat de catre comerciant.</p>';

    $html .= '<br><label for="btpay_rrn">RRN:</label><br>';
    $html .= '<input type="text" size="20" id="btpay_rrn" name="btpay_rrn" value="' . $tr->rrn . '" />';
    $html .= '<p style="display:inline; margin-left:25px">Nu modificați acest câmp. Valoare de referinta in contactul cu RomCard.</p>';

    $html .= '<br><label for="btpay_int_ref">IntRef:</label><br>';
    $html .= '<input type="text" size="20" id="btpay_int_ref" name="btpay_int_ref" value="' . $tr->int_ref . '" />';
    $html .= '<p style="display:inline; margin-left:25px">Nu modificați acest câmp. Valoare de referinta interna RomCard.</p>';

    $html .= '</div>';

    $html .= '<br><br>';
    $html .= '<a onclick="apicall()" class="button" href="#api" id="btpay_api_call">Trimite către BT Pay </a>';
    $html .= '<a onclick="showhide()" class="button" href="#api" id="btpay_more_info_click" style="margin-left: 10px;">informații suplimentare</a>';


    echo $html;
}

function wc_mindmagnet_btpay_standard_init() {

    if (!class_exists('WC_Payment_Gateway'))
        return;

    load_plugin_textdomain('wc_mindmagnet', false, dirname(plugin_basename(__FILE__)) . '/languages');

    include_once dirname(__FILE__).'/includes/class-wc-mindmagnet-btpay-standard.php';

    /**
     * Add the Gateway to WooCommerce
     */
    function add_mindmagnet_btpay_standard($methods) {
        $methods[] = 'WC_Gateway_MindMagnet_BTpay_Standard';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'add_mindmagnet_btpay_standard');
}

add_action('plugins_loaded', 'wc_mindmagnet_btpay_star_init', 0);

function wc_mindmagnet_btpay_star_init() {

    if (!class_exists('WC_Payment_Gateway'))
        return;

    load_plugin_textdomain('wc_mindmagnet', false, dirname(plugin_basename(__FILE__)) . '/languages');

    include_once dirname(__FILE__).'/includes/class-wc-mindmagnet-btpay-star.php';

    /**
     * Add the Gateway to WooCommerce
     */
    function add_mindmagnet_btpay_star($methods) {
        $methods[] = 'WC_Gateway_MindMagnet_BTpay_Star';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'add_mindmagnet_btpay_star');
}
