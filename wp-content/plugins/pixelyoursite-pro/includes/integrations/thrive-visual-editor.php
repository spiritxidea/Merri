<?php

namespace PixelYourSite\FacebookPixelPro\TVEFix;

use PixelYourSite\FacebookPixelPro;

/**
 * Fixes Thrive Visual Editor issue.
 *
 * @see: https://bitbucket.org/pixelyoursite/pys_pro/issues/139/dynamic-events-not-working
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

add_action( 'pys_fb_pixel_manage_pixel', 'PixelYourSite\FacebookPixelPro\TVEFix\tve_fix_maybe_enable', 10, 1 );
add_filter( 'pys_fb_pixel_setting_defaults', 'PixelYourSite\FacebookPixelPro\TVEFix\tve_fix_pys_fb_pixel_setting_defaults', 10, 1 );
add_filter( 'pys_fb_pixel_settings_form_fields', 'PixelYourSite\FacebookPixelPro\TVEFix\tve_fix_pys_fb_pixel_settings_form_fields', 10, 1 );
add_action( 'pys_fb_pixel_admin_events_list', 'PixelYourSite\FacebookPixelPro\TVEFix\tve_fix_render_option', 10, 1 );

/**
 * Removes "return false;" statement from TVE's JS callbacks which prevents PYS event from firing.
 *
 * @param FacebookPixelPro\Addon $plugin
 */
function tve_fix_maybe_enable( $plugin ) {

    if( $plugin->get_option( 'events_enabled' ) && $plugin->get_option( 'tve_fix' ) ) {
        add_action( 'wp_print_footer_scripts', 'PixelYourSite\FacebookPixelPro\TVEFix\tve_fix_update_js_callbacks_code', 9 );
    }

}

function tve_fix_update_js_callbacks_code() {

    if ( empty( $GLOBALS['tve_event_manager_callbacks'] ) ) {
        return;
    }

    foreach ( $GLOBALS['tve_event_manager_callbacks'] as $key => $callback ) {
        $GLOBALS['tve_event_manager_callbacks'][ $key ] = str_replace( ';return false;}', ';}', $callback );
    }

}

function tve_fix_pys_fb_pixel_setting_defaults( $setting_defaults ) {
    
    $setting_defaults['tve_fix'] = false;
    
    return $setting_defaults;
    
}

function tve_fix_pys_fb_pixel_settings_form_fields( $settings_form_fields ) {
    
    $settings_form_fields['events']['tve_fix'] = 'checkbox';
    
    return $settings_form_fields;
    
}

/**
 * @param FacebookPixelPro\Addon $plugin
 */
function tve_fix_render_option( $plugin ) {
    ?>

    <div class="row form-horizontal">
        <div class="col-xs-12">
            <div class="form-group switcher">
                <div class="col-xs-12">
                    <?php $plugin->render_switchery_html( 'tve_fix', 'Enable Thrive Content Builder Fix' ); ?>
                    <span class="help-block">If you add an event on a Thrive Content Builder button that doesn't open a link, you
                        have to edit the button and set the Link Settings URL field value as: <code>#</code>. <a
                            href="http://www.pixelyoursite.com/docs/facebook-pixel-help/events/thrive-editor-fix"
                            target="_blank">More details here.</a></span>
                </div>
            </div>
        </div>
    </div>

    <?php
}
