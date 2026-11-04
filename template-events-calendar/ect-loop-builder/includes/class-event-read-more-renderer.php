<?php
/**
 * Event Read More block render.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Event_Read_More_Renderer extends ELB_Field_Block_Renderer { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	const PREFIX = 'elb-rm-';

	/**
	 * Whether the elb/event-read-more block shows anything.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @return bool True when the read-more control should render.
	 */
	protected static function should_render( array $attributes ): bool {
		return self::flag( $attributes, 'showButton' );
	}

	/**
	 * Render the elb/event-read-more block.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param int                 $post_id    Event post ID.
	 * @return string HTML output.
	 */
	protected static function render_field( array $attributes, int $post_id ): string {
		$label     = ELB_Event_Link::button_label( $attributes );
		$attr_hash = self::attr_hash( $attributes );

		$resp_class = self::design(
			$attributes,
			static function ( string $resp_class ) use ( $attributes ): string {
				// No cost badge on this row: no text targets needed.
				return ELB_Ticket_Button::row_css( $resp_class, $attributes, ':is(.elb-ticket-btn, .elb-ticket-link)' )
					. ELB_Ticket_Button_Styles::anchor_size_css( $attributes, $resp_class );
			},
			$attr_hash
		);
		$ticket     = self::memo(
			'ticket:' . $attr_hash . ':1',
			static function () use ( $attributes ): array {
				$ticket = ELB_Ticket_Button_Styles::ticket_button_inline_styles( $attributes );
				$border = implode( ';', ELB_Field_Styles::border_decls( $attributes ) );
				if ( $border ) {
					$ticket['button'] .= $border . ';';
					$ticket['link']   .= $border . ';';
				}
				return $ticket;
			}
		);
		$btn_style  = $ticket['button'];
		$link_style = $ticket['link'];

		$extra = trim( 'elb-event-read-more ' . self::align_class( $attributes ) );
		// Signal for list CSS: the user set their own button colours (don't treat the fill as auto-restored).
		if ( '' !== (string) ( $attributes['buttonBg'] ?? '' ) || '' !== (string) ( $attributes['buttonTextColor'] ?? '' ) ) {
			$extra .= ' elb-rm-user-colors';
		}
		$wrapper_attrs = get_block_wrapper_attributes(
			self::memo(
				'row:' . $attr_hash . ':' . $resp_class . ':' . $extra,
				static function () use ( $attributes, $resp_class, $extra ): array {
					$args  = ELB_Ticket_Button::row_wrapper_args( $attributes, $resp_class, $extra );
					// Plain link without button colours: text colour as --elb-field-color, like the editor (the List 3/4 link reset reads it).
					$color = ELB_Css_Utils::sanitize_css_color( (string) ( $attributes['textColor'] ?? '' ) );
					if ( '' !== $color && 'link' === ( $attributes['format'] ?? 'link' ) && false === strpos( $extra, 'elb-rm-user-colors' ) ) {
						$args['style'] = ltrim( $args['style'] . ';--elb-field-color:' . $color, ';' );
					}
					return $args;
				}
			)
		);

		$btn_inner = ELB_Ticket_Button::event_anchor( $attributes, $post_id, $label, $btn_style, $link_style, 'link' );

		return '<div ' . $wrapper_attrs . '>' . $btn_inner . '</div>';
	}
}
