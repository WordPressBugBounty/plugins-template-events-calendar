<?php
/**
 * Event Meta styles: inline <ul> declarations and responsive CSS.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Meta_Styles { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/**
	 * Inline declarations for the <ul>: typography + colour, gap / icon / badge / separator variables,
	 * and field padding/margin (only the sides that are set) — same Spacing attrs as Event Time.
	 *
	 * @param array<string,mixed> $attributes Attributes.
	 * @return string[]
	 */
	public static function style_decls( array $attributes ): array {
		$out        = ELB_Typography::declarations( (array) $attributes, null, [ 'font-weight' => 'twin' ], ELB_Typography::FONT_PROPS );
		$text_color = ELB_Css_Utils::sanitize_css_color( $attributes['textColor'] ?? '' );
		if ( $text_color ) {
			$out[] = 'color:' . esc_attr( $text_color );
		}
		$out[] = '--elb-meta-gap:' . (float) $attributes['metaGap'] . 'px';
		$out[] = '--elb-meta-icon-size:' . max( 8, min( 64, (float) $attributes['blockIconSize'] ) ) . 'px';

		$colors = [
			'--elb-meta-icon-color'      => 'blockIconColor',
			'--elb-meta-sep-color'       => 'separatorColor',
			'--elb-meta-badge-accent'    => 'catBackgroundColor',
			'--elb-meta-badge-fg'        => 'catTextColor',
			'--elb-meta-badge-hover-bg'  => 'catHoverBackgroundColor',
			'--elb-meta-badge-hover-fg'  => 'catHoverTextColor',
		];
		foreach ( $colors as $var => $key ) {
			$c = ELB_Css_Utils::sanitize_css_color( $attributes[ $key ] ?? '' );
			if ( $c ) {
				$out[] = $var . ':' . $c;
			}
		}
		if ( isset( $attributes['badgeRadius'] ) && '' !== $attributes['badgeRadius'] ) {
			$out[] = '--elb-meta-badge-radius:' . (float) $attributes['badgeRadius'] . 'px';
		}
		$badge_pad = self::badge_padding( $attributes );
		if ( '' !== $badge_pad ) {
			$out[] = '--elb-meta-badge-padding:' . $badge_pad;
		}
		// Field Spacing → Padding/Margin are emitted by responsive_css() ->
		// ELB_Spacing_Styles::field_scoped_css() as scoped CSS. The <ul> resets
		// padding/margin to 0 in frontend.css when nothing is set.
		return $out;
	}

	/** Scoped typography and padding/margin CSS for the shared class (Desktop only in Free). */
	public static function responsive_css( array $attributes, string $resp_class ): string {
		return (string) ELB_Spacing_Styles::field_scoped_css( $attributes, $resp_class, '' );
	}

	/** Badge padding shorthand value ('' when no side is set). */
	private static function badge_padding( array $attributes ): string {
		$decl = ELB_Spacing_Styles::spacing_box_decl( $attributes, 'catPadding' );
		return '' === $decl ? '' : ELB_Spacing_Styles::spacing_box_value( $decl, 'padding' );
	}

}
