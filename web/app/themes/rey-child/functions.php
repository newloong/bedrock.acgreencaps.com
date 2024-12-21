<?php
if ( ! defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Theme child functions and definitions.
 *
 * A child theme allows you to change small aspects of your site’s appearance
 * yet still preserve your theme’s look and functionality. To understand how child themes work it is first important
 * to understand the relationship between parent and child themes.
 *
 * In case the child theme's stylesheet is not showing the changes in the frontend, you can increase the
 * version in style.css, eg: 1.0.1 .
 *
 * @link https://developer.wordpress.org/themes/advanced-topics/child-themes/
 *
*/

// Hook into Scripts to append custom stylesheet
add_action( 'wp_enqueue_scripts', function () {

	// Load custom stylesheet
	wp_enqueue_style( 'rey-wp-style-child', get_stylesheet_uri(), [], wp_get_theme()->get('Version') );

}, PHP_INT_MAX /* load late */ );

add_action( "rey/before_footer", function(){
    if( class_exists('WooCommerce') && is_product() ){
        echo do_shortcode('[rey_global_section id="1374"]');
    }
});

// Adds the currency switcher in the header
// add_action('woocommerce_after_add_to_cart_button', function(){
//     if( class_exists('WooCommerce') && is_product() ){
//         if( ! ($product = wc_get_product()) ){
//             return;
//         }
//         echo do_shortcode('[tiered-pricing-table]');
//     }
// }, 0);
