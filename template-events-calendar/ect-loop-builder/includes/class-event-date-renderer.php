<?php
/**
 * Event Date block render (presets markup comes from ELB_Date_Presets).
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Event_Date_Renderer extends ELB_Field_Block_Renderer { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	const PREFIX = 'elb-dt-';

	/** Date preset keys (DATE_PRESETS in the editor). */
	const PRESETS = [ 'none', 'classic', 'minimal' ];

	/**
	 * Scoped hover and responsive CSS for one design class.
	 *
	 * @param string              $resp_class Design class.
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param string              $preset_key Sanitized preset key.
	 */
	private static function date_css( string $resp_class, array $attributes, string $preset_key ): string {
		$css  = ELB_Hover_Css::field_hover_css( $resp_class, $attributes );
		$css .= ELB_Spacing_Styles::field_scoped_css( $attributes, $resp_class );
		if ( 'none' === $preset_key ) {
			$css .= ELB_Hover_Css::hover_border_css( $resp_class, $attributes );
		}
		return $css;
	}

	/**
	 * Assemble inline styles for the date block.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param string              $preset_key Sanitized preset key.
	 * @return string[] Inline style declarations.
	 */
	private static function build_date_decls( array $attributes, string $preset_key ): array {
		$styles = array_merge(
			ELB_Field_Styles::field_decls( $attributes ),
			ELB_Layout_Decls::text_align_layout_decls( $attributes, false )
		);
		if ( 'none' !== $preset_key ) {
			$styles = ELB_Css_Utils::without_props(
				$styles,
				[ 'background-color:', 'background:', 'color:', 'padding:', 'padding-', 'border-radius:', 'border-style:', 'border-width:', 'border-color:' ]
			);
		}

		return array_filter( $styles );
	}

	/**
	 * Build wrapper CSS class names for the date block.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param string              $preset     Preset slug.
	 * @param string              $resp_class Responsive class token.
	 * @return string CSS class string.
	 */
	private static function build_date_classes( array $attributes, string $preset, string $resp_class ): string {
		$classes = self::wrapper_classes( 'elb-event-date elb-date-preset elb-date-preset--' . sanitize_html_class( $preset ), $attributes, $resp_class, true );
		$va      = sanitize_key( $attributes['verticalAlign'] ?? 'top' );
		if ( in_array( $va, [ 'top', 'center', 'bottom' ], true ) ) {
			$classes .= ' elb-date-valign-' . $va;
		}
		return $classes;
	}

	/**
	 * Wrapper classes for the text render paths: base, design class, typography classes, optional text-align class.
	 *
	 * @param string              $base       Base class.
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param string              $resp_class Design class.
	 * @param bool                $with_align Add elb-text-align-*.
	 */
	private static function wrapper_classes( string $base, array $attributes, string $resp_class, bool $with_align ): string {
		return implode( ' ', array_filter( [
			$base,
			$resp_class,
			ELB_Typography::field_classnames( $attributes ),
			$with_align ? self::align_class( $attributes ) : '',
		] ) );
	}

	/**
	 * Render date block from a custom source field.
	 */
	private static function render_custom_source_date( int $post_id, string $source, array $attributes, string $resp_class, array $styles ): string {
		$value         = ELB_Event_Formatter::get_event_field_value( $post_id, $source );
		$wrapper_attrs = get_block_wrapper_attributes( [
			'class' => self::wrapper_classes( 'elb-event-date-text', $attributes, $resp_class, true ),
			'style' => implode( ';', $styles ),
		] );
		return '<div ' . $wrapper_attrs . '>' . self::icon_outside_content( '<span>' . esc_html( $value ) . '</span>', $attributes ) . '</div>';
	}

	/**
	 * Render relative human date string (e.g. "2 days ago").
	 */
	private static function render_human_date( int $post_id, array $attributes, string $resp_class, array $styles ): string {
		[ $ts ] = ELB_Event_Repository::get_event_timestamps( $post_id );
		if ( ! $ts ) {
			return '';
		}
		$now           = time();
		$wrapper_attrs = get_block_wrapper_attributes( [
			'class' => self::wrapper_classes( 'elb-date-human', $attributes, $resp_class, false ),
			'style' => implode( ';', $styles ),
		] );
		$diff  = human_time_diff( $ts, $now );
		if ( $ts < $now ) {
			/* translators: %s: Human-readable time difference (e.g. 2 hours). */
			$label = sprintf( __( '%s ago', 'template-events-calendar' ), $diff );
		} else {
			/* translators: %s: Human-readable time difference (e.g. 2 hours). */
			$label = sprintf( __( '%s from now', 'template-events-calendar' ), $diff );
		}
		return '<div ' . $wrapper_attrs . '>' . self::icon_outside_content( '<span class="elb-date-human__label">' . esc_html( $label ) . '</span>', $attributes ) . '</div>';
	}

	/**
	 * Render the elb/event-date block.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param int                 $post_id    Event post ID.
	 * @return string HTML output.
	 */
	protected static function render_field( array $attributes, int $post_id ): string {

		$format = $attributes['format'];
		$preset = sanitize_key( (string) $attributes['datePreset'] );
		$preset = in_array( $preset, self::PRESETS, true ) ? $preset : 'classic';
		$source = self::source_field( $attributes, 'date' );

		$attr_hash  = self::attr_hash( $attributes );
		$resp_class = self::design(
			$attributes,
			static function ( string $resp_class ) use ( $attributes, $preset ): string {
				return self::date_css( $resp_class, $attributes, $preset );
			},
			$attr_hash
		);
		$styles = self::memo(
			'styles:' . $attr_hash,
			static function () use ( $attributes, $preset ): array {
				return self::build_date_decls( $attributes, $preset );
			}
		);

		if ( 'date' !== $source ) {
			return self::render_custom_source_date( $post_id, $source, $attributes, $resp_class, $styles );
		}

		if ( 'human' === $format ) {
			return self::render_human_date( $post_id, $attributes, $resp_class, $styles );
		}

		return self::render_preset_markup( $post_id, $preset, $attributes, $resp_class, $styles );
	}

	/**
	 * Render standard preset date markup.
	 */
	private static function render_preset_markup(
		int $post_id,
		string $preset,
		array $attributes,
		string $resp_class,
		array $styles
	): string {
		$display = self::preset_display_data( $post_id, $attributes );
		$month   = $display['badge_month'];

		$wrapper_attrs = get_block_wrapper_attributes( [
			'class' => self::build_date_classes( $attributes, $preset, $resp_class ),
			'style' => implode( ';', $styles ),
		] );

		$inner = ELB_Date_Presets::markup(
			$preset,
			[
				'is_badge' => $display['is_badge'],
				'month'    => $month,
				'day'      => $display['badge_day'],
				'text'     => $display['text'],
				'mon_t'    => $month ? ( mb_strtoupper( mb_substr( $month, 0, 1 ) ) . mb_strtolower( mb_substr( $month, 1 ) ) ) : '',
			]
		);
		$inner = self::wrap_mdyt_start( $inner, $attributes, $display['start_parts'] );
		$inner = self::inject_time_clock( $inner );

		$content = self::place_preset_icon( $inner, $preset, $attributes );

		return '<div ' . $wrapper_attrs . '>' . $content . '</div>';
	}

	/**
	 * Resolve the date/time display values ELB_Date_Presets::markup() needs for one preset render.
	 *
	 * @return array{start_parts:?array,is_badge:bool,badge_month:string,badge_day:string,text:string}
	 */
	private static function preset_display_data( int $post_id, array $attributes ): array {
		[ $start_ts, $end_ts, $tz ] = ELB_Event_Repository::get_event_timestamps( $post_id );
		$start_parts = ELB_Event_Date_Value::parts( $start_ts, $tz );
		$end_parts   = ELB_Event_Date_Value::parts( $end_ts, $tz );

		$result   = ELB_Event_Date_Value::resolve( $attributes, $start_ts, $end_ts, $post_id, $start_parts, $end_parts, $tz );
		$is_badge = ( 'badge' === ( $result['mode'] ?? '' ) );

		return [
			'start_parts' => $start_parts,
			'is_badge'    => $is_badge,
			'badge_month' => $is_badge ? ( $result['month'] ?? '' ) : '',
			'badge_day'   => $is_badge ? ( $result['day'] ?? '' ) : '',
			'text'        => ! $is_badge ? ( $result['value'] ?? '' ) : '',
		];
	}

	/**
	 * Wrap the mdyt start date so migrated ECT blocks can zero-pad single-digit days.
	 *
	 * @param string                   $html       Preset inner HTML.
	 * @param array<string,mixed>      $attributes Block attributes.
	 * @param array<string,string>|null $start     Start date parts.
	 */
	private static function wrap_mdyt_start( string $html, array $attributes, ?array $start ): string {
		if ( 'mdyt' !== ( $attributes['format'] ?? '' ) || ! $start ) {
			return $html;
		}
		$escaped = esc_html( $start['mon'] . ' ' . $start['day_n'] . ', ' . $start['year'] );
		$pos     = strpos( $html, $escaped );
		if ( false === $pos ) {
			return $html;
		}
		return substr_replace( $html, '<span class="elb-date-mdyt__start">' . $escaped . '</span>', $pos, strlen( $escaped ) );
	}

	/** Swap date/time markers for two-line markup: date + clock, then time / All day. */
	private static function inject_time_clock( string $html ): string {
		if ( false === strpos( $html, ELB_Event_Date_Value::MDYT_OPEN ) ) {
			return $html;
		}
		$svg  = ELB_Icons::svg( 'clock' );
		$icon = '' === $svg ? '' : '<span class="elb-date-time-icon" aria-hidden="true">' . $svg . '</span>';
		return strtr(
			$html,
			[
				ELB_Event_Date_Value::MDYT_OPEN       => '<span class="elb-date-mdyt"><span class="elb-date-mdyt__date">',
				ELB_Event_Date_Value::TIME_CLOCK_MARK => $icon,
				ELB_Event_Date_Value::MDYT_SPLIT      => '</span><span class="elb-date-mdyt__time">',
				ELB_Event_Date_Value::MDYT_CLOSE      => '</span></span>',
			]
		);
	}

	/** Place the preset's icon inside the preset, or outside it, per blockIconPlacement. */
	private static function place_preset_icon( string $inner, string $preset, array $attributes ): string {
		$inside = 'none' !== $preset && 'inside' === ( $attributes['blockIconPlacement'] ?? 'inside' );
		if ( ! $inside ) {
			return self::icon_outside_content( $inner, $attributes );
		}
		return self::icon_inside_preset( $inner, $attributes );
	}

	/** Place the selected icon inside the preset's own visual container. */
	private static function icon_inside_preset( string $markup, array $attributes ): string {
		$icon = ELB_Icons::render_block_icon( $attributes );
		if ( '' === $icon ) {
			return $markup;
		}

		if ( ! preg_match( '/^(\s*<([a-z][a-z0-9]*)\b[^>]*>)(.*)(<\/\2>\s*)$/is', $markup, $parts ) ) {
			return self::icon_outside_content( $markup, $attributes );
		}

		$position = ELB_Icons::normalize_position( $attributes );
		$opening  = preg_replace_callback(
			'/\bclass=(["\'])/i',
			static fn( array $match ): string => 'class=' . $match[1] . 'elb-date-preset__has-icon elb-date-icon--' . $position . ' ',
			$parts[1],
			1
		);
		$content = '<span class="elb-date-preset__icon-content">' . $parts[3] . '</span>';
		$inside  = ELB_Icons::place( $icon, $content, ELB_Icons::place_side( $position ) );

		return $opening . $inside . $parts[4];
	}

	/** Place an icon around date content while honoring all four directions. */
	private static function icon_outside_content( string $content, array $attributes ): string {
		$position = ELB_Icons::normalize_position( $attributes );
		return ELB_Icons::with_block_icon( $content, $attributes, [
			'wrapper_class' => 'elb-date-icon-wrap elb-date-icon--' . $position,
			'style'         => '',
			'four_way'      => true,
		] );
	}

}
