<?php
/**
 * Event Meta item: event date (smart range).
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Meta_Item_Date extends ELB_Meta_Item { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	const ICON = 'calendar';

	public static function render( int $post_id, array $item_settings, array $item = [] ): string {
		$fmt   = ELB_Date_Format_Resolver::resolve_date_format( (string) ( $item_settings['format'] ?? 'site' ), (string) ( $item_settings['customFormat'] ?? '' ) );
		$start = (string) ELB_Event_Formatter::get_event_start_date( $post_id, $fmt );
		if ( '' === $start ) {
			return '';
		}
		$text = $start;
		if ( ! empty( $item_settings['showEndDate'] ) ) {
			$text = self::date_range( $post_id, $fmt, $start );
		}
		return ELB_Meta_Item::time_tag( $text, ELB_Event_Formatter::get_event_start_date( $post_id, 'Y-m-d' ), $item );
	}

	/**
	 * Start – end with a smart range for month/day formats: "Jan 14 – 16, 2026", "Jan 30 – Feb 2".
	 * Single-day events show the start only. Mirrors metaDateText() in meta-items/format.js.
	 */
	protected static function date_range( int $post_id, string $fmt, string $start ): string {
		$end = (string) ELB_Event_Formatter::get_event_end_date( $post_id, $fmt );
		if ( '' === $end ) {
			return $start;
		}
		// $start/$end are non-empty, so the timestamps behind them are always set here —
		// only the year/month/day fields same_day() and the smart-range logic need are fetched
		// (not the full ELB_Event_Date_Value::parts() set, and with no null-timestamp fallback).
		[ $s_ts, $e_ts, $tz ] = ELB_Event_Repository::get_event_timestamps( $post_id );
		$s = ELB_Event_Date_Value::ymd( $s_ts, $tz );
		$e = ELB_Event_Date_Value::ymd( $e_ts, $tz );
		if ( ELB_Event_Date_Value::same_day( $s, $e ) ) {
			return $start;
		}
		if ( ! preg_match( '/^([FM]) j(, Y)?$/', $fmt, $m ) || $s['year'] !== $e['year'] ) {
			return $start . ' – ' . $end;
		}
		$year = isset( $m[2] ) ? ', ' . $s['year'] : '';
		$tail = $s['monFull'] === $e['monFull'] ? (string) (int) $e['day'] : (string) ELB_Event_Formatter::get_event_end_date( $post_id, $m[1] . ' j' );
		return (string) ELB_Event_Formatter::get_event_start_date( $post_id, $m[1] . ' j' ) . ' – ' . $tail . $year;
	}
}
