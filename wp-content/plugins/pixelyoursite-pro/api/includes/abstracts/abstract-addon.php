<?php

namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

abstract class AbstractAddon extends Settings_API {

	protected $slug = '';
	
	protected $name = '';
	
	protected $description = '';

	protected $visible = true;

	protected $menu_items = array();
	
	public function __construct( $slug, $name, $description = '', $visible = true ) {
		
		$this->slug = $slug;
		$this->name = $name;
		$this->description = $description;
		$this->visible = $visible;
		
		// initialize addon settings
		parent::__construct( $this->slug );

	}

	/**
	 * Enabled addon entry point.
	 */
	public abstract function initialize();

	final public function get_slug() {
		return $this->slug;
	}

	final public function get_name() {
		return $this->name;
	}

	final public function get_description() {
		return $this->description;
	}

	final public function get_menu_items() {
		return $this->menu_items;
	}

	public function is_enabled() {
		return array_key_exists( $this->slug, PYS()->get_enabled_addons() );
	}
	
	public function is_active() {
		return $this->is_enabled();
	}

	public function is_visible() {
		return $this->visible;
	}

	public function admin_page_url() {

		return add_query_arg( array(
			'page' => $this->slug
		), admin_url( 'admin.php' ) );

	}

	public function dashboard_button_text() {
		return 'Open';
	}

	public function register_menu_items( $items ) {

		foreach ( $this->menu_items as $menu_item ) {

			$page_slug = $menu_item['menu_slug'];

			$items[] = array(
				'page_title' => $menu_item['page_title'],
				'menu_title' => $menu_item['menu_title'],
				'menu_slug'  => $page_slug
			);

			// register admin page
			add_action( "pys_admin_{$page_slug}_page_content", array( $this, $menu_item['callback'] ), 10, 1 );

		}

		return $items;

	}

}