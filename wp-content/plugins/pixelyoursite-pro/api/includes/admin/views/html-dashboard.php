<?php

namespace PixelYourSite;

/**
 * Dashboard admin page content.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<div class="card-box">

	<h2>Welcome to PixelYourSite</h2>

	<div class="row installed-addons">

		<?php foreach ( PYS()->get_enabled_addons() as $addon ) : ?>

			<?php

			/** @var AbstractAddon $addon */

			if ( false == $addon->is_visible() ) {
				continue;
			}

			$status_class   = $addon->is_active() ? 'primary' : 'danger';
			$admin_page_url = $addon->admin_page_url();

			?>

			<div class="col-xs-12 col-sm-6 col-md-4">
				<div class="panel panel-<?php esc_attr_e( $status_class ); ?>">
					<div class="panel-heading">
						<h3 class="panel-title"><?php echo $addon->get_name(); ?></h3>
					</div>
					<div class="panel-body">
						<?php echo $addon->get_description(); ?>
					</div>

					<?php if ( $admin_page_url ) : ?>

						<div class="panel-footer">
							<a href="<?php echo $admin_page_url; ?>" class="btn btn-<?php esc_attr_e( $status_class ); ?>"><?php echo $addon->dashboard_button_text(); ?></a>
						</div>

					<?php endif; ?>
					
					<?php do_action( "pys_admin_dashboard_page_after_addon_panel", $addon->get_slug() ); ?>

				</div>
			</div>

		<?php endforeach; ?>

		<!-- @todo: show a message when no addons found -->

	</div><!-- /.installed-addons -->

	<!-- @todo: add recommended addons section -->
	<!-- @todo: add recommended plugins section -->

</div>