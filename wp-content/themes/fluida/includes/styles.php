<?php
/**
 * Styles and scripts registration and enqueuing
 *
 * @package fluida
 */

/* Styles and scripts */
function fluida_enqueue_styles() {

	wp_enqueue_script( 'fluida-html5shiv', get_template_directory_uri() . '/resources/js/html5shiv.min.js', null, _CRYOUT_THEME_VERSION );
	if ( function_exists( 'wp_script_add_data' ) ) wp_script_add_data( 'fluida-html5shiv', 'conditional', 'lt IE 9' );

	$cryout_theme_structure = cryout_get_theme_structure();
	$fluids = cryout_get_option();

	wp_enqueue_style( 'fluida-themefonts', get_template_directory_uri() . '/resources/fonts/fontfaces.css', null, _CRYOUT_THEME_VERSION ); // fontfaces.css

	// google fonts
	$gfonts = array();
	$roots = array();
	foreach ( $cryout_theme_structure['google-font-enabled-fields'] as $item ) {
		$itemg = $item . 'google';
		$itemw = $item . 'weight';
		// custom font names
		if ( ! empty( $fluids[$itemg] ) ) {
				$gfonts[] = cryout_gfontclean( $fluids[$itemg] ) . ":" . $fluids[$itemw];
				$roots[] = cryout_gfontclean( $fluids[$itemg] );
		}
		// preset google fonts
		if ( preg_match('/^(.*)\/gfont$/i', $fluids[$item], $bits ) ) {
				$gfonts[] = cryout_gfontclean( $bits[1] ) . ":" . $fluids[$itemw];
				$roots[] = cryout_gfontclean( $bits[1] );
		}
	};

	// enqueue google fonts with subsets separately
	foreach( $gfonts as $i => $gfont ):
		if ( strpos( $gfont, "&" ) === false):
		   // do nothing
		else:
			wp_enqueue_style( 'fluida-googlefont' . $i, '//fonts.googleapis.com/css?family=' . $gfont, null, _CRYOUT_THEME_VERSION );
			unset( $gfonts[$i] );
		endif;
	endforeach;

	// merged google fonts
	if ( count( $gfonts ) > 0 ):
		wp_enqueue_style( 'fluida-googlefonts', '//fonts.googleapis.com/css?family=' . implode( "|" , array_merge( array_unique( $roots ), array_unique( $gfonts ) ) ), null, _CRYOUT_THEME_VERSION );
	endif;

	// main theme style
	wp_enqueue_style( 'fluida-main', get_stylesheet_uri(), null, _CRYOUT_THEME_VERSION );

	// RTL style
	if (is_RTL()) wp_enqueue_style( 'fluida-rtl', get_template_directory_uri() . '/resources/styles/rtl.css', null, _CRYOUT_THEME_VERSION );

	// theme generated style
	wp_add_inline_style( 'fluida-main', preg_replace( "/[\n\r\t\s]+/", " ", fluida_custom_styles() ) ); // includes/custom-styles.php

	// user custom style
	wp_add_inline_style( 'fluida-main', preg_replace( "/[\n\r\t\s]+/", " ", htmlspecialchars_decode( $fluids['fluida_customcss'], ENT_QUOTES ) ) );

	// responsive style
	wp_enqueue_style( 'fluida-responsive', get_template_directory_uri() . '/resources/styles/responsive.css', null, _CRYOUT_THEME_VERSION );

} // fluida_enqueue_styles
add_action( 'wp_head', 'fluida_enqueue_styles', 5 );


/* Outputs the author meta link in header */
function fluida_author_link() {
	global $post;
	if ( is_single() && get_the_author_meta( 'user_url', $post->post_author ) ) {
		echo '<link rel="author" href="' . get_the_author_meta( 'user_url', $post->post_author ) . '">';
	}
}
add_action ( 'wp_head', 'fluida_author_link' );

// Adds HTML5 tags for IEs
function fluida_header_scripts() {
?>
<!--[if lt IE 9]>
<script>
document.createElement('header');
document.createElement('nav');
document.createElement('section');
document.createElement('article');
document.createElement('aside');
document.createElement('footer');
</script>
<![endif]-->
<?php
} // fluida_header_scripts()
//add_action('wp_head','fluida_header_scripts',100);

/* Main theme scripts */
function fluida_scripts_method() {

	$js_options = array(
		'masonry' => cryout_get_option('fluida_masonry'),
		'magazine' => cryout_get_option('fluida_magazinelayout'),
		'fitvids' => cryout_get_option('fluida_fitvids'),
		'articleanimation' => cryout_get_option('fluida_articleanimation'),
	);

	wp_enqueue_script( 'fluida-frontend', get_template_directory_uri() . '/resources/js/frontend.js', array( 'jquery' ), _CRYOUT_THEME_VERSION );
	wp_localize_script( 'fluida-frontend', 'fluida_settings', $js_options );
	if ($js_options['masonry']) wp_enqueue_script( 'jquery-masonry' );

	if ( is_singular() && get_option( 'thread_comments' ) ) wp_enqueue_script( 'comment-reply' );

}
add_action( 'wp_footer', 'fluida_scripts_method' );


function fluida_scripts_filter($tag) {
	$defer = cryout_get_option('fluida_defer');
    $scripts_to_defer = array( 'comment-reply.min.js', 'frontend.js', 'masonry.min.js' );
    foreach( $scripts_to_defer as $defer_script ) {
        if( (true == strpos( $tag, $defer_script )) && $defer )
            return str_replace( ' src', ' defer src', $tag ); // ' async defer src' causes issues with masonry
    }
    return $tag;
}
add_filter( 'script_loader_tag', 'fluida_scripts_filter', 10, 2 );


function fluida_responsive_meta() {
	echo '<meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1.0">' . PHP_EOL;
	echo '<meta http-equiv="X-UA-Compatible" content="IE=edge" />';
}
add_action( 'cryout_meta_hook', 'fluida_responsive_meta' );


/*
 * fluida_custom_editor_styles() is located in custom-styles.php
 */
add_editor_style( add_query_arg( 'action', 'fluida_editor_styles', admin_url( 'admin-ajax.php' ) ) );
add_action( 'wp_ajax_fluida_editor_styles', 'fluida_custom_editor_styles' );
add_action( 'wp_ajax_no_priv_fluida_editor_styles', 'fluida_custom_editor_styles' );

/* FIN */
