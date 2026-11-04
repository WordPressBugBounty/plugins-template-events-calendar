<?php
/**
 * Shared CSS selector helpers for flex containers and cards.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Container_Selectors { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/**
	 * Selector for a flex container inside a loop output.
	 *
	 * @param string $id Block ID.
	 * @return string
	 */
	public static function block_selector( string $id ): string {
		return '.elb-loop-output .wp-block-elb-flex-container.elb-block-' . $id;
	}

	/**
	 * Container selector boosted by a never-matching id arm, so it outranks class-based layout resets.
	 *
	 * @param string $id  Block id.
	 * @param string $tag Id-arm tag (margin, dir, wrap…).
	 * @return string
	 */
	public static function boosted_selector( string $id, string $tag ): string {
		return '.elb-loop-output .wp-block-elb-flex-container:is(.elb-block-' . $id . ',#elb-' . $tag . '-' . $id . ')';
	}

	/**
	 * Direct-child selector of a container, with or without the inner-blocks wrap.
	 *
	 * @param string $parent Parent selector.
	 * @param string $child  Child selector.
	 * @return string
	 */
	public static function child_selector( string $parent, string $child ): string {
		return $parent . '>' . $child . ',' . $parent . '>.elb-flex-container-inner-blocks-wrap>' . $child;
	}

	/**
	 * Sanitized block id from the saved attributes ('' when missing).
	 *
	 * @param array<string,mixed> $attrs Block attributes.
	 * @return string
	 */
	public static function block_id( array $attrs ): string {
		return sanitize_html_class( (string) ( $attrs['block_id'] ?? '' ) );
	}
}
