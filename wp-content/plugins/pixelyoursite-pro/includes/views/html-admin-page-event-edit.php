<?php

namespace PixelYourSite\FacebookPixelPro;

use PixelYourSite\PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/** @var Addon $this */

$event = isset( $_REQUEST['event_id'] ) ? EventsFactory::get_by_id( $_REQUEST['event_id'] ) : new Event();

if( false === $event ) {
//	PixelYourSite\render_not_found_message();   //@todo: show not found message
	return;
}

$event_action = $event->getId() ? 'update_event' : 'create_event';
$submit_btn_text = $event->getId() ? __( 'Update Event', 'pys' ) : __( 'Add Event', 'pys' );
$return_back_url = admin_url( 'admin.php?page=fb_pixel_pro&tab=events' );

?>

<div class="row">
	<div class="col-xs-12">
		<p class="go-back"><a href="<?php echo $return_back_url; ?>"><i class="fa fa-arrow-left" aria-hidden="true"></i> Return to Events Overview</a></p>
	</div>
</div>

<input type="hidden" name="<?php esc_attr_e( $event_action ); ?>" value="1">
<?php wp_nonce_field( $event_action ); ?>

<input type="hidden" name="fb_pixel_pro[event][id]" value="<?php esc_attr_e( $event->getId() ); ?>">

<!-- Common Event Options -->
<div class="row form-horizontal event-properties m-b-30">
	<div class="col-xs-12">

		<div class="form-group">
			<label for="fb_pixel_event_title" class="col-md-3 control-label">Event name</label>
			<div class="col-md-4">
				<input type="text" class="form-control" placeholder="Enter event name" name="fb_pixel_pro[event][title]" value="<?php esc_attr_e( $event->getTitle() ); ?>" id="fb_pixel_event_title">
				<span class="help-block">For internal use, something that will help you remember the event.</span>
			</div>
		</div>

		<div class="form-group">
			<label for="fb_pixel_event_state" class="col-sm-3 control-label">Enabled</label>
			<div class="col-sm-4">
				<input type="checkbox" name="fb_pixel_pro[event][state]" <?php checked( $event->getState(), 'active' ); ?> id="fb_pixel_event_state" data-plugin="switchery" style="display: none;">
			</div>
		</div>

		<div class="form-group">
			<label for="fb_pixel_event_trigger_type" class="col-sm-3 control-label">Type</label>
			<div class="col-sm-9">
				<div class="radio radio-primary">
					<input type="radio" name="fb_pixel_pro[event][type]" id="fb_pixel_event_on_page" value="on_page"
						<?php checked( $event->getType(), 'on_page' ); ?> >
					<label for="fb_pixel_event_on_page">Trigger on key page load</label>
				</div>
				<div class="radio radio-primary">
					<input type="radio" name="fb_pixel_pro[event][type]" id="fb_pixel_event_dynamic" value="dynamic" <?php checked( $event->getType(), 'dynamic' ); ?>>
					<label for="fb_pixel_event_dynamic">Dynamic Event - trigger on key action</label>
				</div>
			</div>
		</div>

	</div>
</div>

<hr>

<!-- OnPage Triggers -->
<div class="row form-horizontal on-page-event-triggers" id="on-page-event-triggers" style="display: none;">
	<div class="col-xs-12">
		<h3>Event Trigger Conditions</h3>

		<div class="triggers-wrapper m-t-30">

			<?php foreach ( $event->getOnPageTriggers() as $trigger ) : ?>

				<?php $uid = uniqid(); ?>

				<div class="event-property">
					<div class="form-group">
						<label for="fb_pixel_event_trigger_<?php esc_attr_e( $uid ); ?>" class="col-md-3 control-label">On URL visit</label>
						<div class="col-md-4">
							<input type="text" class="form-control" placeholder="Enter URL" name="fb_pixel_pro[event][triggers][on_page][<?php esc_attr_e( $uid ); ?>]" value="<?php esc_attr_e( $trigger ); ?>" id="fb_pixel_event_trigger_<?php esc_attr_e( $uid ); ?>">
							<span class="help-block">Event will trigger when this URL is visited. If you add <code>*</code> at the end of the URL string, it will match all URLs starting with the this string.</span>
						</div>
						<div class="col-md-1">
							<button class="btn btn-icon btn-remove remove-property" type="button">
								<i class="fa fa-remove"></i>
							</button>
						</div>
					</div>
				</div>

			<?php endforeach; ?>

		</div><!-- .triggers-wrapper -->

		<div class="form-group">
			<div class="col-md-9 col-md-offset-3">
				<button class="btn btn-primary" type="button" id="add-on-page-trigger">
					<i class="fa fa-plus m-r-10"></i><span>Add Trigger</span>
				</button>
			</div>
		</div>

	</div>
</div>

<!-- Dynamic Triggers -->
<div class="row form-horizontal dynamic-event-triggers" id="dynamic-event-triggers" style="display: none;">
	<div class="col-xs-12">
		<h3>Event Trigger Conditions</h3>

		<div class="triggers-wrapper m-t-30">

			<?php foreach ( $event->getDynamicTriggers() as $trigger ) : ?>

				<?php

				$uid   = uniqid();
				$type  = $trigger['type'];

				switch ( $type ) {
					case 'url_click':
						$label = 'Click on URL';
						$placeholder = 'Enter URL';
						$description = 'Event will trigger when a LINK to this URL is clicked. If you add <code>*</code> at the end of the URL string, it will match all URLs starting with this string.';
						break;

					case 'css_click':
						$label       = 'Click on CSS selector';
						$placeholder = 'Enter CSS selector';
						$description = 'The event will be fired on this CSS selector. Please read about CSS Selector <a href="http://www.pixelyoursite.com/facebook-pixel-dynamic-events" target="_blank">here</a>.';
						break;

					case 'css_mouseover':
						$label       = 'Mouseover on CSS selector';
						$placeholder = 'Enter CSS selector';
						$description = 'The event will be fired when mouse over on this CSS selector. Please read about CSS Selector <a href="http://www.pixelyoursite.com/facebook-pixel-dynamic-events" target="_blank">here</a>.';
						break;

					case 'scroll_pos':
						$label       = 'Page scrolled to position';
						$placeholder = 'Enter page scroll position in %';
						$description = 'The event will be fired when page scrolled to desired position.';
						break;

					default:
						$label = $placeholder = $description = '';
						continue;
				}

				?>

				<div class="event-property">
					<div class="form-group">
						<label for="fb_pixel_event_trigger_<?php esc_attr_e( $uid ); ?>" class="col-md-3 control-label"><?php echo $label; ?></label>
						<div class="col-md-4">
							<input type="text" class="form-control"
							       placeholder="<?php esc_attr_e( $placeholder ); ?>"
							       name="fb_pixel_pro[event][triggers][dynamic][<?php esc_attr_e( $uid ); ?>][value]"
							       value="<?php esc_attr_e( $trigger['value'] ); ?>"
							       id="fb_pixel_event_trigger_<?php esc_attr_e( $uid ); ?>">
							<input type="hidden"
							       name="fb_pixel_pro[event][triggers][dynamic][<?php esc_attr_e( $uid ); ?>][type]"
							       value="<?php esc_attr_e( $type ); ?>">
							<span class="help-block"><?php echo $description; ?></span>
						</div>
						<div class="col-md-1">
							<button class="btn btn-icon btn-remove remove-property" type="button">
								<i class="fa fa-remove"></i>
							</button>
						</div>
					</div>
				</div>

			<?php endforeach; ?>

		</div><!-- .triggers-wrapper -->

		<div class="form-group">
			<div class="col-md-4 col-md-offset-3">
				<select name="" id="dynamic-event-triggers-selector" class="form-control">
					<option value="" selected disabled class="empty">Please, select condition type...</option>
					<option value="url-click">On URL click</option>
					<option value="css-click">Click on CSS selector</option>
					<option value="css-mouseover">Mouse over on CSS selector</option>
					<option value="scroll-pos">On scroll to position</option>
				</select>
			</div>
		</div>

	</div>
</div>

<!-- Dynamic URL Filters -->
<div class="row form-horizontal dynamic-event-triggers m-t-30" id="dynamic-event-url-filters" style="display: none;">
	<div class="col-xs-12">
		<h3>Event URL Filters (optional)</h3>
		<p>If you add URL filters, the event will fire only when the Trigger Condition is met on that URL.</p>

		<div class="triggers-wrapper m-t-30">

			<?php foreach ( $event->getDynamicUrlFilters() as $trigger ) : ?>

				<?php $uid = uniqid(); ?>

				<div class="event-property">
					<div class="form-group">
						<label for="fb_pixel_event_trigger_<?php esc_attr_e( $uid ); ?>" class="col-md-3 control-label">Fire on this URL only</label>
						<div class="col-md-4">
							<input type="text" class="form-control" placeholder="Enter URL" name="fb_pixel_pro[event][triggers][dynamic_url_filters][<?php esc_attr_e( $uid ); ?>]" value="<?php esc_attr_e( $trigger ); ?>" id="fb_pixel_event_trigger_<?php esc_attr_e( $uid ); ?>">
							<span class="help-block">Event will trigger when this URL is visited. If you add <code>*</code> at the end of the URL string, it will match all URLs starting with the this string.</span>
						</div>
						<div class="col-md-1">
							<button class="btn btn-icon btn-remove remove-property" type="button">
								<i class="fa fa-remove"></i>
							</button>
						</div>
					</div>
				</div>

			<?php endforeach; ?>

		</div><!-- .triggers-wrapper -->

		<div class="form-group">
			<div class="col-md-9 col-md-offset-3">
				<button class="btn btn-primary" type="button" id="add-dynamic-url-filter">
					<i class="fa fa-plus m-r-10"></i><span>Add Filter</span>
				</button>
			</div>
		</div>

	</div>
</div>

<hr>

<!-- Facebook Event Options -->
<div class="row form-horizontal facebook-event-properties">
	<div class="col-xs-12">
		<h4 style="font-weight: 400;">Define the Event</h4>

		<?php

		$facebook_event_type = $event->getFacebookEventType();

		if ( ! empty( $facebook_event_type) && $facebook_event_type !== 'CustomCode' && ! in_array( $facebook_event_type, $event->getFacebookEvents() ) ) {
			$facebook_event_type = 'CustomEvent';
		}

		?>

		<div class="form-group m-t-30 m-b-30" id="params-selector-wrapper">
			<label for="" class="col-md-3 control-label"><?php _e( 'Facebook event type', 'pys' ); ?></label>
			<div class="col-md-4">
				<select name="fb_pixel_pro[event][facebook_event_type]" id="facebook-event-type" class="form-control">
					<option value="" selected disabled>Please, select Facebook event type...</option>

					<?php foreach ( $event->getFacebookEvents() as $value => $name ): ?>
						<option value="<?php esc_attr_e( $value ); ?>" <?php selected( $facebook_event_type, $value ); ?>>
							<?php echo $name; ?>
						</option>
					<?php endforeach; ?>

					<option disabled=""></option>

					<option value="CustomEvent" <?php selected( $facebook_event_type, 'CustomEvent' ); ?>>CustomEvent</option>
					<option value="CustomCode" <?php selected( $facebook_event_type, 'CustomCode' ); ?>>CustomCode</option>

				</select>
			</div>
		</div>
		
		<div id="event-properties-wrapper" class="<?php esc_attr_e( $facebook_event_type ); ?>">

			<?php foreach ( $event->getFacebookEventPropertiesOptions() as $name => $properties ) : ?>
				<?php render_facebook_event_property( $name, $properties, $event->getFacebookEventPropertyValue( $name ), $event ); ?>
			<?php endforeach; ?>
			
			<?php foreach ( $event->getFacebookEventCustomProperties() as $name => $value ) : ?>
				<?php render_facebook_event_custom_property( $name, $value ); ?>
			<?php endforeach; ?>
			
			<div id="custom-event-properties-marker"></div>

			<!-- custom event property to clone -->
			<div class="event-property ViewContent Search AddToCart AddToWishlist InitiateCheckout AddPaymentInfo Purchase Lead CompleteRegistration CustomEvent form-group" id="facebook-event-custom-property-donor" style="display: none;">
				<label for="" class="col-md-3 control-label">Custom param</label>
				<div class="col-md-2">
					<input type="text" class="form-control" placeholder="Param name" name="custom_param_name">
				</div>
				<div class="col-md-2">
					<input type="text" class="form-control" placeholder="Param value" name="custom_param_value">
				</div>
				<div class="col-md-1">
					<button class="btn btn-icon btn-remove remove-property" type="button">
						<i class="fa fa-remove"></i>
					</button>
				</div>
			</div>

			<div class="form-group ViewContent Search AddToCart AddToWishlist InitiateCheckout AddPaymentInfo Purchase Lead CompleteRegistration CustomEvent">
				<div class="col-md-9 col-md-offset-3">
					<button class="btn btn-primary" type="button" id="add-event-property">
						<i class="fa fa-plus m-r-10"></i><span>Add Param</span>
					</button>
				</div>
			</div>
		
		</div>
        
        <?php

        // allow 3rd party add own sections
        do_action( 'pys_fb_pixel_admin_event_params_section' );

        ?>

		<p class="m-t-30"><strong>Pro Tip:</strong> use Dynamic Events to optimize your ads for key actions on your site:</p>
		<ul>
			<li>clicks on link or buttons</li>
			<li>page scroll</li>
			<li>mouse over</li>
		</ul>

	</div>
</div>

<hr>

<div class="row clearfix m-t-20">
	<div class="col-xs-12 col-md-4 col-md-offset-4">
		<button type="submit" class="btn btn-lg btn-block btn-primary btn-custom btn-cta" id="submit_event"><?php echo $submit_btn_text; ?></button>
	</div>
</div>

<!-- donors for clone -->
<div style="display: none;">

	<!-- on page url visit trigger -->
	<div class="event-property" id="on-page-url-visit-trigger">
		<div class="form-group">
			<label for="" class="col-md-3 control-label">On URL visit</label>
			<div class="col-md-4">
				<input type="text" class="form-control" placeholder="Enter URL">
				<span class="help-block">Event will trigger when this URL is visited. If you add <code>*</code> at the end of the URL string, it will match all URLs starting with the this string.</span>
			</div>
			<div class="col-md-1">
				<button class="btn btn-icon btn-remove remove-property" type="button">
					<i class="fa fa-remove"></i>
				</button>
			</div>
		</div>
	</div>

	<!-- dynamic on url click trigger -->
	<div class="event-property" id="dynamic-url-click-trigger">
		<div class="form-group">
			<label for="" class="col-md-3 control-label">Click on URL</label>
			<div class="col-md-4">
				<input type="text" class="form-control" placeholder="Enter URL">
				<input type="hidden" name="" value="url_click">
				<span class="help-block"><?php _e( 'Event will trigger when a LINK to this URL is clicked. If you add <code>*</code> at the end of the URL string, it will match all URLs starting with this string.', 'pys' ); ?></span>
			</div>
			<div class="col-md-1">
				<button class="btn btn-icon btn-remove remove-property" type="button">
					<i class="fa fa-remove"></i>
				</button>
			</div>
		</div>
	</div>

	<!-- dynamic on css click trigger -->
	<div class="event-property" id="dynamic-css-click-trigger">
		<div class="form-group">
			<label for="" class="col-md-3 control-label">Click on CSS selector</label>
			<div class="col-md-4">
				<input type="text" class="form-control" placeholder="Enter CSS selector">
				<input type="hidden" name="" value="css_click">
				<span class="help-block"><?php _e( 'The event will be fired on this CSS selector. Please read about CSS Selector <a href="http://www.pixelyoursite.com/facebook-pixel-dynamic-events" target="_blank">here</a>.', 'pys' ); ?></span>
			</div>
			<div class="col-md-1">
				<button class="btn btn-icon btn-remove remove-property" type="button">
					<i class="fa fa-remove"></i>
				</button>
			</div>
		</div>
	</div>

	<!-- dynamic on css mouseover trigger -->
	<div class="event-property" id="dynamic-css-mouseover-trigger">
		<div class="form-group">
			<label for="" class="col-md-3 control-label">Mouseover on CSS selector</label>
			<div class="col-md-4">
				<input type="text" class="form-control" placeholder="Enter CSS selector">
				<input type="hidden" name="" value="css_mouseover">
				<span class="help-block"><?php _e( 'The event will be fired when mouse over on this CSS selector. Please read about CSS Selector <a href="http://www.pixelyoursite.com/facebook-pixel-dynamic-events" target="_blank">here</a>.', 'pys' ); ?></span>
			</div>
			<div class="col-md-1">
				<button class="btn btn-icon btn-remove remove-property" type="button">
					<i class="fa fa-remove"></i>
				</button>
			</div>
		</div>
	</div>

	<!-- dynamic on scroll position trigger -->
	<div class="event-property" id="dynamic-scroll-pos-trigger">
		<div class="form-group">
			<label for="" class="col-md-3 control-label">Page scrolled to position</label>
			<div class="col-md-4">
				<input type="text" class="form-control" placeholder="Enter page scroll position in %">
				<input type="hidden" name="" value="scroll_pos">
				<span class="help-block"><?php _e( 'The event will be fired when page scrolled to desired position.', 'pys' ); ?></span>
			</div>
			<div class="col-md-1">
				<button class="btn btn-icon btn-remove remove-property" type="button">
					<i class="fa fa-remove"></i>
				</button>
			</div>
		</div>
	</div>

	<!-- dynamic url filter trigger -->
	<div class="event-property" id="dynamic-url-filter-trigger">
		<div class="form-group">
			<label for="" class="col-md-3 control-label">On URL visit</label>
			<div class="col-md-4">
				<input type="text" class="form-control" placeholder="Enter URL">
				<span class="help-block">Event will trigger when this URL is visited. If you add <code>*</code> at the end of the URL string, it will match all URLs starting with the this string.</span>
			</div>
			<div class="col-md-1">
				<button class="btn btn-icon btn-remove remove-property" type="button">
					<i class="fa fa-remove"></i>
				</button>
			</div>
		</div>
	</div>

</div>

<script type="text/javascript">
	jQuery(document).ready(function ($) {

		toggle_triggers_sections();

		function make_unique_id() {
			var id = "";
			var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

			for (var i = 0; i < 10; i++)
				id += possible.charAt(Math.floor(Math.random() * possible.length));

			// add forward underscore to avoid conflicts with saved fields
			return '_' + id;

		}

		function toggle_triggers_sections() {

			var event_type = $('input[name="fb_pixel_pro[event][type]"]:checked').val();

			// hide conditions sections
			$('.on-page-event-triggers, .dynamic-event-triggers').hide('fast');

			// show corresponding sections
			if (event_type == 'on_page') {
				$('.on-page-event-triggers').show('fast');
			} else if (event_type == 'dynamic') {
				$('.dynamic-event-triggers').show('fast');
			}

			// add single on page trigger by default
			if( $('.on-page-event-triggers .event-property').length == 0 ) {
				add_on_page_event_trigger();
			}

		}

		function add_on_page_event_trigger() {
			var elem = $('#on-page-url-visit-trigger').clone(true).removeAttr('id').appendTo('#on-page-event-triggers .triggers-wrapper');
			set_event_trigger_attributes(elem, 'on_page');
		}

		function add_dynamic_event_trigger(trigger_type) {

			var id_to_clone = '#dynamic-' + trigger_type + '-trigger';
			var elem = $(id_to_clone).clone(true).removeAttr('id').appendTo('#dynamic-event-triggers .triggers-wrapper');
			set_event_trigger_attributes(elem, 'dynamic');

		}

		// set ID and Name attributes for cloned trigger
		function set_event_trigger_attributes(elem, type) {

			// prepare attributes
			var trigger_id = make_unique_id(),
				label_for = 'fb_pixel_event_trigger_' + trigger_id,
				field_id = 'fb_pixel_event_trigger_' + trigger_id;

			// save attributes
			$('label', elem).attr('for', label_for);

			if( type == 'dynamic' ) {

				// eg. fb_pixel[event][triggers][dynamic][xyz][value]

				var value_field_name = 'fb_pixel_pro[event][triggers][' + type + '][' + trigger_id + '][value]',
					type_field_name = 'fb_pixel_pro[event][triggers][' + type + '][' + trigger_id + '][type]';

				$('input[type="text"]', elem).attr('id', field_id).attr('name', value_field_name);
				$('input[type="hidden"]', elem).attr('id', field_id).attr('name', type_field_name);

			} else {

				var field_name = 'fb_pixel_pro[event][triggers][' + type + '][' + trigger_id + ']';
				$('input[type="text"]', elem).attr('id', field_id).attr('name', field_name);

			}

		}

		// toggle event trigger conditions sections
		$('input[name="fb_pixel_pro[event][type]"]').change(function () {
			toggle_triggers_sections();
		});

		$('#add-on-page-trigger').click(function () {
			add_on_page_event_trigger();
		});

		function update_dynamic_trigger_selector() {

			var elem = $('#dynamic-event-triggers-selector'),
				triggers_count = $('.event-property', '#dynamic-event-triggers').length;

			if( triggers_count > 0 ) {
				$('option.empty', elem).text('Add an extra condition (optional)');
			} else {
				$('option.empty', elem).text('Please, select condition type...');
			}

			elem.val('');

		}

		$('#dynamic-event-triggers-selector').change(function () {

			var selector = $(this),
				trigger_type = selector.val();

			add_dynamic_event_trigger(trigger_type);
			update_dynamic_trigger_selector();

		});

		$('#add-dynamic-url-filter').click(function () {
			var elem = $('#dynamic-url-filter-trigger').clone(true).removeAttr('id').appendTo('#dynamic-event-url-filters .triggers-wrapper');
			set_event_trigger_attributes(elem, 'dynamic_url_filters');
		});

		$('.remove-property').click(function () {

			$(this).closest('div.event-property').toggle('fast', function () {
				$(this).remove();
				update_dynamic_trigger_selector();
			});

		});

		// toggle facebook event properties visibility depends on facebook event type
		$('#facebook-event-type').change(function () {
			$('#event-properties-wrapper').removeClass().addClass( $(this).val() );
		});
		
		// toggle facebook event custom currency field
		$('#fb_pixel_event_currency').change(function () {
			
			if ($(this).val() == 'custom') {
				$(this).closest('.event-property').addClass('custom-currency');
			} else {
				$(this).closest('.event-property').removeClass('custom-currency');
			}
			
		});

		// add custom facebook event property
		$('#add-event-property').click(function () {

			// clone donor
			var new_custom_param = $('#facebook-event-custom-property-donor').clone(true).removeAttr('id');

			// prepare attributes
			var field_id = make_unique_id(),
				label_for = 'fb_pixel_custom_event_property_' + field_id,
				name_field_id = 'fb_pixel_custom_event_property_' + field_id + '_name',
				name_field_name = 'fb_pixel_pro[event][facebook_event_custom_properties][' + field_id + '][name]',
				value_field_id = 'fb_pixel_custom_event_property_' + field_id + '_value',
				value_field_name = 'fb_pixel_pro[event][facebook_event_custom_properties][' + field_id + '][value]';

			// save attributes
			$('label', new_custom_param).attr('for', label_for);
			$('input[name="custom_param_name"]', new_custom_param).attr('id', name_field_id).attr('name', name_field_name);
			$('input[name="custom_param_value"]', new_custom_param).attr('id', value_field_id).attr('name', value_field_name);

			// insert new element
			new_custom_param.insertBefore($('#custom-event-properties-marker')).toggle();
			
		});

		$('#fb_pixel_event_currency').trigger('change');

		$('form', '.main-content').submit(function (e){
			
			var is_valid = true,
				facebook_event_type = $('#facebook-event-type').val(),
				facebook_custom_event_name = $('#fb_pixel_event__custom_event_name').val(),
				facebook_custom_code = $('#fb_pixel_event__custom_code').val();

			if(facebook_event_type == null) {

				is_valid = false;

				swal({
					title: 'Oops...',
					text: 'Facebook event type is not defined!',
					type: 'warning'
				});

			} else if (facebook_event_type == 'CustomEvent' && facebook_custom_event_name.length == 0) {

				is_valid = false;

				swal({
					title: 'Oops...',
					text: 'Facebook custom event name is not defined!',
					type: 'warning'
				});

			} else if (facebook_event_type == 'CustomCode' && facebook_custom_code.length == 0) {

				is_valid = false;

				swal({
					title: 'Oops...',
					text: 'Facebook custom event code is not defined!',
					type: 'warning'
				});

			}

			console.log(is_valid);

			return is_valid;

		});

	});
</script>