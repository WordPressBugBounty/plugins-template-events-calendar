<?php
/**
 * Event display formatting: date/time format keys and cost labels.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Date_Format_Resolver { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/**
	 * Only characters a PHP date()/wp_date() format needs, so a saved custom format can't inject
	 * anything — shared by every block that offers a "Custom format" text input.
	 *
	 * @param mixed  $format   Candidate format string.
	 * @param string $fallback Used when $format is empty (or becomes empty after sanitizing).
	 * @return string
	 */
	public static function safe_date_format( $format, string $fallback ): string {
		$format = preg_replace( '/[^a-zA-Z0-9\s:,.\-\/\\\\]/', '', (string) $format );
		$format = trim( substr( (string) $format, 0, 24 ) );
		return '' !== $format ? $format : $fallback;
	}

	/**
	 * Resolve a time format key or pattern into a validated PHP time format.
	 *
	 * @param string $format_key    '12h', '24h', 'site', 'custom', or explicit PHP format.
	 * @param string $custom_format Custom format string if $format_key is 'custom'.
	 * @return string Validated PHP time format.
	 */
	/**
	 * Shared resolver for time and date formats.
	 */
	private static function resolve_format( string $format_key, string $custom_format, string $option_name, string $default_format, array $allowed, array $aliases = [] ): string {
		static $site_formats = [];
		if ( ! isset( $site_formats[ $option_name ] ) ) {
			$val = (string) get_option( $option_name );
			$site_formats[ $option_name ] = '' !== $val ? $val : $default_format;
		}
		$site_format = $site_formats[ $option_name ];

		if ( isset( $aliases[ $format_key ] ) ) {
			return $aliases[ $format_key ];
		}
		if ( 'site' === $format_key ) {
			return $site_format;
		}
		if ( 'custom' === $format_key ) {
			return self::safe_date_format( $custom_format, $site_format );
		}
		return in_array( $format_key, $allowed, true ) ? $format_key : $site_format;
	}

	/**
	 * Resolve a time format key or pattern into a validated PHP time format.
	 *
	 * @param string $format_key    '12h', '24h', 'site', 'custom', or explicit PHP format.
	 * @param string $custom_format Custom format string if $format_key is 'custom'.
	 * @return string Validated PHP time format.
	 */
	public static function resolve_time_format( string $format_key, string $custom_format = '' ): string {
		return self::resolve_format( $format_key, $custom_format, 'time_format', 'g:i a', [ 'g:i A', 'H:i', 'g:i a' ], [ '12h' => 'g:i A', '24h' => 'H:i' ] );
	}

	/**
	 * Resolve a date format key or pattern into a validated PHP date format.
	 *
	 * @param string $format_key    'site', 'custom', or explicit PHP format.
	 * @param string $custom_format Custom format string if $format_key is 'custom'.
	 * @return string Validated PHP date format.
	 */
	public static function resolve_date_format( string $format_key, string $custom_format = '' ): string {
		return self::resolve_format( $format_key, $custom_format, 'date_format', 'F j, Y', [ 'M j', 'F j, Y', 'l, F j' ] );
	}

}
