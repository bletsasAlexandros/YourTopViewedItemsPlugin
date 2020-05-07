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
          add_action( 'woocommerce_before_single_product', array($this, 'findUser'), 10 );
          
    }
    
      function the_product_id() {
        if ( is_product()):
            global $product;
            return $product->get_id();
        endif;
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
        
        function findUser(){
            $have_been_bought = false;
            $my_product_id = $this->the_product_id();
            $current_user = wp_get_current_user();
            if ( $current_user ):
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

function display_item(){
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        global $wpdb;
        $table = $wpdb->prefix.'your_top_viewed_items';
        $products = $wpdb->get_results(
        "
        SELECT productid FROM wp_your_top_viewed_items WHERE userid=$user_id ORDER BY `wp_your_top_viewed_items`.`views` DESC LIMIT 4;
        "
        );
        $Content = "<section id=\"latest\" class=\"module-small\" style=\"overflow: scroll; \"><div class=\"container\">";
        $Content .= '<div class="row">';
         $Content .= '<div class="col-sm-6 col-sm-offset-3">';
         $Content .= '<h2 class="module-title font-alt product-banners-title">  YOUR TOP VIEWED PRODUCTS  </h2>';
         $Content .= "<ul style=\"display:inline;\" >";
        $Content = "<div class=\"woocommerce columns-4 \"><ul class=\"products columns-4\">";
        foreach ($products as $product){
            $id = $product->productid;
            $_product = wc_get_product( $id );
            $name = $_product->get_name();
            $link = get_permalink($id);
            $image = $_product->get_image();
            $rating = $_product->get_average_rating();
            $Content .= "<li style=\"display: inline; clear:none;\" class=\"product type-product post-101 status-publish first instock has-post-thumbnail sale taxable shipping-taxable purchasable product-type-variable\">
                <a href=\"$link\" class=\"woocommerce-LoopProduct-link woocommerce-loop-product__link\"><div class=\"prod-img-wrap\"><img width=\"262\" height=\"262\"  class=\"attachment-shop_catalog size-shop_catalog wp-post-image\" alt=\"\" title=\"$name\" sizes=\"(max-width: 262px) 100vw, 262px\" src=$image <div class=\"product-button-wrap\"></div></div><h2 class=\"woocommerce-loop-product__title\">$name</h2>
                </a></li>";
        }
         $Content .= "</ul>";
         $Content .= '</div>';
         $Content .= '</div>';
         $Content .= '</div>';
         $Content .= '</section/>';
    $Content .= "</ul></div>";
    return $Content;  

}

function most_viewed(){
    return "Hello gamww";
}

add_shortcode('your-top-viewed-items','display_item');
add_shortcode('heyy','most_viewed');


