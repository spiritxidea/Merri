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
		<p>ViewContent will be fired on Product Pages and it is required for Facebook Dynamic Product Ads.</p>

		<div class="form-group">
			<div class="col-xs-12">
				<?php $this->render_switchery_html( 'woo_view_content_enabled', 'Enable ViewContent on Product Pages' ); ?>
			</div>
		</div>
	</div>
</div>

<div class="row form-horizontal">
	<div class="col-xs-12">

		<div class="form-group">
			<label for="" class="col-md-1 col-md-offset-2 control-label">Delay</label>
			<div class="col-md-4">
				<?php $this->render_text_html( 'woo_view_content_delay' ); ?>
				<span class="help-block">Seconds</span>
			</div>
		</div>

	</div>
</div>

<div class="row form-horizontal">
	<div class="col-xs-12">

		<div class="form-group switcher">
			<div class="col-xs-12">
				<?php $this->render_switchery_html( 'woo_view_content_value_enabled', 'Enable Value' ); ?>
				<span class="help-block">Add value and currency - Important for ROI measurement</span>
			</div>
		</div>

		<div class="form-group after-switcher">
			<div class="col col-md-4">
				<div class="radio">
					<?php $this->render_radio_html( 'woo_view_content_value_option', 'price', 'Product price' ); ?>
				</div>
			</div>
		</div>

		<div class="form-group after-switcher">
			<div class="col col-md-4">
				<div class="radio">
					<?php $this->render_radio_html( 'woo_view_content_value_option', 'percent', 'Percent of product price' ); ?>
				</div>
				<?php $this->render_text_html( 'woo_view_content_value_percent' ); ?>
			</div>
		</div>

		<div class="form-group after-switcher">
			<div class="col col-md-4">
				<div class="radio">
					<?php $this->render_radio_html( 'woo_view_content_value_option', 'global', 'Use Global value' ); ?>
				</div>
				<?php $this->render_text_html( 'woo_view_content_value_global' ); ?>
			</div>
		</div>

	</div>
</div>
