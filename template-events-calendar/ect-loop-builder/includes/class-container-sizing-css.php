<?php
/**
 * Frontend CSS for nested flex container sizing and loop section container type.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Container_Sizing_Css { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/**
	 * Frontend CSS for nested (component-parent) flex containers' Custom Width
	 * and Minimum Height. Walks the loop tree so card inner containers match
	 * the editor even when layout CSS or stale elbFrontendCss would win.
	 *
	 * @param array<int,array<string,mixed>> $blocks Parsed blocks.
	 * @return string CSS.
	 */
	public static function nested_containers_sizing_css( array $blocks ): string {
		$css = '';
		foreach ( $blocks as $block ) {
			if ( ! is_array( $block ) ) {
				continue;
			}
			if ( ( $block['blockName'] ?? '' ) === 'elb/flex-container' ) {
				$css .= self::one_nested_container_sizing_css( ELB_Block_Attrs::of( $block ) );
			}
			if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
				$css .= self::nested_containers_sizing_css( $block['innerBlocks'] );
			}
		}
		return $css;
	}

	/**
	 * Compute sizing CSS for one nested container.
	 *
	 * @param array<string,mixed> $attrs Block attributes.
	 * @return string
	 */
	public static function one_nested_container_sizing_css( array $attrs ): string {
		// Section root is handled by section_container_type_css().
		if ( ! empty( $attrs['isBlockRootParent'] ) ) {
			return '';
		}

		$id = ELB_Container_Selectors::block_id( $attrs );
		if ( '' === $id ) {
			return '';
		}

		$sel = ELB_Container_Selectors::block_selector( $id );

		$css = self::nested_width_css( $sel, $attrs ) . self::min_height_css( $sel, $attrs, true );
		// A user Min height: Read More follows the content as in the editor instead of being pushed to the bottom (margin-top:auto).
		if ( (float) ( $attrs['minHeightDesktop'] ?? 0 ) > 0 ) {
			$css .= $sel . ' .wp-block-elb-event-read-more{margin-top:0;}';
		}
		return $css;
	}

	/**
	 * Custom Width rules (desktop) for a nested container.
	 *
	 * @param string              $sel   Container selector.
	 * @param array<string,mixed> $attrs Container attributes.
	 * @return string
	 */
	public static function nested_width_css( string $sel, array $attrs ): string {
		$css = '';
		// Width values equal to the block.json default (Desktop 100) aren't serialized; the editor still applies them.
		if ( ! empty( $attrs['widthSetByUser'] ) ) {
			$attrs += [ 'widthDesktop' => 100 ];
		}
		if ( ! isset( $attrs['widthDesktop'] ) || ! is_numeric( $attrs['widthDesktop'] ) ) {
			return $css;
		}

		$w    = (float) $attrs['widthDesktop'];
		$unit = ELB_Css_Utils::safe_unit( $attrs['widthType'] ?? '%', '%' );
		$full = ( ( '%' === $unit || 'vw' === $unit ) && $w >= 100.0 );
		$exact_fluid = self::has_exact_list_width( $attrs );
		// Skip default full-width so list body can keep flex-grow:1 unless the user set it.
		if ( ! $full || ! empty( $attrs['widthSetByUser'] ) ) {
			$css .= $sel . '{' . self::width_decls( $w, $unit, $exact_fluid ) . '}';
		}

		return $css;
	}

	/**
	 * Width declarations for percent/vw (max-width cap) vs fixed units (hard width).
	 *
	 * @param float  $width   Width number.
	 * @param string $unit        CSS unit (%, px, vw, etc.).
	 * @param bool   $exact_fluid Use the selected fluid size as the actual flex basis.
	 * @return string
	 */
	public static function width_decls( float $width, string $unit, bool $exact_fluid = false ): string {
		$size = $width . $unit;
		if ( in_array( $unit, [ '%', 'vw' ], true ) ) {
			if ( $exact_fluid ) {
				return 'max-width:' . $size . ';width:' . $size . ';flex-grow:0;flex-shrink:1;flex-basis:' . $size . ';min-width:0;box-sizing:border-box;';
			}
			return 'max-width:' . $size . ';width:auto;flex-grow:0;flex-shrink:1;flex-basis:auto;min-width:0;box-sizing:border-box;';
		}
		return 'flex-grow:0;flex-shrink:0;flex-basis:auto;width:' . $size . ';max-width:' . $size . ';box-sizing:border-box;';
	}

	/** Whether a Monthly Agenda / Minimal List column has an explicit Width control value (applied exactly, as in the editor). */
	private static function has_exact_list_width( array $attrs ): bool {
		return ! empty( $attrs['widthSetByUser'] )
			&& preg_match( '/\belb-card__(date-col|body|media-col)--list-[34]\b/', (string) ( $attrs['className'] ?? '' ) );
	}

	/**
	 * Minimum Height rules (desktop) for a container.
	 *
	 * @param string              $sel               Container selector.
	 * @param array<string,mixed> $attrs             Container attributes.
	 * @param bool                $positive_only     Skip heights of 0 or less (nested containers).
	 * @return string
	 */
	public static function min_height_css( string $sel, array $attrs, bool $positive_only ): string {
		$rule = static function ( $height, $unit ) use ( $sel, $positive_only ) {
			if ( ! is_numeric( $height ) || ( $positive_only && (float) $height <= 0 ) ) {
				return '';
			}
			return $sel . '{min-height:' . (float) $height . ELB_Css_Utils::safe_unit( $unit, 'px' ) . ';}';
		};

		return $rule( $attrs['minHeightDesktop'] ?? null, $attrs['minHeightType'] ?? 'px' );
	}

	/**
	 * Frontend CSS for the loop section Container Type controls
	 * (Container Width, Minimum Height).
	 *
	 * @param array<string,mixed> $attrs Section flex-container attributes.
	 * @return string
	 */
	public static function section_container_type_css( array $attrs ): string {
		$id = ELB_Container_Selectors::block_id( $attrs );
		if ( '' === $id ) {
			return '';
		}

		$sel = ELB_Container_Selectors::block_selector( $id );
		$css = '';

		$content_width = (string) ( $attrs['contentWidth'] ?? 'alignfull' );
		if ( 'alignwide' === $content_width ) {
			$css .= $sel . '{max-width:var(--wp--style--global--wide-size,var(--wp--style--global--content-size,1200px));width:100%;margin-left:auto;margin-right:auto;}';
		} elseif ( 'default' === $content_width ) {
			$width = isset( $attrs['widthDesktop'] ) && is_numeric( $attrs['widthDesktop'] )
				? (float) $attrs['widthDesktop']
				: 100.0;
			$unit  = ELB_Css_Utils::safe_unit( $attrs['widthType'] ?? '%', '%' );
			$css  .= $sel . '{max-width:' . $width . $unit . ';width:100%;margin-left:auto;margin-right:auto;}';
		} else {
			$css .= $sel . '{max-width:100%;width:100%;}';
		}

		$css .= self::min_height_css( $sel, $attrs, false );

		return $css;
	}

	/**
	 * Remove the automatic 10px padding on the loop's outer container.
	 *
	 * Each side is cleared only while it is still the default 10. A custom value stays.
	 *
	 * @param array<string,mixed> $attrs
	 * @return string
	 */
	public static function root_default_padding_reset( array $attrs ): string {
		if ( empty( $attrs['isBlockRootParent'] ) ) {
			return '';
		}

		$sides = array(
			'top'    => 'topPaddingDesktop',
			'right'  => 'rightPaddingDesktop',
			'bottom' => 'bottomPaddingDesktop',
			'left'   => 'leftPaddingDesktop',
		);
		$rules = array();
		foreach ( $sides as $side => $key ) {
			$value = $attrs[ $key ] ?? 10;
			if ( is_numeric( $value ) && 10.0 === (float) $value ) {
				$rules[] = 'padding-' . $side . ':0';
			}
		}
		if ( ! $rules ) {
			return '';
		}

		$id = ELB_Container_Selectors::block_id( $attrs );
		if ( '' === $id ) {
			return '';
		}

		// Beats the saved `:is(.wp-block-elb-flex-container.elb-block-id)` rule.
		return ELB_Container_Selectors::block_selector( $id ) . '{' . implode( ';', $rules ) . ';}';
	}
}
