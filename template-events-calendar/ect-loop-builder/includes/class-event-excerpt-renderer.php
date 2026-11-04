<?php
/**
 * Event Excerpt block renderer (post excerpt with word trimming, links, icon and style support).
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

class ELB_Event_Excerpt_Renderer extends ELB_Text_Block_Renderer { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	const PREFIX       = 'elb-exc-';
	const HOVER_PREFIX = 'exc';
	const BASE_CLASS   = 'elb-event-excerpt';

	/**
	 * Retrieve and sanitize the excerpt content for the given post.
	 *
	 * @param int                 $post_id    Post ID.
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param int                 $words      Word count limit.
	 * @return string
	 */
	private static function resolve_excerpt( int $post_id, array $attributes, int $words ): string {
		$source = self::source_field( $attributes, 'excerpt' );
		$mode   = $attributes['lengthMode'] ?? 'words';
		if ( 'excerpt' !== $source ) {
			$value = (string) ELB_Event_Formatter::get_event_field_value( $post_id, $source );
			return 'words' === $mode ? ELB_Event_Formatter::trim_words_clean( $value, $words ) : $value;
		}
		return ELB_Event_Formatter::excerpt( $post_id, $words, true, (string) $mode );
	}

	/**
	 * Excerpt text trimmed to Word count.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param int                 $post_id    Event post ID.
	 * @return string|null Excerpt text, or null when empty/hidden.
	 */
	protected static function text( array $attributes, int $post_id ): ?string {
		return self::resolve_excerpt( $post_id, $attributes, max( 5, (int) ( $attributes['wordCount'] ?? 20 ) ) );
	}

	/**
	 * Excerpts render as a paragraph.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @return string HTML tag name (`p`).
	 */
	protected static function tag( array $attributes ): string {
		return 'p';
	}

}
