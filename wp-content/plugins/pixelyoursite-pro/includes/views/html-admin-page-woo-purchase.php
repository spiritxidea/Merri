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
		<p>Purchase event will be fired on the Thank You page. It is mandatory for Facebook Dynamic Product Ads.</p>

		<div class="form-group switcher">
			<div class="col-xs-12">
				<?php $this->render_switchery_html( 'woo_purchase_enabled', 'Enable Purchase event on Thank You page' ); ?>
			</div>
		</div>

		<div class="form-group switcher">
			<div class="col-xs-12">
				<?php $this->render_switchery_html( 'woo_purchase_on_transaction', 'Fire the event on transaction only' ); ?>
				<span class="help-block">This will avoid the Purchase event to be fired when the order-received page is visited but no transaction has occurred. <strong>It will improve conversion tracking.</strong></span>
			</div>
		</div>

	</div>
</div>

<div class="row form-horizontal">
	<div class="col-xs-12">

		<div class="form-group switcher">
			<div class="col-xs-12">
				<?php $this->render_switchery_html( 'woo_purchase_value_enabled', 'Enable Value' ); ?>
				<span class="help-block">Add value and currency - Important for ROI measurement</span>
			</div>
		</div>

		<div class="form-group after-switcher">
			<div class="col col-md-4">
				<div class="radio">
					<?php $this->render_radio_html( 'woo_purchase_value_option', 'price', 'Total' ); ?>
				</div>
			</div>
		</div>

		<div class="form-group after-switcher">
			<div class="col col-md-4">
				<div class="radio">
					<?php $this->render_radio_html( 'woo_purchase_value_option', 'percent', 'Percent of Total' ); ?>
				</div>
				<?php $this->render_text_html( 'woo_purchase_value_percent' ); ?>
			</div>
		</div>

		<div class="form-group after-switcher">
			<div class="col col-md-4">
				<div class="radio">
					<?php $this->render_radio_html( 'woo_purchase_value_option', 'global', 'Use Global value' ); ?>
				</div>
				<?php $this->render_text_html( 'woo_purchase_value_global' ); ?>
			</div>
		</div>

	</div>
</div>

<hr>

<div class="row form-horizontal">
	<div class="col-xs-12">
		<h3>Custom Audience Optimization</h3>
		<p><strong>Important:</strong> For the Purchase Event to work, the client must be redirected on the default WooCommerce Thank You page after payment.</p>

		<div class="form-group switcher">
			<div class="col-xs-12">
				<?php $this->render_switchery_html( 'woo_purchase_add_address', 'Add Town, State and Country parameters' ); ?>
				<span class="help-block">Will pull <code>town</code>, <code>state</code> and <code>country</code></span>
			</div>
		</div>

		<div class="form-group switcher">
			<div class="col-xs-12">
				<?php $this->render_switchery_html( 'woo_purchase_add_payment_method', 'Add Payment Method parameter' ); ?>
				<span class="help-block">Will pull <code>payment</code></span>
			</div>
		</div>

		<div class="form-group switcher">
			<div class="col-xs-12">
				<?php $this->render_switchery_html( 'woo_purchase_add_shipping_method', 'Add Shipping Method parameter' ); ?>
				<span class="help-block">Will pull <code>shipping</code></span>
			</div>
		</div>

		<div class="form-group switcher">
			<div class="col-xs-12">
				<?php $this->render_switchery_html( 'woo_purchase_add_coupons', 'Add Coupons parameter' ); ?>
				<span class="help-block">Will pull <code>coupon_used</code> and <code>coupon_name</code></span>
			</div>
		</div>

	</div>
</div>

<div class="row">
	<div class="col-xs-12">
		<p>
			<strong>Important:</strong> For the Purchase Event to work, the client must be redirected to the default WooCommerce Thank You page after payment.
		</p>
	</div>
</div>
