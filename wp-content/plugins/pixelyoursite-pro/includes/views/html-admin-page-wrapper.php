<?php

namespace PixelYourSite\FacebookPixelPro;

use PixelYourSite\PYS_Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/** @var PYS_Admin $admin */

$tabs = array(
	'index'  => array(
		'title' => 'Facebook Pixel',
		'url'   => admin_url( 'admin.php?page=fb_pixel_pro' )
	),
	'events' => array(
		'title' => 'Events',
		'url'   => admin_url( 'admin.php?page=fb_pixel_pro&tab=events' )
	)
);

if( is_woocommerce_active() ) {

	$tabs['woo'] = array(
		'title' => 'WooCommerce',
		'url'   => admin_url( 'admin.php?page=fb_pixel_pro&tab=woo' )
	);

}

if ( is_edd_active() ) {

	$tabs['edd'] = array(
		'title' => 'Easy Digital Downloads',
		'url'   => admin_url( 'admin.php?page=fb_pixel_pro&tab=edd' )
	);

}

/**
 * PHP pre 5.5 capability fix
 *
 * @since 6.0.2
 */
$current_tab = $admin->get_current_tab();
$current_tab = empty( $current_tab ) ? 'index' : $current_tab;

?>

<form method="post" action="">
	<?php wp_nonce_field( 'pys_save_settings' ); ?>

	<ul class="nav nav-tabs">
		<?php foreach ( $tabs as $tab_key => $tab ) : ?>

			<li class="<?php echo ( $current_tab == $tab_key ) ? 'active' : ''; ?>">
				<a href="<?php echo $tab['url']; ?>">
					<span><?php echo $tab['title']; ?></span>
				</a>
			</li>

		<?php endforeach; ?>
	</ul>

	<div class="tab-content">

		<?php

		switch ( $current_tab ) {
			case 'events':
				/** @noinspection PhpIncludeInspection */
				include $admin->get_current_action() == 'edit' ? 'html-admin-page-event-edit.php' : 'html-admin-page-events.php';
				break;

			case 'woo':
				include 'html-admin-page-woo.php';
				break;

			case 'edd':
				include 'html-admin-page-edd.php';
				break;

			default:
				include 'html-admin-page-index.php';
		}

		?>

	</div>
</form>