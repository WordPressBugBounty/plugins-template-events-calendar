<?php
/**
 * Collects dynamic CSS from flex-container / wrapper attributes.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

/**
 * Validates dynamic CSS (ELB_Css_Validator), then hands it to an ELB inline style handle (deduped by md5 in ELB_Inline_Css::add_inline_css_once()).
 */
final class ELB_Dynamic_Css { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/**
	 * CSS found while rendering. Prints in the head when rendered before
	 * wp_head (block themes), otherwise with core's late styles in the footer.
	 */
	public static function add_block_css( string $css ): void {
		// elb-dynamic keeps container CSS ahead of scoped/field CSS (the old head order);
		// once elb-dynamic has printed, late CSS goes to elb-render-containers.
		$handle = wp_style_is( 'elb-dynamic', 'done' ) ? 'elb-render-containers' : 'elb-dynamic';
		// Deduped on the raw chunk; it is only validated the first time it is seen (empty results add nothing).
		ELB_Inline_Css::add_inline_css_once(
			$handle,
			'dynamic:' . md5( $css ),
			static function () use ( $css ): string {
				return ELB_Css_Validator::sanitize( $css );
			}
		);
	}
}
