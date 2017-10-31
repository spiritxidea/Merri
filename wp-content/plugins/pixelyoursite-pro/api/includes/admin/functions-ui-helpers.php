<?php

/**
 * Admin UI helpers.
 */

namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function render_general_button( $text = 'Save Settings' ) {
	?>

	<div class="row clearfix m-t-20">
		<div class="col-xs-12 col-md-4 col-md-offset-4">
			<button type="submit" name="submit" class="btn btn-lg btn-block btn-primary btn-custom btn-cta">
				<?php echo wp_kses_post( $text ); ?>
			</button>
		</div>
	</div>

	<?php
}