<?php
/**
 * elb/loop-builder — render.php
 *
 * Query args come from ELB_Event_Query_Builder (The Events Calendar).
 * Delegated to ELB_Loop::render_loop_builder().
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

echo ELB_Loop::render_loop_builder( $attributes ?? [], $block ?? null ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
