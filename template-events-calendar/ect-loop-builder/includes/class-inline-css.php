<?php
/**
 * Generated CSS pipeline: registration of inline style handles and deduplication.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Inline_Css { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/**
	 * Source-less handles that carry generated CSS via wp_add_inline_style().
	 * Each one depends on the previous, so they always print in this order,
	 * after elb-frontend and the enqueued layout files: head dynamic CSS,
	 * then scoped wrapper rules, field rules and render-time container CSS.
	 */
	const INLINE_HANDLES = [
		'elb-dynamic'           => [],
		'elb-render-scoped'     => [ 'elb-dynamic' ],
		'elb-render-fields'     => [ 'elb-render-scoped' ],
		'elb-render-containers' => [ 'elb-render-fields' ],
	];

	/** @var array<string,true> Dedupe keys already handled in this request. */
	private static array $once = [];

	/**
	 * Whether $key is seen for the first time in this request; marks it seen.
	 *
	 * @param string $key Dedupe key, namespaced by the caller (e.g. 'fields:elb-ti-…').
	 */
	public static function first_time( string $key ): bool {
		if ( isset( self::$once[ $key ] ) ) {
			return false;
		}
		self::$once[ $key ] = true;
		return true;
	}

	/**
	 * Whether $handle may receive generated CSS: a known INLINE_HANDLES key, outside the admin screen.
	 * Shared guard for add_inline_css() and add_inline_css_once(), so both discard the same way.
	 *
	 * @param string $handle Key of INLINE_HANDLES.
	 */
	private static function can_add_inline_css( string $handle ): bool {
		return isset( self::INLINE_HANDLES[ $handle ] ) && ! ELB_Assets::is_admin_screen();
	}

	/**
	 * The single dedupe sink for generated CSS: add $css to $handle only the first time $key is seen.
	 * A callable is only invoked then, so callers can skip building rules that would be discarded.
	 *
	 * @param string          $handle Key of INLINE_HANDLES.
	 * @param string          $key    Dedupe key (shared across handles).
	 * @param string|callable $css    Sanitized CSS, or a callable returning it.
	 */
	public static function add_inline_css_once( string $handle, string $key, $css ): void {
		// add_inline_css() would discard it: skip building the CSS.
		if ( ! self::can_add_inline_css( $handle ) ) {
			return;
		}
		if ( ! self::first_time( $key ) ) {
			return;
		}
		self::add_inline_css( $handle, (string) ( is_callable( $css ) ? $css() : $css ) );
	}

	/**
	 * Attach generated CSS to one of INLINE_HANDLES.
	 *
	 * Private: its only caller, add_inline_css_once(), already checked can_add_inline_css()
	 * before calling, so this only needs the empty-CSS guard.
	 *
	 * @param string $handle Key of INLINE_HANDLES.
	 * @param string $css    Already sanitized CSS.
	 */
	private static function add_inline_css( string $handle, string $css ): void {
		if ( '' === trim( $css ) ) {
			return;
		}
		self::register_inline_chain();
		wp_enqueue_style( $handle );
		wp_add_inline_style( $handle, $css );
	}

	/**
	 * Register every INLINE_HANDLES handle once, so each dependency exists.
	 */
	public static function register_inline_chain(): void {
		if ( wp_style_is( 'elb-dynamic', 'registered' ) ) {
			return;
		}
		$after = [ 'elb-frontend' ];
		foreach ( ELB_Layout_Styles::get_enqueued_layout_keys() as $key ) {
			$after[] = 'elb-layout-' . $key;
		}
		foreach ( self::INLINE_HANDLES as $inline => $chain ) {
			// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- inline-only handle, no file.
			wp_register_style( $inline, false, array_merge( $after, $chain ), null );
		}
	}

	/**
	 * Field block CSS (elb-render-fields), once per key. Blocks that repeat for every event share one
	 * class, so their rules print once per design. A callable is only run the first time the key is seen.
	 *
	 * @param string          $key Unique key (usually the shared CSS class).
	 * @param string|callable $css CSS rules, or a callable returning them.
	 */
	public static function add_field_css( $key, $css ): void {
		self::add_inline_css_once(
			'elb-render-fields',
			'fields:' . (string) $key,
			static function () use ( $css ): string {
				return wp_strip_all_tags( trim( (string) ( is_callable( $css ) ? $css() : $css ) ) );
			}
		);
	}

	/**
	 * Scoped-style rule for a shared class (elb-render-scoped), once per class.
	 *
	 * @param string $class Shared class.
	 * @param string $css   Rule.
	 */
	public static function add_scoped_css( string $class, string $css ): void {
		self::add_inline_css_once( 'elb-render-scoped', 'scoped:' . $class, $css );
	}

	/**
	 * A loop section's container CSS (elb-render-containers), once per key; built lazily.
	 *
	 * @param string   $key   Dedupe key.
	 * @param callable $build Returns the CSS.
	 */
	public static function add_loop_section_css( string $key, callable $build ): void {
		self::add_inline_css_once( 'elb-render-containers', 'containers:' . $key, $build );
	}
}
