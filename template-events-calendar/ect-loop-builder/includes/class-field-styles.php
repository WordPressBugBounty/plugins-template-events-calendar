<?php
/**
 * Field CSS: typography, colors, hover, responsive rules.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

class ELB_Field_Styles { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/** Full-width box declarations shared by row/flex builders. */
	const FULL_WIDTH_DECLS = [ 'width:100%', 'max-width:100%', 'box-sizing:border-box' ];


	/**
	 * border-radius declaration from block attributes, or ''.
	 *
	 * @param array $attributes Block attributes.
	 * @return string e.g. "border-radius:8px 8px 0 0" or ''.
	 */
	public static function border_radius_decl( array $attributes ): string {
		$bru = ELB_Css_Utils::safe_unit( $attributes['borderRadiusUnit'] ?? 'px' );
		$rtl = $attributes['borderRadiusTopLeft'] ?? '';
		$rtr = $attributes['borderRadiusTopRight'] ?? '';
		$rbr = $attributes['borderRadiusBottomRight'] ?? '';
		$rbl = $attributes['borderRadiusBottomLeft'] ?? '';
		if ( '' === $rtl && '' === $rtr && '' === $rbr && '' === $rbl ) {
			return '';
		}
		return sprintf(
			'border-radius:%s %s %s %s',
			'' !== $rtl ? (float) $rtl . $bru : '0',
			'' !== $rtr ? (float) $rtr . $bru : '0',
			'' !== $rbr ? (float) $rbr . $bru : '0',
			'' !== $rbl ? (float) $rbl . $bru : '0'
		);
	}

	/**
	 * Build a CSS style string array from common block style attributes.
	 *
	 * @param array $attributes Block attributes.
	 * @return array CSS declaration strings.
	 *
	 * Editor mirror: source/loop/utils/buildFieldStyles.js. Keep both in sync.
	 */
	public static function field_decls( $attributes ) {
		$s = ELB_Typography::declarations( (array) $attributes, null, array_fill_keys( ELB_Typography::FONT_PROPS, 'twin' ), ELB_Typography::FONT_PROPS );
		array_push( $s, ...self::color_decls( $attributes ) );
		array_push( $s, ...self::border_decls( $attributes ) );
		// Field Shadow is a Pro-only control (every free Style panel locks it), so no box-shadow here.
		array_push( $s, ...self::hover_color_vars( $attributes ) );

		return $s;
	}

	/**
	 * Text and background colour declarations.
	 *
	 * @param array $attributes Block attributes.
	 * @return string[]
	 */
	private static function color_decls( $attributes ) {
		$s    = [];
		$text = ELB_Css_Utils::sanitize_css_color( $attributes['textColor'] ?? '' );
		if ( $text ) {
			$s[] = 'color:' . esc_attr( $text );
			$s[] = '--elb-field-color:' . esc_attr( $text );
		}
		$background = ELB_Css_Utils::sanitize_css_color( $attributes['backgroundColor'] ?? '' );
		if ( $background ) {
			$s[] = 'background-color:' . esc_attr( $background );
			$s[] = '--elb-field-bg:' . esc_attr( $background );
		}
		return $s;
	}

	/**
	 * Border style, radius, width and colour declarations.
	 *
	 * @param array $attributes Block attributes.
	 * @return string[]
	 */
	public static function border_decls( $attributes ) {
		$s      = self::border_style_decls( $attributes );
		$radius = self::border_radius_decl( $attributes );
		if ( $radius ) {
			$s[] = $radius;
		}
		return $s;
	}

	/**
	 * Whether Border style is set to something other than None.
	 *
	 * @param array $attributes Block attributes (borderStyle).
	 */
	public static function has_border( $attributes ): bool {
		$style = sanitize_key( $attributes['borderStyle'] ?? 'none' );
		return '' !== $style && 'none' !== $style;
	}

	/**
	 * Whether any border-radius corner is set.
	 *
	 * @param array $attributes Block attributes (borderRadiusTopLeft/TopRight/BottomRight/BottomLeft).
	 */
	public static function has_border_radius( $attributes ): bool {
		return (bool) array_filter(
			[
				$attributes['borderRadiusTopLeft'] ?? '',
				$attributes['borderRadiusTopRight'] ?? '',
				$attributes['borderRadiusBottomRight'] ?? '',
				$attributes['borderRadiusBottomLeft'] ?? '',
			],
			static function ( $value ) {
				return '' !== (string) $value;
			}
		);
	}

	/**
	 * border-style / width / color declarations (none when Border style is None).
	 *
	 * @param array $attributes Block attributes (borderStyle, borderWidth, borderColor).
	 * @return string[]
	 */
	public static function border_style_decls( $attributes ): array {
		if ( ! self::has_border( $attributes ) ) {
			return [];
		}
		$s     = [ 'border-style:' . sanitize_key( $attributes['borderStyle'] ?? 'none' ) ];
		$width = $attributes['borderWidth'] ?? '';
		if ( '' !== $width ) {
			$s[] = 'border-width:' . (float) $width . 'px';
		}
		$color = ELB_Css_Utils::sanitize_css_color( $attributes['borderColor'] ?? '' );
		if ( $color ) {
			$s[] = 'border-color:' . esc_attr( $color );
		}
		return $s;
	}

	/**
	 * Hover colour custom properties read by the hover rules (--elb-{prefix}-hover-color / -bg).
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $prefix     Custom property prefix ('f' for field blocks, 'title', 'exc').
	 * @return string[]
	 */
	public static function hover_color_vars( $attributes, string $prefix = 'f' ) {
		$s    = [];
		$text = ELB_Css_Utils::sanitize_css_color( $attributes['hoverTextColor'] ?? '' );
		if ( $text ) {
			$s[] = '--elb-' . $prefix . '-hover-color:' . esc_attr( $text );
		}
		$background = ELB_Css_Utils::sanitize_css_color( $attributes['hoverBackgroundColor'] ?? '' );
		if ( $background ) {
			$s[] = '--elb-' . $prefix . '-hover-bg:' . esc_attr( $background );
		}
		return $s;
	}

	/**
	 * Inline style attribute string from field style attributes.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public static function field_style( $attributes ) {
		return implode( ';', array_filter( self::field_decls( $attributes ) ) );
	}

	/**
	 * Base declarations shared by the text field blocks (Title, Excerpt): alignment and full width.
	 *
	 * @param array $attributes Block attributes.
	 * @return string[]
	 */
	public static function text_block_base_decls( $attributes ) {
		return [
			'text-align:' . ELB_Css_Utils::sanitize_text_align( $attributes['textAlign'] ?? 'left', 'left' ),
			...self::FULL_WIDTH_DECLS,
		];
	}

}
