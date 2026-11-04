<?php
/**
 * Event Meta item: price.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Meta_Item_Price extends ELB_Meta_Item { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	const ICON = 'ticket';

	public static function render( int $post_id, array $item_settings, array $item = [] ): string {
		$raw  = (string) ELB_Event_Formatter::get_event_field_value( $post_id, 'cost' );
		$free = trim( (string) ( $item_settings['freeLabel'] ?? '' ) );
		$text = ELB_Event_Cost::cost_label( $raw, $free );
		return ELB_Meta_Item::wrap_with_item_style( esc_html( $text ), $item );
	}
}
