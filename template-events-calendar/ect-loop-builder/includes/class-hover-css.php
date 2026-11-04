<?php
/**
 * Hover / focus CSS for field blocks.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Hover_Css { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/**
	 * Build a :hover rule for color / background.
	 *
	 * @param string $sel       CSS selector list.
	 * @param string $color     Color value (may be a var()).
	 * @param string $bg        Background value (may be a var()).
	 * @param bool   $important Append !important.
	 * @return string CSS or ''.
	 */
	public static function hover_rule( string $sel, string $color, string $bg, bool $important = true ): string {
		if ( '' === $sel || ( '' === $color && '' === $bg ) ) {
			return '';
		}
		$imp = $important ? ' !important' : '';
		$css = $sel . '{';
		if ( $color ) {
			$css .= 'color:' . $color . $imp . ';';
		}
		if ( $bg ) {
			$css .= 'background-color:' . $bg . $imp . ';';
		}
		$css .= '}';
		return $css;
	}

	public static function hover_border_css( $resp_class, $attributes, $inner = '' ) {
		$resp_class = sanitize_html_class( (string) $resp_class );
		$color      = ELB_Css_Utils::sanitize_css_color( $attributes['borderColorHover'] ?? '' );
		if ( '' === $resp_class || ! $color || ! ELB_Field_Styles::has_border( $attributes ) ) {
			return '';
		}
		return '.' . $resp_class . ( $inner ? ' ' . $inner : '' ) . ':hover{border-color:' . esc_attr( $color ) . ' !important;}';
	}

	/**
	 * Hover CSS for a field block's unique class (color / background).
	 *
	 * @param string               $resp_class Unique class name without a leading dot.
	 * @param array                $attributes Block attributes.
	 * @param array<string, mixed> $args {
	 *     Optional. @type bool $wrapper_hover Whether bare `.class:hover` may
	 *     restyle the block wrapper. Disable for full-width rows that mix a
	 *     ticket button with a cost badge (Read more + Event Cost).
	 *     @type array<int,array{selector:string,wrapper?:bool}> $text_targets
	 *     Extra inner-element selectors that get their own `.class <selector>:hover`
	 *     rule. Set 'wrapper' => true to also add the `.class:hover <selector>`
	 *     companion when $wrapper_hover is on (used by the Time block; the cost
	 *     badge deliberately omits it — see text_hover_rules()).
	 * }
	 * @return string CSS or empty string.
	 */
	public static function field_hover_css( $resp_class, $attributes, $args = [] ) {
		$resp_class = sanitize_html_class( (string) $resp_class );
		if ( '' === $resp_class ) {
			return '';
		}

		return self::text_hover_rules( $resp_class, $attributes, $args ) . self::child_hover_rules( $resp_class, $attributes );
	}

	/**
	 * Hover color / background rule for the field's own wrapper, plus any $args['text_targets']
	 * inner-element selectors. Moved out of field_hover_css() verbatim; see its docblock for
	 * what $args controls.
	 *
	 * @param string               $resp_class Sanitized unique class name without a leading dot.
	 * @param array                $attributes Block attributes.
	 * @param array<string, mixed> $args       See field_hover_css().
	 * @return string CSS or ''.
	 */
	private static function text_hover_rules( string $resp_class, array $attributes, array $args ): string {
		$wrapper_hover = ! isset( $args['wrapper_hover'] ) || ! empty( $args['wrapper_hover'] );
		$targets       = $args['text_targets'] ?? [];
		$htc = ELB_Css_Utils::sanitize_css_color( $attributes['hoverTextColor'] ?? '' );
		$hbc = ELB_Css_Utils::sanitize_css_color( $attributes['hoverBackgroundColor'] ?? '' );
		if ( ! $htc && ! $hbc ) {
			return '';
		}

		// Date: no targets, styles live on the wrapper (bare :hover).
		// Time: color is inline on the inner span — target it directly, and also
		// via the wrapper (wrapper => true), since hovering anywhere on the Date/Time
		// row should restyle the time text too.
		// Cost badge: only `.elb-cost-preset:hover` — never the wrapper variant,
		// or hovering the sibling ticket button would restyle the cost.
		$sels         = [];
		$wrapper_sels = [];
		foreach ( $targets as $target ) {
			$sel = (string) ( $target['selector'] ?? '' );
			if ( '' === $sel ) {
				continue;
			}
			$sels[] = '.' . $resp_class . ' ' . $sel . ':hover';
			if ( $wrapper_hover && ! empty( $target['wrapper'] ) ) {
				$wrapper_sels[] = '.' . $resp_class . ':hover ' . $sel;
			}
		}
		if ( $wrapper_hover ) {
			array_unshift( $wrapper_sels, '.' . $resp_class . ':hover' );
		}
		$sel = implode( ',', array_merge( $wrapper_sels, $sels ) );
		return self::hover_rule( $sel, $htc, $hbc, true );
	}

	/**
	 * Hover color / background rule for the ticket/link controls nested inside the
	 * field (View Event / Get Tickets / Read More). Moved out of field_hover_css()
	 * verbatim.
	 *
	 * View Event / Get Tickets / Read More: inline `color` on the <a> beats
	 * a normal :hover rule. Only the actual control may trigger hover —
	 * the wrapper is full-width, so `.class:hover .elb-ticket-btn` would
	 * light up the button when hovering empty space on the same row.
	 *
	 * @param string $resp_class Sanitized unique class name without a leading dot.
	 * @param array  $attributes Block attributes.
	 * @return string CSS or ''.
	 */
	private static function child_hover_rules( string $resp_class, array $attributes ): string {
		$bhtc = ELB_Css_Utils::sanitize_css_color( $attributes['buttonHoverTextColor'] ?? '' );
		$bhbc = ELB_Css_Utils::sanitize_css_color( $attributes['buttonHoverBg'] ?? '' );
		if ( ! $bhtc && ! $bhbc ) {
			return '';
		}

		$sel = implode(
			',',
			[
				'.' . $resp_class . ' .elb-ticket-btn:hover',
				'.' . $resp_class . ' .elb-ticket-link:hover',
				'.' . $resp_class . ' a.elb-ticket-link:hover',
			]
		);
		return self::hover_rule( $sel, $bhtc, $bhbc, true );
	}

}
