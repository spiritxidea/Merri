<?php

namespace PixelYourSite\FacebookPixelPro;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function license_activate( $license_key ) {
	
	$api_params = array(
		'edd_action' => 'activate_license',
		'license'    => $license_key,
		'item_name'  => urlencode( PYS_FB_PIXEL_ITEM_NAME ),
		'url'        => home_url()
	);
	
	$response = wp_remote_post( PYS_FB_PIXEL_STORE_URL, array(
		'timeout'   => 120,
		'sslverify' => false,
		'body'      => $api_params
	) );
	
	if ( is_wp_error( $response ) ) {
		return $response;
	}
    
    // $license_data->license will be either "valid" or "invalid"
    return json_decode( wp_remote_retrieve_body( $response ) );

}

function license_deactivate( $license_key ) {

	$api_params = array(
		'edd_action' => 'deactivate_license',
		'license'    => $license_key,
		'item_name'  => urlencode( PYS_FB_PIXEL_ITEM_NAME ),
		'url'        => home_url()
	);
	
	$response = wp_remote_post( PYS_FB_PIXEL_STORE_URL, array(
		'timeout'   => 120,
		'sslverify' => false,
		'body'      => $api_params
	) );
	
	if ( is_wp_error( $response ) ) {
		return $response;
	}
	
	// $license_data->license will be either "deactivated" or "failed"
    return json_decode( wp_remote_retrieve_body( $response ) );

}