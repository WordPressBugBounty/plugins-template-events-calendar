<?php
/**
 * Read More / ticket button style declarations (colours, typography, border). Markup lives in ELB_Ticket_Button.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

class ELB_Ticket_Button_Styles { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/** Default ticket button colour (matches --elb-blue-600 and the [style*="background-color:#2563eb"] CSS hooks). */
	const DEFAULT_BUTTON_BG = '#2563eb';

	/** Ticket button presets that add a preset class (primary is the default look). */
	const BUTTON_PRESETS = [ 'primary', 'subtle' ];

	/** Default button padding and border radius chrome. */
	const DEFAULT_PADDING = 'padding:8px 18px;';
	const DEFAULT_RADIUS  = 'border-radius:6px;';

	/**
	 * Inline styles for the ticket / read-more button and link variants.
	 *
	 * Unprefixed Typography panel attrs (Read More) are merged onto the control.
	 *
	 * @param array $attributes Block attributes.
	 * @return array{button: string, link: string}
	 */
	public static function ticket_button_inline_styles( $attributes ) {
		$colors = self::ticket_button_colors( $attributes );

		$btn_style  = self::ticket_button_style( $colors );
		$link_style = self::ticket_link_style( $attributes, $colors );

		$typo_append = self::typography_style( $attributes );
		$btn_style  .= $typo_append;
		$link_style .= $typo_append;

		$decoration_append = self::decoration_style( $attributes );
		$btn_style  .= $decoration_append;
		$link_style .= $decoration_append;

		return [
			'button' => $btn_style,
			'link'   => $link_style,
		];
	}

	/**
	 * Device-independent Typography-panel CSS to append (Read More only), or ''.
	 *
	 * Font-family/weight/style/decoration/transform only — same value on every breakpoint, so
	 * inlining them on the anchor is safe. The *sized* props (font-size/letter-spacing/line-height)
	 * are deliberately left out here — Read More anchors them responsively via
	 * ELB_Ticket_Button_Styles::anchor_size_css() instead (see ELB_Event_Read_More_Renderer),
	 * since an inline size would always outrank a later CSS rule.
	 *
	 * @param array $attributes Block attributes.
	 */
	private static function typography_style( $attributes ) {
		$typo = implode(
			';',
			ELB_Typography::declarations(
				(array) $attributes,
				null,
				[ 'font-family' => 'twin', 'font-weight' => 'twin', 'font-style' => 'twin', 'text-transform' => 'twin', 'text-decoration' => 'var' ],
				ELB_Typography::FONT_PROPS
			)
		);
		// Append after defaults so user Typography panel values win.
		return $typo ? $typo . ';' : '';
	}

	/**
	 * !important text-decoration override to append, or ''.
	 *
	 * Button padding/margin (buttonPadding*, buttonMargin*) are emitted as scoped CSS targeting
	 * .elb-ticket-btn / .elb-ticket-link, so they are not baked in here — an inline style always
	 * wins over a non-!important CSS rule.
	 *
	 * Theme compatibility intentionally resets anchor decoration with !important.
	 * Re-assert only an explicit Typography value.
	 *
	 * @param array $attributes Block attributes.
	 */
	private static function decoration_style( $attributes ) {
		$decoration = ELB_Css_Utils::safe_keyword( $attributes['textDecoration'] ?? '', ELB_Css_Utils::TEXT_DECORATIONS );
		return $decoration ? 'text-decoration:' . esc_attr( $decoration ) . ' !important;' : '';
	}

	/**
	 * Colours for the ticket / read-more control, with the "invisible on a white card" cases fixed.
	 *
	 * @param array $attributes Block attributes.
	 * @return array{bg:string,color:string,hover_bg:string,hover_color:string,decoration:string,paint_bg:string,resolved_color:string,bg_is_opaque:bool}
	 */
	private static function ticket_button_colors( $attributes ) {
		$btn_bg          = ELB_Css_Utils::sanitize_css_color( $attributes['buttonBg'] ?? '' );
		$btn_color       = ELB_Css_Utils::sanitize_css_color( $attributes['buttonTextColor'] ?? '' );
		$td              = $attributes['textDecoration'] ?? '';
		$td_safe         = ELB_Css_Utils::safe_keyword( $td, ELB_Css_Utils::TEXT_DECORATIONS ) ?: 'none';

		// Transparent fill + white text is invisible on white cards — restore solid Primary.
		// Also treat white-on-white (solid white fill + white text) the same way.
		$bg_is_transparent = ELB_Css_Utils::is_transparent_css_color( $btn_bg );
		$is_white          = static fn( $c ) => in_array( strtolower( (string) $c ), [ '#fff', '#ffffff', 'white' ], true );
		$bg_is_white       = ! $bg_is_transparent && $is_white( $btn_bg );
		$color_is_white    = '' === $btn_color || $is_white( $btn_color );
		if ( ( $bg_is_transparent || $bg_is_white ) && $color_is_white ) {
			$btn_bg            = self::DEFAULT_BUTTON_BG;
			$btn_color         = '#fff';
			$bg_is_transparent = false;
		}

		$resolved_bg    = $bg_is_transparent ? self::DEFAULT_BUTTON_BG : $btn_bg;
		$resolved_color = $btn_color ? $btn_color : '#fff';
		if ( 0 === strcasecmp( $resolved_color, $resolved_bg ) ) {
			$resolved_color = '#fff';
		}

		// Outlined List Classic keeps transparent paint when text is a visible (non-white) accent.
		$paint_bg = ( $bg_is_transparent && ! $color_is_white ) ? 'transparent' : $resolved_bg;

		return [
			'bg'             => $btn_bg,
			'color'          => $btn_color,
			'hover_bg'       => ELB_Css_Utils::sanitize_css_color( $attributes['buttonHoverBg'] ?? '' ),
			'hover_color'    => ELB_Css_Utils::sanitize_css_color( $attributes['buttonHoverTextColor'] ?? '' ),
			'decoration'     => $td_safe,
			'paint_bg'       => $paint_bg,
			'resolved_color' => $resolved_color,
			'bg_is_opaque'   => ! $bg_is_transparent,
		];
	}

	/**
	 * Inline style for the ticket / read-more button variant.
	 *
	 * Padding/margin come from ELB_Ticket_Button_Styles::child_css() as scoped CSS instead —
	 * see that method and ticket_button_inline_styles(). The static `.elb-ticket-btn` rule in
	 * frontend.css (padding, radius, weight, size) is the fallback look when nothing is set.
	 *
	 * @param array $colors Result of ticket_button_colors().
	 */
	private static function ticket_button_style( array $colors ) {
		$btn_style  = 'text-decoration:' . esc_attr( $colors['decoration'] ) . ';';
		$btn_style .= 'background-color:' . esc_attr( $colors['paint_bg'] ) . ';';
		$btn_style .= 'color:' . esc_attr( $colors['resolved_color'] ) . ';';
		// Do NOT set -webkit-text-fill-color here — Look presets (Subtle/Expandable) paint
		// label colour from --elb-btn-bg via CSS; an inline white fill hides that text.
		if ( $colors['bg_is_opaque'] ) {
			$btn_style .= '--elb-btn-bg:' . esc_attr( $colors['bg'] ) . ';';
		}
		$btn_style .= '--elb-btn-color:' . esc_attr( $colors['resolved_color'] ) . ';';
		// Outline: hard border + no fill so the control is visible before hover.
		if ( 'transparent' === $colors['paint_bg'] ) {
			$btn_style .= 'border:1.5px solid ' . esc_attr( $colors['resolved_color'] ) . ';';
		}
		$btn_style .= self::ticket_hover_vars( $colors );
		return $btn_style . self::DEFAULT_RADIUS . 'display:inline-block;';
	}

	/**
	 * Inline style for the ticket / read-more link variant.
	 *
	 * Padding/margin come from ELB_Ticket_Button_Styles::child_css() as scoped CSS instead —
	 * see ticket_button_style() and ticket_button_inline_styles(). The static `.elb-ticket-link`
	 * rule in frontend.css (size, weight) is the fallback look when nothing is set.
	 *
	 * @param array $attributes Block attributes.
	 * @param array $colors     Result of ticket_button_colors().
	 */
	private static function ticket_link_style( $attributes, array $colors ) {
		$btn_bg     = $colors['bg'];
		$btn_color  = $colors['color'];
		$link_style = 'text-decoration:' . esc_attr( $colors['decoration'] ) . ';display:inline-block;';
		if ( $btn_color ) {
			$link_style .= 'color:' . esc_attr( $btn_color ) . ';';
		} elseif ( ! $btn_bg ) {
			// With a background, the block below sets color:#fff instead.
			$link_style .= 'color:' . self::DEFAULT_BUTTON_BG . ';';
		}
		if ( $btn_bg ) {
			$link_style .= 'background-color:' . esc_attr( $btn_bg ) . ';' . self::DEFAULT_RADIUS;
			$link_style .= '--elb-btn-bg:' . esc_attr( $btn_bg ) . ';';
			// A link styled as a button (background set) still needs button-like padding when no
			// button padding is set; a set value flows via child_css().
			if ( ! ELB_Spacing_Styles::has_spacing( $attributes, 'buttonPadding' ) ) {
				$link_style .= self::DEFAULT_PADDING;
			}
			if ( ! $btn_color ) {
				$link_style .= 'color:#fff;--elb-btn-color:#fff;';
			} else {
				$link_style .= '--elb-btn-color:' . esc_attr( $btn_color ) . ';';
			}
		}
		$link_style .= self::ticket_hover_vars( $colors );
		return $link_style;
	}

	/**
	 * Hover colour custom properties shared by the ticket button and link styles.
	 *
	 * @param array $colors Result of ticket_button_colors().
	 */
	private static function ticket_hover_vars( array $colors ) {
		$css = '';
		if ( $colors['hover_bg'] ) {
			$css .= '--elb-btn-hover-bg:' . esc_attr( $colors['hover_bg'] ) . ';';
		}
		if ( $colors['hover_color'] ) {
			$css .= '--elb-btn-hover-color:' . esc_attr( $colors['hover_color'] ) . ';';
		}
		return $css;
	}

	/**
	 * Padding/margin of the Read More button / link (buttonPadding* / buttonMargin*).
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $resp_class Shared design class.
	 */
	public static function child_css( $attributes, $resp_class ) {
		$class = sanitize_html_class( (string) $resp_class );
		if ( '' === $class ) {
			return '';
		}
		return ELB_Breakpoints::rule( self::anchor_selector( $class ), ELB_Spacing_Styles::spacing_decls( $attributes, 'button' ) );
	}

	/**
	 * Font-size/letter-spacing/line-height of the block's own (unprefixed) Typography
	 * panel values, scoped directly to the ticket button/link anchor.
	 *
	 * Read More inlines the other font props (family/weight/style/decoration/transform — see
	 * ELB_Ticket_Button_Styles::ticket_button_inline_styles()); the sized props are emitted here.
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $resp_class Shared design class.
	 */
	public static function anchor_size_css( $attributes, $resp_class ) {
		$class = sanitize_html_class( (string) $resp_class );
		if ( '' === $class ) {
			return '';
		}
		$rules = ELB_Typography::declarations( (array) $attributes, null, [], [ 'font-size', 'letter-spacing', 'line-height' ] );
		return ELB_Breakpoints::rule( self::anchor_selector( $class ), $rules );
	}

	/**
	 * The ticket button and link anchors inside a design class (scoped as ELB_Spacing_Styles::scoped_selector()).
	 *
	 * @param string $class Sanitized design class.
	 */
	private static function anchor_selector( string $class ): string {
		return ELB_Spacing_Styles::scoped_selector( $class, '.elb-ticket-btn' ) . ',' . ELB_Spacing_Styles::scoped_selector( $class, '.elb-ticket-link' );
	}
}
