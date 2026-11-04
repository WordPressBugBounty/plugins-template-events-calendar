<?php
/**
 * Event Time service: unified resolution of event time strings and the ISO start date.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Event_Time { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/**
	 * Event time display text: "8:00 am – 5:00 pm", start only, the custom time, or the all-day label.
	 *
	 * @param int         $post_id       Event post ID.
	 * @param string|null $format        PHP time format.
	 * @param bool        $show_end      Whether to show end time (default true).
	 * @param string      $all_day_label Custom all day label.
	 * @param bool        $show_timezone Whether to append event timezone abbreviation.
	 */
	public static function text( int $post_id, ?string $format = null, bool $show_end = true, string $all_day_label = '', bool $show_timezone = false ): string {
		if ( ! $post_id ) {
			return '';
		}

		if ( ELB_Event_Repository::is_all_day( $post_id ) ) {
			return ELB_Event_Formatter::all_day_text( $all_day_label );
		}

		$fmt   = $format ?: 'g:i a';
		$range = self::start_end( $post_id, $fmt, $show_end );
		$start = $range['start'];
		$end   = $range['end'];

		if ( '' === $start ) {
			return '';
		}

		if ( $end && preg_match( '/[aA]/', $fmt )
			&& ELB_Event_Formatter::get_event_start_date( $post_id, 'A' ) === ELB_Event_Formatter::get_event_end_date( $post_id, 'A' ) ) {
			// Same AM/PM on both ends: "12:00 – 3:00 PM".
			$start = (string) ELB_Event_Formatter::get_event_start_date( $post_id, trim( (string) preg_replace( '/\s*[aA]/', '', $fmt ) ) );
		}

		$text = ( $end && $end !== $start ) ? $start . ' – ' . $end : $start;

		if ( $show_timezone ) {
			[ $start_ts, , $tz ] = ELB_Event_Repository::get_event_timestamps( $post_id );
			if ( is_int( $start_ts ) && $tz instanceof DateTimeZone ) {
				$text .= ' ' . self::timezone_abbr( $start_ts, $tz );
			}
		}

		return $text;
	}

	/**
	 * Machine-readable start for a <time datetime="">: the day for all-day events, else the full ISO 8601 date.
	 *
	 * @param int $post_id Event post ID.
	 */
	public static function iso_start( int $post_id ): string {
		$format = ELB_Event_Repository::is_all_day( $post_id ) ? 'Y-m-d' : 'c';
		return (string) ELB_Event_Formatter::get_event_start_date( $post_id, $format );
	}

	/**
	 * Display timezone: named abbr (IST) as-is; GMT+0530 → UTC+5:30 (matches WP Settings).
	 *
	 * @param int          $timestamp Event start timestamp.
	 * @param DateTimeZone $tz        Event timezone.
	 */
	private static function timezone_abbr( int $timestamp, DateTimeZone $tz ): string {
		$abbr = (string) wp_date( 'T', $timestamp, $tz );
		if ( ! preg_match( '/^GMT([+-])(\d{1,2})(\d{2})$/i', str_replace( ':', '', $abbr ), $m ) ) {
			return $abbr;
		}
		$h   = (int) $m[2];
		$min = (int) $m[3];
		if ( ! $h && ! $min ) {
			return 'UTC';
		}
		return $min
			? sprintf( 'UTC%s%d:%02d', $m[1], $h, $min )
			: sprintf( 'UTC%s%d', $m[1], $h );
	}

	/**
	 * Same resolution as get_event_time_display(), but with start/end kept separate — for layouts
	 * that put the end time on its own line instead of joining them with an em dash.
	 *
	 * @param int         $post_id  Event post ID (non-zero: text() already guards against 0).
	 * @param string      $format   PHP time format, already defaulted by text() when empty.
	 * @param bool        $show_end False to omit the end time.
	 * @return array{start:string,end:string}
	 */
	private static function start_end( int $post_id, string $format, bool $show_end ): array {
		$empty = [
			'start' => '',
			'end'   => '',
		];

		$custom_time = ELB_Event_Repository::get_event_meta( $post_id, 'elb_event_time' );
		if ( $custom_time ) {
			return [
				'start' => (string) $custom_time,
				'end'   => '',
			];
		}

		$start_fmt = ELB_Event_Formatter::get_event_start_date( $post_id, $format );
		if ( ! $start_fmt ) {
			return $empty;
		}
		$end_fmt = ! $show_end ? '' : (string) ELB_Event_Formatter::get_event_end_date( $post_id, $format );
		if ( $end_fmt === $start_fmt ) {
			$end_fmt = '';
		}
		return [
			'start' => $start_fmt,
			'end'   => $end_fmt,
		];
	}
}
