<?php

namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( ! class_exists( 'PixelYourSite\PixelYourSite' ) ) :
	
	define( 'PYS_API_VERSION', '1.1.0' );
	define( 'PYS_API_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
	define( 'PYS_API_URL', plugin_dir_url( __FILE__ ) );

	require_once 'includes/abstracts/abstract-settings-api.php';

	/**
	 * PixelYourSite Core Class.
	 */
	final class PixelYourSite extends Settings_API {

		private static $_instance = null;

		/** @var PYS_Admin $admin */
		public $admin;

		private $addons = array();

		/**
		 * API singleton instance.
		 *
		 * Ensures only one instance of is loaded and initialized.
		 *
		 * @return PixelYourSite
		 */
		public static function instance() {

			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;

		}

		/**
		 * Cloning is forbidden.
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'pys' ), '6.0.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'pys' ), '6.0.0' );
		}

		public function __construct() {

			// initialize settings API
			parent::__construct( 'core' );

			$this->includes_core();
			$this->includes_admin();

			add_action( 'init', array( $this, 'initialize' ), 11 );

		}

		private function includes_core() {

			require_once 'includes/functions-helpers.php';
			require_once 'includes/abstracts/abstract-settings-api.php';
			require_once 'includes/abstracts/abstract-addon.php';

		}

		private function includes_admin() {

			if ( false == is_admin() ) {
				return;
			}

			require_once 'includes/admin/class-admin.php';
			
			$this->admin = new PYS_Admin();

		}

		public function initialize() {

			load_plugin_textdomain( 'pys', false, basename( dirname( __FILE__ ) ) . '/languages/' );

			// register addons
			$this->addons = apply_filters( 'pys_registered_addons', array() );

			$this->setting_defaults = array(
				'plugin_version' => PYS_API_VERSION
			);

			// add "{$slug}_enabled" setting for each addon and enable them addon by default
			foreach ( $this->addons as $addon ) {
				/** @var AbstractAddon $addon */
				$this->form_fields['addons'][ $addon->get_slug() . '_enabled' ] = 'checkbox';
				$this->setting_defaults[ $addon->get_slug() . '_enabled' ] = true;
			}

			$this->initialize_addons();

		}

		public function initialize_addons() {
			
			foreach ( $this->get_enabled_addons() as $addon ) {
				/** @var AbstractAddon $addon */
				$addon->initialize();
			}
            
		}
		
		public function get_enabled_addons() {
			return $this->get_addons( true );
		}
		
		public function get_registered_addons() {
			return $this->get_addons( false );
		}
		
		/**
		 * Return array of addons by status.
		 *
		 * @param bool $enabled_only
		 * 
		 * @return array PYS_Abstract_Addon instances array.
		 */
		private function get_addons( $enabled_only ) {

			$addons = $this->addons;

			if( $enabled_only ) {
				
				foreach ( $addons as $key => $addon ) {
					/** @var AbstractAddon $addon */

					$enabled = $this->get_option( $addon->get_slug() . '_enabled', true );

					if ( ! $enabled ) {
						unset( $addons[ $key ] );
					}

				}

			}

			return $addons;

		}
		
	}

endif;

if( ! function_exists( 'PixelYourSite\PYS' ) ) :

	function PYS() {
		return PixelYourSite::instance();
	}

endif;