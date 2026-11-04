<?php
/**
 * Builds WP_Query args from loop-builder attributes (ECT-style query builder).
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

class ELB_Event_Query_Builder { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/** Allowed eventsOrder values (first is the default). */
	const ORDERS = [ 'ASC', 'DESC' ];

	/** Allowed eventsOrderBy values (first is the default). */
	const ORDER_BY = [ 'start_date', 'title', 'date' ];

	/** @var array<string,mixed> */
	private array $attrs;

	/** @var array<string,mixed> */
	private array $args = [];

	/** @var array<int|string,mixed> */
	private array $meta_query = [];

	private const DATE_KEY  = '_EventStartDate';
	private const DATE_TYPE = 'DATETIME';

	private string $now;

	private string $today;

	/**
	 * @param array<string,mixed> $attrs Loop attributes.
	 */
	public function __construct( array $attrs ) {
		$this->attrs = $attrs;
		$this->now   = current_time( 'mysql' );
		$this->today = current_time( 'Y-m-d' );
	}

	/**
	 * Build and return WP_Query args.
	 *
	 * @return array<string,mixed>
	 */
	public function build(): array {
		$this->apply_base_args();
		$this->apply_date_filters();
		$this->assign_meta_query();
		$this->apply_tax_filters();
		return $this->args;
	}

	/**
	 * Run the query through The Events Calendar's tribe_get_events(), the same
	 * function the Events Calendar shortcode plugin uses.
	 *
	 * @param array<string,mixed> $attrs Loop attributes.
	 * @return WP_Query|null Null when TEC returns something other than a query.
	 */
	public static function query( array $attrs ): ?WP_Query {
		$result = tribe_get_events( ( new self( $attrs ) )->build(), true );

		return $result instanceof WP_Query ? $result : null;
	}

	/**
	 * Events per page: 1 to 48, default 6.
	 *
	 * @param array<string,mixed> $attrs Loop attributes.
	 * @return int Clamped per-page value.
	 */
	public static function per_page( array $attrs ): int {
		return min( 48, max( 1, (int) ( $attrs['perPage'] ?? 6 ) ) );
	}

	/**
	 * Extract integer IDs from an array of plain ints or {id, name} objects.
	 *
	 * Accepts block-attribute shapes such as `[ 12, 34 ]` or
	 * `[ [ 'id' => 12, 'name' => 'Music' ], … ]`. Non-positive values are dropped.
	 *
	 * @param array<int|string|array{id?:int|string}>|mixed $items Raw ID list from attributes.
	 * @return array<int> Unique positive IDs in first-seen order (empty when none).
	 */
	public static function extract_ids( $items ): array {
		$ids = [];
		foreach ( (array) $items as $item ) {
			$id = is_array( $item ) ? (int) ( $item['id'] ?? 0 ) : (int) $item;
			if ( $id > 0 ) {
				$ids[] = $id;
			}
		}
		return array_values( array_unique( $ids ) );
	}

	private function apply_base_args(): void {
		$raw_order = strtoupper( is_string( $this->attrs['eventsOrder'] ?? null ) ? $this->attrs['eventsOrder'] : 'ASC' );
		$order     = in_array( $raw_order, self::ORDERS, true ) ? $raw_order : self::ORDERS[0];

		$order_by = $this->attrs['eventsOrderBy'] ?? null;
		$order_by = in_array( $order_by, self::ORDER_BY, true ) ? $order_by : self::ORDER_BY[0];

		$this->args = [
			'post_type'      => 'tribe_events',
			'post_status'    => 'publish',
			'posts_per_page' => self::per_page( $this->attrs ),
			'orderby'        => 'event_date',
			'order'          => $order,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- TEC stores event start on this meta key; required for event_date orderby.
			'meta_key'       => self::DATE_KEY,
			'no_found_rows'  => true,
		];

		// Default sorts by event start date (TEC "event_date"), as the shortcode plugin does;
		// title / publish date are plain post fields.
		if ( self::ORDER_BY[0] !== $order_by ) {
			$this->args['orderby'] = $order_by;
			unset( $this->args['meta_key'] );
		}
	}

	/**
	 * Accept only a real Y-m-d day. Anything else is rejected before it reaches SQL.
	 */
	private function valid_ymd( string $value ): string {
		$value = trim( $value );
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
			return '';
		}
		$dt = DateTime::createFromFormat( '!Y-m-d', $value );
		if ( ! $dt || $dt->format( 'Y-m-d' ) !== $value ) {
			return '';
		}
		return $value;
	}

	/**
	 * Validate a Y-m-d range and put it in order. Malformed input must not become
	 * an open-ended MySQL range, so the query is forced to return nothing instead.
	 *
	 * @return array{0:string,1:string}|null [ from, to ], or null when invalid.
	 */
	private function normalized_range( string $from_ymd, string $to_ymd ): ?array {
		$from_ymd = $this->valid_ymd( $from_ymd );
		$to_ymd   = $this->valid_ymd( $to_ymd );
		if ( '' === $from_ymd || '' === $to_ymd ) {
			return null;
		}
		return $from_ymd > $to_ymd ? [ $to_ymd, $from_ymd ] : [ $from_ymd, $to_ymd ];
	}

	/**
	 * Force query to match no posts.
	 */
	private function force_empty_result(): void {
		$this->args['post__in'] = [ 0 ];
	}

	/**
	 * Events overlapping a calendar-day range, same rule as the shortcode plugin's
	 * date filters: starts on/before the end of the range and ends on/after its start.
	 */
	private function add_day_range( string $from_ymd, string $to_ymd ): void {
		$range = $this->normalized_range( $from_ymd, $to_ymd );
		if ( ! $range ) {
			$this->force_empty_result();
			return;
		}
		[ $from_ymd, $to_ymd ] = $range;
		$this->meta_query[] = [
			'relation' => 'AND',
			[
				'key'     => self::DATE_KEY,
				'value'   => $to_ymd . ' 23:59:59',
				'compare' => '<=',
				'type'    => self::DATE_TYPE,
			],
			[
				'key'     => ELB_Event_Repository::END_DATE_KEY,
				'value'   => $from_ymd . ' 00:00:00',
				'compare' => '>=',
				'type'    => self::DATE_TYPE,
			],
		];
	}

	/**
	 * TEC start_date / end_date args (end is exclusive, so +1 day), as in the shortcode plugin.
	 */
	private function add_start_end_date( string $from, string $to ): void {
		$range = $this->normalized_range( $from, $to );
		if ( ! $range ) {
			$this->force_empty_result();
			return;
		}
		$this->args['start_date'] = $range[0];
		$this->args['end_date']   = gmdate( 'Y-m-d H:i:s', strtotime( $range[1] . ' +1 day' ) );
	}

	private function apply_date_filters(): void {
		$events_time = $this->attrs['eventsTime'] ?? 'all';

		if ( 'between_dates' === $events_time ) {
			if ( ! empty( $this->attrs['dateFrom'] ) || ! empty( $this->attrs['dateTo'] ) ) {
				$from = sanitize_text_field( (string) ( $this->attrs['dateFrom'] ?? '' ) );
				$to   = sanitize_text_field( (string) ( ( $this->attrs['dateTo'] ?? '' ) ?: $from ) );
				$this->add_start_end_date( $from, $to );
			}
		} elseif ( is_string( $events_time ) ) {
			$range = $this->preset_range( $events_time );
			if ( $range ) {
				$this->add_day_range( $range[0], $range[1] );
			}
		}

		$this->apply_event_status( $this->attrs['showEvents'] ?? 'both' );
	}

	/**
	 * First and last day (Y-m-d) of a named date preset, or null for "all" and unknown presets.
	 *
	 * @param string $preset Preset key (today, this_week, last_month...).
	 * @return array{0:string,1:string}|null
	 */
	private function preset_range( string $preset ): ?array {
		$today_ts = strtotime( $this->today );

		switch ( $preset ) {
			case 'today':
				return [ $this->today, $this->today ];

			case 'tomorrow':
				$tomorrow = gmdate( 'Y-m-d', strtotime( '+1 day', $today_ts ) );
				return [ $tomorrow, $tomorrow ];

			case 'this_week':
				return $this->week_span( $today_ts, 0 );

			case 'this_month':
				return [ gmdate( 'Y-m-01', $today_ts ), gmdate( 'Y-m-t', $today_ts ) ];

			case 'next_week':
				return $this->week_span( $today_ts, 1 );

			case 'next_30_days':
				return [ $this->today, gmdate( 'Y-m-d', strtotime( '+30 days', $today_ts ) ) ];

			case 'last_7_days':
				return [ gmdate( 'Y-m-d', strtotime( '-7 days', $today_ts ) ), $this->today ];

			case 'last_month':
				$first = strtotime( 'first day of last month', $today_ts );
				return [ gmdate( 'Y-m-01', $first ), gmdate( 'Y-m-t', $first ) ];

			case 'last_year':
				$year = (int) gmdate( 'Y', $today_ts ) - 1;
				return [ $year . '-01-01', $year . '-12-31' ];
		}

		return null;
	}

	/**
	 * Seven-day span aligned to Settings → General → Week Starts On.
	 *
	 * @param int $today_ts    Timestamp of "today" in the site timezone.
	 * @param int $week_offset 0 = this week, 1 = next week.
	 * @return array{0:string,1:string}
	 */
	private function week_span( int $today_ts, int $week_offset ): array {
		$sow   = (int) get_option( 'start_of_week', 1 );
		$dow   = (int) gmdate( 'w', $today_ts );
		$start = strtotime( '-' . ( ( $dow - $sow + 7 ) % 7 ) . ' days', $today_ts );
		if ( 0 !== $week_offset ) {
			$start = strtotime( '+' . ( 7 * $week_offset ) . ' days', $start );
		}
		return [
			gmdate( 'Y-m-d', $start ),
			gmdate( 'Y-m-d', strtotime( '+6 days', $start ) ),
		];
	}

	/**
	 * Upcoming / past narrows any date range too (e.g. "Upcoming" + "This week" = the
	 * rest of this week), so it is always applied on top.
	 *
	 * @param mixed $show_events both, upcoming or past.
	 */
	private function apply_event_status( $show_events ): void {
		if ( 'upcoming' === $show_events || 'past' === $show_events ) {
			$this->meta_query[] = [
				'key'     => ELB_Event_Repository::END_DATE_KEY,
				'value'   => $this->now,
				'compare' => 'upcoming' === $show_events ? '>=' : '<',
				'type'    => self::DATE_TYPE,
			];
		}
	}

	private function assign_meta_query(): void {
		if ( empty( $this->meta_query ) ) {
			return;
		}

		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- TEC date-range filters require a meta query.
		$this->args['meta_query'] = $this->meta_query;
	}

	private function apply_tax_filters(): void {
		$tax_query = [];

		$cat_ids = self::extract_ids( $this->attrs['selectedCategories'] ?? [] );
		if ( $cat_ids ) {
			$tax_query[] = [
				'taxonomy' => ELB_Event_Repository::CATEGORY_TAXONOMY,
				'field'    => 'term_id',
				'terms'    => $cat_ids,
				'operator' => 'IN',
			];
		}

		if ( ! empty( $tax_query ) ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Category filter is an opt-in tax query.
			$this->args['tax_query'] = $tax_query;
		}
	}
}
