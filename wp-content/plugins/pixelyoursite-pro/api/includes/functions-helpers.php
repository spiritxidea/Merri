<?php

/**
 * Shared helper functions.
 *
 * @package PixelYourSite
 * @since   6.0.0
 */

//@todo: check for common cache plugins and show admin notices with recommendations if any

namespace PixelYourSite;

use URL;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Check if WooCommerce plugin installed and activated.
 *
 * @return bool
 */
function is_woocommerce_active() {
	return function_exists( 'WC' );
}

/**
 * Check if Easy Digital Downloads plugin installed and activated.
 *
 * @return bool
 */
function is_edd_active() {
	return function_exists( 'EDD' );
}

/**
 * Check if Product Catalog Feed Pro plugin installed and activated.
 *
 * @return bool
 */
function is_product_catalog_feed_pro_active() {
	return class_exists( 'wpwoof_product_catalog' );
}

/**
 * Check if EDD Products Feed Pro plugin installed and activated.
 *
 * @return bool
 */
function is_edd_products_feed_pro_active(){
    return class_exists( 'Wpeddpcf_Product_Catalog' );
}

/**
 * @param string $taxonomy Taxonomy name
 * @param int    $post_id  (optional) Post ID. Current will be used of not set
 *
 * @return string List of object terms
 */
function get_object_terms( $taxonomy, $post_id = null, $implode = true ) {
	
	$post_id = isset( $post_id ) ? $post_id : get_the_ID();
	$terms   = get_the_terms( $post_id, $taxonomy );
	
	if ( is_wp_error( $terms ) || empty ( $terms ) ) {
		return $implode ? '' : array();
	}
	
	$results = array();
	
	foreach ( $terms as $term ) {
		$results[] = html_entity_decode( $term->name );
	}
	
	if ( $implode ) {
		return implode( ', ', $results );
	} else {
		return $results;
	}
	
}

/**
 * Compare single URL or array of URLs with base URL. If base URL is not set, current page URL will be used.
 *
 * @param string|array $url
 * @param string       $base
 *
 * @return bool
 */
function urls_compare( $url, $base = '' ) {
    
	// use current page url if not set
	if ( empty( $base ) ) {
		$base = get_current_page_url();
	}

	$base = get_relative_to_host_url( $base );

	if ( is_string( $url ) ) {

		if ( empty( $url ) || '*' === $url ) {
			return true;
		}

		$url = get_relative_to_host_url( $url );

		$strict_match = ( substr( $url, -1 ) == '*' ) ? false : true;

		if ( $strict_match ) {

			return $base == $url;

		} else {

			$url = rtrim( $url, '*' );

			return is_string_starts_with( $base, $url );

		}

	} else {

		// recursively compare each url
		foreach ( $url as $single_url ) {

			if ( urls_compare( $single_url, $base ) ) {
				return true;
			}

		}

		return false;

	}

}

function get_current_page_url() {
	return untrailingslashit( $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
}

/**
 * Returns relative path to current or desired host. Scheme, www sub domain, forward and backwards slashes will be trimmed.
 *
 * @param        $url
 * @param string $host
 *
 * @return mixed|string
 */
function get_relative_to_host_url( $url, $host = '' ) {

	if ( empty( $host ) ) {
		$host = home_url();
	}

    if ( extension_loaded( 'mbstring' ) ) {

        $un = new URL\Normalizer();
        $un->setUrl( $url );
        $url = $un->normalize();

    }

	// remove fragment component
	$url_parts = parse_url( $url );
	if( isset( $url_parts['fragment'] ) ) {
		$url = preg_replace( '/#'. $url_parts['fragment'] . '$/', '', $url );
	}

	// remove scheme and www if any
	$host = str_replace( array( 'http://', 'https://', 'http://www.', 'https://www.', 'www.' ), '', $host );

	// remove scheme and www and current host if any
	$url = str_replace( array( 'http://', 'https://', 'http://www.', 'https://www.', 'www.' ), '', $url );
	$url = str_replace( $host, '', $url );

	$url = trim( $url );
	$url = ltrim( $url, '/' );
	$url = rtrim( $url, '/' );

	return $url;

}

function is_string_starts_with( $haystack, $needle ) {
	// search backwards starting from haystack length characters from the end
	return $needle === "" || strrpos( $haystack, $needle, -strlen( $haystack ) ) !== false;
}

/**
 * Add attribute with value to a HTML tag.
 *
 * @param string $attr_name  Attribute name, eg. "class"
 * @param string $attr_value Attribute value
 * @param string $content    HTML content where attribute should be inserted
 * @param bool   $overwrite  Override existing value of attribute or append it
 * @param string $tag        Selector name, eg. "button". Default "a"
 *
 * @return string Modified HTML content
 */
function insert_tag_attribute( $attr_name, $attr_value, $content, $overwrite = false, $tag = 'a' ) {
	
	## do not modify js attributes
	if ( $attr_name == 'on' ) {
		return $content;
	}
	
	$attr_value = trim( $attr_value );
	
	try {
		
		$doc = new \DOMDocument();
		
		/**
		 * Old libxml does not support options parameter.
		 *
		 * @since 3.2.0
		 */
		if ( defined( 'LIBXML_DOTTED_VERSION' ) && version_compare( LIBXML_DOTTED_VERSION, '2.6.0', '>=' ) &&
			version_compare( phpversion(), '5.4.0', '>=' )
		) {
			@$doc->loadHTML( '<?xml encoding="UTF-8">' . $content, LIBXML_NOEMPTYTAG );
		} else {
			@$doc->loadHTML( '<?xml encoding="UTF-8">' . $content );
		}
		
		/**
		 * Select top-level tag if it is not specified in args.
		 *
		 * @since: 5.0.6
		 */
		if ( $tag == 'any' ) {
			
			/** @var \DOMNodeList $node */
			$node = $doc->getElementsByTagName( 'body' );
			
			if ( $node->length == 0 ) {
				throw new \Exception( 'Empty or wrong tag passed to filter.' );
			}
			
			$node = $node->item( 0 )->childNodes->item( 0 );
			
		} else {
			$node = $doc->getElementsByTagName( $tag )->item( 0 );
		}
		
		if ( is_null( $node ) ) {
			return $content;
		}
		
		/** @noinspection PhpUndefinedMethodInspection */
		$attribute = $node->getAttribute( $attr_name );
		
		// add attribute or override old one
		if ( empty( $attribute ) || $overwrite ) {
			
			/** @noinspection PhpUndefinedMethodInspection */
			$node->setAttribute( $attr_name, $attr_value );
			
			return str_replace( array( '<?xml encoding="UTF-8">', '<html>', '</html>', '<body>', '</body>' ), null, $doc->saveHTML() );
			
		}
		
		// append value to exist attribute
		if ( $overwrite == false ) {
			
			$value = $attribute . ' ' . $attr_value;
			/** @noinspection PhpUndefinedMethodInspection */
			$node->setAttribute( $attr_name, $value );
			
			return str_replace( array( '<?xml encoding="UTF-8">', '<html>', '</html>', '<body>', '</body>' ), null, $doc->saveHTML() );
			
		}
		
	} catch ( \Exception $e ) {
		error_log( $e );
	}
	
	return $content;
	
}

/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 *
 * @param string|array $var
 *
 * @return string|array
 */
function pys_clean( $var ) {
    if ( is_array( $var ) ) {
        return array_map( 'pys_clean', $var );
    } else {
        return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
    }
}