<?php
/**
 * Field spacing (padding / margin) and size rules for scoped field CSS.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Spacing_Styles { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/**
	 * Value part of a "property:value" spacing declaration (for CSS vars).
	 *
	 * @param string $decl     Full declaration e.g. "padding:1px 2px".
	 * @param string $property Property name without colon.
	 * @return string
	 */
	public static function spacing_box_value( string $decl, string $property ): string {
		$prefix = $property . ':';
		return 0 === strpos( $decl, $prefix ) ? substr( $decl, strlen( $prefix ) ) : $decl;
	}

	/**
	 * Padding/margin shorthand. Empty unless at least one side is set (and, for margin, non-zero).
	 */
	public static function spacing_box_decl( $attributes, $stem ) {
		$unit = ELB_Css_Utils::safe_unit( $attributes[ $stem . 'Unit' ] ?? 'px' );
		$t    = $attributes[ $stem . 'Top' ] ?? '';
		$r    = $attributes[ $stem . 'Right' ] ?? '';
		$b    = $attributes[ $stem . 'Bottom' ] ?? '';
		$l    = $attributes[ $stem . 'Left' ] ?? '';
		if ( '' === $t && '' === $r && '' === $b && '' === $l ) {
			return '';
		}
		$is_m = false !== stripos( (string) $stem, 'margin' );
		if ( $is_m && ! ( (float) $t || (float) $r || (float) $b || (float) $l ) ) {
			return '';
		}
		$side = static function ( $n ) use ( $unit ) {
			return '' !== $n ? (float) $n . $unit : '0' . $unit;
		};
		return ( $is_m ? 'margin:' : 'padding:' ) . $side( $t ) . ' ' . $side( $r ) . ' ' . $side( $b ) . ' ' . $side( $l );
	}

	/**
	 * True if padding/margin stem has any side set.
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $stem       Attribute stem (e.g. buttonPadding).
	 * @return bool
	 */
	public static function has_spacing( $attributes, $stem ) {
		return (bool) self::spacing_box_decl( $attributes, $stem );
	}

	/**
	 * Field size rules: font-size / letter-spacing / line-height (from ELB_Typography::values)
	 * and padding / margin, each with its --elb-field-* twin.
	 *
	 * @param array $attributes Block attributes.
	 * @return string[]
	 */
	public static function field_size_decls( $attributes ) {
		$rules = [];
		$typo  = ELB_Typography::values( (array) $attributes );
		foreach ( [ 'font-size', 'letter-spacing', 'line-height' ] as $prop ) {
			if ( isset( $typo[ $prop ] ) ) {
				$rules[] = $prop . ':' . $typo[ $prop ];
				// Stylesheets read the font-size and letter-spacing twins only.
				if ( 'line-height' !== $prop ) {
					$rules[] = '--elb-field-' . $prop . ':' . $typo[ $prop ];
				}
			}
		}
		return array_merge( $rules, self::spacing_decls( $attributes, '', true ) );
	}

	/**
	 * Padding and margin declarations, optionally with their --elb-field-* twins.
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $prefix     Attribute prefix ('' for the block's own padding/margin, e.g. 'button', 'cat').
	 * @param bool   $with_vars  Add --elb-field-padding / --elb-field-margin twins.
	 * @return string[]
	 */
	public static function spacing_decls( $attributes, string $prefix = '', bool $with_vars = false ): array {
		$decls = [];
		foreach ( [ 'padding' => 'Padding', 'margin' => 'Margin' ] as $prop => $stem ) {
			$decl = self::spacing_box_decl( $attributes, '' === $prefix ? $prop : $prefix . $stem );
			if ( '' === $decl ) {
				continue;
			}
			$decls[] = $decl;
			if ( $with_vars ) {
				$decls[] = '--elb-field-' . $prop . ':' . self::spacing_box_value( $decl, $prop );
			}
		}
		return $decls;
	}




	/**
	 * Margin sides that are set: side => '{n}{unit}'.
	 *
	 * @param array<string,mixed> $attributes Block attributes (marginTop, marginUnit, …).
	 * @return array<string,string>
	 */
	public static function margin_sides( array $attributes ): array {
		$sides = [];
		$unit  = ELB_Css_Utils::safe_unit( $attributes['marginUnit'] ?? 'px' );
		foreach ( [ 'Top', 'Right', 'Bottom', 'Left' ] as $cap ) {
			$v = $attributes[ 'margin' . $cap ] ?? '';
			if ( '' !== $v ) {
				$sides[ strtolower( $cap ) ] = (float) $v . $unit;
			}
		}
		return $sides;
	}

	/**
	 * Margin shorthand rules (with !important) for field wrappers.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @return string[]
	 */
	public static function field_margin_decls( array $attributes ): array {
		$decl = self::spacing_box_decl( $attributes, 'margin' );
		if ( '' === $decl ) {
			return [];
		}
		$box   = explode( ' ', substr( $decl, strlen( 'margin:' ) ) );
		$rules = [ 'margin:' . implode( ' ', $box ) . ' !important' ];
		if ( (float) $box[1] || (float) $box[3] ) {
			$rules[] = 'max-width:calc(100% - ' . $box[1] . ' - ' . $box[3] . ') !important';
		}
		return $rules;
	}

	/**
	 * Wrapper margin CSS from field_margin_decls(). Image overlays are positioned
	 * absolutely and keep their own placement.
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $resp_class Sanitized scoped class.
	 * @return string
	 */
	public static function wrapper_margin_css( $attributes, $resp_class ) {
		$sel = '.elb-loop-output .' . $resp_class . str_repeat( '.' . $resp_class, 4 ) . ':not([class*="--on-image"])';
		return ELB_Breakpoints::rule( $sel, self::field_margin_decls( (array) $attributes ) );
	}

	/**
	 * Scoped CSS for a field's typography and padding/margin, plus its wrapper
	 * margins (with !important) when the rules target the wrapper itself.
	 *
	 * @param array  $attributes     Block attributes.
	 * @param string $resp_class     Shared design class.
	 * @param string $field_selector Inner element the rules target ('' = the wrapper).
	 */
	public static function field_scoped_css( $attributes, $resp_class, $field_selector = '' ) {
		$class = sanitize_html_class( (string) $resp_class );
		if ( '' === $class ) {
			return '';
		}
		$css = ELB_Breakpoints::rule(
			self::scoped_selector( $class, (string) $field_selector ),
			self::field_size_decls( $attributes )
		);
		if ( '' === (string) $field_selector ) {
			$css .= self::wrapper_margin_css( $attributes, $class );
		}
		return $css;
	}

	/**
	 * Selector for a design class (and an optional descendant), repeated under .elb-loop-output
	 * so theme-compat resets (e.g. `.elb-loop-output h3 { padding:0 }`) cannot wipe spacing.
	 *
	 * @param string $class  Sanitized design class.
	 * @param string $suffix Descendant selector ('' for the element itself).
	 */
	public static function scoped_selector( string $class, string $suffix ): string {
		$suffix = '' === $suffix ? '' : ' ' . ltrim( $suffix );
		return '.elb-loop-output .' . $class . $suffix . ',.' . $class . $suffix;
	}
}
