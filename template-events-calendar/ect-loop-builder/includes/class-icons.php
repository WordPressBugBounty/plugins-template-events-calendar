<?php
/**
 * Block icons and share-network SVGs.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

class ELB_Icons { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/** Icon-to-text gap when flexGap is not set. */
	const DEFAULT_GAP = '6px';


	/** Share network => inline SVG (event-share block). */
	private const INLINE_ICONS = [
		'facebook' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047v-2.66c0-3.025 1.792-4.697 4.533-4.697 1.313 0 2.686.236 2.686.236v2.971H15.83c-1.491 0-1.956.93-1.956 1.886v2.264h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/></svg>',
		'twitter'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
		'copy'     => '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>',
	];

	/**
	 * Returns the icon-name => SVG-markup lookup table generated from the
	 * @wordpress/icons set used by the block editor's icon picker.
	 *
	 * @see source/bin/generate-icon-svgs.js (run from source/)
	 */
	public static function get_icon_svg_map() {
		static $map = null;
		if ( null === $map ) {
			$file = ELB_PATH . 'includes/data/icon-svgs.php';
			$map  = file_exists( $file ) ? (array) include $file : [];
			// The picker offers the built-in clock too; add it once instead of on every render.
			// No hard-coded width/height — icon() injects those via svg_with_attrs() so the
			// rendered markup never carries duplicate width/height attributes.
			$map['clock'] = $map['clock'] ?? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10" fill="none"/><polyline points="12 6 12 12 16 14" fill="none"/></svg>';
		}
		return $map;
	}

	/**
	 * Raw SVG markup of a picker icon (including the built-in clock), or ''.
	 *
	 * @param string $name Icon name.
	 */
	public static function svg( string $name ): string {
		return self::get_icon_svg_map()[ $name ] ?? '';
	}

	/**
	 * Insert a raw attribute string into an SVG's opening tag (right after `<svg `).
	 * Shared low-level helper: icon() always inserts its width/height/style; callers that only
	 * want to add an attribute when it is missing (e.g. aria-hidden/focusable) build that
	 * conditional attribute string themselves and pass it in here.
	 *
	 * @param string $svg   Raw <svg …> markup.
	 * @param string $attrs Attribute string to insert (e.g. 'width="18" height="18" '), or ''.
	 * @return string
	 */
	public static function svg_with_attrs( string $svg, string $attrs ): string {
		if ( '' === $attrs || '' === $svg ) {
			return $svg;
		}
		return (string) preg_replace( '/<svg /', '<svg ' . $attrs, $svg, 1 );
	}

	/**
	 * Icon $name at $size px in an .elb-block-icon span, optionally coloured, or '' for unknown icons.
	 *
	 * @param string       $name  Icon name.
	 * @param float|string $size  Size in px.
	 * @param string       $color Icon colour (sanitized here).
	 */
	public static function icon( string $name, $size = 18, string $color = '' ): string {
		$svg = self::svg( $name );
		if ( '' === $svg ) {
			return '';
		}
		$size       = (float) $size;
		$icon_color = ELB_Css_Utils::sanitize_css_color( $color );

		$svg = self::svg_with_attrs( $svg, 'width="' . $size . '" height="' . $size . '" style="flex-shrink:0" ' );

		$span_style = '';
		if ( $icon_color ) {
			$span_style .= 'color:' . $icon_color . ';';
		}

		$style_attr = $span_style ? ' style="' . esc_attr( $span_style ) . '"' : '';
		return '<span class="elb-block-icon"' . $style_attr . '>' . $svg . '</span>';
	}

	/**
	 * Put an icon before or after the text.
	 *
	 * @param string $icon_html Icon markup ('' leaves the text alone).
	 * @param string $text_html Text markup.
	 * @param string $pos       'right' / 'after' put the icon after the text; anything else before it.
	 */
	public static function place( string $icon_html, string $text_html, string $pos ): string {
		return in_array( $pos, [ 'right', 'after' ], true ) ? $text_html . $icon_html : $icon_html . $text_html;
	}

	/**
	 * Renders the block-icon markup for a field block (Title, Excerpt, Date, …).
	 *
	 * @param array  $attributes   Block attributes.
	 * @param string $default_icon Icon when blockIcon is empty (Time: clock).
	 * @param bool   $default_on   Show the icon when showBlockIcon is unset.
	 * @return string Icon HTML or empty string.
	 */
	public static function render_block_icon( $attributes, string $default_icon = '', bool $default_on = false ) {
		$show = array_key_exists( 'showBlockIcon', $attributes ) ? ! empty( $attributes['showBlockIcon'] ) : $default_on;
		$name = (string) ( $attributes['blockIcon'] ?? '' ) ?: $default_icon;
		if ( ! $show || '' === $name ) {
			return '';
		}
		return self::icon(
			$name,
			$attributes['blockIconSize'] ?? 18,
			(string) ( $attributes['blockIconColor'] ?? '' )
		);
	}

	/**
	 * Where a text block's icon goes, for blocks that render icon + linked text (Title, Excerpt).
	 *
	 * @param array $attributes Block attributes.
	 * @param bool  $is_link    Whether the text is linked.
	 * @return array{html:string,pos:string,outside:bool,styles:string[]}
	 */
	public static function text_block_icon( $attributes, $is_link ) {
		$icon_html    = self::render_block_icon( $attributes );
		$icon_outside = (bool) $icon_html;

		return [
			'html'    => $icon_html,
			'pos'     => 'right' === ( $attributes['blockIconPos'] ?? 'left' ) ? 'right' : 'left',
			'outside' => $icon_outside,
			'styles'  => self::icon_flex_decls( $attributes, $icon_outside ),
		];
	}

	/**
	 * Escaped text with its icon and optional link, placed as set by text_block_icon().
	 *
	 * @param array  $icon         Result of text_block_icon().
	 * @param string $text_html    Already escaped text.
	 * @param string $permalink    Link URL.
	 * @param bool   $is_link      Whether the text is linked.
	 * @param bool   $link_new_tab Open the link in a new tab.
	 * @return string
	 */
	public static function text_with_icon( array $icon, $text_html, $permalink, $is_link, $link_new_tab ) {
		$icon_html = $icon['html'];
		$text      = ELB_Event_Markup::wrap_optional_link( $text_html, $permalink, $is_link, $link_new_tab );
		return $icon['outside'] ? self::place( $icon_html, $text, $icon['pos'] ) : $text;
	}

	/**
	 * Wrap content with block icon (mirrors editor withBlockIcon).
	 *
	 * @param string $content    Inner HTML.
	 * @param array  $attributes Block attributes.
	 * @param array  $args       Optional: 'wrapper_class' (default 'elb-block-icon-wrap'), 'style'
	 *                           (default the inline flex/gap style; '' for none), 'four_way' (true to
	 *                           normalize blockIconPos to left/right/top/bottom via normalize_position()
	 *                           and treat right/bottom as "after" — default false keeps the original
	 *                           raw-value/left-right-only behavior unchanged for existing callers).
	 * @return string
	 */
	public static function with_block_icon( $content, $attributes, array $args = [] ) {
		$icon_html = self::render_block_icon( $attributes );
		if ( ! $icon_html ) {
			return $content;
		}
		$four_way      = ! empty( $args['four_way'] );
		$position      = $four_way ? self::normalize_position( $attributes ) : (string) ( $attributes['blockIconPos'] ?? 'left' );
		$place_pos     = $four_way ? self::place_side( $position ) : $position;
		$wrapper_class = $args['wrapper_class'] ?? 'elb-block-icon-wrap';
		$gap           = ELB_Layout_Decls::flex_gap( $attributes, self::DEFAULT_GAP );
		$style         = $args['style'] ?? ( 'display:inline-flex;align-items:center;gap:' . $gap . ';' );
		$style_attr    = '' !== $style ? ' style="' . esc_attr( $style ) . '"' : '';

		return '<span class="' . esc_attr( $wrapper_class ) . '"' . $style_attr . '>'
			. self::place( $icon_html, $content, $place_pos ) . '</span>';
	}

	/** Normalize an icon position attribute to one of left/right/top/bottom (default left). */
	public static function normalize_position( array $attributes ): string {
		$position = (string) ( $attributes['blockIconPos'] ?? 'left' );
		return in_array( $position, [ 'left', 'right', 'top', 'bottom' ], true ) ? $position : 'left';
	}

	/** Side ('right' or 'left') to pass to place() for a 4-way icon position — right/bottom means "after". */
	public static function place_side( string $position ): string {
		return in_array( $position, [ 'right', 'bottom' ], true ) ? 'right' : 'left';
	}

	/**
	 * Icon-aware flex CSS for blocks that render icon + text as direct siblings.
	 *
	 * @return string[] Style declarations to append to the block's style array.
	 */
	public static function icon_flex_decls( $attributes, $icon_outside ) {
		$flex_direction = sanitize_key( $attributes['flexDirection'] ?? '' );
		if ( ! $icon_outside && ! $flex_direction ) {
			return [];
		}
		return ELB_Layout_Decls::flex_decls(
			$attributes,
			[
				'display'   => $flex_direction ? 'flex' : 'inline-flex',
				'direction' => 'row',
				'align'     => 'center',
				'gap'       => self::DEFAULT_GAP,
			]
		);
	}

	/**
	 * Inline SVG icon for a share network (event-share block).
	 *
	 * @param string $network facebook|twitter|copy.
	 * @return string SVG markup, or '' for unknown networks.
	 */
	public static function inline_icon( $name ) {
		return self::INLINE_ICONS[ $name ] ?? '';
	}
}
