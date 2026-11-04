<?php
/**
 * Base for Event Meta item renderers (one class per item type) and their shared badge / <time> helpers.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

abstract class ELB_Meta_Item { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/** Default icon name ('' for none). */
	const ICON = '';

	/**
	 * Item markup, or '' when the event has no value.
	 *
	 * @param int                 $post_id       Event ID.
	 * @param array<string,mixed> $item_settings Item settings.
	 * @param array<string,mixed> $item          Item (its own badge Style / colours).
	 */
	abstract public static function render( int $post_id, array $item_settings, array $item = [] ): string;

	/**
	 * Style this item has (its own `badgeStyle`, Plain when unset).
	 *
	 * @param array<string,mixed> $item       Item.
	 * @return string One of ELB_Event_Meta_Renderer::BADGE_STYLES.
	 */
	public static function badge_style( array $item ): string {
		$style = sanitize_key( (string) ( $item['badgeStyle'] ?? 'text' ) );
		if ( ! in_array( $style, ELB_Event_Meta_Renderer::BADGE_STYLES, true ) ) {
			return 'text';
		}
		return $style;
	}

	/**
	 * ' class="elb-meta__badge elb-meta__badge--solid" style="--elb-meta-badge-fg:#fff;"', or ''
	 * when this item's style is Plain — ready to drop straight into a tag's opening angle bracket.
	 *
	 * @param array<string,mixed> $item       Item.
	 * @return string
	 */
	public static function badge_attrs_html( array $item ): string {
		$style = ELB_Meta_Item::badge_style( $item );
		if ( 'text' === $style ) {
			return '';
		}
		$vars = '';
		$fg   = ELB_Css_Utils::sanitize_css_color( $item['badgeTextColor'] ?? '' );
		$bg   = ELB_Css_Utils::sanitize_css_color( $item['badgeBackgroundColor'] ?? '' );
		if ( $fg ) {
			$vars .= '--elb-meta-badge-fg:' . esc_attr( $fg ) . ';';
		}
		if ( $bg ) {
			$vars .= '--elb-meta-badge-bg:' . esc_attr( $bg ) . ';';
		}
		return ' class="elb-meta__badge elb-meta__badge--' . esc_attr( $style ) . '"' . ( $vars ? ' style="' . esc_attr( $vars ) . '"' : '' );
	}

	/** The item's icon markup (set by ELB_Event_Meta_Renderer) for a badge opening tag; '' for a bare tag. */
	public static function badge_icon( array $item, string $attrs ): string {
		return '' !== $attrs ? (string) ( $item['iconHtml'] ?? '' ) : '';
	}

	/** Wrap already-escaped $text (or the given $url as a link) in this item's badge Style, or not, if Plain. */
	public static function wrap_with_item_style( string $text, array $item, string $url = '' ): string {
		$attrs = ELB_Meta_Item::badge_attrs_html( $item );
		if ( $url ) {
			return '<a' . $attrs . ' href="' . esc_url( $url ) . '">' . self::badge_icon( $item, $attrs ) . $text . '</a>';
		}
		return '' === $attrs ? $text : '<span' . $attrs . '>' . self::badge_icon( $item, $attrs ) . $text . '</span>';
	}

	/** <time>, optionally wrapped in this item's badge Style. */
	public static function time_tag( string $text, string $iso, array $item = [] ): string {
		if ( '' === $text ) {
			return '';
		}
		$time = '<time' . ( $iso ? ' datetime="' . esc_attr( $iso ) . '"' : '' ) . '>' . esc_html( $text ) . '</time>';
		$attrs = ELB_Meta_Item::badge_attrs_html( $item );
		return '' === $attrs ? $time : '<span' . $attrs . '>' . self::badge_icon( $item, $attrs ) . $time . '</span>';
	}

}
