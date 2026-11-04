<?php
/**
 * Block registration: block types, names and the editor category.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

class ELB_Blocks { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/** Field, card and layout (flex-container, wrapper) blocks registered from build/blocks (loop-builder is registered separately). */
	const BLOCKS = [
		'card-template',
		'event-image',
		'event-date',
		'event-meta',
		'event-time',
		'event-title',
		'event-excerpt',
		'event-read-more',
		'event-info-row',
		'event-share',
		'flex-container',
		'wrapper',
	];

	/**
	 * Every ELB block name. Render filters attach to these only.
	 *
	 * @return string[]
	 */
	public static function block_names(): array {
		return array_merge(
			[ 'elb/loop-builder' ],
			array_map( static fn( $slug ) => 'elb/' . $slug, self::BLOCKS )
		);
	}

	/**
	 * Register every ELB block type from the build/blocks manifest.
	 */
	public static function register_blocks() {
		// Shared editor script is registered once in ELB_Assets::register()
		// (handle: elb-editor). block.json files reference that handle so
		// WordPress does not enqueue build/loop/index.js once per block.

		// render.php walks parsed_block and re-renders the card template itself
		// (see ELB_Loop::render_loop_builder_cards), so core must not also
		// pre-render every inner block before calling it — that would run each
		// field block's render.php twice per event card.
		$blocks_dir = wp_normalize_path( ELB_PATH . 'build/blocks' );
		if ( ! is_dir( $blocks_dir ) ) {
			return;
		}

		$manifest = $blocks_dir . '/blocks-manifest.php';
		if ( is_readable( $manifest ) && function_exists( 'wp_register_block_metadata_collection' ) ) {
			wp_register_block_metadata_collection( $blocks_dir, $manifest );
		}

		self::register_block_from_dir( $blocks_dir . '/loop-builder', [ 'skip_inner_blocks' => true ] );

		foreach ( self::BLOCKS as $slug ) {
			self::register_block_from_dir( $blocks_dir . '/' . $slug );
		}
	}

	/**
	 * Register one block from its folder. Skip when block.json is missing so
	 * WordPress never treats a Windows path as the block name.
	 *
	 * @param array<string,mixed> $args Extra register_block_type_from_metadata() args.
	 */
	private static function register_block_from_dir( string $dir, array $args = [] ): void {
		$real = realpath( $dir );
		if ( false === $real || ! is_readable( $real . '/block.json' ) ) {
			return;
		}
		register_block_type_from_metadata( wp_normalize_path( $real ), $args );
	}

	/**
	 * Register the "ELB Blocks" block category.
	 *
	 * @param array<int,array<string,mixed>> $categories
	 * @return array<int,array<string,mixed>>
	 */
	public static function register_category( array $categories ): array {
		foreach ( $categories as $cat ) {
			if ( isset( $cat['slug'] ) && 'elb-blocks' === $cat['slug'] ) {
				return $categories;
			}
		}
		array_unshift( $categories, [
			'slug'  => 'elb-blocks',
			'title' => __( 'ELB Blocks', 'template-events-calendar' ),
			'icon'  => 'layout',
		] );
		return $categories;
	}
}
