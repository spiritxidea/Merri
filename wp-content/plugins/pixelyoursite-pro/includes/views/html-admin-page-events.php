<?php

namespace PixelYourSite\FacebookPixelPro;

use PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/** @var Addon $this */

$add_event_url = admin_url( 'admin.php?page=fb_pixel_pro&tab=events&action=edit' );

?>

<div class="row form-horizontal">
	<div class="col-xs-12">
		<h2>Facebook Events</h2>

		<div class="form-group switcher">
			<div class="col-xs-12">
				<?php $this->render_switchery_html( 'events_enabled', 'Enabled' ); ?>
			</div>
		</div>
		
	</div>
</div>

<div class="row form-inline m-b-15">
	<div class="col-xs-12">
		<div class="form-group">
			<a href="<?php echo $add_event_url; ?>" class="btn btn-primary">Add Event</a>
			<button class="btn btn-danger" name="bulk_delete_events" type="submit">Delete Selected</button>
		</div>
	</div>
</div>

<div class="row m-b-30">
	<div class="col-xs-12 table-responsive">
		<table class="table">
			<thead>
			<tr>
				<th style="width: 45px;">
					<div class="checkbox">
						<input type="checkbox" name="" id="pys_select_all" value="1">
						<label for="pys_select_all" class="control-label"></label>
					</div>
				</th>
				<th style="width: 150px;">Name</th>
				<th style="width: 100px;">Type</th>
				<th style="width: 150px;">FB Event</th>
				<th>Trigger</th>
				<th style="width: 100px;">Code</th>
				<th style="width: 135px;">Actions</th>
			</tr>
			</thead>
			<tbody>

			<?php foreach ( EventsFactory::get() as $event ) : ?>

				<?php
				
				/** @var Event $event */
				
				$type_pretty = $event->getType() == 'on_page' ? 'On Page' : 'Dynamic';
				$state_class = $event->getState() == 'active' ? 'pause' : 'play';

				$edit_url = add_query_arg( array(
					'event_id' => $event->getId()
				), admin_url( 'admin.php?page=fb_pixel_pro&tab=events&action=edit' ) );

				$clone_url = add_query_arg( array(
					'event_id'           => $event->getId(),
					'clone_event' => true,
					'_wpnonce'           => wp_create_nonce( 'clone_event_' . $event->getId() )
				), admin_url( 'admin.php?page=fb_pixel_pro&tab=events' ) );

				$toggle_url = add_query_arg( array(
					'event_id' => $event->getId(),
					'toggle_event_state'   => true,
					'_wpnonce' => wp_create_nonce( 'toggle_event_state_' . $event->getId() )
				), admin_url( 'admin.php?page=fb_pixel_pro&tab=events' ) );

				$delete_url = add_query_arg( array(
					'event_id'    => $event->getId(),
					'delete_event' => true,
					'_wpnonce'    => wp_create_nonce( 'delete_event_' . $event->getId() )
				), admin_url( 'admin.php?page=fb_pixel_pro&tab=events' ) );

				?>

				<tr class="<?php esc_attr_e( $event->getState() ); ?>">
					<td>
						<div class="checkbox">
							<input type="checkbox" name="selected_events[]" id="event_<?php esc_attr_e( $event->getId() ); ?>" value="<?php esc_attr_e( $event->getId() ); ?>" class="pys-event-selector">
							<label for="event_<?php esc_attr_e( $event->getId() ); ?>" class="control-label"></label>
						</div>
					</td>
					<td><?php echo $event->getTitle(); ?></td>
					<td><?php echo $type_pretty; ?></td>
					<td><?php echo $event->getFacebookEventType(); ?></td>
					<td><?php echo render_custom_event_trigger_conditions( $event ); ?></td>
					<td>
						<small class="code-preview-action code-preview" data-toggle="tooltip" data-placement="bottom" title="<?php echo get_event_code_preview( $event ); ?>">Show</small>&nbsp;
						<small class="code-preview-action code-copy">Copy</small>
					</td>
					<td>
						<a class="btn btn-icon btn-default btn-xs" href="<?php echo $edit_url; ?>">
							<i class="fa fa-pencil"></i>
						</a>
						<a class="btn btn-icon btn-default btn-xs" href="<?php echo $clone_url; ?>">
							<i class="fa fa-clone"></i>
						</a>
						<a class="btn btn-icon btn-<?php esc_attr_e( $state_class ); ?> btn-xs" href="<?php echo $toggle_url; ?>">
							<i class="fa fa-<?php esc_attr_e( $state_class ); ?>"></i>
						</a>
						<a class="btn btn-icon btn-danger btn-xs" href="<?php echo $delete_url; ?>">
							<i class="fa fa-remove"></i>
						</a>
					</td>
				</tr>

			<?php endforeach; ?>

			</tbody>
		</table>
	</div>
</div>

<?php

// allow 3rd party add own sections
do_action( 'pys_fb_pixel_admin_events_list', $this );

?>

<div class="row">
	<div class="col-xs-12">
		<p>Take advantage of pixel events, because they are proved to increase ads profitability.</p>
		<p>You can use them to:</p>
		<ul>
			<li>optimize the ads,</li>
			<li>to track ads conversion,</li>
			<li>to create <a href="http://www.pixelyoursite.com/custom-audiences-from-events" target="_blank">Custom Audiences</a>.</li>
		</ul>
		<p>There are two ways to fire events on your website:</p>
		<p>1. Fire Events on Page Load - when a particular page URL is loaded.</p>
		<p>2. Fire Dynamic Events (pro feature) when a user performs an action like:</p>
		<ul>
			<li>Click on an HTML link</li>
			<li>Click on a CSS element, like form button (contact, popup, newsletter and so on)</li>
			<li>Page scroll</li>
			<li>Mouse over</li>
		</ul>
		<p>Dynamic Events are useful for an affiliate site, newsletter signups and all sorts of conversions.</p>
		<p><strong>Important:</strong> If you use WooCommerce or EDD, you don't have to manually add the required events, because the plugin does that automatically. You can configure them from the dedicated tab.</p>
	</div>
</div>

<?php PixelYourSite\render_general_button( 'Save Settings' ); ?>
<?php render_ecommerce_plugins_notice(); ?>

<script type="text/javascript">
	jQuery(document).ready(function ($) {

		function copyToClipboard(value) {
			var $temp = $("<input>");
			$("body").append($temp);
			$temp.val(value).select();
			document.execCommand("copy");
			$temp.remove();
		}

		$('.code-copy').click(function (e) {
			var code = $(this).prev('small.code-preview').data('original-title');
			copyToClipboard(code);
		});

		$('#pys_select_all').change(function () {

			if( $(this).prop('checked') ) {
				$('.pys-event-selector').prop('checked', 'checked');
			} else {
				$('.pys-event-selector').removeAttr('checked');
			}

		});

	});
</script>
