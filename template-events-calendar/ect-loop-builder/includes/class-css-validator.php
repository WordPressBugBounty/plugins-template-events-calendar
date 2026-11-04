<?php
/**
 * Structural validator for CSS built client-side (elbFrontendCss / ELB_Wrapper_Css).
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

/**
 * Keeps only rules whose selector is scoped to this plugin and whose declarations have a safe shape.
 */
final class ELB_Css_Validator { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/**
	 * Structurally validate CSS built client-side (elbFrontendCss / ELB_Wrapper_Css)
	 * before it's ever printed. Rather than blocklisting known-bad tokens, this
	 * parses the string into rules and only keeps ones whose
	 * selector is provably scoped to this plugin's own elements and whose
	 * declarations match a safe property/value shape. Anything that doesn't
	 * parse cleanly is dropped (per-rule), not "cleaned up" — CSS is fragile to
	 * repair safely, so a rule we can't fully validate is simply excluded.
	 *
	 * @param string $css Raw CSS text.
	 * @return string Only the rules that passed validation.
	 */
	public static function sanitize( string $css ): string {
		$clean = wp_strip_all_tags( (string) $css );
		return '' === trim( $clean ) ? '' : self::validate_blocks( $clean );
	}

	/**
	 * Walk a CSS string at the top level, keeping only well-formed rule blocks.
	 */
	private static function validate_blocks( string $css ): string {
		$out    = '';
		$len    = strlen( $css );
		$cursor = 0;

		while ( $cursor < $len ) {
			// Skip whitespace between statements.
			while ( $cursor < $len && ctype_space( $css[ $cursor ] ) ) {
				++$cursor;
			}
			if ( $cursor >= $len ) {
				break;
			}

			$brace_pos = strpos( $css, '{', $cursor );
			if ( false === $brace_pos ) {
				break; // Trailing garbage with no block — ignore it.
			}
			$head = trim( substr( $css, $cursor, $brace_pos - $cursor ) );

			$block_end = self::find_matching( $css, $brace_pos );
			if ( false === $block_end ) {
				break; // Unbalanced braces — stop rather than guess.
			}
			$body = substr( $css, $brace_pos + 1, $block_end - $brace_pos - 1 );

			if ( '' !== $head && '@' === $head[0] ) {
				// At-rules (@media, @import, @font-face, …) are dropped.
			} elseif ( '' !== $head ) {
				$selector = self::validate_selector( $head );
				$decls    = self::validate_declarations( $body );
				if ( '' !== $selector && '' !== $decls ) {
					$out .= $selector . '{' . $decls . '}';
				}
			}

			$cursor = $block_end + 1;
		}

		return $out;
	}

	/**
	 * Find the character closing the one at $open (a '{' or '('), accounting for nesting.
	 *
	 * @param string $str   Text.
	 * @param int    $open  Offset of the opening character.
	 * @param string $left  Opening character.
	 * @param string $right Closing character.
	 * @return int|false
	 */
	private static function find_matching( string $str, int $open, string $left = '{', string $right = '}' ) {
		$depth = 0;
		$len   = strlen( $str );
		for ( $i = $open; $i < $len; $i++ ) {
			if ( $left === $str[ $i ] ) {
				++$depth;
			} elseif ( $right === $str[ $i ] ) {
				--$depth;
				if ( 0 === $depth ) {
					return $i;
				}
			}
		}
		return false;
	}

	/**
	 * A selector is only trusted when every top-level comma-separated branch
	 * is anchored to this plugin's own generated classes — never a bare
	 * element/global selector like body/html/:root that could style the
	 * whole page instead of just this one block.
	 */
	private static function validate_selector( string $selector ): string {
		if ( ! preg_match( '/^[A-Za-z0-9 \t.#:,()>*_-]+$/', $selector ) ) {
			return '';
		}
		if ( preg_match( '/expression|import|javascript|behavior|vbscript|binding/i', $selector ) ) {
			return '';
		}
		// :has() matches ancestors (html, body, …) of an anchored element, and :host/:scope
		// target the document root, so they escape the block scope. The editor never emits them.
		if ( preg_match( '/:(has|host|scope)\b/i', $selector ) ) {
			return '';
		}
		if ( ! self::validate_selector_branches( $selector ) ) {
			return '';
		}
		// Belt-and-suspenders: no branch anywhere (including ones this plugin
		// doesn't currently generate, so future-proofed) may smuggle in a
		// site-wide selector, whether or not it's wrapped in :is()/:where().
		if ( preg_match( '/(^|[\s,(])(body|html|head|:root)($|[\s,)])/i', $selector ) ) {
			return '';
		}
		return $selector;
	}

	/**
	 * Every comma-separated alternative — checked recursively at every
	 * nesting level, not just the top — must either be anchored to this
	 * plugin's own classes, or be the interior of a further :is()/:where()
	 * group (which itself then has to satisfy the same rule). A bare
	 * selector smuggled inside a nested group (e.g. ":is(.elb-x, body)") is
	 * just as live as one at the top level — browsers match it directly, not
	 * just as an ancestor filter — so a branch that merely *starts* with
	 * ":is(" is not enough; every :is()/:where()/:not() occurring anywhere in
	 * the branch must have its own contents validated too.
	 *
	 * :not() is deliberately never allowed to satisfy the anchor requirement
	 * by itself: unlike :is()/:where(), it requires the element to NOT match
	 * its contents, so ":not(.elb-x){...}" as a whole branch would style
	 * almost every element on the page, not the plugin's own markup. This
	 * plugin only ever emits :not() chained after a genuine anchor (e.g.
	 * ":is(.wp-block-elb-flex-container.elb-block-x):not(.elb-is-root-container)"),
	 * so its contents are still validated recursively for safety, but never
	 * counted as the branch's anchor.
	 *
	 * Sibling combinators (~ / +) are rejected outright: an anchored compound
	 * followed by one of these reaches unrelated elements elsewhere on the
	 * page, and this plugin's generators never emit them.
	 */
	private static function validate_selector_branches( string $selector ): bool {
		foreach ( self::split_top_level( $selector, ',' ) as $branch ) {
			$branch = trim( $branch );
			if ( '' === $branch ) {
				return false;
			}

			if ( preg_match( '/[~+]/', $branch ) ) {
				return false;
			}

			$has_anchor_group = false;
			$scanned          = $branch;
			while ( preg_match( '/:(is|where|not)\(/i', $scanned, $m, PREG_OFFSET_CAPTURE ) ) {
				$type  = strtolower( $m[1][0] );
				$open  = $m[0][1] + strlen( $m[0][0] ) - 1;
				$close = self::find_matching( $scanned, $open, '(', ')' );
				if ( false === $close ) {
					return false;
				}
				$inner = substr( $scanned, $open + 1, $close - $open - 1 );
				if ( ! self::validate_selector_branches( $inner ) ) {
					return false;
				}
				if ( 'not' !== $type ) {
					$has_anchor_group = true;
				}
				// Move past this group and keep scanning the rest of the branch.
				$scanned = substr( $scanned, $close + 1 );
			}

			if ( ! $has_anchor_group && ! preg_match( '/^(\.wp-block-elb-|\.elb-)/i', $branch ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Validate a `property:value;property:value` declaration list. Each
	 * property must look like a real CSS (or --custom) property, and each
	 * value is checked against a safe character set plus explicit checks on
	 * any url()/var() function calls it contains.
	 */
	private static function validate_declarations( string $body ): string {
		$out = [];
		foreach ( self::split_top_level( $body, ';' ) as $decl ) {
			$decl = trim( $decl );
			if ( '' === $decl ) {
				continue;
			}
			$colon = strpos( $decl, ':' );
			if ( false === $colon ) {
				return ''; // Malformed declaration — reject the whole rule.
			}
			$prop = trim( substr( $decl, 0, $colon ) );
			$val  = trim( substr( $decl, $colon + 1 ) );

			if ( ! preg_match( '/^(--[A-Za-z0-9-]+|-?[a-z][a-z-]*)$/i', $prop ) ) {
				return '';
			}
			if ( ! self::validate_value( $val ) ) {
				return '';
			}
			$out[] = $prop . ':' . $val;
		}
		return implode( ';', $out );
	}

	/**
	 * A declaration value: numbers/units/colors/keywords, optional nested
	 * var()/calc()/min()/max()/gradient() calls, and url() only when its
	 * contents are a plain http(s) or protocol-relative URL pointing at this
	 * same site — never a third-party host. image-set(), cross-fade() and
	 * src() are rejected because they can carry a URL without a url() token.
	 * Attribute selectors are already
	 * rejected in validate_selector(), but the same-site host check here
	 * closes the exfiltration vector on its own even if that ever changes:
	 * a value-based url() can only ever fetch this site's own resources, so
	 * it can never be used to leak data to an attacker-controlled server.
	 */
	private static function validate_value( string $val ): bool {
		if ( '' === $val ) {
			return false;
		}
		if ( preg_match( '/expression|@import|javascript:|vbscript:|behavior:|-moz-binding|[<>{}]/i', $val ) ) {
			return false;
		}
		if ( ! preg_match( '/^[A-Za-z0-9 \t.#%,()\/\'"_:!+-]+$/', $val ) ) {
			return false;
		}
		// These functions take a URL without a url() token, so the host check
		// below would never see a third-party address inside them.
		if ( preg_match( '/(image-set|cross-fade|src)\s*\(/i', $val ) ) {
			return false;
		}
		if ( false !== stripos( $val, 'url(' ) ) {
			if ( ! preg_match_all( '/url\(\s*([\'"]?)([^\'")]*)\1\s*\)/i', $val, $matches ) ) {
				return false;
			}
			// preg_match_all() only reports the url() tokens it can parse; a malformed one
			// (mismatched quotes, no closing paren) is silently skipped rather than failing the
			// match, so a value could slip a third-party host through in an unmatched token. Fail
			// closed instead: every literal "url(" in the string must correspond to one matched token.
			if ( substr_count( strtolower( $val ), 'url(' ) !== count( $matches[0] ) ) {
				return false;
			}
			static $site_host = null;
			if ( null === $site_host ) {
				$site_host = wp_parse_url( home_url(), PHP_URL_HOST );
			}
			foreach ( $matches[2] as $url ) {
				if ( '' === $url ) {
					continue;
				}
				if ( ! preg_match( '`^(https?:)?//[A-Za-z0-9_.\-~:%/?#\[\]@!$&\'()*+,;=]+$`i', $url ) ) {
					return false;
				}
				$url_host = wp_parse_url( $url, PHP_URL_HOST );
				if ( ! $url_host || $url_host !== $site_host ) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Split a string on a delimiter, but only at paren-depth 0 (so commas or
	 * semicolons inside function calls like :is(a, b) or url(...) survive).
	 *
	 * @return string[]
	 */
	private static function split_top_level( string $str, string $delimiter ): array {
		$parts = [];
		$depth = 0;
		$buf   = '';
		$len   = strlen( $str );
		for ( $i = 0; $i < $len; $i++ ) {
			$ch = $str[ $i ];
			if ( '(' === $ch ) {
				++$depth;
			} elseif ( ')' === $ch ) {
				$depth = max( 0, $depth - 1 );
			}
			if ( $ch === $delimiter && 0 === $depth ) {
				$parts[] = $buf;
				$buf     = '';
				continue;
			}
			$buf .= $ch;
		}
		if ( '' !== trim( $buf ) ) {
			$parts[] = $buf;
		}
		return $parts;
	}
}
