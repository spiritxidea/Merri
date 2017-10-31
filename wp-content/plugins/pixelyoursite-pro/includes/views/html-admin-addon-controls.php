<?php

namespace PixelYourSite\FacebookPixelPro;

use PixelYourSite\PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/** @var Addon $this */

$license_status = $this->get_option( 'license_status' );

$license_expires = $this->get_option( 'license_expires', null );
$license_key = $this->get_option( 'license_key' );
$license_expires_soon = false;
$license_expired = false;

if( $license_expires ) {

    $now = time();

    if( $now >= $license_expires ) {
        $license_expired = true;
    } elseif ( $now >= ( $license_expires - 30 * DAY_IN_SECONDS ) ) {
        $license_expires_soon = true;
    }

}

?>

<div class="form-group">
	<div class="col-xs-9">
		<?php $this->render_text_html( 'license_key', 'Enter your license key' ); ?>
	</div>
	<div class="col-xs-3">
		<?php if( $license_status == 'valid' ) : ?>
			<button class="btn btn-block btn-danger" name="pys_fb_pixel_pro_license_action" value="deactivate">Deactivate License</button>
		<?php else : ?>
			<button class="btn btn-block btn-primary" name="pys_fb_pixel_pro_license_action" value="activate">Activate License</button>
		<?php endif; ?>
	</div>
</div>

<?php if ( $notice = get_transient( 'pys_fb_pixel_pro_license_notice' ) ) : ?>

    <div class="row">
        <div class="col-xs-12">
            <div class="alert alert-<?php esc_attr_e( $notice['class'] ); ?>">
                <?php echo $notice['msg']; ?>
            </div>
        </div>
    </div>

    <?php delete_transient( 'pys_fb_pixel_pro_license_notice' ); ?>
<?php endif; ?>

<?php if ( $license_expires_soon ) : ?>

    <div class="row">
        <div class="col-xs-12">
            <div class="alert alert-pys-notice">
                <p>Your license key <strong>expires on <?php echo date( get_option( 'date_format' ), $license_expires ); ?></strong>. Make sure you keep everything updated and in order.</p>
                <p><a href="http://www.pixelyoursite.com/checkout/?edd_license_key=<?php esc_attr_e( $license_key ); ?>&utm_campaign=admin&utm_source=licenses&utm_medium=renew" target="_blank"><strong>Click here to renew your license now for a 40% discount</strong></a></p>
            </div>
        </div>
    </div>

<?php endif; ?>

<?php if ( $license_expired ) : ?>

    <div class="row">
        <div class="col-xs-12">
            <div class="alert alert-pys-red">
                <p><strong>Your license key is expired</strong>, so you no longer get any updates. Don't miss our last improvements and
                    make sure that everything works smoothly.</p>
                <p><a href="http://www.pixelyoursite.com/checkout/?edd_license_key=<?php esc_attr_e( $license_key ); ?>&utm_campaign=admin&utm_source=licenses&utm_medium=renew" target="_blank"><strong>Click here to renew your license now</strong></a></p>
            </div>
        </div>
    </div>

<?php endif; ?>
