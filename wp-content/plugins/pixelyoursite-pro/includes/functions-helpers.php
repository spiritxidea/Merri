<?php

namespace PixelYourSite\FacebookPixelPro;

use PixelYourSite;

function is_facebook_standard_event( $event_name ) {

	$facebook_standard_events = array(
		'PageView',
		'ViewContent',
		'Search',
		'AddToCart',
		'AddToWishlist',
		'InitiateCheckout',
		'AddPaymentInfo',
		'Purchase',
		'Lead'
	);

	return in_array( $event_name, $facebook_standard_events );

}

function get_custom_audiences_optimization_params( $post_id, $taxonomy ) {
	
	$post = get_post( $post_id );
	
	if ( ! $post ) {
		return array(
			'content_name'  => '',
			'category_name' => ''
		);
	}
	
	return array(
		'content_name'  => $post->post_title,
		'category_name' => PixelYourSite\get_object_terms( $taxonomy, $post_id )
	);
	
}

/**
 * @param Event $event
 *
 * @return string
 */
function get_event_code_preview( $event ) {
	
	if ( $event->getFacebookEventType() == 'CustomCode' ) {
		return trim( $event->getFacebookCustomCode() );
	}

	$event_params = array();
	foreach ( $event->getFacebookEventParams() as $name => $value ) {
		$event_params[] = esc_js( $name ) . ": '" . $value . "'";
	}

	$event_type   = $event->isFacebookStandardEvent() ? 'track' : 'trackCustom';
	$event_name   = $event->getFacebookEventType();
	$event_params = implode( ', ', $event_params );

	return "fbq('{$event_type}', '{$event_name}', {{$event_params}});";
	
}

/**
 * @param Event $event
 *
 * @return string
 */
function render_custom_event_trigger_conditions( $event ) {

	$html = '<div class="trigger_conditions">';

	// collect event triggers
	$pages_to_visit = array();

	foreach ( $event->getOnPageTriggers() as $trigger_value ) {
		$pages_to_visit[] = "<code>{$trigger_value}</code>";
	}

	foreach ( $event->getDynamicUrlFilters() as $trigger_value ) {
		$pages_to_visit[] = "<code>{$trigger_value}</code>";
	}

	if( ! empty( $pages_to_visit ) ) {

		$title = count( $pages_to_visit ) == 1 ? 'Page visited:' : 'One of pages visited:';
		$html .= "<p><strong>{$title}</strong>&nbsp;" . implode( ', ', $pages_to_visit ) . "</p>";

	}

	$dynamic_triggers = array();

	foreach ( $event->getDynamicTriggers() as $trigger_options ) {
		$dynamic_triggers[ $trigger_options['type'] ][] = "<code>{$trigger_options['value']}</code>";
	}

	if( ! empty( $pages_to_visit ) && ! empty( $dynamic_triggers ) ) {
		$html .= '<p class="and_rule"><strong>AND</strong></p>';
	}

	$dynamic_condition_rendered = false;
	foreach ( $dynamic_triggers as $type => $values ) {

		switch ( $type ) {
			case 'url_click':
				$title = 'An URL clicked:';
				break;

			case 'css_click':
				$title = 'A CSS selector clicked:';
				break;

			case 'css_mouseover':
				$title = 'Mouse over on CSS a selector:';
				break;

			case 'scroll_pos':
				$title = 'Page scrolled (%) to a:';
				break;

			default:
				$title = '';
				continue;
		}

		if( $dynamic_condition_rendered ) {
			//$html .= '<p class="or_rule"><strong>OR</strong></p>';
		}

		$dynamic_condition_rendered = true;
		$class = ! empty( $pages_to_visit ) ? 'sub_rule' : '';

		$html .= "<p class='{$class}'><strong>{$title}</strong>&nbsp;" . implode( ', ', $values ) . "</p>";

	}

	$html .= "</div>";
	
	return $html;

}

function render_ecommerce_plugins_notice() {

	if( is_woocommerce_active() && is_edd_active() ) {
		include 'views/html-ecommerce-notice-woo-edd.php';
	} elseif ( is_woocommerce_active() ) {
		include 'views/html-ecommerce-notice-woo.php';
	} elseif ( is_edd_active() ) {
		include 'views/html-ecommerce-notice-edd.php';
	} else {
		include 'views/html-ecommerce-notice-no-woo-no-edd.php';
	}

}

/**
 * Retrieves parameters values for for current queried object, eg. Post, Taxonomy, etc.
 *
 * @param array $content_types Allowed content types.
 *
 * @return array Array with parameters values.
 */
function get_content_parameters_values( $content_types = array() ) {
    global $post;
    
    $defaults = array(
        'on_posts_enabled'      => true,
        'on_pages_enables'      => true,
        'on_taxonomies_enabled' => true,
        'on_cpt_enabled'        => true,
        'on_woo_enabled'        => true,
        'on_edd_enabled'        => true,
    );
    
    $content_types = wp_parse_args( $content_types, $defaults );
    
    $cpt = get_post_type();
    
    $params = array();
    
    // Posts
    if ( $content_types['on_posts_enabled'] && is_singular( 'post' ) ) {
        
        $params['post_type']        = 'post';
        $params['content_name']     = $post->post_title;
        $params['post_id']          = $post->ID;
        $params['content_category'] = PixelYourSite\get_object_terms( 'category', $post->ID );
        $params['tags']             = PixelYourSite\get_object_terms( 'post_tag', $post->ID );
        
        return $params;
        
    }
    
    // Pages or Front Page
    if ( $content_types['on_pages_enables'] && ( is_singular( 'page' ) || is_home() ) ) {
        
        $params['post_type']    = 'page';
        $params['content_name'] = is_home() == true ? get_bloginfo( 'name' ) : $post->post_title;
        
        is_home() != true ? $params['post_id'] = $post->ID : null;
        
        return $params;
        
    }
    
    // WooCommerce Shop page
    if ( $content_types['on_pages_enables'] && PixelYourSite\is_woocommerce_active() && is_shop() ) {
        
        $page_id = wc_get_page_id( 'shop' );
        
        $params['post_type']    = 'page';
        $params['post_id']      = $page_id;
        $params['content_name'] = get_the_title( $page_id );
        
        return $params;
        
    }
    
    // Taxonomies (built-in and custom)
    if ( $content_types['on_taxonomies_enabled'] && ( is_category() || is_tax() || is_tag() ) ) {
        
        $term = null;
        $type = null;
        
        if ( is_category() ) {
            
            $cat  = get_query_var( 'cat' );
            $term = get_category( $cat );
            
            $params['post_type']    = 'category';
            $params['content_name'] = $term->name;
            $params['post_id']      = $cat;
            
        } elseif ( is_tag() ) {
            
            $slug = get_query_var( 'tag' );
            $term = get_term_by( 'slug', $slug, 'post_tag' );
            
            $params['post_type']    = 'tag';
            $params['content_name'] = $term->name;
            $params['post_id']      = $term->term_id;
            
        } else {
            
            $term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
            
            $params['post_type']    = get_query_var( 'taxonomy' );
            $params['content_name'] = $term->name;
            $params['post_id']      = $term->term_id;
            
        }
        
        return $params;
        
    }
    
    // WooCommerce Products
    if ( $content_types['on_woo_enabled'] && PixelYourSite\is_woocommerce_active() && $cpt == 'product' ) {
        
        $product = wc_get_product( $post->ID );
        
        $params['post_type']    = 'product';
        $params['content_name'] = $post->post_title;
        $params['post_id']      = $post->ID;
        $params['value']        = $product->get_price();
        $params['currency']     = get_woocommerce_currency();
        
        if ( $terms = PixelYourSite\get_object_terms( 'product_cat', $post->ID ) ) {
            $params['content_category'] = $terms;
        }
        
        $params['tags'] = PixelYourSite\get_object_terms( 'product_tag', $post->ID );
        
        return $params;
        
    }
    
    // Easy Digital Downloads
    if ( $content_types['on_edd_enabled'] && PixelYourSite\is_edd_active() && $cpt == 'download' ) {
        
        $download = new \EDD_Download( $post->ID );
        
        $params['post_type']    = 'download';
        $params['content_name'] = $download->post_title;
        $params['post_id']      = $post->ID;
        $params['value']        = get_edd_product_price_to_display( $post->ID );
        $params['currency']     = edd_get_currency();
        
        if ( $terms = PixelYourSite\get_object_terms( 'download_category', $post->ID ) ) {
            $params['content_category'] = $terms;
        }
        
        $params['tags'] = PixelYourSite\get_object_terms( 'download_tag', $post->ID );
        
        return $params;
        
    }

    /**
     * Custom Post Type should be last one.
     */

    // Custom Post Type
    if ( $content_types['on_cpt_enabled'] && $cpt != 'post' && $cpt != 'page' ) {

        // skip products and downloads is plugins are activated
        if ( ( PixelYourSite\is_woocommerce_active() && $cpt == 'product' ) || ( PixelYourSite\is_edd_active() && $cpt == 'download' ) ) {
            return $params;
        }

        $params['post_type']    = $cpt;
        $params['content_name'] = $post->post_title;
        $params['post_id']      = $post->ID;

        $taxonomies = get_post_taxonomies( get_post() );

        if ( ! empty( $taxonomies ) && $terms = PixelYourSite\get_object_terms( $taxonomies[0], $post->ID ) ) {
            $params['content_category'] = $terms;
        }

        $params['tags'] = PixelYourSite\get_object_terms( 'post_tag', $post->ID );

        return $params;

    }
    
    return $params;
    
}