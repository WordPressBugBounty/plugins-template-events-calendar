<?php
/**
 * Event Title block renderer (post title with full style, link, typography, and icon support).
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

class ELB_Event_Title_Renderer extends ELB_Text_Block_Renderer { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	const PREFIX       = 'elb-ti-';
	const HOVER_PREFIX = 'title';

	const HOVER_IMPORTANT = true;

	const HOVER_LINK = true;
	const BASE_CLASS   = 'elb-event-title';

	/** Default font size per heading level (0 = p) when the block sets none. */
	const LEVEL_PX = [ 0 => 16, 1 => 32, 2 => 24, 3 => 20, 4 => 18, 5 => 16, 6 => 14 ];

	/**
	 * Heading level (0 for p, 1-6).
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 */
	private static function level( array $attributes ): int {
		return (int) ( $attributes['level'] ?? 3 );
	}

	/**
	 * Title text. Word limit and Hide if empty are Pro-only and are ignored here.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param int                 $post_id    Event post ID.
	 * @return string|null Title text, or null when empty.
	 */
	protected static function text( array $attributes, int $post_id ): ?string {
		return (string) ELB_Event_Formatter::get_event_field_value( $post_id, self::source_field( $attributes, 'title' ) );
	}

	/**
	 * Heading tag for the level (p for 0).
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @return string HTML tag name (`h1`–`h6` or `p`).
	 */
	protected static function tag( array $attributes ): string {
		$level = self::level( $attributes );
		return 0 === $level ? 'p' : 'h' . max( 1, min( 6, $level ) );
	}

	/**
	 * Link settings from Enable link / Link source.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param int                 $post_id    Event post ID.
	 * @return array{0:bool,1:bool,2:string} [ is_link, new_tab, url ].
	 */
	protected static function link( array $attributes, int $post_id ): array {
		return ELB_Event_Link::link_settings( $attributes, $post_id );
	}

	/**
	 * Default font size for the heading level when none is set.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @return string[] CSS declarations.
	 */
	protected static function size_decls( array $attributes ): array {
		if ( '' !== (string) ( $attributes['fontSize'] ?? '' ) ) {
			return [];
		}
		$px = self::LEVEL_PX[ self::level( $attributes ) ] ?? 20;
		return [ 'font-size:' . $px . 'px', '--elb-field-font-size:' . $px . 'px' ];
	}

}
