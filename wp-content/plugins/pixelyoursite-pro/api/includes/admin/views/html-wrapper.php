<?php

namespace PixelYourSite;

/**
 * HTML wrapper for all admin pages.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/** @var PYS_Admin $this */
$current_screen = $this->get_current_screen();
$current_tab = $this->get_current_tab();

//@todo: allow addons manage page title

?>

<div class="wrap">
	<div class="pys-page-header">
		<h1><?php _e( 'PixelYourSite Pro', 'pys' ); ?></h1>
	</div>

	<div class="pys-wrapper <?php esc_attr_e( $current_screen ); ?> <?php esc_attr_e( $current_tab ); ?>">
		<div class="container-fluid">
			<div class="row">

				<div class="col-xs-12 col-sm-9 col-md-8 main-content">
					<?php do_action( 'pys_admin_page_content', $this ); ?>
					<?php do_action( 'pys_admin_' . $current_screen . '_page_content', $this ); ?>
				</div><!-- /.main-content -->

				<div class="col-xs-12 col-sm-3 col-md-3 sidebar-content">
					<?php do_action( 'pys_admin_sidebar_content', $this ); ?>
					<?php do_action( 'pys_admin_' . $current_screen . '_sidebar_content', $this ); ?>
				</div><!-- /.sidebar-content -->

			</div>

		</div>
	</div>
</div>