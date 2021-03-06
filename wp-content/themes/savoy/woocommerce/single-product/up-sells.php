<?php
/**
 * Single Product Up-Sells
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product, $woocommerce_loop;

$upsells = $product->get_upsells();

if ( sizeof( $upsells ) == 0 ) { 
	return;
}

$meta_query = WC()->query->get_meta_query();

$args = array(
	'post_type'           => 'product',
	'ignore_sticky_posts' => 1,
	'no_found_rows'       => 1,
	'posts_per_page'      => $posts_per_page, // Note: This variable is filtered in "../savoy/includes/woocommerce/woocommerce-functions.php"
	//'posts_per_page'      => -1, // Use to show all products
	'orderby'             => $orderby,
	'post__in'            => $upsells,
	'post__not_in'        => array( $product->id ),
	'meta_query'          => $meta_query
);

$products = new WP_Query( $args );

//$woocommerce_loop['columns'] = ( $products->post_count > 4 ) ? '5' : '4';
$woocommerce_loop['columns'] = $columns; // Note: This variable is filtered in "../savoy/includes/woocommerce/woocommerce-functions.php"
$woocommerce_loop['columns_xsmall'] = '2';
$woocommerce_loop['columns_small'] = '2';
$woocommerce_loop['columns_medium'] = '4';

if ( $products->have_posts() ) : ?>

	<div id="nm-upsells" class="upsells products">
		
        <div class="nm-row">
        	<div class="col-xs-12">
            
                <h2><?php _e( 'You may also like&hellip;', 'woocommerce' ) ?></h2>
        
                <?php woocommerce_product_loop_start(); ?>
        
                    <?php while ( $products->have_posts() ) : $products->the_post(); ?>
        
                        <?php wc_get_template_part( 'content', 'product' ); ?>
        
                    <?php endwhile; // end of the loop. ?>
        
                <?php woocommerce_product_loop_end(); ?>
        
        	</div>
        </div>

	</div>

<?php endif;

wp_reset_postdata();
