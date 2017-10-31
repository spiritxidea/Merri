<?php

namespace PixelYourSite\FacebookPixelPro;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/** @noinspection PhpIncludeInspection */
require_once PYS_API_PATH . '/includes/abstracts/abstract-addon.php';

use PixelYourSite;

class Addon extends PixelYourSite\AbstractAddon {

	public $version = PYS_FB_PIXEL_VERSION;

	private $regular_events = array();

	private $dynamic_events = array();

	private $dynamic_custom_code_events = array();

	private $custom_code_events = array();

	private $ajax_events = array();

	public function __construct() {
		parent::__construct( 'fb_pixel_pro', 'Facebook Pixel PRO', 'With PixelYourSite Pro you can add the new Facebook Pixel with just a few clicks, create Standard Events, or use Dynamic Events. Complete WooCommerce integration with out of the box Facebook Dynamic Product Ads setup.', true );
	}

	public function initialize() {

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 1 );
		
		add_action( 'admin_init', array( $this, 'update_plugin' ), 0 );
		add_action( 'admin_init', array( $this, 'process_admin_actions' ), 11 );
		
		add_action( 'pys_settings_sections', array( $this, 'render_settings_section' ) );
		add_action( 'pys_save_settings', array( $this, 'update_global_options' ) );
		add_action( 'pys_save_fb_pixel_pro', array( $this, 'update_general_options' ) );
		add_action( 'pys_save_fb_pixel_pro_events', array( $this, 'update_events_options' ) );
		add_action( 'pys_save_fb_pixel_pro_woo', array( $this, 'update_woo_options' ) );
		add_action( 'pys_save_fb_pixel_pro_edd', array( $this, 'update_edd_options' ) );
		add_action( 'pys_render_fb_pixel_pro_addon_controls', array( $this, 'render_addon_controls' ) );
		add_action( 'pys_save_addons', array( $this, 'update_license' ) );
		add_action( 'pys_admin_fb_pixel_pro_sidebar_content', array( $this, 'render_help_widget' ) );
        add_action( 'pys_admin_dashboard_page_after_addon_panel', array ( $this, 'render_dashboard_license_expiration_notices' ), 10, 1 );

        /**
         * Fixes #139
         *
         * @since 6.2.0
         */
        if ( is_plugin_active( 'thrive-visual-editor/thrive-visual-editor.php' ) ) {
            require_once 'integrations/thrive-visual-editor.php';
        }
        
		$this->initialize_settings();
		$this->maybe_migrate_settings();

		/**
		 * Do not show addon admin page and do not process front-end business logic
		 * if license was never activated before.
		 */

		/**
		 * PHP pre 5.5 capability fix
		 *
		 * @since 6.0.2
		 */

		$license_status = $this->get_option( 'license_status' );

		if( empty( $license_status ) ) {
			return;
		}

		$this->menu_items[] = array(
			'page_title' => 'Facebook Pixel',
			'menu_title' => 'Facebook Pixel',
			'menu_slug'  => $this->slug,
			'callback'   => 'admin_page_callback'
		);

		add_filter( 'pys_admin_submenu_items', array( $this, 'register_menu_items' ), 10, 1 );

        // perform pixel related stuff only at front-end
		if ( ! is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action( 'template_redirect', array( $this, 'manage_pixel' ) );
		}
        
        // perform custom audiences export
        if ( is_admin() && isset( $_GET['action'] ) && $_GET['action'] == 'pys_fb_pixel_customers_export' && isset(
                $_GET['export_scope'] )
        ) {
            
            // security check
            if ( ! current_user_can( 'manage_options' ) || empty( $_GET['nonce'] ) || ! wp_verify_nonce(
                    $_GET['nonce'], 'pys_fb_pixel_customers_export' )
            ) {
                wp_die( __( 'You are not allowed to access this page.' ), 403 );
            }
            
            if ( 'woo' == $_GET['export_scope'] ) {
                require_once 'export/export-woo.php';
                Export\woo_export_customers_to_csv( $this );
            } else {
                require_once 'export/export-edd.php';
                Export\edd_export_customers_to_csv( $this );
            }
            
            wp_die();
            
        }

	}

	private function initialize_settings() {
		global $wp_roles;

        // set options defaults
        $setting_defaults = array(
            
            // System
            'license_key'                         => '',
            'license_status'                      => '',
            'license_expires'                     => '',
            
            // Globals
            'pixel_id'                            => '',
            'in_footer'                           => false,
            'woo_enabled'                         => PixelYourSite\is_woocommerce_active() ? true : false,
            'edd_enabled'                         => PixelYourSite\is_edd_active() ? true : false,
            'track_traffic_source_enabled'        => true,
            'advance_matching_enabled'            => true,
            'click_event_enabled'                 => true,
            
            // General Event
            'general_event_enabled'               => true,
            'general_event_name'                  => 'GeneralEvent',
            'general_event_delay'                 => '',
            'general_event_on_posts_enabled'      => true,
            'general_event_on_pages_enabled'      => true,
            'general_event_on_tax_enabled'        => true,
            'general_event_on_woo_enabled'        => true,
            'general_event_on_edd_enabled'        => true,
            
            // Extras
            'search_event_enabled'                => true,
            
            // Events
            'events_enabled'                      => true,
            
            // WooCommerce Globals
            'woo_content_id'                      => 'product_id',
            'woo_product_data'                    => 'variation',
            'woo_content_id_prefix'               => '',
            'woo_content_id_suffix'               => '',
            'woo_event_value'                     => 'price',
            'woo_tax_option'                      => 'included',
            'woo_shipping_option'                 => 'included',
            'woo_track_custom_audiences'          => true,
            'woo_lifetime_value_enabled'          => true,
            'woo_lifetime_value_order_statuses'   => array(
                'wc-pending',
                'wc-processing',
                'wc-on-hold',
                'wc-completed',
            ),
            'woo_customers_export_order_statuses' => array(
                'wc-pending',
                'wc-processing',
                'wc-on-hold',
                'wc-completed',
            ),
            
            // WooCommerce ViewContent
            'woo_view_content_enabled'            => true,
            'woo_view_content_delay'              => '',
            'woo_view_content_value_enabled'      => false,
            'woo_view_content_value_option'       => 'price',
            'woo_view_content_value_percent'      => '',
            'woo_view_content_value_global'       => '',
            
            // WooCommerce AddToCart
            'woo_add_to_cart_btn_enabled'         => true,
            'woo_add_to_cart_page_enabled'        => true,
            'woo_add_to_cart_value_enabled'       => false,
            'woo_add_to_cart_value_option'        => 'price',
            'woo_add_to_cart_value_percent'       => '',
            'woo_add_to_cart_value_global'        => '',
            
            // WooCommerce InitiateCheckout
            'woo_initiate_checkout_enabled'       => true,
            'woo_initiate_checkout_value_enabled' => false,
            'woo_initiate_checkout_value_option'  => 'price',
            'woo_initiate_checkout_value_percent' => '',
            'woo_initiate_checkout_value_global'  => '',
            
            // WooCommerce Purchase
            'woo_purchase_enabled'                => true,
            'woo_purchase_on_transaction'         => true,
            'woo_purchase_value_enabled'          => true,
            'woo_purchase_value_option'           => 'price',
            'woo_purchase_value_percent'          => '',
            'woo_purchase_value_global'           => '',
            'woo_purchase_add_address'            => true,
            'woo_purchase_add_payment_method'     => true,
            'woo_purchase_add_shipping_method'    => true,
            'woo_purchase_add_coupons'            => true,
            
            // WooCommerce Affiliate
            'woo_affiliate_enabled'               => false,
            'woo_affiliate_event_type'            => 'Lead',
            'woo_affiliate_custom_event_type'     => '',
            'woo_affiliate_value_enabled'         => false,
            'woo_affiliate_value_option'          => 'price',
            'woo_affiliate_value_global'          => '',
            
            // WooCommerce PayPal Standard
            'woo_paypal_enabled'                  => false,
            'woo_paypal_event_type'               => 'AddPaymentInfo',
            'woo_paypal_custom_event_type'        => '',
            'woo_paypal_value_enabled'            => false,
            'woo_paypal_value_option'             => 'price',
            'woo_paypal_value_global'             => '',
            
            // EDD Globals
            'edd_content_id'                      => 'download_id',
            'edd_content_id_prefix'               => '',
            'edd_content_id_suffix'               => '',
            'edd_event_value'                     => 'price',
            'edd_track_custom_audiences'          => true,
            'edd_tax_option'                      => 'included',
            'edd_lifetime_value_enabled'          => true,
            'edd_lifetime_value_order_statuses'   => array( 'publish' ),
            'edd_customers_export_order_statuses' => array( 'publish' ),
            
            // EDD ViewContent
            'edd_view_content_enabled'            => true,
            'edd_view_content_delay'              => null,
            'edd_view_content_value_enabled'      => false,
            'edd_view_content_value_option'       => 'price',
            'edd_view_content_value_percent'      => '',
            'edd_view_content_value_global'       => '',
            
            // EDD AddToCart
            'edd_add_to_cart_enabled'             => true,
            'edd_add_to_cart_value_enabled'       => false,
            'edd_add_to_cart_value_option'        => 'price',
            'edd_add_to_cart_value_percent'       => '',
            'edd_add_to_cart_value_global'        => '',
            
            // EDD InitiateCheckout
            'edd_initiate_checkout_enabled'       => true,
            'edd_initiate_checkout_value_enabled' => false,
            'edd_initiate_checkout_value_option'  => 'price',
            'edd_initiate_checkout_value_percent' => '',
            'edd_initiate_checkout_value_global'  => '',
            
            // EDD Purchase
            'edd_purchase_enabled'                => true,
            'edd_purchase_on_transaction'         => true,
            'edd_purchase_value_enabled'          => true,
            'edd_purchase_value_option'           => 'price',
            'edd_purchase_value_percent'          => '',
            'edd_purchase_value_global'           => '',
            'edd_purchase_add_address'            => true,
            'edd_purchase_add_payment_method'     => true,
            'edd_purchase_add_coupons'            => true,
        
        );
		
		$this->setting_defaults = apply_filters( 'pys_fb_pixel_setting_defaults', $setting_defaults );
		
		// set options validation type
        $settings_form_fields = array(
            'system'  => array(
                'license_key'     => 'text',
                'license_status'  => 'text',
                'license_expires' => 'text',
            ),
            'global'  => array(
                'in_footer'   => 'radio',
                'woo_enabled' => 'radio',
                'edd_enabled' => 'radio',
            ),
            'general' => array(
                
                'pixel_id'                     => 'text',
                'track_traffic_source_enabled' => 'checkbox',
                'advance_matching_enabled'     => 'checkbox',
                'click_event_enabled'          => 'checkbox',
                
                'general_event_enabled'          => 'checkbox',
                'general_event_name'             => 'text',
                'general_event_delay'            => 'text',
                'general_event_on_posts_enabled' => 'checkbox',
                'general_event_on_pages_enabled' => 'checkbox',
                'general_event_on_tax_enabled'   => 'checkbox',
                'general_event_on_woo_enabled'   => 'checkbox',
                'general_event_on_edd_enabled'   => 'checkbox',
                
                'search_event_enabled' => 'checkbox',
            
            ),
            'events'  => array(
                'events_enabled' => 'checkbox',
            ),
            'woo'     => array(
                
                // WooCommerce Globals
                'woo_content_id'             => 'select',
                'woo_content_id_prefix'      => 'text',
                'woo_content_id_suffix'      => 'text',
                'woo_product_data'           => 'checkbox',
                'woo_track_custom_audiences' => 'checkbox',
                'woo_tax_option'             => 'select',
                'woo_shipping_option'        => 'select',
                'woo_event_value'            => 'radio',
                
                'woo_lifetime_value_enabled'          => 'checkbox',
                'woo_lifetime_value_order_statuses'   => 'multi_select',
                'woo_customers_export_order_statuses' => 'multi_select',
                
                // WooCommerce ViewContent
                'woo_view_content_enabled'            => 'checkbox',
                'woo_view_content_delay'              => 'text',
                'woo_view_content_value_enabled'      => 'checkbox',
                'woo_view_content_value_option'       => 'radio',
                'woo_view_content_value_percent'      => 'text',
                'woo_view_content_value_global'       => 'text',
                
                // WooCommerce AddToCart
                'woo_add_to_cart_btn_enabled'         => 'checkbox',
                'woo_add_to_cart_page_enabled'        => 'checkbox',
                'woo_add_to_cart_value_enabled'       => 'checkbox',
                'woo_add_to_cart_value_option'        => 'radio',
                'woo_add_to_cart_value_percent'       => 'text',
                'woo_add_to_cart_value_global'        => 'text',
                
                // WooCommerce InitiateCheckout
                'woo_initiate_checkout_enabled'       => 'checkbox',
                'woo_initiate_checkout_value_enabled' => 'checkbox',
                'woo_initiate_checkout_value_option'  => 'radio',
                'woo_initiate_checkout_value_percent' => 'text',
                'woo_initiate_checkout_value_global'  => 'text',
                
                // WooCommerce Purchase
                'woo_purchase_enabled'                => 'checkbox',
                'woo_purchase_on_transaction'         => 'checkbox',
                'woo_purchase_value_enabled'          => 'checkbox',
                'woo_purchase_value_option'           => 'radio',
                'woo_purchase_value_percent'          => 'text',
                'woo_purchase_value_global'           => 'text',
                'woo_purchase_add_address'            => 'checkbox',
                'woo_purchase_add_payment_method'     => 'checkbox',
                'woo_purchase_add_shipping_method'    => 'checkbox',
                'woo_purchase_add_coupons'            => 'checkbox',
                
                // WooCommerce Affiliate
                'woo_affiliate_enabled'               => 'checkbox',
                'woo_affiliate_event_type'            => 'select',
                'woo_affiliate_custom_event_type'     => 'text',
                'woo_affiliate_value_enabled'         => 'checkbox',
                'woo_affiliate_value_option'          => 'radio',
                'woo_affiliate_value_global'          => 'text',
                
                // WooCommerce PayPal Standard
                'woo_paypal_enabled'                  => 'checkbox',
                'woo_paypal_event_type'               => 'select',
                'woo_paypal_custom_event_type'        => 'text',
                'woo_paypal_value_enabled'            => 'checkbox',
                'woo_paypal_value_option'             => 'radio',
                'woo_paypal_value_global'             => 'text',
            
            ),
            'edd'     => array(
                
                // EDD Globals
                'edd_content_id'                      => 'select',
                'edd_content_id_prefix'               => 'text',
                'edd_content_id_suffix'               => 'text',
                'edd_event_value'                     => 'select',
                'edd_track_custom_audiences'          => 'checkbox',
                'edd_tax_option'                      => 'select',
                'edd_lifetime_value_enabled'          => 'checkbox',
                'edd_lifetime_value_order_statuses'   => 'multi_select',
                'edd_customers_export_order_statuses' => 'multi_select',
                
                // EDD ViewContent
                'edd_view_content_enabled'            => 'checkbox',
                'edd_view_content_delay'              => 'text',
                'edd_view_content_value_enabled'      => 'checkbox',
                'edd_view_content_value_option'       => 'radio',
                'edd_view_content_value_percent'      => 'text',
                'edd_view_content_value_global'       => 'text',
                
                // EDD AddToCart
                'edd_add_to_cart_enabled'             => 'checkbox',
                'edd_add_to_cart_value_enabled'       => 'checkbox',
                'edd_add_to_cart_value_option'        => 'radio',
                'edd_add_to_cart_value_percent'       => 'text',
                'edd_add_to_cart_value_global'        => 'text',
                
                // EDD InitiateCheckout
                'edd_initiate_checkout_enabled'       => 'checkbox',
                'edd_initiate_checkout_value_enabled' => 'checkbox',
                'edd_initiate_checkout_value_option'  => 'radio',
                'edd_initiate_checkout_value_percent' => 'text',
                'edd_initiate_checkout_value_global'  => 'text',
                
                // EDD Purchase
                'edd_purchase_enabled'                => 'checkbox',
                'edd_purchase_on_transaction'         => 'checkbox',
                'edd_purchase_value_enabled'          => 'checkbox',
                'edd_purchase_value_option'           => 'radio',
                'edd_purchase_value_percent'          => 'text',
                'edd_purchase_value_global'           => 'text',
                'edd_purchase_add_address'            => 'checkbox',
                'edd_purchase_add_payment_method'     => 'checkbox',
                'edd_purchase_add_coupons'            => 'checkbox',
            
            ),
        );

		/**
		 * Add user roles to settings
		 */

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new \WP_Roles();
		}

		foreach ( $wp_roles->get_names() as $value => $name ) {
			$settings_form_fields['global'][ 'disable_for_' . $value ] = 'checkbox';
		}

		/**
		 * Add available public custom post types to settings
		 */

		foreach ( get_post_types( array( 'public' => true, '_builtin' => false ), 'objects' ) as $post_type ) {

			// skip product post type when WOO is active
			if ( PixelYourSite\is_woocommerce_active() && $post_type->name == 'product' ) {
				continue;
			}

			// skip download post type when EDD is active
			if ( PixelYourSite\is_edd_active() && $post_type->name == 'download' ) {
				continue;
			}

			$settings_form_fields['general']["general_event_on_{$post_type->name}_enabled"] = 'checkbox';

		}

		$this->form_fields = apply_filters( 'pys_fb_pixel_settings_form_fields', $settings_form_fields );

	}

	private function maybe_migrate_settings() {

		if( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		if( false == current_user_can( 'manage_options' ) ) {
			return;
		}

		$version = get_option( 'pys_fb_pixel_version', false );

		// data scheme is actual
		if( $version && version_compare( $this->version, $version, '>=' ) ) {
			return;
		}

		$transient_name = $this->get_slug() . '_doing_upgrade';

		if( get_transient( $transient_name ) ) {
			return;
		}

		set_transient( $transient_name, true, 60 );

		require_once PYS_FB_PIXEL_PATH . '/includes/upgrade.php';
		upgrade_to_6_0_0( $this->setting_defaults );

		update_option( 'pys_fb_pixel_version', $this->version );
		delete_transient( $transient_name );

	}

	private function license_is_valid() {
		return $this->get_option( 'license_status' ) == 'valid';
	}

	public function is_active() {
		return parent::is_active() && $this->license_is_valid();
	}

	public function dashboard_button_text() {

		if( $this->license_is_valid() ) {
			return parent::dashboard_button_text();
		} else {
			return 'Activate License';
		}

	}

	public function admin_page_url() {

		if ( $this->license_is_valid() ) {

			return parent::admin_page_url();

		} else {

			return add_query_arg( array(
				'page' => 'pys_addons#fb_pixel_pro'
			), admin_url( 'admin.php' ) );

		}

	}
	
	/**
	 * Addon core functions entry point.
	 */
	public function manage_pixel() {

		add_action( 'wp_head', array( $this, 'output_version_message' ) );

		/**
		 * PHP pre 5.5 capability fix
		 *
		 * @since 6.0.2
		 */
		$pixel_id = $this->get_option( 'pixel_id' );

		// check if pixel ID is set
		if ( empty( $pixel_id ) ) {
			return;
		}
		
		if ( $this->is_disabled_for_current_role() ) {
			return;
		}

        // allow extensions to disable pixel
        if( apply_filters( 'pys_fb_pixel_disabled', false ) ) {
            return;
        }
		
		/**
		 * Hooks call priority:
		 * wp_head:
		 * 1 - pixel options;
		 * 2 - init event;
		 * 3 - all events evaluation;
		 * 4 - output events;
		 * 9 (20) - enqueue public scripts (head/footer);
		 * wp_footer
		 */
		
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_scripts' ) );
		
		add_action( 'wp_head', array( $this, 'output_pixel_options' ), 1 );
		
		add_action( 'wp_head', array( $this, 'add_initialize_pixel_event' ), 2 );
		add_action( 'wp_head', array( $this, 'add_page_view_event' ), 3 );
		add_action( 'wp_head', array( $this, 'add_general_event' ), 3 );
		add_action( 'wp_head', array( $this, 'add_search_event' ), 3 );
		
		if ( $this->get_option( 'events_enabled' ) ) {
			
			add_action( 'wp_head', array( $this, 'add_custom_events' ), 3 );
			add_filter( 'the_content', array( $this, 'filter_content_urls' ), 1000 );
			add_filter( 'widget_text', array( $this, 'filter_content_urls' ), 1000 );
            
		}
		
		add_action( 'wp_head', array( $this, 'add_woo_events' ), 3 );
		add_action( 'wp_head', array( $this, 'add_edd_events' ), 3 );
		
		add_action( 'wp_head', array( $this, 'output_regular_events' ), 4 );
		add_action( 'wp_head', array( $this, 'output_dynamic_events' ), 4 );
		add_action( 'wp_head', array( $this, 'output_custom_code_events' ), 4 );
		add_action( 'wp_head', array( $this, 'output_noscript_code' ), 4 );
		
		add_action( 'wp_footer', array( $this, 'output_ajax_events' ), 10 );
		
		// additional matching
		if ( $this->get_option( 'advance_matching_enabled' ) == true ) {
			
			//@see: https://www.facebook.com/help/ipad-app/606443329504150
			add_filter( 'pys_fb_pixel_init_params', array( $this, 'get_common_additional_matching_params' ), 10, 1 );
			add_filter( 'pys_fb_pixel_init_params', array( $this, 'get_purchase_additional_matching_params' ), 10, 1 );
			
		}
		
		// woocommerce shop page ajax AddToCart simple and external products, paypal order button events
		if ( PixelYourSite\is_woocommerce_active() && $this->get_option( 'woo_enabled' ) ) {
			
			if ( $this->get_option( 'woo_add_to_cart_btn_enabled' ) || $this->get_option( 'woo_affiliate_enabled' ) ) {
				add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'add_woo_add_to_cart_button_attribute' ), 10, 2 );
			}
			
			if ( $this->get_option( 'woo_paypal_enabled' ) ) {
				add_action( 'wp_head', array( $this, 'add_woo_paypal_event' ), 4 );
			}
			
		}
		
		// edd add_to_cart buttons
		if ( PixelYourSite\is_edd_active() && $this->get_option( 'edd_enabled' ) && $this->get_option( 'edd_add_to_cart_enabled' ) ) {
			add_filter( 'edd_purchase_link_args', array( $this, 'add_edd_add_to_cart_button_attribute' ), 10, 1 );
		}
		
		// extras
		add_filter( 'pys_fb_pixel_event_params', array( $this, 'add_event_domain_param' ), 10, 2 );
        
        // allow 3rd party add own hooks
        do_action( 'pys_fb_pixel_manage_pixel', $this );
        
	}

	public function update_global_options() {
		$this->update_options( 'global' );
	}

	public function update_license() {

        // nothing to do...
        if( ! isset( $_POST['pys_fb_pixel_pro_license_action'] ) ) {
            return;
        }

		$license_key = isset( $_POST['pys_fb_pixel_pro_license_key'] ) ? $_POST['pys_fb_pixel_pro_license_key'] : '';
		$license_status = $this->get_option( 'license_status' );
        $license_expires = $this->get_option( 'license_expires' );
        
		// activate/deactivate license
        if ( $_POST['pys_fb_pixel_pro_license_action'] == 'activate' ) {
            $license_data = license_activate( $license_key );
        } else {
            $license_data = license_deactivate( $license_key );
        }

        $admin_notice = null;     // data for admin notice

        if ( is_wp_error( $license_data ) ) {

            $admin_notice = array(
                'class' => 'danger',
                'msg'   => 'Something went wrong during license update request. [' . $license_data->get_error_message() . ']'
            );

        } else {

            /**
             * Overwrite empty license status only on successful activation.
             * For existing status overwrite with any value except error.
             */
            if ( empty( $license_status ) && $license_data->license == 'valid' ) {
                $license_status = 'valid';
            } elseif ( ! empty( $license_status ) ) {
                $license_status = $license_data->license;
            }

            if ( $license_data->success ) {

                switch ( $license_data->license ) {
                    case
                    'valid':
                        $admin_notice = array(
                            'class' => 'success',
                            'msg'   => 'Your license is working fine. Good job!'
                        );
                        break;

                    case 'deactivated':
                        $admin_notice = array(
                            'class' => 'success',
                            'msg'   => 'Your license was successfully deactivated for this site.'
                        );
                        break;
                }

                $license_expires = strtotime( $license_data->expires );

            } else {

                switch ( $license_data->error ) {
                    case 'invalid':                 // key do not exist
                    case 'missing':
                    case 'key_mismatch':
                        $admin_notice = array(
                            'class' => 'danger',
                            'msg'   => "License keys don't match. Make sure you're using the correct license."
                        );
                        break;

                    case 'license_not_activable':   // trying to activate bundle license
                        $admin_notice = array(
                            'class' => 'danger',
                            'msg'   => 'If you have a bundle package, please use each individual license for your products.'
                        );
                        break;

                    case 'revoked':                 // license key revoked
                        $admin_notice = array(
                            'class' => 'danger',
                            'msg'   => 'This license was revoked.'
                        );
                        break;

                    case 'no_activations_left':     // no activations left
                        $admin_notice = array(
                            'class' => 'danger',
                            'msg'   => 'No activations left. Log in to your account to extent your license.'
                        );
                        break;

                    case 'invalid_item_id':
                        $admin_notice = array(
                            'class' => 'danger',
                            'msg'   => 'Invalid item ID.'
                        );
                        break;

                    case 'item_name_mismatch':      // item names don't match
                        $admin_notice = array(
                            'class' => 'danger',
                            'msg'   => "Item names don't match."
                        );
                        break;

                    case 'expired':                 // license has expired
                        $admin_notice = array(
                            'class' => 'danger',
                            'msg'   => 'Your License has expired. <a href="http://www.pixelyoursite.com/checkout/?edd_license_key=' . esc_url( $license_key ) . '&utm_campaign=admin&utm_source=licenses&utm_medium=renew" target="_blank">Renew it now.</a>'
                        );
                        break;

                    case 'inactive':                // license is not active
                        $admin_notice = array(
                            'class' => 'danger',
                            'msg'   => 'This license is not active. Activate it now.'
                        );
                        break;

                    case 'disabled':                // license key disabled
                        $admin_notice = array(
                            'class' => 'danger',
                            'msg'   => 'License key disabled.'
                        );
                        break;

                    case 'site_inactive':
                        $admin_notice = array(
                            'class' => 'danger',
                            'msg'   => 'The license is not active for this site. Activate it now.'
                        );
                        break;

                }

                // add error code
                $admin_notice['msg'] .= " [error: $license_data->error]";

            }

        }

        if ( $admin_notice ) {
            set_transient( 'pys_fb_pixel_pro_license_notice', $admin_notice, 60 * 5 );
        }

        $values = array (
            'license_key'     => $license_key,
            'license_status'  => $license_status,
            'license_expires' => $license_expires
        );

		$this->update_options( 'system', $values );

		if( ! is_wp_error( $license_data ) && $license_data->license == 'valid' ) : ?>

			<script type="text/javascript">
				window.location = '<?php echo admin_url( 'admin.php?page=fb_pixel_pro' ); ?>';
			</script>

		<?php endif;
	}

	public function update_general_options() {
		$this->update_options( 'general' );
	}

	public function update_events_options() {
		$this->update_options( 'events' );
	}

	public function update_woo_options() {
		$this->update_options( 'woo' );
	}

	public function update_edd_options() {
		$this->update_options( 'edd' );
	}

	public function admin_page_callback( $admin ) {
		include 'views/html-admin-page-wrapper.php';
	}

	public function render_settings_section() {
		include 'views/html-settings-section.php';
	}
	
	public function is_disabled_for_current_role() {

		$user = wp_get_current_user();

		foreach ( (array) $user->roles as $role ) {

			$is_disabled = $this->get_option( 'disable_for_' . $role, false );

			if( $is_disabled ) {

				add_action( 'wp_head', array( $this, 'output_disabled_for_role_message' ) );
				return true;

			}

		}

		return false;

	}

	public function output_disabled_for_role_message() {
		echo "\r\n<!-- Facebook Pixel is disabled for current role -->\r\n\r\n";
	}

	public function output_version_message() {
		echo "\r\n<!-- Facebook Pixel code is added on this page by PixelYourSite " . $this->version  . " addon. You can test it with Pixel Helper Chrome Extension. -->\r\n\r\n";
	}

	public function admin_enqueue_scripts( $page_hook ) {

        /**
         * Issues #144, 143, 142 fix.
         * Enqueue dependencies only at addon's pages.
         *
         * @since 6.2.1
         */
        if ( ! in_array( $page_hook, PixelYourSite\PYS()->admin->get_page_hooks() ) ) {
            return;
        }

        wp_register_style(
            'sweetalert2',
            '//cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/6.5.5/sweetalert2.min.css'
        );

        wp_register_script(
            'sweetalert2',
            '//cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/6.5.5/sweetalert2.min.js',
            array( 'jquery' )
        );

        wp_register_style(
            'select2',
            '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css'
        );

        wp_register_script(
            'select2',
            '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
            array( 'jquery' )
        );

        wp_enqueue_style( 'sweetalert2' );
        wp_enqueue_script( 'sweetalert2' );

        wp_enqueue_style( 'select2' );
        wp_enqueue_script( 'select2' );

        /**
         * Issue #93 fix.
         * Imagify 1.6.3 plugin uses old Sweetalert2 version which is creates unwanted output on any admin page and
         * blocks UI with invisible full page overlay. This tweak overwrites Imagify's script and styles with last version.
         *
         * @since 6.0.3
         */
        if ( is_plugin_active( 'imagify/imagify.php' ) ) {

            wp_register_style(
                'imagify-css-sweetalert',
                '//cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/6.5.5/sweetalert2.min.css'
            );

            wp_register_script(
                'imagify-js-sweetalert',
                '//cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/6.5.5/sweetalert2.min.js',
                array( 'jquery' )
            );

        }

        /**
         * Issue #117 fix.
         * SUMO Discounts 2.9 plugin uses old Sweetalert2 version which is creates unwanted output on any admin page and
         * blocks UI with invisible full page overlay. This tweak overwrites Imagify's script and styles with last version.
         *
         * @since 6.1.1
         */
        if ( is_plugin_active( 'sumodiscounts/sumodiscounts.php' ) ) {
            
            wp_register_style(
                'css-sweet-alert',
                '//cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/6.5.5/sweetalert2.min.css'
            );
            
            wp_register_script(
                'jquery-sweet-alert',
                '//cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/6.5.5/sweetalert2.min.js',
                array( 'jquery' )
            );
            
        }
        
	}

	public function enqueue_public_scripts() {

		$in_footer = $this->get_option( 'in_footer', false );

		wp_enqueue_script( 'jquery-bind-first', PYS_FB_PIXEL_URL . '/js/jquery.bind-first-0.2.3.min.js', array( 'jquery' ), $this->version, $in_footer );
		wp_enqueue_script( 'js-cookie', PYS_FB_PIXEL_URL . '/js/js.cookie-2.1.3.min.js', array(), $this->version, $in_footer );
		wp_enqueue_script( $this->slug, PYS_FB_PIXEL_URL . '/js/public.js', array( 'jquery', 'js-cookie' , 'jquery-bind-first' ), $this->version, $in_footer );

	}

	public function output_pixel_options() {
        
        $pixel_options = array(
            'site_url'             => get_site_url(),
            'domain'               => substr( get_home_url( null, '', 'http' ), 7 ),
            'track_traffic_source' => $this->get_option( 'track_traffic_source_enabled' ),
            'click_event_enabled'  => $this->get_option( 'click_event_enabled' ),
            'click_event_params'   => get_content_parameters_values(),
        );
		
		$pixel_options = apply_filters( 'pys_fb_pixel_options', $pixel_options );
		
		wp_localize_script( $this->slug, 'pys_fb_pixel_options', $pixel_options );

	}

	public function output_noscript_code() {

		if ( empty( $this->regular_events ) ) {
			return;
		}

		foreach ( $this->regular_events as $event ) {

			$args = array();

			if ( $event['type'] == 'init' ) {
				continue;
			}

			$args['id']       = $this->get_option( 'pixel_id' );
			$args['ev']       = $event['name'];
			$args['noscript'] = 1;

			foreach ( $event['params'] as $param => $value ) {
				@$args[ 'cd[' . $param . ']' ] = urlencode( $value );
			}

			$src_attr = add_query_arg( $args, 'https://www.facebook.com/tr' );

			// note: ALT tag used to pass ADA compliance
			// @see: issue #63 for details

			echo "<noscript><img height='1' width='1' style='display: none;' src='{$src_attr}' alt='facebook_pixel'></noscript>";

		}

	}

	public function add_initialize_pixel_event() {

		$params = apply_filters( 'pys_fb_pixel_init_params', array() );
        $params = $this->sanitize_event_params( $params );

        // default pixel ID
        $init_events[] = array (
            'pixel_id' => $this->get_option( 'pixel_id' ),
            'params'   => $params,
        );

        // multiple pixels IDs support
        $init_events = apply_filters( 'pys_fb_pixel_initialize_pixel_event', $init_events, $params );

        // run pixel initialize events
        foreach ( $init_events as $event ) {

            $this->regular_events[] = array (
                'type'   => 'init',
                'name'   => $event['pixel_id'],
                'params' => $event['params']
            );

        }

	}
	
	public function add_page_view_event() {
		$this->add_regular_event( 'PageView' );
	}

    public function add_general_event() {

        if ( false == $this->get_option( 'general_event_enabled' ) ) {
            return;
        }

        $event_name = $this->get_option( 'general_event_name' );
        if ( empty( $event_name ) ) {
            $event_name = 'GeneralEvent';
        }

        $delay = floatval( $this->get_option( 'general_event_delay', 0 ) );

        $content_types = array(
            'on_posts_enabled'      => $this->get_option( 'general_event_on_posts_enabled' ),
            'on_pages_enables'      => $this->get_option( 'general_event_on_pages_enabled' ),
            'on_taxonomies_enabled' => $this->get_option( 'general_event_on_tax_enabled' ),
            'on_cpt_enabled'        => $this->get_option( 'general_event_on_' . get_post_type() . '_enabled', false ),
            'on_woo_enabled'        => $this->get_option( 'general_event_on_woo_enabled' ),
            'on_edd_enabled'        => $this->get_option( 'general_event_on_edd_enabled' ),
        );

        $params = get_content_parameters_values( $content_types );

        if ( ! empty( $params ) ) {
            $this->add_regular_event( $event_name, $params, $delay );
        }
        
    }
	
	public function add_search_event() {
		
		if ( false == $this->get_option( 'search_event_enabled' ) || false == is_search() || empty( $_REQUEST['s'] ) ) {
			return;
		}
		
		$this->add_regular_event( 'Search', array(
			'search_string' => $_REQUEST['s']
		) );

	}
	
	public function add_custom_events() {

		// process 'on_page' events
		foreach ( EventsFactory::get( 'on_page', 'active' ) as $on_page_event ) {
			/** @var Event $on_page_event */

			/**
			 * PHP pre 5.5 capability fix
			 *
			 * @since 6.0.2
			 */
			$on_page_triggers = $on_page_event->getOnPageTriggers();
            
            if ( ! empty( $on_page_triggers ) ) {

                // at least one trigger should match current page url
                if ( false == PixelYourSite\urls_compare( $on_page_event->getOnPageTriggers() ) ) {
                    continue;
                }

            }

			$facebook_event_type = $on_page_event->getFacebookEventType();
			
			if ( empty( $facebook_event_type ) ) {
				continue;
			}

			if( 'CustomCode' == $facebook_event_type ) {
				$this->custom_code_events[] = $on_page_event->getFacebookCustomCode();
			} else {
				$this->add_regular_event( $facebook_event_type, $on_page_event->getFacebookEventParams() );
			}
			
		}

		// process 'dynamic' events
		foreach ( EventsFactory::get( 'dynamic', 'active' ) as $dynamic_event ) {
			/** @var Event $dynamic_event */

			/**
			 * PHP pre 5.5 capability fix
			 *
			 * @since 6.0.2
			 */
			$dynamic_triggers = $dynamic_event->getDynamicTriggers();

			// at least one trigger should be set
			//@todo: may be remove it to allow assign custom event to any element by data attribute
			if ( empty( $dynamic_triggers ) ) {
				continue;
			}

			/**
			 * PHP pre 5.5 capability fix
			 *
			 * @since 6.0.2
			 */
			$dynamic_url_filters = $dynamic_event->getDynamicUrlFilters();

			// at least one url filter should match current page url
			if ( false == empty( $dynamic_url_filters ) && false == PixelYourSite\urls_compare( $dynamic_event->getDynamicUrlFilters() ) ) {
				continue;
			}

			$facebook_event_type = $dynamic_event->getFacebookEventType();

			if ( empty( $facebook_event_type ) ) {
				continue;
			}

			foreach ( $dynamic_event->getDynamicTriggers() as $event_trigger ) {

				if ( 'CustomCode' == $facebook_event_type ) {
					$this->add_dynamic_custom_code_event( $dynamic_event, $event_trigger['type'], $event_trigger['value'] );
				} else {
					$this->add_dynamic_event( $dynamic_event, $event_trigger['type'], $event_trigger['value'] );
				}

			}

		}

		
	}

	public function add_woo_events() {
		global $post;

		if ( false == PixelYourSite\is_woocommerce_active() || false == $this->get_option( 'woo_enabled' ) ) {
			return;
		}

		$currency               = get_woocommerce_currency();
		$include_tax            = $this->get_option( 'woo_tax_option' ) == 'included' ? true : false;
		$track_custom_audiences = $this->get_option( 'woo_track_custom_audiences' );
		$customize_value        = $this->get_option( 'woo_event_value' ) == 'custom';

		$params                 = array(
			'content_type' => 'product'
		);

		// AddToCart non-ajax on Shop Page Event
		if ( $this->get_option( 'woo_add_to_cart_btn_enabled' ) && isset( $_REQUEST['add-to-cart'] ) ) {

			if ( $this->get_option( 'woo_product_data' ) != 'main' && isset( $_REQUEST['variation_id'] ) ) {
				$product_id = $_REQUEST['variation_id'];
			} else {
				$product_id = $_REQUEST['add-to-cart'];
			}

			$this->add_regular_event( 'AddToCart', $this->get_woo_product_add_to_cart_params( $product_id ) );

            // IMPORTANT: this block should not break function flow (return), as of ViewContent bellow also should be processed

		}

		// ViewContent Event
		if ( $this->get_option( 'woo_view_content_enabled' ) && is_product() ) {

            $product = wc_get_product( $post->ID );

            $delay                  = floatval( $this->get_option( 'woo_view_content_delay', 0 ) );
            $params['content_ids']  = json_encode( $this->get_woo_product_content_id( $post->ID ) );

            if ( $product->get_type() == 'variable' && $this->get_option( 'woo_product_data' ) != 'main' ) {
                $params['content_type'] = 'product_group';
            } else {
                $params['content_type'] = 'product';
            }

			// content_name, category_name, tags
			if ( $track_custom_audiences ) {
				$params['tags'] = get_woo_product_tags( $post->ID, true );
				$params = array_merge( $params, get_woo_custom_audiences_optimization_params( $post->ID ) );
			}

			// currency, value
			if ( $this->get_option( 'woo_view_content_value_enabled' ) ) {

				if( $customize_value ) {
					$amount = get_woo_product_price( $post->ID, $include_tax );
				} else {
					$amount = get_woo_product_price_to_display( $post->ID );
				}

				$value_option   = $this->get_option( 'woo_view_content_value_option' );
				$global_value   = $this->get_option( 'woo_view_content_value_global', 0 );
				$percents_value = $this->get_option( 'woo_view_content_value_percent', 100 );

				$params['value']    = get_woo_event_value( $value_option, $amount, $global_value, $percents_value );
				$params['currency'] = $currency;

			}

			$this->add_regular_event( 'ViewContent', $params, $delay );

			return;

		}

		// AddToCart on Cart Page Event
		if ( $this->get_option( 'woo_add_to_cart_page_enabled' ) && is_cart() ) {

			$content_ids        = array();
			$content_names      = array();
			$content_categories = array();
			$tags               = array();

			foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {

                $product_id  = $this->get_woo_product_id( $cart_item );
                $content_ids = array_merge( $content_ids, $this->get_woo_product_content_id( $product_id ) );

				// content_name, category_name, tags
				if ( $track_custom_audiences ) {

					$custom_audiences = get_woo_custom_audiences_optimization_params( $product_id );

					$content_names[]      = $custom_audiences['content_name'];
					$content_categories[] = $custom_audiences['category_name'];

					$cart_item_tags = get_woo_product_tags( $product_id );
					$tags = array_merge( $tags, $cart_item_tags );

				}

			}

			$params['content_ids']   = json_encode( $content_ids );
			$params['content_name']  = implode( ', ', $content_names );
			$params['category_name'] = implode( ', ', $content_categories );

			$tags           = array_unique( $tags );
			$tags           = array_slice( $tags, 0, 100 );
			$params['tags'] = implode( ', ', $tags );

			// currency, value
			if ( $this->get_option( 'woo_add_to_cart_value_enabled' ) ) {

				if ( $customize_value ) {
					$amount = get_woo_cart_total( $include_tax );
				} else {
					$amount = $params['value'] = WC()->cart->subtotal;
				}

				$value_option   = $this->get_option( 'woo_add_to_cart_value_option' );
				$global_value   = $this->get_option( 'woo_add_to_cart_value_global', 0 );
				$percents_value = $this->get_option( 'woo_add_to_cart_value_percent', 100 );

				$params['value'] = get_woo_event_value( $value_option, $amount, $global_value, $percents_value );
				$params['currency'] = $currency;

			}

			$this->add_regular_event( 'AddToCart', $params );

			return;

		}

		// InitiateCheckout Event
		if ( $this->get_option( 'woo_initiate_checkout_enabled' ) && is_checkout() && ! is_wc_endpoint_url() ) {

			$content_ids        = array();
			$content_names      = array();
			$content_categories = array();
			$tags               = array();

			foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {

                $product_id  = $this->get_woo_product_id( $cart_item );
                $content_ids = array_merge( $content_ids, $this->get_woo_product_content_id( $product_id ) );

				// content_name, category_name, tags
				if ( $track_custom_audiences ) {

					$custom_audiences = get_woo_custom_audiences_optimization_params( $product_id );

					$content_names[]      = $custom_audiences['content_name'];
					$content_categories[] = $custom_audiences['category_name'];

					$cart_item_tags = get_woo_product_tags( $product_id );
					$tags = array_merge( $tags, $cart_item_tags );

				}

			}

            $params['content_ids']   = json_encode( $content_ids );
            $params['content_name']  = implode( ', ', $content_names );
            $params['category_name'] = implode( ', ', $content_categories );

			$tags           = array_unique( $tags );
			$tags           = array_slice( $tags, 0, 100 );
			$params['tags'] = implode( ', ', $tags );

			if ( $track_custom_audiences ) {
				$params['num_items'] = WC()->cart->get_cart_contents_count();
			}

			// currency, value
			if ( $this->get_option( 'woo_initiate_checkout_value_enabled' ) ) {

				if ( $customize_value ) {
					$amount = get_woo_cart_total( $include_tax );
				} else {
					$amount = WC()->cart->subtotal;
				}

				$value_option   = $this->get_option( 'woo_initiate_checkout_value_option' );
				$global_value   = $this->get_option( 'woo_initiate_checkout_value_global', 0 );
				$percents_value = $this->get_option( 'woo_initiate_checkout_value_percent', 100 );

				$params['value'] = get_woo_event_value( $value_option, $amount, $global_value, $percents_value );
				$params['currency'] = $currency;

			}

			$this->add_regular_event( 'InitiateCheckout', $params );

			return;

		}

		// Purchase Event
		if ( $this->get_option( 'woo_purchase_enabled' ) && is_order_received_page() && isset( $_REQUEST['key'] ) ) {

			$order_id = (int) wc_get_order_id_by_order_key( $_REQUEST['key'] );

			// skip if event was fired before
			if ( $this->get_option( 'woo_purchase_on_transaction' ) && get_post_meta( $order_id, '_pys_purchase_event_fired', true ) ) {
				return;
			}

			update_post_meta( $order_id, '_pys_purchase_event_fired', true );

			$order = new \WC_Order( $order_id );

			$content_ids        = array();
			$content_names      = array();
			$content_categories = array();
			$tags               = array();
			$num_items          = 0;

			foreach ( $order->get_items( 'line_item' ) as $line_item ) {

                $product_id  = $this->get_woo_product_id( $line_item );
                $content_ids = array_merge( $content_ids, $this->get_woo_product_content_id( $product_id ) );

				$num_items += $line_item['qty'];

				// content_name, category_name, tags
				if ( $track_custom_audiences ) {

					$custom_audiences = get_woo_custom_audiences_optimization_params( $product_id );

					$content_names[]      = $custom_audiences['content_name'];
					$content_categories[] = $custom_audiences['category_name'];

					$cart_item_tags = get_woo_product_tags( $product_id );
					$tags = array_merge( $tags, $cart_item_tags );

				}

			}

            $params['content_ids']   = json_encode( $content_ids );
            $params['content_name']  = implode( ', ', $content_names );
            $params['category_name'] = implode( ', ', $content_categories );

			$tags           = array_unique( $tags );
			$tags           = array_slice( $tags, 0, 100 );
			$params['tags'] = implode( ', ', $tags );

			if ( $track_custom_audiences ) {
				$params['num_items'] = $num_items;
			}

			// currency, value
			if ( $this->get_option( 'woo_purchase_value_enabled' ) ) {

				if ( $customize_value ) {
					$include_shipping = $this->get_option( 'woo_shipping_option' ) == 'included' ? true : false;
					$amount = get_woo_order_total( $order, $include_tax, $include_shipping );
				} else {
					$amount = $order->get_total();
				}

				$value_option   = $this->get_option( 'woo_purchase_value_option' );
				$global_value   = $this->get_option( 'woo_purchase_value_global', 0 );
				$percents_value = $this->get_option( 'woo_purchase_value_percent', 100 );

				$params['value'] = get_woo_event_value( $value_option, $amount, $global_value, $percents_value );
				$params['currency'] = $currency;

			}

			$additional_matching_params = get_woo_purchase_additional_matching_params(
				$order,
				$this->get_option( 'woo_purchase_add_address' ),
				$this->get_option( 'woo_purchase_add_payment_method' ),
				$this->get_option( 'woo_purchase_add_shipping_method' ),
				$this->get_option( 'woo_purchase_add_coupons' )
			);

			$params = array_merge( $params, $additional_matching_params );

            // customer LTV
            if ( $this->get_option( 'woo_lifetime_value_enabled' ) ) {
                $params['lifetime_value'] = get_woo_customer_life_time_value( $order_id,
                    $this->get_option( 'woo_lifetime_value_order_statuses', array() ) );
            }

            $params['order_id'] = $order_id;

			$this->add_regular_event( 'Purchase', $params );

			return;

		}

	}

	public function add_edd_events() {
		global $post;

		if ( false == PixelYourSite\is_edd_active() || false == $this->get_option( 'edd_enabled' ) ) {
			return;
		}

		$currency               = edd_get_currency();
		$include_tax            = $this->get_option( 'edd_tax_option' ) == 'included' ? true : false;
		$track_custom_audiences = $this->get_option( 'edd_track_custom_audiences' );
		$customize_value        = $this->get_option( 'edd_event_value' ) == 'custom';

		$params = array(
			'content_type' => 'product'
		);

		// ViewContent Event
		if ( $this->get_option( 'edd_view_content_enabled' ) && is_singular( array( 'download' ) ) ) {

			$delay                 = floatval( $this->get_option( 'edd_view_content_delay', 0 ) );
			$params['content_ids'] = "['{$this->get_edd_product_content_id( $post->ID )}']";

			// content_name, category_name
			if ( $track_custom_audiences ) {
				$params['tags'] = get_edd_product_tags( $post->ID, true );
				$params = array_merge( $params, get_edd_custom_audiences_optimization_params( $post->ID ) );
			}

			// currency, value
			if ( $this->get_option( 'edd_view_content_value_enabled' ) ) {

				if( $customize_value ) {
					$amount = get_edd_product_price( $post->ID, $include_tax );
				} else {
					$amount = get_edd_product_price_to_display( $post->ID );
				}

				$value_option   = $this->get_option( 'edd_view_content_value_option' );
				$percents_value = $this->get_option( 'edd_view_content_value_percent', 100 );
				$global_value   = $this->get_option( 'edd_view_content_value_global', 0 );

				$params['value'] = get_edd_event_value( $value_option, $amount, $global_value, $percents_value );
				$params['currency'] = $currency;

			}

			$this->add_regular_event( 'ViewContent', $params, $delay );

			return;

		}

		/**
		 * AddToCart Event (button)
		 *
		 * @see add_edd_add_to_cart_button_attribute()
		 */

		// InitiateCheckout Event
		if ( $this->get_option( 'edd_initiate_checkout_enabled' ) && edd_is_checkout() ) {
			
			$content_ids        = array();
			$content_names      = array();
			$content_categories = array();
			$tags               = array();
			
			$num_items   = 0;
			$total       = 0;
			$total_as_is = 0;
			
			$licenses = array(
				'transaction_type'   => null,
				'license_site_limit' => null,
				'license_time_limit' => null,
				'license_version'    => null
			);
			
			foreach ( edd_get_cart_contents() as $cart_item_key => $cart_item ) {
				
				$download_id    = intval( $cart_item['id'] );
				$content_ids[] = $this->get_edd_product_content_id( $download_id );

				// content_name, category_name
				if ( $track_custom_audiences ) {

					$custom_audiences = get_edd_custom_audiences_optimization_params( $download_id );

					$content_names[]      = $custom_audiences['content_name'];
					$content_categories[] = $custom_audiences['category_name'];

					$tags = array_merge( $tags, get_edd_product_tags( $download_id ) );

				}

				$num_items += $cart_item['quantity'];
				
				// calculate cart items total
				if ( $this->get_option( 'edd_initiate_checkout_value_enabled' ) ) {

					$total += get_edd_product_price( $download_id, $include_tax, $cart_item['options'] ) * $cart_item['quantity'];
					$total_as_is += edd_get_cart_item_final_price( $cart_item_key );

				}
				
				// get download license data
				array_walk( $licenses, function( &$value, $key, $license ) {
					
					if ( ! isset( $license[ $key ] ) ) {
						return;
					}
					
					if ( $value ) {
						$value = $value . ', ' . $license[ $key ];
					} else {
						$value = $license[ $key ];
					}
					
				}, get_edd_product_license_data( $download_id ) );

			}

            $params['content_ids']   = "['" . implode( "','", $content_ids ) . "']";
            $params['content_name']  = implode( ', ', $content_names );
            $params['category_name'] = implode( ', ', $content_categories );

			if ( $track_custom_audiences ) {

				$tags           = array_slice( array_unique( $tags ), 0, 100 );
				$params['tags'] = implode( ', ', $tags );

				$params['num_items'] = $num_items;
			}

			// currency, value
			if ( $this->get_option( 'edd_initiate_checkout_value_enabled' ) ) {

				if( $customize_value ) {
					$amount = $total;
				} else {
					$amount = $total_as_is;
				}

				$value_option   = $this->get_option( 'edd_initiate_checkout_value_option' );
				$percents_value = $this->get_option( 'edd_initiate_checkout_value_percent', 100 );
				$global_value   = $this->get_option( 'edd_initiate_checkout_value_global', 0 );

				$params['value'] = get_edd_event_value( $value_option, $amount, $global_value, $percents_value );
				$params['currency'] = $currency;

			}

			$params = array_merge( $params, $licenses );
			$this->add_regular_event( 'InitiateCheckout', $params );

			return;

		}

		// Purchase Event
		if ( $this->get_option( 'edd_purchase_enabled' ) && edd_is_success_page() ) {
			global $edd_receipt_args;
			
			// skip payment confirmation page
			if ( isset( $_GET['payment-confirmation'] ) ) {
				return;
			}
			
			$session = edd_get_purchase_session();
			if ( isset( $_GET['payment_key'] ) ) {
				$payment_key = urldecode( $_GET['payment_key'] );
			} else if ( $session ) {
				$payment_key = $session['purchase_key'];
			} elseif ( $edd_receipt_args['payment_key'] ) {
				$payment_key = $edd_receipt_args['payment_key'];
			}
			
			if ( ! isset( $payment_key ) ) {
				return;
			}
			
			$payment_id    = (int) edd_get_purchase_id_by_key( $payment_key );
			$user_can_view = edd_can_view_receipt( $payment_key );
			
			if ( ! $user_can_view && ! empty( $payment_key ) && ! is_user_logged_in() && ! edd_is_guest_payment( $payment_id ) ) {
				return;
			}
			
			// skip if event was fired before
			if ( $this->get_option( 'edd_purchase_on_transaction' ) && get_post_meta( $payment_id, '_pys_purchase_event_fired', true ) ) {
				return;
			}
			
			update_post_meta( $payment_id, '_pys_purchase_event_fired', true );
			
			$meta   = edd_get_payment_meta( $payment_id );
			$cart   = edd_get_payment_meta_cart_details( $payment_id, true );
			$user   = edd_get_payment_meta_user_info( $payment_id );
			$status = edd_get_payment_status( $payment_id, true );
			
			## pending payment status used because we can't fire event on IPN
			if ( strtolower( $status ) != 'complete' && strtolower( $status ) != 'pending' ) {
				return;
			}
			
			$content_ids        = array();
			$content_names      = array();
			$content_categories = array();
			$tags               = array();
			
			$num_items   = 0;
			$total       = 0;
			$total_as_is = 0;
			
			$licenses = array(
				'transaction_type'   => null,
				'license_site_limit' => null,
				'license_time_limit' => null,
				'license_version'    => null
			);
			
			foreach ( $cart as $cart_item_key => $cart_item ) {
				
				$download_id   = intval( $cart_item['id'] );
				$content_ids[] = $this->get_edd_product_content_id( $download_id );
				
				// content_name, category_name
				if ( $track_custom_audiences ) {
					
					$custom_audiences = get_edd_custom_audiences_optimization_params( $download_id );
					
					$content_names[]      = $custom_audiences['content_name'];
					$content_categories[] = $custom_audiences['category_name'];

					$tags = array_merge( $tags, get_edd_product_tags( $download_id ) );

				}

				$num_items += $cart_item['quantity'];
				
				// calculate cart items total
				if ( $this->get_option( 'edd_initiate_checkout_value_enabled' ) ) {
					
					if ( $include_tax ) {
						$total += $cart_item['subtotal'] + $cart_item['tax'] - $cart_item['discount'];
					} else {
						$total += $cart_item['subtotal'] - $cart_item['discount'];
					}

					$total_as_is += $cart_item['price'];

				}
	
				// get download license data
				array_walk( $licenses, function( &$value, $key, $license ) {
					
					if ( ! isset( $license[ $key ] ) ) {
						return;
					}
					
					if ( $value ) {
						$value = $value . ', ' . $license[ $key ];
					} else {
						$value = $license[ $key ];
					}
					
				}, get_edd_product_license_data( $download_id ) );
				
			}

            $params['content_ids']   = "['" . implode( "','", $content_ids ) . "']";
            $params['content_name']  = implode( ', ', $content_names );
            $params['category_name'] = implode( ', ', $content_categories );

			if ( $track_custom_audiences ) {

				$tags           = array_slice( array_unique( $tags ), 0, 100 );
				$params['tags'] = implode( ', ', $tags );

				$params['num_items'] = $num_items;
			}
			
			// currency, value
			if ( $this->get_option( 'edd_purchase_value_enabled' ) ) {

				if ( $customize_value ) {
					$amount = $total;
				} else {
					$amount = $total_as_is;
				}

				$value_option   = $this->get_option( 'edd_purchase_value_option' );
				$percents_value = $this->get_option( 'edd_purchase_value_percent', 100 );
				$global_value   = $this->get_option( 'edd_purchase_value_global', 0 );

				$params['value'] = get_edd_event_value( $value_option, $amount, $global_value, $percents_value );
				$params['currency'] = $currency;
				
			}
			
			// town, state, country
			if ( $this->get_option( 'edd_purchase_add_address' ) && isset( $user['address'] ) ) {
				
				if ( ! empty( $user['address']['city'] ) ) {
					$params['town'] = $user['address']['city'];
				}
				
				if ( ! empty( $user['address']['state'] ) ) {
					$params['state'] = $user['address']['state'];
				}
				
				if ( ! empty( $user['address']['country'] ) ) {
					$params['country'] = $user['address']['country'];
				}
				
			}
			
			// payment method
			if ( $this->get_option( 'edd_purchase_add_payment_method' ) && isset( $session['gateway'] ) ) {
				$params['payment'] = $session['gateway'];
			}
			
			// coupons
			$coupons = isset( $user['discount'] ) && $user['discount'] != 'none' ? $user['discount'] : null;
			if ( $this->get_option( 'edd_purchase_add_coupons' ) && ! empty( $coupons ) ) {
				
				$params['coupon_used'] = 'yes';
				$params['coupon_name'] = $coupons;
				
			} elseif ( $this->get_option( 'edd_purchase_add_coupons' ) ) {
				
				$params['coupon_used'] = 'no';
				
			}
			
			// add transaction date
			$params['transaction_year']  = strftime( '%Y', strtotime( $meta['date'] ) );
			$params['transaction_month'] = strftime( '%m', strtotime( $meta['date'] ) );
			$params['transaction_day']   = strftime( '%d', strtotime( $meta['date'] ) );
			
			$params = array_merge( $params, $licenses );

            // customer LTV
            if ( $this->get_option( 'edd_lifetime_value_enabled' ) ) {
                $params['lifetime_value'] = get_edd_customer_life_time_value( $payment_id,
                    $this->get_option( 'edd_lifetime_value_order_statuses', array() ) );
            }

            $params['order_id'] = $payment_id;

			$this->add_regular_event( 'Purchase', $params );

			return;

		}

	}

	public function sanitize_event_params( $params ) {

		$sanitized = array();

		foreach ( $params as $name => $value ) {

			// skip empty (but not zero)
			if ( empty( $value ) && $value !== '0' ) {
				continue;
			}

			$key               = esc_js( $name );
			$sanitized[ $key ] = html_entity_decode( $value );

		}

		return $sanitized;

	}
	
	/**
	 * Add regular event to output. These events will be triggered on any page load.
	 *
	 * @param string $event_name Event name, eg. "PageView"
	 * @param array  $params     Optional. Associated array of event parameters in 'param_name' => 'param_value' format.
	 * @param int    $delay      Optional. If set, event will be fired with desired delay in seconds.
	 */
	public function add_regular_event( $event_name, $params = array(), $delay = 0 ) {
		
		$params = apply_filters( 'pys_fb_pixel_event_params', $params, $event_name );

		$this->regular_events[] = array(
			'type'   => is_facebook_standard_event( $event_name ) ? 'track' : 'trackCustom',
			'name'   => $event_name,
			'params' => $this->sanitize_event_params( $params ),
			'delay'  => $delay
		);
		
	}
	
	/**
	 * Add dynamic event to output. These events will be triggered only on front-end JS action.
	 *
	 * @param Event $event
	 * @param       $trigger_type
	 * @param       $trigger_value
	 */
	public function add_dynamic_event( $event, $trigger_type, $trigger_value ) {

		$params = apply_filters( 'pys_fb_pixel_event_params', $event->getFacebookEventParams(), $event->getFacebookEventType() );

		$this->dynamic_events[] = array(
			'event_id'      => $event->getId(),
			'trigger_type'  => $trigger_type,
			'trigger_value' => $trigger_value,
			'type'          => $event->isFacebookStandardEvent() ? 'track' : 'trackCustom',
			'name'          => $event->getFacebookEventType(),
			'params'        => $this->sanitize_event_params( $params )
		);

	}

	public function add_ajax_event( $event_id, $name, $params ) {

        $params = apply_filters( 'pys_fb_pixel_event_params', $params, $name );

		$this->ajax_events[ $event_id ] = array(
			'type' => is_facebook_standard_event( $name ) ? 'track' : 'trackCustom',
			'name' => $name,
			'params'  => $this->sanitize_event_params( $params ),
		);

	}
	
	/**
	 * @param Event  $event
	 * @param string $trigger_type
	 * @param string $trigger_value
	 */
	public function add_dynamic_custom_code_event( $event, $trigger_type, $trigger_value ) {

		$this->dynamic_custom_code_events[] = array(
			'event_id'      => $event->getId(),
			'trigger_type'  => $trigger_type,
			'trigger_value' => $trigger_value,
			'custom_code'   => $event->getFacebookCustomCode()
		);

	}

	public function output_regular_events() {

		// allow external plugins modify events
		$events = apply_filters( 'pys_fb_pixel_events_to_output', $this->regular_events, 'regular' );
		wp_localize_script( $this->slug, 'pys_fb_pixel_regular_events', $events );

	}

	public function output_dynamic_events() {

		$triggers      = array();
		$events_params = array();

		// allow external plugins modify events
		$dynamic_events = apply_filters( 'pys_fb_pixel_events_to_output', $this->dynamic_events, 'dynamic' );

		foreach ( $dynamic_events as $event ) {

			$event_id = $event['event_id'];

			$triggers[] = array(
				'event_id'      => $event_id,
				'trigger_type'  => $event['trigger_type'],
				'trigger_value' => $event['trigger_value']
			);

			$events_params[ $event_id ] = array(
				'type'   => $event['type'],
				'name'   => $event['name'],
				'params' => $event['params']
			);

		}

		foreach ( $this->dynamic_custom_code_events as $custom_code_event ) {

			$event_id = $custom_code_event['event_id'];

			$triggers[] = array(
				'event_id'      => $event_id,
				'trigger_type'  => $custom_code_event['trigger_type'],
				'trigger_value' => $custom_code_event['trigger_value']
			);

			$events_params[ $event_id ] = array(
				'custom_code'   => $custom_code_event['custom_code']
			);

		}

		wp_localize_script( $this->slug, 'pys_fb_pixel_dynamic_events', $events_params );
		wp_localize_script( $this->slug, 'pys_fb_pixel_dynamic_triggers', $triggers );

	}

	public function output_ajax_events() {

		// wp_localize_script() will not work there!

		?>

		<script type="text/javascript">
			/* <![CDATA[ */
			var pys_fb_pixel_ajax_events = <?php echo json_encode( $this->ajax_events ); ?>;
			/* ]]> */
		</script>

		<?php
	}
	
	public function output_custom_code_events() {
		wp_localize_script( $this->slug, 'pys_fb_pixel_custom_code_events', $this->custom_code_events );
	}

	public function get_common_additional_matching_params( $params ) {

		$user = wp_get_current_user();

		// something wrong
		if ( ! ( $user instanceof \WP_User ) ) {
			return $params;
		}

		// it is a guest
		if ( is_user_logged_in() == false ) {
			return $params;
		}

		// get user regular data
		$params['fn']    = $user->get( 'user_firstname' );
		$params['ln']    = $user->get( 'user_lastname' );
		$params['email'] = $user->get( 'user_email' );

		if ( ! PixelYourSite\is_woocommerce_active() ) {
			return $params;
		}

		// if first name is not set in regular wp user meta
		if ( empty( $params['fn'] ) ) {
			$params['fn'] = $user->get( 'billing_first_name' );
		}

		// if last name is not set in regular wp user meta
		if ( empty( $params['ln'] ) ) {
			$params['ln'] = $user->get( 'billing_last_name' );
		}

		$params['phone']   = $user->get( 'billing_phone' );
		$params['ct']      = $user->get( 'billing_city' );
		$params['st']      = $user->get( 'billing_state' );
		$params['zip']     = $user->get( 'billing_postcode' );
		$params['country'] = $user->get( 'billing_country' );

		return $params;

	}

	public function get_purchase_additional_matching_params( $params ) {

		// add extra params only on thank you page and if option enabled
		if ( ! PixelYourSite\is_woocommerce_active() || ! is_order_received_page() || ! isset( $_REQUEST['key'] ) ) {
			return $params;
		}

		$order_id = wc_get_order_id_by_order_key( $_REQUEST['key'] );
		$order    = wc_get_order( $order_id );

		if ( ! $order ) {
			return $params;
		}
		
		$additional_params = get_woo_additional_matching_params( $order );

		return array_merge( $params, $additional_params );

	}

	public function add_event_domain_param( $params ) {

		// get home URL without protocol
		$params['domain'] = substr( get_home_url( null, '', 'http' ), 7 );

		return $params;

	}

	public function process_admin_actions() {

		// nothing to do
		if( empty( $_REQUEST['page'] ) || $_REQUEST['page'] !== 'fb_pixel_pro' || empty( $_REQUEST['_wpnonce'] ) ) {
			return;
		}

		// create new event
		if ( isset( $_POST['create_event'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'create_event' ) ) {

			$event_data = isset( $_POST[ $this->slug ]['event'] ) ? $_POST[ $this->slug ]['event'] : array();
			EventsFactory::create( $event_data );

			// redirect to events tab
			wp_safe_redirect( admin_url( 'admin.php?page=fb_pixel_pro&tab=events' ) );
			exit;

		}

		// update existing event
		if ( isset( $_POST['update_event'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'update_event' ) ) {

			$event_data = isset( $_POST[ $this->slug ]['event'] ) ? $_POST[ $this->slug ]['event'] : array();

			if ( ! empty( $event_data['id'] ) && $event = EventsFactory::get_by_id( $event_data['id'] ) ) {
				$event->update( $event_data );
			}

			// redirect to events tab
			wp_safe_redirect( admin_url( 'admin.php?page=fb_pixel_pro&tab=events' ) );
			exit;

		}

		// pause/active a custom event
		if ( isset( $_REQUEST['toggle_event_state'] ) && isset( $_REQUEST['event_id'] ) ) {

			if ( wp_verify_nonce( $_REQUEST['_wpnonce'], 'toggle_event_state_' . $_REQUEST['event_id'] ) ) {
				EventsFactory::toggle_state( $_REQUEST['event_id'] );
			}

			// redirect to events tab
			wp_safe_redirect( admin_url( 'admin.php?page=fb_pixel_pro&tab=events' ) );
			exit;

		}

		// clone a custom event
		if ( isset( $_REQUEST['clone_event'] ) && isset( $_REQUEST['event_id'] ) ) {

			if ( wp_verify_nonce( $_REQUEST['_wpnonce'], 'clone_event_' . $_REQUEST['event_id'] ) ) {
				EventsFactory::clone_event( $_REQUEST['event_id'] );
			}

			// redirect to events tab
			wp_safe_redirect( admin_url( 'admin.php?page=fb_pixel_pro&tab=events' ) );
			exit;

		}

		// delete a custom event
		if ( isset( $_REQUEST['delete_event'] ) && isset( $_REQUEST['event_id'] ) ) {

			if ( wp_verify_nonce( $_REQUEST['_wpnonce'], 'delete_event_' . $_REQUEST['event_id'] ) ) {

				EventsFactory::remove( $_REQUEST['event_id'] );

				wp_safe_redirect( admin_url( 'admin.php?page=fb_pixel_pro&tab=events' ) );
				exit;

			}

		}
		
		// bulk delete events
		if ( isset( $_REQUEST['bulk_delete_events'] ) && isset( $_REQUEST['selected_events'] ) && is_array( $_REQUEST['selected_events'] ) ) {

			if( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'pys_save_settings' ) ) {
				return;
			}

			foreach ( $_REQUEST['selected_events'] as $event_id ) {
				EventsFactory::remove( $event_id );
			}

		}

	}

	public function get_woo_product_content_id( $product_id ) {

		$content_id_format = $this->get_option( 'woo_content_id_format', 'default' );

		if ( $this->get_option( 'woo_content_id' ) == 'product_sku' ) {
			$content_id = get_post_meta( $product_id, '_sku', true );
		} else {
			$content_id = $product_id;
		}

		$prefix = $this->get_option( 'woo_content_id_prefix' );
		$suffix = $this->get_option( 'woo_content_id_suffix' );

		$value = $prefix . $content_id . $suffix;

        return apply_filters( 'pys_fb_pixel_woo_product_content_id', array( $value ), $product_id, $content_id, $suffix,
            $prefix, $content_id_format );

	}

	public function get_edd_product_content_id( $download_id ) {

		if ( $this->get_option( 'edd_content_id' ) == 'download_sku' ) {
			$content_id = get_post_meta( $download_id, 'edd_sku', true );
		} else {
			$content_id = $download_id;
		}

		$prefix = $this->get_option( 'edd_content_id_prefix' );
		$suffix = $this->get_option( 'edd_content_id_suffix' );

		return $prefix . $content_id . $suffix;

	}

	public function get_woo_product_id( $product ) {

		if ( $this->get_option( 'woo_product_data' ) != 'main' && isset( $product['variation_id'] ) && $product['variation_id'] !== 0 ) {
            return $product['variation_id'];
		} else {
            return $product['product_id'];
        }

	}

	public function get_woo_product_add_to_cart_params( $product_id ) {

		$params                 = array();
		$params['content_type'] = 'product';
		$params['content_ids']  = json_encode( $this->get_woo_product_content_id( $product_id ) );

		// content_name, category_name, tags
		if ( $this->get_option( 'woo_track_custom_audiences' ) ) {
			$params['tags'] = get_woo_product_tags( $product_id, true );
			$params = array_merge( $params, get_woo_custom_audiences_optimization_params( $product_id ) );
		}

		// currency, value
		if ( $this->get_option( 'woo_add_to_cart_value_enabled' ) ) {

			$customize_value = $this->get_option( 'woo_event_value' ) == 'custom';

			if ( $customize_value ) {
				$include_tax = $this->get_option( 'woo_tax_option' ) == 'included' ? true : false;
				$amount      = get_woo_product_price( $product_id, $include_tax );
			} else {
				$amount = get_woo_product_price_to_display( $product_id );
			}

			$value_option   = $this->get_option( 'woo_add_to_cart_value_option' );
			$global_value   = $this->get_option( 'woo_add_to_cart_value_global', 0 );
			$percents_value = $this->get_option( 'woo_add_to_cart_value_percent', 100 );

			$params['value']    = get_woo_event_value( $value_option, $amount, $global_value, $percents_value );
			$params['currency'] = get_woocommerce_currency();

		}

		return $params;

	}

	/**
	 * Adds data-pixelcode attribute to "add to cart" buttons in the WooCommerce loop.
	 *
	 * @param string     $tag
	 * @param \WC_Product $product
	 *
	 * @return string
	 */
	public function add_woo_add_to_cart_button_attribute( $tag, $product ) {

		if ( is_woo_version_gte( '2.7' ) ) {
			$is_simple_product   = $product->is_type( 'simple' );
			$is_external_product = $product->is_type( 'external' );
		} else {
			$is_simple_product   = $product->product_type == 'simple';
			$is_external_product = $product->product_type == 'external';
		}

		if ( false == $is_simple_product && false == $is_external_product ) {
			return $tag;
		}

		$event_id = uniqid();

		if ( is_woo_version_gte( '2.6' ) ) {
			$product_id = $product->get_id();
		} else {
			$product_id = $product->post->ID;
		}

		$params                 = array();
		$params['content_type'] = 'product';
		$params['content_ids']  = json_encode( $this->get_woo_product_content_id( $product_id ) );

		// content_name, category_name, tags
		if ( $this->get_option( 'woo_track_custom_audiences' ) ) {
			$params['tags'] = get_woo_product_tags( $product_id, true );
			$params = array_merge( $params, get_woo_custom_audiences_optimization_params( $product_id ) );
		}

		if ( $is_simple_product && $product->is_purchasable() ) {

			// do not add code if AJAX is disabled. Event will be hooked usual way
			if ( 'yes' !== get_option( 'woocommerce_enable_ajax_add_to_cart' ) ) {
				return $tag;
			}

			$tag = PixelYourSite\insert_tag_attribute( 'data-pys-ajax-event-id', $event_id, $tag, true, 'any' );

			// currency, value
			if ( $this->get_option( 'woo_add_to_cart_value_enabled' ) ) {

				$customize_value = $this->get_option( 'woo_event_value' ) == 'custom';

				if ( $customize_value ) {
					$include_tax = $this->get_option( 'woo_tax_option' ) == 'included' ? true : false;
					$amount = get_woo_product_price( $product_id, $include_tax );
				} else {
					$amount = get_woo_product_price_to_display( $product_id );
				}

				$value_option   = $this->get_option( 'woo_add_to_cart_value_option' );
				$global_value   = $this->get_option( 'woo_add_to_cart_value_global', 0 );
				$percents_value = $this->get_option( 'woo_add_to_cart_value_percent', 100 );

				$params['value'] = get_woo_event_value( $value_option, $amount, $global_value, $percents_value );
				$params['currency'] = get_woocommerce_currency();

			}

			$this->add_ajax_event( $event_id, 'AddToCart', $params );

		}

		if ( $is_external_product ) {

			$tag = PixelYourSite\insert_tag_attribute( 'data-pys-ajax-event-id', $event_id, $tag, true, 'any' );

			// currency, value
			if ( $this->get_option( 'woo_affiliate_value_enabled' ) ) {

				$customize_value = $this->get_option( 'woo_event_value' ) == 'custom';

				if ( $customize_value ) {
					$include_tax = $this->get_option( 'woo_tax_option' ) == 'included' ? true : false;
					$amount = get_woo_product_price( $product_id, $include_tax );
				} else {
					$amount = get_woo_product_price_to_display( $product_id );
				}

				$value_option = $this->get_option( 'woo_affiliate_value_option' );
				$global_value = $this->get_option( 'woo_affiliate_value_global', 0 );

				$params['value'] = get_woo_event_value( $value_option, $amount, $global_value, 0 );
				$params['currency'] = get_woocommerce_currency();

			}

			$event_name = $this->get_option( 'woo_affiliate_event_type' );

			if( $event_name == 'custom' ) {
				$event_name = $this->get_option( 'woo_affiliate_custom_event_type' );
			}

			$this->add_ajax_event( $event_id, $event_name, $params );

		}

		return $tag;

	}

	public function add_woo_paypal_event() {

		// add event only on Checkout page
		if( false == is_checkout() || true == is_wc_endpoint_url() ) {
			return;
		}

		if ( false == WC()->cart->needs_payment() ) {
			return;
		}

		$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();

		if( false == array_key_exists( 'paypal', $available_gateways ) ) {
			return;
		}

		$track_custom_audiences = $this->get_option( 'woo_track_custom_audiences' );

		$params = array(
			'content_type' => 'product'
		);

		$content_ids        = array();
		$content_names      = array();
		$content_categories = array();
		$tags               = array();

		foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {

			$product_id    = $this->get_woo_product_id( $cart_item );
			$content_ids = array_merge( $content_ids, $this->get_woo_product_content_id( $product_id ) );

			// content_name, category_name
			if ( $track_custom_audiences ) {

				$custom_audiences = get_woo_custom_audiences_optimization_params( $product_id );

				$content_names[]      = $custom_audiences['content_name'];
				$content_categories[] = $custom_audiences['category_name'];

				$cart_item_tags = get_woo_product_tags( $product_id );
				$tags = array_merge( $tags, $cart_item_tags );

			}

		}

        $params['content_ids']   = json_encode( $content_ids );
        $params['content_name']  = implode( ', ', $content_names );
        $params['category_name'] = implode( ', ', $content_categories );

		$tags           = array_unique( $tags );
		$tags           = array_slice( $tags, 0, 100 );
		$params['tags'] = implode( ', ', $tags );

		if ( $track_custom_audiences ) {
			$params['num_items'] = WC()->cart->get_cart_contents_count();
		}

		// currency, value
		if ( $this->get_option( 'woo_paypal_value_enabled' ) ) {

			$customize_value = $this->get_option( 'woo_event_value' ) == 'custom';

			if ( $customize_value ) {
				$include_tax = $this->get_option( 'woo_tax_option' ) == 'included' ? true : false;
				$amount = get_woo_cart_total( $include_tax );
			} else {
				$amount = WC()->cart->subtotal;
			}

			$value_option = $this->get_option( 'woo_paypal_value_option' );
			$global_value = $this->get_option( 'woo_paypal_value_global', 0 );

			$params['value'] = get_woo_event_value( $value_option, $amount, $global_value, 0 );
			$params['currency'] = get_woocommerce_currency();

		}

		$event_name = $this->get_option( 'woo_paypal_event_type' );

		if ( $event_name == 'custom' ) {
			$event_name = $this->get_option( 'woo_paypal_custom_event_type' );
		}

		$event_id = uniqid();
		$this->add_ajax_event( $event_id, $event_name, $params );
		
		wp_localize_script( $this->slug, 'pys_fb_pixel_woo_paypal_event_id', $event_id );

	}
	
	public function add_edd_add_to_cart_button_attribute( $args = array() ) {
		
		$download_id = $args['download_id'];
		
		$params = array(
			'content_type' => 'product'
		);

		$track_custom_audiences = $this->get_option( 'edd_track_custom_audiences' );
		$customize_value        = $this->get_option( 'edd_event_value' ) == 'custom';
		
		$params['content_ids'] = "['{$this->get_edd_product_content_id( $download_id )}']";
		
		// content_name, category_name
		if ( $track_custom_audiences ) {
			$params['tags'] = get_edd_product_tags( $download_id, true );
			$params = array_merge( $params, get_edd_custom_audiences_optimization_params( $download_id ) );
		}
		
		// currency, value
		if ( $this->get_option( 'edd_add_to_cart_value_enabled' ) ) {

			if ( $customize_value ) {
				$include_tax = $this->get_option( 'edd_tax_option' ) == 'included' ? true : false;
				$amount = get_edd_product_price( $download_id, $include_tax );
			} else {
				$amount = get_edd_product_price_to_display( $download_id );
			}

			$value_option   = $this->get_option( 'edd_add_to_cart_value_option' );
			$percents_value = $this->get_option( 'edd_add_to_cart_value_percent', 100 );
			$global_value   = $this->get_option( 'edd_add_to_cart_value_global', 0 );

			$params['value'] = get_edd_event_value( $value_option, $amount, $global_value, $percents_value );
			$params['currency'] = edd_get_currency();
			
		}

		$license = get_edd_product_license_data( $download_id );
		$params  = array_merge( $params, $license );
		
		$event_id = uniqid();
		$this->add_ajax_event( $event_id, 'AddToCart', $params );
		
		$classes       = isset( $args['class'] ) ? $args['class'] : null;
		$args['class'] = $classes . " pys-event-id-{$event_id}";
		
		return $args;
		
	}

	/**
	 * Adds 'pys-event-id' data attribute to HTML tags on content and widgets in case if href attribute match to an
	 * custom event "url_click" trigger condition.
	 *
	 * @param $content
	 *
	 * @return mixed
	 */
	public function filter_content_urls( $content ) {

		/**
		 * @todo: it is not possible to attach multiple events to same ID as of it data attribute will overwritten (test)
		 */

		$url_triggers = array();

		/**
		 * Collect all click on URL triggers.
		 * Dynamic events are already filtered by status and current page URL.
		 */
		foreach ( $this->dynamic_events as $event ) {
			/** @var Event $event */

			if( $event['trigger_type'] !== 'url_click' ) {
				continue;
			}

			$url_triggers[] = array(
				'event_id' => $event['event_id'],
				'url'      => $event['trigger_value']
			);

		}

		if ( empty( $url_triggers ) ) {
			return $content;
		}

		// don't do a thing if there's no anchor at all
		if ( false === stripos( $content, '<a ' ) ) {
			return $content;
		}

		$old_content = array();
		$new_content = array();

		// find all occurrences of anchors and fill matches with links
		preg_match_all( '#(<a\s[^>]+?>).*?</a>#iu', $content, $tags, PREG_SET_ORDER );

		foreach ( $tags as $tag ) {

			// get a href attribute value
			$href = preg_replace( '/^.*href="([^"]*)".*$/iu', '$1', $tag[0] );

			// not found or not set
			if ( ! isset( $href ) || empty( $href ) ) {
				continue;
			}

			foreach ( $url_triggers as $trigger ) {

				// filter content URL
				if ( ! PixelYourSite\urls_compare( $trigger['url'], $href ) ) {
					continue;
				}

				// add dynamic event ID to element attributes
				$new_tag = PixelYourSite\insert_tag_attribute( 'data-pys-event-id', $trigger['event_id'], $tag[0], true );

				// add new tag to replacement list
				$old_content[] = $tag[0];
				$new_content[] = $new_tag;

			}

		}

		// replace content
		if ( ! empty( $old_content ) && ! empty( $new_content ) ) {
			$content = str_replace( $old_content, $new_content, $content );
		}

		return $content;

	}

	public function render_help_widget() {
		include 'views/html-admin-sidebar-help.php';
	}
	
	public function render_addon_controls() {
		include 'views/html-admin-addon-controls.php';
	}

	public function update_plugin() {

		if ( ! class_exists( 'FacebookPixelPro\Plugin_Updater' ) ) {
			require_once 'plugin-updater.php';
		}

		$license_key = $this->get_option( 'license_key' );

		new Plugin_Updater( PYS_FB_PIXEL_STORE_URL, PYS_FB_PIXEL_PLUGIN_FILE, array(
				'version'   => $this->version,
				'license'   => $license_key,
				'item_name' => PYS_FB_PIXEL_ITEM_NAME,
				'author'    => 'PixelYourSite'
			)
		);

	}

    public function render_dashboard_license_expiration_notices( $slug ) {

        if( $slug !== $this->get_slug() ) {
            return;
        }

        $license_expires = $this->get_option( 'license_expires', null );
        $license_key = $this->get_option( 'license_key' );
        $license_expires_soon = false;
        $license_expired = false;

        if ( $license_expires ) {

            $now = time();

            if ( $now >= $license_expires ) {
                $license_expired = true;
            } elseif ( $now >= ( $license_expires - 30 * DAY_IN_SECONDS ) ) {
                $license_expires_soon = true;
            }

        }

        if ( $license_expires_soon ) : ?>

            <div class="alert alert-pys-notice alert-no-border-radius">
                <p>Your license key <strong>expires on <?php echo date( get_option( 'date_format' ), $license_expires ); ?></strong>. Make
                    sure you keep everything updated and in order.</p>
                <p>
                    <a href="http://www.pixelyoursite.com/checkout/?edd_license_key=<?php esc_attr_e( $license_key ); ?>&utm_campaign=admin&utm_source=licenses&utm_medium=renew"
                       target="_blank"><strong>Click here to renew your license now for a 40% discount</strong></a>
                </p>
            </div>

        <?php elseif ( $license_expired ) : ?>

            <div class="alert alert-pys-red alert-no-border-radius">
                <p><strong>Your license key is expired</strong>, so you no longer get any updates. Don't miss our last improvements and
                    make sure that everything works smoothly.</p>
                <p><a href="http://www.pixelyoursite.com/checkout/?edd_license_key=<?php esc_attr_e( $license_key ); ?>&utm_campaign=admin&utm_source=licenses&utm_medium=renew"
                       target="_blank"><strong>Click here to renew your license now</strong></a></p>
            </div>

        <?php endif;
    }
    
    /**
     * @return array
     */
    public function getRegularEvents() {
        return $this->regular_events;
    }
    
    /**
     * @return string
     */
    public function getPixelID() {
        return $this->get_option( 'pixel_id' );
    }
    
}