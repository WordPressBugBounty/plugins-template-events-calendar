<?php
/**
 * Render-tree depth state for one request: loops and outer wrapper/flex-container trees being rendered.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Render_Tracker { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/** @var self|null */
	private static $instance;

	/** @var int Loops currently rendering; their whole tree's CSS was added up front. */
	private int $loop_depth = 0;

	/** @var int Wrapper/flex-container trees (outside loops) currently rendering; the outermost one walked its subtree. */
	private int $tree_depth = 0;

	/** The request's tracker. */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function enter_loop(): void {
		++$this->loop_depth;
	}

	public function leave_loop(): void {
		if ( $this->loop_depth > 0 ) {
			--$this->loop_depth;
		}
	}

	public function in_loop(): bool {
		return $this->loop_depth > 0;
	}

	/** True when no wrapper/flex-container tree is open (the next one is outermost). */
	public function outside_tree(): bool {
		return 0 === $this->tree_depth;
	}

	public function enter_tree(): void {
		++$this->tree_depth;
	}

	/** A wrapper/flex-container outside any loop finished rendering. */
	public function leave_tree(): void {
		if ( 0 === $this->loop_depth && $this->tree_depth > 0 ) {
			--$this->tree_depth;
		}
	}
}
