<?php

namespace PixelYourSite\HeadFooter;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/** @noinspection PhpIncludeInspection */
require_once PYS_API_PATH . '/includes/abstracts/abstract-addon.php';

use PixelYourSite;

class Addon extends PixelYourSite\AbstractAddon {
    
    private $is_mobile;
    
    private $replacements = array();
    
    public function __construct() {
        parent::__construct( 'head_footer', 'Head & Footer Scripts', '<ol><li>Insert global scripts in the 
        head/footer of all your site pages.</li><li>Insert per page scripts on each page by editing it.</li></ol>',
            true );
    }
    
    public function initialize() {
        
        $this->initialize_settings();

        /**
         * Do not show addon admin page and do not process business logic
         * if main plugin license was never activated before.
         */

        $license_status = $this->get_main_addon_license_status();

        if ( empty( $license_status ) ) {
            return;
        }
        
        $this->menu_items[] = array(
            'page_title' => 'Head & Footer Scripts',
            'menu_title' => 'Head & Footer',
            'menu_slug'  => $this->slug,
            'callback'   => 'admin_page_callback',
        );
        
        add_filter( 'pys_admin_submenu_items', array( $this, 'register_menu_items' ), 10, 1 );
        add_action( 'pys_save_head_footer', array( $this, 'update_global_options' ) );
        
        add_action( 'add_meta_boxes', array( $this, 'register_meta_box' ) );
        add_action( 'save_post', array( $this, 'save_meta_box' ) );
        
        add_action( 'template_redirect', array( $this, 'output_scripts' ) );
        
    }
    
    private function initialize_settings() {

        // set options defaults
        $setting_defaults = array(

            // Global
            'head_any'                          => '',
            'head_desktop'                      => '',
            'head_mobile'                       => '',
            'footer_any'                        => '',
            'footer_desktop'                    => '',
            'footer_mobile'                     => '',
            'woo_order_received_disable_global' => false,
            'woo_order_received_head_any'       => '',
            'woo_order_received_head_desktop'   => '',
            'woo_order_received_head_mobile'    => '',
            'woo_order_received_footer_any'     => '',
            'woo_order_received_footer_desktop' => '',
            'woo_order_received_footer_mobile'  => '',

        );

        $this->setting_defaults = $setting_defaults;

        // set options validation type
        $settings_form_fields = array(

            'global' => array(
                'head_any'                          => 'textarea',
                'head_desktop'                      => 'textarea',
                'head_mobile'                       => 'textarea',
                'footer_any'                        => 'textarea',
                'footer_desktop'                    => 'textarea',
                'footer_mobile'                     => 'textarea',
                'woo_order_received_disable_global' => 'checkbox',
                'woo_order_received_head_any'       => 'textarea',
                'woo_order_received_head_desktop'   => 'textarea',
                'woo_order_received_head_mobile'    => 'textarea',
                'woo_order_received_footer_any'     => 'textarea',
                'woo_order_received_footer_desktop' => 'textarea',
                'woo_order_received_footer_mobile'  => 'textarea',
            ),

        );
        
        $this->form_fields = $settings_form_fields;
        
    }

    /**
     * Return main plugin license status.
     *
     * @return string|null
     */
    private function get_main_addon_license_status() {

        $registered_addons = PixelYourSite\PYS()->get_registered_addons();

        if ( ! array_key_exists( 'fb_pixel_pro', $registered_addons ) ) {
            return null;
        }

        /** @var PixelYourSite\FacebookPixelPro\Addon $pixel_addon */
        $pixel_addon = $registered_addons['fb_pixel_pro'];

        return $pixel_addon->get_option( 'license_status' );

    }

    public function is_active() {
        return parent::is_active() && $this->get_main_addon_license_status() == 'valid';
    }

    public function dashboard_button_text() {

        if ( $this->get_main_addon_license_status() == 'valid' ) {
            return parent::dashboard_button_text();
        } else {
            return 'Activate License';
        }

    }

    public function admin_page_url() {

        if ( $this->get_main_addon_license_status() == 'valid' ) {

            return parent::admin_page_url();

        } else {

            return add_query_arg( array(
                'page' => 'pys_addons#fb_pixel_pro',
            ), admin_url( 'admin.php' ) );

        }

    }
    
    public function update_global_options() {
        $this->update_options( 'global' );
    }
    
    public function admin_page_callback( $admin ) {
        include 'views/html-admin-page.php';
    }
    
    /**
     * Register meta box for each public post type.
     */
    public function register_meta_box() {
        
        $screens = get_post_types( array( 'public' => true ) );
        
        foreach ( $screens as $screen ) {
            add_meta_box( 'pys-head-footer', 'PixelYourSite Head & Footer Scripts', array( $this, 'render_meta_box' ),
                $screen );
        }
        
    }
    
    public function render_meta_box() {
        include 'views/html-meta-box.php';
    }
    
    public function save_meta_box( $post_id ) {
        
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        
        if ( ! isset( $_POST['pys_head_footer'] ) ) {
            
            delete_post_meta( $post_id, '_pys_head_footer' );
            
            return;
            
        }
        
        $data = $_POST['pys_head_footer'];

        $meta = array(
            'disable_global' => isset( $data['disable_global'] ) ? true : false,
            'head_any'       => isset( $data['head_any'] ) ? trim( stripslashes( $data['head_any'] ) ) : '',
            'head_desktop'   => isset( $data['head_desktop'] ) ? trim( stripslashes( $data['head_desktop'] ) ) : '',
            'head_mobile'    => isset( $data['head_mobile'] ) ? trim( stripslashes( $data['head_mobile'] ) ) : '',
            'footer_any'     => isset( $data['footer_any'] ) ? trim( stripslashes( $data['footer_any'] ) ) : '',
            'footer_desktop' => isset( $data['footer_desktop'] ) ? trim( stripslashes( $data['footer_desktop'] ) ) : '',
            'footer_mobile'  => isset( $data['footer_mobile'] ) ? trim( stripslashes( $data['footer_mobile'] ) ) : '',
        );
        
        update_post_meta( $post_id, '_pys_head_footer', $meta );
        
    }
    
    public function output_scripts() {
        global $post;
        
        if ( is_admin() || defined( 'DOING_AJAX' ) || defined( 'DOING_CRON' ) ) {
            return;
        }
        
        $detect          = new \Mobile_Detect();
        $this->is_mobile = $detect->isMobile();

        /**
         * WooCommerce Order Received page
         */

        if ( PixelYourSite\is_woocommerce_active() && is_order_received_page() ) {
            add_action( 'wp_head', array( $this, 'output_head_woo_order_received' ) );
            add_action( 'wp_footer', array( $this, 'output_footer_woo_order_received' ) );
        }

        $disabled_by_woo = PixelYourSite\is_woocommerce_active() && is_order_received_page() &&
            $this->get_option( 'woo_order_received_disable_global' );

        if ( $disabled_by_woo ) {
            return;
        }

        /**
         * Single Post
         */

        if ( is_singular() ) {
            $post_meta = get_post_meta( $post->ID, '_pys_head_footer', true );
        } else {
            $post_meta = array();
        }

        if ( ! empty( $post_meta ) ) {
            add_action( 'wp_head', array( $this, 'output_head_post' ) );
            add_action( 'wp_footer', array( $this, 'output_footer_post' ) );
        }

        /**
         * Global
         */

        $disabled_by_post = ! empty( $post_meta ) && $post_meta['disable_global'];

        if ( ! $disabled_by_post ) {
            add_action( 'wp_head', array( $this, 'output_head_global' ) );
            add_action( 'wp_footer', array( $this, 'output_footer_global' ) );
        }

    }

    public function output_head_woo_order_received() {

        $scripts_any = $this->get_option( 'woo_order_received_head_any' );

        if ( $scripts_any ) {
            echo "\r\n" . $this->replace_variables( $scripts_any ) . "\r\n";
        }

        if ( $this->is_mobile ) {
            $scripts_by_device = $this->get_option( 'woo_order_received_head_mobile' );
        } else {
            $scripts_by_device = $this->get_option( 'woo_order_received_head_desktop' );
        }

        if ( $scripts_by_device ) {
            echo "\r\n" . $this->replace_variables( $scripts_by_device ) . "\r\n";
        }

    }

    public function output_footer_woo_order_received() {

        $scripts_any = $this->get_option( 'woo_order_received_footer_any' );

        if ( $scripts_any ) {
            echo "\r\n" . $this->replace_variables( $scripts_any ) . "\r\n";
        }

        if ( $this->is_mobile ) {
            $scripts_by_device = $this->get_option( 'woo_order_received_footer_mobile' );
        } else {
            $scripts_by_device = $this->get_option( 'woo_order_received_footer_desktop' );
        }

        if ( $scripts_by_device ) {
            echo "\r\n" . $this->replace_variables( $scripts_by_device ) . "\r\n";
        }

    }
    
    public function output_head_global() {

        $scripts_any = $this->get_option( 'head_any' );

        if ( $scripts_any ) {
            echo "\r\n" . $this->replace_variables( $scripts_any ) . "\r\n";
        }

        if ( $this->is_mobile ) {
            $scripts_by_device = $this->get_option( 'head_mobile' );
        } else {
            $scripts_by_device = $this->get_option( 'head_desktop' );
        }

        if ( $scripts_by_device ) {
            echo "\r\n" . $this->replace_variables( $scripts_by_device ) . "\r\n";
        }
        
    }
    
    public function output_footer_global() {

        $scripts_any = $this->get_option( 'footer_any' );

        if ( $scripts_any ) {
            echo "\r\n" . $this->replace_variables( $scripts_any ) . "\r\n";
        }

        if ( $this->is_mobile ) {
            $scripts_by_device = $this->get_option( 'footer_mobile' );
        } else {
            $scripts_by_device = $this->get_option( 'footer_desktop' );
        }

        if ( $scripts_by_device ) {
            echo "\r\n" . $this->replace_variables( $scripts_by_device ) . "\r\n";
        }
        
    }
    
    public function output_head_post() {
        global $post;
        
        $post_meta = get_post_meta( $post->ID, '_pys_head_footer', true );

        $scripts_any = isset( $post_meta['head_any'] ) ? $post_meta['head_any'] : false;

        if ( $scripts_any ) {
            echo "\r\n" . $this->replace_variables( $scripts_any ) . "\r\n";
        }
        
        if ( $this->is_mobile ) {
            $scripts_by_device = isset( $post_meta['head_mobile'] ) ? $post_meta['head_mobile'] : false;
        } else {
            $scripts_by_device = isset( $post_meta['head_desktop'] ) ? $post_meta['head_desktop'] : false;
        }

        if ( $scripts_by_device ) {
            echo "\r\n" . $this->replace_variables( $scripts_by_device ) . "\r\n";
        }
        
    }
    
    public function output_footer_post() {
        global $post;
        
        $post_meta = get_post_meta( $post->ID, '_pys_head_footer', true );

        $scripts_any = isset( $post_meta['footer_any'] ) ? $post_meta['footer_any'] : false;

        if ( $scripts_any ) {
            echo "\r\n" . $this->replace_variables( $scripts_any ) . "\r\n";
        }

        if ( $this->is_mobile ) {
            $scripts_by_device = isset( $post_meta['footer_mobile'] ) ? $post_meta['footer_mobile'] : false;
        } else {
            $scripts_by_device = isset( $post_meta['footer_desktop'] ) ? $post_meta['footer_desktop'] : false;
        }

        if ( $scripts_by_device ) {
            echo "\r\n" . $this->replace_variables( $scripts_by_device ) . "\r\n";
        }
        
    }
    
    /**
     * Replace variables with values.
     *
     * @param string $content
     *
     * @return string
     */
    private function replace_variables( $content ) {
        
        if ( empty( $this->replacements ) ) {
            $this->set_replacements_values();
        }
        
        return str_replace( array_keys( $this->replacements ), array_values( $this->replacements ), $content );
        
    }
    
    /**
     * Initialize replacements values.
     */
    private function set_replacements_values() {
        
        $replacements = array(
            '[id]'             => $this->get_content_id(),
            '[title]'          => $this->get_content_title(),
            '[categories]'     => $this->get_content_categories(),
            '[email]'          => $this->get_user_email(),
            '[first_name]'     => $this->get_user_first_name(),
            '[last_name]'      => $this->get_user_last_name(),
            '[order_number]'   => $this->get_order_id(),
            '[order_subtotal]' => $this->get_order_subtotal(),
            '[order_total]'    => $this->get_order_total(),
            '[currency]'       => $this->get_order_currency(),
        );
        
        //url encode values
        foreach ( $replacements as $key => $value ) {
            $replacements[ $key ] = urlencode( $value );
        }
        
        $this->replacements = $replacements;
        
    }
    
    private function get_content_id() {
        global $post;
        
        return is_singular() ? $post->ID : '';
        
    }
    
    private function get_content_title() {
        global $post;
        
        if ( is_singular() && ! is_page() ) {
            
            return $post->post_title;
            
        } elseif ( is_page() || is_home() ) {
            
            return is_home() == true ? get_bloginfo( 'name' ) : $post->post_title;
            
        } elseif ( PixelYourSite\is_woocommerce_active() && is_shop() ) {
            
            return get_the_title( wc_get_page_id( 'shop' ) );
            
        } elseif ( is_category() || is_tax() || is_tag() ) {
            
            if ( is_category() ) {
                
                $cat  = get_query_var( 'cat' );
                $term = get_category( $cat );
                
            } elseif ( is_tag() ) {
                
                $slug = get_query_var( 'tag' );
                $term = get_term_by( 'slug', $slug, 'post_tag' );
                
            } else {
                
                $term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
                
            }
            
            return $term->name;
            
        } else {
            
            return '';
            
        }
        
    }
    
    private function get_content_categories() {
        global $post;
        
        return is_single() ? PixelYourSite\get_object_terms( 'category', $post->ID ) : '';
        
    }
    
    private function get_user_email() {
        
        $user = wp_get_current_user();
        
        if ( $user ) {
            return $user->user_email;
        } else {
            return '';
        }
        
    }
    
    private function get_user_first_name() {
        
        $user = wp_get_current_user();
        
        if ( $user ) {
            return $user->user_firstname;
        } else {
            return '';
        }
        
    }
    
    private function get_user_last_name() {
        
        $user = wp_get_current_user();
        
        if ( $user ) {
            return $user->user_lastname;
        } else {
            return '';
        }
        
    }
    
    private function get_order_id() {
        
        if ( PixelYourSite\is_woocommerce_active() && is_order_received_page() && isset( $_REQUEST['key'] ) ) {
            
            return wc_get_order_id_by_order_key( $_REQUEST['key'] );
            
        } elseif ( PixelYourSite\is_edd_active() && edd_is_success_page() ) {
            
            return $this->get_edd_order_meta( 'id' );
            
        } else {
            return '';
        }
        
    }
    
    private function get_order_subtotal() {
        
        if ( PixelYourSite\is_woocommerce_active() && is_order_received_page() && isset( $_REQUEST['key'] ) ) {
            
            $order_id = wc_get_order_id_by_order_key( $_REQUEST['key'] );
            $order    = new \WC_Order( $order_id );
            
            return $order->get_subtotal();
            
        } elseif ( PixelYourSite\is_edd_active() && edd_is_success_page() ) {
            
            return $this->get_edd_order_meta( 'subtotal' );
            
        } else {
            return '';
        }
        
    }
    
    private function get_order_total() {
        
        if ( PixelYourSite\is_woocommerce_active() && is_order_received_page() && isset( $_REQUEST['key'] ) ) {
            
            $order_id = wc_get_order_id_by_order_key( $_REQUEST['key'] );
            $order    = new \WC_Order( $order_id );
            
            return $order->get_total();
            
        } elseif ( PixelYourSite\is_edd_active() && edd_is_success_page() ) {
            
            return $this->get_edd_order_meta( 'total' );
            
        } else {
            return '';
        }
        
    }
    
    private function get_order_currency() {
        
        if ( PixelYourSite\is_woocommerce_active() && is_order_received_page() && isset( $_REQUEST['key'] ) ) {
            
            return get_woocommerce_currency();
            
        } elseif ( PixelYourSite\is_edd_active() && edd_is_success_page() ) {
            
            return edd_get_currency();
            
        } else {
            return '';
        }
        
    }
    
    private function get_edd_order_meta( $key ) {
        global $edd_receipt_args;
        
        // skip payment confirmation page
        if ( isset( $_GET['payment-confirmation'] ) ) {
            return '';
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
            return '';
        }
        
        $payment_id    = edd_get_purchase_id_by_key( $payment_key );
        $user_can_view = edd_can_view_receipt( $payment_key );
        
        if ( ! $user_can_view && ! empty( $payment_key ) && ! is_user_logged_in() && ! edd_is_guest_payment( $payment_id ) ) {
            return '';
        }
        
        switch ( $key ) {
            case 'id':
                return $payment_id;
                break;
            
            case 'subtotal':
                return $session['subtotal'];
                break;
            
            case 'total':
                return $session['price'];
                break;
            
            default:
                return '';
        }
        
    }
    
}