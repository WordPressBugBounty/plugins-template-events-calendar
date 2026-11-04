<?php
/**
 * Category term colors and badge styles.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

class ELB_Category { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/**
	 * Read TEC category badge colors for a term.
	 *
	 * @param int $term_id Term ID.
	 * @return array{primary:string,text:string}
	 */
	public static function get_category_term_colors( $term_id ) {
		static $cache = [];

		$term_id = (int) $term_id;
		$out     = [ 'primary' => '', 'text' => '' ];
		if ( $term_id <= 0 ) {
			return $out;
		}
		if ( isset( $cache[ $term_id ] ) ) {
			return $cache[ $term_id ];
		}

		$all_meta = get_term_meta( $term_id );
		$get_meta = static function ( string $key ) use ( $all_meta ): string {
			if ( is_array( $all_meta ) && isset( $all_meta[ $key ][0] ) && is_scalar( $all_meta[ $key ][0] ) ) {
				return (string) $all_meta[ $key ][0];
			}
			return '';
		};

		$out['primary'] = ELB_Css_Utils::normalize_hex_color( $get_meta( 'tec-events-cat-colors-primary' ) );
		$out['text']    = ELB_Css_Utils::normalize_hex_color( $get_meta( 'tec-events-cat-colors-text' ) );
		if ( $out['primary'] && ! $out['text'] ) {
			$out['text'] = ELB_Css_Utils::contrast_text_color( $out['primary'] );
		}
		$cache[ $term_id ] = $out;
		return $out;
	}

	/**
	 * Term name for editor JSON / React text. WordPress may HTML-encode "&";
	 * React prints that as literal "&amp;" unless it is decoded first.
	 *
	 * @param WP_Term|string $term Term or name.
	 */
	public static function editor_term_name( $term ): string {
		$name = $term instanceof WP_Term ? (string) $term->name : (string) $term;
		return wp_specialchars_decode( $name, ENT_QUOTES );
	}

	/**
	 * Format a category term for editor JSON / frontend badges.
	 *
	 * @param WP_Term $term Term object.
	 * @return array{id:int,name:string,slug:string,color:string,textColor:string}
	 */
	public static function format_category_term( $term ) {
		if ( ! $term instanceof WP_Term ) {
			return [ 'id' => 0, 'name' => '', 'slug' => '', 'color' => '', 'textColor' => '' ];
		}
		$colors = self::get_category_term_colors( (int) $term->term_id );
		return [
			'id'        => (int) $term->term_id,
			'name'      => self::editor_term_name( $term ),
			'slug'      => $term->slug,
			'color'     => $colors['primary'],
			'textColor' => $colors['text'],
		];
	}

}
