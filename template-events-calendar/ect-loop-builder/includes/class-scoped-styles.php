<?php
/**
 * Frontend: move a field block's wrapper inline style into one footer stylesheet.
 *
 * Each distinct style gets one class (elb-s-<hash>) and its rule is printed once per loop, so a
 * loop no longer repeats the same inline declarations on every event card. Only the outer
 * wrapper is touched; inner elements keep their inline styles. The editor is unaffected.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

class ELB_Scoped_Styles { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/**
	 * Inline-style fragments that frontend.css matches with [style*="…"] on the wrapper,
	 * mirrored as classes once the style attribute is gone.
	 */
	const MARKERS = [
		'--elb-field-color' => 'elb-has-field-color',
		'--elb-field-bg'    => 'elb-has-field-bg',
		'text-align:center' => 'elb-ta-center',
		'text-align:right'  => 'elb-ta-right',
	];

	/**
	 * Move the wrapper's inline style into a shared class (see ELB_Field_Block::filter).
	 *
	 * @param WP_HTML_Tag_Processor $tags Processor on the block's wrapper.
	 * @return bool Whether the wrapper changed.
	 */
	public static function scope_wrapper_style( WP_HTML_Tag_Processor $tags ): bool {
		if ( ELB_Assets::is_admin_screen() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || is_feed() ) {
			return false;
		}
		$style = $tags->get_attribute( 'style' );
		if ( ! is_string( $style ) ) {
			return false;
		}
		// Cards sharing a design carry the same style string: work it out once per string.
		static $seen = [];
		if ( ! isset( $seen[ $style ] ) ) {
			$decls = trim( str_replace( '<', '', wp_strip_all_tags( $style ) ), " ;\t\n\r" );
			if ( '' === $decls || false !== strpbrk( $decls, '{}' ) ) {
				$seen[ $style ] = false;
			} else {
				$class   = 'elb-s-' . substr( md5( $decls ), 0, 10 );
				$classes = [ $class ];
				foreach ( self::MARKERS as $needle => $marker ) {
					if ( false !== strpos( $decls, $needle ) ) {
						$classes[] = $marker;
					}
				}
				$seen[ $style ] = [ $class, $classes, $decls ];
			}
		}
		if ( false === $seen[ $style ] ) {
			return false;
		}
		[ $class, $classes, $decls ] = $seen[ $style ];
		$tags->remove_attribute( 'style' );
		foreach ( $classes as $name ) {
			$tags->add_class( $name );
		}

		// Keep the rule out of the wrapper. A heading cannot contain <style>,
		// and the same class is shared by every card with these settings.
		ELB_Inline_Css::add_scoped_css( $class, ':is(.' . $class . ',#elb-s#elb-s#elb-s){' . $decls . '}' );
		return true;
	}

}
