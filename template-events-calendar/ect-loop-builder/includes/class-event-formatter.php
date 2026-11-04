<?php
/**
 * Event display formatting: excerpt, start/end dates, time range, all-day text and field values.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Event_Formatter { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/** Source keys Free will resolve. Organizer / tags / tickets are Pro-only. */
	const FREE_SOURCES = [
		'title',
		'excerpt',
		'image',
		'date',
		'time',
		'venue',
		'cost',
		'id',
		'categories',
		'category',
		'category_time',
		'readmore',
		'share',
	];

	/**
	 * Unified excerpt resolution for frontend, editor preview, and REST.
	 *
	 * @param int    $post_id    Post ID.
	 * @param int    $words      Word count limit.
	 * @param bool   $use_manual Whether to respect the manual excerpt box.
	 * @param string $mode       'words' for truncated excerpt, 'full' for complete text.
	 * @return string
	 */
	public static function excerpt( int $post_id, int $words = 20, bool $use_manual = true, string $mode = 'words' ): string {
		if ( post_password_required( $post_id ) ) {
			return '';
		}
		$post = get_post( $post_id );
		if ( ! $post ) {
			return '';
		}
		$text = ( $use_manual && has_excerpt( $post_id ) )
			? $post->post_excerpt
			: strip_shortcodes( excerpt_remove_blocks( $post->post_content ) );
		$text = trim( wp_strip_all_tags( $text ) );
		return 'words' === $mode ? self::trim_words_clean( $text, $words ) : $text;
	}

	/**
	 * wp_trim_words() wrapper that avoids a "word.…" ending (trailing punctuation butting
	 * straight up against the ellipsis). When trimming actually happens, trailing whitespace and
	 * sentence punctuation (. , ; : ! ? – —) are stripped from the cut word before the ellipsis
	 * is appended. Text that was short enough to NOT need trimming keeps its own punctuation as
	 * written — the JS editor preview (elbTrimWords() in event-field-config.js) mirrors this.
	 *
	 * @param string $text  Already-stripped/trimmed source text.
	 * @param int    $words Word count limit.
	 * @param string $more  Appended only when trimming actually occurs.
	 * @return string
	 */
	public static function trim_words_clean( string $text, int $words, string $more = '&hellip;' ): string {
		$trimmed = wp_trim_words( $text, $words, '' );
		if ( $trimmed === $text ) {
			return $text;
		}
		return preg_replace( '/[\s.,;:!?–—]+$/u', '', $trimmed ) . $more;
	}

	/**
	 * Get event start date/time.
	 * Uses tribe_get_start_date() when available.
	 */
	public static function get_event_start_date( $id, $format = 'F j, Y' ) {
		return self::get_event_datetime( $id, $format, 'start' );
	}

	/**
	 * Get event end date/time.
	 */
	public static function get_event_end_date( $id, $format = 'F j, Y' ) {
		return self::get_event_datetime( $id, $format, 'end' );
	}

	/**
	 * Format the event start or end with a PHP date format (localized, event timezone).
	 *
	 * @param int    $id     Event post ID.
	 * @param string $format PHP date format.
	 * @param string $which  start|end.
	 * @return string
	 */
	private static function get_event_datetime( $id, $format, $which ) {
		[ $start, $end, $tz ] = ELB_Event_Repository::get_event_timestamps( $id );
		$ts                   = 'start' === $which ? $start : $end;
		return null === $ts ? '' : (string) wp_date( $format, $ts, $tz );
	}

	/**
	 * Event time string for meta badges: "8:00 am – 5:00 pm" (or start-only), no leading "@".
	 *
	 * @param int $post_id Event post ID.
	 */
	public static function get_event_time_display( int $post_id ): string {
		return ELB_Event_Time::text( $post_id );
	}

	/**
	 * Use $source when it is a Free field, otherwise $default.
	 *
	 * @param string $source  Saved sourceField.
	 * @param string $default Block's own field.
	 */
	public static function resolve_source_field( string $source, string $default ): string {
		$source  = sanitize_key( $source );
		$default = sanitize_key( $default );
		if ( '' === $source || ! in_array( $source, self::FREE_SOURCES, true ) ) {
			return '' !== $default ? $default : 'title';
		}
		return $source;
	}

	/**
	 * Resolve a dynamic source field value for loop field blocks (frontend).
	 *
	 * @param int    $post_id      Event post ID.
	 * @param string $source_field Field key (title, excerpt, date, …).
	 * @param int    $word_count   Word limit for excerpt-like fields.
	 * @return string
	 */
	public static function get_event_field_value( $post_id, $source_field, $word_count = 20 ) {
		$post_id = (int) $post_id;
		if ( ! $post_id ) {
			return '';
		}

		switch ( $source_field ) {
			case 'title':
				return ELB_Event_Repository::get_event_title( $post_id );
			case 'excerpt':
				return self::excerpt( $post_id, (int) $word_count );
			case 'date':
				return self::get_event_start_date( $post_id, ELB_Date_Format_Resolver::resolve_date_format( 'site' ) . ' ' . ELB_Date_Format_Resolver::resolve_time_format( 'site' ) );
			case 'time':
				$time = self::get_event_start_date( $post_id, ELB_Date_Format_Resolver::resolve_time_format( 'site' ) );
				return '' !== $time ? '@ ' . $time : '';
			case 'venue':
				return (string) ELB_Venue_Repository::get_venue_name( $post_id );
			case 'cost':
				return (string) ELB_Event_Repository::get_event_cost( $post_id );
			case 'id':
				return (string) $post_id;
			case 'image':
				return ELB_Event_Repository::get_event_image_url( $post_id );
			case 'categories':
			case 'category':
				return self::term_names_list( ELB_Event_Repository::get_event_category_terms( $post_id ) );
			default:
				return ELB_Event_Repository::get_event_title( $post_id );
		}
	}

	/**
	 * Comma-separated term names, or empty string.
	 *
	 * @param WP_Term[] $terms Terms.
	 * @return string
	 */
	private static function term_names_list( $terms ) {
		if ( empty( $terms ) ) {
			return '';
		}
		return implode( ', ', wp_list_pluck( $terms, 'name' ) );
	}

	/**
	 * Text shown for an all-day event: the custom label, or "All day".
	 *
	 * @param string $label Custom label (may be empty).
	 */
	public static function all_day_text( string $label = '' ): string {
		$label = trim( $label );
		return '' !== $label ? $label : __( 'All day', 'template-events-calendar' );
	}
}
