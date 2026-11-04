<?php
/**
 * Field block helpers: event post id, wrapper tag lookup and the wrapper filter.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Field_Block { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/**
	 * render_block_elb/event-*: one pass over the field block's wrapper for the
	 * scoped-style class.
	 *
	 * @param string              $content Rendered block HTML.
	 * @param array<string,mixed> $block   Parsed block.
	 * @return string
	 */
	public static function filter( $content, $block ) {
		if ( '' === trim( (string) $content ) ) {
			return $content;
		}
		$tags = self::wrapper_tag( $content );
		if ( ! $tags ) {
			return $content;
		}
		$changed = ELB_Scoped_Styles::scope_wrapper_style( $tags );
		return $changed ? $tags->get_updated_html() : $content;
	}

	/**
	 * Tag processor positioned on the block's outer element, skipping any scoped
	 * <style> or <script> a field block prints right before its wrapper.
	 *
	 * @param string $content Rendered block HTML.
	 * @return WP_HTML_Tag_Processor|null Null when there is no element to work on.
	 */
	public static function wrapper_tag( $content ) {
		$tags = new WP_HTML_Tag_Processor( $content );
		while ( $tags->next_tag() ) {
			if ( ! in_array( $tags->get_tag(), [ 'STYLE', 'SCRIPT' ], true ) ) {
				return $tags;
			}
		}
		return null;
	}
}
