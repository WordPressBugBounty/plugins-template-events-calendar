<?php
/**
 * Frontend CSS for the elb/wrapper block (a flex column of cards with a row gap).
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Wrapper_Css { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/**
	 * Flex-column CSS (with row gap) for an elb/wrapper block.
	 *
	 * @param array<string,mixed> $attrs Block attributes.
	 * @return string CSS.
	 */
	public static function wrapper_css( array $attrs ): string {
		$id = ELB_Container_Selectors::block_id( $attrs );
		if ( '' === $id ) {
			return '';
		}
		$sel = '.wp-block-elb-wrapper.elb-wrapper-' . $id;

		$row_gap      = $attrs['rowGap'] ?? ( $attrs['columnGap'] ?? 10 );
		$row_gap_unit = ELB_Css_Utils::safe_unit( $attrs['rowGapType'] ?? ( $attrs['columnGapType'] ?? 'px' ) );

		$css = $sel . '{display:flex;flex-direction:column;align-items:stretch;';
		if ( is_numeric( $row_gap ) ) {
			$css .= 'row-gap:' . (float) $row_gap . $row_gap_unit . ';';
		}
		return $css . '}';
	}
}
