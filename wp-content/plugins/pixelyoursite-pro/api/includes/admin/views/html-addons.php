<?php

namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<div class="card-box">
	<h2>Licenses & Add-ons</h2>

	<form method="post" id="pys_addons_form" action="" enctype="multipart/form-data">
		<?php wp_nonce_field( 'pys_save_settings' ); ?>

		<?php foreach ( PYS()->get_registered_addons() as $addon ) : ?>

			<?php

			/** @var AbstractAddon $addon */

			$status_class = $addon->is_enabled() ? 'primary' : 'default';
			$slug = esc_attr( $addon->get_slug() );

			?>

			<div class="row">
				
				<!-- anchor for navigation from other pages -->
				<a name="<?php echo $slug; ?>"></a>

				<div class="col-xs-12">

					<div class="panel panel-<?php esc_attr_e( $status_class ); ?>">
						<div class="panel-heading">
							<h3 class="panel-title"><?php echo $addon->get_name(); ?></h3>
						</div>
						<div class="panel-body form-horizontal">
							<?php echo $addon->get_description(); ?>

							<div class="form-group switcher">
								<div class="col-xs-12">
									<?php PYS()->render_switchery_html( $slug . '_enabled', 'Enabled' ); ?>
								</div>
							</div>

							<?php do_action( "pys_render_{$slug}_addon_controls" ); ?>

						</div>
					</div>

				</div>
			</div>

		<?php endforeach; ?>

		<?php render_general_button(); ?>

	</form>

</div>