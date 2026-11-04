<?php
/**
 * Event Meta item: venue.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Meta_Item_Venue extends ELB_Meta_Item { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	const ICON = 'mapMarker';

	public static function render( int $post_id, array $item_settings, array $item = [] ): string {
		$format = ELB_Css_Utils::safe_keyword( sanitize_key( $item_settings['show'] ?? 'full' ), [ 'name', 'name_city' ], 'full' );
		$text   = (string) ELB_Venue_Repository::get_venue_display_text( $post_id, $format );
		if ( '' === $text ) {
			return '';
		}
		$url = '';
		if ( ! empty( $item_settings['link'] ) && ELB_Tec_Pro::active() ) {
			// Reuse the venue ID already resolved by get_event_venue_parts() instead of a second,
			// independent tribe_get_venue_id() lookup.
			$url = (string) ELB_Venue_Repository::get_event_venue_parts( $post_id )['permalink'];
		}
		$html      = esc_html( $text );
		$max_lines = (int) ( $item_settings['maxLines'] ?? 0 );
		if ( $max_lines > 0 ) {
			$html = '<span class="elb-meta__clamp" style="--elb-meta-clamp:' . $max_lines . ';">' . $html . '</span>';
		}
		return ELB_Meta_Item::wrap_with_item_style( $html, $item, $url );
	}
}
