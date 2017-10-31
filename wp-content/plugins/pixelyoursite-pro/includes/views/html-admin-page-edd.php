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
		<h2>Easy Digital Downloads Settings</h2>
		<p>Manage your EDD Events. All your EDD Events are Dynamic Ads ready ("Promote a product catalog" objective).</p>

        <?php if ( false == PixelYourSite\is_edd_products_feed_pro_active() ) : ?>
            <p>You can create a Product Catalog Feed with our dedicated plugin: <a
                href="http://www.pixelyoursite.com/easy-digital-downloads-product-catalog" target="_blank">CLICK HERE FOR DETAILS</a>
            </p>
        <?php endif; ?>
	</div>
</div>

<hr>

<div class="row form-horizontal">
	<div class="col-xs-12">
		<h3>Content ID Settings</h3>

		<div class="form-group">
			<label for="" class="col-md-3 control-label">content_id</label>
			<div class="col-md-4">

				<?php

				$this->render_select_html( 'edd_content_id', array(
					'download_id'  => 'Download ID',
					'download_sku' => 'Download SKU',
				) );

				?>
				
			</div>
		</div>

		<div class="form-group">
			<label for="" class="col-md-3 control-label">content_id prefix</label>
			<div class="col-md-4">

				<?php

				$this->render_text_html( 'edd_content_id_prefix', '(optional)' );

				?>
			
			</div>
		</div>

		<div class="form-group">
			<label for="" class="col-md-3 control-label">content_id suffix</label>
			<div class="col-md-4">

				<?php

				$this->render_text_html( 'edd_content_id_suffix', '(optional)' );

				?>

			</div>
		</div>

	</div>
</div>

<hr>

<div class="row form-horizontal">
	<div class="col-xs-12">
		<h3>Event Values Settings</h3>

		<div class="radio">
			<?php $this->render_radio_html( 'edd_event_value', 'price', 'Use Downloads prices as they are on the site' ); ?>
		</div>

		<div class="radio">
			<?php $this->render_radio_html( 'edd_event_value', 'custom', 'Customize the value' ); ?>
		</div>

		<div class="form-group m-t-20 custom-value-control">
			<label for="" class="col-md-3 control-label">Tax</label>
			<div class="col-md-4">

				<?php

				$this->render_select_html( 'edd_tax_option', array(
					'included' => 'Include Tax',
					'excluded' => 'Exclude Tax',
				) );

				?>

			</div>
		</div>

		<div class="form-group">
			<p class="col-sm-12">You have more value options under each event: turn On/Off, define value.</p>
		</div>

	</div>
</div>

<hr>

<div class="row form-horizontal">
    <div class="col-xs-12">
        <h3>Lifetime Customer Value</h3>
        <p>Add the lifetime total transaction value as <code>lifetime_value</code> Purchase Event parameter.</p>

        <div class="form-group switcher">
            <div class="col-xs-12">
                <?php $this->render_switchery_html( 'edd_lifetime_value_enabled',
                    'Add customer Life Time Value' ); ?>
            </div>
        </div>

        <div class="form-group after-switcher">
            <div class="col-xs-12">
                <?php $this->render_multi_select_html( 'edd_lifetime_value_order_statuses',
                    edd_get_payment_statuses() ); ?>
                <span class="help-block">Use the <code>lifetime_value</code> parameter to create Custom Audiences. Expand
                    your reach with Lookalike Audiences based on the Lifetime Value Custom 
                    Audiences. <a href="http://www.pixelyoursite.com/facebook-lifetime-value-audiences"
                                  target="_blank">Learn More</a></span>
            </div>
        </div>

    </div>
</div>

<hr>

<div class="row form-horizontal">
	<div class="col-xs-12">
		<h3>Custom Audiences Optimization</h3>

		<div class="form-group">
			<div class="col-xs-12">
				<?php $this->render_switchery_html( 'edd_track_custom_audiences', 'Enabled' ); ?>
			</div>
		</div>

		<div class="form-group">
			<p class="col-sm-12">The Download Name will be pulled as <code>content_name</code>, and the Product Category as
				<code>category_name</code> for all the EDD events. The number of items goes under the <code>num_items</code> parameter for InitiateCheckout and Purchase events. Download tags will be tracked for all the events. Traffic Source and URL parameters are also tracked.</p>
			<p class="col-sm-12">If you use the License Software plugin, additional parameters are added:</p>
			<ul class="col-sm-12" style="margin-left: 20px;">
				<li>license_time_limit: the license expiration time</li>
				<li>license_site_limit: the number of sites a user can use the download</li>
				<li>license_version: license number</li>
				<li>transaction_year: the year when transaction took place</li>
				<li>transaction_month</li>
			</ul>
			<p class="col-sm-12"><strong>Tip:</strong> you can use these parameters to create super-targeted Custom Audiences.</p>

		</div>

	</div>
</div>

<hr>

<?php

// allow 3rd party add own sections
do_action( 'pys_fb_pixel_admin_edd_section' );

?>

<div class="row">
	<div class="col-xs-12">
		<h3>Easy Digital Downloads Events</h3>

		<div class="panel-group" role="tablist" id="accordion" aria-multiselectable="true">
			<div class="panel panel-primary">
				<div class="panel-heading" role="tab" id="heading-view-content">
					<h4 class="panel-title">
						<a href="#collapse-view-content" role="button" data-toggle="collapse" data-parent="#accordion" aria-expanded="true" aria-controls="collapse-view-content" class="">ViewContent</a>
					</h4>
				</div>
				<div class="panel-collapse collapse" role="tabpanel" id="collapse-view-content" aria-labelledby="heading-view-content" aria-expanded="true">
					<div class="panel-body">
						<?php include 'html-admin-page-edd-view-content.php'; ?>
					</div>
				</div>
			</div>
			<div class="panel panel-primary">
				<div class="panel-heading" role="tab" id="heading-add-to-cart">
					<h4 class="panel-title">
						<a href="#collapse-add-to-cart" role="button" data-toggle="collapse" data-parent="#accordion" aria-expanded="true" aria-controls="collapse-add-to-cart" class="">AddToCart</a>
					</h4>
				</div>
				<div class="panel-collapse collapse" role="tabpanel" id="collapse-add-to-cart" aria-labelledby="heading-add-to-cart" aria-expanded="true">
					<div class="panel-body">
						<?php include 'html-admin-page-edd-add-to-cart.php'; ?>
					</div>
				</div>
			</div>
			<div class="panel panel-primary">
				<div class="panel-heading" role="tab" id="heading-initiate-checkout">
					<h4 class="panel-title">
						<a href="#collapse-initiate-checkout" role="button" data-toggle="collapse" data-parent="#accordion" aria-expanded="true" aria-controls="collapse-initiate-checkout" class="">InitiateCheckout</a>
					</h4>
				</div>
				<div class="panel-collapse collapse" role="tabpanel" id="collapse-initiate-checkout" aria-labelledby="heading-initiate-checkout" aria-expanded="true">
					<div class="panel-body">
						<?php include 'html-admin-page-edd-initiate-checkout.php'; ?>
					</div>
				</div>
			</div>
			<div class="panel panel-primary">
				<div class="panel-heading" role="tab" id="heading-purchase">
					<h4 class="panel-title">
						<a href="#collapse-purchase" role="button" data-toggle="collapse" data-parent="#accordion" aria-expanded="true" aria-controls="collapse-purchase" class="">Purchase</a>
					</h4>
				</div>
				<div class="panel-collapse collapse" role="tabpanel" id="collapse-purchase" aria-labelledby="heading-purchase" aria-expanded="true">
					<div class="panel-body">
						<?php include 'html-admin-page-edd-purchase.php'; ?>
					</div>
				</div>
			</div>
		</div>

	</div>
</div>

<hr>

<div class="row form-horizontal">
    <div class="col-xs-12">
        <h3>Custom Audience File Export</h3>
        <p>Export a customer file with lifetime value. Use it to create a Custom Audience and a Value-Based Lookalike
            Audience. <a href="http://www.pixelyoursite.com/value-based-facebook-lookalike-audiences" target="_blank">More
                details here.</a></p>

        <div class="form-group">
            <div class="col-xs-12 col-md-8">
                <?php $this->render_multi_select_html( 'edd_customers_export_order_statuses',
                    edd_get_payment_statuses() ); ?>
            </div>

            <div class="col-xs-12 col-md-4">
                
                <?php $edd_export_url = add_query_arg( array(
                    'action'       => 'pys_fb_pixel_customers_export',
                    'export_scope' => 'edd',
                    'nonce'        => wp_create_nonce( 'pys_fb_pixel_customers_export' ),
                ), admin_url( 'admin.php' ) ); ?>

                <a href="<?php echo esc_url( $edd_export_url ); ?>" class="btn btn-lg btn-block btn-default"
                   target="_blank">Download CSV</a>

            </div>
        </div>

    </div>
</div>

<hr>

<?php PixelYourSite\render_general_button( 'Save Settings' ); ?>
<?php render_ecommerce_plugins_notice(); ?>

<script type="text/javascript">
	jQuery(document).ready(function ($) {

		$('input[name="pys_fb_pixel_pro_edd_event_value"]').change(function (e) {
			toggleEventValueControls();
		});

		toggleEventValueControls();

		function toggleEventValueControls() {

			var value_option = $('input[name="pys_fb_pixel_pro_edd_event_value"]:checked').val();

			if (value_option == 'price') {
				$('.custom-value-control').hide();
			} else {
				$('.custom-value-control').show();
			}

		}

        //@fixme: move to api as well as scripts enqueue
        //@todo: fix select2 css to make it matching other inputs
        $('.pys-select2').select2();

	});
</script>