<?php
/**
 * elb/event-image — render.php
 */
defined( 'ABSPATH' ) || exit;

echo ELB_Event_Image_Renderer::render( $attributes ?? [], $block ?? null ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
