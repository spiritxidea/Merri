<?php

namespace PixelYourSite\FacebookPixelPro;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/** @var Addon $this */

?>

<div class="row">
	<div class="col-xs-12">
		<h2>Facebook Pixel Settings</h2>
	</div>
</div>

<div class="row form-horizontal">

	<div class="col-md-4 col-md-offset-2">
		<h4>Deploy pixel in:</h4>

		<div class="form-group">
			<div class="col-xs-12">
				<div class="radio">
					<?php $this->render_radio_html( 'in_footer', false, 'Head' ); ?>
				</div>
				<div class="radio">
					<?php $this->render_radio_html( 'in_footer', true, 'Footer' ); ?>
				</div>
			</div>
		</div>

	</div>

	<div class="col-md-4">
		<h4>WooCommerce Integration:</h4>

		<div class="form-group">
			<div class="col-xs-12">
				<div class="radio">
					<?php $this->render_radio_html( 'woo_enabled', true, 'Enabled' ); ?>
				</div>
				<div class="radio">
					<?php $this->render_radio_html( 'woo_enabled', false, 'Disabled' ); ?>
				</div>
			</div>
		</div>

	</div>

</div>

<div class="row form-horizontal m-t-20">

	<div class="col-md-4 col-md-offset-2">
		<h4>Do not track:</h4>

		<?php

		/**
		 * List all available user roles
		 */

		global $wp_roles;

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new \WP_Roles();
		}

		$roles = $wp_roles->get_names();
		foreach ( $roles as $role_value => $role_name ) : ?>

			<div class="form-group">
				<div class="checkbox">
					<?php $this->render_checkbox_html( "disable_for_{$role_value}", $role_name ); ?>
				</div>
			</div>
			
		<?php endforeach; ?>

	</div>

	<div class="col-md-4">
		<h4>Easy Digital Downloads Integration:</h4>

		<div class="form-group">
			<div class="col-xs-12">
				<div class="radio">
					<?php $this->render_radio_html( 'edd_enabled', true, 'Enabled' ); ?>
				</div>
				<div class="radio">
					<?php $this->render_radio_html( 'edd_enabled', false, 'Disabled' ); ?>
				</div>
			</div>
		</div>

	</div>

</div>

<hr>