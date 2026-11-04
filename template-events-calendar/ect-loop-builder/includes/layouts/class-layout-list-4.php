<?php
/**
 * List Style 4 (Minimal): category-coloured date block and its accent border.
 * Autoloaded (includes/layouts/) only when a list-4 loop or date column is rendered.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Layout_List_4 extends ELB_Layout_Hooks { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/** Stock date-column border colour (accent_color() only applies to the stock card). */
	const STOCK_BORDER_COLOR = '#1e88e5';

	/** Stock date-column background colour (no accent when unchanged). */
	const STOCK_BG_COLOR = '#4fc3f7';

	/**
	 * Card in a category-colour scope when "Use category colour" is on and the event has one.
	 *
	 * @param string $html    Card HTML.
	 * @param int    $post_id Event post ID.
	 */
	public function wrap_card( string $html, int $post_id ): string {
		$event_color = self::uses_category_color( $this->attrs ) ? self::event_category_color( $post_id ) : '';
		return '' !== $event_color
			? '<div class="elb-event-color-scope" style="' . esc_attr( '--elb-event-color:' . $event_color . ';' ) . '">' . $html . '</div>'
			: $html;
	}

	/**
	 * Colour of the event's first category that has one (TEC category colours
	 * via ELB_Category), or '' when none is set.
	 *
	 * @param int $post_id Event post ID.
	 */
	public static function event_category_color( int $post_id ): string {
		foreach ( ELB_Event_Repository::get_event_category_terms( $post_id ) as $term ) {
			if ( ! $term instanceof WP_Term ) {
				continue;
			}
			$colors = ELB_Category::get_category_term_colors( (int) $term->term_id );
			$color  = $colors['primary'];
			if ( $color ) {
				return $color;
			}
		}
		return '';
	}

	/**
	 * Date col: the stock accent border (#1e88e5) follows a user-changed solid
	 * background as a darker shade of it. Stock pair (#4fc3f7 / #1e88e5) and a user-set
	 * border colour are left alone; the category-colour rule (higher specificity) still wins.
	 *
	 * @param array<string,mixed> $attrs Container attributes.
	 */
	public static function accent_color( array $attrs ): string {
		if ( false === strpos( (string) ( $attrs['className'] ?? '' ), 'elb-card__date-col--list-4' )
			|| 'color' !== (string) ( $attrs['backgroundType'] ?? '' )
			|| self::STOCK_BORDER_COLOR !== strtolower( (string) ( $attrs['containerBorderColor'] ?? '' ) ) ) {
			return '';
		}
		$bg = ELB_Css_Utils::sanitize_css_color( (string) ( $attrs['backgroundColor'] ?? '' ) );
		if ( '' === $bg || self::STOCK_BG_COLOR === strtolower( $bg ) ) {
			return '';
		}
		return 'color-mix(in srgb, ' . $bg . ' 75%, #000)';
	}

	/**
	 * @param array<string,mixed> $attrs Container attributes.
	 * @param string              $id    Sanitized block id.
	 */
	public static function accent_css( array $attrs, string $id ): string {
		$mix = '' !== $id ? self::accent_color( $attrs ) : '';
		return '' === $mix ? '' : '.elb-loop-output .elb-block-' . $id . '{border-left-color:' . $mix . ' !important;}';
	}

	/**
	 * Tint the date block with the event's category colour: List Style 4 only; "Use category colour" (useCategoryColor) defaults on.
	 *
	 * @param array<string,mixed> $attrs Loop block attributes.
	 */
	private static function uses_category_color( array $attrs ): bool {
		return self::attr_flag( $attrs, 'useCategoryColor' );
	}
}
