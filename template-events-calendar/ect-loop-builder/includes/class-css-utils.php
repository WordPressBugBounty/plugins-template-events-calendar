<?php
/**
 * CSS value sanitizers and font helpers shared by the style builders.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

class ELB_Css_Utils { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/** Maps a textAlign keyword to its flexbox justify-content value. */
	const ALIGN_TO_FLEX = [
		'left'    => 'flex-start',
		'center'  => 'center',
		'right'   => 'flex-end',
		'justify' => 'space-between',
	];

	const TEXT_DECORATIONS = [ 'underline', 'line-through', 'none', 'overline' ];
	const FONT_STYLES      = [ 'italic', 'normal', 'oblique' ];
	const TEXT_TRANSFORMS  = [ 'uppercase', 'lowercase', 'capitalize', 'none' ];
	const TEXT_ALIGNS      = [ 'left', 'center', 'right', 'justify' ];
	const BOX_ALIGNS       = [ 'left', 'center', 'right' ];

	/**
	 * @param string $align One of ALIGN_TO_FLEX's keys.
	 * @return string The matching justify-content value, or '' if unrecognised.
	 */
	public static function align_to_flex( string $align ): string {
		return self::ALIGN_TO_FLEX[ $align ] ?? '';
	}

	/**
	 * @param mixed  $value    Candidate align keyword.
	 * @param string $fallback Returned when invalid (default '').
	 * @return string
	 */
	public static function sanitize_text_align( $value, string $fallback = '' ): string {
		$v = sanitize_key( (string) $value );
		return in_array( $v, self::TEXT_ALIGNS, true ) ? $v : $fallback;
	}

	/**
	 * @param mixed    $value    Candidate keyword.
	 * @param string[] $allowed  One of the keyword whitelist arrays.
	 * @param string   $fallback Fallback value when not allowed.
	 * @return string $value if it's in $allowed, otherwise $fallback.
	 */
	public static function safe_keyword( $value, array $allowed, string $fallback = '' ): string {
		return in_array( $value, $allowed, true ) ? (string) $value : $fallback;
	}

	/**
	 * Quote a font-family value for safe use in inline CSS.
	 *
	 * @param string $family Font family name.
	 * @return string e.g. 'Open Sans' or empty.
	 */
	public static function css_font_family( $family ) {
		$family = trim( (string) $family, "\"' \t\n\r\0\x0B" );
		$family = preg_replace( '/[^A-Za-z0-9 _-]/', '', $family );
		return '' === $family ? '' : "'" . $family . "'";
	}

	/**
	 * Whitelist a CSS unit so it can't be used to break out of a declaration.
	 *
	 * @param mixed  $unit     Raw unit value from block attributes.
	 * @param string $fallback Fallback when the value isn't a known unit.
	 * @return string
	 */
	public static function safe_unit( $unit, $fallback = 'px' ) {
		$unit = strtolower( trim( (string) $unit ) );
		return in_array( $unit, [ 'px', 'em', 'rem', '%', 'vw', 'vh' ], true ) ? $unit : $fallback;
	}

	/**
	 * Whitelist a CSS font-weight value.
	 *
	 * @param mixed $weight Raw font-weight from block attributes.
	 * @return string Sanitized value or empty string.
	 */
	public static function safe_font_weight( $weight ) {
		$weight = trim( (string) $weight );
		return preg_match( '/^(normal|bold|lighter|bolder|[1-9]00)$/', $weight ) ? $weight : '';
	}

	/**
	 * Sanitize a CSS color value (hex, rgba, hsl) for icon/style attributes.
	 *
	 * @param string $color Raw color from block attributes.
	 * @return string Sanitized color or empty string.
	 */
	public static function sanitize_css_color( $color ) {
		$color = trim( (string) $color );
		if ( '' === $color ) {
			return '';
		}
		$hex = sanitize_hex_color( $color );
		if ( $hex ) {
			return $hex;
		}
		if ( preg_match( '/^#([A-Fa-f0-9]{8})$/', $color ) ) {
			return $color;
		}
		// rgb()/rgba() → hex. WordPress's get_block_wrapper_attributes() runs the inline style through
		// safecss_filter_attr(), which silently drops any declaration containing "rgba(…)" (so a shadow
		// or background with an rgba colour never reached the frontend). Hex survives and looks the same.
		if ( preg_match( '/^rgba?\(\s*(\d{1,3})\s*[,\s]\s*(\d{1,3})\s*[,\s]\s*(\d{1,3})\s*(?:[,\/]\s*(\d*\.?\d+)(%?))?\s*\)$/i', $color, $m ) ) {
			$hex = sprintf( '#%02x%02x%02x', min( 255, (int) $m[1] ), min( 255, (int) $m[2] ), min( 255, (int) $m[3] ) );
			if ( isset( $m[4] ) && '' !== $m[4] ) {
				$alpha = (float) $m[4];
				$alpha = ( isset( $m[5] ) && '%' === $m[5] ) ? $alpha / 100 : $alpha;
				$alpha = max( 0, min( 1, $alpha ) );
				if ( $alpha < 1 ) {
					$hex .= sprintf( '%02x', (int) round( $alpha * 255 ) );
				}
			}
			return $hex;
		}
		if ( preg_match( '/^hsla?\(\s*[\d.%\s,\/]+\)$/i', $color ) ) {
			return $color;
		}
		return '';
	}

	/**
	 * True when a CSS color paints nothing (List Classic outline uses transparent fill).
	 *
	 * @param string $color Color already passed through sanitize_css_color() (transparent → '', rgb() → hex).
	 * @return bool
	 */
	public static function is_transparent_css_color( $color ) {
		$n = trim( (string) $color );
		// Empty, or 8-digit hex with zero alpha (#00000000, #rrggbb00).
		return '' === $n || (bool) preg_match( '/^#[0-9a-f]{6}00$/i', $n );
	}

	/**
	 * Drop declarations whose property starts with one of the given prefixes.
	 *
	 * @param string[] $decls         CSS declarations ("prop:value").
	 * @param string[] $prop_prefixes Lower-case prefixes, e.g. 'padding' or 'color:'.
	 * @return string[]
	 */
	public static function without_props( array $decls, array $prop_prefixes ): array {
		return array_values( array_filter( $decls, static function ( $decl ) use ( $prop_prefixes ) {
			$decl = strtolower( (string) $decl );
			foreach ( $prop_prefixes as $prefix ) {
				if ( 0 === strpos( $decl, $prefix ) ) {
					return false;
				}
			}
			return true;
		} ) );
	}

	/**
	 * Normalize a hex color string (#RGB / #RRGGBB).
	 *
	 * @param string $color Raw color.
	 * @return string Empty string or #RRGGBB.
	 */
	public static function normalize_hex_color( $color ) {
		$color = trim( (string) $color );
		if ( '' === $color ) {
			return '';
		}
		if ( '#' !== $color[0] ) {
			$color = '#' . $color;
		}
		if ( preg_match( '/^#([A-Fa-f0-9]{3})$/', $color, $m ) ) {
			$color = '#' . $m[1][0] . $m[1][0] . $m[1][1] . $m[1][1] . $m[1][2] . $m[1][2];
		}
		return sanitize_hex_color( $color ) ?: '';
	}

	/**
	 * Pick black/white text for contrast against a background color.
	 *
	 * @param string $bg Hex background.
	 * @return string #111827 or #ffffff.
	 */
	public static function contrast_text_color( $bg ) {
		$bg = self::normalize_hex_color( $bg );
		if ( ! $bg || 7 !== strlen( $bg ) ) {
			return '#111827';
		}
		$r = hexdec( substr( $bg, 1, 2 ) );
		$g = hexdec( substr( $bg, 3, 2 ) );
		$b = hexdec( substr( $bg, 5, 2 ) );
		$luma = ( 0.299 * $r + 0.587 * $g + 0.114 * $b ) / 255;
		return $luma > 0.55 ? '#111827' : '#ffffff';
	}
}
