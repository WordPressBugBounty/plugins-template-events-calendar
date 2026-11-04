<?php
/**
 * Event cost display: free detection, formatted cost, cost label and ticket text.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Event_Cost { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/**
	 * Whether a cost value means a free event (0, or the calendar's own "Free" text).
	 *
	 * @param string $raw Cost value as returned by ELB_Event_Formatter::get_event_field_value().
	 */
	public static function is_free_cost( string $raw ): bool {
		$raw = trim( wp_strip_all_tags( $raw ) );
		return ( is_numeric( $raw ) && 0.0 === (float) $raw )
			|| 0 === strcasecmp( $raw, 'Free' )
			|| 0 === strcasecmp( $raw, __( 'Free', 'template-events-calendar' ) );
	}

	/**
	 * Format event cost for display.
	 *
	 * @param mixed $raw Raw cost value.
	 * @return string
	 */
	public static function format_event_cost_display( $raw ) {
		$raw = trim( wp_strip_all_tags( (string) $raw ) );
		if ( '' === $raw ) {
			return '';
		}
		if ( is_numeric( $raw ) ) {
			if ( 0.0 === (float) $raw ) {
				return __( 'Free', 'template-events-calendar' );
			}
			$amount = number_format_i18n( (float) $raw, 2 );
			if ( function_exists( 'tribe_format_currency' ) ) {
				return tribe_format_currency( $amount );
			}
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Bundled Events Loop Builder hook.
			$symbol = wp_strip_all_tags( (string) apply_filters( 'template_events_calendar_elb_currency_symbol', '$' ) );
			return $symbol . $amount;
		}
		return $raw;
	}

	/**
	 * Get cost label, defaulting to free label when empty or zero.
	 *
	 * @param string $raw        Raw cost value.
	 * @param string $free_label Free label text.
	 * @return string
	 */
	public static function cost_label( string $raw, string $free_label = '' ): string {
		$free    = '' !== $free_label ? $free_label : __( 'Free', 'template-events-calendar' );
		$display = self::format_event_cost_display( $raw );
		return '' === $display || self::is_free_cost( $raw ) ? $free : $display;
	}

	/**
	 * Ticket/price label for list info rows — always returns a visible string.
	 *
	 * @param int $post_id Event post ID.
	 * @return string
	 */
	public static function get_ticket_display_text( $post_id ) {
		$raw = (string) ELB_Event_Formatter::get_event_field_value( $post_id, 'cost' );
		return self::cost_label( $raw );
	}
}
