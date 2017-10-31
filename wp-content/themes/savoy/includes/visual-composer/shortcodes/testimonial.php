<?php
	
	// Shortcode: nm_testimonial
	function nm_shortcode_nm_testimonial( $atts, $content = NULL ) {
		shortcode_atts( array(
			'image_id'		=> '',
			'signature'		=> '',
			'company'		=> '',
			'description'	=> ''
		), $atts );
		
		// Image
		$image_class = $image_output = '';
		if ( strlen( $atts['image_id'] ) > 0 ) {
			$image_class = ' has-image';
			
            $image = wp_get_attachment_image( $atts['image_id'], 'full' );
            $image_output = '<div class="nm-testimonial-image">' . $image . '</div>';
		}
		
		// Company signature
		$company_output = ( isset( $atts['company'] ) ) ? ', <em>' . $atts['company'] . '</em>' : '';
		
		return '
			<div class="nm-testimonial' . $image_class . '">' .
				$image_output . '
				<div class="nm-testimonial-content">
					<div class="nm-testimonial-description">' . $atts['description'] . '</div>
					<div class="nm-testimonial-author">
						<span>' . $atts['signature'] . '</span>' .
						$company_output . '
					</div>
				</div>
			</div>';
	}
	
	add_shortcode( 'nm_testimonial', 'nm_shortcode_nm_testimonial' );