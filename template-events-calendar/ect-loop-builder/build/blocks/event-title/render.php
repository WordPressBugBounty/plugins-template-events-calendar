<?php
/**
 * elb/event-title — render.php
 * Displays the event post title with full style attribute support.
 * Delegated to ELB_Event_Title_Renderer::render().
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

echo ELB_Event_Title_Renderer::render( $attributes ?? [], $block ?? null ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
