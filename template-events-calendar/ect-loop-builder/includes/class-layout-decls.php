<?php
/**
 * Layout declarations for field blocks: text-align layout, flex, row layout and item order.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Layout_Decls { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/**
	 * Alignment declarations for field wrappers.
	 *
	 * @param array $attributes Block attributes.
	 * @param bool  $stretch    When true, force width:100% so alignment is visible in flex columns.
	 * @return array
	 */
	public static function text_align_layout_decls( array $attributes, $stretch = true ): array {
		$ta = ELB_Css_Utils::sanitize_text_align( $attributes['textAlign'] ?? '' );
		if ( ! $ta ) {
			return [];
		}
		// Shrink-to-fit (date badge): justify-content needs a full-width wrapper
		// to show. Use align-self + auto margins so the compact badge can move.
		// Inline margins also beat flex-container's margin-inline:0 on children.
		if ( ! $stretch ) {
			if ( 'justify' === $ta ) {
				return [
					'text-align:justify',
					...ELB_Field_Styles::FULL_WIDTH_DECLS,
					'display:flex',
					'justify-content:space-between',
					'align-self:stretch',
				];
			}
			$decls = [
				'text-align:' . $ta,
				'align-self:' . ELB_Css_Utils::align_to_flex( $ta ),
			];
			if ( 'center' === $ta ) {
				$decls[] = 'margin-left:auto';
				$decls[] = 'margin-right:auto';
			} elseif ( 'right' === $ta ) {
				$decls[] = 'margin-left:auto';
			}
			return $decls;
		}

		return [
			'text-align:' . $ta,
			'justify-content:' . ELB_Css_Utils::align_to_flex( $ta ),
			...ELB_Field_Styles::FULL_WIDTH_DECLS,
			'display:flex',
		];
	}

	/**
	 * Flex declarations from flexDirection / justifyContent / alignItems / flexWrap / flexGap,
	 * with per-caller defaults for unset attributes.
	 *
	 * @param array                $attributes Block attributes.
	 * @param array<string,string> $defaults   display, direction, justify, align, wrap, gap (CSS value).
	 *                                         Without 'display', display:flex is only emitted when flexDirection is set.
	 * @return string[]
	 */
	public static function flex_decls( $attributes, array $defaults = [] ): array {
		$dir_attr  = sanitize_key( $attributes['flexDirection'] ?? '' );
		$display   = array_key_exists( 'display', $defaults ) ? (string) $defaults['display'] : ( $dir_attr ? 'flex' : '' );
		$direction = $dir_attr ?: ( $display ? (string) ( $defaults['direction'] ?? '' ) : '' );
		$values    = [
			'display'         => $display,
			'flex-direction'  => $direction,
			'justify-content' => sanitize_key( $attributes['justifyContent'] ?? '' ) ?: (string) ( $defaults['justify'] ?? '' ),
			'align-items'     => sanitize_key( $attributes['alignItems'] ?? '' ) ?: (string) ( $defaults['align'] ?? '' ),
			'flex-wrap'       => sanitize_key( $attributes['flexWrap'] ?? '' ) ?: (string) ( $defaults['wrap'] ?? '' ),
			'gap'             => self::flex_gap( $attributes, (string) ( $defaults['gap'] ?? '' ) ),
		];
		$decls = [];
		foreach ( $values as $prop => $value ) {
			if ( '' !== $value ) {
				$decls[] = $prop . ':' . $value;
			}
		}
		return $decls;
	}

	/**
	 * The flexGap attribute as a px value, or $fallback when unset.
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $fallback   CSS value used when flexGap is empty.
	 */
	public static function flex_gap( $attributes, string $fallback = '' ): string {
		$gap = $attributes['flexGap'] ?? '';
		return '' !== $gap && null !== $gap ? (float) $gap . 'px' : $fallback;
	}

	/**
	 * Flex-only styles for a row wrapper (time+category, button+cost).
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public static function row_layout_style( $attributes ) {
		$ta       = ELB_Css_Utils::sanitize_text_align( $attributes['textAlign'] ?? '' );
		$defaults = $ta ? [
			'display' => 'flex',
			'justify' => ELB_Css_Utils::align_to_flex( $ta ),
			'wrap'    => 'wrap',
			'align'   => 'center',
		] : [];
		$s = self::flex_decls( $attributes, $defaults );
		if ( $ta ) {
			$s[] = 'text-align:' . $ta;
		}
		// Full width either way so a Clean Image Card parent (align-items:center)
		// does not shrink-wrap meta into a centered cluster.
		array_push( $s, ...ELB_Field_Styles::FULL_WIDTH_DECLS );
		return implode( ';', array_filter( $s ) );
	}

	/**
	 * Whether the user set arrangement (flex/order) so template CSS must yield.
	 *
	 * @param array  $attributes    Block attributes.
	 * @param string $default_order Default itemOrder string.
	 * @return bool
	 */
	public static function has_custom_row_layout( $attributes, $default_order = '' ) {
		if ( ! empty( $attributes['layoutUnlocked'] ) ) {
			return true;
		}
		foreach ( [ 'flexDirection', 'justifyContent', 'alignItems', 'flexWrap' ] as $key ) {
			if ( '' !== (string) ( $attributes[ $key ] ?? '' ) ) {
				return true;
			}
		}
		$ta = sanitize_key( $attributes['textAlign'] ?? '' );
		if ( in_array( $ta, [ 'center', 'right', 'justify' ], true ) ) {
			return true;
		}
		$order = preg_replace( '/\s+/', '', (string) ( $attributes['itemOrder'] ?? '' ) );
		if ( $default_order && $order && $order !== $default_order ) {
			return true;
		}
		return false;
	}

	/**
	 * Sanitize and complete a comma-separated item order.
	 *
	 * @param string   $order    Saved order.
	 * @param string[] $allowed  Allowed keys.
	 * @param string[] $fallback Default order.
	 * @return string[]
	 */
	public static function parse_item_order( $order, $allowed, $fallback ) {
		$parts = array_values(
			array_filter(
				array_map( 'sanitize_key', explode( ',', (string) $order ) )
			)
		);
		$parts = array_values( array_intersect( $parts, $allowed ) );
		foreach ( $fallback as $key ) {
			if ( ! in_array( $key, $parts, true ) ) {
				$parts[] = $key;
			}
		}
		return array_values( array_intersect( $parts, $allowed ) );
	}
}
