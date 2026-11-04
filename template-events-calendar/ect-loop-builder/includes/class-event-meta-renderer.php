<?php
/**
 * Event Meta — flexible items (category, time, date, venue, price).
 *
 * Each item type is registered in self::types() (one ELB_Meta_Item class: default icon + render); the
 * editor mirrors this in source/loop/meta-items/registry.js. Output is <ul class="elb-meta"> with one
 * <li> per item; separators are drawn in CSS, so hidden / empty items leave no stray separators.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

class ELB_Event_Meta_Renderer extends ELB_Field_Block_Renderer { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	const PREFIX       = 'elb-meta-';
	const BADGE_STYLES = [ 'solid', 'text' ];
	/** Free separators (Pipe / Dash / Slash and "Wrap items" off are Pro-only). */
	const SEPARATORS   = [ 'none', 'dot' ];

	/**
	 * Registered item types.
	 *
	 * @return array<string,class-string<ELB_Meta_Item>> UI labels live in the editor's JS registry.
	 */
	public static function types(): array {
		static $types = null;
		return $types ??= [
			'category' => ELB_Meta_Item_Category::class,
			'time'     => ELB_Meta_Item_Time::class,
			'date'     => ELB_Meta_Item_Date::class,
			'venue'    => ELB_Meta_Item_Venue::class,
			'price'    => ELB_Meta_Item_Price::class,
		];
	}

	// ── Block ────────────────────────────────────────────────────────────────

	/**
	 * Render meta items list for one event card.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param int                 $post_id    Event post ID.
	 * @return string HTML list markup (may be empty).
	 */
	protected static function render_field( array $attributes, int $post_id ): string {
		$items = is_array( $attributes['items'] ?? null ) ? $attributes['items'] : [];
		$lis   = self::render_items( $items, $post_id, $attributes );
		if ( '' === $lis ) {
			return '';
		}

		$overlay_on_image = self::overlay_on_image( $items, $attributes );
		// Items don't affect the design CSS or the wrapper class/style (only the overlay flag
		// does), so one hash — attributes minus the per-card items, plus that flag — keys both
		// caches instead of hashing $attributes a second time for the memoized wrapper build.
		$design_key = self::build_design_key( $attributes, $overlay_on_image );
		$attr_hash  = self::attr_hash( $design_key );

		$class = self::design(
			$design_key,
			static function ( string $class ) use ( $attributes, $overlay_on_image ): string {
				return self::design_css( $attributes, $class, $overlay_on_image );
			},
			$attr_hash
		);

		$wrapper = get_block_wrapper_attributes( self::wrapper_args( $attributes, $class, $overlay_on_image, $attr_hash ) );

		return '<ul ' . $wrapper . '>' . $lis . '</ul>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Concatenated <li> markup for every visible item.
	 *
	 * @param array<int,mixed>    $items      Block items.
	 * @param int                 $post_id    Event post ID.
	 * @param array<string,mixed> $attributes Block attributes.
	 */
	private static function render_items( array $items, int $post_id, array $attributes ): string {
		$lis = '';
		foreach ( $items as $item ) {
			if ( is_array( $item ) ) {
				$lis .= self::render_item( $item, $post_id, $attributes );
			}
		}
		return $lis;
	}

	/**
	 * Design-class cache key: attributes minus the per-card items (which don't affect the
	 * queued CSS), plus the overlay flag that does — so blocks differing only in items share a
	 * class, while blocks differing in overlay state get separate ones.
	 *
	 * @param array<string,mixed> $attributes       Block attributes.
	 * @param bool                $overlay_on_image Whether the overlay-on-image layout is on.
	 * @return array<string,mixed>
	 */
	private static function build_design_key( array $attributes, bool $overlay_on_image ): array {
		$design = $attributes;
		unset( $design['items'] );
		$design['_overlayActive'] = $overlay_on_image;
		return $design;
	}

	/**
	 * Scoped CSS queued for the shared design class.
	 *
	 * @param array<string,mixed> $attributes       Block attributes.
	 * @param string              $class            Shared design class.
	 * @param bool                $overlay_on_image Whether the overlay-on-image layout is on.
	 */
	private static function design_css( array $attributes, string $class, bool $overlay_on_image ): string {
		$css = ELB_Meta_Styles::responsive_css( $attributes, $class );
		if ( $overlay_on_image ) {
			$css .= ELB_Overlay_Css::overlay_on_image_css(
				$class,
				[
					'extra_parent_sels' => [ '.elb-card__media-col' ],
				]
			);
		}
		return $css;
	}

	/**
	 * Attribute-only wrapper class and style strings, shared by every card of this block.
	 *
	 * @param array<string,mixed> $attributes       Block attributes.
	 * @param string              $class            Shared design class.
	 * @param bool                $overlay_on_image Whether the overlay-on-image layout is on.
	 * @param string              $attr_hash        Precomputed design-key hash (see build_design_key()).
	 * @return array{class:string,style:string}
	 */
	private static function wrapper_args( array $attributes, string $class, bool $overlay_on_image, string $attr_hash ): array {
		return self::memo(
			'styles:' . $attr_hash,
			static function () use ( $attributes, $class, $overlay_on_image ): array {
				$style_decls = ELB_Meta_Styles::style_decls( $attributes );
				if ( $overlay_on_image ) {
					$style_decls = self::overlay_style_decls( $style_decls );
				}
				return [
					'class' => self::wrapper_class( $attributes, $class, $overlay_on_image ),
					'style' => implode( ';', $style_decls ),
				];
			}
		);
	}

	/**
	 * "Overlay on image" only makes sense for a category badge sitting on the card image —
	 * same restriction the legacy single-source render already applies.
	 *
	 * @param array<int,mixed>    $items      Block items.
	 * @param array<string,mixed> $attributes Block attributes.
	 */
	private static function overlay_on_image( array $items, array $attributes ): bool {
		if ( empty( $attributes['overlayOnImage'] ) ) {
			return false;
		}
		foreach ( $items as $item ) {
			if ( is_array( $item ) && 'category' === sanitize_key( $item['type'] ?? '' ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Overlay positioning declarations, with the field gutter padding removed.
	 *
	 * @param string[] $style_decls Declarations before the overlay is applied.
	 * @return string[]
	 */
	private static function overlay_style_decls( array $style_decls ): array {
		array_push( $style_decls, ...ELB_Overlay_Css::overlay_on_image_style_decls( 10, 'calc(100% - 24px)' ) );
		// Overlay badges sit on the image — drop field gutter padding.
		$style_decls   = ELB_Css_Utils::without_props( $style_decls, [ 'padding:', '--elb-field-padding:' ] );
		$style_decls[] = 'padding:0';
		return $style_decls;
	}

	/**
	 * Class list for the list wrapper: separator, alignment, overlay and the shared design class.
	 *
	 * @param array<string,mixed> $attributes       Block attributes.
	 * @param string              $resp_class       Shared design class.
	 * @param bool                $overlay_on_image Whether the overlay-on-image layout is on.
	 */
	private static function wrapper_class( array $attributes, string $resp_class, bool $overlay_on_image ): string {
		$align = ELB_Css_Utils::safe_keyword( sanitize_key( $attributes['textAlign'] ), ELB_Css_Utils::BOX_ALIGNS, 'left' );
		$sep   = ELB_Css_Utils::safe_keyword( sanitize_key( $attributes['metaSeparator'] ), self::SEPARATORS, 'dot' );

		return trim(
			'elb-meta elb-meta--sep-' . $sep . ' elb-meta--align-' . $align
			. ( $overlay_on_image ? ' elb-meta--on-image' : '' ) . ' ' . $resp_class
		);
	}

	/**
	 * One <li>, or '' when the item is hidden or has no value.
	 *
	 * @param array<string,mixed> $item       Item { id, type, visible, icon, settings }.
	 * @param int                 $post_id    Event ID.
	 * @param array<string,mixed> $attributes Block attributes.
	 * @return string List-item HTML or empty string.
	 */
	public static function render_item( array $item, int $post_id, array $attributes ): string {
		$types = self::types();
		$type  = sanitize_key( $item['type'] ?? '' );
		if ( ! isset( $types[ $type ] ) || empty( $item['visible'] ) ) {
			return '';
		}
		$settings = is_array( $item['settings'] ?? null ) ? $item['settings'] : [];
		// iconHtml is only ever meant to be markup WE generated below (icon_html()). Clear
		// whatever the saved item array carries first, so an attacker-controlled items[] entry
		// (free-form block attribute) can never reach ELB_Meta_Item::badge_icon()'s raw output.
		unset( $item['iconHtml'] );
		// Style: the item's own override, else the block's "Look · All items" style.
		$item['badgeStyle'] = '' !== (string) ( $item['badgeStyle'] ?? '' ) ? $item['badgeStyle'] : ( $attributes['badgeStyle'] );
		// Icon: the item's pick, its type default when empty, nothing when 'none'.
		$icon_html = self::icon_html( (string) ( $item['icon'] ?? '' ), $types[ $type ]::ICON );
		// Preset badges contain their icon: the renderers put it inside every badge they open.
		if ( '' !== $icon_html && 'text' !== ELB_Meta_Item::badge_style( $item ) ) {
			$item['iconHtml'] = $icon_html;
			$icon_html        = '';
		}
		// Every renderer gets $item too, so it can read this item's own Style (badgeStyle / colours).
		$inner = (string) $types[ $type ]::render( $post_id, $settings, $item );
		if ( '' === trim( $inner ) ) {
			return '';
		}
		// "Prefix" (More options) is generic — every item type gets it the same way.
		$prefix = trim( (string) ( $settings['prefix'] ?? '' ) );
		if ( '' !== $prefix ) {
			$inner = '<span class="elb-meta__prefix">' . esc_html( $prefix ) . ' </span>' . $inner;
		}
		// Item position overrides the legacy block-wide setting.
		$after = 'after' === ( $item['iconPosition'] ?? $attributes['iconPosition'] );
		return '<li class="elb-meta__item elb-meta__item--' . esc_attr( $type ) . ( $after ? ' elb-meta__item--icon-after' : '' ) . '">'
			. ( $after ? $inner . $icon_html : $icon_html . $inner ) . '</li>';
	}

	// ── Icons ────────────────────────────────────────────────────────────────

	private static function icon_html( string $icon, string $fallback ): string {
		if ( 'none' === $icon ) {
			return '';
		}
		$name = '' !== $icon ? $icon : $fallback;
		if ( '' === $name ) {
			return '';
		}
		// Same icon on every card: build its markup once per request.
		static $cache = [];
		if ( isset( $cache[ $name ] ) ) {
			return $cache[ $name ];
		}
		$svg = ELB_Icons::svg( $name );
		if ( '' === $svg ) {
			return $cache[ $name ] = '';
		}
		// Most icon SVGs already carry aria-hidden / focusable; add each only when missing so neither is duplicated.
		$add = ( preg_match( '/^<svg\b[^>]*\baria-hidden=/', $svg ) ? '' : 'aria-hidden="true" ' )
			. ( preg_match( '/^<svg\b[^>]*\bfocusable=/', $svg ) ? '' : 'focusable="false" ' );
		$svg = ELB_Icons::svg_with_attrs( $svg, $add );
		return $cache[ $name ] = '<span class="elb-meta__icon">' . $svg . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

}
