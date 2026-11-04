<?php
/**
 * Convert saved Event Block markup to Event Loop Builder markup.
 *
 * @package ect
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- ECT_ is this plugin's prefix. Plugin Check only accepts prefixes of 4 or more characters.

if ( ! class_exists( 'ECT_Block_Content_Migrator' ) ) {
class ECT_Block_Content_Migrator {

	/**
	 * Build one Event Loop Builder block from legacy attributes.
	 *
	 * @param array<string,mixed> $attributes Legacy attributes.
	 * @param int                 $post_id Post ID used for deterministic block IDs.
	 * @param int                 $index Block index used for deterministic block IDs.
	 * @return array{supported:bool,content:string,warnings:string[]}
	 */
	public static function from_legacy_attributes( array $attributes, $post_id = 0, $index = 1 ) {
		$mapped = ECT_Block_Attribute_Mapper::map( $attributes );
		return array(
			'supported' => $mapped['supported'],
			'content'   => $mapped['supported'] ? self::target_markup( $mapped, $post_id, $index ) : '',
			'warnings'  => $mapped['warnings'],
		);
	}

	/**
	 * Inspect content without changing it.
	 *
	 * @param string $content Post content.
	 * @return array{legacy:int,target:int,supported:int,unsupported:int,warnings:string[]}
	 */
	public static function inspect( $content ) {
		$report = array(
			'legacy'      => 0,
			'target'      => 0,
			'supported'   => 0,
			'unsupported' => 0,
			'warnings'    => array(),
		);
		self::inspect_blocks( parse_blocks( (string) $content ), $report );
		$report['warnings'] = array_values( array_unique( $report['warnings'] ) );
		return $report;
	}

	/**
	 * Convert all supported legacy blocks in a document.
	 *
	 * @param string $content Post content.
	 * @param int    $post_id Post ID used for deterministic block IDs.
	 * @return array{content:string,converted:int,unsupported:int,warnings:string[]}
	 */
	public static function migrate( $content, $post_id ) {
		$state  = array(
			'converted'   => 0,
			'unsupported' => 0,
			'warnings'    => array(),
			'index'       => 0,
			'post_id'     => absint( $post_id ),
		);
		$blocks = self::migrate_blocks( parse_blocks( (string) $content ), $state );
		return array(
			'content'     => serialize_blocks( $blocks ),
			'converted'   => $state['converted'],
			'unsupported' => $state['unsupported'],
			'warnings'    => array_values( array_unique( $state['warnings'] ) ),
		);
	}

	private static function inspect_blocks( array $blocks, array &$report ) {
		foreach ( $blocks as $block ) {
			$name = $block['blockName'] ?? '';
			if ( ECT_Block_Attribute_Mapper::LEGACY_BLOCK === $name ) {
				++$report['legacy'];
				$mapped = ECT_Block_Attribute_Mapper::map( is_array( $block['attrs'] ?? null ) ? $block['attrs'] : array() );
				if ( $mapped['supported'] ) {
					++$report['supported'];
				} else {
					++$report['unsupported'];
				}
				$report['warnings'] = array_merge( $report['warnings'], $mapped['warnings'] );
			} elseif ( ECT_Block_Attribute_Mapper::TARGET_BLOCK === $name ) {
				++$report['target'];
			}
			if ( ! empty( $block['innerBlocks'] ) ) {
				self::inspect_blocks( $block['innerBlocks'], $report );
			}
		}
	}

	private static function migrate_blocks( array $blocks, array &$state ) {
		foreach ( $blocks as &$block ) {
			if ( ECT_Block_Attribute_Mapper::LEGACY_BLOCK === ( $block['blockName'] ?? '' ) ) {
				$mapped            = ECT_Block_Attribute_Mapper::map( is_array( $block['attrs'] ?? null ) ? $block['attrs'] : array() );
				$state['warnings'] = array_merge( $state['warnings'], $mapped['warnings'] );
				if ( ! $mapped['supported'] ) {
					++$state['unsupported'];
					continue;
				}
				++$state['index'];
				$serialized  = self::target_markup( $mapped, $state['post_id'], $state['index'] );
				$replacement = parse_blocks( $serialized );
				if ( 1 !== count( $replacement ) || ECT_Block_Attribute_Mapper::TARGET_BLOCK !== ( $replacement[0]['blockName'] ?? '' ) ) {
					++$state['unsupported'];
					$state['warnings'][] = 'The generated target block could not be parsed.';
					continue;
				}
				$block = $replacement[0];
				++$state['converted'];
				continue;
			}

			if ( ! empty( $block['innerBlocks'] ) ) {
				$block['innerBlocks'] = self::migrate_blocks( $block['innerBlocks'], $state );
				self::replace_inner_content_blocks( $block );
			}
		}
		unset( $block );
		return $blocks;
	}

	private static function replace_inner_content_blocks( array &$block ) {
		if ( ! isset( $block['innerContent'] ) || ! is_array( $block['innerContent'] ) ) {
			return;
		}
		$inner_index = 0;
		foreach ( $block['innerContent'] as &$part ) {
			if ( null === $part && isset( $block['innerBlocks'][ $inner_index ] ) ) {
				++$inner_index;
			}
		}
		unset( $part );
	}

	private static function target_markup( array $mapped, $post_id, $index ) {
		$base_id       = sanitize_key( 'ect-migrated-' . absint( $post_id ) . '-' . absint( $index ) );
		$layout        = $mapped['attributes']['layoutStyle'];
		$card          = 'list-4' === $layout
			? self::minimal_card( $mapped['fields'], $base_id )
			: self::default_card( $mapped['fields'], $base_id );
		$card_template = self::block( 'elb/card-template', array(), $card );
		$section_attrs = array(
			'block_id'                       => $base_id . '-section',
			'variationSelected'              => true,
			'isBlockRootParent'              => true,
			'directionDesktop'               => 'column',
			'childrenWidth'                  => 'full',
			'contentWidth'                   => 'alignfull',
			'innerContentWidth'              => 'alignwide',
			'innerContentCustomWidthDesktop' => 1200,
			'innerContentCustomWidthType'    => 'px',
			'equalHeight'                    => false,
			'alignItemsDesktop'              => 'stretch',
			'alignItemsTablet'               => 'stretch',
			'alignItemsMobile'               => 'stretch',
			'rowGapDesktop'                  => 0,
			'rowGapType'                     => 'px',
			'htmlTag'                        => 'div',
			'topPaddingDesktop'              => 0,
			'rightPaddingDesktop'            => 0,
			'bottomPaddingDesktop'           => 0,
			'leftPaddingDesktop'             => 0,
			'className'                      => 'ect-migrated-legacy-section ect-migrated-legacy-' . $layout,
			'_ectLegacyLayout'               => $layout,
			'_ectLegacySkinColor'            => $mapped['fields']['card']['skinColor'],
		);
		$section       = self::flex( $section_attrs, $card_template, true );
		return self::block( ECT_Block_Attribute_Mapper::TARGET_BLOCK, $mapped['attributes'], $section );
	}

	private static function default_card( array $fields, $base_id ) {
		$date = self::leaf(
			'elb/event-date',
			array_merge(
				self::merge_legacy_field(
					array(
						'format'        => 'MD,YT',
						'dateShow'      => 'start',
						'showBlockIcon' => false,
						'className'     => 'elb-card__meta-row elb-card__meta-row--list-3 ect-migrated-legacy-main-date',
						'textAlign'     => 'left',
						'textColor'     => '#0e4d5f',
						'fontSize'      => '13',
						'marginTop'     => '0',
						'marginBottom'  => '0',
					),
					$fields['date']
				),
				array(
					'datePreset' => 'none',
					'dateShow'   => 'start',
					'format'     => $fields['date']['format'] ?? 'MD,YT',
				)
			)
		);
		$body = $date . self::leaf(
			'elb/event-title',
			self::merge_legacy_field(
				array(
					'level'          => 3,
					'textAlign'      => 'left',
					'fontWeight'     => '500',
					'fontSize'       => '20',
					'lineHeight'     => '1.4',
					'lineHeightUnit' => 'em',
					'textDecoration' => 'underline',
					'textColor'      => '#0e4d5f',
					'marginTop'      => '0',
					'marginBottom'   => '0',
				),
				$fields['title']
			)
		);
		if ( ! empty( $fields['venue']['enabled'] ) ) {
			$body .= self::leaf(
				'elb/event-meta',
				self::merge_legacy_field(
					array(
						'className'      => 'ect-migrated-legacy-venue',
						'metaSeparator'  => 'none',
						'metaGap'        => 8,
						'metaWrap'       => true,
						'textAlign'      => 'left',
						'textColor'      => '#0e4d5f',
						'fontSize'       => '13',
						'blockIconSize'  => 15,
						'blockIconColor' => '#0e4d5f',
						'marginTop'      => '0',
						'marginBottom'   => '0',
						'paddingTop'     => '0',
						'paddingRight'   => '0',
						'paddingBottom'  => '0',
						'paddingLeft'    => '0',
						'paddingUnit'    => 'PX',
					),
					self::without_enabled( $fields['venue'] )
				)
			);
		}
		if ( ! empty( $fields['excerpt']['enabled'] ) ) {
			$body .= self::leaf(
				'elb/event-excerpt',
				self::merge_legacy_field(
					array(
						'wordCount'     => 28,
						'textColor'     => '#1f2937',
						'textAlign'     => 'left',
						'fontSize'       => '14',
						'lineHeight'     => '1.6',
						'lineHeightUnit' => 'em',
						'marginTop'     => '0',
						'marginBottom'  => '0',
					),
					self::without_enabled( $fields['excerpt'] )
				)
			);
		}
		$body .= self::leaf(
			'elb/event-meta',
			array_merge(
				array_merge(
					array(
						'showIcons'      => false,
						'blockIconSize'  => 15,
						'blockIconColor' => '#0e4d5f',
						'metaSeparator'  => 'none',
						'metaGap'        => 8,
						'metaWrap'       => true,
						'textAlign'      => 'left',
						'textColor'      => '#1f2937',
						'fontSize'       => '16',
						'marginTop'      => '0',
						'marginBottom'   => '0',
						'paddingTop'     => '0',
						'paddingRight'   => '0',
						'paddingBottom'  => '0',
						'paddingLeft'    => '0',
						'paddingUnit'    => 'PX',
					),
					$fields['price']
				),
				array(
					'className' => 'ect-migrated-legacy-price',
					'items'     => array(
						array(
							'id'         => 'ect-migrated-price',
							'type'       => 'price',
							'visible'    => true,
							'icon'       => 'none',
							'badgeStyle' => 'text',
							'settings'   => array( 'freeLabel' => '' ),
						),
					),
				)
			)
		);
		$body .= self::leaf(
			'elb/event-read-more',
			array_merge(
				self::merge_legacy_field(
					array(
						'format'               => 'link',
						'buttonLabel'          => 'Find out More',
						'showButton'           => true,
						'showBlockIcon'        => false,
						'blockIcon'            => '',
						'enableLink'           => true,
						'textColor'            => '#0e4d5f',
						'buttonTextColor'      => '#0e4d5f',
						'hoverTextColor'       => '#0a3a48',
						'buttonHoverTextColor' => '#0a3a48',
						'fontSize'             => '14',
						'textAlign'            => 'left',
						'marginTop'            => '4',
					),
					$fields['read_more']
				),
				array(
					'showBlockIcon' => false,
					'blockIcon'     => '',
					'className'     => 'ect-migrated-legacy-read-more',
				)
			)
		);

		$columns  = self::flex(
			array(
				'block_id'              => $base_id . '-date',
				'variationSelected'     => true,
				'directionDesktop'      => 'column',
				'alignItemsDesktop'     => 'center',
				'justifyContentDesktop' => 'center',
				'widthDesktop'          => 14,
				'widthType'             => '%',
				'contentWidth'          => 'default',
				'topPaddingDesktop'     => 0,
				'bottomPaddingDesktop'  => 0,
				'leftPaddingDesktop'    => 0,
				'rightPaddingDesktop'   => 0,
				'paddingType'           => 'px',
				'className'             => 'elb-card__date-col elb-card__date-col--list-3 ect-migrated-legacy-date-col',
			),
			self::leaf(
				'elb/event-date',
				self::date_badge_attrs(
					$fields,
					array(
						'textColor'     => '#0e4d5f',
						'textAlign'     => 'center',
						'showBlockIcon' => false,
					)
				)
			)
		);
		$columns .= self::flex(
			array(
				'block_id'                   => $base_id . '-body',
				'variationSelected'          => true,
				'directionDesktop'           => 'column',
				'alignItemsDesktop'          => 'flex-start',
				'justifyContentDesktop'      => 'flex-start',
				'rowGapDesktop'              => 0,
				'rowGapType'                 => 'px',
				'topPaddingDesktop'          => 0,
				'bottomPaddingDesktop'       => 0,
				'leftPaddingDesktop'         => 30,
				'rightPaddingDesktop'        => 0,
				'paddingType'                => 'px',
				'widthDesktop'               => 48,
				'widthType'                  => '%',
				'contentWidth'               => 'default',
				'containerBorderStyle'       => 'solid',
				'containerBorderColor'       => $fields['card']['skinColor'],
				'containerBorderTopWidth'    => 0,
				'containerBorderRightWidth'  => 0,
				'containerBorderBottomWidth' => 0,
				'containerBorderLeftWidth'   => 3,
				'className'                  => 'elb-card__body elb-card__body--list-3 ect-migrated-legacy-body-col',
			),
			$body
		);
		if ( ! empty( $fields['image']['enabled'] ) ) {
			$columns .= self::flex(
				array(
					'block_id'             => $base_id . '-image',
					'variationSelected'    => true,
					'directionDesktop'      => 'column',
					'alignItemsDesktop'     => 'flex-start',
					'justifyContentDesktop' => 'flex-start',
					'overflow'              => 'hidden',
					'widthDesktop'          => 38,
					'widthType'             => '%',
					'contentWidth'          => 'default',
					'topPaddingDesktop'     => 0,
					'bottomPaddingDesktop'  => 0,
					'leftPaddingDesktop'    => 0,
					'rightPaddingDesktop'   => 0,
					'paddingType'           => 'px',
					'className'             => 'elb-card__media-col--list-3 ect-migrated-legacy-image-col',
				),
				self::leaf(
					'elb/event-image',
					array(
						'aspectRatio'    => 'auto',
						'imageWidth'     => '100',
						'imageWidthUnit' => '%',
					)
				)
			);
		}
		return self::flex(
			array(
				'block_id'             => $base_id . '-card',
				'variationSelected'    => true,
				'directionDesktop'     => 'row',
				'alignItemsDesktop'    => 'stretch',
				'columnGapDesktop'     => 0,
				'columnGapType'        => 'px',
				'bottomMarginDesktop'  => 20,
				'marginType'           => 'px',
				'overflow'             => 'visible',
				'topPaddingDesktop'    => 0,
				'bottomPaddingDesktop' => 0,
				'leftPaddingDesktop'   => 0,
				'rightPaddingDesktop'  => 0,
				'paddingType'          => 'px',
				'className'            => 'elb-card--list elb-card--list-3 elb-card--timeline ect-migrated-legacy-card',
			),
			$columns
		);
	}

	private static function minimal_card( array $fields, $base_id ) {
		$date    = self::flex(
			array(
				'block_id'                   => $base_id . '-date',
				'variationSelected'          => true,
				'directionDesktop'           => 'column',
				'alignItemsDesktop'          => 'center',
				'justifyContentDesktop'      => 'center',
				'widthDesktop'               => 22,
				'widthType'                  => '%',
				'contentWidth'               => 'default',
				'topPaddingDesktop'          => 16,
				'bottomPaddingDesktop'       => 16,
				'leftPaddingDesktop'         => 12,
				'rightPaddingDesktop'        => 12,
				'paddingType'                => 'px',
				'backgroundColor'            => $fields['card']['simpleColor'],
				'backgroundType'             => 'color',
				'containerBorderStyle'       => 'solid',
				'containerBorderColor'       => '#1e88e5',
				'containerBorderTopWidth'    => 0,
				'containerBorderRightWidth'  => 0,
				'containerBorderBottomWidth' => 0,
				'containerBorderLeftWidth'   => 4,
				'className'                  => 'elb-card__date-col elb-card__date-col--list-4 ect-migrated-legacy-minimal-date-col',
			),
			self::leaf(
				'elb/event-date',
				self::date_badge_attrs(
					$fields,
					array(
						'textAlign' => 'center',
						'textColor' => '#1e3a5f',
					)
				)
			)
		);
		$body    = self::leaf(
			'elb/event-date',
			array_merge(
				self::merge_legacy_field(
					array(
						'className'     => 'elb-card__meta-row elb-card__meta-row--list-4 ect-migrated-legacy-main-date',
						'datePreset'    => 'none',
						'dateShow'      => 'start',
						'textAlign'     => 'left',
						'textColor'     => '#1e3a5f',
						'fontSize'      => '13',
						'marginTop'     => '0',
						'marginBottom'  => '0',
						'paddingBottom' => '4',
						'paddingUnit'   => 'PX',
					),
					$fields['date']
				),
				array(
					'datePreset' => 'none',
					'dateShow'   => 'start',
					'format'     => $fields['date']['format'] ?? 'MD,YT',
				)
			)
		);
		$body   .= self::leaf(
			'elb/event-title',
			self::merge_legacy_field(
				array(
					'level'          => 3,
					'textAlign'      => 'left',
					'fontWeight'     => '500',
					'fontSize'       => '22',
					'lineHeight'     => '1.35',
					'lineHeightUnit' => 'em',
					'textDecoration' => 'underline',
					'textColor'      => '#1e3a5f',
					'marginTop'      => '0',
					'marginBottom'   => '0',
					'paddingBottom'  => '8',
					'paddingUnit'    => 'PX',
				),
				$fields['title']
			)
		);
		$body   .= self::leaf(
			'elb/event-read-more',
			array_merge(
				self::merge_legacy_field(
					array(
						'format'               => 'link',
						'buttonLabel'          => 'Find out More',
						'showButton'           => true,
						'showBlockIcon'        => false,
						'blockIcon'            => '',
						'enableLink'           => true,
						'textColor'            => '#475569',
						'buttonTextColor'      => '#475569',
						'hoverTextColor'       => '#2563eb',
						'buttonHoverTextColor' => '#2563eb',
						'fontSize'             => '14',
						'textAlign'            => 'left',
						'marginTop'            => '0',
					),
					$fields['read_more']
				),
				array(
					'showBlockIcon' => false,
					'blockIcon'     => '',
					'className'     => 'ect-migrated-legacy-read-more',
				)
			)
		);
		$content = self::flex(
			array(
				'block_id'              => $base_id . '-body',
				'variationSelected'     => true,
				'directionDesktop'      => 'column',
				'alignItemsDesktop'     => 'flex-start',
				'justifyContentDesktop' => 'center',
				'rowGapDesktop'         => 0,
				'rowGapType'            => 'px',
				'topPaddingDesktop'     => 15,
				'bottomPaddingDesktop'  => 15,
				'leftPaddingDesktop'    => 10,
				'rightPaddingDesktop'   => 10,
				'paddingType'           => 'px',
				'widthDesktop'          => 78,
				'widthType'             => '%',
				'contentWidth'          => 'default',
				'className'             => 'elb-card__body elb-card__body--list-4 ect-migrated-legacy-minimal-body',
			),
			$body
		);
		return self::flex(
			array(
				'block_id'                   => $base_id . '-card',
				'variationSelected'          => true,
				'directionDesktop'           => 'row',
				'alignItemsDesktop'          => 'stretch',
				'columnGapDesktop'           => 0,
				'columnGapType'              => 'px',
				'bottomMarginDesktop'        => 20,
				'marginType'                 => 'px',
				'backgroundColor'            => '#ffffff',
				'backgroundType'             => 'color',
				'overflow'                   => 'hidden',
				'containerBorderStyle'       => 'solid',
				'containerBorderColor'       => '#e2e8f0',
				'containerBorderTopWidth'    => 1,
				'containerBorderRightWidth'  => 1,
				'containerBorderBottomWidth' => 1,
				'containerBorderLeftWidth'   => 1,
				'topPaddingDesktop'          => 0,
				'bottomPaddingDesktop'       => 0,
				'leftPaddingDesktop'         => 0,
				'rightPaddingDesktop'        => 0,
				'paddingType'                => 'px',
				'className'                  => 'elb-card--list elb-card--list-4 elb-card--minimal ect-migrated-legacy-minimal-card',
			),
			$date . $content
		);
	}

	private static function without_enabled( array $attributes ) {
		unset( $attributes['enabled'] );
		return $attributes;
	}

	/**
	 * Side date badge keeps layout/skin. Content-date italic/underline/transform stay on the in-body date.
	 *
	 * @param array<string,mixed> $fields   Mapped field bags.
	 * @param array<string,mixed> $defaults Badge defaults before the shared date bag.
	 * @return array<string,mixed>
	 */
	private static function date_badge_attrs( array $fields, array $defaults ) {
		$attrs = array_merge(
			array(
				'format'     => 'badge',
				'datePreset' => 'minimal',
			),
			$defaults,
			$fields['date'],
			array(
				'format'         => 'badge',
				'datePreset'     => 'minimal',
				'className'      => 'ect-migrated-legacy-date-badge',
				'textColor'      => $fields['card']['skinColor'],
				'fontFamily'     => '',
				'fontSize'       => '24',
				'fontWeight'     => '700',
				'lineHeight'     => '1',
				'lineHeightUnit' => 'em',
				'letterSpacing'  => '0',
			)
		);
		unset( $attrs['fontStyle'], $attrs['textDecoration'], $attrs['textTransform'] );
		return $attrs;
	}

	private static function leaf( $name, array $attributes ) {
		return '<!-- wp:' . esc_html( $name ) . self::attributes_json( $attributes ) . ' /-->';
	}

	/**
	 * Overlay Event Block field attributes onto layout defaults.
	 *
	 * Event Block line-height "initial" must not keep the template 1.4em/1.6em values,
	 * which opened extra space under title and description.
	 *
	 * @param array<string,mixed> $template Layout defaults.
	 * @param array<string,mixed> $field    Mapped Event Block field attrs.
	 * @return array<string,mixed>
	 */
	private static function merge_legacy_field( array $template, array $field ) {
		$merged = array_merge( $template, $field );
		if ( ! isset( $field['lineHeight'] ) ) {
			unset( $merged['lineHeight'], $merged['lineHeightUnit'] );
		}
		return $merged;
	}

	private static function block( $name, array $attributes, $inner ) {
		return '<!-- wp:' . esc_html( $name ) . self::attributes_json( $attributes ) . ' -->' . $inner . '<!-- /wp:' . esc_html( $name ) . ' -->';
	}

	private static function flex( array $attributes, $inner, $root = false ) {
		$id            = sanitize_html_class( $attributes['block_id'] ?? 'ect-migrated' );
		$legacy_layout = sanitize_key( $attributes['_ectLegacyLayout'] ?? '' );
		$legacy_skin   = sanitize_hex_color( $attributes['_ectLegacySkinColor'] ?? '' );
		unset( $attributes['_ectLegacyLayout'], $attributes['_ectLegacySkinColor'] );
		if ( empty( $attributes['elbFrontendCss'] ) ) {
			$attributes['elbFrontendCss'] = self::container_frontend_css( $attributes, $root, $legacy_layout, $legacy_skin );
		}
		$classes = array( 'wp-block-elb-flex-container' );
		if ( ! empty( $attributes['className'] ) ) {
			$classes[] = $attributes['className'];
		}
		$classes[] = 'elb-block-' . $id;
		if ( $root ) {
			$classes[] = 'alignfull elb-is-root-container elb-equal-height';
			$inner     = '<div class="elb-flex-container-inner-blocks-wrap">' . $inner . '</div>';
		}
		$html = '<div class="' . esc_attr( implode( ' ', $classes ) ) . '">' . $inner . '</div>';
		return self::block( 'elb/flex-container', $attributes, $html );
	}

	/**
	 * Build the initial frontend CSS normally generated when Gutenberg saves a container.
	 *
	 * @param array $attributes Container attributes.
	 * @param bool  $root       Whether this is the loop section container.
	 * @return string
	 */
	private static function container_frontend_css( array $attributes, $root, $legacy_layout = '', $legacy_skin = '' ) {
		$id       = sanitize_html_class( $attributes['block_id'] ?? 'ect-migrated' );
		$selector = ':is(.wp-block-elb-flex-container.elb-block-' . $id . ')';
		$unit     = strtolower( (string) ( $attributes['paddingType'] ?? 'px' ) );
		$unit     = in_array( $unit, array( 'px', '%', 'em', 'rem', 'vw', 'vh' ), true ) ? $unit : 'px';
		$rules    = array();

		foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
			$key = $side . 'PaddingDesktop';
			if ( isset( $attributes[ $key ] ) && is_numeric( $attributes[ $key ] ) ) {
				$rules[] = 'padding-' . $side . ':' . (float) $attributes[ $key ] . $unit;
			}
		}
		if ( isset( $attributes['bottomMarginDesktop'] ) && is_numeric( $attributes['bottomMarginDesktop'] ) ) {
			$margin_unit = strtolower( (string) ( $attributes['marginType'] ?? 'px' ) );
			$margin_unit = in_array( $margin_unit, array( 'px', '%', 'em', 'rem', 'vw', 'vh' ), true ) ? $margin_unit : 'px';
			$rules[]     = 'margin-bottom:' . (float) $attributes['bottomMarginDesktop'] . $margin_unit;
		}

		$overflow = (string) ( $attributes['overflow'] ?? 'visible' );
		if ( in_array( $overflow, array( 'visible', 'hidden', 'auto', 'scroll' ), true ) ) {
			$rules[] = 'overflow:' . $overflow;
		}

		$background = sanitize_hex_color( (string) ( $attributes['backgroundColor'] ?? '' ) );
		if ( $background ) {
			$rules[] = 'background-color:' . $background;
		}

		$border_style = (string) ( $attributes['containerBorderStyle'] ?? '' );
		$border_color = sanitize_hex_color( (string) ( $attributes['containerBorderColor'] ?? '' ) );
		if ( in_array( $border_style, array( 'solid', 'dashed', 'dotted', 'double' ), true ) ) {
			$rules[] = 'border-style:' . $border_style;
			if ( $border_color ) {
				$rules[] = 'border-color:' . $border_color;
			}
			foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
				$key = 'containerBorder' . ucfirst( $side ) . 'Width';
				if ( isset( $attributes[ $key ] ) && is_numeric( $attributes[ $key ] ) ) {
					$rules[] = 'border-' . $side . '-width:' . (float) $attributes[ $key ] . 'px';
				}
			}
		}

		$direction = (string) ( $attributes['directionDesktop'] ?? 'column' );
		$direction = in_array( $direction, array( 'row', 'row-reverse', 'column', 'column-reverse' ), true ) ? $direction : 'column';
		$align     = (string) ( $attributes['alignItemsDesktop'] ?? 'stretch' );
		$align     = in_array( $align, array( 'stretch', 'flex-start', 'center', 'flex-end' ), true ) ? $align : 'stretch';
		$justify   = (string) ( $attributes['justifyContentDesktop'] ?? 'flex-start' );
		$justify   = in_array( $justify, array( 'flex-start', 'center', 'flex-end', 'space-between', 'space-around', 'space-evenly' ), true ) ? $justify : 'center';
		$layout    = array(
			'flex-direction:' . $direction,
			'align-items:' . $align,
			'justify-content:' . $justify,
			'flex-wrap:nowrap',
			'row-gap:' . (float) ( $attributes['rowGapDesktop'] ?? 20 ) . 'px',
			'column-gap:' . (float) ( $attributes['columnGapDesktop'] ?? 20 ) . 'px',
		);

		$css = $selector . '{' . implode( ';', array_merge( $rules, $root ? array() : $layout ) ) . '}';
		if ( $root ) {
			$width = max( 1, (float) ( $attributes['innerContentCustomWidthDesktop'] ?? 1200 ) );
			$css  .= $selector . '>.elb-flex-container-inner-blocks-wrap{' . implode( ';', $layout ) . ';--inner-content-custom-width:min(100vw,' . $width . 'px);max-width:var(--inner-content-custom-width);width:100%;margin-left:auto;margin-right:auto}';
			$css  .= self::legacy_layout_css( $selector, $legacy_layout, $legacy_skin );
		}

		return $css;
	}

	/**
	 * Preserve legacy Event Block geometry without changing shared ELB styles.
	 *
	 * @param string $selector Root migrated section selector.
	 * @param string $layout   ELB layout name.
	 * @param string $skin     Legacy skin color.
	 * @return string
	 */
	private static function legacy_layout_css( $selector, $layout, $skin ) {
		if ( ! in_array( $layout, array( 'list-3', 'list-4' ), true ) ) {
			return '';
		}

		$css = '';

		if ( 'list-4' === $layout ) {
			$css .= '@media only screen and (min-width:769px){';
			$css .= $selector . ' .ect-migrated-legacy-minimal-card{margin:5px auto 20px!important;background:transparent!important;border-color:rgba(0,0,0,.125)!important}';
			$css .= $selector . ' .ect-migrated-legacy-minimal-date-col{flex:0 0 22%!important;width:22%!important;max-width:22%!important}';
			$css .= $selector . ' .ect-migrated-legacy-minimal-body{flex:0 0 78%!important;width:78%!important;max-width:78%!important}';
			$css .= '}';
			return $css;
		}

		$skin = $skin ?: '#00445e';
		$css .= '@media only screen and (min-width:769px){';
		$css .= $selector . ' .ect-migrated-legacy-card{margin:5px auto 20px!important}';
		$css .= $selector . ' .ect-migrated-legacy-date-col{flex:0 0 14%!important;width:14%!important;max-width:14%!important;min-width:0!important;padding:25px!important}';
		$css .= $selector . ' .ect-migrated-legacy-body-col{flex:0 0 47.5%!important;width:47.5%!important;max-width:47.5%!important;padding-left:30px!important;padding-right:30px!important;border-left-width:3px!important}';
		$css .= $selector . ' .ect-migrated-legacy-image-col{flex:0 0 38.5%!important;width:38.5%!important;max-width:38.5%!important;align-self:flex-start;justify-content:flex-start}';
		$css .= '}';
		$css .= '@media only screen and (max-width:768px){';
		$css .= $selector . ' .ect-migrated-legacy-card{flex-direction:column!important;flex-wrap:nowrap!important;gap:0!important;margin:5px auto 20px!important;border-left:3px solid ' . $skin . '!important}';
		$css .= $selector . ' .ect-migrated-legacy-date-col{order:0!important;flex:0 0 auto!important;width:100%!important;max-width:100%!important;min-width:0!important;padding:0 0 0 38px!important;align-items:flex-start!important}';
		$css .= $selector . ' .ect-migrated-legacy-date-col>.wp-block-elb-event-date{display:block!important;align-self:flex-start!important;width:auto!important;max-width:100%!important;text-align:left!important}';
		$css .= $selector . ' .ect-migrated-legacy-date-col .elb-date-badge--minimal{flex-direction:row!important;gap:4px!important;min-width:0!important}';
		$css .= $selector . ' .ect-migrated-legacy-image-col{order:1!important;flex:0 0 auto!important;width:calc(100% - 17px)!important;max-width:calc(100% - 17px)!important;margin-left:17px!important}';
		$css .= $selector . ' .ect-migrated-legacy-body-col{order:2!important;flex:0 0 auto!important;width:100%!important;max-width:100%!important;padding:0 0 0 18px!important;border:0!important}';
		$css .= '}';

		return $css;
	}

	private static function attributes_json( array $attributes ) {
		if ( empty( $attributes ) ) {
			return '';
		}
		$json = function_exists( 'serialize_block_attributes' ) ? serialize_block_attributes( $attributes ) : wp_json_encode( $attributes );
		return ' ' . $json;
	}
}
}
