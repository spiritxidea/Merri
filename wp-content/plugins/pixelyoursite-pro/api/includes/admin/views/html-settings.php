<?php

namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<div class="card-box">

	<form method="post" id="pys_settings_form" action="" enctype="multipart/form-data">
		
		<?php wp_nonce_field( 'pys_save_settings' ); ?>
		
		<?php do_action( 'pys_settings_sections' ); ?>

		<?php render_general_button(); ?>
		
	</form>
	
</div>