<?php
/**
 * Front page Latest Products Section
 *
 * @package WordPress
 * @subpackage Shop Isle
 */
require_once('your-top-viewed-items.php');

$shop_isle_products_hide = get_theme_mod( 'shop_isle_products_hide', false );
$section_style           = '';
if ( ! empty( $shop_isle_products_hide ) && (bool) $shop_isle_products_hide === true ) {
	if ( is_customize_preview() ) {
		$section_style = 'style="display: none"';
	} else {
		return;
	}
}

echo '<section id="latest" class="module-small" ' . $section_style . '>';
shop_isle_display_customizer_shortcut( 'shop_isle_products_section' );
echo '<div class="container">';


/**
 * Display section titke
 */
echo '<div class="row">';
	echo '<div class="col-sm-6 col-sm-offset-3">';
    echo '<h2 class="module-title font-alt product-banners-title">  YOUR TOP VIEWED PRODUCTS  </h2>';
	echo '</div>';
    echo '</div>';
/**
 *  WooCommerce shortcode.
 */
echo '<div class="products_shortcode">';
echo do_shortcode('[your-top-viewed-items]');
echo '</div>';

echo '</div><!-- .container -->';

echo '</section>';