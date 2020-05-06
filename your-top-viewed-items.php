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
        global $wpdb;
        $table_name = $wpdb->prefix. "your_top_viewed_items";
        global $charset_collate;
        $charset_collate = $wpdb->get_charset_collate();
        global $db_version;
           $create_sql = "CREATE TABLE " . $table_name . " (
            id INT(11) NOT NULL auto_increment,
            views INT(11) NOT NULL ,
            productid INT(11) NOT NULL,
            userid int(10) NOT NULL,
            PRIMARY KEY (id))$charset_collate;";
        
        require_once(ABSPATH . "wp-admin/includes/upgrade.php");
        dbDelta( $create_sql );



        //register the new table with the wpdb object
        if (!isset($wpdb->ratings_fansub))
        {
            $wpdb->ratings_fansub = $table_name;
            //add the shortcut so you can use $wpdb->stats
            $wpdb->tables[] = str_replace($wpdb->prefix, '', $table_name);
        }
      }

      function you_login(){
        if ( is_user_logged_in() ):
        endif;
        }
        function findUser(){
            $have_been_bought = false;
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
                        if ( $product_id == $my_product_id):
                            $have_been_bought = true;
                        endif;
                    }
                }
                 //an boolean true tote den kanw kati alliw paw kai prostheto stn db mou
                if ( ! $have_been_bought):
                    $this->add_views($user_id, $my_product_id);
                endif;
            endif;
           
            
        }

        function add_views($user_id, $product_id){
            global $wpdb;
            $table = $wpdb->prefix.'your_top_viewed_items';
            $views = $wpdb->get_results(
            "
            SELECT views FROM wp_your_top_viewed_items WHERE productid=$product_id AND userid=$user_id;
            "
            );
            if ($views):
                $all_views =  $views[0]->views + 1;
                $data = array('views'=>$all_views);
                $where = array('productid'=>$product_id, 'userid'=>$user_id);
                $updated = $wpdb->update( $table, $data, $where );
            else:
                $data = array('productid' => $product_id, 'userid' => $user_id, 'views'=>1);
                $format = array('%s','%d');
                $wpdb->insert($table,$data,$format);
            endif;
        }

  }
if ( class_exists( 'YourTopViewedItems' ) ){
  $yourTopViewedItems = new YourTopViewedItems();
}

//activation
register_activation_hook(__FILE__, array($yourTopViewedItems, 'activate'));

//deactivation
register_deactivation_hook(__FILE__, array($yourTopViewedItems, 'deactivate'));

