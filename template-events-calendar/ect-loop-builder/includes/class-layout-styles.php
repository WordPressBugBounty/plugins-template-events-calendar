<?php
/**
 * Per-layout stylesheet detection and registration.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Layout_Styles { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/**
	 * Per-style stylesheet (assets/css/layouts/{key}.css) => card classes that select it.
	 * Shared rules stay in frontend.css; each file holds only one style's own rules.
	 * Grid (grid-1/grid-2) and list-2 ("Modern List") are Pro-only — a separate plugin, never
	 * active alongside this one — so Free ships no Pro template, markup, or CSS at all for them.
	 */
	const LAYOUT_STYLES = [
		'list-1' => [ 'elb-card--list-1', 'elb-card--horizontal-magazine', 'elb-card__body--list-1', 'elb-card__media-col', 'elb-card__details-col' ],
		'list-3' => [ 'elb-card--list-3', 'elb-card--timeline', 'elb-card__date-col--list-3', 'elb-card__media-col--list-3', 'elb-card__body--list-3' ],
		'list-4' => [ 'elb-card--list-4', 'elb-card--minimal', 'elb-card__date-col--list-4', 'elb-card__body--list-4' ],
	];

	/** @var array<string,bool> Layout style keys already enqueued. */
	private static array $layouts_done = [];

	/**
	 * Get layout keys that have been enqueued so far in this request.
	 *
	 * @return string[]
	 */
	public static function get_enqueued_layout_keys(): array {
		return array_keys( self::$layouts_done );
	}

	/**
	 * Editor / canvas: any style can be inserted, so load every layout stylesheet.
	 */
	public static function enqueue_all_layout_styles(): void {
		foreach ( array_keys( self::LAYOUT_STYLES ) as $key ) {
			wp_enqueue_style( 'elb-layout-' . $key );
		}
	}

	/**
	 * Enqueue the per-style stylesheet(s) whose card classes appear in $haystack.
	 *
	 * @param string $haystack Block className attribute or raw post/template content.
	 */
	public static function enqueue_layout_styles_for( string $haystack ): void {
		// Card classes (elb-card--*) and the layout's column classes (elb-card__*) both pull in its stylesheet.
		if ( '' === $haystack || false === strpos( $haystack, 'elb-card' ) ) {
			return;
		}
		foreach ( self::LAYOUT_STYLES as $key => $classes ) {
			if ( isset( self::$layouts_done[ $key ] ) ) {
				continue;
			}
			foreach ( $classes as $class ) {
				if ( preg_match( '/(?<![\\w-])' . preg_quote( $class, '/' ) . '(?![\\w-])/', $haystack ) ) {
					self::$layouts_done[ $key ] = true;
					wp_enqueue_style( 'elb-layout-' . $key );
					self::print_inline_after( 'elb-layout-' . $key );
					break;
				}
			}
		}
	}

	/**
	 * Make a stylesheet enqueued after the inline handles were registered still
	 * print before them, so generated CSS keeps overriding it.
	 *
	 * @param string   $handle Enqueued stylesheet handle.
	 * @param string[] $skip   Inline handles that must keep printing before $handle.
	 */
	public static function print_inline_after( string $handle, array $skip = [] ): void {
		// Every handle, not just the first: one already printed in the head no longer orders the rest.
		foreach ( array_keys( ELB_Inline_Css::INLINE_HANDLES ) as $inline ) {
			if ( ! in_array( $inline, $skip, true ) ) {
				self::add_style_dep( $inline, $handle );
			}
		}
	}

	/**
	 * Make a registered stylesheet depend on another one (no-op when unregistered or already set).
	 *
	 * @param string $handle Registered style handle.
	 * @param string $dep    Handle it must print after.
	 */
	public static function add_style_dep( string $handle, string $dep ): void {
		$style = wp_styles()->query( $handle, 'registered' );
		if ( $style && ! in_array( $dep, $style->deps, true ) ) {
			$style->deps[] = $dep;
		}
	}
}
