<?php

namespace PixelYourSite\FacebookPixelPro;

use PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/** @var Addon $this */

?>

<div class="row form-horizontal">
	<div class="col-xs-12">
		<h2>Facebook Pixel Settings</h2>
		
		<div class="form-group">
			<label for="pixel_id" class="col-md-3 control-label">Add your Facebook Pixel ID:</label>
			<div class="col-md-4">
				
				<?php $this->render_text_html( 'pixel_id', 'Enter your Facebook Pixel ID' ); ?>
				
				<span class="help-block">Where to find the Pixel ID? <a href="http://www.pixelyoursite.com/facebook-pixel-plugin-help" target="_blank">Click here for help</a></span>
			</div>
		</div>
		
		<?php do_action( 'pys_fb_pixel_admin_pixel_id_after' ); ?>

	</div>
</div>

<hr>

<div class="row form-horizontal">
	<div class="col-xs-12">

		<div class="form-group switcher m-b-30">
			<div class="col-xs-12">
				<?php $this->render_switchery_html( 'general_event_enabled', 'Enable the GeneralEvent' ); ?>
				<p>Use the GeneralEvent for your Custom Audiences and Custom Conversions.</p>
			</div>
		</div>

		<div class="form-group">
			<label for="general_event_name" class="col-md-3 control-label">General Event Name:</label>
			<div class="col-md-4">
				<?php $this->render_text_html( 'general_event_name', 'GeneralEvent' ); ?>
			</div>
		</div>

		<div class="form-group">
			<label for="general_event_delay" class="col-md-3 control-label">Delay:</label>
			<div class="col-md-4">
				<?php $this->render_text_html( 'general_event_delay' ); ?>
				<span class="help-block">Avoid retargeting bouncing users. People that spent less time on the page will not be part of your Custom Audiences based on the GeneralEvent. (It is better to add a lower time that the desired one because the pixel code will not load instantaneously).</span>
			</div>
		</div>

		<div class="form-group switcher">
			<div class="col-md-9 col-md-offset-3">
				<?php $this->render_switchery_html( 'general_event_on_posts_enabled', 'Enable on Posts' ); ?>
				<p>Will pull post title as <code>content_name</code> and post category name as <code>category_name</code>
				</p>
			</div>
		</div>

		<div class="form-group switcher">
			<div class="col-md-9 col-md-offset-3">
				<?php $this->render_switchery_html( 'general_event_on_pages_enabled', 'Enable on Pages' ); ?>
				<p>Will pull page title as <code>content_name</code></p>
			</div>
		</div>

		<div class="form-group switcher">
			<div class="col-md-9 col-md-offset-3">
				<?php $this->render_switchery_html( 'general_event_on_tax_enabled', 'Enable on Taxonomies' ); ?>
				<p>Will pull taxonomy name as <code>content_name</code></p>
			</div>
		</div>

		<?php if ( PixelYourSite\is_woocommerce_active() ) : ?>

			<div class="form-group switcher">
				<div class="col-md-9 col-md-offset-3">
					<?php $this->render_switchery_html( 'general_event_on_woo_enabled', 'Enable on WooCommerce Products' ); ?>
					<p>Will pull product title as <code>content_name</code> and product category name as
						<code>category_name</code>, product price as <code>value</code>, currency as
						<code>currency</code>, post type as <code>content_type</code></p>
				</div>
			</div>

		<?php endif; ?>

		<?php if ( PixelYourSite\is_edd_active() ) : ?>

			<div class="form-group switcher">
				<div class="col-md-9 col-md-offset-3">
					<?php $this->render_switchery_html( 'general_event_on_edd_enabled', 'Enable on Easy Digital Downloads Products' ); ?>
					<p>Will pull product title as <code>content_name</code> and product category name as
						<code>category_name</code>, product price as <code>value</code>, currency as
						<code>currency</code>, post type as <code>content_type</code></p>
				</div>
			</div>

		<?php endif; ?>

		<?php $inactive_ctp_html = ''; ?>

		<?php foreach ( get_post_types( array( 'public' => true, '_builtin' => false ), 'objects' ) as $post_type ) : ?>

			<?php

			// skip product post type when WOO is active
			if ( PixelYourSite\is_woocommerce_active() && $post_type->name == 'product' ) {
				continue;
			}

			// skip download post type when EDD is active
			if ( PixelYourSite\is_edd_active() && $post_type->name == 'download' ) {
				continue;
			}

			ob_start();

			?>

			<div class="form-group switcher">
				<div class="col-md-9 col-md-offset-3">
					<?php $this->render_switchery_html( "general_event_on_{$post_type->name}_enabled", 'Enable on ' . ucfirst( $post_type->name ) . ' Post Type' ); ?>
					<p>Will pull <?php esc_attr_e( $post_type->name ); ?> title as
						<code>content_name</code> and <?php esc_attr_e( $post_type->name ); ?> taxonomy name as
						<code>category_name</code>, post type as <code>content_type</code></code></p>
				</div>
			</div>

			<?php

			if ( $this->get_option( "general_event_on_{$post_type->name}_enabled" ) ) {
				ob_flush();
			} else {
				$inactive_ctp_html .= ob_get_clean();
			}

			?>

		<?php endforeach; ?>

		<?php if( ! empty( $inactive_ctp_html ) ) : ?>

		<div class="panel-group" role="tablist" id="accordion" aria-multiselectable="true">
			<div class="panel panel-primary">
				<div class="panel-heading" role="tab" id="heading-general-event-inactive-cpt">
					<h4 class="panel-title">
						<a href="#collapse-general-event-inactive-cpt" role="button" data-toggle="collapse" data-parent="#accordion" aria-expanded="true" aria-controls="collapse-general-event-inactive-cpt" class="">Inactive Custom Post Types</a>
					</h4>
				</div>
				<div class="panel-collapse collapse" role="tabpanel" id="collapse-general-event-inactive-cpt" aria-labelledby="heading-general-event-inactive-cpt" aria-expanded="true">
					<div class="panel-body" style="padding: 0 0 20px;">
						<?php echo $inactive_ctp_html; ?>
					</div>
				</div>
			</div>
		</div>

		<?php endif; ?>
		
	</div>
</div>

<hr>

<div class="row form-horizontal">
    <div class="col-xs-12">
        <div class="form-group switcher">
            <div class="col-xs-12">
                <?php $this->render_switchery_html( 'click_event_enabled', 'Enable the ClickEvent' ); ?>
                <p>Capture clicks on links and buttons. Use this event for Custom Audiences or Custom Conversions.</p>
            </div>
        </div>
    </div>
</div>

<hr>

<div class="row form-horizontal">
	<div class="col-xs-12">

		<div class="form-group switcher">
			<div class="col-md-12">
				<?php $this->render_switchery_html( 'search_event_enabled', 'Enable the Search Event' ); ?>
				<p>The Search Event will be active on the Search page and will automatically pull the search string as a parameter. Useful for creating Custom Audiences.</p>
			</div>
		</div>

	</div>
</div>

<hr>

<div class="row form-horizontal">
	<div class="col-xs-12">

		<div class="form-group switcher">
			<div class="col-xs-12">
				<?php $this->render_switchery_html( 'track_traffic_source_enabled', 'Track Traffic Source and URL Parameters' ); ?>
				<p>Add traffic source as <code>traffic_source</code> and URL parameters (UTM) as parameters to all your events. </p>
			</div>
		</div>

		<p><strong>Tip:</strong> use them to segment your Custom Audiences and improve your retargeting (retarget people based on when they come from, like Google, Facebook or a particular ad, for example).</p>

	</div>
</div>

<hr>

<div class="row form-horizontal">
	<div class="col-xs-12">

		<div class="form-group switcher">
			<div class="col-xs-12">
				<?php $this->render_switchery_html( 'advance_matching_enabled', 'Enable Advanced Matching' ); ?>
				<p>Advance Matching can lead to 10% increase in attributed conversions and 20% increase in reach of retargeting campaigns -
					<a href="http://www.pixelyoursite.com/enable-advance-matching-woocommerce" target="_blank">click to read more</a>
				</p>
			</div>
		</div>

		<p>The plugin will securely send to Facebook Additional data about your visitors (like name, email, phone, etc). This works very well with WooCommerce or Easy Digital Downloads.</p>
		
	</div>
</div>

<?php do_action( 'pys_fb_pixel_admin_index_end' ); ?>

<?php PixelYourSite\render_general_button( 'Save Settings' ); ?>
<?php render_ecommerce_plugins_notice(); ?>
