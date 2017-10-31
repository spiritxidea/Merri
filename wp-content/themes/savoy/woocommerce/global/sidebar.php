<?php
/**
 * Sidebar
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<div class="nm-shop-sidebar-col col-md-3 col-sm-12">
    <div id="nm-shop-sidebar" class="nm-shop-sidebar" data-sidebar-layout="default">
        <ul id="nm-shop-widgets-ul">
            <?php
                if ( is_active_sidebar( 'widgets-shop' ) ) {
                    dynamic_sidebar( 'widgets-shop' );
                }
            ?>
        </ul>
    </div>
</div>
