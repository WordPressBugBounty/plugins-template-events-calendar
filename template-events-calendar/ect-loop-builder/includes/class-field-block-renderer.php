<?php
/**
 * Base for the loop field block renderers: post id guard, design class with lazily
 * queued CSS, wrapper helpers. Subclasses implement render_field().
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

abstract class ELB_Field_Block_Renderer { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/** Design-class prefix (e.g. 'elb-ti-'). */
	const PREFIX = '';

	/**
	 * Render the block for the card's event; '' outside a loop card or when hidden.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param mixed               $block      Block instance or context.
	 * @return string HTML output.
	 */
	final public static function render( array $attributes, $block ): string {
		$post_id = static::post_id( $block );
		if ( ! $post_id || ! static::should_render( $attributes ) ) {
			return '';
		}
		return static::render_field( $attributes, $post_id );
	}

	/**
	 * Block HTML for one event.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param int                 $post_id    Event post ID.
	 * @return string HTML markup (may be empty).
	 */
	abstract protected static function render_field( array $attributes, int $post_id ): string;

	/**
	 * Whether the block's own show toggle allows output.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @return bool True when the field should render.
	 */
	protected static function should_render( array $attributes ): bool {
		return true;
	}

	/**
	 * Per-request memoization cache for computed design classes and styles.
	 *
	 * @var array<string,mixed>
	 */
	private static $memo_cache = [];

	/**
	 * Compute or retrieve a memoized value for a key within the calling class.
	 *
	 * @param string   $key Unique key for the computation.
	 * @param callable $fn  Factory function returning the value.
	 * @return mixed
	 */
	protected static function memo( string $key, callable $fn ) {
		$scoped_key = static::class . ':' . $key;
		if ( ! array_key_exists( $scoped_key, self::$memo_cache ) ) {
			self::$memo_cache[ $scoped_key ] = $fn();
		}
		return self::$memo_cache[ $scoped_key ];
	}

	/**
	 * Hash of a block's attributes, used for the design class and the per-settings memo keys.
	 * Compute it once per field and pass it on rather than re-encoding the same array.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @return string MD5 hash of the encoded attributes.
	 */
	protected static function attr_hash( array $attributes ): string {
		return md5( (string) wp_json_encode( $attributes ) );
	}

	/**
	 * Design class for these settings; $css( $class ) builds its CSS once, only when printed.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param callable            $css        Receives the design class, returns CSS.
	 * @param string|null         $attr_hash  Precomputed attr_hash( $attributes ), when the caller has it.
	 * @return string Sanitized design class name (e.g. elb-ti-a1b2c3d4).
	 */
	protected static function design( array $attributes, callable $css, ?string $attr_hash = null ): string {
		$attr_hash = $attr_hash ?? static::attr_hash( $attributes );
		return static::memo(
			'design:' . $attr_hash,
			static function () use ( $attributes, $css, $attr_hash ): string {
				$class = static::PREFIX . substr( $attr_hash, 0, 8 );
				ELB_Inline_Css::add_field_css(
					$class,
					static function () use ( $css, $class ): string {
						return (string) $css( $class );
					}
				);
				return $class;
			}
		);
	}

	/**
	 * On/off attribute with a default for when it is missing.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param string              $key        Attribute name.
	 * @param bool                $default    Value when unset.
	 * @return bool Resolved flag value.
	 */
	protected static function flag( array $attributes, string $key, bool $default = true ): bool {
		return array_key_exists( $key, $attributes ) ? ! empty( $attributes[ $key ] ) : $default;
	}

	/**
	 * 'elb-text-align-{x}' for the textAlign attribute, or ''.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param string              $default    Alignment when unset.
	 * @return string CSS class or empty string.
	 */
	protected static function align_class( array $attributes, string $default = '' ): string {
		$ta = ELB_Css_Utils::sanitize_text_align( $attributes['textAlign'] ?? $default );
		return $ta ? 'elb-text-align-' . $ta : '';
	}

	/**
	 * Event post id from the block context (0 when the block is rendered outside a loop card).
	 *
	 * @param WP_Block|object|null $block Block being rendered.
	 * @return int Event post ID, or 0 when unavailable.
	 */
	protected static function post_id( $block ): int {
		return (int) ( $block->context['elb/postId'] ?? 0 );
	}


	/**
	 * Sanitized sourceField attribute, or the block's own field.
	 * Pro-only sources (organizer, tags, tickets) fall back to $default.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param string              $default    Field the block shows by default.
	 * @return string Resolved source field key.
	 */
	protected static function source_field( array $attributes, string $default ): string {
		return ELB_Event_Formatter::resolve_source_field( (string) ( $attributes['sourceField'] ?? '' ), $default );
	}
}
