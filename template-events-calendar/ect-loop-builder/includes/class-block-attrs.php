<?php
/**
 * Dependency-free readers for block attributes shared by the CSS builders.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Block_Attrs { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/**
	 * A prefixed attribute as a string, e.g. ( 'button', 'fontSize' ) => buttonFontSize.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param string              $prefix     Attribute prefix.
	 * @param string              $name       Base attribute name.
	 */
	public static function prefixed( $attributes, $prefix, $name ): string {
		$key = $prefix . ucfirst( (string) $name );
		return isset( $attributes[ $key ] ) ? (string) $attributes[ $key ] : '';
	}

	/**
	 * Parsed block attributes as an array.
	 *
	 * @param mixed $block Parsed block.
	 * @return array<string,mixed>
	 */
	public static function of( $block ): array {
		return is_array( $block ) && is_array( $block['attrs'] ?? null ) ? $block['attrs'] : [];
	}
}
