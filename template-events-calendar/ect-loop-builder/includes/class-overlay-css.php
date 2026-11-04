<?php
/**
 * Overlay-on-image positioning for date / meta blocks.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Overlay_Css { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/**
	 * Inline style declarations for date/meta overlay-on-image.
	 *
	 * @param int    $z         z-index.
	 * @param string $max_width CSS max-width value.
	 * @return string[]
	 */
	public static function overlay_on_image_style_decls( int $z = 5, string $max_width = 'none' ): array {
		return [
			'position:absolute',
			'top:12px',
			'left:12px',
			'z-index:' . $z,
			'margin:0',
			'width:auto',
			'max-width:' . $max_width,
			'float:none',
		];
	}

	/**
	 * Scoped CSS making the overlay's flex-container parent position:relative.
	 *
	 * @param string              $resp_class Unique block class (no leading dot).
	 * @param array<string,mixed> $args       extra_parent_sels (selector prefixes before :has).
	 * @return string
	 */
	public static function overlay_on_image_css( string $resp_class, array $args = [] ): string {
		$class = sanitize_html_class( $resp_class );
		if ( '' === $class ) {
			return '';
		}
		$parents = [
			'.wp-block-elb-flex-container:has(>.' . $class . ')',
			'.wp-block-elb-flex-container:has(> .elb-flex-container-inner-blocks-wrap >.' . $class . ')',
		];
		foreach ( (array) ( $args['extra_parent_sels'] ?? [] ) as $prefix ) {
			$prefix = (string) $prefix;
			if ( '' !== $prefix ) {
				$parents[] = $prefix . ':has(.' . $class . ')';
			}
		}
		// The overlay declarations themselves are already on the element (inline → scoped class
		// via ELB_Scoped_Styles), so only the parent needs a rule here.
		return implode( ',', $parents ) . '{position:relative;}';
	}
}
