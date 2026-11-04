<?php
/**
 * Legacy Event Block attribute mapping.
 *
 * @package ect
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- ECT_ is this plugin's prefix. Plugin Check only accepts prefixes of 4 or more characters.

if ( ! class_exists( 'ECT_Block_Attribute_Mapper' ) ) {
class ECT_Block_Attribute_Mapper {

	const LEGACY_BLOCK = 'ebec/event-list';
	const TARGET_BLOCK = 'elb/loop-builder';
	const VERSION      = '1.0.0';

	/**
	 * Convert a legacy attribute bag.
	 *
	 * @param array<string,mixed> $legacy Legacy block attributes.
	 * @return array{supported:bool,attributes:array<string,mixed>,fields:array<string,array<string,mixed>>,warnings:string[]}
	 */
	public static function map( array $legacy ) {
		$legacy  = array_merge( self::legacy_defaults(), $legacy );
		$layout  = isset( $legacy['event_layout'] ) ? sanitize_key( $legacy['event_layout'] ) : 'default';
		$layouts = array(
			'default' => 'list-3',
			'minimal' => 'list-4',
		);

		if ( ! isset( $layouts[ $layout ] ) ) {
			return array(
				'supported'  => false,
				'attributes' => array(),
				'fields'     => array(),
				'warnings'   => array( sprintf( 'Unsupported legacy layout: %s', $layout ) ),
			);
		}

		$warnings        = array();
		$category_failed = false;
		$categories      = self::map_categories( $legacy['ebec_ev_category'] ?? array(), $warnings, $category_failed );
		if ( $category_failed ) {
			return array(
				'supported'  => false,
				'attributes' => array(),
				'fields'     => array(),
				'warnings'   => $warnings,
			);
		}
		$is_range    = ! empty( $legacy['ebec_event_source'] );
		$date_from   = $is_range ? self::date_value( $legacy['ebec_date_range_start'] ?? '' ) : '';
		$date_to     = $is_range ? self::date_value( $legacy['ebec_date_range_end'] ?? '' ) : '';
		$date_format = self::date_format( $legacy['ebec_date_formats'] ?? 'MD,YT' );
		if ( $is_range && ( '' === $date_from || '' === $date_to ) ) {
			return array(
				'supported'  => false,
				'attributes' => array(),
				'fields'     => array(),
				'warnings'   => array( 'The custom date range is invalid.' ),
			);
		}
		if ( null === $date_format ) {
			return array(
				'supported'  => false,
				'attributes' => array(),
				'fields'     => array(),
				'warnings'   => array( 'The legacy date format is unsupported.' ),
			);
		}
		$target     = array(
			'postType'           => 'tribe_events',
			'perPage'            => min( 48, max( 1, absint( $legacy['ebec_max_events'] ?? 10 ) ) ),
			'offset'             => 0,
			'dateFrom'           => $date_from,
			'dateTo'             => $date_to,
			'paginationMode'     => 'none',
			'noResultsText'      => sanitize_text_field( $legacy['no_event_text'] ?? 'There is No Event' ),
			'isSetup'            => true,
			'layoutStyle'        => $layouts[ $layout ],
			'eventsTime'         => $is_range ? 'between_dates' : 'all',
			'showEvents'         => self::event_status( $legacy['ebec_type'] ?? 'all' ),
			'eventsType'         => 'any',
			'eventsOrder'        => self::order( $legacy['ebec_order'] ?? 'ASC' ),
			'eventsOrderBy'      => 'start_date',
			'selectedCategories' => $categories,
			'selectedTags'       => array(),
			'selectedOrganizers' => array(),
			'selectedVenues'     => array(),
			'showMonthHeading'   => 'show_header' === ( $legacy['event_header_type'] ?? 'show_header' ),
			'useCategoryColor'   => 'minimal' !== $layout,
		);
		$skin_color = self::color( $legacy['main_skin_color'] );
		if ( 'default' === $layout ) {
			$target['monthHeadingColor']        = $skin_color;
			$target['monthHeadingFontSize']     = '20';
			$target['monthHeadingFontWeight']   = '600';
			$target['monthHeadingLineColor']    = $skin_color;
			$target['monthHeadingLineWidth']    = '3';
			$target['monthHeadingGap']          = '8';
			$target['monthHeadingMarginBottom'] = '25';
			$target['timelineLineColor']        = $skin_color;
		}
		if ( 'minimal' === $layout && self::color( $legacy['event_featured_color'] ) !== self::color( $legacy['event_simple_color'] ) ) {
			$warnings[] = 'Featured-event date colors have no direct ELB equivalent; the legacy simple-event color is used.';
		}

		$fields = array(
			'date'      => array_merge(
				array(
					'format'     => $date_format,
					'datePreset' => 'minimal',
					'textColor'  => self::color( $legacy['event_date_color'] ?? '' ),
				),
				self::typography( $legacy, 'event_date' )
			),
			'title'     => array_merge(
				array(
					'enableLink' => true,
					'level'      => 3,
					'textColor'  => self::color( $legacy['event_title_color'] ?? '' ),
				),
				self::typography( $legacy, 'event_title' )
			),
			'venue'     => array_merge(
				array(
					'items'     => array(
						array(
							'id'         => 'ect-migrated-venue',
							'type'       => 'venue',
							'visible'    => true,
							'icon'       => 'mapMarker',
							'badgeStyle' => 'text',
							'settings'   => array(
								'show'     => 'full',
								'link'     => false,
								'maxLines' => 0,
							),
						),
					),
					'showIcons' => true,
					'textColor' => self::color( $legacy['event_venue_color'] ?? '' ),
				),
				self::typography( $legacy, 'event_venue' )
			),
			'excerpt'   => array_merge(
				array(
					'wordCount'          => 55,
					'replaceFullContent' => 'full' === ( $legacy['event_desc_type'] ?? 'short' ),
					'textColor'          => self::color( $legacy['event_description_color'] ?? '' ),
				),
				self::typography( $legacy, 'event_description' )
			),
			'read_more' => array_merge(
				array(
					'format'          => 'link',
					'buttonLabel'     => sanitize_text_field( $legacy['event_link_name'] ?? 'Find out More' ),
					'showButton'      => true,
					'showBlockIcon'   => false,
					'blockIcon'       => '',
					'enableLink'      => true,
					'textColor'       => self::color( $legacy['event_link_color'] ?? '' ),
					'buttonTextColor' => self::color( $legacy['event_link_color'] ?? '' ),
				),
				self::typography( $legacy, 'event_link' )
			),
			'price'     => array(
				'textColor' => $skin_color,
			),
			'card'      => array(
				'skinColor'   => $skin_color,
				'simpleColor' => self::color( $legacy['event_simple_color'] ),
			),
		);

		$fields['venue']['enabled']   = 'no' === ( $legacy['ebec_venue'] ?? 'no' ) && 'minimal' !== $layout;
		$fields['excerpt']['enabled'] = 'yes' === ( $legacy['ebec_display_desc'] ?? 'yes' ) && 'minimal' !== $layout;
		$fields['image']              = array( 'enabled' => 'minimal' !== $layout );

		return array(
			'supported'  => true,
			'attributes' => array_filter(
				$target,
				static function ( $value ) {
					return null !== $value; }
			),
			'fields'     => $fields,
			'warnings'   => $warnings,
		);
	}

	private static function map_categories( $values, array &$warnings, &$failed ) {
		$mapped = array();
		foreach ( array_unique( array_map( 'sanitize_title', (array) $values ) ) as $slug ) {
			if ( '' === $slug || 'all' === $slug ) {
				continue;
			}
			$term = get_term_by( 'slug', $slug, 'tribe_events_cat' );
			if ( ! $term || is_wp_error( $term ) ) {
				$warnings[] = sprintf( 'Event category could not be resolved: %s', $slug );
				$failed     = true;
				continue;
			}
			$mapped[] = array(
				'id'   => (int) $term->term_id,
				'name' => $term->name,
			);
		}
		return $mapped;
	}

	private static function event_status( $value ) {
		return array(
			'future' => 'upcoming',
			'past'   => 'past',
		)[ sanitize_key( $value ) ] ?? 'both';
	}

	private static function order( $value ) {
		$value = strtoupper( sanitize_text_field( $value ) );
		return in_array( $value, array( 'ASC', 'DESC' ), true ) ? $value : 'ASC';
	}

	private static function date_value( $value ) {
		$value = substr( sanitize_text_field( $value ), 0, 10 );
		$date  = DateTime::createFromFormat( '!Y-m-d', $value );
		return $date && $date->format( 'Y-m-d' ) === $value ? $value : '';
	}

	private static function date_format( $value ) {
		$value   = sanitize_text_field( $value );
		$aliases = array(
			'FD'  => 'FD,Y',
			'jMl' => 'DML',
		);
		if ( isset( $aliases[ $value ] ) ) {
			$value = $aliases[ $value ];
		}
		$allowed = array( 'default', 'MD,Y', 'MD,YT', 'FD,Y', 'DM', 'DML', 'DF', 'MD', 'd.FY', 'd.F', 'ldF', 'Mdl', 'd.Ml', 'dMY', 'full', 'dFT' );
		return in_array( $value, $allowed, true ) ? $value : null;
	}

	private static function color( $value ) {
		$color = sanitize_hex_color( $value );
		return $color ? $color : '';
	}

	private static function typography( array $legacy, $prefix ) {
		$mapped = array();
		$keys   = array(
			'family'         => 'fontFamily',
			'font'           => 'fontSize',
			'weight'         => 'fontWeight',
			'transform'      => 'textTransform',
			'style'          => 'fontStyle',
			'decoration'     => 'textDecoration',
			'line_height'    => 'lineHeight',
			'letter_spacing' => 'letterSpacing',
		);
		foreach ( $keys as $legacy_suffix => $target_key ) {
			$key = $prefix . '_' . $legacy_suffix;
			if ( ! isset( $legacy[ $key ] ) || '' === (string) $legacy[ $key ] || 'initial' === $legacy[ $key ] ) {
				continue;
			}
			if ( 'line_height' === $legacy_suffix ) {
				if ( ! is_numeric( $legacy[ $key ] ) ) {
					continue;
				}
				// The old Event Block slider is "Line Height (in Pixel)" and its CSS appends px.
				// Leaving the unit off makes ELB treat 50 as 50em and opens a huge gap.
				$mapped['lineHeight']     = sanitize_text_field( (string) $legacy[ $key ] );
				$mapped['lineHeightUnit'] = 'px';
				continue;
			}
			if ( 'letter_spacing' === $legacy_suffix ) {
				$mapped['letterSpacing']     = sanitize_text_field( (string) $legacy[ $key ] );
				$mapped['letterSpacingUnit'] = 'px';
				continue;
			}
			if ( 'font' === $legacy_suffix ) {
				$mapped['fontSize']     = sanitize_text_field( (string) $legacy[ $key ] );
				$mapped['fontSizeUnit'] = 'px';
				continue;
			}
			$value = sanitize_text_field( (string) $legacy[ $key ] );
			if ( 'transform' === $legacy_suffix && 'normal' === $value ) {
				$value = 'none';
			}
			$mapped[ $target_key ] = $value;
		}
		return $mapped;
	}

	private static function legacy_defaults() {
		$defaults   = array(
			'ebec_ev_category'        => array( 'all' ),
			'ebec_max_events'         => '10',
			'ebec_venue'              => 'no',
			'ebec_display_desc'       => 'yes',
			'ebec_type'               => 'all',
			'ebec_date_formats'       => 'MD,YT',
			'ebec_order'              => 'ASC',
			'ebec_event_source'       => false,
			'event_header_type'       => 'show_header',
			'event_layout'            => 'default',
			'event_desc_type'         => 'short',
			'event_link_name'         => 'Find out More',
			'no_event_text'           => 'There is No Event',
			'main_skin_color'         => '#00445e',
			'event_date_color'        => '#00445e',
			'event_title_color'       => '#00445e',
			'event_venue_color'       => '#00445e',
			'event_description_color' => '#515d64',
			'event_link_color'        => '#00445e',
			'event_simple_color'      => '#99d6b6',
			'event_featured_color'    => '#66baff',
		);
		$typography = array(
			'event_date'        => array( 15, 'bold', 'initial' ),
			'event_title'       => array( 26, 'bold', 'underline' ),
			'event_venue'       => array( 15, 'bold', 'initial' ),
			'event_description' => array( 13, 'bold', 'initial' ),
			'event_link'        => array( 16, 'normal', 'initial' ),
		);
		foreach ( $typography as $prefix => $values ) {
			$defaults[ $prefix . '_font' ]           = $values[0];
			$defaults[ $prefix . '_family' ]         = 'Abel';
			$defaults[ $prefix . '_weight' ]         = $values[1];
			$defaults[ $prefix . '_transform' ]      = 'none';
			$defaults[ $prefix . '_style' ]          = 'initial';
			$defaults[ $prefix . '_decoration' ]     = $values[2];
			$defaults[ $prefix . '_line_height' ]    = 'initial';
			$defaults[ $prefix . '_letter_spacing' ] = 0;
		}
		return $defaults;
	}
}
}
