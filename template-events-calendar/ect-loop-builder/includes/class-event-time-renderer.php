<?php
/**
 * Event Time block renderer (time only, no category).
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

class ELB_Event_Time_Renderer extends ELB_Field_Block_Renderer { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	const PREFIX = 'elb-tm-';

	/**
	 * Time text for display, using the block's Time format / Show / All-day label settings.
	 *
	 * @param int                 $post_id    Post ID.
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param string              $time_style Sanitized timeStyle.
	 */
	private static function resolve_time_text( int $post_id, array $attributes, string $time_style ): string {
		$time_format_key = sanitize_key( $attributes['timeFormat'] ?? '' );
		$time_show_key   = sanitize_key( $attributes['timeShow'] ?? '' );
		$all_day_label   = trim( (string) ( $attributes['allDayLabel'] ?? '' ) );

		$has_custom_time_settings = '' !== $time_style || '' !== $time_format_key || '' !== $time_show_key || '' !== $all_day_label;
		$fmt      = $has_custom_time_settings
			? ELB_Date_Format_Resolver::resolve_time_format( $time_format_key, (string) ( $attributes['timeFormatCustom'] ?? '' ) )
			: null;
		$show_end = 'start' !== $time_show_key;

		return ELB_Event_Time::text( $post_id, $fmt, $show_end, $all_day_label );
	}

	/**
	 * Build the icon SVG string for the time block.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @return string SVG HTML.
	 */
	private static function build_icon_svg( array $attributes ): string {
		// The time block shows the clock icon by default.
		return ELB_Icons::render_block_icon( $attributes, 'clock', true );
	}

	/**
	 * Render time badge or plain span markup.
	 */
	private static function render_time_badge( string $time_style, string $time_text, string $style_attr, callable $with_icon ): string {
		if ( '' === $time_style || 'plain' === $time_style ) {
			return '<span class="elb-event-meta__time"' . $style_attr . '>' . $with_icon( esc_html( $time_text ) ) . '</span>';
		}

		$value_html = '<span class="elb-time-style__value">' . esc_html( $time_text ) . '</span>';
		$classes    = 'elb-event-meta__time elb-time-style elb-time-style--' . sanitize_html_class( $time_style );
		return '<span class="' . esc_attr( $classes ) . '"' . $style_attr . '>' . $with_icon( $value_html ) . '</span>';
	}

	/**
	 * Whether the elb/event-time block shows anything.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @return bool True when the time field should render.
	 */
	protected static function should_render( array $attributes ): bool {
		return self::flag( $attributes, 'showTime' );
	}

	/**
	 * Event time badge.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param int                 $post_id    Event post ID.
	 * @return string Time HTML (may be empty).
	 */
	protected static function render_field( array $attributes, int $post_id ): string {

		$time_style = sanitize_key( $attributes['timeStyle'] ?? '' );
		$time_style = in_array( $time_style, [ '', 'plain', 'pill' ], true ) ? $time_style : 'plain';
		$time_text  = self::resolve_time_text( $post_id, $attributes, $time_style );
		if ( '' === $time_text ) {
			return '';
		}

		$attr_hash  = self::attr_hash( $attributes );
		$resp_class = self::design(
			$attributes,
			static function ( string $resp_class ) use ( $attributes ): string {
				return ELB_Hover_Css::field_hover_css( $resp_class, $attributes, [ 'text_targets' => [ [ 'selector' => '.elb-event-meta__time', 'wrapper' => true ] ] ] )
					. ELB_Hover_Css::hover_border_css( $resp_class, $attributes, '.elb-event-meta__time' )
					. ELB_Spacing_Styles::field_scoped_css( $attributes, $resp_class, '.elb-event-meta__time' );
			},
			$attr_hash
		);

		[ $style_attr, $icon_svg, $layout_style ] = self::memo(
			'styles:' . $attr_hash,
			static function () use ( $attributes ): array {
				$style_str = ELB_Field_Styles::field_style( $attributes );
				$icon_svg  = self::build_icon_svg( $attributes );
				if ( $icon_svg ) {
					$icon_gap = ELB_Layout_Decls::flex_gap( $attributes );
					if ( '' !== $icon_gap ) {
						$style_str = trim( $style_str . ';gap:' . $icon_gap, ';' );
					}
				}
				return [
					$style_str ? ' style="' . esc_attr( $style_str ) . '"' : '',
					$icon_svg,
					ELB_Layout_Decls::row_layout_style( $attributes ),
				];
			}
		);

		$icon_pos  = (string) ( $attributes['blockIconPos'] ?? 'left' );
		$time_html = self::render_time_badge(
			$time_style,
			$time_text,
			$style_attr,
			static function ( $text_html ) use ( $icon_svg, $icon_pos ) {
				return ELB_Icons::place( $icon_svg, $text_html, $icon_pos );
			}
		);

		$wrapper_attrs = get_block_wrapper_attributes(
			[
				'class' => 'elb-event-meta elb-event-time ' . $resp_class,
				'style' => $layout_style,
			]
		);

		return '<div ' . $wrapper_attrs . '>' . $time_html . '</div>';
	}
}
