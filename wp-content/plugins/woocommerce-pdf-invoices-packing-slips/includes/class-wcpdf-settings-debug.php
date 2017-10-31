<?php
namespace WPO\WC\PDF_Invoices;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( '\\WPO\\WC\\PDF_Invoices\\Settings_Debug' ) ) :

class Settings_Debug {

	function __construct()	{
		add_action( 'admin_init', array( $this, 'init_settings' ) );
		add_action( 'wpo_wcpdf_settings_output_debug', array( $this, 'output' ), 10, 1 );
		add_action( 'wpo_wcpdf_after_settings_page', array( $this, 'debug_tools' ), 10, 2 );
	}

	public function output( $section ) {
		settings_fields( "wpo_wcpdf_settings_debug" );
		do_settings_sections( "wpo_wcpdf_settings_debug" );

		submit_button();
	}

	public function debug_tools( $tab, $section ) {
		if ($tab !== 'debug') {
			return;
		}
		?>
		<form method="post">
			<input type="hidden" name="wpo_wcpdf_debug_tools_action" value="install_fonts">
			<input type="submit" name="submit" id="submit" class="button" value="<?php _e( 'Reinstall fonts', 'woocommerce-pdf-invoices-packing-slips' ); ?>">
			<?php
			if (isset($_POST['wpo_wcpdf_debug_tools_action']) && $_POST['wpo_wcpdf_debug_tools_action'] == 'install_fonts') {
				$font_path = WPO_WCPDF()->main->get_tmp_path( 'fonts' );
				WPO_WCPDF()->main->copy_fonts( $font_path );
				printf('<div class="notice notice-success"><p>%s</p></div>', __( 'Fonts reinstalled!', 'woocommerce-pdf-invoices-packing-slips' ) );
			}
			?>
		</form>
		<?php
		include( WPO_WCPDF()->plugin_path() . '/includes/views/dompdf-status.php' );
	}

	public function init_settings() {
		// Register settings.
		$page = $option_group = $option_name = 'wpo_wcpdf_settings_debug';

		$settings_fields = array(
			array(
				'type'			=> 'section',
				'id'			=> 'debug_settings',
				'title'			=> __( 'Debug settings', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'section',
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'legacy_mode',
				'title'			=> __( 'Legacy mode', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'checkbox',
				'section'		=> 'debug_settings',
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'legacy_mode',
					'description'	=> __( "Legacy mode ensures compatibility with templates and filters from previous versions.", 'woocommerce-pdf-invoices-packing-slips' ),
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'enable_debug',
				'title'			=> __( 'Enable debug output', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'checkbox',
				'section'		=> 'debug_settings',
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'enable_debug',
					'description'	=> __( "Enable this option to output plugin errors if you're getting a blank page or other PDF generation issues", 'woocommerce-pdf-invoices-packing-slips' ) . '<br>' .
									   __( '<b>Caution!</b> This setting may reveal errors (from other plugins) in other places on your site too, therefor this is not recommended to leave it enabled on live sites.', 'woocommerce-pdf-invoices-packing-slips' ),
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'html_output',
				'title'			=> __( 'Output to HTML', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'checkbox',
				'section'		=> 'debug_settings',
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'html_output',
					'description'	=> __( 'Send the template output as HTML to the browser instead of creating a PDF.', 'woocommerce-pdf-invoices-packing-slips' ),
				)
			),
		);

		// allow plugins to alter settings fields
		$settings_fields = apply_filters( 'wpo_wcpdf_settings_fields_debug', $settings_fields, $page, $option_group, $option_name );
		WPO_WCPDF()->settings->add_settings_fields( $settings_fields, $page, $option_group, $option_name );
		return;
	}

}

endif; // class_exists

return new Settings_Debug();