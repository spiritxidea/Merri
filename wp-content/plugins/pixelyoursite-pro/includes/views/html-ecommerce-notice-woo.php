<?php

//@todo: add anchor in settings tab (will be useful when many addons installed)

$woo_tab_url = admin_url( 'admin.php?page=fb_pixel_pro&tab=woo' );
$settings_tab_url = admin_url( 'admin.php?page=pys_settings' );

?>

<div class="row pys-ecommerce-plugins-notice">
	<div class="col-xs-12" style="padding: 30px 50px;">
		<h2><img class="plugin-logo" src="<?php echo PYS_FB_PIXEL_URL; ?>/assets/images/woocommerce_logo.png">WooCommerce Detected</h2>
		<p>You can configure your WooCommerce Events from the dedicated tab: <a href="<?php echo esc_url( $woo_tab_url ); ?>">Click Here</a></p>
		<p>You can turn ON/OFF WooCommerce Events from the plugin general settings: <a href="<?php echo esc_url( $settings_tab_url ); ?>">Click Here</a></p>
		<p>Attention: There is no need to manually add events for WooCommerce, everything is automatically taken care of by the plugin.</p>
	</div>
</div>