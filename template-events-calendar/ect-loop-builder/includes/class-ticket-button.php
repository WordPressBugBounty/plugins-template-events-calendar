<?php
/**
 * Ticket / Read More anchor markup (button or text link).
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Ticket_Button { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/**
	 * Shared ticket-row CSS (hover, hover border, responsive) for one design class.
	 *
	 * @param string              $resp_class  Design class token.
	 * @param array<string,mixed> $attributes  Block attributes.
	 * @param string              $inner_sel   Inner element selector for the hover border.
	 * @param string              $resp_inner  Inner selector for field_scoped_css ('' for the wrapper).
	 * @param array<string,mixed> $hover_args  Extra field_hover_css() args (e.g. 'text_targets' => [ [ 'selector' => '.elb-cost-preset' ] ]
	 *                                         for rows that also show a cost badge, like Price + Button).
	 */
	public static function row_css( string $resp_class, array $attributes, string $inner_sel, string $resp_inner = '', array $hover_args = [] ): string {
		return ELB_Hover_Css::field_hover_css( $resp_class, $attributes, array_merge( [ 'wrapper_hover' => false ], $hover_args ) )
			. ELB_Hover_Css::hover_border_css( $resp_class, $attributes, $inner_sel )
			. ELB_Spacing_Styles::field_scoped_css( $attributes, $resp_class, $resp_inner )
			. ELB_Ticket_Button_Styles::child_css( $attributes, $resp_class );
	}

	/**
	 * Wrapper class and style for a ticket row (.elb-event-price-row); attribute-only, so callers memoize it.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param string              $resp_class Design class token.
	 * @param string              $extra      Extra classes.
	 * @return array{class:string,style:string}
	 */
	public static function row_wrapper_args( array $attributes, string $resp_class, string $extra ): array {
		return [
			'class' => trim( 'elb-event-price-row ' . ELB_Typography::field_classnames( $attributes ) . ' ' . $resp_class . ' ' . $extra ),
			'style' => ELB_Layout_Decls::row_layout_style( $attributes ),
		];
	}

	/**
	 * The ticket / read-more anchor for an event: link target and new-tab setting, optional block icon and presets.
	 *
	 * @param array<string,mixed> $attributes     Block attributes.
	 * @param int                 $post_id        Event post ID.
	 * @param string              $label          Button label (already sanitized).
	 * @param string              $btn_style      Inline style for the button look.
	 * @param string              $link_style     Inline style for the text-link look.
	 * @param string              $default_format 'button' or 'link' when the block sets no format.
	 */
	public static function event_anchor( array $attributes, int $post_id, string $label, string $btn_style, string $link_style, string $default_format ): string {
		[ $is_link, $new_tab, $url ] = ELB_Event_Link::link_settings( $attributes, $post_id );
		$format          = $attributes['format'] ?? $default_format;
		$is_auto_primary = empty( $attributes['buttonBg'] ) && empty( $attributes['buttonTextColor'] );
		return self::anchor( [
			'href'         => $is_link ? $url : '#',
			'new_tab'      => $new_tab,
			'format'       => $format,
			'style'        => 'link' === $format ? $link_style : $btn_style,
			'preset'       => $attributes['buttonPreset'] ?? 'primary',
			'inner'        => ELB_Icons::with_block_icon( esc_html( $label ), $attributes ),
			'auto_primary' => $is_auto_primary,
			'user_border'  => ELB_Field_Styles::has_border( $attributes ) || ELB_Field_Styles::has_border_radius( $attributes ),
		] );
	}

	/**
	 * Anchor markup.
	 *
	 * @param array{href:string,new_tab:bool,format:string,style:string,preset:string,inner:string,auto_primary?:bool,user_border?:bool} $options Options.
	 */
	private static function anchor( array $options ): string {
		$target  = $options['new_tab'] ? ' target="_blank" rel="noopener noreferrer"' : '';
		$classes = 'link' === $options['format'] ? 'elb-ticket-link' : 'elb-ticket-btn';
		if ( ! empty( $options['auto_primary'] ) ) {
			$classes .= ' elb-ticket-link--auto-primary';
		}

		// "Primary" is the bare `elb-ticket-btn` look (no modifier class).
		$preset = sanitize_key( (string) $options['preset'] );
		if ( 'button' === $options['format'] && in_array( $preset, ELB_Ticket_Button_Styles::BUTTON_PRESETS, true ) && 'primary' !== $preset ) {
			$classes .= ' elb-ticket-btn--' . $preset;
		}
		if ( 'button' === $options['format'] && ! empty( $options['user_border'] ) ) {
			$classes .= ' elb-ticket-btn--user-border';
		}

		return '<a class="' . esc_attr( $classes ) . '" href="' . esc_url( $options['href'] ) . '"' . $target
			. ( $options['style'] ? ' style="' . esc_attr( $options['style'] ) . '"' : '' ) . '>'
			. $options['inner'] . '</a>';
	}
}
