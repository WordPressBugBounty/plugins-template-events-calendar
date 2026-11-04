<?php
/**
 * elb/event-meta — render.php
 */
defined( 'ABSPATH' ) || exit;

echo ELB_Event_Meta_Renderer::render( $attributes ?? [], $block ?? null ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
