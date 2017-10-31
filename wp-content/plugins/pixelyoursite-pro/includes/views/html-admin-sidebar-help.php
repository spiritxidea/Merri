<?php

/**
 * PixelYourSite PRO help sidebar widget.
 */

namespace PixelYourSite\FacebookPixelPro;

use PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<div class="card-box widget-help">
	<h4 class="text-dark"><i class="fa fa-question-circle" aria-hidden="true"></i> Help</h4>
	
	<p class="help-link help-link-primary">
		<a href="http://www.pixelyoursite.com/facebook-pixel-plugin-help" target="_blank">How to install the Facebook Pixel</a>
	</p>

	<?php if ( isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] == 'events' ) : ?>
		<p class="help-link help-link-secondary">
			<a href="http://www.pixelyoursite.com/how-to-add-facebook-pixel-events" target="_blank">Events Help</a>
		</p>
		<p class="help-link help-link-secondary">
			<a href="http://www.pixelyoursite.com/facebook-pixel-dynamic-events" target="_blank">Dynamic Events Help</a>
		</p>
	<?php endif; ?>

	<?php if ( isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] == 'woo' ) : ?>
		<p class="help-link help-link-secondary">
			<a href="http://www.pixelyoursite.com/facebook-pixel-plugin-woocommerce" target="_blank">WooCommerce Pixel Help</a>
		</p>
	<?php endif; ?>

	<?php if ( isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] == 'edd' ) : ?>
		<p class="help-link-secondary">
			<a href="http://www.pixelyoursite.com/easy-digital-download-facebook-pixel-help" target="_blank">EDD Pixel Help</a>
		</p>
	<?php endif; ?>

</div>

<?php if ( PixelYourSite\is_woocommerce_active() && false == PixelYourSite\is_product_catalog_feed_pro_active() ) : ?>

	<div class="card-box widget-promo">
		<h4 class="text-dark">WooCommerce Product Catalog</h4>
		<p>Use our WooCommerce Facebook Product Catalog special plugin.</p>
		<p>
			<a class="btn btn-block" href="http://www.pixelyoursite.com/product-catalog-facebook" target="_blank">Click for details</a>
		</p>
	</div>

<?php endif; ?>

<?php if ( PixelYourSite\is_edd_active() && false == PixelYourSite\is_edd_products_feed_pro_active() ) : ?>

    <div class="card-box widget-promo" style="background: #2794DA;">
        <h4 class="text-dark">Easy Digital Downloads Product Catalog</h4>
        <p>Use our Easy Digital Downloads Facebook Product Catalog special plugin.</p>
        <p>
            <a class="btn btn-block" href="http://www.pixelyoursite.com/easy-digital-downloads-product-catalog" target="_blank">Click
                for details</a>
        </p>
    </div>

<?php endif; ?>
