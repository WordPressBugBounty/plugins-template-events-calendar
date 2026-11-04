<?php
/**
 * Loop-builder block discovery and card rendering.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

class ELB_Loop { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/**
	 * Whether the loop section’s first child is an elb/wrapper (grid shell).
	 *
	 * @param mixed $section Parsed section block or null.
	 * @return bool True when the section opens with elb/wrapper.
	 */
	public static function section_has_wrapper( $section ): bool {
		return is_array( $section )
			&& isset( $section['innerBlocks'][0]['blockName'] )
			&& 'elb/wrapper' === $section['innerBlocks'][0]['blockName'];
	}

	/**
	 * Card template blocks of a loop: the card-template's children, or the container's.
	 *
	 * @param array $loop_block Parsed elb/loop-builder block.
	 * @return array Parsed card blocks (may be empty).
	 */
	public static function resolve_card_templates( array $loop_block ): array {
		$section     = $loop_block['innerBlocks'][0] ?? null;
		$has_wrapper = self::section_has_wrapper( $section );
		$container   = $has_wrapper ? ( $section['innerBlocks'][0] ?? [] ) : ( $section ?? [] );
		$first_child = $container['innerBlocks'][0] ?? null;
		return ( isset( $first_child['blockName'] ) && 'elb/card-template' === $first_child['blockName'] )
			? ( $first_child['innerBlocks'] ?? [] )
			: ( $container['innerBlocks'] ?? [] );
	}

	/**
	 * Render the card(s) for the current event in the loop.
	 *
	 * @param array<int,array<string,mixed>> $card_templates Parsed card blocks.
	 * @param ELB_Layout_Hooks               $layout         Layout hooks (card wrapper).
	 * @return string Rendered card HTML for the current event.
	 */
	private static function render_event_card( array $card_templates, ELB_Layout_Hooks $layout ): string {
		$context   = [
			'elb/postId'   => get_the_ID(),
			'elb/postType' => get_post_type(),
		];
		$card_html = '';
		foreach ( $card_templates as $card_tpl ) {
			if ( empty( $card_tpl['blockName'] ) ) {
				continue;
			}
			$card_html .= ( new WP_Block( $card_tpl, $context ) )->render();
		}
		return $layout->wrap_card( $card_html, (int) get_the_ID() );
	}

	/**
	 * Query events and render every card for an elb/loop-builder block.
	 *
	 * @param array<string,mixed> $loop_block Parsed elb/loop-builder block.
	 * @return array{html:string,layout?:ELB_Layout_Hooks,json_ld?:string} Card HTML plus layout hooks and optional JSON-LD.
	 */
	public static function render_loop_builder_cards( array $loop_block ): array {
		$attrs = $loop_block['attrs'] ?? [];

		$card_templates = self::resolve_card_templates( $loop_block );
		if ( empty( $card_templates ) ) {
			return [ 'html' => '' ];
		}

		$query = ELB_Event_Query_Builder::query( $attrs );
		if ( ! $query ) {
			return [ 'html' => '' ];
		}

		ELB_Event_Cache::prime_for_query( $query );

		$cards_html     = '';
		$section_block  = $loop_block['innerBlocks'][0] ?? null;
		$layout         = ELB_Layout_Hooks::resolve( $attrs, is_array( $section_block ) ? $section_block : null );
		while ( $query->have_posts() ) {
			$query->the_post();
			$cards_html .= $layout->before_card( (int) get_the_ID() );
			$cards_html .= self::render_event_card( $card_templates, $layout );
		}
		$json_ld = '';
		if ( $query->posts && class_exists( 'Tribe__Events__JSON_LD__Event' ) ) {
			$json_ld = Tribe__Events__JSON_LD__Event::instance()->get_markup( $query->posts );
		}
		wp_reset_postdata();

		return [ 'html' => $cards_html, 'layout' => $layout, 'json_ld' => $json_ld ];
	}

	/**
	 * Assemble and render the container shell for the loop builder.
	 *
	 * @param array<string,mixed> $section     Section block schema.
	 * @param bool                $has_wrapper Whether section has wrapper child.
	 * @param string              $token       Placeholder token for inner cards.
	 * @param array<string,mixed> $context     Block context.
	 * @return string Rendered shell HTML.
	 */
	private static function render_shell_markup( array $section, bool $has_wrapper, string $token, array $context ): string {
		$replace_children_with_token = static function ( array $node ) use ( $token ) {
			$node['innerBlocks'] = [];
			$content             = [];
			$token_placed        = false;
			foreach ( (array) ( $node['innerContent'] ?? [] ) as $chunk ) {
				if ( is_string( $chunk ) ) {
					$content[] = $chunk;
				} elseif ( ! $token_placed ) {
					$content[]    = $token;
					$token_placed = true;
				}
			}
			if ( ! $token_placed ) {
				$content[] = $token;
			}
			$node['innerContent'] = $content;
			return $node;
		};

		$shell = $section;
		if ( $has_wrapper ) {
			$wrapper = $section['innerBlocks'][0] ?? null;
			if ( ! is_array( $wrapper ) ) {
				return '';
			}
			$shell['innerBlocks'][0] = $replace_children_with_token( $wrapper );
		} else {
			$shell = $replace_children_with_token( $section );
		}

		$shell_html = ( new WP_Block( $shell, $context ) )->render();
		return is_string( $shell_html ) ? $shell_html : '';
	}

	/**
	 * Generate and enqueue container inline CSS for the loop section.
	 */
	private static function enqueue_container_css( array $section, string $loop_key = '' ): void {
		$dedupe_key = '' !== $loop_key ? $loop_key : md5( wp_json_encode( $section ) );
		ELB_Inline_Css::add_loop_section_css(
			$dedupe_key,
			static function () use ( $section ): string {
				$container_type_css  = ELB_Container_Sizing_Css::section_container_type_css( ELB_Block_Attrs::of( $section ) );
				$container_type_css .= ELB_Container_Sizing_Css::nested_containers_sizing_css( [ $section ] );
				$container_type_css .= ELB_Card_Layout_Css::card_layout_css( $section );
				return wp_strip_all_tags( $container_type_css );
			}
		);
	}

	/**
	 * Render the elb/loop-builder block.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param mixed               $block      Block instance.
	 * @return string HTML output.
	 */
	public static function render_loop_builder( array $attributes, $block ): string {
		if ( empty( $attributes['isSetup'] ) || empty( $block->parsed_block['innerBlocks'] ) || ! is_array( $block->parsed_block['innerBlocks'] ) ) {
			return '';
		}

		$section = $block->parsed_block['innerBlocks'][0] ?? null;
		if ( ! is_array( $section ) ) {
			return '';
		}

		$page_data  = self::render_loop_builder_cards( $block->parsed_block );
		$cards_html = isset( $page_data['html'] ) ? (string) $page_data['html'] : '';

		if ( '' === $cards_html ) {
			$no_results = esc_html( trim( (string) ( $attributes['noResultsText'] ?? '' ) ) ?: __( 'No events found.', 'template-events-calendar' ) );
			return '<p class="elb-no-results">' . $no_results . '</p>';
		}

		$token      = '<!--ELB_LOOP_CARDS-->';
		$shell_html = self::render_shell_markup( $section, self::section_has_wrapper( $section ), $token, $block->context ?? [] );
		$output     = str_replace( $token, $cards_html, $shell_html );

		self::enqueue_container_css( $section );
		$equal_height_class = ! empty( $section['attrs']['equalHeight'] ) ? ' elb-equal-height' : '';
		$mh_style           = isset( $page_data['layout'] ) ? $page_data['layout']->wrapper_attr() : '';
		$json_ld            = isset( $page_data['json_ld'] ) ? (string) $page_data['json_ld'] : '';
		$comment            = '<!---------- Events Loop Builder Version:' . esc_html( ELB_VER ) . ' By Cool Plugins Team-------------->';

		return $json_ld . $comment . '<div ' . get_block_wrapper_attributes( [ 'class' => 'elb-loop-output' . $equal_height_class ] ) . $mh_style . '>' . $output . '</div>';
	}
}
