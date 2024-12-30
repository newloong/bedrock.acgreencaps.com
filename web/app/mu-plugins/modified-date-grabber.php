<?php
/*
  Plugin Name: Modified Date Grabber
  Description: Add Last Modified Date to Posts and Pages List
  Version: 1.0
  Author: Newloong LLC
  Author URI: https://newloong.com
*/

use function Env\env;

// Add modified date column to posts and pages lists
function custom_modified_date_column($defaults) {
    $defaults['modified_date'] = 'Modified Date';
    return $defaults;
}
function custom_modified_date_column_content($column_name, $post_ID) {
    if ($column_name == 'modified_date') {
        $modified_date = get_post_field('post_modified', $post_ID);
        $formatted_date = date_i18n('Y/m/d \a\t g:i a', strtotime($modified_date));
        echo 'Modified ' . '<br>' . esc_html($formatted_date);
    }
}
function custom_modified_date_column_sortable($columns) {
    $columns['modified_date'] = 'modified_date';
    return $columns;
}
function custom_sortable_columns_orderby($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }
    $orderby = $query->get('orderby');
    if ($orderby == 'modified_date') {
        $query->set('orderby', 'modified');
        $query->set('order', 'desc'); // Set the order to descending
    }
}

if ('development' === env('WP_ENV')) {
    add_filter('manage_posts_columns', 'custom_modified_date_column');
    add_action('manage_posts_custom_column', 'custom_modified_date_column_content', 10, 2);
    add_filter('manage_pages_columns', 'custom_modified_date_column'); // Add this line to include the column in pages list
    add_action('manage_pages_custom_column', 'custom_modified_date_column_content', 10, 2);
    add_filter('manage_edit-post_sortable_columns', 'custom_modified_date_column_sortable'); // Add this line to make the column sortable in posts list
    add_filter('manage_edit-page_sortable_columns', 'custom_modified_date_column_sortable'); // Add this line to make the column sortable in pages list
    add_action('pre_get_posts', 'custom_sortable_columns_orderby');
}