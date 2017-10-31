<?php

namespace PixelYourSite\FacebookPixelPro\Export;

use PixelYourSite\FacebookPixelPro;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * @param FacebookPixelPro\Addon $plugin
 */
function edd_export_customers_to_csv( $plugin ) {
    global $wpdb;

    $csv_data = array();

    $order_statues = $plugin->get_option( 'edd_customers_export_order_statuses', array() );

    if ( empty( $order_statues ) ) {
        $order_statues = array_keys( edd_get_payment_statuses() );
    }

    $order_statues_placeholders = implode( ', ', array_fill( 0, count( $order_statues ), '%s' ) );

    // collect all unique customers by email
    $query = $wpdb->prepare( "
        SELECT  postmeta.meta_value AS email, postmeta.post_id
        FROM    $wpdb->postmeta AS postmeta
        JOIN    $wpdb->posts AS posts ON postmeta.post_id = posts.ID
        WHERE   posts.post_type = 'edd_payment'
                AND posts.post_status IN ({$order_statues_placeholders})
                AND postmeta.meta_key = '_edd_payment_user_email'
    ", $order_statues );

    $results = $wpdb->get_results( $query );

    $customers = array();

    // format data as email => [ order_ids ]
    foreach ( $results as $row ) {

        $order_ids   = isset( $customers[ $row->email ] ) ? $customers[ $row->email ] : array();
        $order_ids[] = (int) $row->post_id;

        $customers[ $row->email ] = $order_ids;

    }

    @ini_set( 'max_execution_time', 180 );

    // collect data per each customer
    foreach ( $customers as $email => $order_ids ) {

        $order_ids_placeholders = implode( ',', array_fill( 0, count( $order_ids ), '%d' ) );

        // calculate customer LTV
        $query = $wpdb->prepare( "
            SELECT  SUM( meta_value )
            FROM    $wpdb->postmeta
            WHERE   post_id IN ( {$order_ids_placeholders} )
                    AND meta_key = '_edd_payment_total'
        ", $order_ids );

        $customer_ltv = $wpdb->get_col( $query );

        // query customer data from last order
        $query = $wpdb->prepare( "
            SELECT  meta_value
            FROM    $wpdb->postmeta
            WHERE   post_id = %d
                    AND meta_key = '_edd_payment_meta'
        ", end( $order_ids ) );

        $customer_meta        = $wpdb->get_col( $query );
        $customer_meta        = maybe_unserialize( $customer_meta[0] );
        $customer_meta['ltv'] = (float) $customer_ltv[0];

        $csv_data[] = $customer_meta;

    }

    // generate file name
    $site_name = site_url();
    $site_name = str_replace( array( 'http://', 'https://' ), '', $site_name );
    $site_name = strtolower( preg_replace( "/[^A-Za-z]/", '_', $site_name ) );
    $file_name = strftime( '%Y%m%d' ) . '_' . $site_name . '_edd_customers.csv';

    // output CSV
    header( 'Content-Type: text/csv; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename=' . $file_name );

    $output = fopen( 'php://output', 'w' );

    // headings
    fputcsv( $output, array( 'email', 'phone', 'fn', 'ln', 'ct', 'st', 'country', 'zip', 'value' ) );

    // rows
    foreach ( $csv_data as $row ) {

        fputcsv( $output, array(
            $row['email'],
            '',
            isset( $row['user_info']['first_name'] ) ? $row['user_info']['first_name'] : '',
            isset( $row['user_info']['last_name'] ) ? $row['user_info']['last_name'] : '',
            isset( $row['user_info']['address']['city'] ) ? $row['user_info']['address']['city'] : '',
            isset( $row['user_info']['address']['state'] ) ? $row['user_info']['address']['state'] : '',
            isset( $row['user_info']['address']['country'] ) ? $row['user_info']['address']['country'] : '',
            isset( $row['user_info']['address']['zip'] ) ? $row['user_info']['address']['zip'] : '',
            $row['ltv']
        ) );

    }

    exit;

}