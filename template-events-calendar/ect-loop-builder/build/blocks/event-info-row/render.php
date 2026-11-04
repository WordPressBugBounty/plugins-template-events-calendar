<?php
/**
 * elb/event-info-row — Venue, Time & Ticket compact row.
 * Delegated to ELB_Event_Info_Row_Renderer::render().
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

echo ELB_Event_Info_Row_Renderer::render( $attributes ?? [], $block ?? null ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
