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
		<p>InitiateCheckout event will be fired on the Checkout page. It is not mandatory for Facebook Dynamic Product Ads, but it is better to keep it on.</p>

		<div class="form-group">
			<div class="col-xs-12">
				<?php $this->render_switchery_html( 'woo_initiate_checkout_enabled', 'Enable InitiateCheckout on Checkout page' ); ?>
			</div>
		</div>
	</div>
</div>

<div class="row form-horizontal">
	<div class="col-xs-12">

		<div class="form-group switcher">
			<div class="col-xs-12">
				<?php $this->render_switchery_html( 'woo_initiate_checkout_value_enabled', 'Enable Value' ); ?>
				<span class="help-block">Add value and currency - Important for ROI measurement</span>
			</div>
		</div>

		<div class="form-group after-switcher">
			<div class="col col-md-4">
				<div class="radio">
					<?php $this->render_radio_html( 'woo_initiate_checkout_value_option', 'price', 'Products price (subtotal)' ); ?>
				</div>
			</div>
		</div>

		<div class="form-group after-switcher">
			<div class="col col-md-4">
				<div class="radio">
					<?php $this->render_radio_html( 'woo_initiate_checkout_value_option', 'percent', 'Percent of products value (subtotal)' ); ?>
				</div>
				<?php $this->render_text_html( 'woo_initiate_checkout_value_percent' ); ?>
			</div>
		</div>

		<div class="form-group after-switcher">
			<div class="col col-md-4">
				<div class="radio">
					<?php $this->render_radio_html( 'woo_initiate_checkout_value_option', 'global', 'Use Global value' ); ?>
				</div>
				<?php $this->render_text_html( 'woo_initiate_checkout_value_global' ); ?>
			</div>
		</div>

	</div>
</div>
