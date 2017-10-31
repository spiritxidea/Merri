<?php

namespace PixelYourSite\FacebookPixelPro;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Event {
	
	private $id = 0;

	private $state;
	
	private $type;
	
	private $title;

	private $on_page_triggers;

	private $dynamic_triggers;

	private $dynamic_url_filters;
	
	private $facebook_event_type;
	
	private $facebook_event_properties;
	
	private $facebook_event_custom_properties;
	
	private static $properties = array(
		'state'                            => 'active',
		'type'                             => 'on_page',
		'on_page_triggers'                 => array(),
		'dynamic_triggers'                 => array(),
		'dynamic_url_filters'              => array(),
		'facebook_event_type'              => '',
		'facebook_event_properties'        => array(),
		'facebook_event_custom_properties' => array()
	);

	private static $facebook_event_properties_options = array(
		'value' => array(
			'title' => 'value',
			'description' => 'Mandatory for purchase event only.',
			'visibility' => array(
				'ViewContent',
				'Search',
				'AddToCart',
				'AddToWishlist',
				'InitiateCheckout',
				'AddPaymentInfo',
				'Purchase',
				'Lead',
				'CompleteRegistration'
			)
		),
		'currency' => array(
			'title'       => 'currency',
			'description' => 'Mandatory for purchase event only.',
			'visibility'  => array(
				'ViewContent',
				'Search',
				'AddToCart',
				'AddToWishlist',
				'InitiateCheckout',
				'AddPaymentInfo',
				'Purchase',
				'Lead',
				'CompleteRegistration'
			)
		),
		'content_name' => array(
			'title'       => 'content_name',
			'description' => 'Name of the page/product i.e "Really Fast Running Shoes".',
			'visibility'  => array(
				'ViewContent',
				'AddToCart',
				'AddToWishlist',
				'InitiateCheckout',
				'Purchase',
				'Lead',
				'CompleteRegistration'
			)
		),
		'content_ids' => array(
			'title'       => 'content_ids',
			'description' => 'Product ids/SKUs associated with the event.',
			'visibility'  => array(
				'ViewContent',
				'Search',
				'AddToCart',
				'AddToWishlist',
				'InitiateCheckout',
				'AddPaymentInfo',
				'Purchase'
			)
		),
		'content_type' => array(
			'title'       => 'content_type',
			'description' => 'The type of content. i.e "product" or "product_group".',
			'visibility'  => array(
				'ViewContent',
				'AddToCart',
				'InitiateCheckout',
				'Purchase'
			)
		),
		'content_category' => array(
			'title'       => 'content_category',
			'description' => 'Category of the page/product.',
			'visibility'  => array(
				'Search',
				'AddToWishlist',
				'InitiateCheckout',
				'AddPaymentInfo',
				'Lead'
			)
		),
		'num_items' => array(
			'title'       => 'num_items',
			'description' => 'The number of items in the cart. i.e 3.',
			'visibility'  => array(
				'InitiateCheckout',
				'Purchase'
			)
		),
		'order_id' => array(
			'title'       => 'order_id',
			'description' => 'The unique order id of the successful purchase. i.e 19.',
			'visibility'  => array(
				'Purchase'
			)
		),
		'search_string' => array(
			'title'       => 'search_string',
			'description' => 'The string entered by the user for the search. i.e "Shoes".',
			'visibility'  => array(
				'Search'
			)
		),
		'status' => array(
			'title'       => 'status',
			'description' => 'The status of the registration. i.e "completed".',
			'visibility'  => array(
				'CompleteRegistration'
			)
		),
		'_custom_code' => array(
			'title'       => 'Custom event code<br>(advanced users only)',
			'description' => 'The code inserted in the field MUST be complete, including <code>fbq(\'track\', \'AddToCart\', { … });</code>',
			'visibility'  => array(
				'CustomCode'
			)
		),
		'_custom_event_name' => array(
			'title'       => 'Custom event name',
			'description' => '',
			'visibility'  => array(
				'CustomEvent'
			)
		),
	);

	public function __construct( $post_id = null ) {

		if ( $this->id ) {
			throw new \Exception( 'Event is already initialized.' );
		}

		$this->initialize( $post_id );

	}

	private function initialize( $post_id ) {

		if ( $post_id ) {

			$this->id    = $post_id;
			$this->title = get_the_title( $post_id );
			$post_meta   = get_post_meta( $post_id );

		} else {
			$post_meta = array();
		}

		foreach ( self::$properties as $property => $default_value ) {

			$meta_key = '_' . $property;

			if ( empty( $post_meta[ $meta_key ] ) ) {

				$this->$property = $default_value;
				continue;

			}

			$this->$property = maybe_unserialize( $post_meta[ $meta_key ][0] );

		}

	}
	
	public function update( $args ) {
		
		if ( ! $this->id ) {
			throw new \Exception( 'Could not update not existing event.' );
		}

		$this->title = empty( $args['title'] ) ? '' : sanitize_text_field( $args['title'] );

		// update event title
		wp_update_post( array(
			'ID'         => $this->id,
			'post_title' => $this->title
		) );

		// properties which has complex update logic
		$to_skip = array(
			'state',
			'type',
			'on_page_triggers',
			'dynamic_triggers',
			'dynamic_url_filters',
			'facebook_event_properties',
			'facebook_event_custom_properties'
		);

		// save or remove simple event meta
		//@todo: replace with something more clear
		foreach ( Event::getProperties() as $property => $default_value ) {

			// skip system properties
			if( in_array( $property, $to_skip ) ) {
				continue;
			}

			// remove meta if it is not present in args
			if ( empty( $args[ $property ] ) ) {

				delete_post_meta( $this->id, "_{$property}" );
				$this->$property = null;
				continue;

			}
			
			$value = sanitize_text_field( $args[ $property ] );

			$this->$property = $value;
			update_post_meta( $this->id, "_{$property}", $value );

		}

		// update event state
		if ( isset( $args['state'] ) && $args['state'] == 'on' ) {

			$this->state = 'active';
			update_post_meta( $this->id, '_state', $this->state );

		} else {

			$this->state = 'paused';
			update_post_meta( $this->id, '_state', $this->state );

		}

		// update event type
		if ( isset( $args['type'] ) && $args['type'] == 'on_page' ) {

			$this->type = 'on_page';
			update_post_meta( $this->id, '_type', $this->type );

		} else {

			$this->type = 'dynamic';
			update_post_meta( $this->id, '_type', $this->type );

		}

		// delete old event triggers
		delete_post_meta( $this->id, '_on_page_triggers' );
		delete_post_meta( $this->id, '_dynamic_triggers' );
		delete_post_meta( $this->id, '_dynamic_url_filters' );

		$this->on_page_triggers    = array();
		$this->dynamic_triggers    = array();
		$this->dynamic_url_filters = array();

		// update on page triggers
		if( $this->type == 'on_page' && isset( $args['triggers']['on_page'] ) && is_array( $args['triggers']['on_page'] ) ) {

			foreach ( $args['triggers']['on_page'] as $trigger_value ) {

				if( empty( $trigger_value ) ) {
					continue;
				}

				$this->on_page_triggers[] = $trigger_value;

			}

			update_post_meta( $this->id, '_on_page_triggers', $this->on_page_triggers );

		}

		// update dynamic triggers
		if( $this->type == 'dynamic' ) {

			if( isset( $args['triggers']['dynamic'] ) && is_array( $args['triggers']['dynamic'] ) ) {

				foreach ( $args['triggers']['dynamic'] as $trigger ) {

					if ( empty( $trigger['type'] ) || empty( $trigger['value'] ) ) {
						continue;
					}

					$this->dynamic_triggers[] = $trigger;

				}

				update_post_meta( $this->id, '_dynamic_triggers', $this->dynamic_triggers );

			}

			if ( isset( $args['triggers']['dynamic_url_filters'] ) && is_array( $args['triggers']['dynamic_url_filters'] ) ) {

				foreach ( $args['triggers']['dynamic_url_filters'] as $trigger_value ) {

					if ( empty( $trigger_value ) ) {
						continue;
					}

					//@fixme: compare sanitization with old version
					$this->dynamic_url_filters[] = $trigger_value;

				}

				update_post_meta( $this->id, '_dynamic_url_filters', $this->dynamic_url_filters );

			}

		}

		// delete old facebook event properties
		delete_post_meta( $this->id, '_facebook_event_properties' );
		delete_post_meta( $this->id, '_facebook_event_custom_properties' );

		// update facebook event properties if facebook event type was defined
		if( ! empty( $this->facebook_event_type ) ) {

			$this->updateBuiltinFacebookEventProperties( $args );
			$this->updateCustomFacebookEventProperties( $args );

		}

		EventsFactory::reset_cache( $this->id );

	}

	private function updateBuiltinFacebookEventProperties( $post_data ) {

		$properties = isset( $post_data['facebook_event_properties'] ) && is_array( $post_data['facebook_event_properties'] )
			? $post_data['facebook_event_properties']
			: array();

		$to_save = array();

		foreach ( $properties as $name => $value ) {

			if ( empty( $value ) ) {
				continue;
			}

			// skip all properties for CustomEvent except its name
			if( $this->facebook_event_type == 'CustomEvent' && $name !== '_custom_event_name' ) {
				continue;
			}

			// skip not valid properties except custom currency
			if( $name !== '_custom_currency' ) {

				// skip unknown
				if ( false == array_key_exists( $name, self::$facebook_event_properties_options ) ) {
					continue;
				}

				// skip not suitable properties for selected event type
				if ( false == in_array( $this->facebook_event_type, self::$facebook_event_properties_options[ $name ]['visibility'] ) ) {
					continue;
				}

			}
            
			$to_save[ $name ] = $value;

		}

		// maybe unset custom currency
		if ( isset( $to_save['currency'] ) && $to_save['currency'] !== 'custom' ) {
			unset( $to_save['_custom_currency'] );
		}

		$this->facebook_event_properties = $to_save;
		update_post_meta( $this->id, '_facebook_event_properties', $to_save );

	}

	private function updateCustomFacebookEventProperties( $post_data ) {

		if( $this->facebook_event_type == 'CustomCode' ) {
			return;
		}

		$custom_properties = isset( $post_data['facebook_event_custom_properties'] ) && is_array( $post_data['facebook_event_custom_properties'] )
			? $post_data['facebook_event_custom_properties']
			: array();

		$to_save = array();

		foreach ( $custom_properties as $custom_property ) {

			if ( empty( $custom_property['value'] ) ) {
				continue;
			}

			//@todo: do not allow to use builtin and reserved names
			$name  = $custom_property['name'];   //@todo: sanitize param name
			$value = sanitize_text_field( $custom_property['value'] );

			$to_save[ $name ] = $value;

		}

		$this->facebook_event_custom_properties = $to_save;
		update_post_meta( $this->id, '_facebook_event_custom_properties', $to_save );

	}

	public function getFacebookEvents() {
		
		$builtin = array(
			'ViewContent'          => 'ViewContent',
			'AddToCart'            => 'AddToCart',
			'AddToWishlist'        => 'AddToWishlist',
			'InitiateCheckout'     => 'InitiateCheckout',
			'AddPaymentInfo'       => 'AddPaymentInfo',
			'Purchase'             => 'Purchase',
			'Lead'                 => 'Lead',
			'CompleteRegistration' => 'CompleteRegistration'
		);
		
		//@todo: add filter
		return $builtin;
		
	}
	
	/**
	 * @return int|null
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getState() {
		return $this->state;
	}
	
	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}
	
	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}
	
	/**
	 * @return array
	 */
	public static function getProperties() {
		return self::$properties;
	}
	
	/**
	 * @return mixed
	 */
	public function getFacebookEventType() {
		return $this->facebook_event_type == 'CustomEvent' && isset( $this->facebook_event_properties['_custom_event_name'] )
			? $this->facebook_event_properties['_custom_event_name']
			: $this->facebook_event_type;
	}
	
	/**
	 * @return array
	 */
	public static function getFacebookEventPropertiesOptions() {
		return self::$facebook_event_properties_options;
	}
	
	public static function getCurrencies() {
		
		$currencies = array(
			'AUD' => 'Australian Dollar',
			'BRL' => 'Brazilian Real',
			'CAD' => 'Canadian Dollar',
			'CZK' => 'Czech Koruna',
			'DKK' => 'Danish Krone',
			'EUR' => 'Euro',
			'HKD' => 'Hong Kong Dollar',
			'HUF' => 'Hungarian Forint',
			'IDR' => 'Indonesian Rupiah',
			'ILS' => 'Israeli New Sheqel',
			'JPY' => 'Japanese Yen',
			'KRW' => 'Korean Won',
			'MYR' => 'Malaysian Ringgit',
			'MXN' => 'Mexican Peso',
			'NOK' => 'Norwegian Krone',
			'NZD' => 'New Zealand Dollar',
			'PHP' => 'Philippine Peso',
			'PLN' => 'Polish Zloty',
			'RON' => 'Romanian Leu',
			'GBP' => 'Pound Sterling',
			'SGD' => 'Singapore Dollar',
			'SEK' => 'Swedish Krona',
			'CHF' => 'Swiss Franc',
			'TWD' => 'Taiwan New Dollar',
			'THB' => 'Thai Baht',
			'TRY' => 'Turkish Lira',
			'USD' => 'U.S. Dollar',
			'ZAR' => 'South African Rands'
		);
		
		//@todo: add filter
		return $currencies;
		
	}
	
	public function getCurrency() {
		
		if( isset( $this->facebook_event_properties['currency'] ) && $this->facebook_event_properties['currency'] == 'custom' ) {
			return isset( $this->facebook_event_properties['_custom_currency'] ) ? $this->facebook_event_properties['_custom_currency'] : '';
		} else {
			return isset( $this->facebook_event_properties['currency'] ) ? $this->facebook_event_properties['currency'] : '';
		}
		
	}
	
	public function isCustomCurrency() {
		return ! array_key_exists( $this->getCurrency(), $this->getCurrencies() );
	}
	
	/**
	 * @return array
	 */
	public function getFacebookEventCustomProperties() {
		return $this->facebook_event_custom_properties;
	}
	
	public function getFacebookEventPropertyValue( $property ) {
		return isset( $this->facebook_event_properties[ $property ] ) 
			? $this->facebook_event_properties[ $property ]
			: '';
	}
	
	/**
	 * @return mixed
	 */
	public function getOnPageTriggers() {
		return apply_filters( 'pys_fb_pixel_pro_event_on_page_triggers', $this->on_page_triggers );
	}
	
	/**
	 * @return mixed
	 */
	public function getDynamicTriggers() {
		return $this->dynamic_triggers;
	}
	
	/**
	 * @return mixed
	 */
	public function getDynamicUrlFilters() {
		return $this->dynamic_url_filters;
	}

	/**
	 * Return Facebook event custom code if it is present and Facebook event type is set to 'Custom Code'.
	 * Otherwise returns empty string.
	 *
	 * @return string
	 */
	public function getFacebookCustomCode() {
		return $this->facebook_event_type == 'CustomCode' && isset( $this->facebook_event_properties['_custom_code'] )
			? $this->facebook_event_properties['_custom_code']
			: '';
	}

	public function getFacebookEventParams() {

		if( $this->facebook_event_type !== 'CustomCode' ) {

			$params = array();

			// add builtin params
			foreach ( $this->facebook_event_properties as $name => $value ) {

				if( $name == '_custom_event_name' ) {
					continue;
				}

				if( $name == 'currency' && $value == 'custom' ) {
					continue;
				}

				if( $name == '_custom_currency' ) {
					$params['currency'] = $value;
					continue;
				}

				$params[ $name ] = $value;

			}

			// add custom params
			foreach ( $this->facebook_event_custom_properties as $name => $value ) {
				$params[ $name ] = $value;
			}

			return $params;

		} else {
			return array();
		}

	}

	public function setState( $new_state ) {
		
		if( $new_state == 'active' || $new_state == 'paused' ) {
			
			$this->state = $new_state;
			update_post_meta( $this->id, '_state', $new_state );
			
		}
		
	}
	
	public function isFacebookStandardEvent() {
		return is_facebook_standard_event( $this->facebook_event_type );
	}
	
}