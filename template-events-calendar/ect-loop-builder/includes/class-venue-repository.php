<?php
/**
 * Venue parts and display-text formatting.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

class ELB_Venue_Repository { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/**
	 * Venue parts for display (matches editor REST venue_* fields).
	 *
	 * @param int $post_id Event post ID.
	 * @return array{id:int,name:string,address:string,city:string,state:string,zip:string,raw:string,full:string,permalink:string}
	 */
	public static function get_event_venue_parts( $post_id ) {
		static $cache = [];
		$post_id      = (int) $post_id;
		if ( isset( $cache[ $post_id ] ) ) {
			return $cache[ $post_id ];
		}

		$empty    = [
			'id'        => 0,
			'name'      => '',
			'city'      => '',
			'full'      => '',
			'permalink' => '',
		];
		$venue_id = (int) get_post_meta( $post_id, '_EventVenueID', true );
		if ( $venue_id ) {
			$venue_obj = tribe_get_venue_object( $venue_id );
			if ( $venue_obj ) {
				return $cache[ $post_id ] = self::parts_from_venue_object( $venue_obj );
			}
		}

		$name = wp_strip_all_tags( (string) tribe_get_venue( $post_id ) );
		return $cache[ $post_id ] = array_merge(
			$empty,
			[
				'name' => $name,
				'full' => $name,
			]
		);
	}

	/**
	 * Venue name for an event (TEC formatting, unescaped).
	 *
	 * @param int $post_id Event post ID.
	 */
	public static function get_venue_name( $post_id ) {
		return tribe_get_venue( $post_id );
	}

	/**
	 * Venue parts read from a TEC venue object.
	 *
	 * @param object $venue_obj Venue post with TEC address properties.
	 * @return array{id:int,name:string,city:string,full:string,permalink:string}
	 */
	private static function parts_from_venue_object( $venue_obj ): array {
		$venue_id = (int) ( $venue_obj->ID ?? 0 );
		$raw      = (string) $venue_obj->post_title;
		$address  = ! empty( $venue_obj->address ) ? (string) $venue_obj->address : '';
		$city     = ! empty( $venue_obj->city ) ? (string) $venue_obj->city : '';
		$state    = ! empty( $venue_obj->stateprovince ) ? (string) $venue_obj->stateprovince : '';
		$zip      = ! empty( $venue_obj->zip ) ? (string) $venue_obj->zip : '';

		$parts = array_filter( [
			$raw,
			$address,
			trim( $city . ( $state ? ', ' . $state : '' ) . ( $zip ? ' ' . $zip : '' ) ),
		] );
		$full = implode( ', ', $parts );

		return [
			'id'        => $venue_id,
			'name'      => $raw,
			'city'      => $city,
			'full'      => $full ?: $raw,
			'permalink' => self::get_venue_permalink( $venue_id ),
		];
	}

	/**
	 * Venue permalink for a resolved venue ID, or ''.
	 *
	 * @param int $venue_id Venue post ID.
	 * @return string
	 */
	private static function get_venue_permalink( int $venue_id ): string {
		if ( $venue_id < 1 ) {
			return '';
		}
		$permalink = get_permalink( $venue_id );
		return $permalink ? esc_url_raw( $permalink ) : '';
	}

	/**
	 * Venue text by format key (matches editor venueFormat options).
	 *
	 * @param int    $post_id Event post ID.
	 * @param string $format  full|name|city|address_city|city_state|raw.
	 * @return string
	 */
	public static function get_venue_display_text( $post_id, $format = 'full' ) {
		$post_id = (int) $post_id;
		$format  = sanitize_key( $format );
		$parts   = self::get_event_venue_parts( $post_id );
		$name    = $parts['name'];

		switch ( $format ) {
			case 'name':
				return $name;
			case 'name_city':
				$joined = implode( ', ', array_filter( [ $name, $parts['city'] ] ) );
				return $joined;
			case 'full':
			default:
				return $parts['full'];
		}
	}
}
