<?php

include_once ABSPATH . 'wp-content/plugins/woocommerce/includes/admin/post-types/class-wc-admin-meta-boxes.php';
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of metabox
 *
 * @author New User
 */
class Metabox extends WC_Admin_Meta_Boxes{
    //put your code here
    
    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 30 );
        
    }
    
    public function add_meta_boxes() {
        add_meta_box( 'woocommerce-order-notess', __( 'Order Notess', 'woocommerce' ), 'WC_Meta_Box_Order_Notes::output', 'shop_order', 'normal', 'default' );
    }
}
