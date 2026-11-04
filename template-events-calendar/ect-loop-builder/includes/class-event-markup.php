<?php
/**
 * Event HTML helpers: optional link wrapper and the event image tag.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Event_Markup { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/**
	 * Optionally wrap inner HTML in an event permalink <a>.
	 *
	 * @param string $inner   Already-escaped inner markup.
	 * @param string $url     Link URL.
	 * @param bool   $enabled Whether linking is on.
	 * @param bool   $new_tab Open in a new tab.
	 * @param string $style   Inline style attribute value.
	 * @return string
	 */
	public static function wrap_optional_link( $inner, $url, $enabled, $new_tab, $style = 'color:inherit;text-decoration:none;' ) {
		if ( ! $enabled || ! $url ) {
			return $inner;
		}
		$target = $new_tab ? ' target="_blank" rel="noopener noreferrer"' : '';
		return '<a href="' . esc_url( $url ) . '"' . $target . ' style="' . esc_attr( $style ) . '">' . $inner . '</a>';
	}

	/**
	 * Render default placeholder image tag.
	 *
	 * @param int   $id   Post ID.
	 * @param array $attr Image attributes.
	 * @return string HTML img tag.
	 */
	public static function placeholder_image( int $id, array $attr = [] ): string {
		if ( $id <= 0 ) {
			return '';
		}

		$attr = wp_parse_args(
			$attr,
			[
				'class'   => 'elb-event-img elb-event-img--default',
				'loading' => 'lazy',
				'alt'     => get_the_title( $id ),
			]
		);

		$url = ELB_Event_Repository::get_default_event_image_url();

		if ( false === strpos( (string) $attr['class'], 'elb-event-img--default' ) ) {
			$attr['class'] = trim( (string) $attr['class'] . ' elb-event-img--default' );
		}

		return sprintf(
			'<img src="%1$s" alt="%2$s" class="%3$s" loading="%4$s"%5$s%6$s />',
			esc_url( $url ),
			esc_attr( $attr['alt'] ),
			esc_attr( $attr['class'] ),
			esc_attr( $attr['loading'] ),
			! empty( $attr['style'] ) ? ' style="' . esc_attr( $attr['style'] ) . '"' : '',
			! empty( $attr['sizes'] ) ? ' sizes="' . esc_attr( $attr['sizes'] ) . '"' : ''
		);
	}
}
