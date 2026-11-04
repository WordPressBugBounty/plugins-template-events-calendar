<?php
/**
 * Event Info Row block renderer (venue, time, ticket items with icon boxes and shared typography).
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

class ELB_Event_Info_Row_Renderer extends ELB_Field_Block_Renderer { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	const PREFIX = 'elb-ir-';

	/** Default icon box background. */
	const DEFAULT_ICON_BG = '#eff6ff';

	/**
	 * Assemble flex row layout style string.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param int                 $gap        Flex gap in pixels.
	 * @param string              $row_bg     Optional background color.
	 * @return string Inline CSS string.
	 */
	private static function build_layout_style( array $attributes, int $gap, string $row_bg ): string {
		$layout_style = ELB_Layout_Decls::row_layout_style( $attributes );
		if ( false === strpos( (string) $layout_style, 'display:' ) ) {
			$layout_style = 'display:flex;flex-wrap:wrap;align-items:center;justify-content:flex-start;' . $layout_style;
		}
		if ( false === strpos( (string) $layout_style, 'gap:' ) ) {
			$layout_style .= ';gap:' . max( 0, $gap ) . 'px';
		}
		if ( $row_bg ) {
			$layout_style .= ';background-color:' . esc_attr( $row_bg );
		}
		return $layout_style;
	}

	/**
	 * Responsive media-query CSS for the row's text items.
	 *
	 * @param string              $resp_class Responsive class token.
	 * @param array<string,mixed> $attributes Block attributes.
	 */
	private static function responsive_css( string $resp_class, array $attributes ): string {
		$extra_css = ELB_Breakpoints::rule(
			'.' . $resp_class . ' .elb-info-row__text',
			ELB_Spacing_Styles::field_size_decls( $attributes )
		);
		foreach ( [ 'venue', 'time', 'ticket' ] as $typo_prefix ) {
			$extra_css .= ELB_Breakpoints::rule(
				'.' . $resp_class . ' .elb-info-row__item--' . $typo_prefix . ' .elb-info-row__text',
				ELB_Typography::declarations( (array) $attributes, $typo_prefix )
			);
		}
		return $extra_css;
	}

	/**
	 * Build wrapper HTML attributes for info row block.
	 */
	private static function build_info_row_wrapper_attributes( array $attributes, string $layout_style, string $resp_class, string $typo_class ): string {
		$layout_class = ELB_Layout_Decls::has_custom_row_layout( $attributes, 'venue,time,ticket' ) ? ' elb-has-layout' : '';
		$align_class  = ' ' . self::align_class( $attributes, 'left' );

		return get_block_wrapper_attributes(
			[
				'class' => trim( 'elb-event-info-row elb-card__meta-row ' . $resp_class . ' ' . $typo_class . $layout_class . $align_class ),
				'style' => $layout_style,
			]
		);
	}

	/**
	 * Render the concatenated info items HTML.
	 *
	 * @param array{attributes:array<string,mixed>,items:array<string,array{icon:string,text:string}>,icon_size:int,text_color:string,icon_color:string,icon_bg:string} $ctx Render context.
	 */
	private static function render_info_items( array $ctx ): string {
		$attributes = $ctx['attributes'];
		$order = ELB_Layout_Decls::parse_item_order(
			$attributes['itemOrder'] ?? 'venue,time,ticket',
			[ 'venue', 'time', 'ticket' ],
			[ 'venue', 'time', 'ticket' ]
		);

		$items_html = '';
		foreach ( $order as $item_key ) {
			$items_html .= self::render_item( $item_key, $ctx );
		}
		return $items_html;
	}

	/**
	 * Resolve color and sizing theme parameters for info row.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @return array<string,mixed>
	 */
	private static function resolve_row_theme( array $attributes ): array {
		$gap             = isset( $attributes['flexGap'] ) ? (int) round( (float) $attributes['flexGap'] ) : 32;
		// block.json gives textColor a non-empty default ('#64748b'), so this is never '' in practice.
		$user_text_color = ELB_Css_Utils::sanitize_css_color( $attributes['textColor'] ?? '' );
		$icon_size       = (int) ( $attributes['iconSize'] ?? 18 );

		return [
			'gap'        => $gap,
			'text_color' => $user_text_color,
			'icon_color'      => ELB_Css_Utils::sanitize_css_color( $attributes['iconColor'] ?? '' ),
			'icon_bg'         => ELB_Css_Utils::sanitize_css_color( $attributes['iconBg'] ?? self::DEFAULT_ICON_BG ) ?: self::DEFAULT_ICON_BG,
			'row_bg'          => ELB_Css_Utils::sanitize_css_color( $attributes['backgroundColor'] ?? '' ),
			'icon_size'       => 14 === $icon_size ? 18 : $icon_size,
		];
	}

	/**
	 * Resolve text values for the info row items.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param int                 $post_id    Event post ID.
	 * @return array{venue: string, time: string, ticket: string}|null
	 */
	private static function resolve_item_texts( array $attributes, int $post_id ): ?array {
		$show_row    = self::flag( $attributes, 'showInfoRow' );
		$show_venue  = self::flag( $attributes, 'showVenue' );
		$show_time   = self::flag( $attributes, 'showTime' );
		$show_ticket = self::flag( $attributes, 'showTicket' );

		if ( ! $show_row || ( ! $show_venue && ! $show_time && ! $show_ticket ) ) {
			return null;
		}

		$venue_text = $show_venue ? ELB_Venue_Repository::get_venue_display_text( $post_id, 'name' ) : '';
		$time_str   = $show_time ? ELB_Event_Formatter::get_event_time_display( $post_id ) : '';
		$cost_str   = $show_ticket ? ELB_Event_Cost::get_ticket_display_text( $post_id ) : '';

		if ( '' === (string) $venue_text && '' === (string) $time_str && '' === (string) $cost_str ) {
			return null;
		}

		return [
			'venue'  => $venue_text,
			'time'   => $time_str,
			'ticket' => $cost_str,
		];
	}

	/**
	 * Render the elb/event-info-row block.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param int                 $post_id    Event post ID.
	 * @return string HTML output.
	 */
	protected static function render_field( array $attributes, int $post_id ): string {
		$texts = self::resolve_item_texts( $attributes, $post_id );
		if ( null === $texts ) {
			return '';
		}

		$attr_hash = self::attr_hash( $attributes );
		ELB_Typography::enqueue_fonts( $attributes );
		// Attribute-only theme, layout style and typography classes, shared by every card of this block.
		[ $theme, $layout_style, $typo_class ] = self::memo(
			'styles:' . $attr_hash,
			static function () use ( $attributes ): array {
				$theme = self::resolve_row_theme( $attributes );
				return [ $theme, self::build_layout_style( $attributes, $theme['gap'], $theme['row_bg'] ), ELB_Typography::field_classnames( $attributes ) ];
			}
		);
		$resp_class   = self::design(
			$attributes,
			static function ( string $resp_class ) use ( $attributes ): string {
				return self::responsive_css( $resp_class, $attributes );
			},
			$attr_hash
		);
		$wrapper_attrs = self::build_info_row_wrapper_attributes( $attributes, $layout_style, $resp_class, $typo_class );
		$items = [
			'venue'  => [ 'icon' => 'mapMarker', 'text' => $texts['venue'] ],
			'time'   => [ 'icon' => 'timeToRead', 'text' => $texts['time'] ],
			'ticket' => [ 'icon' => 'ticket', 'text' => $texts['ticket'] ],
		];

		$items_html = self::render_info_items(
			array_intersect_key( $theme, array_flip( [ 'icon_size', 'text_color', 'icon_color', 'icon_bg' ] ) ) + [
				'attributes' => $attributes,
				'items'      => $items,
			]
		);

		return '<div ' . $wrapper_attrs . '>' . $items_html . '</div>';
	}

	/**
	 * Build item text style string.
	 */
	private static function build_item_text_style( string $item_text ): string {
		$text_style = 'color:' . esc_attr( $item_text ) . ';';
		if ( $item_text ) {
			$text_style .= '--elb-field-color:' . esc_attr( $item_text ) . ';';
		}
		return $text_style;
	}

	/**
	 * Build item icon box HTML.
	 */
	private static function build_item_icon_box(
		string $icon_name,
		int $icon_size,
		string $item_icon,
		string $custom_bg,
		string $icon_bg
	): string {
		$icon_wrap_style = '--elb-icon-size:' . (int) $icon_size . 'px;';
		if ( $item_icon ) {
			$icon_wrap_style .= 'color:' . esc_attr( $item_icon ) . ';--elb-icon-color:' . esc_attr( $item_icon ) . ';';
		}
		if ( $custom_bg ) {
			$icon_wrap_style .= '--elb-icon-bg:' . esc_attr( $custom_bg ) . ';';
		} elseif ( $icon_bg && 0 !== strcasecmp( $icon_bg, self::DEFAULT_ICON_BG ) ) {
			$icon_wrap_style .= '--elb-icon-bg:' . esc_attr( $icon_bg ) . ';';
		}

		$icon_html = '' !== $icon_name ? ELB_Icons::icon( $icon_name, $icon_size, $item_icon ) : '';

		return $icon_html
			? '<span class="elb-info-row__icon" style="' . esc_attr( $icon_wrap_style ) . '" aria-hidden="true">' . $icon_html . '</span>'
			: '';
	}

	/**
	 * Render one info item with icon box.
	 *
	 * @param string              $type Item type (venue / time / ticket).
	 * @param array<string,mixed> $ctx  Render context (see render_info_items()).
	 */
	private static function render_item( string $type, array $ctx ): string {
		$attributes = $ctx['attributes'];
		$text_color = $ctx['text_color'];
		$icon_color = $ctx['icon_color'];
		$icon_bg    = $ctx['icon_bg'];
		$icon_size  = $ctx['icon_size'];
		$item       = $ctx['items'][ $type ] ?? null;
		if ( ! $item || '' === (string) $item['text'] ) {
			return '';
		}

		$item_user_text = ELB_Css_Utils::sanitize_css_color( $attributes[ $type . 'TextColor' ] ?? '' );
		$item_text      = $item_user_text ?: $text_color;
		$item_icon      = ELB_Css_Utils::sanitize_css_color( $attributes[ $type . 'IconColor' ] ?? '' ) ?: $icon_color;
		$custom_bg      = ELB_Css_Utils::sanitize_css_color( $attributes[ $type . 'IconBg' ] ?? '' );
		$icon_name      = ! empty( $attributes[ $type . 'Icon' ] ) ? (string) $attributes[ $type . 'Icon' ] : $item['icon'];

		$text_style = self::build_item_text_style( $item_text );
		$icon_box   = self::build_item_icon_box( $icon_name, $icon_size, $item_icon, $custom_bg, $icon_bg );

		return '<div class="elb-info-row__item elb-info-row__item--' . esc_attr( $type ) . '">'
			. $icon_box
			. '<span class="elb-info-row__text" style="' . esc_attr( $text_style ) . '">' . esc_html( $item['text'] ) . '</span>'
			. '</div>';
	}
}
