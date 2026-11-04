<?php
/**
 * Event Share block renderer (social network buttons and native copy link).
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

class ELB_Event_Share_Renderer extends ELB_Field_Block_Renderer { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	const PREFIX = 'elb-sh-';

	/** Share presets (editor: event-share register.js). */
	const PRESETS = [ 'classic', 'flat' ];

	const FREE_NETWORKS = [ 'facebook', 'twitter', 'copy' ];

	/**
	 * Canonical brand colors for each supported social network.
	 */
	const NETWORK_COLORS = [
		'facebook' => '#1877F2',
		'twitter'  => '#000000',
		'copy'     => '#374151',
	];

	/**
	 * Build sharing URLs for all supported networks.
	 *
	 * @param string $permalink Encoded event URL.
	 * @param string $title     Plaintext event title.
	 * @return array<string,string>
	 */
	public static function build_network_urls( string $permalink, string $title ): array {
		$enc_url   = rawurlencode( $permalink );
		$enc_title = rawurlencode( $title );

		return [
			'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . $enc_url,
			'twitter'  => 'https://twitter.com/intent/tweet?url=' . $enc_url . '&text=' . $enc_title,
			'copy'     => $permalink,
		];
	}

	/**
	 * Retrieve localized labels for each network.
	 *
	 * @return array<string,string>
	 */
	public static function get_network_labels(): array {
		static $labels = null;
		return $labels ??= [
			'facebook' => __( 'Facebook', 'template-events-calendar' ),
			'twitter'  => __( 'X (Twitter)', 'template-events-calendar' ),
			'copy'     => __( 'Copy Link', 'template-events-calendar' ),
		];
	}

	/**
	 * Assemble layout and container inline style rules.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param string[]            $flex_decls Pre-computed flex layout declarations (see flex_layout_decls()).
	 * @return string Inline CSS string.
	 */
	private static function build_wrap_style( array $attributes, array $flex_decls ): string {
		$align_decls = ELB_Css_Utils::without_props(
			ELB_Layout_Decls::text_align_layout_decls( $attributes ),
			[ 'display:', 'justify-content:' ]
		);

		return implode( ';', array_filter( array_merge( ELB_Field_Styles::field_decls( $attributes ), $align_decls, $flex_decls ) ) );
	}

	/**
	 * Row flex layout shared by the wrapper and the networks list (defaults: wrap, 8px gap, textAlign justify).
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @return string[]
	 */
	private static function flex_layout_decls( array $attributes ): array {
		return ELB_Layout_Decls::flex_decls(
			$attributes,
			[
				'display'   => 'flex',
				'direction' => 'row',
				'justify'   => ELB_Css_Utils::align_to_flex( sanitize_key( $attributes['textAlign'] ?? 'left' ) ),
				'align'     => 'flex-start',
				'wrap'      => 'wrap',
				'gap'       => '8px',
			]
		);
	}

	/**
	 * Build HTML markup for all share network buttons.
	 *
	 * @param array<string>       $networks  List of network keys.
	 * @param array<string,string> $urls     URLs per network.
	 * @param int                 $size      Icon size in pixels.
	 * @param int                 $radius    Border radius in pixels.
	 * @param bool                $labels    Whether to display text labels.
	 * @param string              $permalink Raw event URL.
	 * @return string HTML output.
	 */
	private static function build_network_buttons( array $networks, array $urls, int $size, int $radius, bool $labels, string $permalink ): string {
		$net_colors   = self::NETWORK_COLORS;
		$net_labels   = self::get_network_labels();
		$buttons_html = '';
		$size_box     = $labels
			? 'height:' . $size . 'px;width:auto;padding:0 12px;border-radius:' . $radius . 'px;'
			: 'width:' . $size . 'px;height:' . $size . 'px;border-radius:' . $radius . 'px;';

		foreach ( (array) $networks as $net ) {
			if ( ! is_string( $net ) || ! isset( $urls[ $net ] ) ) {
				continue;
			}
			$label = $net_labels[ $net ];
			$color = $net_colors[ $net ];
			$style = '--elb-share-color:' . esc_attr( $color ) . ';' . $size_box;

			$inner = ELB_Icons::inline_icon( $net ) . ( $labels ? '<span class="elb-share__label">' . esc_html( $label ) . '</span>' : '' );
			if ( 'copy' === $net ) {
				$buttons_html .= '<button type="button" class="elb-share__btn elb-share__btn--copy" data-elb-copy="' . esc_url( $permalink ) . '" aria-label="' . esc_attr( $label ) . '" style="' . esc_attr( $style ) . '">' . $inner . '</button>';
				continue;
			}

			$buttons_html .= '<a href="' . esc_url( $urls[ $net ] ) . '" class="elb-share__btn elb-share__btn--' . esc_attr( $net ) . '" target="_blank" rel="noopener noreferrer" aria-label="' . esc_attr( $label ) . '" style="' . esc_attr( $style ) . '">' . $inner . '</a>';
		}
		return $buttons_html;
	}

	/**
	 * Render the elb/event-share block.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param int                 $post_id    Event post ID.
	 * @return string HTML output.
	 */
	protected static function render_field( array $attributes, int $post_id ): string {
		$permalink = get_permalink( $post_id );
		if ( ! $permalink ) {
			return '';
		}
		$title     = html_entity_decode( wp_strip_all_tags( get_post_field( 'post_title', $post_id ) ), ENT_QUOTES, 'UTF-8' );

		$networks     = $attributes['shareNetworks'] ?? [ 'facebook', 'twitter', 'copy' ];
		$networks     = array_values( array_filter(
			(array) $networks,
			static fn( $net ) => in_array( $net, self::FREE_NETWORKS, true )
		) );
		$preset       = sanitize_key( (string) ( $attributes['sharePreset'] ?? 'classic' ) );
		$preset       = in_array( $preset, self::PRESETS, true ) ? $preset : 'classic';
		$size         = (int) ( $attributes['shareSize'] ?? 44 );
		$labels       = (bool) ( $attributes['showLabels'] ?? false );
		$radius       = (int) ( $attributes['borderRadius'] ?? 8 );

		$urls         = self::build_network_urls( $permalink, $title );
		$uid          = wp_unique_id( 'elb-share-' );
		$attr_hash    = self::attr_hash( $attributes );
		// flex_layout_decls()/build_wrap_style() are attribute-only, so cache them per unique
		// attribute set instead of rebuilding for every card of this block.
		[ $flex_decls, $wrap_style ] = self::memo(
			'wrap:' . $attr_hash,
			static function () use ( $attributes ): array {
				$flex_decls = self::flex_layout_decls( $attributes );
				return [ $flex_decls, self::build_wrap_style( $attributes, $flex_decls ) ];
			}
		);
		$resp_class   = self::design(
			$attributes,
			static function ( string $resp_class ) use ( $attributes ): string {
				return ELB_Spacing_Styles::field_scoped_css( $attributes, $resp_class );
			},
			$attr_hash
		);

		$buttons_html = self::build_network_buttons( (array) $networks, $urls, $size, $radius, $labels, $permalink );

		$wrapper_args = [
			'class' => trim( 'elb-share-wrap elb-share--' . $preset . ' elb-share--horizontal ' . ELB_Typography::field_classnames( $attributes ) . ' ' . $resp_class ),
			'style' => $wrap_style,
		];
		if ( empty( $attributes['anchor'] ) ) {
			$wrapper_args['id'] = $uid;
		}
		$wrapper_attrs = get_block_wrapper_attributes( $wrapper_args );

		return '<div ' . $wrapper_attrs . '>' . $buttons_html . '</div>';
	}
}
