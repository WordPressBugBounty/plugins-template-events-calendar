<?php
/**
 * Frontend CSS for card layout, container margins, column stacking and wrapping.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Card_Layout_Css { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/** Minimum width (px) of a content-sized (auto) column such as the date column. */
	const AUTO_COL_MIN_PX = 56;

	/**
	 * Card layout CSS for the loop tree: user-set Spacing > Margin for every flex-container
	 * (card root, columns, nested containers, all four sides), the card root's
	 * column-stacking and wrap rules, and layout-specific rules (List 3 timeline, List 4 accent).
	 *
	 * @param array<string,mixed>|null $section Parsed loop section block.
	 * @return string CSS.
	 */
	public static function card_layout_css( $section ): string {
		if ( ! is_array( $section ) ) {
			return '';
		}
		$css = '';
		foreach ( ELB_Loop::resolve_card_templates( [ 'innerBlocks' => [ $section ] ] ) as $root ) {
			$css .= self::container_tree_css( $root, true );
			$css .= self::card_root_column_css( $root );
			$css .= self::card_root_wrap_css( $root );
		}
		return $css;
	}

	/**
	 * Layout-specific container CSS, keyed by the container class that selects it.
	 *
	 * @return array<string,callable>
	 */
	private static function layout_contributors(): array {
		static $contributors = null;
		if ( null === $contributors ) {
			$contributors = (array) apply_filters(
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Bundled Events Loop Builder hook.
				'template_events_calendar_elb_container_layout_css_contributors',
				[
					'elb-card__body--list-3'     => [ 'ELB_Layout_List_3', 'timeline_line_css' ],
					'elb-card__date-col--list-4' => [ 'ELB_Layout_List_4', 'accent_css' ],
				]
			);
		}
		return $contributors;
	}

	/**
	 * Margin and layout-specific CSS for a container and all its descendants.
	 *
	 * @param mixed $block
	 * @param bool  $is_card_root Whether $block is the card's root container.
	 * @return string
	 */
	public static function container_tree_css( $block, bool $is_card_root ): string {
		if ( ! is_array( $block ) ) {
			return '';
		}
		$css = '';
		if ( 'elb/flex-container' === ( $block['blockName'] ?? '' ) ) {
			$attrs = ELB_Block_Attrs::of( $block );
			$class = (string) ( $attrs['className'] ?? '' );
			$css  .= self::one_container_margin_css( $attrs, $is_card_root );
			foreach ( self::layout_contributors() as $marker => $callback ) {
				if ( false !== strpos( $class, $marker ) ) {
					$css .= (string) call_user_func( $callback, $attrs, ELB_Container_Selectors::block_id( $attrs ) );
				}
			}
		}
		foreach ( (array) ( $block['innerBlocks'] ?? [] ) as $child ) {
			$css .= self::container_tree_css( $child, false );
		}
		return $css;
	}

	/**
	 * Numeric margin values set on a container, per side.
	 *
	 * @param array<string,mixed> $attrs Container attributes.
	 * @return array<string,float> Side => value (only set sides).
	 */
	private static function collect_margin_sides( array $attrs ): array {
		$set = array();
		foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
			$v = $attrs[ $side . 'MarginDesktop' ] ?? null;
			if ( is_numeric( $v ) ) {
				$set[ $side ] = (float) $v;
			}
		}
		return $set;
	}

	/**
	 * Margin (and width compensation) declarations.
	 *
	 * @param array<string,mixed> $attrs        Container attributes.
	 * @param array<string,float> $sides        Side => value.
	 * @param bool                $is_card_root Whether this is the card root container.
	 * @return string[] Declarations.
	 */
	private static function margin_rules( array $attrs, array $sides, bool $is_card_root ): array {
		$unit = ELB_Css_Utils::safe_unit( $attrs['marginType'] ?? 'px' );
		$fmt   = static function ( float $v, string $zero ) use ( $unit ): string {
			return 0.0 === $v ? $zero : rtrim( rtrim( number_format( $v, 3, '.', '' ), '0' ), '.' ) . $unit;
		};
		$rules = array();
		foreach ( $sides as $side => $v ) {
			$rules[] = 'margin-' . $side . ':' . $fmt( $v, '0' ) . ' !important';
		}
		$has_x = ! empty( $sides['left'] ) || ! empty( $sides['right'] );
		if ( $is_card_root && $has_x ) {
			// Card root is width:100%; that would push the right margin out of view.
			$rules[] = 'width:auto';
			$rules[] = 'max-width:none';
		} elseif ( $has_x && 'default' !== (string) ( $attrs['contentWidth'] ?? 'alignfull' ) ) {
			// Full-width nested containers are width:100% (theme-compat .alignfull); shrink by the side margins.
			$rules[] = 'width:calc(100% - ' . $fmt( $sides['left'] ?? 0.0, '0px' ) . ' - ' . $fmt( $sides['right'] ?? 0.0, '0px' ) . ') !important';
			$rules[] = 'max-width:100% !important';
		}
		return $rules;
	}

	/**
	 * Compute margin rules for a container.
	 *
	 * @param array<string,mixed> $attrs
	 * @param bool                $is_card_root Whether these are the card root container's attributes.
	 * @return string
	 */
	public static function one_container_margin_css( array $attrs, bool $is_card_root ): string {
		$id = ELB_Container_Selectors::block_id( $attrs );
		if ( '' === $id || ! empty( $attrs['isBlockRootParent'] ) ) {
			return '';
		}
		$set = self::collect_margin_sides( $attrs );
		if ( ! $set ) {
			return '';
		}

		// Stock List templates shipped bottomMarginDesktop 20/32 that never rendered; keep those cards unchanged.
		$class = (string) ( $attrs['className'] ?? '' );
		if ( array( 'bottom' ) === array_keys( $set )
			&& in_array( $set['bottom'], array( 20.0, 32.0 ), true ) && false !== strpos( $class, 'elb-card--list-' ) ) {
			return '';
		}

		$sel   = ELB_Container_Selectors::boosted_selector( $id, 'margin' );
		$out   = '';
		$has_x = ! empty( $set['left'] ) || ! empty( $set['right'] );
		$out  .= $sel . '{' . implode( ';', self::margin_rules( $attrs, $set, $is_card_root ) ) . ';}';

		// List cards stack columns at <=1024px with width:100% !important; let side margins fit instead of overflowing.
		if ( $has_x && preg_match( '/\belb-card__(media-col|body)\b/', $class ) ) {
			$out .= ELB_Breakpoints::max( ELB_Breakpoints::TABLET_MAX, $sel . '{width:auto !important;max-width:100% !important;}' );
		}
		return $out;
	}

	/**
	 * Find list card root block id, accepting pre-resolved attributes if available.
	 *
	 * @param mixed                     $root  Parsed card root block.
	 * @param array<string,mixed>|null $attrs Pre-resolved attributes.
	 * @return string
	 */
	public static function list_card_root_id( $root, ?array $attrs = null ): string {
		if ( ! is_array( $root ) || 'elb/flex-container' !== ( $root['blockName'] ?? '' ) ) {
			return '';
		}
		$attrs = null !== $attrs ? $attrs : ELB_Block_Attrs::of( $root );
		if ( ! preg_match( '/\belb-card--list-[34]\b/', (string) ( $attrs['className'] ?? '' ) ) ) {
			return '';
		}
		return ELB_Container_Selectors::block_id( $attrs );
	}

	/**
	 * List 3 / 4 card root set to Direction = Column.
	 *
	 * @param mixed $root Card root block.
	 * @return string
	 */
	public static function card_root_column_css( $root ): string {
		$attrs = ELB_Block_Attrs::of( $root );
		$id    = self::list_card_root_id( $root, $attrs );
		if ( '' === $id ) {
			return '';
		}
		$desk = (string) ( $attrs['directionDesktop'] ?? 'column' );
		if ( 0 !== strpos( $desk, 'column' ) ) {
			return '';
		}

		$sel  = ELB_Container_Selectors::boosted_selector( $id, 'dir' );
		// A column the user gave a Width keeps it when stacked.
		$kids = ELB_Container_Selectors::child_selector( $sel, '.wp-block-elb-flex-container:not(.elb-user-width)' );
		return $kids . '{flex:0 0 auto !important;width:100% !important;max-width:100% !important;min-width:0 !important;align-self:stretch !important;}';
	}

	/**
	 * List 3/4 card root, Row + Wrap on (desktop).
	 *
	 * @param mixed $root Parsed card root block.
	 * @return string
	 */
	public static function card_root_wrap_css( $root ): string {
		$attrs = ELB_Block_Attrs::of( $root );
		$id    = self::list_card_root_id( $root, $attrs );
		if ( '' === $id
			|| 0 !== strpos( (string) ( $attrs['directionDesktop'] ?? 'column' ), 'row' )
			|| 0 !== strpos( (string) ( $attrs['wrapDesktop'] ?? '' ), 'wrap' ) ) {
			return '';
		}
		$gap = 'px' === (string) ( $attrs['columnGapType'] ?? 'px' ) ? (float) ( $attrs['columnGapDesktop'] ?? 20 ) : 0.0;
		$kids = array_values( array_filter( (array) ( $root['innerBlocks'] ?? [] ), static fn( $b ) => is_array( $b ) && 'elb/flex-container' === ( $b['blockName'] ?? '' ) ) );
		$cols = [];
		$sum  = 0.0;
		$auto = []; // list-3 date col: content-sized (matches wrap-off and the editor).
		foreach ( $kids as $k ) {
			$ka  = ELB_Block_Attrs::of( $k );
			$kid = ELB_Container_Selectors::block_id( $ka );
			if ( false !== strpos( (string) ( $ka['className'] ?? '' ), 'elb-card__date-col--list-3' ) && '' !== $kid ) {
				$auto[] = $kid;
				continue;
			}
			$w  = (float) ( $ka['widthDesktop'] ?? 0 );
			if ( '%' === (string) ( $ka['widthType'] ?? '%' ) && $w > 0 && '' !== $kid ) {
				$cols[ $kid ] = $w;
				$sum += $w;
			}
		}
		if ( $gap <= 0 || count( $cols ) < 1 || ( ! $auto && count( $cols ) < 2 ) || ( ! $auto && $sum < 90 ) ) {
			return '';
		}
		$total = $gap * ( count( $kids ) - 1 );
		$sel   = ELB_Container_Selectors::boosted_selector( $id, 'wrap' );
		$rules = $sel . '{height:auto !important;}';
		foreach ( $auto as $aid ) {
			$k      = '.wp-block-elb-flex-container.elb-block-' . $aid;
			$rules .= ELB_Container_Selectors::child_selector( $sel, $k ) . '{flex:0 1 auto !important;width:auto !important;min-width:' . self::AUTO_COL_MIN_PX . 'px !important;}';
		}
		foreach ( $cols as $cid => $w ) {
			// With a content-sized date col, the % cols share what's left after it (AUTO_COL_MIN_PX) and the gaps.
			$v      = $auto
				? 'calc((100% - ' . round( $total + self::AUTO_COL_MIN_PX * count( $auto ), 2 ) . 'px) * ' . round( $w / $sum, 4 ) . ')'
				: 'calc(' . round( $w, 2 ) . '% - ' . round( $total * $w / $sum, 2 ) . 'px)';
			$k      = '.wp-block-elb-flex-container.elb-block-' . $cid;
			$rules .= ELB_Container_Selectors::child_selector( $sel, $k ) . '{flex:0 1 ' . $v . ' !important;width:' . $v . ' !important;}';
		}
		return ELB_Breakpoints::min( ELB_Breakpoints::TABLET_MAX + 1, $rules );
	}
}
