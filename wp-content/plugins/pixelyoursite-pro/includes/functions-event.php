<?php

namespace PixelYourSite\FacebookPixelPro;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * @param $name
 * @param $properties
 * @param $value
 * @param Event $event
 */
function render_facebook_event_property( $name, $properties, $value, &$event ) {

	if( empty( $properties ) ) {
		return;
	}
	
	$visibility  = implode( ' ', $properties['visibility'] );
	$title       = $properties['title'];
	$description = $properties['description'];
	$id          = 'fb_pixel_event_' . $name;
	
	?>
	
	<div class="event-property <?php esc_attr_e( $visibility ); ?>">
		<div class="form-group">
			<label for="<?php esc_attr_e( $id ); ?>" class="col-md-3 control-label"><?php echo $title; ?></label>
			<div class="col-md-4">

				<?php if( $name == '_custom_code' ) { ?>

					<textarea name="fb_pixel_pro[event][facebook_event_properties][<?php esc_attr_e( $name ); ?>]" id="<?php esc_attr_e( $id ); ?>" rows="10" class="form-control"><?php echo stripslashes( $value ); ?></textarea>

				<?php } elseif( $name == 'currency' ) { ?>

					<?php

					$currency = $event->getCurrency();
					$is_custom = empty( $currency ) ? false : $event->isCustomCurrency();

					$custom_currency = $is_custom ? $currency : '';

					?>
					<select name="fb_pixel_pro[event][facebook_event_properties][<?php esc_attr_e( $name ); ?>]" id="<?php esc_attr_e( $id ); ?>" class="form-control">
						<option selected disabled>Please, select currency...</option>

						<?php foreach ( Event::getCurrencies() as $currency_code => $currency_name ): ?>
							<option value="<?php esc_attr_e( $currency_code ); ?>" <?php selected( $value, $currency_code ); ?>><?php echo $currency_name; ?></option>
						<?php endforeach; ?>

						<option disabled></option>
						<option value="custom" <?php selected( $is_custom ); ?>>Custom currency</option>
					</select>

					<input type="text" class="form-control" placeholder="Enter custom currency value" name="fb_pixel_pro[event][facebook_event_properties][_custom_currency]" value="<?php esc_attr_e( stripslashes( $custom_currency ) ); ?>" id="custom-currency" style="margin-top: 15px;">

				<?php } else { ?>

					<input type="text" name="fb_pixel_pro[event][facebook_event_properties][<?php esc_attr_e( $name ); ?>]" id="<?php esc_attr_e( $id ); ?>"
				       value="<?php esc_attr_e( stripslashes( $value ) ); ?>" class="form-control">

				<?php } ?>

				<span class="help-block"><?php echo $description; ?></span>
			</div>
		</div>
	</div>

	<?php	
}

function render_facebook_event_custom_property( $name, $value ) {
	
	$unique_id = uniqid();
	
	?>

	<div class="event-property ViewContent Search AddToCart AddToWishlist InitiateCheckout AddPaymentInfo Purchase Lead CompleteRegistration CustomEvent form-group">
		<label for="fb_pixel_custom_event_property_<?php esc_attr_e( $name ); ?>_name" class="col-md-3 control-label">Custom param</label>
		<div class="col-md-2">
			<input type="text" class="form-control" placeholder="Param name" name="fb_pixel_pro[event][facebook_event_custom_properties][<?php echo $unique_id; ?>][name]" id="fb_pixel_custom_event_property_<?php esc_attr_e( $name ); ?>_name" value="<?php esc_attr_e( stripslashes( $name ) ); ?>">
		</div>
		<div class="col-md-2">
			<input type="text" class="form-control" placeholder="Param value" name="fb_pixel_pro[event][facebook_event_custom_properties][<?php echo $unique_id; ?>][value]" id="fb_pixel_custom_event_property_<?php esc_attr_e( $name ); ?>_value" value="<?php esc_attr_e( stripslashes( $value ) ); ?>">
		</div>
		<div class="col-md-1">
			<button class="btn btn-icon btn-remove remove-property" type="button">
				<i class="fa fa-remove"></i>
			</button>
		</div>
	</div>

	<?php
}