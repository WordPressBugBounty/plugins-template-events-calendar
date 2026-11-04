<?php
/**
 * The Events Calendar Pro detection (cached per request).
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Tec_Pro { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/** @var bool|null Cached result of active(). */
	private static $pro_active = null;

	/**
	 * Whether The Events Calendar Pro is active (archive/profile links for TEC Pro-only meta
	 * settings; also passed to the editor by ELB_Assets). Cached per request.
	 */
	public static function active(): bool {
		if ( null === self::$pro_active ) {
			self::$pro_active = self::detect();
		}
		return self::$pro_active;
	}

	/**
	 * Uncached TEC Pro detection.
	 */
	private static function detect(): bool {
		if (
			defined( 'EVENTS_CALENDAR_PRO_FILE' )
			|| class_exists( 'Tribe__Events__Pro__Main' )
			|| class_exists( 'Tribe\\Events\\Pro\\Plugin' )
		) {
			return true;
		}

		// Same check as is_plugin_active() (incl. network-active) without loading wp-admin/includes/plugin.php.
		$active = (array) get_option( 'active_plugins', [] );
		if ( is_multisite() ) {
			$active = array_merge( $active, array_keys( (array) get_site_option( 'active_sitewide_plugins', [] ) ) );
		}
		return (bool) array_intersect( [ 'events-calendar-pro/events-calendar-pro.php', 'the-events-calendar-pro/the-events-calendar-pro.php' ], $active );
	}
}
