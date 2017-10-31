<?php

namespace PixelYourSite\HeadFooter;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

use PixelYourSite;

/** @var Addon $this */

?>

<style type="text/css">
    #pys_head_footer_settings textarea {
        font-family: monospace;
    }
</style>

<div class="card-box">
    <form method="post" action="" id="pys_head_footer_settings">
        <?php wp_nonce_field( 'pys_save_settings' ); ?>

        <div class="row">
            <div class="col-xs-12">
                <h2>Head & Footer Scripts</h2>

                <p>Add any script in the Head or Footer section of your pages. You can also add custom per page scripts
                    by editing each page.</p>

                <p>For the Facebook Pixel use our <a href="<?php echo admin_url( 'admin.php?page=fb_pixel_pro' ); ?>">dedicated integration</a>
                    .</p>
            </div>
        </div>

        <div class="row">

            <div class="col-xs-12">
                <h3>Header Scripts</h3>
            </div>

            <div class="col-xs-12">
                <label for="pys_head_footer_head_any" class="control-label">Head (any device type):</label>
                <?php $this->render_textarea_html( 'head_any' ); ?>
            </div>

            <div class="col-xs-12 m-t-20">
                <label for="pys_head_footer_head_desktop" class="control-label">Head - Desktop Only:</label>
                <?php $this->render_textarea_html( 'head_desktop', null, false, 5 ); ?>
            </div>

            <div class="col-xs-12 m-t-20">
                <label for="pys_head_footer_head_mobile" class="control-label">Head - Mobile Only:</label>
                <?php $this->render_textarea_html( 'head_mobile', null, false, 5 ); ?>
            </div>

            <div class="col-xs-12 m-t-30">
                <h3>Footer Scripts</h3>
            </div>

            <div class="col-xs-12">
                <label for="pys_head_footer_footer_any" class="control-label">Footer (any device type):</label>
                <?php $this->render_textarea_html( 'footer_any' ); ?>
            </div>

            <div class="col-xs-12 m-t-20">
                <label for="pys_head_footer_footer_desktop" class="control-label">Footer - Desktop Only:</label>
                <?php $this->render_textarea_html( 'footer_desktop', null, false, 5 ); ?>
            </div>

            <div class="col-xs-12 m-t-20">
                <label for="pys_head_footer_footer_mobile" class="control-label">Footer - Mobile Only:</label>
                <?php $this->render_textarea_html( 'footer_mobile', null, false, 5 ); ?>
            </div>

        </div>

        <hr>

        <?php if( PixelYourSite\is_woocommerce_active() ) : ?>

            <div class="row m-t-20">

                <div class="col-xs-12">
                    <h2>WooCommerce Order Received Page</h2>
                    <p>Insert any script on the WooCommerce Thank You Page (order-received).</p>
                </div>

                <div class="col-xs-12">
                    <h3>Header Scripts</h3>
                </div>

                <div class="col-xs-12">
                    <label for="pys_head_footer_woo_order_received_head_any" class="control-label">Head (any device
                        type):</label>
                    <?php $this->render_textarea_html( 'woo_order_received_head_any' ); ?>
                </div>

                <div class="col-xs-12 m-t-20">
                    <label for="pys_head_footer_woo_order_received_head_desktop" class="control-label">Head - Desktop
                        Only:</label>
                    <?php $this->render_textarea_html( 'woo_order_received_head_desktop', null, false, 5 ); ?>
                </div>

                <div class="col-xs-12 m-t-20">
                    <label for="pys_head_footer_woo_order_received_head_mobile" class="control-label">Head - Mobile
                        Only:</label>
                    <?php $this->render_textarea_html( 'woo_order_received_head_mobile', null, false, 5 ); ?>
                </div>

                <div class="col-xs-12 m-t-30">
                    <h3>Footer Scripts</h3>
                </div>

                <div class="col-xs-12">
                    <label for="pys_head_footer_woo_order_received_footer_any" class="control-label">Footer (any device
                        type):</label>
                    <?php $this->render_textarea_html( 'woo_order_received_footer_any' ); ?>
                </div>

                <div class="col-xs-12 m-t-20">
                    <label for="pys_head_footer_woo_order_received_footer_desktop" class="control-label">Footer -
                        Desktop Only:</label>
                    <?php $this->render_textarea_html( 'woo_order_received_footer_desktop', null, false, 5 ); ?>
                </div>

                <div class="col-xs-12 m-t-20">
                    <label for="pys_head_footer_woo_order_received_footer_mobile" class="control-label">Footer - Mobile
                        Only:</label>
                    <?php $this->render_textarea_html( 'woo_order_received_footer_mobile', null, false, 5 ); ?>
                </div>

            </div>

            <div class="row form-horizontal">
                <div class="col-xs-12">
                    <div class="form-group switcher">
                        <div class="col-xs-12">
                            <?php $this->render_switchery_html( 'woo_order_received_disable_global', 'Disable global 
                        head and footer scripts on Order Received page' ); ?>
                        </div>
                    </div>
                </div>
            </div>

            <hr>
            
        <?php endif; ?>

        <div class="row m-t-20">
            <div class="col-xs-12">
                <?php include 'html-variables-help.php'; ?>
            </div>
        </div>

        <hr>

        <?php PixelYourSite\render_general_button( 'Save Settings' ); ?>

    </form>
</div>
