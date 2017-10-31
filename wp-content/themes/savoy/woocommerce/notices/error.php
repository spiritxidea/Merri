<?php
/**
 * Show error messages
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! $messages ) {
	return;
}

$nm_shop_notice_single_class = ( count( $messages ) > 1 ) ? ' nm-shop-notice-multiple' : '';

?>

<ul class="nm-shop-notice woocommerce-error<?php echo $nm_shop_notice_single_class; ?>">
    <?php foreach ( $messages as $message ) : ?>
    <li><span><i class="nm-font nm-font-close"></i><?php echo wp_kses_post( $message ); ?></span></li>
    <?php endforeach; ?>
</ul>
