<?php
/**
 * Event Image block: settings, scoped CSS and markup.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

class ELB_Event_Image_Renderer extends ELB_Field_Block_Renderer { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	const PREFIX = 'elb-img-';

	// Preset border-radius (applied on <img>, not the wrapper) so the shape does not clip
	// sibling overlays (elb-meta--on-image). Other shapes are Pro-only — Free ships Default/Rounded.
	const RADIUS_PRESETS = [
		'rounded' => [ '8', 'px' ],
	];

	/**
	 * Featured image markup for one event.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param int                 $post_id    Event post ID.
	 * @return string Image HTML (may be empty).
	 */
	protected static function render_field( array $attributes, int $post_id ): string {
		$source = self::source_field( $attributes, 'image' );
		// Non-image source: the field value as text.
		if ( 'image' !== $source ) {
			return '<div ' . get_block_wrapper_attributes( [ 'class' => 'elb-event-image-text' ] ) . '>'
				. esc_html( ELB_Event_Formatter::get_event_field_value( $post_id, $source ) ) . '</div>';
		}
		[ $is_link, $new_tab, $permalink ] = ELB_Event_Link::link_settings( $attributes, $post_id );
		return self::render_image( $attributes, $post_id, (bool) $is_link, (bool) $new_tab, (string) $permalink );
	}

	/**
	 * Registered intermediate image sizes, read once per request.
	 *
	 * @return string[]
	 */
	private static function intermediate_image_sizes(): array {
		static $sizes = null;
		$sizes ??= get_intermediate_image_sizes();
		return $sizes;
	}

	/**
	 * Featured image markup for an event.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param int                 $post_id    Event post ID.
	 * @param bool                $is_link    Whether the image links to the event.
	 * @param bool                $new_tab    Whether the link opens in a new tab.
	 * @param string              $permalink  Link URL.
	 */
	private static function render_image( array $attributes, int $post_id, bool $is_link, bool $new_tab, string $permalink ): string {
		$attr_hash = self::attr_hash( $attributes );

		// Attribute-only settings and wrapper style string, shared by every card of this block.
		[ $image_settings, $wrapper_style ] = self::memo(
			'settings:' . $attr_hash,
			static function () use ( $attributes ): array {
				$image_settings = self::settings( $attributes );
				$styles         = self::wrapper_decls( $attributes, $image_settings );
				return [ $image_settings, implode( ';', array_filter( $styles ) ) ];
			}
		);

		// Blocks with the same settings share one class, so the scoped rules are printed once.
		$resp_class = self::design(
			$attributes,
			static function ( string $resp_class ) use ( $attributes, $image_settings ): string {
				return (string) self::scoped_css( $attributes, $image_settings, $resp_class );
			},
			$attr_hash
		);

		$img = self::image_html( $attributes, $image_settings, $post_id );

		// Nothing to show: render nothing instead of an empty wrapper.
		if ( '' === trim( (string) $img ) ) {
			return '';
		}

		$wrapper_attrs = get_block_wrapper_attributes( [
			'class' => 'elb-event-image ' . $resp_class,
			'style' => $wrapper_style,
		] );

		if ( ! $is_link ) {
			return '<div ' . $wrapper_attrs . '>' . $img . '</div>';
		}
		$target = $new_tab ? ' target="_blank" rel="noopener noreferrer"' : '';
		// The link's accessible name comes from the image alt text.
		return '<div ' . $wrapper_attrs . '>'
			. '<a href="' . esc_url( $permalink ) . '"' . $target . ' style="display:block;width:100%;height:auto;">'
			. $img
			. '</a></div>';
	}

	/**
	 * Block attributes read into the values the rest of the class needs.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @return array<string,mixed>
	 */
	private static function settings( array $attributes ): array {
		$raw_aspect_ratio = (string) $attributes['aspectRatio'];
		$image_style      = sanitize_key( $attributes['imageStyle'] );

		// Registered image size (Thumbnail / Medium / Large / Full …); unknown values fall back to Large.
		$image_size = sanitize_key( $attributes['imageSize'] );
		if ( 'full' !== $image_size && ! in_array( $image_size, self::intermediate_image_sizes(), true ) ) {
			$image_size = 'large';
		}
		// Hover transition length in ms (100–1000). Unset keeps each effect's original timing.
		$hover_ms = (int) $attributes['hoverDuration'];
		$hover_ms = $hover_ms > 0 ? min( 1000, max( 100, $hover_ms ) ) : 0;

		$hover_effect = sanitize_key( $attributes['hoverEffect'] );

		return [
			'aspect_ratio'       => preg_match( '#^(auto|\d+(\.\d+)?\s*/\s*\d+(\.\d+)?)$#', $raw_aspect_ratio ) ? $raw_aspect_ratio : '4/3',
			'image_style'        => $image_style,
			'auto_height'        => ! isset( $attributes['autoHeight'] ) || ! empty( $attributes['autoHeight'] ),
			'hover_effect'       => $hover_effect,
			'zoom_hover'         => 'zoom-in' === $hover_effect,
			'image_size'         => $image_size,
			'hover_ms'           => $hover_ms,
			'border_style'       => sanitize_key( $attributes['borderStyle'] ),
			'border_color'       => $attributes['borderColor'],
			'border_color_hover' => ELB_Css_Utils::sanitize_css_color( $attributes['borderColorHover'] ),
			'border_width'       => $attributes['borderWidth'],
		];
	}

	/**
	 * Inline style declarations for the wrapper.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param array<string,mixed> $image_settings          Result of settings().
	 * @return string[]
	 */
	private static function wrapper_decls( array $attributes, array $image_settings ): array {
		// Full column width (list cards don't stagger per image size), flush left. Image width and
		// alignment controls are Pro-only.
		$styles = [ 'width:100%', 'max-width:100%', 'box-sizing:border-box', 'margin-left:0', 'margin-right:0' ];

		// Hover border color token on the wrapper; border itself lives on <img> (matches radius/clip).
		$hover_border = $image_settings['border_color_hover'];
		if ( $hover_border ) {
			$styles[] = '--elb-img-border-hover:' . esc_attr( $hover_border );
		}

		// Zoom hover needs a clipping box on the wrapper; shape radius/clip live on <img>.
		if ( $image_settings['zoom_hover'] ) {
			$styles[] = 'overflow:hidden';
		}

		return $styles;
	}

	/**
	 * Scoped CSS: hover border and effects, margins.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param array<string,mixed> $image_settings          Result of settings().
	 * @param string              $resp_class Shared design class.
	 */
	private static function scoped_css( array $attributes, array $image_settings, string $resp_class ): string {
		$extra_css  = self::hover_css( $image_settings, $resp_class );
		$extra_css .= self::margin_override_css( $attributes, $resp_class );

		return $extra_css;
	}

	/**
	 * Hover border colour + hover effect rules.
	 *
	 * @param array<string,mixed> $image_settings Result of settings().
	 * @param string              $resp_class     Shared design class.
	 */
	private static function hover_css( array $image_settings, string $resp_class ): string {
		$extra_css = '';

		// Hover border color on the media element (border is on <img>, not the wrapper).
		// The hover colour itself comes from --elb-img-border-hover (wrapper_decls) via frontend.css.
		if ( $image_settings['border_color_hover'] ) {
			$extra_css .= '.' . $resp_class . ' img{transition:border-color .2s ease;}';
		}

		$extra_css .= self::hover_effect_css( $image_settings, $resp_class );

		return $extra_css;
	}

	/**
	 * Spacing → Margin overrides (and the full-height media column height fix).
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param string              $resp_class Shared design class.
	 */
	private static function margin_override_css( array $attributes, string $resp_class ): string {
		$extra_css = ELB_Breakpoints::rule( '.' . $resp_class . ':not(#elb-img-margin)', self::margin_decls( $attributes ) );

		// Full-height media columns (List 1) stretch the image to 100%; a top/bottom margin
		// would push it past the clipped column, so shrink the height by the vertical margin.
		$sides  = ELB_Spacing_Styles::margin_sides( $attributes );
		$t      = $sides['top'] ?? '0px';
		$b      = $sides['bottom'] ?? '0px';
		$fill_h = ( '0px' === $t && '0px' === $b )
			? []
			: [ 'height:calc(100% - ' . $t . ' - ' . $b . ') !important', 'min-height:0 !important' ];
		$extra_css .= ELB_Breakpoints::rule(
			'.elb-card__media-col .' . $resp_class . ':not(#elb-img-margin)',
			$fill_h
		);

		return $extra_css;
	}

	/**
	 * Hover effect rules on <img>. Without an explicit duration each effect keeps its original
	 * timing, so existing blocks render as before.
	 *
	 * @param array<string,mixed> $image_settings          Result of settings().
	 * @param string              $resp_class Shared design class.
	 */
	private static function hover_effect_css( array $image_settings, string $resp_class ): string {
		$hover_ms   = $image_settings['hover_ms'];
		$hover_time = static function ( $legacy ) use ( $hover_ms ) {
			return $hover_ms ? $hover_ms . 'ms' : $legacy;
		};
		$c           = '.' . $resp_class;
		$hover_rules = [
			'zoom-in' => $c . ' img{transition:transform ' . $hover_time( '.4s' ) . ' ease;}' . $c . ':hover img{transform:scale(1.1);}',
		];
		if (
			! $image_settings['hover_effect']
			|| ! isset( $hover_rules[ $image_settings['hover_effect'] ] )
		) {
			return '';
		}
		$css = $hover_rules[ $image_settings['hover_effect'] ];
		// Reduced motion: no animated transition or movement.
		return $css . '@media (prefers-reduced-motion:reduce){' . $c . ' img{transition:none !important;}' . $c . ':hover img{transform:none !important;}}';
	}

	/**
	 * Border on the media element so it follows border-radius / clip-path (not a square wrapper box).
	 *
	 * @param array<string,mixed> $image_settings Resolved image settings.
	 */
	private static function border_style( array $image_settings ): string {
		if ( ! $image_settings['border_style'] || 'none' === $image_settings['border_style'] ) {
			return '';
		}
		$style  = 'border-style:' . esc_attr( $image_settings['border_style'] ) . ';';
		$style .= 'border-width:' . ( $image_settings['border_width'] !== '' ? (int) $image_settings['border_width'] : 1 ) . 'px;';
		$safe_border_color = ELB_Css_Utils::sanitize_css_color( $image_settings['border_color'] );
		if ( $safe_border_color ) {
			$style .= 'border-color:' . esc_attr( $safe_border_color ) . ';';
		}
		return $style . 'box-sizing:border-box;';
	}

	/**
	 * Inline style of the <img>: size, fit, focal point, shape, border.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param array<string,mixed> $image_settings          Result of settings().
	 */
	private static function image_style( array $attributes, array $image_settings ): string {
		$img_style = 'width:100%;display:block;height:auto;';
		if ( $image_settings['auto_height'] && 'auto' !== $image_settings['aspect_ratio'] ) {
			$img_style .= 'aspect-ratio:' . esc_attr( $image_settings['aspect_ratio'] ) . ';';
		}
		$img_style .= 'object-fit:cover;';

		// Shape presets — on the media element so sibling overlays stay unclipped. A shape
		// outside Free's allow-list (e.g. a legacy value from the Pro plugin) falls back to
		// the plain "default" look rather than erroring.
		$image_style  = isset( self::RADIUS_PRESETS[ $image_settings['image_style'] ] ) || 'default' === $image_settings['image_style']
			? $image_settings['image_style']
			: 'default';
		$radius_attrs = $attributes;
		if ( isset( self::RADIUS_PRESETS[ $image_style ] ) && ! ELB_Field_Styles::has_border_radius( $attributes ) ) {
			$preset_r = self::RADIUS_PRESETS[ $image_style ][0];
			$radius_attrs['borderRadiusTopLeft']     = $preset_r;
			$radius_attrs['borderRadiusTopRight']    = $preset_r;
			$radius_attrs['borderRadiusBottomRight'] = $preset_r;
			$radius_attrs['borderRadiusBottomLeft']  = $preset_r;
			$radius_attrs['borderRadiusUnit']        = self::RADIUS_PRESETS[ $image_style ][1];
		}
		$radius_decl = ELB_Field_Styles::border_radius_decl( $radius_attrs );
		if ( $radius_decl ) {
			$img_style .= $radius_decl . ';';
		}

		$img_style .= self::border_style( $image_settings );

		return $img_style;
	}

	/**
	 * The <img>: the featured image, else the site default placeholder.
	 * wp_get_attachment_image() outputs srcset, sizes, width / height and lazy loading; alt is the
	 * media-library alt text, falling back to the event title.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param array<string,mixed> $image_settings          Result of settings().
	 * @param int                 $post_id    Event post id.
	 */
	private static function image_html( array $attributes, array $image_settings, int $post_id ): string {
		$img_attrs = [
			'class'   => 'elb-event-img',
			'loading' => 'lazy',
			'style'   => self::image_style( $attributes, $image_settings ),
			'sizes'   => '(max-width: 600px) 100vw, 28vw',
		];

		$attachment_id = (int) get_post_thumbnail_id( $post_id );

		if ( $attachment_id && wp_attachment_is_image( $attachment_id ) ) {
			// Attachment meta is already in the object cache (primed per query).
			$alt = ELB_Event_Repository::image_alt( $attachment_id, get_the_title( $post_id ) );
			return (string) wp_get_attachment_image( $attachment_id, $image_settings['image_size'], false, $img_attrs + [ 'alt' => $alt ] );
		}

		// No featured or fallback image: keep the site-wide default placeholder if one is configured.
		return (string) ELB_Event_Markup::placeholder_image( $post_id, $img_attrs );
	}

	/**
	 * Margin rules for Event Image; side margins also shrink the image so it stays inside its column.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @return string[]
	 */
	private static function margin_decls( array $attributes ): array {
		$sides = ELB_Spacing_Styles::margin_sides( $attributes );
		$rules = [];
		foreach ( $sides as $side => $val ) {
			$rules[] = 'margin-' . $side . ':' . $val . ' !important';
		}
		if ( isset( $sides['left'] ) || isset( $sides['right'] ) ) {
			$inset   = 'calc(100% - ' . ( $sides['left'] ?? '0px' ) . ' - ' . ( $sides['right'] ?? '0px' ) . ')';
			$rules[] = 'max-width:' . $inset . ' !important';
			$rules[] = 'width:' . $inset . ' !important';
		}
		return $rules;
	}
}
