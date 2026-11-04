<?php
/**
 * Event link helpers for field blocks: link settings, link URL and button label.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Event_Link { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/**
	 * Link settings of a field block: [ is_link, new_tab, url ]. The URL is always the event
	 * permalink (the Ticket URL link source is Pro-only).
	 *
	 * @param array<string,mixed> $attributes Block attributes (enableLink, linkNewTab).
	 * @param int                 $post_id    Event post ID.
	 * @return array{0:bool,1:bool,2:string}
	 */
	public static function link_settings( array $attributes, int $post_id ): array {
		// No link: callers only read the URL when the first flag is true, so skip the URL lookup.
		// new_tab is still reported because the ticket button's '#' anchor honours it.
		if ( empty( $attributes['enableLink'] ) ) {
			return [ false, ! empty( $attributes['linkNewTab'] ), '' ];
		}
		return [
			true,
			! empty( $attributes['linkNewTab'] ),
			(string) get_permalink( $post_id ),
		];
	}

	/**
	 * Button label: the saved text, or "Read more".
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 */
	public static function button_label( array $attributes ): string {
		$label = trim( sanitize_text_field( (string) ( $attributes['buttonLabel'] ?? '' ) ) );
		if ( '' !== $label ) {
			return $label;
		}
		return __( 'Read more', 'template-events-calendar' );
	}
}
