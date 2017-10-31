<?php

namespace PixelYourSite\FacebookPixelPro;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Upgrade data from < 6.0.0
 *
 * @param array $defaults
 */
function upgrade_to_6_0_0( $defaults ) {

	$old = get_option( 'pixel_your_site', false );

	// migrate settings
	if ( is_array( $old ) ) {
		
		$license_key    = get_option( 'pys_license_key' );
		$license_status = get_option( 'pys_license_status' );
		
		if( isset( $old['woo']['aff_event'] ) && $old['woo']['aff_event'] == 'predefined' ) {
			$affiliate_event_type = isset( $old['woo']['aff_predefined_value'] )
				? $old['woo']['aff_predefined_value']
				: $defaults['woo_affiliate_event_type'];
			$affiliate_custom_event_type = '';
		} else {
			$affiliate_event_type = 'custom';
			$affiliate_custom_event_type = isset( $old['woo']['aff_custom_value'] ) ? $old['woo']['aff_custom_value'] : '';
		}

		if ( isset( $old['woo']['pp_event'] ) && $old['woo']['pp_event'] == 'predefined' ) {
			$paypal_event_type        = isset( $old['woo']['pp_predefined_value'] )
				? $old['woo']['pp_predefined_value']
				: $defaults['woo_paypal_event_type'];
			$paypal_custom_event_type = '';
		} else {
			$paypal_event_type        = 'custom';
			$paypal_custom_event_type = isset( $old['woo']['pp_custom_value'] ) ? $old['woo']['pp_custom_value'] : '';
		}

		if ( isset( $old['woo']['purchase_value_option'] ) ) {
			$woo_purchase_value_option = $old['woo']['purchase_value_option'] == 'total' ? 'price' : $old['woo']['purchase_value_option'];
		} else {
			$woo_purchase_value_option = $defaults['woo_purchase_value_option'];
		}

		if ( isset( $old['edd']['purchase_value_option'] ) ) {
			$edd_purchase_value_option = $old['edd']['purchase_value_option'] == 'total' ? 'price' : $old['edd']['purchase_value_option'];
		} else {
			$edd_purchase_value_option = $defaults['edd_purchase_value_option'];
		}

		$settings = array(
			
			// System
			'license_key'    => $license_key,
			'license_status' => $license_status,
			
			// Globals
			'pixel_id' => isset( $old['general']['pixel_id'] )
				? $old['general']['pixel_id']
				: '',

			'in_footer' => isset( $old['general']['in_footer'] )
				? (bool) $old['general']['in_footer']
				: $defaults['in_footer'],

			'woo_enabled' => isset( $old['woo']['enabled'] )
				? (bool) $old['woo']['enabled']
				: $defaults['woo_enabled'],

			'edd_enabled' => isset( $old['edd']['enabled'] )
				? (bool) $old['edd']['enabled']
				: $defaults['edd_enabled'],

			'track_traffic_source_enabled' => isset( $old['general']['add_traffic_source'] )
				? (bool) $old['general']['add_traffic_source']
				: $defaults['track_traffic_source_enabled'],

			'advance_matching_enabled' => isset( $old['general']['enable_advance_matching'] )
				? (bool) $old['general']['enable_advance_matching']
				: $defaults['advance_matching_enabled'],

			'events_enabled' => $defaults['events_enabled'],
			
			// General Event
			'general_event_enabled' => isset( $old['general']['general_event_enabled'] )
				? (bool) $old['general']['general_event_enabled']
				: $defaults['general_event_enabled'],

			'general_event_name' => isset( $old['general']['general_event_name'] )
				? $old['general']['general_event_name']
				: $defaults['general_event_name'],

			'general_event_delay' => isset( $old['general']['general_event_delay'] )
				? $old['general']['general_event_delay']
				: $defaults['general_event_delay'],

			'general_event_on_posts_enabled' => isset( $old['general']['general_event_on_posts_enabled'] )
				? (bool) $old['general']['general_event_on_posts_enabled']
				: $defaults['general_event_on_posts_enabled'],

			'general_event_on_pages_enabled' => isset( $old['general']['general_event_on_pages_enabled'] )
				? (bool) $old['general']['general_event_on_pages_enabled']
				: $defaults['general_event_on_pages_enabled'],

			'general_event_on_tax_enabled' => isset( $old['general']['general_event_on_tax_enabled'] )
				? (bool) $old['general']['general_event_on_tax_enabled']
				: $defaults['general_event_on_tax_enabled'],

			'general_event_on_woo_enabled' => isset( $old['general']['general_event_on_woo_enabled'] )
				? (bool) $old['general']['general_event_on_woo_enabled']
				: $defaults['general_event_on_woo_enabled'],

			'general_event_on_edd_enabled' => isset( $old['general']['general_event_on_edd_enabled'] )
				? (bool) $old['general']['general_event_on_edd_enabled']
				: $defaults['general_event_on_edd_enabled'],
			
			// Extras
			'search_event_enabled' => $old['general']['search_event_enabled']
				? (bool) $old['general']['search_event_enabled']
				: $defaults['search_event_enabled'],
			
			// WooCommerce Globals
			'woo_content_id'       => isset( $old['woo']['content_id'] )
				? ( $old['woo']['content_id'] == 'id' ? 'product_id' : 'product_sku' )
				: $defaults['woo_content_id'],

			'woo_product_data' => isset( $old['woo']['variation_id'] )
				? $old['woo']['variation_id']
				: $defaults['woo_product_data'],
			
			'woo_event_value' => $defaults['woo_event_value'],

			'woo_tax_option' => isset( $old['woo']['tax'] )
				? ( $old['woo']['tax'] == 'incl' ? 'included' : 'excluded' )
				: $defaults['woo_tax_option'],

			'woo_shipping_option' => isset( $old['woo']['purchase_transport'] ) 
				? (bool) $old['woo']['purchase_transport']
				: $defaults['woo_shipping_option'],

			'woo_track_custom_audiences' => isset( $old['woo']['enable_additional_params'] )
				? (bool) $old['woo']['enable_additional_params']
				: $defaults['woo_track_custom_audiences'],


			// WooCommerce ViewContent
			'woo_view_content_enabled' => isset( $old['woo']['on_view_content'] )
				? (bool) $old['woo']['on_view_content']
				: $defaults['woo_view_content_enabled'],

			'woo_view_content_delay' => isset( $old['woo']['on_view_content_delay'] )
				? $old['woo']['on_view_content_delay']
				: $defaults['woo_view_content_delay'],

			'woo_view_content_value_enabled' => isset( $old['woo']['enable_view_content_value'] )
				? (bool) $old['woo']['enable_view_content_value']
				: $defaults['woo_view_content_value_enabled'],

			'woo_view_content_value_option' => isset( $old['woo']['view_content_value_option'] )
				? $old['woo']['view_content_value_option']
				: $defaults['woo_view_content_value_option'],

			'woo_view_content_value_percent' => isset( $old['woo']['view_content_percent_value'] )
				? $old['woo']['view_content_percent_value']
				: $defaults['woo_view_content_value_percent'],

			'woo_view_content_value_global' => isset( $old['woo']['view_content_global_value'] )
				? $old['woo']['view_content_global_value']
				: $defaults['woo_view_content_value_global'],
			
			// WooCommerce AddToCart
			'woo_add_to_cart_btn_enabled' => isset( $old['woo']['on_add_to_cart_btn'] )
				? (bool) $old['woo']['on_add_to_cart_btn']
				: $defaults['woo_add_to_cart_btn_enabled'],

			'woo_add_to_cart_page_enabled' => isset( $old['woo']['on_cart_page'] )
				? (bool) $old['woo']['on_cart_page']
				: $defaults['woo_add_to_cart_page_enabled'],

			'woo_add_to_cart_value_enabled' => isset( $old['woo']['enable_add_to_cart_value'] )
				? (bool) $old['woo']['enable_add_to_cart_value']
				: $defaults['woo_add_to_cart_value_enabled'],

			'woo_add_to_cart_value_option' => isset( $old['woo']['add_to_cart_value_option'] )
				? $old['woo']['add_to_cart_value_option']
				: $defaults['woo_add_to_cart_value_option'],

			'woo_add_to_cart_value_percent' => isset( $old['woo']['add_to_cart_percent_value'] )
				? $old['woo']['add_to_cart_percent_value']
				: $defaults['woo_add_to_cart_value_percent'],

			'woo_add_to_cart_value_global' => isset( $old['woo']['add_to_cart_global_value'] )
				? $old['woo']['add_to_cart_global_value']
				: $defaults['woo_add_to_cart_value_global'],
			
			// WooCommerce InitiateCheckout
			'woo_initiate_checkout_enabled' => isset( $old['woo']['on_checkout_page'] )
				? (bool) $old['woo']['on_checkout_page']
				: $defaults['woo_initiate_checkout_enabled'],

			'woo_initiate_checkout_value_enabled' => isset( $old['woo']['enable_checkout_value'] )
				? (bool) $old['woo']['enable_checkout_value']
				: $defaults['woo_initiate_checkout_value_enabled'],

			'woo_initiate_checkout_value_option' => isset( $old['woo']['checkout_value_option'] )
				? $old['woo']['checkout_value_option']
				: $defaults['woo_initiate_checkout_value_option'],

			'woo_initiate_checkout_value_percent' => isset( $old['woo']['checkout_percent_value'] )
				? $old['woo']['checkout_percent_value']
				: $defaults['woo_initiate_checkout_value_percent'],

			'woo_initiate_checkout_value_global' => isset( $old['woo']['checkout_global_value'] )
				? $old['woo']['checkout_global_value']
				: $defaults['woo_initiate_checkout_value_global'],
			
			// WooCommerce Purchase
			'woo_purchase_enabled' => isset( $old['woo']['on_thank_you_page'] )
				? (bool) $old['woo']['on_thank_you_page']
				: $defaults['woo_purchase_enabled'],

			'woo_purchase_on_transaction' => isset( $old['woo']['purchase_fire_once'] )
				? (bool) $old['woo']['purchase_fire_once']
				: $defaults['woo_purchase_on_transaction'],

			'woo_purchase_value_enabled' => isset( $old['woo']['enable_purchase_value'] )
				? (bool) $old['woo']['enable_purchase_value']
				: $defaults['woo_purchase_value_enabled'],

			'woo_purchase_value_option' => $woo_purchase_value_option,

			'woo_purchase_value_percent' => isset( $old['woo']['purchase_percent_value'] )
				? $old['woo']['purchase_percent_value']
				: $defaults['woo_purchase_value_percent'],

			'woo_purchase_value_global' => isset( $old['woo']['purchase_global_value'] )
				? $old['woo']['purchase_global_value']
				: $defaults['woo_purchase_value_global'],

			'woo_purchase_add_address' => isset( $old['woo']['purchase_add_address'] )
				? (bool) $old['woo']['purchase_add_address']
				: $defaults['woo_purchase_add_address'],

			'woo_purchase_add_payment_method' => isset( $old['woo']['purchase_add_payment_method'] )
				? (bool) $old['woo']['purchase_add_payment_method']
				: $defaults['woo_purchase_add_payment_method'],

			'woo_purchase_add_shipping_method' => isset( $old['woo']['purchase_add_shipping_method'] )
				? (bool) $old['woo']['purchase_add_shipping_method']
				: $defaults['woo_purchase_add_shipping_method'],

			'woo_purchase_add_coupons' => isset( $old['woo']['purchase_add_coupons'] )
				? (bool) $old['woo']['purchase_add_coupons']
				: $defaults['woo_purchase_add_coupons'],
			
			// WooCommerce Affiliate
			'woo_affiliate_enabled' => isset( $old['woo']['enable_aff_event'] )
				? (bool) $old['woo']['enable_aff_event']
				: $defaults['woo_affiliate_enabled'],

			'woo_affiliate_event_type' => $affiliate_event_type,

			'woo_affiliate_custom_event_type' => $affiliate_custom_event_type,

			'woo_affiliate_value_enabled' => isset( $old['woo']['aff_value_option'] ) && $old['woo']['aff_value_option'] !== 'none'
				? true
				: $defaults['woo_affiliate_value_enabled'],

			'woo_affiliate_value_option' => isset( $old['woo']['aff_value_option'] )
				? ( $old['woo']['aff_value_option'] == 'global' ? 'global' : 'price' )
				: $defaults['woo_affiliate_value_option'],

			'woo_affiliate_value_global' => isset( $old['woo']['aff_global_value'] )
				? $old['woo']['aff_global_value']
				: $defaults['woo_affiliate_value_global'],
			
			// WooCommerce PayPal Standard
			'woo_paypal_enabled' => isset( $old['woo']['enable_paypal_event'] )
				? (bool) $old['woo']['enable_paypal_event']
				: $defaults['woo_paypal_enabled'],

			'woo_paypal_event_type' => $paypal_event_type,

			'woo_paypal_custom_event_type' => $paypal_custom_event_type,

			'woo_paypal_value_enabled' => isset( $old['woo']['pp_value_option'] ) && $old['woo']['pp_value_option'] !== 'none'
				? true
				: $defaults['woo_paypal_value_enabled'],

			'woo_paypal_value_option' => isset( $old['woo']['pp_value_option'] )
				? ( $old['woo']['pp_value_option'] == 'global' ? 'global' : 'price' )
				: $defaults['woo_paypal_value_option'],

			'woo_paypal_value_global' => isset( $old['woo']['pp_global_value'] )
				? $old['woo']['pp_global_value']
				: $defaults['woo_paypal_value_global'],
			
			// EDD Globals
			'edd_content_id' => isset( $old['edd']['content_id'] )
				? ( $old['edd']['content_id'] == 'id' ? 'download_id' : 'download_sku' )
				: $defaults['edd_content_id'],

			'edd_tax_option' => isset( $old['edd']['tax'] )
				? ( $old['edd']['tax'] == 'incl' ? 'included' : 'excluded' )
				: $defaults['edd_tax_option'],

			'edd_event_value' => $defaults['edd_event_value'],

			'edd_track_custom_audiences' => isset( $old['edd']['enable_additional_params'] )
				? (bool) $old['edd']['enable_additional_params']
				: $defaults['edd_track_custom_audiences'],
			
			// EDD ViewContent
			'edd_view_content_enabled' => isset( $old['edd']['on_view_content'] )
				? (bool) $old['edd']['on_view_content']
				: $defaults['edd_view_content_enabled'],

			'edd_view_content_delay' => isset( $old['edd']['on_view_content_delay'] )
				? $old['edd']['on_view_content_delay']
				: $defaults['edd_view_content_delay'],

			'edd_view_content_value_enabled' => isset( $old['edd']['enable_view_content_value'] )
				? (bool) $old['edd']['enable_view_content_value']
				: $defaults['edd_view_content_value_enabled'],

			'edd_view_content_value_option' => isset( $old['edd']['view_content_value_option'] )
				? $old['edd']['view_content_value_option']
				: $defaults['edd_view_content_value_option'],

			'edd_view_content_value_percent' => isset( $old['edd']['view_content_percent_value'] )
				? $old['edd']['view_content_percent_value']
				: $defaults['edd_view_content_value_percent'],

			'edd_view_content_value_global' => isset( $old['edd']['view_content_global_value'] )
				? $old['edd']['view_content_global_value']
				: $defaults['edd_view_content_value_global'],
			
			// EDD AddToCart
			'edd_add_to_cart_enabled' => isset( $old['edd']['on_add_to_cart_btn'] )
				? (bool) $old['edd']['on_add_to_cart_btn']
				: $defaults['edd_add_to_cart_enabled'],

			'edd_add_to_cart_value_enabled' => isset( $old['edd']['enable_add_to_cart_value'] )
				? (bool) $old['edd']['enable_add_to_cart_value']
				: $defaults['edd_add_to_cart_value_enabled'],

			'edd_add_to_cart_value_option' => isset( $old['edd']['add_to_cart_value_option'] )
				? $old['edd']['add_to_cart_value_option']
				: $defaults['edd_add_to_cart_value_option'],

			'edd_add_to_cart_value_percent' => isset( $old['edd']['add_to_cart_percent_value'] )
				? $old['edd']['add_to_cart_percent_value']
				: $defaults['edd_add_to_cart_value_percent'],

			'edd_add_to_cart_value_global' => isset( $old['edd']['add_to_cart_global_value'] )
				? $old['edd']['add_to_cart_global_value']
				: $defaults['edd_add_to_cart_value_global'],
			
			// EDD InitiateCheckout
			'edd_initiate_checkout_enabled' => isset( $old['edd']['on_checkout_page'] )
				? (bool) $old['edd']['on_checkout_page']
				: $defaults['edd_initiate_checkout_enabled'],

			'edd_initiate_checkout_value_enabled' => isset( $old['edd']['enable_checkout_value'] )
				? (bool) $old['edd']['enable_checkout_value']
				: $defaults['edd_initiate_checkout_value_enabled'],

			'edd_initiate_checkout_value_option' => isset( $old['edd']['checkout_value_option'] )
				? $old['edd']['checkout_value_option']
				: $defaults['edd_initiate_checkout_value_option'],

			'edd_initiate_checkout_value_percent' => isset( $old['edd']['checkout_percent_value'] )
				? $old['edd']['checkout_percent_value']
				: $defaults['edd_initiate_checkout_value_percent'],

			'edd_initiate_checkout_value_global' => isset( $old['edd']['checkout_global_value'] )
				? $old['edd']['checkout_global_value']
				: $defaults['edd_initiate_checkout_value_global'],
			
			// EDD Purchase
			'edd_purchase_enabled' => isset( $old['edd']['on_success_page'] )
				? (bool) $old['edd']['on_success_page']
				: $defaults['edd_purchase_enabled'],

			'edd_purchase_on_transaction' => isset( $old['edd']['purchase_fire_once'] )
				? (bool) $old['edd']['purchase_fire_once']
				: $defaults['edd_purchase_on_transaction'],

			'edd_purchase_value_enabled' => isset( $old['edd']['enable_purchase_value'] )
				? (bool) $old['edd']['enable_purchase_value']
				: $defaults['edd_purchase_value_enabled'],

			'edd_purchase_value_option' => $edd_purchase_value_option,

			'edd_purchase_value_percent' => isset( $old['edd']['purchase_percent_value'] )
				? $old['edd']['purchase_percent_value']
				: $defaults['edd_purchase_value_percent'],

			'edd_purchase_value_global' => isset( $old['edd']['purchase_global_value'] )
				? $old['edd']['purchase_global_value']
				: $defaults['edd_purchase_value_global'],

			'edd_purchase_add_address' => isset( $old['edd']['purchase_add_address'] )
				? (bool) $old['edd']['purchase_add_address']
				: $defaults['edd_purchase_add_address'],

			'edd_purchase_add_payment_method' => isset( $old['edd']['purchase_add_payment_method'] )
				? (bool) $old['edd']['purchase_add_payment_method']
				: $defaults['edd_purchase_add_payment_method'],

			'edd_purchase_add_coupons' => isset( $old['edd']['purchase_add_coupons'] )
				? (bool) $old['edd']['purchase_add_coupons']
				: $defaults['edd_purchase_add_coupons'],
		
		);
		
		update_option( 'pys_fb_pixel_pro', $settings );

	}

	require_once PYS_FB_PIXEL_PATH . '/includes/class-events-factory.php';
	
	$std_events = get_option( 'pixel_your_site_std_events', false );
	$dyn_events = get_option( 'pixel_your_site_dyn_events', false );

	$old_system_params = array(
		'pageurl',
		'eventtype',
		'custom_currency',
		'code',
		'custom_name',
		'trigger_type',
		'url_filter',
		'url',
		'css',
		'scroll_pos',
		'value',
		'currency',
		'content_name',
		'content_ids',
		'content_type',
		'content_category',
		'num_items',
		'order_id',
		'search_string',
		'status'
	);

	// migrate standard events
	if( is_array( $std_events ) ) {

		$is_active = isset( $old['std']['enabled'] ) && $old['std']['enabled'];

		foreach ( $std_events as $old_event ) {

			// skip wrong events
			if ( ! isset( $old_event['eventtype'] ) || ! isset( $old_event['pageurl'] ) ) {
				continue;
			}

			if( ! empty( $old_event['custom_name'] )  ) {
				$event_type = 'CustomEvent';
			} elseif ( ! empty( $old_event['code'] ) ) {
				$event_type = 'CustomCode';
			} else {

				$event_type = $old_event['eventtype'];

				// as of Search event removed
				if ( $old_event['eventtype'] == 'Search' ) {
					$event_type = 'CustomEvent';
					$old_event['custom_name'] = 'Search';
				}

			}

			if( ! empty( $old_event['custom_currency'] ) ) {
				$currency = 'custom';
				$custom_currency = isset( $old_event['currency'] ) ? $old_event['currency'] : '';
			} else {
				$currency        = isset( $old_event['currency'] ) ? $old_event['currency'] : '';
				$custom_currency = '';
			}

			$args = array(
				'state'               => $is_active ? 'on' : 'off',
				'type'                => 'on_page',
				'triggers'            => array(
					'on_page' => array(),
					'dynamic' => array(),
					'dynamic_url_filters' => array()
				),
				'facebook_event_type' => $event_type,
				'facebook_event_properties' => array(
					'value'              => isset( $old_event['value'] ) ? $old_event['value'] : '',
					'currency'           => $currency,
					'content_name'       => isset( $old_event['content_name'] ) ? $old_event['content_name'] : '',
					'content_ids'        => isset( $old_event['content_ids'] ) ? $old_event['content_ids'] : '',
					'content_type'       => isset( $old_event['content_type'] ) ? $old_event['content_type'] : '',
					'content_category'   => isset( $old_event['content_category'] ) ? $old_event['content_category'] : '',
					'num_items'          => isset( $old_event['num_items'] ) ? $old_event['num_items'] : '',
					'order_id'           => isset( $old_event['order_id'] ) ? $old_event['order_id'] : '',
					'search_string'      => isset( $old_event['search_string'] ) ? $old_event['search_string'] : '',
					'status'             => isset( $old_event['status'] ) ? $old_event['status'] : '',
					'_custom_currency'   => $custom_currency,
					'_custom_code'       => isset( $old_event['code'] ) ? $old_event['code'] : '',
					'_custom_event_name' => isset( $old_event['custom_name'] ) ? $old_event['custom_name'] : '',
				),
				'facebook_event_custom_properties' => array(),
			);

			foreach ( $old_event as $param_name => $param_value ) {

				if ( in_array( $param_name, $old_system_params ) ) {
					continue;
				}

				$args['facebook_event_custom_properties'][ uniqid() ] = array(
					'name'  => $param_name,
					'value' => $param_value
				);

			}
			
			// migrate url filter
			if ( ! empty( $old_event['pageurl'] ) ) {
				$args['triggers']['on_page'] = array(
					uniqid() => $old_event['pageurl']
				);
			}

			EventsFactory::create( $args );
			
		}

	}

	// migrate dynamic events
	if ( is_array( $dyn_events ) ) {

		$is_active = isset( $old['dyn']['enabled'] ) && $old['dyn']['enabled'];

		foreach ( $dyn_events as $old_event ) {

			// skip wrong events
			if ( empty( $old_event['eventtype'] ) || empty( $old_event['trigger_type'] ) ) {
				continue;
			}

			if ( ! empty( $old_event['custom_name'] ) ) {
				$event_type = 'CustomEvent';
			} elseif ( ! empty( $old_event['code'] ) ) {
				$event_type = 'CustomCode';
			} else {

				$event_type = $old_event['eventtype'];

				// as of Search event removed
				if ( $old_event['eventtype'] == 'Search' ) {
					$event_type               = 'CustomEvent';
					$old_event['custom_name'] = 'Search';
				}

			}

			if ( ! empty( $old_event['custom_currency'] ) ) {
				$currency        = 'custom';
				$custom_currency = isset( $old_event['currency'] ) ? $old_event['currency'] : '';
			} else {
				$currency        = isset( $old_event['currency'] ) ? $old_event['currency'] : '';
				$custom_currency = '';
			}

			$args = array(
				'state'                            => $is_active ? 'on' : 'off',
				'type'                             => 'dynamic',
				'triggers'                         => array(
					'on_page' => array(),
					'dynamic' => array(),
					'dynamic_url_filters' => array()
				),
				'facebook_event_type'              => $event_type,
				'facebook_event_properties'        => array(
					'value'              => isset( $old_event['value'] ) ? $old_event['value'] : '',
					'currency'           => $currency,
					'content_name'       => isset( $old_event['content_name'] ) ? $old_event['content_name'] : '',
					'content_ids'        => isset( $old_event['content_ids'] ) ? $old_event['content_ids'] : '',
					'content_type'       => isset( $old_event['content_type'] ) ? $old_event['content_type'] : '',
					'content_category'   => isset( $old_event['content_category'] ) ? $old_event['content_category'] : '',
					'num_items'          => isset( $old_event['num_items'] ) ? $old_event['num_items'] : '',
					'order_id'           => isset( $old_event['order_id'] ) ? $old_event['order_id'] : '',
					'search_string'      => isset( $old_event['search_string'] ) ? $old_event['search_string'] : '',
					'status'             => isset( $old_event['status'] ) ? $old_event['status'] : '',
					'_custom_currency'   => $custom_currency,
					'_custom_code'       => isset( $old_event['code'] ) ? $old_event['code'] : '',
					'_custom_event_name' => isset( $old_event['custom_name'] ) ? $old_event['custom_name'] : '',
				),
				'facebook_event_custom_properties' => array(),
			);

			// migrate facebook event properties
			foreach ( $old_event as $param_name => $param_value ) {

				if ( in_array( $param_name, $old_system_params ) ) {
					continue;
				}

				$args['facebook_event_custom_properties'][ uniqid() ] = array(
					'name'  => $param_name,
					'value' => $param_value
				);

			}

			// migrate url filter
			if( ! empty( $old_event['url_filter'] ) ) {
				$args['triggers']['dynamic_url_filters'] = array(
					uniqid() => $old_event['url_filter']
				);
			}

			// migrate URL click trigger
			if ( $old_event['trigger_type'] == 'URL' ) {
				$args['triggers']['dynamic'][ uniqid() ] = array(
					'value' => $old_event['url'],
					'type'  => 'url_click'
				);
			}

			// migrate CSS click trigger
			if ( $old_event['trigger_type'] == 'CSS' ) {
				$args['triggers']['dynamic'][ uniqid() ] = array(
					'value' => $old_event['css'],
					'type'  => 'css_click'
				);
			}

			// migrate Scroll Position trigger
			if ( $old_event['trigger_type'] == 'scroll' ) {
				$args['triggers']['dynamic'][ uniqid() ] = array(
					'value' => $old_event['scroll_pos'],
					'type'  => 'scroll_pos'
				);
			}

			// migrate Mouseover trigger
			if ( $old_event['trigger_type'] == 'mouse-over' ) {
				$args['triggers']['dynamic'][ uniqid() ] = array(
					'value' => $old_event['css'],
					'type'  => 'css_mouseover'
				);
			}

			EventsFactory::create( $args );

		}

	}
	
}