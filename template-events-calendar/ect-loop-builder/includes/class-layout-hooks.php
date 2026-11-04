<?php
/**
 * Per-layout hooks for the loop: the loop resolves one instance per render and calls
 * before_card() / wrap_card() / wrapper_attr() instead of comparing layoutStyle strings.
 * This base class is the no-op used by layouts without hooks.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

class ELB_Layout_Hooks { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/** @var array<string,mixed> Loop block attributes. */
	protected array $attrs;

	/** @var array<string,mixed>|null Section block. */
	protected ?array $section;

	/**
	 * @param array<string,mixed>      $attrs   Loop block attributes.
	 * @param array<string,mixed>|null $section Section block.
	 */
	public function __construct( array $attrs, ?array $section = null ) {
		$this->attrs   = $attrs;
		$this->section = $section;
	}

	/** Layouts with their own hooks class. */
	const HOOKS = [
		'list-3' => ELB_Layout_List_3::class,
		'list-4' => ELB_Layout_List_4::class,
	];

	/**
	 * Hooks for the loop's layout.
	 *
	 * @param array<string,mixed>      $attrs   Loop block attributes.
	 * @param array<string,mixed>|null $section Section block.
	 */
	public static function resolve( array $attrs, ?array $section = null ): self {
		$class = self::HOOKS[ sanitize_key( $attrs['layoutStyle'] ?? '' ) ] ?? self::class;
		return new $class( $attrs, $section );
	}

	/**
	 * On/off loop setting that defaults on when unset.
	 *
	 * @param array<string,mixed> $attrs Loop block attributes.
	 * @param string              $key   Attribute name.
	 */
	protected static function attr_flag( array $attrs, string $key ): bool {
		return ! isset( $attrs[ $key ] ) || rest_sanitize_boolean( $attrs[ $key ] );
	}

	/**
	 * Markup printed before the event's card (e.g. a month heading).
	 *
	 * @param int $post_id Event post ID.
	 */
	public function before_card( int $post_id ): string {
		return '';
	}

	/**
	 * The event's card markup, optionally wrapped.
	 *
	 * @param string $html    Card HTML.
	 * @param int    $post_id Event post ID.
	 */
	public function wrap_card( string $html, int $post_id ): string {
		return $html;
	}

	/** Extra attribute markup for the loop wrapper (leading space), or ''. */
	public function wrapper_attr(): string {
		return '';
	}
}
