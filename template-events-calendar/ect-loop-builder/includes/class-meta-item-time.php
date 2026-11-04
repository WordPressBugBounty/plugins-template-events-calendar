<?php
/**
 * Event Meta item: event time.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Meta_Item_Time extends ELB_Meta_Item { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	const ICON = 'clock';

	public static function render( int $post_id, array $item_settings, array $item = [] ): string {
		$fmt      = ELB_Date_Format_Resolver::resolve_time_format( (string) ( $item_settings['format'] ?? 'site' ), (string) ( $item_settings['customFormat'] ?? '' ) );
		$show_end = ! isset( $item_settings['showEnd'] ) || ! empty( $item_settings['showEnd'] );
		$text     = ELB_Event_Time::text(
			$post_id,
			$fmt,
			$show_end,
			(string) ( $item_settings['allDayLabel'] ?? '' ),
			! empty( $item_settings['showTimezone'] )
		);
		if ( '' === $text ) {
			return '';
		}
		return ELB_Meta_Item::time_tag( $text, ELB_Event_Time::iso_start( $post_id ), $item );
	}
}
