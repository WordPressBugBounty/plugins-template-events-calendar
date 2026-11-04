<?php
/**
 * elb/event-read-more — render.php (button / link only, no cost).
 * Delegated to ELB_Event_Read_More_Renderer::render().
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

echo ELB_Event_Read_More_Renderer::render( $attributes ?? [], $block ?? null ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
