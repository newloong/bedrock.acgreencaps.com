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

// // Register Modified Date Column for both posts & pages
// function modified_column_register( $columns ) {
// 	$columns['Modified'] = __( 'Modified Date', 'show-modified-date-in-admin-lists' );
// 	return $columns;
// }
// add_filter( 'manage_posts_columns', 'modified_column_register' );
// add_filter( 'manage_pages_columns', 'modified_column_register' );
// add_filter( 'manage_media_columns', 'modified_column_register' );

// function modified_column_display( $column_name, $post_id ) {
// 	switch ( $column_name ) {
// 	case 'Modified':
// 		global $post; 
// 	       	echo '<p class="mod-date">';
// 	       	echo '<em>'.get_the_modified_date().' '.get_the_modified_time().'</em><br />';
// 			if ( !empty( get_the_modified_author() ) ) {
// 				echo '<small>' . esc_html__( 'by', 'show-modified-date-in-admin-lists' ) . ' <strong>'.get_the_modified_author().'<strong></small>';
// 			} else {
// 				echo '<small>' . esc_html__( 'by', 'show-modified-date-in-admin-lists' ) . ' <strong>' . esc_html__( 'UNKNOWN', 'show-modified-date-in-admin-lists' ) . '<strong></small>';
// 			}
// 			echo '</p>';
// 		break; // end all case breaks
// 	}
// }
// add_action( 'manage_posts_custom_column', 'modified_column_display', 10, 2 );
// add_action( 'manage_pages_custom_column', 'modified_column_display', 10, 2 );
// add_action( 'manage_media_custom_column', 'modified_column_display', 10, 2 );

// function modified_column_register_sortable( $columns ) {
// 	$columns['Modified'] = 'modified';
// 	return $columns;
// }
// add_filter( 'manage_edit-post_sortable_columns', 'modified_column_register_sortable' );
// add_filter( 'manage_edit-page_sortable_columns', 'modified_column_register_sortable' );
// add_filter( 'manage_upload_sortable_columns', 'modified_column_register_sortable' );
