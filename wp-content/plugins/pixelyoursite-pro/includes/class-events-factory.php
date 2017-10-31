<?php

namespace PixelYourSite\FacebookPixelPro;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class EventsFactory {

	private static $cache = array();

	public static function create( $args ) {
		
		// create event post object
		$post_id = wp_insert_post( array(
			'post_title'     => isset( $args['title'] ) ? $args['title'] : '',
			'post_type'      => 'pys_fb_event',
			'post_status'    => 'publish',
			'ping_status'    => 'closed',
			'comment_status' => 'closed',
		), true );
		
		if ( is_wp_error( $post_id ) ) {
			throw new \Exception( 'Could not insert new post.' );
		}

		$event = new Event( $post_id );
		$event->update( $args );
		
		return $event;
		
	}

	public static function get( $type = 'any', $state = 'any', $post_id = null ) {

		$limit = isset( $post_id ) ? 1 : -1;

		$args = array(
			'post_type'   => 'pys_fb_event',
			'numberposts' => $limit,
			'meta_query'  => array(
				'relation' => 'AND'
			)
		);

		if( isset( $post_id ) ) {
			$args['include'] = intval( $post_id );
		}

		if ( $type !== 'any' ) {

			$args['meta_query'][] = array(
				'key'   => '_type',
				'value' => $type
			);

		}

		if ( $state !== 'any' ) {

			$args['meta_query'][] = array(
				'key'   => '_state',
				'value' => $state
			);

		}

		$posts = get_posts( $args );
		$results = array();

		foreach ( $posts as $post ) {
			self::$cache[ $post->ID ] = new Event( $post->ID );
			$results[ $post->ID ] = &self::$cache[ $post->ID ];
		}

		wp_reset_postdata();

		return $results;

	}
	
	/**
	 * @param $post_id
	 *
	 * @return bool|Event
	 */
	public static function get_by_id( $post_id ) {

		if( isset( self::$cache[ $post_id ] ) ) {
			return self::$cache[ $post_id ];
		}

		self::get( 'any', 'any', $post_id );

		if ( isset( self::$cache[ $post_id ] ) ) {
			return self::$cache[ $post_id ];
		} else {
			return false;
		}

	}
	
	public static function reset_cache( $post_id ) {
		unset( self::$cache[ $post_id ] );		
	}

	public static function toggle_state( $post_id ) {

		if ( $event = self::get_by_id( $post_id ) ) {

			$new_state = $event->getState() == 'active' ? 'paused' : 'active';
			$event->setState( $new_state );

		}

	}

	public static function clone_event( $post_id ) {

		if ( $event = self::get_by_id( $post_id ) ) {

			$args = array(
				'title' => $event->getTitle() . ' (duplicate)',
			);

			// create new event
			$new_event = self::create( $args );

			// copy meta from original event
			foreach ( get_post_meta( $event->getId() ) as $meta_key => $meta_values ) {
				foreach ( $meta_values as $meta_value ) {
					update_post_meta( $new_event->getId(), $meta_key, maybe_unserialize( $meta_value ) );
				}
			}

			// pause cloned event
			$new_event->setState( 'paused' );

		}
		
	}

	public static function remove( $post_id ) {
		wp_delete_post( $post_id, true );
	}
	
}