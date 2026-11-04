<?php
/**
 * Responsive breakpoints and the @media format list-card layout CSS emits.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Breakpoints { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/** Tablet breakpoint max-width (px), matches editor / frontend.css (list cards stack below it). */
	const TABLET_MAX = 1024;

	/**
	 * Wrap rules in a max-width media query.
	 *
	 * @param int    $px    Max width.
	 * @param string $rules CSS rules.
	 */
	public static function max( int $px, string $rules ): string {
		return '' === $rules ? '' : '@media only screen and (max-width:' . $px . 'px){' . $rules . '}';
	}

	/**
	 * Wrap rules in a min-width media query.
	 *
	 * @param int    $px    Min width.
	 * @param string $rules CSS rules.
	 */
	public static function min( int $px, string $rules ): string {
		return '' === $rules ? '' : '@media only screen and (min-width:' . $px . 'px){' . $rules . '}';
	}

	/**
	 * One plain CSS rule, or '' when there are no declarations.
	 *
	 * @param string        $sel   Selector.
	 * @param string[]|null $decls Declarations.
	 */
	public static function rule( string $sel, $decls ): string {
		if ( ! $decls ) {
			return '';
		}
		return $sel . '{' . implode( ';', (array) $decls ) . '}';
	}
}
