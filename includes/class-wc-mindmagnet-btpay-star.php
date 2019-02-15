<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

include_once dirname(__FILE__).'/lib/btpaygate/btpaygate.php';
include_once dirname(__FILE__).'/TransactionDB.php';

/**
 * WC_MindMagnet_eBTpay class.
 *
 * @extends WC_Payment_Gateway
 */
class WC_Gateway_MindMagnet_BTpay_Star extends WC_Payment_Gateway {

    /**
     * Constructor
     */
    public function __construct() {

        global $woocommerce;
        global $bt_paygate;
        global $bt_payment;
        global $bt_gatewayresponse;

        $wc_log = new WC_Logger();

        $bt_paygate = new BTPayGate();
        $bt_payment = new BTPayment();

        $this->id = strtolower('MindMagnet_BTPay_Star');
        $this->method_title = __('BTPay Star', 'wc_mindmagnet');
        $this->method_description = __('<p>See more information on <a href="http://www.mmecommerce.com">Mind Magnet - Full eCommerce Solutions</a></p>
                        <p>For any questions related BT Pay, please visit <a href="http://support.mmecommerce.com">Mind Magnet - Support</a></p>
                        <p>RomCard - Sistem de testare comercianti 3DSecure: <a href="https://www.activare3dsecure.ro/teste3d/login.php">Login</a></p>', 'wc_mindmagnet');
        $this->has_fields = true;

        // Load the form fields
        $this->init_form_fields();

        // Load the settings.
        $this->init_settings();

        if (!empty($_POST) && strstr($_GET['section'], 'star')) {
            $options = get_option('woocommerce_MindMagnet_BTPay_Standard_settings', array());
            $this->settings['payment_btpay_api_test'] = $options['payment_btpay_api_test'] = $_POST['woocommerce_mindmagnet_btpay_star_payment_btpay_api_test'];
            $this->settings['payment_btpay_api_debug'] = $options['payment_btpay_api_debug'] = $_POST['woocommerce_mindmagnet_btpay_star_payment_btpay_api_debug'];
            $this->settings['payment_btpay_api_merchant_name'] = $options['payment_btpay_api_merchant_name'] = $_POST['woocommerce_mindmagnet_btpay_star_payment_btpay_api_merchant_name'];
            $this->settings['payment_btpay_api_merchant_url'] = $options['payment_btpay_api_merchant_url'] = $_POST['woocommerce_mindmagnet_btpay_star_payment_btpay_api_merchant_url'];
            $this->settings['payment_btpay_api_merchant_email'] = $options['payment_btpay_api_merchant_email'] = $_POST['woocommerce_mindmagnet_btpay_star_payment_btpay_api_merchant_email'];
            $this->settings['payment_btpay_api_terminal'] = $options['payment_btpay_api_terminal'] = $_POST['woocommerce_mindmagnet_btpay_star_payment_btpay_api_terminal'];
            $this->settings['payment_btpay_api_encryption_key'] = $options['payment_btpay_api_encryption_key'] = $_POST['woocommerce_mindmagnet_btpay_star_payment_btpay_api_encryption_key'];

            update_option('woocommerce_mindmagnet_btpay_star_settings', $this->settings);
            update_option('woocommerce_mindmagnet_btpay_standard_settings', $options);
        }

        $this->title = $this->settings['title'];
        $this->description = $this->settings['description'];
        $this->enabled = $this->settings['enabled'];

        $star = true;
//        if ($this->settings['payment_btpay_star_min_order_total'] != '') {
//            if (WC()->cart->total < $this->settings['payment_btpay_star_min_order_total'])
//                $star = false;
//        }
//
//        if ($this->settings['payment_btpay_star_max_order_total'] != '') {
//            if (WC()->cart->total > $this->settings['payment_btpay_star_max_order_total'])
//                $star = false;
//        }

        if ($this->enabled == 'yes' && $star == true)
            $this->enabled = 'yes';
        else
            $this->enabled = 'no';

        global $wpdb;
        $db = new TransactionDB($wpdb);
        $db->create();

        if (isset($_GET) && $_GET != null) {
            $p = array();
            $log = '';
            foreach ($_GET as $key => $value) {
                if ($key == strtoupper($key)) {
                    $log .=$key . ' = ' . $value . PHP_EOL;
                    $p[$key] = $value;
                }
            }

            $bt_gatewayresponse = new BTGatewayResponse($p);

            if (isset($_GET['post'])) {
                $order = new WC_Order(intval($_GET['post']));
                $order->get_data($_GET['post']);
                $order_id = $order->get_id();
            } else {
                $order = new WC_Order(intval($p['DESC']));
                $order->get_data(intval($p['DESC']));
                $order_id = $order->get_id();
            }

            $meta = get_post_meta($order_id);

            if ($this->settings['payment_btpay_api_debug'] == '1' && $p != null && $_SERVER['HTTP_REFERER'] != 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] && $meta['_payment_method']['0'] == 'MindMagnet_BTPay_Star') {
                $settings = PHP_EOL . '  Gateway Response' . PHP_EOL . '';
                foreach ($p as $key => $value) {
                    $settings .= '      ' . $key . ' = ' . $value . PHP_EOL;
                }
                $wc_log->add('btpay-star-log', 'Gateway response received.' . $settings);
            }

            $link = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            if ($_SERVER['HTTP_REFERER'] != $link && $_SERVER['HTTP_REFERER'] != $link . '#api') {

                $date = date_create();
                date_timestamp_set($date, $p['TIMESTAMP']);
                switch ($p['TRTYPE']) {
                    case '0':
                        $trtype = 'PREAUTH';
                        break;
                    case '21':
                        $trtype = 'CAPTURE';
                        break;
                    case '24':
                        $trtype = 'VOID';
                        break;
                    case '25':
                        $trtype = 'PARTIAL_REFUND';
                        break;
                    case '26':
                        $trtype = 'VOID_FRAUD';
                        break;

                    default:
                        break;
                }

                if ($p['MESSAGE'] == 'Approved')
                    $status = 1;
                else
                    $status = 0;

                $transaction = array(
                    'order_id' => $order->get_id(),
                    'transaction_type' => $trtype,
                    'transaction_status' => $status,
                    'amount_processed' => floatval($p['AMOUNT']),
                    'currency_code' => $p['CURRENCY'],
                    'order_name' => $p['ORDER'],
                    'rrn' => $p['RRN'],
                    'int_ref' => $p['INT_REF'],
                    'response_message' => $p['MESSAGE'],
                    'payment_method' => $order->get_payment_method_title(),
                    'extra_info' => $log,
                    'created_at' => date_format($date, 'Y-m-d H:i:s')
                );

                $transaction_string = null;
                foreach ($transaction as $key => $value) {
                    $transaction_string .="'" . $value . "', ";
                }

                if ($log != null) {
                    $duplicate = $db->duplicate($log);
                    if (empty($duplicate))
                        $db->insert(substr($transaction_string, 0, -2), $transaction);
                    if ($this->settings['payment_btpay_api_debug'] == '1' && $meta['_payment_method']['0'] == 'MindMagnet_BTPay_Star') {
                        $wc_log->add('btpay-star-log', 'Transaction saved to DB.');
                    }
                }

                if ($bt_gatewayresponse->isValid($this->settings['payment_btpay_api_encryption_key'])) {

                    if ($p['ACTION'] == '0') {
                        if (array_key_exists('APPROVAL', $p)) {
                            if ($bt_gatewayresponse->isAuthorized()) {
                                $order->update_status($this->settings['payment_btpay_star_order_status'], $log . PHP_EOL . PHP_EOL);
                                if ($this->settings['payment_btpay_api_debug'] == '1' && $meta['_payment_method']['0'] == 'MindMagnet_BTPay_Star') {
                                    $wc_log->add('btpay-star-log', 'Response is valid. Order status updated - ' . $this->settings['payment_btpay_star_order_status']);
                                }
                            }
                        } elseif ($p['TRTYPE'] == '21') {
                            if ($bt_gatewayresponse->isCaptured()) {
                                $order->update_status('completed', $log . PHP_EOL . PHP_EOL);
                                if ($this->settings['payment_btpay_api_debug'] == '1' && $meta['_payment_method']['0'] == 'MindMagnet_BTPay_Star') {
                                    $wc_log->add('btpay-star-log', 'Response is valid. Order status updated - ' . 'completed');
                                }
                            }
                        } elseif ($p['TRTYPE'] == '24') {
                            if ($bt_gatewayresponse->isVoided()) {
                                if ($order->status == 'completed') {
                                    $order->update_status('refunded', $log . PHP_EOL . PHP_EOL);
                                    if ($this->settings['payment_btpay_api_debug'] == '1' && $meta['_payment_method']['0'] == 'MindMagnet_BTPay_Star') {
                                        $wc_log->add('btpay-star-log', 'Response is valid. Order status updated - ' . 'refunded');
                                    }
                                } else {
                                    $order->cancel_order($log . PHP_EOL . PHP_EOL);
                                    if ($this->settings['payment_btpay_api_debug'] == '1' && $meta['_payment_method']['0'] == 'MindMagnet_BTPay_Star') {
                                        $wc_log->add('btpay-star-log', 'Response is valid. Order status updated - ' . 'cancelled');
                                    }
                                }
                            }
                        } elseif ($p['TRTYPE'] == '25') {
                            if ($bt_gatewayresponse->isVoided()) {
                                $order->update_status('processing', 'Partial Refund ' . $log . PHP_EOL . PHP_EOL);
                                if ($this->settings['payment_btpay_api_debug'] == '1' && $meta['_payment_method']['0'] == 'MindMagnet_BTPay_Star') {
                                    $wc_log->add('btpay-star-log', 'Response is valid. Order status updated - ' . 'processing');
                                }
                            }
                        }
                    } else {
                        $order->update_status('failed', $log . PHP_EOL . PHP_EOL);
                        if ($this->settings['payment_btpay_api_debug'] == '1' && $meta['_payment_method']['0'] == 'MindMagnet_BTPay_Star') {
                            $wc_log->add('btpay-star-log', 'Response is valid. Order status updated - ' . 'failed');
                        }
                    }


                    echo '<script>window.location = "' . $link . '";</script>';
                } else {
                    if ($this->settings['payment_btpay_api_debug'] == '1' && $meta['_payment_method']['0'] == 'MindMagnet_BTPay_Star') {
                        $wc_log->add('btpay-star-log', 'Response is not valid.');
                    }
                }
            }
        }

        add_action('admin_notices', array($this, 'checks'));
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }

    /**
     * Check if SSL is enabled and notify the user
     */
    public function checks() {
        if ($this->enabled == 'no')
            return;
    }

    /**
     * Check if this gateway is enabled
     */
    public function is_available() {
        if ($this->enabled != "yes")
            return false;

        return true;
    }

    /**
     * Initialise Gateway Settings Form Fields
     */
    public function init_form_fields() {

        include_once dirname(__FILE__).'/countries.php';

        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', 'wc_mindmagnet'),
                'label' => __('Enable BTpay Star', 'wc_mindmagnet'),
                'type' => 'checkbox',
                'description' => '',
                'default' => 'no'
            ),
            'title' => array(
                'title' => __('Title', 'wc_mindmagnet'),
                'type' => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'wc_mindmagnet'),
                'default' => __('Rate prin StarBT', 'wc_mindmagnet'),
                'desc_tip' => true
            ),
            'payment_btpay_star_instructions' => array(
                'title' => __('Extra Note', 'wc_mindmagnet'),
                'label' => __('Extra Note', 'wc_mindmagnet'),
                'type' => 'textarea',
                'css' => 'width: 350px; height: 100px;',
                'description' => '',
                'default' => 'Prin efectuarea platii acceptati numarul de rate fara dobanda specificat mai sus conform Regulamentului Bancii Transilvania "Rate fara dobanda".',
            ),
            'payment_btpay_star_rambursare' => array(
                'title' => __('Allowed Installments (comma separated values)', 'wc_mindmagnet'),
                'label' => __('Allowed Installments (comma separated values)', 'wc_mindmagnet'),
                'type' => 'text',
                'description' => '',
                'default' => '2,3,4,5',
            ),
            'payment_btpay_star_order_status' => array(
                'title' => __('New Order Status', 'wc_mindmagnet'),
                'label' => __('New Order Status', 'wc_mindmagnet'),
                'type' => 'select',
                'description' => '',
                'default' => 'processing',
                'options' => array(
                    'processing' => __('Processing', 'woocommerce'),
                    'pending' => __('Pending', 'woocommerce'),
                    'failed' => __('Failed', 'woocommerce'),
                    'on-hold' => __('On-Hold', 'woocommerce'),
                    'completed' => __('Completed', 'woocommerce'),
                    'refunded' => __('Refunded', 'woocommerce'),
                    'cancelled' => __('Cancelled', 'woocommerce')
                )
            ),
            'payment_btpay_star_min_order_total' => array(
                'title' => __('Minimum Order Total', 'wc_mindmagnet'),
                'label' => __('Minimum Order Total', 'wc_mindmagnet'),
                'type' => 'text',
                'description' => '',
                'default' => '',
            ),
            'payment_btpay_star_max_order_total' => array(
                'title' => __('Maximum Order Total', 'wc_mindmagnet'),
                'label' => __('Maximum Order Total', 'wc_mindmagnet'),
                'type' => 'text',
                'description' => '',
                'default' => '',
            ),
            array('title' => __('API Settings', 'woocommerce'), 'type' => 'title', 'description' => __('General for both modules. Any change will affect the other module too.', 'wc_mindmagnet'), 'desc_tip' => true),
            'payment_btpay_api_test' => array(
                'title' => __('Test Mode', 'wc_mindmagnet'),
                'label' => __('Test Mode', 'wc_mindmagnet'),
                'type' => 'select',
                'description' => '',
                'default' => '0',
                'options' => array(
                    '0' => __('No', 'woocommerce'),
                    '1' => __('Yes', 'woocommerce')
                )
            ),
            'payment_btpay_api_debug' => array(
                'title' => __('Debug Mode', 'wc_mindmagnet'),
                'label' => __('Debug Mode', 'wc_mindmagnet'),
                'type' => 'select',
                'description' => '',
                'default' => '0',
                'options' => array(
                    '0' => __('No', 'woocommerce'),
                    '1' => __('Yes', 'woocommerce')
                )
            ),
            'payment_btpay_api_merchant_name' => array(
                'title' => __('API Merchant\'s Name', 'wc_mindmagnet'),
                'label' => __('API Merchant\'s Name', 'wc_mindmagnet'),
                'type' => 'text',
                'description' => __('Numele comerciantului (numele firmei care apare pe site si este declarat la banca).', 'wc_mindmagnet'),
                'default' => '',
                'desc_tip' => true
            ),
            'payment_btpay_api_merchant_url' => array(
                'title' => __('API Merchant\'s URL', 'wc_mindmagnet'),
                'label' => __('API Merchant\'s URL', 'wc_mindmagnet'),
                'type' => 'text',
                'description' => __('Adresa site-ului.', 'wc_mindmagnet'),
                'default' => '',
                'desc_tip' => true
            ),
            'payment_btpay_api_merchant_email' => array(
                'title' => __('API Merchant\'s Email', 'wc_mindmagnet'),
                'label' => __('API Merchant\'s Email', 'wc_mindmagnet'),
                'type' => 'text',
                'description' => __('Email de contact comerciant.', 'wc_mindmagnet'),
                'default' => '',
                'desc_tip' => true
            ),
            'payment_btpay_api_terminal' => array(
                'title' => __('API Terminal', 'wc_mindmagnet'),
                'label' => __('API Terminal', 'wc_mindmagnet'),
                'type' => 'text',
                'description' => __('Valoare asignata de catre banca. Se gaseste in sectiunea "Configurare cont".', 'wc_mindmagnet'),
                'default' => '',
                'desc_tip' => true
            ),
            'payment_btpay_api_encryption_key' => array(
                'title' => __('API Encryption Key', 'wc_mindmagnet'),
                'label' => __('API Encryption Key', 'wc_mindmagnet'),
                'type' => 'text',
                'description' => __('Valoare asignata de catre banca.', 'wc_mindmagnet'),
                'default' => '',
                'desc_tip' => true
            ),
        );
    }

    /**
     * Payment form on checkout page
     */
    public function payment_fields() {

        global $woocommerce;

        $installments = explode(',', $this->settings['payment_btpay_star_rambursare']);
        //var_dump($installments);
        $rate = array();
        $c = 0;
        foreach ($installments as $i) {
            if ($i != '0' && $i != '1') {
                $rate[$c] = $i;
                $c++;
            }
        }
        ?>

        <ul class="form-list" id="payment_form_btpay_star" style="list-style-type: none">
            <?php if (!empty($rate)) { ?>
                <li>
                    <label for="btpay_star_rambursare">Installments:</label>
                    <div class="input-box">
                        <select autocomplete="off" id="btpay_star_rambursare" name="payment[rambursare]" class="required-entry">
                            <option value="0" selected></option>
                            <?php foreach ($rate as $i) { ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?> rate fără dobândă</option>  
                            <?php }
                            ?>
                        </select>
                    </div>
                </li>
            <?php } ?>
            <li>
                <div class="btpay_star-instructions-content" style="background-color: white;  border-color: #E4E4E4; padding: 5px; border: 1px solid #BBB6A5; overflow: auto;">
                    <?php echo $this->settings['payment_btpay_star_instructions']; ?>        
                </div>
            </li>
        </ul>

        <?php
    }

    /**
     * payment_scripts function.
     *
     * Outputs scripts used for payment
     */
    public function payment_scripts() {
        if (!is_checkout())
            return;

        wp_enqueue_script('jquery-payment', plugins_url('assets/js/jquery.payment.js', dirname(__FILE__)), array('jquery'), '1.0', true);
    }

    /**
     * Process the payment
     */
    public function process_payment($order_id) {

        global $woocommerce;
        global $order;
        global $bt_paygate;
        global $bt_payment;

        $wc_log = new WC_Logger();

        $meta = get_post_meta($order_id);
        $order = new WC_Order($order_id);
        $bt_payment = new BTPayment();
        $bt_payment->setAmount((float) $order->get_total());
        $bt_payment->setCurrency($order->get_currency());
        if ($_POST['payment']['rambursare'] == '0')
            throw new Exception('Optiune Rate Star BT Invalida!', 1);
        else
            $bt_payment->setRambursare($_POST['payment']['rambursare']);

        $bt_payment->setOrder(intval('1' . str_pad($order_id, 6, "0", STR_PAD_LEFT)));

        if ($_POST['order_comments'] !== '')
            $bt_payment->setDesc($_POST['order_comments']);
        else
            $bt_payment->setDesc(strval($order_id));
        if (isset($_GET['order-pay']))
            $bt_payment->setDesc(strval($_GET['order-pay']));

        if ($bt_payment->isValid()) {
            $config_data = array(
                'test_mode' => $this->settings['payment_btpay_api_test'],
                'encryption_key' => $this->settings['payment_btpay_api_encryption_key'],
                'merchant_name' => $this->settings['payment_btpay_api_merchant_name'],
                'merchant_url' => $this->settings['payment_btpay_api_merchant_url'],
                'terminal' => $this->settings['payment_btpay_api_terminal'],
                'merchant_email' => $this->settings['payment_btpay_api_merchant_email']
            );

            $bt_paygate = new BTPayGate($config_data);

            if ($bt_paygate->check()) {
                session_start();
                $_SESSION['backref'] = $this->get_return_url($order);
                $_SESSION['method'] = $_POST['payment']['method'];

                $link = strstr($this->get_return_url($order), '?', true) . '/wp-content/plugins/woocommerce-mindmagnet-ebtpay/includes/lib/btpaygate/redirect.php';

                $params = $bt_paygate->getActionParams($bt_payment, 'preauthorize', $link, null);
                $settings = '';
                if ($this->settings['payment_btpay_api_debug'] == '1' && $meta['_payment_method']['0'] == 'MindMagnet_BTPay_Star') {
                    foreach ($params as $key => $value) {
                        $settings .= '      ' . $key . ' = ' . $value . PHP_EOL;
                    }
                    $wc_log->add('btpay-star-log', 'Checkout finished and data sent to BT.' . PHP_EOL . $settings);
                }
                $woocommerce->cart->empty_cart();
                die($bt_paygate->renderForm($params, false, true));
            } else
                throw new Exception('Invalid/incomplete API configuration', 1);
        }
    }

}
