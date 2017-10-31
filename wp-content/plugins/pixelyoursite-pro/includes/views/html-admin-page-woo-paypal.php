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
		<p>You can add an event that will trigger on PayPal Standard button click.</p>

		<div class="form-group switcher">
			<div class="col-xs-12">
				<?php $this->render_switchery_html( 'woo_paypal_enabled', 'Activate PayPal Standard Events' ); ?>
			</div>
		</div>

		<div class="form-group">
			<label for="" class="col-md-3 control-label">Event type</label>
			<div class="col-md-4">
				
				<?php
				
				$this->render_select_html( 'woo_paypal_event_type', array(
					'ViewContent'          => 'ViewContent',
					'AddToCart'            => 'AddToCart',
					'AddToWishlist'        => 'AddToWishlist',
					'InitiateCheckout'     => 'InitiateCheckout',
					'AddPaymentInfo'       => 'AddPaymentInfo',
					'Purchase'             => 'Purchase',
					'Lead'                 => 'Lead',
					'CompleteRegistration' => 'CompleteRegistration',
					'disabled'             => '',
					'custom'               => 'Custom'
				) );
				
				?>
				
			</div>
		</div>
		
		<div class="form-group paypal-custom-name" style="display: none;">
			<div class="col-md-4 col-md-offset-3">
				<?php $this->render_text_html( 'woo_paypal_custom_event_type', 'Enter custom event name' ); ?>
			</div>
		</div>
		
		<div class="form-group">
			<div class="col-md-4 col-md-offset-3">
				<span class="help-block">* The PayPal Standard event will have all the parameters values specific for selected event.</span>
				<span class="help-block">* The Custom paypal event will have value, currency, content_type, content_ids.</span>
			</div>
		</div>


	</div>
</div>

<div class="row form-horizontal">
	<div class="col-xs-12">

		<div class="form-group switcher">
			<div class="col-xs-12">
				<?php $this->render_switchery_html( 'woo_paypal_value_enabled', 'Enable Value' ); ?>
			</div>
		</div>

		<div class="form-group after-switcher">
			<div class="col col-md-4">
				<div class="radio">
					<?php $this->render_radio_html( 'woo_paypal_value_option', 'price', 'Event Value = Cart Total' ); ?>
				</div>
			</div>
		</div>

		<div class="form-group after-switcher">
			<div class="col col-md-4">
				<div class="radio">
					<?php $this->render_radio_html( 'woo_paypal_value_global', 'global', 'Use Global value' ); ?>
				</div>
				<?php $this->render_text_html( 'woo_paypal_value_global' ); ?>
				<span class="help-block">* Set this if you want a unique global value every PayPal payment.</span>
			</div>
		</div>

	</div>
</div>
