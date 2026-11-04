<?php
/**
 * Event data shaped for the block editor preview (REST).
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

class ELB_Event_Editor_Data { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/**
	 * Format one event for the block editor. Organizers and tags are Pro-only and not included.
	 *
	 * @param int $post_id Post ID.
	 * @return array<string, mixed>
	 */
	public static function format_event_for_editor( $post_id ) {
		$post_id = (int) $post_id;
		$post    = get_post( $post_id );
		if ( ! $post ) {
			return [];
		}

		$dates       = self::editor_dates( $post_id );
		$venue_parts = ELB_Venue_Repository::get_event_venue_parts( $post_id );

		$image_url    = ELB_Event_Repository::get_event_image_url( $post_id, [ 'medium_large', 'medium', 'large' ] );
		$is_protected = post_password_required( $post );

		$cost = ELB_Event_Repository::get_event_cost( $post_id );

		$head = [
			'id'           => $post_id,
			'post_type'    => $post->post_type,
			'title'        => $post->post_title,
			'permalink'    => get_permalink( $post_id ),
			'start_date'   => $dates['start'],
			'end_date'     => $dates['end'],
			'display_date' => $dates['display'],
			'time_short'   => ELB_Event_Formatter::get_event_time_display( $post_id ),
			'time_custom'  => ELB_Event_Repository::get_event_meta( $post_id, 'elb_event_time' ),
			'all_day'      => ELB_Event_Repository::is_all_day( $post_id ),
			'image_url'    => $image_url ? esc_url_raw( $image_url ) : '',
			'image_alt'    => self::editor_image_alt( $post ),
		];
		$tail = [
			'cost'            => (string) $cost,
			'excerpt'         => self::editor_excerpt( $post ),
			'description'     => $is_protected ? '' : ELB_Event_Formatter::excerpt( $post_id, 0, false, 'full' ),
			'manual_excerpt'  => ( $is_protected || ! has_excerpt( $post->ID ) ) ? '' : wp_strip_all_tags( $post->post_excerpt ),
			'categories'      => self::editor_categories( $post_id ),
		];

		return array_merge( $head, self::editor_venue( $venue_parts ), $tail );
	}

	/**
	 * Event categories shaped for the editor.
	 *
	 * @param int $post_id Event post id.
	 * @return array<int,mixed>
	 */
	private static function editor_categories( int $post_id ): array {
		$categories = [];
		foreach ( ELB_Event_Repository::get_event_category_terms( $post_id ) as $term ) {
			$categories[] = ELB_Category::format_category_term( $term );
		}
		return $categories;
	}

	/**
	 * Venue fields of the editor payload.
	 *
	 * @param array<string,string> $venue_parts Result of ELB_Venue_Repository::get_event_venue_parts().
	 * @return array<string,string>
	 */
	private static function editor_venue( array $venue_parts ): array {
		$name = $venue_parts['name'];
		return [
			'venue'      => $name,
			'venue_full' => $venue_parts['full'],
			'venue_city' => $venue_parts['city'],
		];
	}

	/**
	 * Alt text of the featured image, or the event title when there is none.
	 *
	 * @param WP_Post $post Event post.
	 */
	private static function editor_image_alt( $post ): string {
		$thumb_id = (int) get_post_thumbnail_id( $post->ID );
		return ELB_Event_Repository::image_alt( $thumb_id, $post->post_title );
	}

	/**
	 * The excerpt, or the first 20 words of the content (nothing for password-protected events;
	 * ELB_Event_Formatter::excerpt() already checks post_password_required() itself).
	 *
	 * @param WP_Post $post Event post.
	 */
	private static function editor_excerpt( $post ): string {
		return ELB_Event_Formatter::excerpt( (int) $post->ID, 20 );
	}

	/**
	 * Raw start / end dates and the "start – end" text shown in the editor.
	 *
	 * @param int $post_id Event post ID.
	 * @return array{start:string,end:string,display:string}
	 */
	private static function editor_dates( int $post_id ): array {
		$date_format = ELB_Date_Format_Resolver::resolve_date_format( 'site' ) . ' ' . ELB_Date_Format_Resolver::resolve_time_format( 'site' );
		$start_date  = ELB_Event_Repository::get_event_meta( $post_id, '_EventStartDate' );
		$end_date    = ELB_Event_Repository::get_event_meta( $post_id, ELB_Event_Repository::END_DATE_KEY );

		$display_start = ELB_Event_Formatter::get_event_start_date( $post_id, $date_format );
		// Same $date_format as the start date, so the two never disagree in format; keeps TEC's
		// own end-of-day/multiday adjustment (tribe_get_display_end_date() already shifts the
		// displayed end date back a day when it falls on the EOD cutoff of a multiday event).
		$display_end = tribe_get_display_end_date( $post_id, true, $date_format );

		$display_date = $display_start;
		if ( $display_end && $display_end !== $display_start ) {
			$display_date .= ' – ' . $display_end;
		}

		return [
			'start'   => $start_date,
			'end'     => $end_date,
			'display' => $display_date,
		];
	}

}
