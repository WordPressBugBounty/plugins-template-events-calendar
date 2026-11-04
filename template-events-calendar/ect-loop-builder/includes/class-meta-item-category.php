<?php
/**
 * Event Meta item: event categories.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Meta_Item_Category extends ELB_Meta_Item { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/**
	 * Event categories: "Show one" keeps a single category unless a limit is set;
	 * Plain renders one <span> per name, Solid renders badges (linked when "Link" is on).
	 *
	 * @param int                 $post_id       Event ID.
	 * @param array<string,mixed> $item_settings Item settings.
	 * @param array<string,mixed> $item          Item (its own badge Style / colours).
	 * @return string Category markup (may be empty).
	 */
	public static function render( int $post_id, array $item_settings, array $item = [] ): string {
		$terms = ELB_Event_Repository::get_event_category_terms( $post_id );
		if ( ! $terms ) {
			return '';
		}
		$limit = self::term_limit( $item_settings );
		if ( $limit > 0 ) {
			$terms = array_slice( $terms, 0, $limit );
		}
		$attrs = ELB_Meta_Item::badge_attrs_html( $item ); // '' when Plain — every term then renders bare.
		$link  = ! empty( $item_settings['link'] );
		$html  = '';
		foreach ( $terms as $term ) {
			if ( ! $term instanceof WP_Term ) {
				continue;
			}
			$url  = $link ? get_term_link( $term ) : '';
			$name = esc_html( $term->name );
			if ( $url && ! is_wp_error( $url ) ) {
				$html .= '<a' . $attrs . ' href="' . esc_url( $url ) . '">' . self::badge_icon( $item, $attrs ) . $name . '</a>';
			} elseif ( '' === $attrs ) {
				// One element per name, as in the editor preview, so the item's flex gap separates them.
				$html .= '<span>' . $name . '</span>';
			} else {
				$html .= '<span' . $attrs . '>' . self::badge_icon( $item, $attrs ) . $name . '</span>';
			}
		}
		return $html;
	}

	/**
	 * How many categories to show (0 = all): the item's limit, else 1 when "Show one" is on.
	 *
	 * @param array<string,mixed> $item_settings Item settings.
	 */
	private static function term_limit( array $item_settings ): int {
		if ( isset( $item_settings['limit'] ) ) {
			return (int) $item_settings['limit'];
		}
		return ! empty( $item_settings['showOne'] ) ? 1 : 0;
	}
}
