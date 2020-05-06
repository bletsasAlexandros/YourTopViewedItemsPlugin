<?php

/**
 * Trigger this file on plugin unistall
 * @package YourTopViewedItems
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}


// Clear DB stored data
$books = get_posts( array('post_type' => 'book', 'numberposts' => -1) );

foreach( $books as $book) {
    wp_delete_post($book->ID, false)
}