<?php
/**
 * elb/event-excerpt — render.php
 * Displays a trimmed excerpt from the event post with full style support.
 * Delegated to ELB_Event_Excerpt_Renderer::render().
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

echo ELB_Event_Excerpt_Renderer::render( $attributes ?? [], $block ?? null ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
