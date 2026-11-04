<?php
/**
 * Script and style registration / enqueue.
 *
 * @package ELB
 */

defined( 'ABSPATH' ) || exit;

final class ELB_Assets { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Events Loop Builder public API uses the ELB_ prefix.

	/** @var bool */
	private static bool $frontend_done = false;

	/**
	 * Whether the current request is an admin screen (and not an AJAX call).
	 *
	 * @return bool True on wp-admin screens outside AJAX.
	 */
	public static function is_admin_screen(): bool {
		return is_admin() && ! wp_doing_ajax();
	}

	/**
	 * Inline bootstrap that sets one entry of the plugin's single browser namespace,
	 * window.elb = { blocksInfo, editor, share } (read in JS through source/elb-globals.js).
	 *
	 * @param string              $key  Namespace entry (camelCase).
	 * @param array<string,mixed> $data Data to expose.
	 * @return string JavaScript.
	 */
	private static function global_script( string $key, array $data ): string {
		return 'window.elb=window.elb||{};window.elb.' . $key . '=' . wp_json_encode( $data ) . ';';
	}

	/**
	 * Register every script and style the plugin uses (enqueued later, only where needed).
	 *
	 * @return void
	 */
	public static function register(): void {
		self::register_editor_assets();
		self::register_layout_block_assets();
		self::register_frontend_assets();
	}

	/**
	 * Built asset metadata (dependencies, version) from a wp-scripts *.asset.php file.
	 *
	 * @param string   $path         Absolute path of the .asset.php file.
	 * @param string[] $default_deps Dependencies used when the file is missing.
	 * @return array{dependencies:string[],version:string}
	 */
	private static function load_asset_meta( string $path, array $default_deps ): array {
		$asset  = is_readable( $path ) ? include $path : null;
		$asset  = is_array( $asset ) ? $asset : [ 'dependencies' => $default_deps, 'version' => ELB_VER ];
		$bundle = preg_replace( '/\.asset\.php$/', '.js', $path );
		if ( is_readable( $bundle ) ) {
			$asset['version'] .= '-' . (string) filemtime( $bundle );
		}
		return $asset;
	}

	/**
	 * Loop editor bundle (field blocks and the loop builder) with its data and style.
	 */
	private static function register_editor_assets(): void {
		$asset = self::load_asset_meta(
			ELB_PATH . 'build/loop/index.asset.php',
			[ 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-data', 'wp-hooks' ]
		);

		wp_register_script(
			'elb-editor',
			ELB_URL . 'build/loop/index.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);
		wp_set_script_translations( 'elb-editor', 'template-events-calendar' );
		wp_add_inline_script(
			'elb-editor',
			self::global_script( 'editor', [ 'addEventUrl' => admin_url( 'post-new.php?post_type=tribe_events' ) ] ),
			'before'
		);

		if ( is_readable( ELB_PATH . 'build/loop/index.css' ) ) {
			wp_register_style(
				'elb-editor',
				ELB_URL . 'build/loop/index.css',
				[ 'wp-edit-blocks' ],
				$asset['version']
			);
		}
	}

	/**
	 * Layout blocks editor bundle (elb/flex-container, elb/wrapper); editorScript in their block.json.
	 */
	private static function register_layout_block_assets(): void {
		$layout_asset = self::load_asset_meta(
			ELB_PATH . 'build/layout/elb-blocks.asset.php',
			[ 'react', 'react-jsx-runtime', 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-compose', 'wp-i18n', 'wp-data', 'wp-hooks' ]
		);
		wp_register_script(
			'elb-layout-blocks-editor',
			ELB_URL . 'build/layout/elb-blocks.js',
			$layout_asset['dependencies'],
			$layout_asset['version'],
			true
		);
		wp_set_script_translations( 'elb-layout-blocks-editor', 'template-events-calendar' );
	}

	/**
	 * Frontend styles (base, per-layout, layout blocks, theme compat) and the frontend script.
	 */
	private static function register_frontend_assets(): void {
		wp_register_style( 'elb-frontend', ELB_URL . 'assets/css/frontend.css', [], ELB_VER );
		foreach ( array_keys( ELB_Layout_Styles::LAYOUT_STYLES ) as $key ) {
			wp_register_style( 'elb-layout-' . $key, ELB_URL . 'assets/css/layouts/' . $key . '.css', [ 'elb-frontend' ], ELB_VER );
		}
		wp_register_style( 'elb-layout-blocks', ELB_URL . 'build/layout/elb-blocks.css', [], ELB_VER );
		wp_register_style( 'elb-theme-compat', ELB_URL . 'assets/css/theme-compat.css', [ 'elb-frontend' ], ELB_VER );
		$frontend_asset = self::load_asset_meta( ELB_PATH . 'build/frontend/frontend.asset.php', [] );
		wp_register_script(
			'elb-frontend',
			ELB_URL . 'build/frontend/frontend.js',
			$frontend_asset['dependencies'],
			$frontend_asset['version'],
			[
				'in_footer' => true,
				'strategy'  => 'defer',
			]
		);
	}

	/**
	 * Settings for the layout blocks editor script (loaded via editorScript in their block.json).
	 * The layout JS registers elb/flex-container and elb/wrapper on the client.
	 *
	 * @return void
	 */
	public static function add_layout_editor_data(): void {
		wp_add_inline_script( 'elb-layout-blocks-editor', self::global_script( 'blocksInfo', self::editor_blocks_info() ), 'before' );
	}

	/**
	 * Settings the layout blocks script reads from window.elb.blocksInfo.
	 *
	 * @return array<string,mixed>
	 */
	private static function editor_blocks_info(): array {
		$content_width = isset( $GLOBALS['content_width'] ) ? (int) $GLOBALS['content_width'] : 1200;
		return [
			'content_width'              => $content_width,
			'elb_plugin_url'             => ELB_URL . 'assets/',
			'events_calendar_pro_active' => ELB_Tec_Pro::active(),
			'features'                   => [
				'upgradeUrl' => defined( 'ELB_UPGRADE_URL' ) ? ELB_UPGRADE_URL : '',
			],
		];
	}

	/**
	 * Enqueue layout-block frontend styles in the editor canvas only.
	 *
	 * @return void
	 */
	public static function enqueue_editor_canvas(): void {
		if ( ! is_admin() ) {
			return;
		}

		wp_enqueue_style( 'elb-layout-blocks' );
		wp_enqueue_style( 'elb-frontend' );
		ELB_Layout_Styles::enqueue_all_layout_styles();
	}

	/**
	 * Register + enqueue public CSS/JS once per request.
	 *
	 * @return void
	 */
	public static function enqueue_frontend_runtime(): void {
		if ( self::is_admin_screen() ) {
			return;
		}

		if ( self::$frontend_done ) {
			return;
		}
		self::$frontend_done = true;

		wp_enqueue_style( 'elb-layout-blocks' );
		wp_enqueue_style( 'elb-frontend' );
		$elb_share = [
			'i18n' => [
				'copied'       => __( 'Copied!', 'template-events-calendar' ),
				'copyThisLink' => __( 'Copy this link:', 'template-events-calendar' ),
			],
		];
		wp_add_inline_script( 'elb-frontend', self::global_script( 'share', $elb_share ), 'before' );
	}

	/**
	 * Enqueue the loop's frontend CSS/JS and isolation CSS when it renders
	 * (render_block_elb/loop-builder). Inner ELB blocks enqueue the same runtime
	 * and their layout stylesheet from ELB_Block_Render_Filters::filter_rendered_block, so a
	 * page without the block loads nothing. Covers synced patterns and do_blocks().
	 *
	 * @param string $content Rendered loop HTML.
	 * @return string Unchanged loop HTML (assets are enqueued as a side effect).
	 */
	public static function enqueue_loop_assets( $content ) {
		self::enqueue_frontend_runtime();
		// Theme compat prints after the container CSS (elb-dynamic) and before scoped/field CSS.
		ELB_Inline_Css::register_inline_chain();
		ELB_Layout_Styles::add_style_dep( 'elb-theme-compat', 'elb-dynamic' );
		wp_enqueue_style( 'elb-theme-compat' );
		ELB_Layout_Styles::print_inline_after( 'elb-theme-compat', [ 'elb-dynamic' ] );
		return $content;
	}
}
