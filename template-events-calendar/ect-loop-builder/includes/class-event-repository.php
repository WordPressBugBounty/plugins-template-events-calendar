<?php
/**
 * Event data access: TEC meta, timestamps, cost, terms and image (no formatting).
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Event_Repository { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/** TEC event end-date meta key. */
	const END_DATE_KEY = '_EventEndDate';

	/** TEC event category taxonomy. */
	const CATEGORY_TAXONOMY = 'tribe_events_cat';

	public static function get_event_title( $id ) {
		return get_the_title( $id );
	}

	/**
	 * Single TEC meta value of an event as a string, read once per request.
	 *
	 * @param int    $id  Event post ID.
	 * @param string $key Meta key.
	 * @return string
	 */
	public static function get_event_meta( int $id, string $key ): string {
		static $cache = [];
		if ( ! isset( $cache[ $id ][ $key ] ) ) {
			$cache[ $id ][ $key ] = (string) get_post_meta( $id, $key, true );
		}
		return $cache[ $id ][ $key ];
	}

	/**
	 * Start and end timestamps of an event, read once per request from TEC meta (event-local times).
	 *
	 * @param int $id Event post ID.
	 * @return array{0:int|null,1:int|null,2:DateTimeZone} [ start, end, event timezone ].
	 */
	public static function get_event_timestamps( $id ): array {
		static $cache = [];
		static $zones = null;
		$id = (int) $id;
		if ( ! isset( $cache[ $id ] ) ) {
			if ( null === $zones ) {
				$zones = array_flip( timezone_identifiers_list() );
			}
			$tz_name = self::get_event_meta( $id, '_EventTimezone' );
			$tz      = ( $tz_name && isset( $zones[ $tz_name ] ) ) ? new DateTimeZone( $tz_name ) : wp_timezone();
			$ts      = static function ( string $key ) use ( $id, $tz ): ?int {
				$raw = self::get_event_meta( $id, $key );
				if ( '' === $raw ) {
					return null;
				}
				try {
					return ( new DateTimeImmutable( $raw, $tz ) )->getTimestamp();
				} catch ( Exception $e ) {
					return null;
				}
			};
			$cache[ $id ] = [ $ts( '_EventStartDate' ), $ts( self::END_DATE_KEY ), $tz ];
		}
		return $cache[ $id ];
	}

	/**
	 * Get event cost/price.
	 */
	public static function get_event_cost( $id ) {
		static $cache = [];
		$id = (int) $id;
		if ( ! array_key_exists( $id, $cache ) ) {
			$cache[ $id ] = tribe_get_cost( $id, true );
		}
		return $cache[ $id ];
	}

	/**
	 * Get event category terms.
	 *
	 * @param int $post_id Event post ID.
	 * @return WP_Term[]
	 */
	public static function get_event_category_terms( $post_id ) {
		return self::get_post_terms( $post_id, self::CATEGORY_TAXONOMY );
	}

	/**
	 * Terms of one taxonomy for a post.
	 *
	 * @param int    $post_id  Post ID.
	 * @param string $taxonomy Taxonomy slug.
	 * @return WP_Term[]
	 */
	private static function get_post_terms( $post_id, string $taxonomy ) {
		$terms = get_the_terms( (int) $post_id, $taxonomy );
		return ( ! is_wp_error( $terms ) && ! empty( $terms ) ) ? (array) $terms : [];
	}

	/**
	 * Default placeholder image used when an event has no featured image.
	 */
	public static function get_default_event_image_url() {
		return ELB_URL . 'assets/images/event-placeholder.svg';
	}

	/**
	 * Event image URL with placeholder fallback.
	 *
	 * @param int          $id   Post ID.
	 * @param string|array $size Image size, or ordered list of sizes to try.
	 * @return string
	 */
	public static function get_event_image_url( $id, $size = 'large' ) {
		$id = (int) $id;
		if ( $id <= 0 ) {
			return self::get_default_event_image_url();
		}

		$candidates = is_array( $size ) ? $size : [ $size ];
		foreach ( $candidates as $candidate ) {
			$url = get_the_post_thumbnail_url( $id, $candidate );
			if ( $url ) {
				return $url;
			}
		}

		return self::get_default_event_image_url();
	}

	/**
	 * Whether the event is flagged All Day (_EventAllDay = yes).
	 *
	 * @param int $post_id Event post ID.
	 */
	public static function is_all_day( int $post_id ): bool {
		return 'yes' === self::get_event_meta( $post_id, '_EventAllDay' );
	}

	/**
	 * An attachment's alt text, or a fallback title when there is no attachment or no alt text set.
	 * Shared by the editor preview (featured image only) and the frontend image renderer (featured
	 * or block-configured fallback image) — each passes the title fallback it already uses so this
	 * helper doesn't have to choose between raw vs. filtered title itself.
	 *
	 * @param int    $attachment_id  Attachment (image) post ID, 0 when there is none.
	 * @param string $fallback_title Title to use when there is no attachment or no alt text.
	 * @return string
	 */
	public static function image_alt( int $attachment_id, string $fallback_title ): string {
		if ( ! $attachment_id ) {
			return $fallback_title;
		}
		$alt = trim( (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) );
		return '' !== $alt ? $alt : $fallback_title;
	}
}
