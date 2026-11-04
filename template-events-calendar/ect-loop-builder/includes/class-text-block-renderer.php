<?php
/**
 * Base for text field blocks (Event Title, Event Excerpt): field styles, icon,
 * hover vars, scoped CSS and the text-with-icon wrapper.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

abstract class ELB_Text_Block_Renderer extends ELB_Field_Block_Renderer { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/** Hover CSS var prefix ('title', 'exc'). */
	const HOVER_PREFIX = '';

	/** Hover rules use !important. */
	const HOVER_IMPORTANT = false;

	/** Hover text colour also targets the inner link. */
	const HOVER_LINK = false;

	/** Wrapper base class. */
	const BASE_CLASS = '';

	/**
	 * Plain text to show, or null to render nothing.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param int                 $post_id    Event post ID.
	 */
	abstract protected static function text( array $attributes, int $post_id ): ?string;

	/**
	 * Wrapper tag name.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 */
	abstract protected static function tag( array $attributes ): string;

	/**
	 * Hover colour / hover bg, hover border, focus-visible and responsive typography CSS.
	 *
	 * @param string              $resp_class Design class.
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param bool                $is_link    Whether the block wraps an anchor.
	 * @return string CSS rules for the text field.
	 */
	protected static function text_css( string $resp_class, array $attributes, bool $is_link ): string {
		$var_prefix = static::HOVER_PREFIX;
		$important  = static::HOVER_IMPORTANT;
		$hover_text = ELB_Css_Utils::sanitize_css_color( $attributes['hoverTextColor'] ?? '' );
		$hover_bg   = ELB_Css_Utils::sanitize_css_color( $attributes['hoverBackgroundColor'] ?? '' );
		$css        = '';
		if ( $hover_text || $hover_bg ) {
			$css .= ELB_Hover_Css::hover_rule(
				'.' . $resp_class . ':hover',
				$hover_text ? 'var(--elb-' . $var_prefix . '-hover-color)' : '',
				$hover_bg ? 'var(--elb-' . $var_prefix . '-hover-bg)' : '',
				$important
			);
			if ( $hover_text && static::HOVER_LINK ) {
				$css .= ELB_Hover_Css::hover_rule( '.' . $resp_class . ':hover a', 'var(--elb-' . $var_prefix . '-hover-color)', '', $important );
			}
		}
		$css .= ELB_Hover_Css::hover_border_css( $resp_class, $attributes );
		if ( $is_link ) {
			$css .= '.' . $resp_class . ' a:focus-visible{outline:2px solid currentColor;outline-offset:2px;}';
		}
		return $css . ELB_Spacing_Styles::field_scoped_css( $attributes, $resp_class );
	}

	/**
	 * Link settings [ is_link, new_tab, url ]; no link by default.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param int                 $post_id    Event post ID.
	 * @return array{0:bool,1:bool,2:string}
	 */
	protected static function link( array $attributes, int $post_id ): array {
		return [ false, false, '' ];
	}

	/**
	 * Declarations placed right after the field styles (e.g. a default font size).
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @return string[]
	 */
	protected static function size_decls( array $attributes ): array {
		return [];
	}

	/**
	 * Declarations placed after the icon styles.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @return string[]
	 */
	protected static function extra_decls( array $attributes ): array {
		return [];
	}

	/**
	 * Inline style rules (pure: no side effects).
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param array<string,mixed> $icon       Resolved icon data.
	 * @return string[]
	 */
	private static function build_decls( array $attributes, array $icon ): array {
		// field_decls() already appends hover_color_vars() with its default 'f' prefix, which
		// nothing reads for this renderer: the frontend :hover rule for Title/Excerpt reads the
		// HOVER_PREFIX-specific var (--elb-title-hover-color / --elb-exc-hover-color, see
		// frontend.css), not --elb-f-hover-color. Drop the unused 'f'-prefixed pair so it is not
		// double-appended alongside the one this block actually needs.
		$styles = array_filter(
			ELB_Field_Styles::field_decls( $attributes ),
			static function ( $decl ) {
				return 0 !== strpos( (string) $decl, '--elb-f-hover-' );
			}
		);
		array_push( $styles, ...static::size_decls( $attributes ) );
		array_push( $styles, ...ELB_Field_Styles::text_block_base_decls( $attributes ) );
		array_push( $styles, ...$icon['styles'] );
		array_push( $styles, ...static::extra_decls( $attributes ) );
		array_push( $styles, ...ELB_Field_Styles::hover_color_vars( $attributes, static::HOVER_PREFIX ) );
		return array_filter( $styles );
	}

	/**
	 * Text block HTML.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param int                 $post_id    Event post ID.
	 * @return string Field HTML (may be empty).
	 */
	protected static function render_field( array $attributes, int $post_id ): string {
		$text = static::text( $attributes, $post_id );
		if ( null === $text ) {
			return '';
		}
		[ $is_link, $new_tab, $url ] = static::link( $attributes, $post_id );
		$attr_hash                   = static::attr_hash( $attributes );

		$resp_class = self::design(
			$attributes,
			static function ( string $resp_class ) use ( $attributes, $is_link ): string {
				return static::text_css( $resp_class, $attributes, $is_link );
			},
			$attr_hash
		);
		// Keyed by the attributes too: blocks with different settings must not share a cached icon / style string.
		$memo_key = $attr_hash . ':' . ( $is_link ? '1' : '0' );
		$icon     = static::memo(
			'icon:' . $memo_key,
			static function () use ( $attributes, $is_link ): array {
				return ELB_Icons::text_block_icon( $attributes, $is_link );
			}
		);
		$style_str = static::memo(
			'styles:' . $memo_key,
			static function () use ( $attributes, $icon ): string {
				return implode( ';', self::build_decls( $attributes, $icon ) );
			}
		);
		$wrapper_attrs = get_block_wrapper_attributes(
			[
				'class' => static::BASE_CLASS . ' ' . $resp_class,
				'style' => $style_str,
			]
		);
		$tag = static::tag( $attributes );

		return '<' . $tag . ' ' . $wrapper_attrs . '>' . ELB_Icons::text_with_icon( $icon, esc_html( $text ), $url, $is_link, $new_tab ) . '</' . $tag . '>';
	}
}
