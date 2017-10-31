<?php
/**
 * Ajax read more homepage functionality
 * @since Cryout 0.5.1
*/

if (! function_exists( 'cryout_ajax_init' ) ):
function cryout_ajax_init() {
	// loading theme settings
	$options = cryout_get_option( array(
		_CRYOUT_THEME_NAME . '_frontpage',
		_CRYOUT_THEME_NAME . '_frontpostscount'
	) );
	
	if( is_front_page() && cryout_is_true( $options[_CRYOUT_THEME_NAME . '_frontpage'] ) ) {  
		$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
		$the_query = new WP_Query( 
				array( 'posts_per_page' => $options[_CRYOUT_THEME_NAME . '_frontpostscount'], 'paged' => $paged ) 
				); 
	} /*
	elseif ( is_page_template('templates/template-blog.php') ) {
		$the_query = new WP_Query( 'post_status=publish&orderby=date&order=desc&posts_per_page='.get_option('posts_per_page'));
	}
	elseif ( is_home() ) {
		global $wp_query;
		$the_query = $wp_query;
	} */
	else {
		return;
	}
	
	// enqueue js 
	wp_enqueue_script(
		'cryout_ajax_more',
		get_template_directory_uri(). '/cryout/js/ajax.js',
		array('jquery'),
		_CRYOUT_THEME_VERSION,
		true
	);

	// Max number of pages
	$page_number_max = $the_query->max_num_pages;

	// Next page to load
	$page_number_next = (get_query_var('paged') > 1) ? get_query_var('paged') + 1 : 2;

	// Add some parameters for the JS.
	wp_localize_script(
		'cryout_ajax_more',
		'cryout_ajax_more',
		array(
			'page_number_next' => $page_number_next,
			'page_number_max' => $page_number_max,
			'page_link_model' => get_pagenum_link(9999999),
			'load_more_str' => __('More posts', 'cryout'),
			'content_css_selector' => '#content',
			'pagination_css_selector' =>  '.pagination, .navigation',
		)
	);
} // cryout_ajax_init()
endif; 

if ( ! function_exists( 'cryout_query_offset' ) ):
function cryout_query_offset(&$query) {

	$options = cryout_get_option( array(
		_CRYOUT_THEME_NAME . '_frontpage',
		_CRYOUT_THEME_NAME . '_frontpostscount'
	) );

	if ( !is_front_page() || !cryout_is_true($options[_CRYOUT_THEME_NAME . '_frontpage']) )  {
		return;
	}

    //Determine how many posts per page you want (we'll use WordPress's settings)
    $ppp = $options[_CRYOUT_THEME_NAME . '_frontpostscount'];

    //Detect and handle pagination...
    if ( $query->is_paged ) {

        //Manually determine page query offset (offset + current page (minus one) x posts per page)
        $page_offset =  ($query->query_vars['paged']-1) * $ppp ;

        //Apply adjust page offset
        $query->set('offset', $page_offset );

    }
    else {

        //This is the first page. No offset
        $query->set('offset',0);

    }
} // cryout_query_offset()
endif;

// FIN