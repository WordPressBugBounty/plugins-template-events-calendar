<?php
/**
 * Event Date block: date parts, format resolution and the preset markup.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

class ELB_Date_Presets { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/**
	 * Inner markup of the block for a preset.
	 *
	 * @param string              $preset Preset key.
	 * @param array<string,mixed> $date_view Values: is_badge, month, day, text, mon_t.
	 */
	public static function markup( $preset, array $date_view ): string {
		if ( 'none' === $preset ) {
			return '<span class="elb-date-preset__text">' . esc_html( $date_view['is_badge'] ? ( $date_view['mon_t'] . ' ' . $date_view['day'] ) : $date_view['text'] ) . '</span>';
		}
		// The caller already limits $preset to ELB_Event_Date_Renderer::PRESETS; anything else draws Classic.
		return self::badge( in_array( $preset, ELB_Event_Date_Renderer::PRESETS, true ) ? $preset : 'classic', $date_view );
	}

	/**
	 * Badge presets: classic / minimal.
	 *
	 * Do not add --split for overlay badges: editor classic is a solid
	 * blue pill (month+day on one background). --split paints a navy
	 * month bar and mismatches Clean Image Card / Horizontal Magazine.
	 *
	 * @param string              $preset Preset key.
	 * @param array<string,mixed> $date_view      Values from markup().
	 */
	private static function badge( $preset, array $date_view ): string {
		$badge_class = 'elb-date-badge elb-date-badge--' . sanitize_html_class( $preset );
		if ( $date_view['is_badge'] ) {
			$html  = '<div class="' . esc_attr( $badge_class ) . '">';
			$html .= '<span class="elb-date-badge__month">' . esc_html( $date_view['month'] ) . '</span>';
			$html .= '<span class="elb-date-badge__day">' . esc_html( $date_view['day'] ) . '</span>';
			return $html . '</div>';
		}
		$html  = '<div class="' . esc_attr( $badge_class ) . ' elb-date-badge--text">';
		$html .= '<span class="elb-date-badge__text">' . esc_html( $date_view['text'] ) . '</span>';
		return $html . '</div>';
	}
}
