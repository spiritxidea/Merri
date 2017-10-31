<?php

namespace PixelYourSite\FacebookPixelPro;

use PixelYourSite;

/**
 * Check if WooCommerce plugin installed and activated.
 *
 * @return bool
 */
function is_woocommerce_active() {
	return function_exists( 'WC' );
}

function is_woo_version_gte( $version ) {
	
	if ( defined( 'WC_VERSION' ) && WC_VERSION ) {
		return version_compare( WC_VERSION, $version, '>=' );
	} else if ( defined( 'WOOCOMMERCE_VERSION' ) && WOOCOMMERCE_VERSION ) {
		return version_compare( WOOCOMMERCE_VERSION, $version, '>=' );
	} else {
		return false;
	}
	
}

function get_woo_custom_audiences_optimization_params( $post_id ) {
	return get_custom_audiences_optimization_params( $post_id, 'product_cat' );
}

function get_woo_product_price( $product_id, $include_tax ) {
	
	$product = wc_get_product( $product_id );
	
	if( false == $product ) {
		return 0;
	}
	
	if ( $product->is_taxable() && $include_tax ) {
		
		if ( is_woo_version_gte( '2.7' ) ) {
			$value = wc_get_price_including_tax( $product, $product->get_price() );
		} else {
			$value = $product->get_price_including_tax();
		}
		
	} else {
		
		if ( is_woo_version_gte( '2.7' ) ) {
			$value = wc_get_price_excluding_tax( $product, $product->get_price() );
		} else {
			$value = $product->get_price_excluding_tax();
		}
		
	}
	
	return $value;
	
}

function get_woo_product_price_to_display( $product_id ) {

	if ( ! $product = wc_get_product( $product_id ) ) {
		return 0;
	}
	
	if ( is_woo_version_gte( '2.7' ) ) {
		
		return wc_get_price_to_display( $product );
		
	} else {
		
		return 'incl' === get_option( 'woocommerce_tax_display_shop' ) 
			? $product->get_price_including_tax() 
			: $product->get_price_excluding_tax();
		
	}
	
}

function get_woo_event_value( $option, $amount, $global, $percent ) {

	switch ( $option ) {
		case 'global':
			$value = floatval( $global );
			break;

		case 'price':
			$value = floatval( $amount );
			break;

		case 'percent':
			$percents = floatval( $percent );
			$percents = str_replace( '%', null, $percents );
			$percents = floatval( $percents ) / 100;
			$value    = floatval( $amount ) * $percents;
			break;

		default:
			$value = 0;
	}

	return $value;

}

function get_woo_product_tags( $product_id, $implode = false ) {
	return PixelYourSite\get_object_terms( 'product_tag', $product_id, $implode );
}

function get_woo_cart_total( $include_tax ) {

	if ( $include_tax ) {
		$total = WC()->cart->cart_contents_total + WC()->cart->tax_total;
	} else {
		$total = WC()->cart->cart_contents_total;
	}
	
	return $total;
	
}

/**
 * @param \WC_Order $order
 * @param $include_tax
 * @param $include_shipping
 *
 * @return string
 */
function get_woo_order_total( $order, $include_tax, $include_shipping ) {

	if ( $include_shipping && $include_tax ) {
		
		$total = $order->get_total();   // full order price
		
	} elseif ( ! $include_shipping && ! $include_tax ) {

		$cart_subtotal  = $order->get_subtotal();

        if ( is_woo_version_gte( '2.7' ) ) {
            $discount_total = floatval( $order->get_discount_total( 'edit' ) );
        } else {
            $discount_total = $order->get_total_discount();
        }

		$total = $cart_subtotal - $discount_total;
		
	} elseif ( ! $include_shipping && $include_tax ) {

        if ( is_woo_version_gte( '2.7' ) ) {
            $cart_total     = floatval( $order->get_total( 'edit' ) );
            $shipping_total = floatval( $order->get_shipping_total( 'edit' ) );
            $shipping_tax   = floatval( $order->get_shipping_tax( 'edit' ) );
        } else {
            $cart_total     = $order->get_total();
            $shipping_total = $order->get_total_shipping();
            $shipping_tax   = $order->get_shipping_tax();
        }

		$total = $cart_total - $shipping_total - $shipping_tax;
		
	} else {
		// $include_shipping && !$include_tax

		$cart_subtotal  = $order->get_subtotal();

        if ( is_woo_version_gte( '2.7' ) ) {
            $discount_total = floatval( $order->get_discount_total( 'edit' ) );
            $shipping_total = floatval( $order->get_shipping_total( 'edit' ) );
        } else {
            $discount_total = $order->get_total_discount();
            $shipping_total = $order->get_total_shipping();
        }

		$total = $cart_subtotal - $discount_total + $shipping_total;
		
	}
	
	//wc_get_price_thousand_separator is ignored
	return number_format( $total, wc_get_price_decimals(), '.', '' );
	
}

/**
 * @param \WC_Order $order
 *
 * @return array
 */
function get_woo_additional_matching_params( $order ) {

	if( is_woo_version_gte( '3.0.0' ) ) {

		$params = array(
			'email'   => $order->get_billing_email(),
			'phone'   => $order->get_billing_phone(),
			'fn'      => $order->get_billing_first_name(),
			'ln'      => $order->get_billing_last_name(),
			'ct'      => $order->get_billing_city(),
			'st'      => $order->get_billing_state(),
			'zip'     => $order->get_billing_postcode(),
			'country' => $order->get_billing_country()
		);

	} else {

		$params = array(
			'email'   => $order->billing_email,
			'phone'   => $order->billing_phone,
			'fn'      => $order->billing_first_name,
			'ln'      => $order->billing_last_name,
			'ct'      => $order->billing_city,
			'st'      => $order->billing_state,
			'zip'     => $order->billing_postcode,
			'country' => $order->billing_country
		);

	}

	return $params;

}

/**
 * @param \WC_Order $order
 *
 * @return array
 */
function get_woo_purchase_additional_matching_params( $order, $add_address, $add_payment_method, $add_shipping_method, $add_coupons ) {
	
	$params = array();
	
	if( is_woo_version_gte( '3.0.0' ) ) {
		
		// town, state, country
		if ( $add_address ) {
			
			$params['town']    = $order->get_billing_city();
			$params['state']   = $order->get_billing_state();
			$params['country'] = $order->get_billing_country();
			
		}
		
		// payment method
		if ( $add_payment_method ) {
			$params['payment'] = $order->get_payment_method_title();
		}
		
	} else {
		
		// town, state, country
		if ( $add_address ) {
			
			$params['town']    = $order->billing_city;
			$params['state']   = $order->billing_state;
			$params['country'] = $order->billing_country;
			
		}
		
		// payment method
		if ( $add_payment_method ) {
			$params['payment'] = $order->payment_method_title;
		}
		
	}
	
	// shipping method
	$shipping_methods = $order->get_items( 'shipping' );
	if ( $add_shipping_method && $shipping_methods ) {
		
		$labels = array();
		foreach ( $shipping_methods as $shipping ) {
			$labels[] = $shipping['name'] ? $shipping['name'] : null;
		}
		
		$params['shipping'] = implode( ', ', $labels );
		
	}
	
	// coupons
	$coupons = $order->get_items( 'coupon' );
	if ( $add_coupons && $coupons ) {
		
		$labels = array();
		foreach ( $coupons as $coupon ) {
			$labels[] = $coupon['name'] ? $coupon['name'] : null;
		}
		
		$params['coupon_used'] = 'yes';
		$params['coupon_name'] = implode( ', ', $labels );
		
	} elseif ( $add_coupons ) {
		
		$params['coupon_used'] = 'no';
		
	}
	
	return $params;
	
}

/**
 * Calculate customer LTV.
 *
 *
 * @param int   $order_id
 * @param array $order_statues
 *
 * @return float
 */
function get_woo_customer_life_time_value( $order_id, $order_statues = array() ) {
    global $wpdb;

    $order_ids = array();

    // get all customer order ids or current order for guests
    if ( $user_id = get_current_user_id() ) {

        $order_ids = $wpdb->get_col( $wpdb->prepare( "
            SELECT post_id 
            FROM $wpdb->postmeta 
            WHERE   meta_key = '_customer_user' 
              AND   meta_value = '%d'
            ", $user_id ) );

    } else {
        $order_ids[] = $order_id;
    }

    if( empty( $order_ids ) ) {
        return 0;
    }
    
    if ( empty( $order_statues ) ) {
        $order_statues = array_keys( wc_get_order_statuses() );
    }

    // calculate totals

    $post_ids = implode( ', ', array_fill( 0, count( $order_ids ), '%d' ) );
    $post_statuses = implode( ', ', array_fill( 0, count( $order_statues ), '%s' ) );
    
    $query = $wpdb->prepare( "
        SELECT  SUM(meta_value) AS value
        FROM    $wpdb->postmeta AS pm
        JOIN    $wpdb->posts AS p ON pm.post_id = p.ID
        WHERE   p.ID IN ({$post_ids})
                AND p.post_status IN ({$post_statuses})
                AND pm.meta_key = '_order_total'
        GROUP BY meta_key
    ", array_merge( $order_ids, $order_statues ) );

    $results = $wpdb->get_var( $query );

    if ( null === $results ) {
        return 0;
    } else {
        return (float) $results;
    }

}