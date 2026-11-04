<?php
/**
 * elb/event-date — render.php
 *
 * Supports editor date formats + presets on the frontend.
 * Delegated to ELB_Event_Date_Renderer::render().
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

echo ELB_Event_Date_Renderer::render( $attributes ?? [], $block ?? null ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
