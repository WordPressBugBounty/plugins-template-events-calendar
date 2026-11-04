<?php
/**
 * Event date values for the Date block: date parts, time range, same-day check and the resolved badge/text value.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Event_Date_Value { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/** Plain text date formats by key. */
	const TEXT_FORMATS = [
		'default' => '{day} {monFull} {year}',
		'MD,Y'    => '{mon} {day}, {year}',
		'FD,Y'    => '{monFull} {day}, {year}',
		'DM'      => '{day} {mon}',
		'DML'     => '{day} {mon} {weekday}',
		'DF'      => '{day} {monFull}',
		'MD'      => '{mon} {day}',
		'd.FY'    => '{day}. {monFull} {year}',
		'd.F'     => '{day}. {monFull}',
		'ldF'     => '{weekday} {day} {monFull}',
		'Mdl'     => '{mon} {day} {weekday}',
		'd.Ml'    => '{day}. {mon} {weekday}',
		'dMY'     => '{day} {mon} {year}',
		'MD,YT'   => '{mon} {day}, {year}',
		'full'    => '{day} {monFull} {year}',
	];

	/** Formats that always append the event time range (start–end). */
	const WITH_TIME_RANGE = [ 'MD,YT', 'full', 'mdyt' ];

	/** Placeholders swapped for two-line date/time markup after the text is escaped. */
	const TIME_CLOCK_MARK = '{{{elb_clock}}}';
	const MDYT_OPEN       = '{{{elb_mdyt}}}';
	const MDYT_SPLIT      = '{{{elb_mdyt_split}}}';
	const MDYT_CLOSE      = '{{{/elb_mdyt}}}';

	/**
	 * Day, month, year and weekday strings for a timestamp.
	 *
	 * @param int|false         $ts Timestamp.
	 * @param DateTimeZone|null $tz Optional event timezone.
	 * @return array<string,string>|null Null when there is no date.
	 */
	public static function parts( $ts, ?DateTimeZone $tz = null ): ?array {
		if ( ! $ts ) {
			return null;
		}
		return [
			'day'     => (string) wp_date( 'd', $ts, $tz ),
			'day_n'   => (string) wp_date( 'j', $ts, $tz ),
			'mon'     => (string) wp_date( 'M', $ts, $tz ),
			'monFull' => (string) wp_date( 'F', $ts, $tz ),
			'year'    => (string) wp_date( 'Y', $ts, $tz ),
			'weekday' => (string) wp_date( 'l', $ts, $tz ),
		];
	}

	/**
	 * Year / full month name / day for a timestamp, from a single wp_date() call.
	 *
	 * Used where only these three fields are needed (e.g. a same_day() comparison),
	 * to avoid 3 separate wp_date() calls. '|' is used as the delimiter since it
	 * cannot appear in a translated month name.
	 *
	 * @param int|false         $ts Timestamp.
	 * @param DateTimeZone|null $tz Optional event timezone.
	 * @return array{year:string,monFull:string,day:string}
	 */
	public static function ymd( $ts, ?DateTimeZone $tz = null ): array {
		[ $year, $mon_full, $day ] = explode( '|', (string) wp_date( 'Y|F|d', $ts, $tz ) );
		return [
			'year'    => $year,
			'monFull' => $mon_full,
			'day'     => $day,
		];
	}

	/**
	 * Resolve the Date block settings into a badge (month + day) or a line of text.
	 *
	 * format: 'badge', 'site' (WordPress date format), a TEXT_FORMATS key, 'mdyt', or 'custom' (customDateFormat).
	 * dateShow 'range' adds the end date when it is another day; showTime adds the start time to text.
	 * MD,YT / full / mdyt always include the event time range (mdyt also includes a multi-day date range).
	 *
	 * @param array<string,mixed> $attributes  Block attributes.
	 * @param int|false           $start_ts    Start timestamp.
	 * @param int|false           $end_ts      End timestamp.
	 * @param int                 $post_id     Event post id (all-day check for showTime).
	 * @param array|null          $start_parts parts( $start_ts ).
	 * @param array|null          $end_parts   parts( $end_ts ).
	 * @param DateTimeZone|null   $tz          Optional event timezone.
	 * @return array{mode:string,month?:string,day?:string,value?:string}
	 */
	public static function resolve( array $attributes, $start_ts, $end_ts, int $post_id, ?array $start_parts, ?array $end_parts, ?DateTimeZone $tz = null ): array {
		if ( ! $start_parts ) {
			return [ 'mode' => 'text', 'value' => '' ];
		}
		$fmt       = (string) ( $attributes['format'] ?? 'badge' );
		$range     = 'range' === ( $attributes['dateShow'] ?? 'start' ) && $end_parts && ! self::same_day( $start_parts, $end_parts );

		if ( 'badge' === $fmt ) {
			$badge = self::badge_result( $start_parts );
			if ( $range ) {
				// Another month: name it so "Jun 20–21" never stands for Jun 20 – Aug 21.
				$badge['day'] .= '–' . ( $end_parts['mon'] !== $start_parts['mon'] ? $end_parts['mon'] . ' ' : '' ) . $end_parts['day'];
			}
			return $badge;
		}

		if ( 'mdyt' === $fmt ) {
			return self::text_result( self::mdyt_text( $start_parts, $end_parts, $post_id ) );
		}

		$text = static function ( $ts, array $parts ) use ( $fmt, $attributes, $tz ): string {
			if ( isset( self::TEXT_FORMATS[ $fmt ] ) ) {
				return self::fill( self::TEXT_FORMATS[ $fmt ], $parts );
			}
			return (string) wp_date( ELB_Date_Format_Resolver::resolve_date_format( 'custom' === $fmt ? 'custom' : 'site', (string) ( $attributes['customDateFormat'] ?? '' ) ), $ts, $tz );
		};
		$value = $text( $start_ts, $start_parts ) . ( $range ? ' – ' . $text( $end_ts, $end_parts ) : '' );
		if ( in_array( $fmt, self::WITH_TIME_RANGE, true ) ) {
			return self::text_result( self::join_date_and_time( $value, ELB_Event_Formatter::get_event_time_display( $post_id ) ) );
		}
		if ( ! empty( $attributes['showTime'] ) && ! ELB_Event_Repository::is_all_day( $post_id ) ) {
			$value .= '  ' . (string) wp_date( ELB_Date_Format_Resolver::resolve_time_format( 'site' ), $start_ts, $tz );
		}
		return self::text_result( $value );
	}

	/** mdyt: date on the first line, clock + time (or All day) on the next. */
	private static function mdyt_text( array $start, ?array $end, int $post_id ): string {
		$line = $start['mon'] . ' ' . $start['day_n'] . ', ' . $start['year'];
		if ( $end && ! self::same_day( $start, $end ) ) {
			$line .= ' – ' . $end['mon'] . ' ' . $end['day_n'] . ', ' . $end['year'];
		}
		return self::join_date_and_time( $line, ELB_Event_Formatter::get_event_time_display( $post_id ) );
	}

	/** Date + clock stay together; time sits on the same line when it fits. */
	private static function join_date_and_time( string $line, string $time ): string {
		if ( '' === $time ) {
			return $line;
		}
		return self::MDYT_OPEN . $line . ' ' . self::TIME_CLOCK_MARK . self::MDYT_SPLIT . $time . self::MDYT_CLOSE;
	}

	/**
	 * Whether two parts() results are the same calendar day.
	 *
	 * @param array<string,string> $a Date parts.
	 * @param array<string,string> $b Date parts.
	 */
	public static function same_day( array $a, array $b ): bool {
		return $a['year'] === $b['year'] && $a['monFull'] === $b['monFull'] && $a['day'] === $b['day'];
	}

	/**
	 * Fill a "{day} {mon}" style template with the date parts.
	 *
	 * @param string               $template Template.
	 * @param array<string,string> $parts    Date parts.
	 */
	private static function fill( string $template, array $parts ): string {
		return strtr(
			$template,
			[
				'{day}'     => $parts['day'],
				'{mon}'     => $parts['mon'],
				'{monFull}' => $parts['monFull'],
				'{year}'    => $parts['year'],
				'{weekday}' => $parts['weekday'],
			]
		);
	}

	/**
	 * @param array<string,string> $parts Date parts.
	 * @return array{mode:string,month:string,day:string}
	 */
	private static function badge_result( array $parts ): array {
		return [ 'mode' => 'badge', 'month' => mb_strtoupper( $parts['mon'] ), 'day' => $parts['day'] ];
	}

	/**
	 * @return array{mode:string,value:string}
	 */
	private static function text_result( string $value ): array {
		return [ 'mode' => 'text', 'value' => $value ];
	}
}
