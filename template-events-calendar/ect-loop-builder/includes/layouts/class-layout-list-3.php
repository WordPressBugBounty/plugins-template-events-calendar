<?php
/**
 * List Style 3 (Monthly Agenda): month headings and the timeline line.
 * Autoloaded (includes/layouts/) only when a list-3 loop or column is rendered.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Layout_List_3 extends ELB_Layout_Hooks { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/** Month of the previous card (month headings print once per month). */
	private string $last_month = '';

	/**
	 * Month heading before the first card of each month.
	 *
	 * @param int $post_id Event post ID.
	 */
	public function before_card( int $post_id ): string {
		if ( ! self::groups_by_month( $this->attrs ) ) {
			return '';
		}
		$month_key = self::event_month_key( $post_id );
		if ( '' === $month_key || $month_key === $this->last_month ) {
			return '';
		}
		$this->last_month = $month_key;
		return self::month_heading_markup( $month_key );
	}

	/** Month heading style vars on the loop wrapper. */
	public function wrapper_attr(): string {
		return self::month_heading_style_attr( $this->attrs, $this->section );
	}

	/** Stock Monthly Agenda timeline colour (matches --elb-teal in frontend.css). */
	const STOCK_LINE_COLOR = '#0e4d5f';

	/**
	 * Whether a Monthly Agenda text column still has the stock timeline border
	 * (2px solid #0e4d5f, on the left only). A user-changed Border keeps its own CSS.
	 *
	 * @param array<string,mixed> $attrs Container attributes.
	 */
	public static function is_stock_timeline_border( array $attrs ): bool {
		if ( false === strpos( (string) ( $attrs['className'] ?? '' ), 'elb-card__body--list-3' ) ) {
			return false;
		}
		$n = static fn( $k ) => (float) ( $attrs[ $k ] ?? 0 );
		return 'solid' === (string) ( $attrs['containerBorderStyle'] ?? '' )
			&& self::STOCK_LINE_COLOR === strtolower( (string) ( $attrs['containerBorderColor'] ?? '' ) )
			&& 2.0 === $n( 'containerBorderLeftWidth' )
			&& 0.0 === $n( 'containerBorderTopWidth' ) && 0.0 === $n( 'containerBorderRightWidth' ) && 0.0 === $n( 'containerBorderBottomWidth' );
	}

	/**
	 * Timeline line settings (Events Loop > Style) on the stock list-3 text column.
	 * Fallbacks equal the stock border, so unset settings render as before.
	 *
	 * @param array<string,mixed> $attrs Container attributes.
	 * @param string              $id    Sanitized block id.
	 */
	public static function timeline_line_css( array $attrs, string $id ): string {
		if ( '' === $id || ! self::is_stock_timeline_border( $attrs ) ) {
			return '';
		}
		return ELB_Container_Selectors::boosted_selector( $id, 'tl' ) . '{'
			. 'border-left-width:var(--elb-tl-w,2px) !important;border-left-style:var(--elb-tl-style,solid) !important;border-left-color:var(--elb-tl-color,' . self::STOCK_LINE_COLOR . ') !important;}';
	}

	/**
	 * Year-month key (Y-m) of an event's start date, or '' when unknown.
	 *
	 * @param int $post_id Event post ID.
	 */
	private static function event_month_key( int $post_id ): string {
		return (string) ELB_Event_Formatter::get_event_start_date( $post_id, 'Y-m' );
	}

	/**
	 * Month heading row printed before the first card of each month.
	 * `data-elb-month` lets Load more drop a repeated heading across pages.
	 *
	 * @param string $month_key Year-month key (Y-m).
	 */
	private static function month_heading_markup( string $month_key ): string {
		// Build "1st of month, midnight" directly in the site's timezone; strtotime() would
		// read it against PHP's default timezone (UTC in WP), shifting the heading by a day
		// (and, on the 1st, a whole month) for timezones west of UTC.
		$dt    = date_create_immutable_from_format( 'Y-m-d H:i:s', $month_key . '-01 00:00:00', wp_timezone() );
		$label = $dt ? wp_date( _x( 'F Y', 'month heading date format', 'template-events-calendar' ), $dt->getTimestamp(), wp_timezone() ) : $month_key;
		return '<div class="elb-month-heading" role="heading" aria-level="2" data-elb-month="' . esc_attr( $month_key ) . '">'
			. '<span class="elb-month-heading__label">' . esc_html( $label ) . '</span>'
			. '</div>';
	}

	/**
	 * Month headings between cards: List Style 3 only; "Show month heading" (showMonthHeading) defaults on.
	 *
	 * @param array<string,mixed> $attrs Loop block attributes.
	 */
	private static function groups_by_month( array $attrs ): bool {
		return self::attr_flag( $attrs, 'showMonthHeading' );
	}

	/**
	 * Monthly Agenda month heading Style-tab settings as CSS vars on the loop wrapper.
	 * Unset values emit nothing, so frontend.css fallbacks keep the stock look.
	 *
	/**
	 * Monthly Agenda month heading Style-tab settings as CSS vars on the loop wrapper.
	 * Unset values emit nothing, so frontend.css fallbacks keep the stock look.
	 *
	 * @param array<string,mixed>      $attrs   Loop block attributes.
	 * @param array<string,mixed>|null $section Section block.
	 */
	public static function month_heading_style_attr( array $attrs, ?array $section = null ): string {
		$vars  = self::month_heading_vars( $attrs, $section );
		$parts = [];
		foreach ( $vars as $name => $value ) {
			$parts[] = $name . ':' . $value;
		}
		return $parts ? ' style="' . esc_attr( implode( ';', $parts ) ) . '"' : '';
	}

	/**
	 * @param array<string,mixed>      $attrs   Loop block attributes.
	 * @param array<string,mixed>|null $section Section block.
	 * @return array<string,string>
	 */
	private static function month_heading_vars( array $attrs, ?array $section = null ): array {
		$color = [ 'ELB_Css_Utils', 'sanitize_css_color' ];
		$px = static function ( $v, $min, $max ) {
			if ( '' === (string) $v || ! is_numeric( $v ) ) {
				return '';
			}
			return max( $min, min( $max, (float) $v ) ) . 'px';
		};
		$vars = [
			'--elb-mh-color'      => $color( $attrs['monthHeadingColor'] ?? '' ),
			'--elb-mh-size'       => $px( $attrs['monthHeadingFontSize'] ?? '', 8, 96 ),
			'--elb-mh-weight'     => in_array( (string) ( $attrs['monthHeadingFontWeight'] ?? '' ), [ '300', '400', '500', '600', '700', '800', '900' ], true ) ? (string) $attrs['monthHeadingFontWeight'] : '',
			'--elb-mh-line'       => ( isset( $attrs['monthHeadingShowLine'] ) && false === $attrs['monthHeadingShowLine'] ) ? 'none' : '',
			'--elb-mh-line-color' => $color( $attrs['monthHeadingLineColor'] ?? '' ),
			'--elb-mh-line-w'     => $px( $attrs['monthHeadingLineWidth'] ?? '', 0, 20 ),
			'--elb-mh-line-style' => in_array( (string) ( $attrs['monthHeadingLineStyle'] ?? '' ), [ 'solid', 'dashed', 'dotted' ], true ) ? (string) $attrs['monthHeadingLineStyle'] : '',
			'--elb-mh-gap'        => $px( $attrs['monthHeadingGap'] ?? '', 0, 200 ),
			'--elb-mh-mb'         => $px( $attrs['monthHeadingMarginBottom'] ?? '', 0, 200 ),
			// Timeline line (left border of the Monthly Agenda text column).
			'--elb-tl-color'      => $color( $attrs['timelineLineColor'] ?? '' ),
			'--elb-tl-w'          => ( isset( $attrs['timelineShowLine'] ) && false === $attrs['timelineShowLine'] ) ? '0px' : $px( $attrs['timelineLineWidth'] ?? '', 0, 20 ),
			'--elb-tl-style'      => in_array( (string) ( $attrs['timelineLineStyle'] ?? '' ), [ 'solid', 'dashed', 'dotted' ], true ) ? (string) $attrs['timelineLineStyle'] : '',
		];

		if ( is_array( $section ) && ! empty( $section['attrs'] ) && is_array( $section['attrs'] ) ) {
			$s_attrs  = $section['attrs'];
			$row_gap  = $s_attrs['rowGapDesktop'] ?? null;
			$row_unit = ELB_Css_Utils::safe_unit( $s_attrs['rowGapType'] ?? 'px', 'px' );
			if ( null !== $row_gap && '' !== (string) $row_gap && is_numeric( $row_gap ) ) {
				$vars['--elb-row-gap'] = (float) $row_gap . $row_unit;
			}
		}

		return array_filter( $vars, static fn( $v ) => '' !== $v );
	}
}
