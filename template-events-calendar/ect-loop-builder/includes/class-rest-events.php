<?php
/**
 * REST API — Events query endpoints for events-test plugin.
 * Namespace: elb/v1 (distinct from eclb/v1 used by events-builder-loop-builder).
 *
 * Endpoints:
 *   GET /elb/v1/filter-options  — returns categories
 */
defined( 'ABSPATH' ) || exit;

class ELB_REST_Events { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	public static function register() {
		( new self() )->register_routes();
	}

	public function register_routes() {
		register_rest_route(
			'elb/v1',
			'/filter-options',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_filter_options' ],
				'permission_callback' => [ $this, 'check_permission' ],
			]
		);

		register_rest_route(
			'elb/v1',
			'/loop-query',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_loop_query_events' ],
				'permission_callback' => [ $this, 'check_permission' ],
				'args'                => self::loop_query_args(),
			]
		);

		register_rest_route(
			'elb/v1',
			'/event/(?P<id>\d+)',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_single_event' ],
				'permission_callback' => [ $this, 'check_permission' ],
				'args'                => [
					'id' => [
						'required'          => true,
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && (int) $param > 0;
						},
					],
				],
			]
		);
	}

	/**
	 * Declared args for /elb/v1/loop-query; values are clamped / whitelisted in loop_query_attributes().
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private static function loop_query_args(): array {
		$args = [
			'perPage' => [ 'type' => 'integer', 'default' => 6 ],
		];
		foreach ( [ 'order', 'orderBy', 'showEvents', 'eventsTime', 'dateFrom', 'dateTo', 'categoryIds' ] as $name ) {
			$args[ $name ] = [ 'type' => 'string' ];
		}
		return $args;
	}

	public function check_permission() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Map WP_Term list to { value, label } options.
	 *
	 * @param WP_Term[]|WP_Error|mixed $terms Terms.
	 * @return array<int,array{value:int,label:string}>
	 */
	private function map_terms_to_options( $terms ): array {
		$out = [];
		if ( is_wp_error( $terms ) || empty( $terms ) || ! is_array( $terms ) ) {
			return $out;
		}
		foreach ( $terms as $t ) {
			if ( ! $t instanceof WP_Term ) {
				continue;
			}
			$out[] = [
				'value' => (int) $t->term_id,
				'label' => ELB_Category::editor_term_name( $t ),
			];
		}
		return $out;
	}

	/**
	 * GET /elb/v1/filter-options
	 * Returns categories for the Filter Events panel (Tags/Venues/Organizers filters are Pro-only).
	 */
	public function get_filter_options() {
		$response = [ 'categories' => [] ];

		if ( taxonomy_exists( ELB_Event_Repository::CATEGORY_TAXONOMY ) ) {
			$response['categories'] = $this->map_terms_to_options(
				get_terms(
					[
						'taxonomy'   => ELB_Event_Repository::CATEGORY_TAXONOMY,
						'hide_empty' => false,
						'number'     => 200,
					]
				)
			);
		}

		return new WP_REST_Response( $response, 200 );
	}

	/**
	 * GET /elb/v1/loop-query
	 * Returns events for the loop-builder editor (same shape as /elb/v1/events).
	 */
	public function get_loop_query_events( $request ) {
		$attributes = $this->loop_query_attributes( $request );

		$query = ELB_Event_Query_Builder::query( $attributes );
		if ( ! $query ) {
			return new WP_REST_Response( [ 'events' => [], 'count' => 0, 'noResults' => true ], 200 );
		}
		$events     = [];
		$no_results = ! $query->have_posts();

		// Editor-only fallback: if filters return nothing, still return some events
		// (flagged via noResults) so field previews keep event data to work with.
		if ( $no_results ) {
			$query = ELB_Event_Query_Builder::query(
				[
					'perPage'       => $attributes['perPage'],
					'eventsOrder'   => $attributes['eventsOrder'],
					'eventsOrderBy' => $attributes['eventsOrderBy'],
					'showEvents'    => 'both',
					'eventsTime'    => 'all',
				]
			);
		}

		if ( $query ) {
			ELB_Event_Cache::prime_for_query( $query );
		}
		while ( $query && $query->have_posts() ) {
			$query->the_post();
			$events[] = ELB_Event_Editor_Data::format_event_for_editor( get_the_ID() );
		}
		wp_reset_postdata();

		return new WP_REST_Response(
			[
				'events'    => $events,
				'count'     => count( $events ),
				'noResults' => $no_results,
			],
			200
		);
	}

	/**
	 * Turn the editor's query-string parameters into the attributes the query builder reads.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return array<string,mixed>
	 */
	private function loop_query_attributes( $request ): array {
		// Split comma-separated ID strings; ELB_Event_Query_Builder::extract_ids() does the clean-up.
		$parse_ids = static function ( $param ) use ( $request ) {
			$raw = trim( (string) $request->get_param( $param ) );
			return '' === $raw ? [] : explode( ',', $raw );
		};

		return [
			'perPage'            => ELB_Event_Query_Builder::per_page( [ 'perPage' => $request->get_param( 'perPage' ) ] ),
			'eventsOrder'        => (string) $request->get_param( 'order' ),
			'eventsOrderBy'      => (string) $request->get_param( 'orderBy' ),
			'showEvents'         => sanitize_key( $request->get_param( 'showEvents' ) ?: 'both' ),
			'eventsTime'         => sanitize_key( $request->get_param( 'eventsTime' ) ?: 'all' ),
			'dateFrom'           => sanitize_text_field( (string) $request->get_param( 'dateFrom' ) ),
			'dateTo'             => sanitize_text_field( (string) $request->get_param( 'dateTo' ) ),
			'selectedCategories' => $parse_ids( 'categoryIds' ),
		];
	}

	/**
	 * GET /elb/v1/event/{id}
	 * Single formatted event for block editor previews.
	 */
	public function get_single_event( $request ) {
		$id   = (int) $request['id'];
		$post = get_post( $id );

		if (
			! $post
			|| 'tribe_events' !== $post->post_type
			|| ! current_user_can( 'read_post', $id )
			|| post_password_required( $post )
		) {
			return new WP_Error( 'elb_forbidden', __( 'Not allowed.', 'template-events-calendar' ), [ 'status' => 403 ] );
		}

		$event = ELB_Event_Editor_Data::format_event_for_editor( $id );
		if ( empty( $event ) ) {
			return new WP_Error( 'elb_event_not_found', __( 'Event not found.', 'template-events-calendar' ), [ 'status' => 404 ] );
		}
		return new WP_REST_Response( $event, 200 );
	}
}
