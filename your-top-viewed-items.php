<?php
/**
 * @package YourTopViewedItems
 */

 /*
 Plugin Name: Your Top Viewed Items
 Plugin URI: http://bletsasalexandros.gr/snowboard
 Description: This is a plugin for viewing the prducts that the user has most viewed
 Version: 1.0.0
 Author: Alexandros Christos Bletsas
 Author URI: http://bletsasalexandros.gr
 License: GPLv2 or later
 Text Domain: your-most-viewed-items
  */

  if ( ! defined( 'ABSPATH' ) ) {
      die;
  }
  class YourTopViewedItems
  {
      function __construct(){
          add_action('init', array($this, "you_login"));
          add_action( 'woocommerce_before_single_product', array($this, 'findUser'), 10 );  
    }
      function the_product_id() {
        echo "I am in ";
        global $product;
        return $product->get_id();
        }
     
      function activate() {
          //generate a CPT
          $this->custom_post_type();
          //flush reqrite rules
          flush_rewrite_rules();
      }

      function deactivate() {
          //flush rewrite rules
          flush_rewrite_rules();
      }

      function custom_post_type(){
          register_post_type( 'book', ['public' => true, 'label' => 'Books'] );
      }
      function you_login(){
        if ( is_user_logged_in() ):
            echo "Welcome user! : ";
        else:
            echo 'Welcome, visitor!';
        endif;
        }
        function findUser(){
            //edw tha valw boolean an exei agorastei to proinon
            $my_product_id = $this->the_product_id();
            $current_user = wp_get_current_user();
            if ( $current_user ):
                echo $current_user->first_name;
                $user_id = $current_user->ID;
                // GET USER ORDERS (COMPLETED + PROCESSING)
                $customer_orders = get_posts( array(
                    'numberposts' => -1,
                    'meta_key'    => '_customer_user',
                    'meta_value'  => $current_user->ID,
                    'post_type'   => wc_get_order_types(),
                    'post_status' => array_keys( wc_get_is_paid_statuses() ),
                ) );

                 // LOOP THROUGH ORDERS AND GET PRODUCT IDS
                if ( ! $customer_orders ) return;
                $product_ids = array();
                foreach ( $customer_orders as $customer_order ) {
                    $order = wc_get_order( $customer_order->ID );
                    $items = $order->get_items();
                    foreach ( $items as $item ) {
                        $product_id = $item->get_product_id();
                        echo "+ $product_id +";
                        $product_ids[] = $product_id;
                        //sygkrinw an to exei xanagorasei kai an nai tote h boolean ginetai true
                    }
                }
            endif;
            //an boolean true tote den kanw kati alliw paw kai prostheto stn db mou
            
        }

  }
if ( class_exists( 'YourTopViewedItems' ) ){
  $yourTopViewedItems = new YourTopViewedItems();
}

//activation
register_activation_hook(__FILE__, array($yourTopViewedItems, 'activate'));

//deactivation
register_deactivation_hook(__FILE__, array($yourTopViewedItems, 'deactivate'));

