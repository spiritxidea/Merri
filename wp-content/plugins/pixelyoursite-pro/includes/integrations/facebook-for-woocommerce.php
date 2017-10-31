<?php

/**
 * Manage integration with Facebook for WooCommerce plugin.
 *
 * When Facebook for WooCommerce is activated this integration completely removes
 * default Facebook for WooCommerce pixels and replaces them with PYS's. Also, new "Pixel ID format" option added to
 * PYS WooCommerce tab where user can decide what format to use in pixel: PYS default or Facebook for WooCommerce.
 * 
 * @todo: override WC_Facebookcommerce_EventsTracker class only when PYS WooCommerce module enabled.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'WC_Facebookcommerce' ) && ! class_exists( 'WC_Facebookcommerce_EventsTracker' ) ) :
    
    /**
     * Declare fake WC_Facebookcommerce_EventsTracker class to remove all unwanted front-end pixel events.
     */
    
	/** @noinspection PhpUndefinedClassInspection */
	class WC_Facebookcommerce_EventsTracker {

		public function __construct( $pixel_id, $user_info ) {}

		public function inject_base_pixel() {}
        
        public function inject_view_content_event() {}
        
        public function inject_search_event() {}
        
        public function inject_add_to_cart_event() {}
        
        public function inject_initiate_checkout_event() {}
        
        public function inject_purchase_event( $order_id ) {}

		public function inject_view_category_event() {}

	}

endif;

if ( class_exists( 'WC_Facebookcommerce' ) ) :
    
    /**
     * Setup PYS hooks and filters for Facebook for WooCommerce related options and pixel ID output format.
     */

	add_filter( 'pys_fb_pixel_woo_product_content_id', 'fb_for_woo_pys_fb_pixel_woo_product_content_id', 10, 6 );
	function fb_for_woo_pys_fb_pixel_woo_product_content_id( $value, $product_id, $content_id, $suffix, $prefix, $content_id_format ) {

		// use PYS content_id format
		if( $content_id_format !== 'facebook_for_woocommerce' ) {
			return $value;
		}

        $ids = array(
            'wc_post_id_' . $product_id
        );

		if( $sku = get_post_meta( $product_id, '_sku', true ) ) {

            $ids[] = $sku;
            $ids[] = 'wc_post_id_' . $sku;

        }

		return $ids;

	}

	add_filter( 'pys_fb_pixel_setting_defaults', 'fb_for_woo_pys_fb_pixel_setting_defaults', 10, 1 );
	function fb_for_woo_pys_fb_pixel_setting_defaults( $setting_defaults ) {

		$setting_defaults['woo_content_id_format'] = 'default';

		return $setting_defaults;

	}

	add_filter( 'pys_fb_pixel_settings_form_fields', 'fb_for_woo_pys_fb_pixel_settings_form_fields', 10, 1 );
	function fb_for_woo_pys_fb_pixel_settings_form_fields( $settings_form_fields ) {

		$settings_form_fields['woo']['woo_content_id_format'] = 'radio';

		return $settings_form_fields;

	}

	add_action( 'pys_fb_pixel_admin_woo_content_id_before', 'pys_fb_pixel_admin_woo_content_id_before', 10, 1 );
	function pys_fb_pixel_admin_woo_content_id_before( $plugin ) {
		/** @var PixelYourSite\FacebookPixelPro\Addon $plugin */

		?>

		<p><strong>It looks like you're using both PixelYourSite and Facebook Ads Extension. Good, because they can do a great job together!</strong></p>

		<p>Facebook Ads Extension is a useful free tool that lets you import your products to a Facebook shop and adds a very basic Facebook pixel on your site. PixelYourSite is a dedicated plugin that supercharges your Facebook Pixel with extremely useful features.</p>

		<p>We made it possible to use both plugins together. You just have to decide what ID to use for your events.</p>

		<div class="radio">
			<?php $plugin->render_radio_html( 'woo_content_id_format', 'facebook_for_woocommerce', 'Use Facebook for WooCommerce extension content_id logic' ); ?>
		</div>

		<div class="radio">
			<?php $plugin->render_radio_html( 'woo_content_id_format', 'default', 'Use PixelYourSite content_id logic' ); ?>
		</div>

		<p><em>* If you plan to use the product catalog created by Facebook for WooCommerce Extension, use the Facebook for WooCommerce Extension ID. If you plan to use older product catalogs, or new ones created with other plugins, it's better to keep the default PixelYourSite settings.</em></p>

		<script type="text/javascript">
			jQuery(document).ready(function ($) {

				$('input[name="pys_fb_pixel_pro_woo_content_id_format"]').change(function (e) {
					toggleContentIDFormatControls();
				});

				toggleContentIDFormatControls();

				function toggleContentIDFormatControls() {

					var format = $('input[name="pys_fb_pixel_pro_woo_content_id_format"]:checked').val();

					if (format == 'default') {
						$('.form-group', '#woo_content_id' ).show();
					} else {
						$('.form-group', '#woo_content_id').hide();
					}

				}

			});
		</script>

		<?php
	}

	add_action( 'admin_notices', 'fb_for_woo_admin_notice_display' );
	function fb_for_woo_admin_notice_display() {

		$user_id = get_current_user_id();

		if( get_user_meta( $user_id, 'fb_for_woo_admin_notice_dismissed' ) ) {
			return;
		}

		?>

		<div class="notice notice-success is-dismissible fb_for_woo_admin_notice">
			<p>You're using both PixelYourSite and Facebook for WooCommerce Extension. Good, because they can do a great job together! <strong><a href="<?php echo admin_url( 'admin.php?page=fb_pixel_pro&tab=woo#woo_content_id' ); ?>">Click here for more details</a></strong>.</p>
		</div>

		<script type="text/javascript">
			jQuery(document).on('click', '.fb_for_woo_admin_notice .notice-dismiss', function () {

				jQuery.ajax({
					url: ajaxurl,
					data: {
						action: 'fb_for_woo_admin_notice_dismiss',
						nonce: '<?php echo wp_create_nonce( 'fb_for_woo_admin_notice_dismiss' ); ?>',
						user_id: '<?php echo $user_id; ?>'
					}
				})

			})
		</script>

		<?php
	}

	add_action( 'wp_ajax_fb_for_woo_admin_notice_dismiss', 'fb_for_woo_admin_notice_dismiss_handler' );
	function fb_for_woo_admin_notice_dismiss_handler() {

		//@todo: implement standardized admin notices API
		
		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'fb_for_woo_admin_notice_dismiss' ) ) {
			return;
		}

		add_user_meta( $_REQUEST['user_id'], 'fb_for_woo_admin_notice_dismissed', true );
		
	}

endif;