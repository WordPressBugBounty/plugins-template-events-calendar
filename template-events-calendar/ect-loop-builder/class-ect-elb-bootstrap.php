<?php
/**
 * Load the bundled Events Loop Builder runtime when supported.
 *
 * @package Template_Events_Calendar
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'ECT_ELB_Bootstrap' ) ) {
	final class ECT_ELB_Bootstrap { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Bundled Events Loop Builder bootstrap.

		/**
		 * Load and initialize the bundled runtime.
		 *
		 * A separately activated Events Loop Builder plugin takes precedence.
		 *
		 * @return bool Whether the runtime is available.
		 */
		public static function boot() {
			global $wp_version;

			if ( version_compare( PHP_VERSION, '7.4', '<' ) || version_compare( (string) $wp_version, '6.7', '<' ) ) {
				return false;
			}

			if ( class_exists( 'ELB_Plugin', false ) ) {
				ELB_Plugin::boot();
				return true;
			}

			if ( defined( 'ELB_PATH' ) || defined( 'ELB_URL' ) || defined( 'ELB_VER' ) ) {
				// Another copy defined the constants but has not loaded ELB_Plugin (checked above).
				return false;
			}

			define( 'ELB_PATH', ECT_PLUGIN_DIR . 'ect-loop-builder/' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- Bundled Events Loop Builder path.
			define( 'ELB_URL', ECT_PLUGIN_URL . 'ect-loop-builder/' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- Bundled Events Loop Builder URL.
			define( 'ELB_VER', '1.0.6' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- Bundled Events Loop Builder version.
			if ( ! defined( 'ELB_UPGRADE_URL' ) ) {
				define( 'ELB_UPGRADE_URL', 'https://eventscalendaraddons.com/plugin/events-shortcodes-pro/?utm_source=ect_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=loop_builder_layout_list' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- Bundled Events Loop Builder upgrade URL.
			}

			require_once ELB_PATH . 'includes/class-plugin.php';

			ELB_Plugin::boot();
			return true;
		}
	}
}
