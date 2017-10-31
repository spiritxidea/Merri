<?php
/**
 * The template for displaying product archives, including the main shop page which is a post type archive
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// If "shop_load" is set, make sure request is via AJAX
if ( isset( $_REQUEST['shop_load'] ) && nm_is_ajax_request() ) {
	
	if ( 'products' !== $_REQUEST['shop_load'] ) {
		// AJAX filter or search
        wc_get_template_part( 'archive', 'product_nm_ajax_full' );
	} else {
		// AJAX page load
        wc_get_template_part( 'archive', 'product_nm_ajax_products' );
	}
	
} else {
	
    wc_get_template_part( 'archive', 'product_nm' );
	
}
