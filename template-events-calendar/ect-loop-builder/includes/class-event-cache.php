<?php
/**
 * Bulk cache priming for event queries (frontend loop and editor REST preview).
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Event_Cache { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/**
	 * Prime everything the cards read per event, so rendering N events does not cost N queries
	 * per related object: venue posts, featured-image attachments with their meta, and event terms.
	 * Organizers and tags are Pro-only, so Free never loads them.
	 *
	 * Each card resolves its venue via tribe_get_venue_object() (get_post() under the hood) and its
	 * image via wp_get_attachment_image(). The events' own meta is already bulk-loaded by WP_Query,
	 * so reading _EventVenueID is a cache hit.
	 *
	 * @param WP_Query $query Event query.
	 */
	public static function prime_for_query( WP_Query $query ): void {
		$posts = $query->posts;
		if ( ! $posts ) {
			return;
		}

		$ids = [];
		foreach ( $posts as $post ) {
			$venue_id = (int) get_post_meta( $post->ID, '_EventVenueID', true );
			if ( $venue_id ) {
				$ids[] = $venue_id;
			}
		}
		if ( $ids ) {
			_prime_post_caches( array_unique( $ids ), true, true );
		}

		update_post_thumbnail_cache( $query );
		// Second argument is the object (post) type, not a taxonomy list; IDs already cached by WP_Query are skipped.
		update_object_term_cache( wp_list_pluck( $posts, 'ID' ), 'tribe_events' );
	}
}
