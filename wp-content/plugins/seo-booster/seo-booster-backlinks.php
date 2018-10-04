<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if (!current_user_can('update_plugins')) {
	wp_die(__('You are not allowed to update plugins on this blog.', 'seo-booster'));
}

global $seobooster2, $wpdb;

$bltable = $wpdb->prefix . "sb2_bl";



global $wpdb, $seobooster2;


if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
			// PHP VERSION CHECK / WARNING
			include_once 'inc/phpcheck.php';
require dirname( __FILE__ ) . '/inc/class-backlink-pages-list-table.php';


$backlink_pages_list_table = new SB_Backlinks_List_Table();

$backlink_pages_list_table->prepare_items();

include dirname( __FILE__ ) . '/views/backlink-pages.php';


?>