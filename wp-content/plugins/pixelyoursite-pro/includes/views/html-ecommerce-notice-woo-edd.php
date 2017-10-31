<?php

//@todo: add anchor in settings tab (will be useful when many addons installed)

$woo_tab_url      = admin_url( 'admin.php?page=fb_pixel_pro&tab=woo' );
$edd_tab_url      = admin_url( 'admin.php?page=fb_pixel_pro&tab=edd' );
$settings_tab_url = admin_url( 'admin.php?page=pys_settings' );

?>

<div class="row pys-ecommerce-plugins-notice">
	<div class="col-xs-12" style="padding: 30px 50px;">
		<h2>E-Commerce Settings</h2>
		<p>You are using both WooCommerce and Easy Digital Downloads on your site. Boy, you're a serious seller!</p>
		<p>Configure the pixel for WooCommerce from <a href="<?php echo esc_url( $woo_tab_url ); ?>">here</a>.</p>
		<p>Configure the pixel for EDD from <a href="<?php echo esc_url( $edd_tab_url ); ?>">here</a>.</p>
		<p>Turn ON/OFF the pixel from <a href="<?php echo esc_url( $settings_tab_url ); ?>">here</a>.</p>
	</div>
</div>