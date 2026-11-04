<?php
/**
 * Plugin bootstrap — singleton that registers every hook in one place.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'ELB_Plugin', false ) ) {
	return;
}

final class ELB_Plugin { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/** @var self|null */
	private static $instance;

	/** @var bool Guards against attaching hooks twice (standalone plugin + host bundle). */
	private $hooked = false;

	/**
	 * Bootstrap the plugin on plugins_loaded.
	 */
	public static function boot(): void {
		if ( class_exists( 'Tribe__Events__Main' ) ) {
			self::get_instance()->register_hooks();
			return;
		}
		add_action( 'admin_notices', [ __CLASS__, 'missing_tec_notice' ] );
	}

	/**
	 * Admin notice displayed when The Events Calendar is missing.
	 */
	public static function missing_tec_notice(): void {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		echo '<div class="notice notice-error"><p>' .
			esc_html__( 'Events Loop Builder requires The Events Calendar plugin to be installed and active.', 'template-events-calendar' ) .
			'</p></div>';
	}

	/**
	 * Unique plugin instance.
	 */
	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	private function __clone() {}

	/**
	 * Singletons must not be unserialized.
	 *
	 * @throws \LogicException Always.
	 */
	public function __wakeup(): void {
		throw new \LogicException( 'Cannot unserialize ' . __CLASS__ . '.' );
	}

	/**
	 * Register the autoloader and attach WordPress hooks (boot() runs once).
	 */
	public function register_hooks(): void {
		if ( $this->hooked ) {
			return;
		}
		$this->hooked = true;

		$this->register_autoloader();

		// Registration (init).
		add_action( 'init', [ 'ELB_Assets', 'register' ], 5 );
		add_action( 'init', [ 'ELB_Blocks', 'register_blocks' ] );
		add_filter( 'block_categories_all', [ 'ELB_Blocks', 'register_category' ] );

		// Block rendering (frontend and REST previews).
		// Only ELB blocks are filtered; core and third-party blocks never reach these callbacks.
		foreach ( ELB_Blocks::block_names() as $name ) {
			add_filter( "render_block_{$name}", [ 'ELB_Block_Render_Filters', 'filter_rendered_block' ], 10, 2 );
			if ( 0 === strpos( $name, 'elb/event-' ) ) {
				add_filter( "render_block_{$name}", [ 'ELB_Field_Block', 'filter' ], 20, 2 );
			}
		}
		add_filter( 'render_block_data', [ 'ELB_Block_Render_Filters', 'add_css_before_render' ] );
		add_filter( 'render_block_elb/loop-builder', [ 'ELB_Block_Render_Filters', 'leave_loop' ], 0 );
		// After the filters above, as when they ran on the generic render_block. Enqueues the frontend CSS/JS only when a loop renders.
		add_filter( 'render_block_elb/loop-builder', [ 'ELB_Assets', 'enqueue_loop_assets' ], 21 );

		// REST endpoints.
		add_action( 'rest_api_init', [ 'ELB_REST_Events', 'register' ] );

		// Editor assets (enqueue_block_assets also fires on the frontend).
		add_action( 'enqueue_block_editor_assets', [ 'ELB_Assets', 'add_layout_editor_data' ] );
		add_action( 'enqueue_block_assets', [ 'ELB_Assets', 'enqueue_editor_canvas' ] );
	}

	/**
	 * Load domain and integration classes on first use.
	 */
	private function register_autoloader(): void {
		spl_autoload_register( [ __CLASS__, 'autoload' ] );
	}

	/**
	 * Autoloader: ELB_Some_Class lives in includes/class-some-class.php (ELB_Layout_List_* in includes/layouts/, migration classes in includes/elb-migration/).
	 *
	 * @param string $class_name Requested class.
	 */
	public static function autoload( string $class_name ): void {
		if ( ! preg_match( '/^ELB_[A-Za-z0-9_]+$/', $class_name ) ) {
			return;
		}
		$slug = str_replace( '_', '-', strtolower( substr( $class_name, 4 ) ) );
		// Per-layout code (ELB_Layout_List_3 …) lives in includes/layouts/ and loads only when used.
		$subdir = '';
		if ( 0 === strpos( $slug, 'layout-list-' ) ) {
			$subdir = 'layouts/';
		} elseif ( in_array( $slug, array( 'block-migration', 'block-attribute-mapper', 'block-content-migrator' ), true ) ) {
			$subdir = 'elb-migration/';
		}
		$file = ELB_PATH . 'includes/' . $subdir . 'class-' . $slug . '.php';
		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
}
