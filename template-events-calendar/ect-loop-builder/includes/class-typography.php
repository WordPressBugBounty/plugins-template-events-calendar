<?php
/**
 * Typography attributes (font family / weight / style / transform / decoration / sizes) as sanitized CSS values.
 *
 * One reader for every block that has a Typography panel; callers decide how to print each value
 * (plain declaration, `--elb-field-*` twin, device media query).
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Typography { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/** Device-independent font properties (written once, not per breakpoint). */
	const FONT_PROPS = [ 'font-family', 'font-weight', 'font-style', 'text-decoration', 'text-transform' ];

	/**
	 * Enqueue the Google Font chosen in the Typography attributes, if any.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @param string|null         $prefix     Attribute prefix for a child element (e.g. 'cat'); null reads the block's own attributes.
	 */
	public static function enqueue_fonts( array $attributes, ?string $prefix = null ): void {
		$family = null === $prefix
			? (string) ( $attributes['fontFamily'] ?? '' )
			: ELB_Block_Attrs::prefixed( $attributes, $prefix, 'fontFamily' );
		if ( '' !== $family ) {
			self::enqueue_google_font( $family );
		}
	}

	/** Unit used when a size attribute has none saved (matches the editor's Typography control). */
	const DEFAULT_UNITS = [
		'font-size'      => 'px',
		'letter-spacing' => 'px',
		'line-height'    => 'em',
	];

	/** System and theme fonts: no download, so visitors are never sent to Google for them. */
	private const SYSTEM_FONTS = [ 'arial', 'helvetica', 'georgia', 'times new roman', 'verdana', 'tahoma', 'courier new', 'system-ui', 'sans-serif', 'serif', 'monospace' ];

	/**
	 * Enqueue a Google Font stylesheet once per request (frontend + editor iframe).
	 *
	 * @param string $family Font family name.
	 */
	private static function enqueue_google_font( string $family ): void {
		$family = trim( $family, " \t\n\r\0\x0B\"'" );
		if ( '' === $family || in_array( strtolower( $family ), self::SYSTEM_FONTS, true ) ) {
			return;
		}
		if ( ! apply_filters( 'template_events_calendar_elb_load_google_fonts', true ) || ! ELB_Inline_Css::first_time( 'font:' . $family ) ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Bundled Events Loop Builder hook.
			return;
		}
		$handle = 'elb-gfont-' . sanitize_title( $family );
		$url    = 'https://fonts.googleapis.com/css2?family=' . rawurlencode( $family ) . ':wght@400;500;600;700&display=swap';
		$url    = str_replace( '%20', '+', $url );

		// Enqueued during body rendering, core prints it with the late styles in the footer.
		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Google Fonts manages its own versioning via URL.
		wp_enqueue_style( $handle, $url, [], null );
	}

	/**
	 * Sanitized typography values (pure: no asset side effects).
	 */
	public static function values( array $attributes, ?string $prefix = null ): array {
		$attr   = static function ( string $name ) use ( $attributes, $prefix ): string {
			if ( null === $prefix ) {
				return isset( $attributes[ $name ] ) ? (string) $attributes[ $name ] : '';
			}
			return ELB_Block_Attrs::prefixed( $attributes, $prefix, $name );
		};
		$values = [];

		$family = $attr( 'fontFamily' );
		if ( $family ) {
			$values['font-family'] = ELB_Css_Utils::css_font_family( $family );
		}
		$keywords = [
			'font-weight'     => ELB_Css_Utils::safe_font_weight( $attr( 'fontWeight' ) ),
			'font-style'      => ELB_Css_Utils::safe_keyword( sanitize_key( $attr( 'fontStyle' ) ), ELB_Css_Utils::FONT_STYLES ),
			'text-transform'  => ELB_Css_Utils::safe_keyword( sanitize_key( $attr( 'textTransform' ) ), ELB_Css_Utils::TEXT_TRANSFORMS ),
			'text-decoration' => ELB_Css_Utils::safe_keyword( $attr( 'textDecoration' ), ELB_Css_Utils::TEXT_DECORATIONS ),
		];
		foreach ( $keywords as $prop => $value ) {
			if ( $value ) {
				$values[ $prop ] = $value;
			}
		}

		foreach ( [ 'font-size' => 'fontSize', 'letter-spacing' => 'letterSpacing', 'line-height' => 'lineHeight' ] as $prop => $name ) {
			$size = $attr( $name );
			if ( '' !== $size ) {
				$unit            = ELB_Css_Utils::safe_unit( $attr( $name . 'Unit' ) ?: ( self::DEFAULT_UNITS[ $prop ] ?? 'px' ) );
				$values[ $prop ] = ( (float) $size ) . $unit;
			}
		}

		return $values;
	}

	/**
	 * Typography declarations: 'prop:value', plus a --elb-field-* twin
	 * for the props in $var_twins ('twin' = both, 'var' = the twin only).
	 *
	 * @param array<string,mixed>  $attributes Block attributes.
	 * @param string|null          $prefix     Attribute prefix (e.g. 'button'), or null.
	 * @param array<string,string> $var_twins  prop => 'twin'|'var'.
	 * @param string[]|null        $only       Props to emit (in this order); null for all.
	 * @return string[]
	 */
	public static function declarations( array $attributes, ?string $prefix = null, array $var_twins = [], ?array $only = null ): array {
		self::enqueue_fonts( $attributes, $prefix );
		$values = self::values( $attributes, $prefix );
		if ( null !== $only ) {
			$order  = array_flip( $only );
			$values = array_intersect_key( array_replace( $order, $values ), $values, $order );
		}
		$out = [];
		foreach ( $values as $prop => $value ) {
			$mode = $var_twins[ $prop ] ?? '';
			if ( 'var' !== $mode ) {
				$out[] = $prop . ':' . $value;
			}
			if ( '' !== $mode ) {
				$out[] = '--elb-field-' . $prop . ':' . $value;
			}
		}
		return $out;
	}

	/**
	 * Class names for active typography overrides (matches JS fieldTypoClassNames).
	 *
	 * @param array $attributes Block attributes.
	 * @return string Space-separated class names.
	 */
	public static function field_classnames( $attributes ) {
		$classes = [];
		if ( ! empty( $attributes['fontFamily'] ) ) {
			$classes[] = 'elb-typo-family';
		}
		if ( ! empty( $attributes['fontWeight'] ) ) {
			$classes[] = 'elb-typo-weight';
		}
		if ( ! empty( $attributes['fontStyle'] ) ) {
			$classes[] = 'elb-typo-style';
		}
		if ( ! empty( $attributes['textDecoration'] ) ) {
			$classes[] = 'elb-typo-decoration';
		}
		if ( ! empty( $attributes['textTransform'] ) ) {
			$classes[] = 'elb-typo-transform';
		}
		if ( isset( $attributes['letterSpacing'] ) && '' !== (string) $attributes['letterSpacing'] ) {
			$classes[] = 'elb-typo-tracking';
		}
		if ( ! empty( $attributes['fontSize'] ) ) {
			$classes[] = 'elb-typo-size';
		}
		return implode( ' ', $classes );
	}
}
