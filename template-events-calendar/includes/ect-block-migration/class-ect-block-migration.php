<?php
/**
 * Legacy Event Block to Event Loop Builder migration runtime.
 *
 * Converters stay available for onboarding. Editor convert and frontend
 * fallback start only for existing (old) users.
 *
 * @package ect
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- ECT_ is this plugin's prefix. Plugin Check only accepts prefixes of 4 or more characters.

require_once __DIR__ . '/class-ect-block-attribute-mapper.php';
require_once __DIR__ . '/class-ect-block-content-migrator.php';

if ( ! class_exists( 'ECT_Block_Migration' ) ) {
class ECT_Block_Migration {

	const NEEDED_OPTION = 'ect_elb_block_migration_needed';
	const FOUND_OPTION  = 'ect_elb_legacy_event_block_found';

	private static $instance;

	/** @var array<int,int> Legacy block render count keyed by host post ID. */
	private $frontend_block_indexes = array();

	/** @var bool Whether late legacy-block styles were printed in this request. */
	private $frontend_styles_printed = false;

	/**
	 * True only when an existing site still has the legacy Event Block in content.
	 *
	 * Fresh installs skip this. Old installs without `ebec/event-list` also skip
	 * editor convert and frontend fallback.
	 *
	 * @return bool
	 */
	public static function is_needed() {
		if ( 'no' === get_option( self::NEEDED_OPTION, null ) ) {
			return false;
		}

		$found = get_option( self::FOUND_OPTION, null );
		if ( 'yes' === $found ) {
			return true;
		}
		if ( 'no' === $found ) {
			return false;
		}

		$has_block = self::has_legacy_event_block();
		update_option( self::FOUND_OPTION, $has_block ? 'yes' : 'no', false );
		update_option( self::NEEDED_OPTION, $has_block ? 'yes' : 'no', false );

		return $has_block;
	}

	/**
	 * Whether any post content still contains the legacy Event Block.
	 *
	 * @return bool
	 */
	public static function has_legacy_event_block() {
		global $wpdb;

		$needle  = '%' . $wpdb->esc_like( '<!-- wp:' . ECT_Block_Attribute_Mapper::LEGACY_BLOCK ) . '%';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin migration lookup; LIKE on post_content cannot use WP_Query efficiently.
		$post_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_content LIKE %s AND post_status NOT IN ('trash','auto-draft','inherit') AND post_type NOT IN ('revision','attachment') LIMIT 1",
				$needle
			)
		);

		return ! empty( $post_id );
	}

	public static function get_instance() {
		if ( ! self::is_needed() ) {
			return null;
		}
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Frontend visual parity with the editor preview for already-migrated cards.
	 *
	 * REST / editor convert stay gated by is_needed(). Read-more still
	 * needs a render rewrite because block.json defaults the arrow on.
	 */
	public static function boot_frontend_compat() {
		static $booted = false;
		if ( $booted ) {
			return;
		}
		$booted = true;

		add_filter( 'render_block_data', array( __CLASS__, 'rewrite_migrated_block_data' ), 10, 3 );
	}

	private function __construct() {
		self::boot_frontend_compat();
		add_action( 'rest_api_init', array( $this, 'register_rest_route' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_migration' ), 30 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_migration_frontend_assets' ) );
		add_filter( 'render_block_ebec/event-list', array( $this, 'render_legacy_block' ), 10, 2 );
		add_filter( 'render_block_elb/event-date', array( $this, 'preserve_legacy_date_format' ), 20, 2 );
	}

	/**
	 * Preserve the legacy Event Block's zero-padded day in migrated date rows.
	 *
	 * @param string $content Rendered Event Loop Builder date block.
	 * @param array  $block Parsed date block.
	 * @return string
	 */
	public function preserve_legacy_date_format( $content, array $block ) {
		$class_name = (string) ( $block['attrs']['className'] ?? '' );
		if ( false === strpos( $class_name, 'ect-migrated-legacy-main-date' ) ) {
			return $content;
		}

		return (string) preg_replace(
			'/(<span class="elb-date-mdyt__start">[^<]*\s)([1-9])(,\s*\d{4}<\/span>)/u',
			'${1}0$2$3',
			$content
		);
	}

	/**
	 * Frontend/editor render tweaks for already-converted Event Loop cards.
	 *
	 * - Drop convert-time badge typography locks from saved section CSS so side
	 *   date Typography settings are not beaten by !important month/day rules.
	 * - Keep migrated read-more links matching the editor (no default arrow).
	 *
	 * @param array               $parsed_block Parsed block.
	 * @param array               $source_block Original block.
	 * @param array|WP_Block|null $parent_block Parent block.
	 * @return array
	 */
	public static function rewrite_migrated_block_data( $parsed_block, $source_block = array(), $parent_block = null ) {
		if ( ! is_array( $parsed_block ) ) {
			return $parsed_block;
		}

		$name       = (string) ( $parsed_block['blockName'] ?? '' );
		$class_name = (string) ( $parsed_block['attrs']['className'] ?? '' );
		$css        = $parsed_block['attrs']['elbFrontendCss'] ?? '';

		if (
			'elb/flex-container' === $name
			&& is_string( $css )
			&& '' !== $css
			&& false !== strpos( $class_name, 'ect-migrated-legacy-section' )
		) {
			$parsed_block['attrs']['elbFrontendCss'] = (string) preg_replace(
				'/[^{}]*\.ect-migrated-legacy-date-badge\s+\.elb-date-badge__(?:month|day)[^{]*\{[^{}]*\}/',
				'',
				$css
			);
		}

		$parent_attrs = array();
		if ( $parent_block instanceof WP_Block ) {
			$parent_attrs = is_array( $parent_block->parsed_block['attrs'] ?? null ) ? $parent_block->parsed_block['attrs'] : array();
		} elseif ( is_array( $parent_block ) ) {
			$parent_attrs = is_array( $parent_block['attrs'] ?? null ) ? $parent_block['attrs'] : array();
		}
		$parent_class = (string) ( $parent_attrs['className'] ?? '' );
		$in_migrated  = false !== strpos( $class_name, 'ect-migrated-legacy' ) || false !== strpos( $parent_class, 'ect-migrated-legacy' );

		if ( 'elb/event-read-more' === $name && ( $in_migrated || false !== strpos( $class_name, 'ect-migrated-legacy-read-more' ) ) ) {
			$parsed_block['attrs']['showBlockIcon'] = false;
			$parsed_block['attrs']['blockIcon']     = '';
		}

		return $parsed_block;
	}

	/**
	 * Register the authenticated editor conversion endpoint.
	 */
	public function register_rest_route() {
		register_rest_route(
			'ect/v1',
			'/event-block-migration/convert',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'convert_editor_block' ),
				'permission_callback' => array( $this, 'can_convert_editor_block' ),
				'args'                => array(
					'postId'     => array(
						'type'     => 'integer',
						'required' => true,
					),
					'blockIndex' => array(
						'type'     => 'integer',
						'required' => true,
					),
					'attributes' => array(
						'type'     => 'object',
						'required' => true,
					),
				),
			)
		);
	}

	/**
	 * Check whether the current user can edit the requested post.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool
	 */
	public function can_convert_editor_block( WP_REST_Request $request ) {
		return current_user_can( 'edit_post', absint( $request->get_param( 'postId' ) ) );
	}

	/**
	 * Convert one legacy attribute bag for the block editor.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function convert_editor_block( WP_REST_Request $request ) {
		$attributes = $request->get_param( 'attributes' );
		if ( ! is_array( $attributes ) ) {
			return new WP_Error( 'ect_invalid_legacy_attributes', __( 'The legacy Event Block attributes are invalid.', 'template-events-calendar' ), array( 'status' => 400 ) );
		}

		$result = ECT_Block_Content_Migrator::from_legacy_attributes(
			$attributes,
			absint( $request->get_param( 'postId' ) ),
			max( 1, absint( $request->get_param( 'blockIndex' ) ) )
		);
		if ( ! $result['supported'] ) {
			return new WP_Error(
				'ect_unsupported_legacy_block',
				! empty( $result['warnings'] ) ? implode( ' ', $result['warnings'] ) : __( 'This Event Block cannot be converted automatically.', 'template-events-calendar' ),
				array( 'status' => 422 )
			);
		}

		return rest_ensure_response( array( 'content' => $result['content'] ) );
	}

	/**
	 * Load the hidden legacy-block shim only inside the block editor.
	 */
	public function enqueue_editor_migration() {
		if ( ! wp_script_is( 'elb-editor', 'registered' ) || ! wp_script_is( 'elb-layout-blocks-editor', 'registered' ) ) {
			return;
		}

		$handle = 'ect-event-block-editor-migration';
		wp_enqueue_script(
			$handle,
			ECT_PLUGIN_URL . 'assets/js/event-block-editor-migration.js',
			array( 'wp-api-fetch', 'wp-blocks', 'wp-block-editor', 'wp-components', 'wp-data', 'wp-element', 'wp-i18n', 'elb-layout-blocks-editor', 'elb-editor' ),
			ECT_VERSION,
			true
		);
		wp_localize_script(
			$handle,
			'ectEventBlockMigration',
			array(
				'endpoint'    => '/ect/v1/event-block-migration/convert',
				'title'       => __( 'Legacy Event Block', 'template-events-calendar' ),
				'description' => __( 'This saved Event Block is being converted to Event Loop Builder automatically.', 'template-events-calendar' ),
				'converting'  => __( 'Converting...', 'template-events-calendar' ),
				'invalid'     => __( 'The converted Event Loop Builder block could not be created.', 'template-events-calendar' ),
			)
		);
	}

	/**
	 * Enqueue ELB styles for migrated or legacy blocks before the theme prints them.
	 *
	 * Some themes do not print styles first enqueued while post content is rendering,
	 * so migration-related ELB assets are detected from the main query here.
	 */
	public function enqueue_migration_frontend_assets() {
		global $wp_query;

		if ( is_admin() || ! class_exists( 'ELB_Assets' ) ) {
			return;
		}

		$has_legacy_block = false;
		$elb_content      = '';
		$posts            = ! empty( $wp_query->posts ) ? $wp_query->posts : array();
		$queried_post     = get_queried_object();
		if ( $queried_post instanceof WP_Post && ! in_array( $queried_post, $posts, true ) ) {
			$posts[] = $queried_post;
		}

		foreach ( $posts as $post ) {
			if ( ! $post instanceof WP_Post ) {
				continue;
			}
			if ( has_block( ECT_Block_Attribute_Mapper::LEGACY_BLOCK, $post->post_content ) ) {
				$has_legacy_block = true;
			}
			$has_target_block     = has_block( ECT_Block_Attribute_Mapper::TARGET_BLOCK, $post->post_content );
			$has_migrated_markup = has_block( 'elb/flex-container', $post->post_content ) && false !== strpos( $post->post_content, 'ect-migrated-' );
			if ( $has_target_block || $has_migrated_markup ) {
				$elb_content .= "\n" . $post->post_content;
			}
		}
		if ( ! $has_legacy_block && '' === $elb_content ) {
			return;
		}

		if ( ! wp_style_is( 'elb-frontend', 'registered' ) ) {
			ELB_Assets::register();
		}

		ELB_Assets::enqueue_frontend_runtime();
		wp_enqueue_style( 'elb-layout-blocks' );
		wp_enqueue_style( 'elb-frontend' );
		wp_enqueue_script( 'elb-frontend' );
		if ( '' !== $elb_content ) {
			ELB_Layout_Styles::enqueue_layout_styles_for( $elb_content );
			foreach ( array_keys( ELB_Layout_Styles::LAYOUT_STYLES ) as $layout ) {
				wp_enqueue_style( 'elb-layout-' . $layout );
			}
		}
		if ( $has_legacy_block ) {
			wp_enqueue_style( 'elb-layout-list-3' );
			wp_enqueue_style( 'elb-layout-list-4' );
		}
		ELB_Assets::enqueue_loop_assets( '' );
		wp_enqueue_style( 'elb-theme-compat' );
	}

	/**
	 * Render unmigrated legacy blocks through Event Loop Builder on the frontend.
	 *
	 * This compatibility path does not write post content. Opening the post in
	 * the editor converts the saved Event Block automatically.
	 *
	 * @param string $content Existing rendered content.
	 * @param array  $block Parsed block.
	 * @return string
	 */
	public function render_legacy_block( $content, array $block ) {
		if ( ! WP_Block_Type_Registry::get_instance()->is_registered( ECT_Block_Attribute_Mapper::TARGET_BLOCK ) ) {
			return $content;
		}

		$post_id = get_the_ID();
		if ( ! isset( $this->frontend_block_indexes[ $post_id ] ) ) {
			$this->frontend_block_indexes[ $post_id ] = 0;
		}
		++$this->frontend_block_indexes[ $post_id ];

		$result = ECT_Block_Content_Migrator::from_legacy_attributes(
			is_array( $block['attrs'] ?? null ) ? $block['attrs'] : array(),
			$post_id,
			$this->frontend_block_indexes[ $post_id ]
		);
		if ( ! $result['supported'] ) {
			return $content;
		}

		$blocks = parse_blocks( $result['content'] );
		if ( 1 !== count( $blocks ) || ECT_Block_Attribute_Mapper::TARGET_BLOCK !== ( $blocks[0]['blockName'] ?? '' ) ) {
			return $content;
		}

		$rendered = render_block( $blocks[0] );

		return $this->print_pending_frontend_styles() . $rendered;
	}

	/**
	 * Print migration styles beside the first legacy fallback when wp_head has passed.
	 *
	 * This is a last-resort compatibility path for themes or optimizers that do not
	 * print styles enqueued while a dynamic block is rendered.
	 *
	 * @return string Stylesheet tags, or an empty string when already printed.
	 */
	private function print_pending_frontend_styles() {
		if ( $this->frontend_styles_printed || ! did_action( 'wp_head' ) ) {
			return '';
		}

		$handles = array(
			'elb-layout-blocks',
			'elb-frontend',
			'elb-layout-list-3',
			'elb-layout-list-4',
			'elb-theme-compat',
		);
		$pending = array();
		foreach ( $handles as $handle ) {
			wp_enqueue_style( $handle );
			if ( ! wp_style_is( $handle, 'done' ) ) {
				$pending[] = $handle;
			}
		}
		if ( empty( $pending ) ) {
			$this->frontend_styles_printed = true;
			return '';
		}

		ob_start();
		wp_print_styles( $pending );
		$styles                        = (string) ob_get_clean();
		$this->frontend_styles_printed = true;

		return $styles;
	}
}
}
