<?php

namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class PYS_Admin {
	
	private $current_screen = '';
	private $current_tab = '';
	private $current_action = '';

	private $page_hooks = array();

	public function __construct() {

		//@todo: remove 'pys_' prefix only form the beginning
		$this->current_screen = empty( $_GET['page'] ) ? 'dashboard' : str_replace( 'pys_', '', $_GET['page'] );
		$this->current_tab    = empty( $_GET['tab'] ) ? '' : $_GET['tab'];
		$this->current_action = empty( $_GET['action'] ) ? '' : $_GET['action'];

        require_once dirname( __FILE__ ) . '/functions-system-report.php';
        require_once dirname( __FILE__ ) . '/functions-ui-helpers.php';

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		
		// register built-in admin pages
		add_action( 'pys_admin_dashboard_page_content', array( $this, 'dashboard_page' ) );
		add_action( 'pys_admin_settings_page_content', array( $this, 'settings_page' ) );
		add_action( 'pys_admin_addons_page_content', array( $this, 'addons_page' ) );
        add_action( 'pys_admin_system_report_page_content', array( $this, 'system_report_page' ) );
		
		add_action( 'pys_admin_sidebar_content', array( $this, 'render_logo_sidebar' ) );

		add_action( 'pys_save_addons', array( $this, 'update_addons_settings' ) );

	}
	
	public function admin_menu() {
		global $submenu;
		
		if ( false == current_user_can( 'manage_options' ) ) {
			return;
		}
		
		$this->page_hooks[] = add_menu_page( 'Dashboard', 'PixelYourSite', 'manage_options', 'pys_dashboard', array( $this, 'admin_page' ), PYS_API_URL . 'dist/images/favicon.png' );
		
		/**
		 * Allow plugins to add own menu items.
		 *
		 * @param array $menu_items {
		 *                          Array of menu items parameters.
		 *
		 * @type string $page_title The text to be displayed in the title tags of the page when the menu is selected.
		 * @type string $menu_title The text to be used for the menu.
		 * @type string $menu_slug  The slug name to refer to this menu by (should be unique for this menu).
		 * }
		 *
		 */
		$submenu_items = apply_filters( 'pys_admin_submenu_items', array() );
		
		foreach ( $submenu_items as $submenu_item ) {
			
			$this->page_hooks[] = add_submenu_page( 'pys_dashboard', $submenu_item['page_title'], $submenu_item['menu_title'],
				'manage_options', $submenu_item['menu_slug'], array( $this, 'admin_page' ) );
			
		}
		
		$this->page_hooks[] = add_submenu_page( 'pys_dashboard', 'Settings', 'Settings',
			'manage_options', 'pys_settings', array( $this, 'admin_page' ) );

		$this->page_hooks[] = add_submenu_page( 'pys_dashboard', 'Licenses & Add-ons', 'Licenses & Add-ons',
			'manage_options', 'pys_addons', array( $this, 'admin_page' ) );

        $this->page_hooks[] = add_submenu_page( 'pys_dashboard', 'System Report', 'System Report',
            'manage_options', 'pys_system_report', array( $this, 'admin_page' ) );
		
//		$this->page_hooks[] = add_submenu_page( 'pys_dashboard', 'Developers', 'Developers',
//			'manage_options', 'pys_developers', array( $this, 'admin_page' ) );
		
		// Rename first submenu item
		if ( isset( $submenu['pys_dashboard'] ) ) {
			$submenu['pys_dashboard'][0][0] = 'Dashboard';
		}
		
	}
	
	public function admin_page() {

		// save forms data if data has been posted
		if ( current_user_can( 'manage_options' ) && isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'pys_save_settings' ) ) {

			$hook = $this->current_screen;
			$hook .= ! empty( $this->current_tab ) ? '_' . $this->current_tab : '';
			$hook .= ! empty( $this->current_action ) ? '_' . $this->current_action : '';

			do_action( 'pys_save_' . $hook, $_POST );

		}
		
		wp_enqueue_style( 'fontawesome', '//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css' );
		wp_enqueue_style( 'pys-fonts', 'https://fonts.googleapis.com/css?family=Montserrat:300,400,600|Open+Sans:400,600' );    //@todo: clean
		
		wp_enqueue_style( 'pys', PYS_API_URL . 'dist/styles/admin.css', array(), PYS_API_VERSION );
		wp_enqueue_script( 'pys', PYS_API_URL . 'dist/scripts/admin.js', array( 'jquery' ), PYS_API_VERSION );

		include dirname( __FILE__ ) . '/views/html-wrapper.php';
		
	}
	
	public function get_current_screen() {
		return $this->current_screen;
	}

	public function get_current_tab() {
		return $this->current_tab;
	}

	public function get_current_action() {
		return $this->current_action;
	}

	public function dashboard_page() {
		include 'views/html-dashboard.php';
	}
	
	public function render_logo_sidebar() {
		include dirname( __FILE__ ) . '/views/html-sidebar-global.php';
	}
	
	public function settings_page() {
		include dirname( __FILE__ ) . '/views/html-settings.php';
	}

	public function addons_page() {
		include dirname( __FILE__ ) . '/views/html-addons.php';
	}
    
    public function system_report_page() {
        include dirname( __FILE__ ) . '/views/html-system-report.php';
    }

	public function update_addons_settings() {

		PYS()->update_options( 'addons' );

		//@fixme: it stop hooks propagation
//		wp_safe_redirect( admin_url( 'admin.php?page=pys_addons' ) );
//		exit;

	}
	
	/**
	 * Return registered admin pages hooks.
	 * 
	 * @return array
	 */
	public function get_page_hooks() {
		return $this->page_hooks;
	}

}
