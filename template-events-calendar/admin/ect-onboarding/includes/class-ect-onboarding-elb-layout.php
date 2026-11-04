<?php
/**
 * Stock Event Loop Builder markup for onboarding draft pages.
 *
 * Inner structure matches the Free editor templates (list-1, list-3, list-4) so Preview
 * and Edit open the same layout the wizard selected.
 *
 * @package Template_Events_Calendar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'ECT_Onboarding_Elb_Layout' ) ) {

	/**
	 * Serializes a loop-builder block from a layoutStyle key.
	 */
	final class ECT_Onboarding_Elb_Layout {

		/**
		 * @param string               $layout_style list-1|list-3|list-4
		 * @param array<string,mixed>  $loop_attrs   elb/loop-builder attributes.
		 * @param string               $date_format  Event Date text format key.
		 * @return string Serialized block markup.
		 */
		public static function serialize( $layout_style, array $loop_attrs, $date_format ) {
			$layout_style = sanitize_key( str_replace( ',', '', (string) $layout_style ) );
			$free_styles  = array( 'list-1', 'list-3', 'list-4' );
			if ( ! in_array( $layout_style, $free_styles, true ) ) {
				$layout_style = 'list-1';
			}
			$date_format = sanitize_text_field( (string) $date_format );
			$default_fmt = self::style_default_date_format( $layout_style );
			if ( '' === $date_format ) {
				$date_format = $default_fmt;
			}
			$apply_format = ( $date_format !== $default_fmt );

			$card   = self::serialize_node( self::card_template( $layout_style ), $date_format, $apply_format );
			$marked = self::block( 'elb/card-template', array(), $card );

			$section = self::flex(
				array(
					'block_id'                       => self::new_id(),
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
					'htmlTag'                        => 'div',
					'topPaddingDesktop'              => 0,
					'rightPaddingDesktop'            => 0,
					'bottomPaddingDesktop'           => 0,
					'leftPaddingDesktop'             => 0,
				),
				$marked,
				true
			);

			$loop_attrs['layoutStyle']      = $layout_style;
			$loop_attrs['isSetup']          = true;
			$loop_attrs['showMonthHeading'] = ( 'list-3' === $layout_style );

			return self::block( 'elb/loop-builder', $loop_attrs, $section );
		}

		/**
		 * @param string $layout_style Layout key.
		 * @return array{0:string,1:array,2:array}
		 */
		private static function card_template( $layout_style ) {
			switch ( $layout_style ) {
				case 'list-1':
					return self::card_list_1();
				case 'list-4':
					return self::card_list_4();
				default:
					return self::card_list_3();
			}
		}

		private static function card_list_1() {
			return array(
				'elb/flex-container',
				array_merge(
					self::card_border(),
					self::card_shadow(),
					self::no_padding(),
					array(
						'variationSelected'     => true,
						'directionDesktop'      => 'row',
						'alignItemsDesktop'     => 'stretch',
						'columnGapDesktop'      => 0,
						'columnGapType'         => 'px',
						'bottomMarginDesktop'   => 20,
						'marginType'            => 'px',
						'backgroundColor'       => '#ffffff',
						'backgroundType'        => 'classic',
						'overflow'              => 'hidden',
						'containerBorderRadius' => 10,
						'className'             => 'elb-card--list-1 elb-card--horizontal-magazine',
					)
				),
				array(
					array(
						'elb/flex-container',
						array_merge(
							self::no_padding(),
							array(
								'variationSelected' => true,
								'directionDesktop'  => 'column',
								'alignItemsDesktop' => 'stretch',
								'overflow'          => 'hidden',
								'widthDesktop'      => 34,
								'widthType'         => '%',
								'contentWidth'      => 'default',
								'className'         => 'elb-card__media-col',
							)
						),
						array(
							array(
								'elb/event-image',
								array(
									'aspectRatio'    => '4/3',
									'imageWidth'     => '100',
									'imageWidthUnit' => '%',
								),
							),
							array(
								'elb/event-meta',
								array_merge(
									self::category_overlay(),
									array(
										'items' => array( self::meta_category( 'list1-cat' ) ),
									)
								),
							),
						),
					),
					array(
						'elb/flex-container',
						array(
							'variationSelected'     => true,
							'directionDesktop'      => 'row',
							'alignItemsDesktop'     => 'stretch',
							'justifyContentDesktop' => 'flex-start',
							'columnGapDesktop'      => 16,
							'columnGapType'         => 'px',
							'topPaddingDesktop'     => 22,
							'bottomPaddingDesktop'  => 22,
							'leftPaddingDesktop'    => 24,
							'rightPaddingDesktop'   => 24,
							'paddingType'           => 'px',
							'widthDesktop'          => 66,
							'widthType'             => '%',
							'contentWidth'          => 'default',
							'className'             => 'elb-card__body elb-card__body--list-1',
						),
						array(
							array(
								'elb/event-date',
								array(
									'format'         => 'badge',
									'datePreset'     => 'classic',
									'overlayOnImage' => false,
									'textAlign'      => 'center',
									'verticalAlign'  => 'top',
								),
							),
							array(
								'elb/flex-container',
								array_merge(
									self::no_padding(),
									array(
										'variationSelected' => true,
										'directionDesktop'  => 'column',
										'alignItemsDesktop' => 'flex-start',
										'rowGapDesktop'     => 0,
										'rowGapType'        => 'px',
										'className'         => 'elb-card__details-col',
									)
								),
								array(
									array(
										'elb/event-title',
										array(
											'level'         => 3,
											'textAlign'     => 'left',
											'fontWeight'    => '700',
											'fontSize'      => '20',
											'paddingBottom' => '20',
											'paddingUnit'   => 'PX',
										),
									),
									array(
										'elb/event-excerpt',
										array(
											'wordCount'     => 28,
											'textAlign'     => 'left',
											'fontSize'      => '14',
											'lineHeight'    => '1.55',
											'paddingBottom' => '20',
											'paddingUnit'   => 'PX',
										),
									),
									array(
										'elb/event-meta',
										array_merge(
											self::meta_row( 20 ),
											array(
												'items'          => array(
													self::meta_venue(
														'list1-venue',
														array(
															'show'     => 'name',
															'maxLines' => 0,
														)
													),
													self::meta_time( 'list1-time' ),
												),
												'textAlign'      => 'left',
												'textColor'      => '#64748b',
												'fontSize'       => '13',
												'blockIconSize'  => 16,
												'blockIconColor' => '#94a3b8',
												'paddingBottom'  => '20',
												'paddingUnit'    => 'PX',
											)
										),
									),
									array(
										'elb/event-meta',
										array_merge(
											self::meta_row( 8 ),
											array(
												'items'          => array( self::meta_price( 'list1-price' ) ),
												'textAlign'      => 'left',
												'textColor'      => '#64748b',
												'fontSize'       => '13',
												'blockIconSize'  => 16,
												'blockIconColor' => '#94a3b8',
												'paddingBottom'  => '20',
												'paddingUnit'    => 'PX',
											)
										),
									),
									array(
										'elb/event-read-more',
										array(
											'format'               => 'button',
											'buttonPreset'         => 'subtle',
											'buttonLabel'          => 'Read more',
											'showButton'           => true,
											'enableLink'           => true,
											'buttonBg'             => '#2563eb',
											'buttonTextColor'      => '#2563eb',
											'buttonHoverBg'        => '#2563eb',
											'buttonHoverTextColor' => '#1d4ed8',
											'textAlign'            => 'left',
											'buttonPaddingTop'     => '8',
											'buttonPaddingRight'   => '18',
											'buttonPaddingBottom'  => '8',
											'buttonPaddingLeft'    => '18',
											'buttonPaddingUnit'    => 'PX',
											'marginTop'            => '4',
										),
									),
								),
							),
						),
					),
				),
			);
		}

		private static function card_list_3() {
			return array(
				'elb/flex-container',
				array_merge(
					self::no_padding(),
					array(
						'variationSelected'   => true,
						'directionDesktop'    => 'row',
						'alignItemsDesktop'   => 'stretch',
						'columnGapDesktop'    => 28,
						'columnGapType'       => 'px',
						'bottomMarginDesktop' => 32,
						'marginType'          => 'px',
						'overflow'            => 'visible',
						'className'           => 'elb-card--list elb-card--list-3 elb-card--timeline',
					)
				),
				array(
					array(
						'elb/flex-container',
						array_merge(
							self::no_padding(),
							array(
								'variationSelected'     => true,
								'directionDesktop'      => 'column',
								'alignItemsDesktop'     => 'center',
								'justifyContentDesktop' => 'center',
								'widthDesktop'          => 12,
								'widthType'             => '%',
								'contentWidth'          => 'default',
								'className'             => 'elb-card__date-col elb-card__date-col--list-3',
							)
						),
						array(
							array(
								'elb/event-date',
								array(
									'format'        => 'badge',
									'datePreset'    => 'minimal',
									'textColor'     => '#0e4d5f',
									'textAlign'     => 'center',
									'showBlockIcon' => false,
								),
							),
						),
					),
					array(
						'elb/flex-container',
						array(
							'variationSelected'          => true,
							'directionDesktop'           => 'column',
							'alignItemsDesktop'          => 'flex-start',
							'justifyContentDesktop'      => 'flex-start',
							'rowGapDesktop'              => 0,
							'rowGapType'                 => 'px',
							'topPaddingDesktop'          => 0,
							'bottomPaddingDesktop'       => 0,
							'leftPaddingDesktop'         => 28,
							'rightPaddingDesktop'        => 0,
							'paddingType'                => 'px',
							'widthDesktop'               => 54,
							'widthType'                  => '%',
							'contentWidth'               => 'default',
							'containerBorderStyle'       => 'solid',
							'containerBorderColor'       => '#0e4d5f',
							'containerBorderTopWidth'    => 0,
							'containerBorderRightWidth'  => 0,
							'containerBorderBottomWidth' => 0,
							'containerBorderLeftWidth'   => 2,
							'className'                  => 'elb-card__body elb-card__body--list-3',
						),
						array(
							array(
								'elb/event-date',
								array(
									'format'        => 'MD,Y',
									'datePreset'    => 'none',
									'showTime'      => true,
									'dateShow'      => 'range',
									'showBlockIcon' => false,
									'className'     => 'elb-card__meta-row elb-card__meta-row--list-3',
									'textAlign'     => 'left',
									'textColor'     => '#0e4d5f',
									'fontSize'      => '13',
									'paddingBottom' => '8',
									'paddingUnit'   => 'PX',
								),
							),
							array(
								'elb/event-title',
								array(
									'level'          => 3,
									'textAlign'      => 'left',
									'fontWeight'     => '500',
									'fontSize'       => '20',
									'lineHeight'     => '1.4',
									'textDecoration' => 'underline',
									'textColor'      => '#0e4d5f',
									'paddingBottom'  => '8',
									'paddingUnit'    => 'PX',
								),
							),
							array(
								'elb/event-meta',
								array_merge(
									self::meta_row( 8 ),
									array(
										'items'          => array(
											self::meta_venue(
												'list3-venue',
												array(
													'show'     => 'full',
													'maxLines' => 0,
												)
											),
										),
										'textAlign'      => 'left',
										'textColor'      => '#0e4d5f',
										'fontSize'       => '13',
										'blockIconSize'  => 15,
										'blockIconColor' => '#0e4d5f',
										'paddingBottom'  => '8',
										'paddingUnit'    => 'PX',
									)
								),
							),
							array(
								'elb/event-excerpt',
								array(
									'wordCount'     => 28,
									'textColor'     => '#1f2937',
									'textAlign'     => 'left',
									'fontSize'      => '14',
									'lineHeight'    => '1.6',
									'paddingBottom' => '12',
									'paddingUnit'   => 'PX',
								),
							),
							array(
								'elb/event-meta',
								array_merge(
									self::meta_row( 8 ),
									array(
										'items'          => array( self::meta_price( 'list3-price' ) ),
										'textAlign'      => 'left',
										'textColor'      => '#1f2937',
										'fontSize'       => '16',
										'blockIconSize'  => 15,
										'blockIconColor' => '#0e4d5f',
										'paddingBottom'  => '8',
										'paddingUnit'    => 'PX',
									)
								),
							),
							array(
								'elb/event-read-more',
								array(
									'format'          => 'link',
									'buttonLabel'     => 'Find out More',
									'showButton'      => true,
									'enableLink'      => true,
									'textColor'       => '#0e4d5f',
									'hoverTextColor'  => '#0a3a48',
									'fontSize'        => '14',
									'textAlign'       => 'left',
									'marginTop'       => '4',
								),
							),
						),
					),
					array(
						'elb/flex-container',
						array_merge(
							self::no_padding(),
							array(
								'variationSelected' => true,
								'directionDesktop'  => 'column',
								'alignItemsDesktop' => 'stretch',
								'overflow'          => 'hidden',
								'widthDesktop'      => 34,
								'widthType'         => '%',
								'contentWidth'      => 'default',
								'className'         => 'elb-card__media-col--list-3',
							)
						),
						array(
							array(
								'elb/event-image',
								array(
									'aspectRatio'    => '3/2',
									'imageWidth'     => '100',
									'imageWidthUnit' => '%',
								),
							),
						),
					),
				),
			);
		}

		private static function card_list_4() {
			return array(
				'elb/flex-container',
				array_merge(
					self::card_border(),
					self::no_padding(),
					array(
						'variationSelected'   => true,
						'directionDesktop'    => 'row',
						'alignItemsDesktop'   => 'stretch',
						'columnGapDesktop'    => 0,
						'columnGapType'       => 'px',
						'bottomMarginDesktop' => 20,
						'marginType'          => 'px',
						'backgroundColor'     => '#ffffff',
						'backgroundType'      => 'color',
						'overflow'            => 'hidden',
						'className'           => 'elb-card--list elb-card--list-4 elb-card--minimal',
					)
				),
				array(
					array(
						'elb/flex-container',
						array(
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
							'backgroundColor'            => '#4fc3f7',
							'backgroundType'             => 'color',
							'containerBorderStyle'       => 'solid',
							'containerBorderColor'       => '#1e88e5',
							'containerBorderTopWidth'    => 0,
							'containerBorderRightWidth'  => 0,
							'containerBorderBottomWidth' => 0,
							'containerBorderLeftWidth'   => 4,
							'className'                  => 'elb-card__date-col elb-card__date-col--list-4',
						),
						array(
							array(
								'elb/event-date',
								array(
									'format'     => 'badge',
									'datePreset' => 'minimal',
									'textAlign'  => 'center',
									'textColor'  => '#1e3a5f',
								),
							),
						),
					),
					array(
						'elb/flex-container',
						array(
							'variationSelected'     => true,
							'directionDesktop'      => 'column',
							'alignItemsDesktop'     => 'flex-start',
							'justifyContentDesktop' => 'center',
							'rowGapDesktop'         => 0,
							'rowGapType'            => 'px',
							'topPaddingDesktop'     => 14,
							'bottomPaddingDesktop'  => 14,
							'leftPaddingDesktop'    => 18,
							'rightPaddingDesktop'   => 18,
							'paddingType'           => 'px',
							'widthDesktop'          => 78,
							'widthType'             => '%',
							'contentWidth'          => 'default',
							'className'             => 'elb-card__body elb-card__body--list-4',
						),
						array(
							array(
								'elb/event-meta',
								array_merge(
									self::meta_row( 8 ),
									array(
										'className'      => 'elb-card__meta-row elb-card__meta-row--list-4',
										'items'          => array(
											array(
												'id'       => 'list4-date',
												'type'     => 'date',
												'visible'  => true,
												'icon'     => 'none',
												'settings' => array(
													'format'       => 'custom',
													'customFormat' => 'M j, Y',
													'showEndDate'  => false,
												),
											),
											self::meta_time( 'list4-time', array( 'allDayLabel' => 'All Day' ) ),
										),
										'textAlign'      => 'left',
										'textColor'      => '#1e3a5f',
										'fontSize'       => '13',
										'blockIconSize'  => 14,
										'blockIconColor' => '#1e3a5f',
										'paddingBottom'  => '4',
										'paddingUnit'    => 'PX',
									)
								),
							),
							array(
								'elb/event-title',
								array(
									'level'          => 3,
									'textAlign'      => 'left',
									'fontWeight'     => '500',
									'fontSize'       => '22',
									'lineHeight'     => '1.35',
									'textDecoration' => 'underline',
									'textColor'      => '#1e3a5f',
									'paddingBottom'  => '8',
									'paddingUnit'    => 'PX',
								),
							),
							array(
								'elb/event-read-more',
								array(
									'format'         => 'link',
									'buttonLabel'    => 'Find out More',
									'showButton'     => true,
									'enableLink'     => true,
									'textColor'      => '#475569',
									'hoverTextColor' => '#2563eb',
									'fontSize'       => '14',
									'textAlign'      => 'left',
									'marginTop'      => '0',
								),
							),
						),
					),
				),
			);
		}

		/**
		 * @param array{0:string,1?:array,2?:array} $node Template node.
		 * @param string                            $date_format Date format key.
		 * @param bool                              $apply_format Whether the user changed the style default.
		 * @return string
		 */
		private static function serialize_node( array $node, $date_format, $apply_format ) {
			$name  = $node[0];
			$attrs = isset( $node[1] ) && is_array( $node[1] ) ? $node[1] : array();
			$inner = isset( $node[2] ) && is_array( $node[2] ) ? $node[2] : array();

			if ( $apply_format && 'elb/event-date' === $name ) {
				$attrs['format'] = $date_format;
				if ( in_array( $date_format, array( 'MD,YT', 'mdyt', 'full' ), true ) ) {
					$attrs['showTime'] = true;
				}
			}

			if ( $apply_format && 'elb/event-meta' === $name ) {
				$attrs = self::apply_date_format_to_meta( $attrs, $date_format );
			}

			$inner_html = '';
			foreach ( $inner as $child ) {
				if ( is_array( $child ) ) {
					$inner_html .= self::serialize_node( $child, $date_format, $apply_format );
				}
			}

			if ( 'elb/flex-container' === $name ) {
				return self::flex( $attrs, $inner_html, ! empty( $attrs['isBlockRootParent'] ) );
			}
			if ( '' !== $inner_html ) {
				return self::block( $name, $attrs, $inner_html );
			}
			return self::leaf( $name, $attrs );
		}

		/**
		 * Default date-format key for a stock layout (matches data-elb-date-format).
		 *
		 * @param string $layout_style Layout key.
		 * @return string
		 */
		private static function style_default_date_format( $layout_style ) {
			$map = array(
				'list-1' => 'MD',
				'list-3' => 'MD,Y',
				'list-4' => 'MD,Y',
			);
			return isset( $map[ $layout_style ] ) ? $map[ $layout_style ] : 'MD,Y';
		}

		/**
		 * Apply the wizard date format to Event Meta date items.
		 * Badge/preset Event Date blocks stay as designed.
		 *
		 * @param array<string,mixed> $attrs        event-meta attributes.
		 * @param string              $date_format  ELB date key.
		 * @return array<string,mixed>
		 */
		private static function apply_date_format_to_meta( array $attrs, $date_format ) {
			if ( empty( $attrs['items'] ) || ! is_array( $attrs['items'] ) ) {
				return $attrs;
			}
			$php_fmt = self::date_format_to_php( $date_format );
			foreach ( $attrs['items'] as $i => $item ) {
				if ( ! is_array( $item ) || 'date' !== ( $item['type'] ?? '' ) ) {
					continue;
				}
				if ( ! isset( $attrs['items'][ $i ]['settings'] ) || ! is_array( $attrs['items'][ $i ]['settings'] ) ) {
					$attrs['items'][ $i ]['settings'] = array();
				}
				$attrs['items'][ $i ]['settings']['format']       = 'custom';
				$attrs['items'][ $i ]['settings']['customFormat'] = $php_fmt;
			}
			return $attrs;
		}

		/**
		 * Event Loop Builder date key → PHP date() pattern for meta date items.
		 *
		 * @param string $date_format ELB date key.
		 * @return string
		 */
		private static function date_format_to_php( $date_format ) {
			$map = array(
				'default' => 'd F Y',
				'MD,Y'    => 'M j, Y',
				'FD,Y'    => 'F j, Y',
				'DM'      => 'd M',
				'DML'     => 'd M l',
				'DF'      => 'd F',
				'MD'      => 'M j',
				'd.FY'    => 'd. F Y',
				'd.F'     => 'd. F',
				'ldF'     => 'l d F',
				'Mdl'     => 'M j l',
				'd.Ml'    => 'd. M l',
				'dMY'     => 'd M Y',
				'MD,YT'   => 'M j, Y',
				'mdyt'    => 'M j, Y',
				'full'    => 'd F Y',
			);
			return isset( $map[ $date_format ] ) ? $map[ $date_format ] : 'M j, Y';
		}

		private static function flex( array $attributes, $inner, $root = false ) {
			if ( empty( $attributes['block_id'] ) ) {
				$attributes['block_id'] = self::new_id();
			}
			$id = sanitize_html_class( $attributes['block_id'] );
			if ( empty( $attributes['elbFrontendCss'] ) ) {
				$attributes['elbFrontendCss'] = self::container_css( $attributes, $root );
			}
			$classes = array( 'wp-block-elb-flex-container' );
			if ( ! empty( $attributes['className'] ) ) {
				$classes[] = $attributes['className'];
			}
			$classes[] = 'elb-block-' . $id;
			if ( $root ) {
				$classes[] = 'alignfull elb-is-root-container';
				if ( ! empty( $attributes['equalHeight'] ) ) {
					$classes[] = 'elb-equal-height';
				}
				$inner = '<div class="elb-flex-container-inner-blocks-wrap">' . $inner . '</div>';
			}
			$html = '<div class="' . esc_attr( implode( ' ', $classes ) ) . '">' . $inner . '</div>';
			return self::block( 'elb/flex-container', $attributes, $html );
		}


		private static function container_css( array $attributes, $root ) {
			$id       = sanitize_html_class( $attributes['block_id'] ?? 'ect-onboard' );
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
			if ( isset( $attributes['containerBorderRadius'] ) && is_numeric( $attributes['containerBorderRadius'] ) ) {
				$rules[] = 'border-radius:' . (float) $attributes['containerBorderRadius'] . 'px';
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
			if ( ! empty( $attributes['boxShadowColor'] ) ) {
				$h     = (float) ( $attributes['boxShadowHOffset'] ?? 0 );
				$v     = (float) ( $attributes['boxShadowVOffset'] ?? 0 );
				$blur  = (float) ( $attributes['boxShadowBlur'] ?? 0 );
				$spread = (float) ( $attributes['boxShadowSpread'] ?? 0 );
				$rules[] = 'box-shadow:' . $h . 'px ' . $v . 'px ' . $blur . 'px ' . $spread . 'px ' . $attributes['boxShadowColor'];
			}
			if ( isset( $attributes['widthDesktop'] ) && is_numeric( $attributes['widthDesktop'] ) ) {
				$w_unit  = (string) ( $attributes['widthType'] ?? '%' );
				$w_unit  = in_array( $w_unit, array( '%', 'px', 'em', 'rem' ), true ) ? $w_unit : '%';
				$width   = (float) $attributes['widthDesktop'];
				$rules[] = 'width:' . $width . $w_unit;
				$rules[] = 'flex:0 0 ' . $width . $w_unit;
				$rules[] = 'max-width:' . $width . $w_unit;
			}

			$direction = (string) ( $attributes['directionDesktop'] ?? 'column' );
			$direction = in_array( $direction, array( 'row', 'row-reverse', 'column', 'column-reverse' ), true ) ? $direction : 'column';
			$align     = (string) ( $attributes['alignItemsDesktop'] ?? 'stretch' );
			$align     = in_array( $align, array( 'stretch', 'flex-start', 'center', 'flex-end' ), true ) ? $align : 'stretch';
			$justify   = (string) ( $attributes['justifyContentDesktop'] ?? 'flex-start' );
			$justify   = in_array( $justify, array( 'flex-start', 'center', 'flex-end', 'space-between', 'space-around', 'space-evenly' ), true ) ? $justify : 'flex-start';
			$layout    = array(
				'display:flex',
				'flex-direction:' . $direction,
				'align-items:' . $align,
				'justify-content:' . $justify,
				'flex-wrap:nowrap',
				'row-gap:' . (float) ( $attributes['rowGapDesktop'] ?? 0 ) . 'px',
				'column-gap:' . (float) ( $attributes['columnGapDesktop'] ?? 0 ) . 'px',
			);

			$css = $selector . '{' . implode( ';', array_merge( $rules, $root ? array() : $layout ) ) . '}';
			if ( $root ) {
				$width = max( 1, (float) ( $attributes['innerContentCustomWidthDesktop'] ?? 1200 ) );
				$wrap  = $selector . '>.elb-flex-container-inner-blocks-wrap,.wp-block-elb-flex-container.alignfull.elb-is-root-container.elb-block-' . $id . '>.elb-flex-container-inner-blocks-wrap';
				$css  .= $wrap . '{' . implode( ';', $layout ) . ';max-width:min(100vw,' . $width . 'px);width:100%;margin-left:auto;margin-right:auto}';
			}
			return $css;
		}

		private static function leaf( $name, array $attributes ) {
			return '<!-- wp:' . esc_html( $name ) . self::attributes_json( $attributes ) . ' /-->';
		}

		private static function block( $name, array $attributes, $inner ) {
			return '<!-- wp:' . esc_html( $name ) . self::attributes_json( $attributes ) . ' -->' . $inner . '<!-- /wp:' . esc_html( $name ) . ' -->';
		}

		private static function attributes_json( array $attributes ) {
			if ( empty( $attributes ) ) {
				return '';
			}
			$json = function_exists( 'serialize_block_attributes' ) ? serialize_block_attributes( $attributes ) : wp_json_encode( $attributes );
			return ' ' . $json;
		}

		private static function new_id() {
			return 'ectob' . wp_generate_password( 8, false, false );
		}

		private static function no_padding() {
			return array(
				'topPaddingDesktop'    => 0,
				'bottomPaddingDesktop' => 0,
				'leftPaddingDesktop'   => 0,
				'rightPaddingDesktop'  => 0,
				'paddingType'          => 'px',
			);
		}

		private static function card_border() {
			return array(
				'containerBorderStyle'       => 'solid',
				'containerBorderColor'       => '#e2e8f0',
				'containerBorderTopWidth'    => 1,
				'containerBorderRightWidth'  => 1,
				'containerBorderBottomWidth' => 1,
				'containerBorderLeftWidth'   => 1,
			);
		}

		private static function card_shadow() {
			return array(
				'boxShadowColor'    => '#00000014',
				'boxShadowHOffset'  => 0,
				'boxShadowVOffset'  => 4,
				'boxShadowBlur'     => 16,
				'boxShadowSpread'   => 0,
				'boxShadowPosition' => 'outset',
			);
		}

		private static function meta_row( $gap ) {
			return array(
				'metaSeparator' => 'none',
				'metaGap'       => (int) $gap,
				'metaWrap'      => true,
			);
		}

		private static function category_badge_typo() {
			return array(
				'catFontSize'       => '11',
				'catFontWeight'     => '700',
				'catTextTransform'  => 'uppercase',
				'catLetterSpacing'  => '0.4',
				'catPaddingTop'     => '4',
				'catPaddingRight'   => '10',
				'catPaddingBottom'  => '4',
				'catPaddingLeft'    => '10',
				'catPaddingUnit'    => 'PX',
			);
		}

		private static function category_overlay() {
			return array_merge(
				self::category_badge_typo(),
				self::meta_row( 8 ),
				array(
					'sourceField'        => 'categories',
					'overlayOnImage'     => true,
					'catTextColor'       => '#ffffff',
					'catBackgroundColor' => '#2563eb',
				)
			);
		}

		private static function meta_category( $id, array $settings = array() ) {
			return array(
				'id'         => $id,
				'type'       => 'category',
				'visible'    => true,
				'icon'       => 'none',
				'badgeStyle' => 'solid',
				'settings'   => array_merge(
					array(
						'showOne'  => false,
						'link'     => false,
						'useColor' => false,
					),
					$settings
				),
			);
		}

		private static function meta_venue( $id, array $settings = array() ) {
			return array(
				'id'       => $id,
				'type'     => 'venue',
				'visible'  => true,
				'icon'     => 'mapMarker',
				'settings' => array_merge(
					array(
						'link' => false,
					),
					$settings
				),
			);
		}

		private static function meta_price( $id, $icon = 'ticket', $badge_style = '' ) {
			$item = array(
				'id'       => $id,
				'type'     => 'price',
				'visible'  => true,
				'icon'     => $icon,
				'settings' => array(
					'freeLabel' => '',
				),
			);
			if ( '' !== $badge_style ) {
				$item['badgeStyle'] = $badge_style;
			}
			return $item;
		}

		private static function meta_time( $id, array $settings = array() ) {
			return array(
				'id'       => $id,
				'type'     => 'time',
				'visible'  => true,
				'icon'     => 'clock',
				'settings' => array_merge(
					array(
						'format'       => 'site',
						'customFormat' => '',
						'showEnd'      => true,
						'showTimezone' => false,
						'allDayLabel'  => '',
					),
					$settings
				),
			);
		}
	}
}
