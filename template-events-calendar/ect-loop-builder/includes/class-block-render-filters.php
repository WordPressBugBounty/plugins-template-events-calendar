<?php
/**
 * render_block filters for ELB blocks: dynamic container CSS, loop nesting, user-width class.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Block_Render_Filters { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/**
	 * The raw dynamic CSS a wrapper/flex-container block contributes — the
	 * wrapper's computed grid CSS, or the flex-container's client-built
	 * elbFrontendCss attribute. Added by filter_rendered_block() when the
	 * block renders (post content, templates, synced patterns, do_blocks(), AJAX).
	 *
	 * @param array<string,mixed> $block
	 * @return string Empty when the block contributes none.
	 */
	private static function css_for_block( array $block ): string {
		$name = $block['blockName'] ?? '';
		if ( 'elb/wrapper' === $name ) {
			return ELB_Wrapper_Css::wrapper_css( ELB_Block_Attrs::of( $block ) );
		}
		if ( 'elb/flex-container' === $name ) {
			$attrs = ELB_Block_Attrs::of( $block );
			$css   = $attrs['elbFrontendCss'] ?? '';
			$css   = is_string( $css ) ? $css : '';
			return $css . ELB_Container_Sizing_Css::root_default_padding_reset( $attrs );
		}
		return '';
	}

	/**
	 * render_block_data: add the dynamic CSS of a loop / wrapper / flex-container tree before
	 * it renders, so rules keep document (parent-before-child) order. filter_rendered_block()
	 * offers the same CSS again; ELB_Dynamic_Css dedupes it.
	 *
	 * @param array<string,mixed> $parsed_block
	 * @return array<string,mixed>
	 */
	public static function add_css_before_render( $parsed_block ) {
		$name = is_array( $parsed_block ) ? (string) ( $parsed_block['blockName'] ?? '' ) : '';
		// Hooked on the global render_block_data filter: other plugins' and core blocks need nothing here.
		if ( 0 !== strpos( $name, 'elb/' ) ) {
			return $parsed_block;
		}
		$tracker = ELB_Render_Tracker::instance();
		if ( 'elb/loop-builder' === $name ) {
			$tracker->enter_loop();
		} elseif ( $tracker->in_loop() ) {
			// Inside a loop (its shell and every card): already covered by the loop's walk.
			return $parsed_block;
		}
		$is_container = 'elb/flex-container' === $name || 'elb/wrapper' === $name;
		if ( 'elb/loop-builder' === $name || $is_container ) {
			// Base CSS first, so it keeps printing ahead of the generated CSS.
			ELB_Assets::enqueue_frontend_runtime();
			// Walk the whole saved tree up front (document order): a loop renders its card template itself,
			// and a nested container is already covered by its outermost ancestor's walk.
			if ( $tracker->outside_tree() ) {
				self::walk_blocks_for_dynamic_css( [ $parsed_block ] );
			}
			if ( $is_container ) {
				$tracker->enter_tree();
			}
		}
		return $parsed_block;
	}

	/**
	 * render_block_elb/loop-builder: the loop finished rendering.
	 *
	 * @param string $content Rendered loop HTML.
	 * @return string
	 */
	public static function leave_loop( $content ) {
		ELB_Render_Tracker::instance()->leave_loop();
		return $content;
	}

	/**
	 * Add the dynamic CSS of every wrapper/flex-container in $blocks, depth first.
	 *
	 * @param array<int,array<string,mixed>> $blocks Parsed blocks.
	 */
	private static function walk_blocks_for_dynamic_css( array $blocks ): void {
		foreach ( $blocks as $block ) {
			$css = self::css_for_block( $block );
			if ( '' !== $css ) {
				ELB_Dynamic_Css::add_block_css( $css );
			}
			if ( ! empty( $block['innerBlocks'] ) ) {
				self::walk_blocks_for_dynamic_css( $block['innerBlocks'] );
			}
		}
	}

	/**
	 * Single render_block callback for ELB blocks: assets and container CSS.
	 *
	 * @param string               $block_content
	 * @param array<string,mixed>  $block
	 * @return string
	 */
	public static function filter_rendered_block( string $block_content, array $block ): string {
		// Hooked per ELB block name (render_block_{$name}), so this is always an elb/* block.
		$name = $block['blockName'] ?? '';

		ELB_Assets::enqueue_frontend_runtime();
		// Only the layouts whose classes are on the rendered root tag load (printed late in the footer on classic themes).
		// The rendered tag, not attrs.className: it is what the CSS matches, even when the saved attribute is stale.
		$root_end = strpos( $block_content, '>' );
		static $seen_root_tags = [];
		if ( false !== $root_end ) {
			$root_tag = substr( $block_content, 0, $root_end );
			if ( ! isset( $seen_root_tags[ $root_tag ] ) ) {
				$seen_root_tags[ $root_tag ] = true;
				ELB_Layout_Styles::enqueue_layout_styles_for( $root_tag );
			}
		}

		if ( 'elb/flex-container' !== $name && 'elb/wrapper' !== $name ) {
			return $block_content;
		}

		ELB_Render_Tracker::instance()->leave_tree();

		if ( self::has_user_width( ELB_Block_Attrs::of( $block ) ) ) {
			$tags = new WP_HTML_Tag_Processor( $block_content );
			$tags->next_tag() && $tags->add_class( 'elb-user-width' );
			$block_content = $tags->get_updated_html();
		}

		return $block_content;
	}

	private static function has_user_width( array $attrs ): bool {
		return ! empty( $attrs['widthSetByUser'] ) && (float) ( $attrs['widthDesktop'] ?? 100 ) > 0
			&& preg_match( '/\belb-card__(body--list-[134]|date-col|media-col)\b/', (string) ( $attrs['className'] ?? '' ) );
	}
}
