<?php
/*
Plugin Name:Events Shortcodes For The Events Calendar
Plugin URI:https://eventscalendaraddons.com/plugin/events-shortcodes-pro/?utm_source=ect_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=plugin_uri
Description:<a href="http://wordpress.org/plugins/the-events-calendar/">📅 The Events Calendar Addon</a> - Shortcodes to show The Events Calendar plugin events list on any page or post in different layouts.
Version:2.6.6
Requires PHP:7.2
Author:Cool Plugins
Author URI: https://coolplugins.net/?utm_source=ect_plugin&utm_medium=inside&utm_campaign=author_page&utm_content=plugins_list
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: template-events-calendar
Requires Plugins: the-events-calendar
*/

if (! defined('ABSPATH')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}
if (! defined('ECT_VERSION')) {
	define('ECT_VERSION', '2.6.6');//phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
}

/*** Defined constent for later use */
define('ECT_PLUGIN_URL', plugin_dir_url(__FILE__));//phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
define('ECT_PLUGIN_DIR', plugin_dir_path(__FILE__));//phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
define('ECT_FEEDBACK_URL','https://feedback.coolplugins.net/');//phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound

/*** EventsCalendarTemplates main class by CoolPlugins.net */
//phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
if (! class_exists('EventsCalendarTemplates')) {
	final class EventsCalendarTemplates
	{

		/**
		 * The unique instance of the plugin.
		 */
		private static $instance;

		/**
		 * Gets an instance of our plugin.
		 */
		public static function get_instance()
		{
			if (null === self::$instance) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 */
		private function __construct() {}

		// register all hooks
		public function registers()
		{
			
			if (file_exists(plugin_dir_path(__DIR__) . 'the-events-calendar-templates-and-shortcode/the-events-calendar-templates-and-shortcode.php')) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
				if (is_plugin_active('the-events-calendar-templates-and-shortcode/the-events-calendar-templates-and-shortcode.php')) {
					deactivate_plugins(plugin_basename(__FILE__));
					return;
				}
			}


			/*** Installation and uninstallation hooks */
			register_activation_hook(__FILE__, array('EventsCalendarTemplates', 'activate'));
			register_deactivation_hook(__FILE__, array('EventsCalendarTemplates', 'deactivate'));
			 
			add_action('admin_init', array(self::$instance, 'ect_settings_migration'));
			add_action('admin_init', array(self::$instance, 'onInit'));
			add_action('activated_plugin', array(self::$instance, 'ect_plugin_redirection'));

			/*** Check The Event Calendar is installed or not */
			add_action('plugins_loaded', array(self::$instance, 'ect_check_event_calender_installed'));

			/*** Load required files */
			add_action('plugins_loaded', array(self::$instance, 'ect_load_files'));
			add_action('init', array(self::$instance, 'ect_load_textdomain'));
			add_action('admin_enqueue_scripts', array(self::$instance, 'ect_tc_css'));
			/*** Template Setting Page Link */
			add_filter('plugin_action_links_' . plugin_basename(__FILE__), array(self::$instance, 'ect_template_settings_page'));
			add_action('plugin_row_meta', array(self::$instance, 'ect_addMeta_Links'), 10, 2);
			foreach (array('post.php', 'post-new.php') as $hook) {
				add_action("admin_head-$hook", array(self::$instance, 'ect_rest_url'));
			}

			/*** Include Gutenberg Block */
			require_once ECT_PLUGIN_DIR . 'admin/gutenberg-block/ect-block.php';

			/***Include Share Buttons*/
			require_once ECT_PLUGIN_DIR . '/includes/ect-share-functions.php';
			$this->cpfm_load_files();
			add_action('admin_print_scripts', [$this, 'ect_hide_unrelated_notices']);
		}

		public function ect_hide_unrelated_notices(){ 
			
			// phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded, Generic.Metrics.NestingLevel.MaxExceeded
            $events_pages = false;
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Checking page parameter to conditionally hide notices, no data processing
            if (isset($_GET['page'])) {
				
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Checking page parameter to conditionally hide notices, no data processing
				$page_param = sanitize_key( wp_unslash( $_GET['page'] ) );

				$allowed_pages = array(
					'cool-plugins-events-addon',
					'cool-events-registration',
					'tribe-events-shortcode-template-settings',
					'tribe_events-events-template-settings',
					'countdown_for_the_events_calendar',
					'esas-speaker-sponsor-settings',
					'esas_speaker',
					'esas_sponsor',
					'ewpe',
					'epta'
				);

				if (in_array($page_param, $allowed_pages, true)) {
					$events_pages = true;
				}
            }
			$is_post_type_page = false;

			$current_screen = get_current_screen();
			
			if ( $current_screen && ! empty( $current_screen->post_type ) ) {
			
				$allowed_post_types = array(
					'esas_speaker',
					'esas_sponsor',
					'epta',
					'ewpe'
				);
			
				if ( in_array( $current_screen->post_type, $allowed_post_types, true ) ) {
					$is_post_type_page = true;
				}
			}
            if ($events_pages) {
                global $wp_filter;
                // Define rules to remove callbacks.
                $rules = [
                    'user_admin_notices' => [], // remove all callbacks.
                    'admin_notices'      => [],
                    'all_admin_notices'  => [],
                    'admin_footer'       => [
                        'render_delayed_admin_notices', // remove this particular callback.
                    ],
                ];
                $notice_types = array_keys($rules);
                foreach ($notice_types as $notice_type) {
                    if (empty($wp_filter[$notice_type]) || empty($wp_filter[$notice_type]->callbacks) || ! is_array($wp_filter[$notice_type]->callbacks)) {
                        continue;
                    }
                    $remove_all_filters = empty($rules[$notice_type]);
                    foreach ($wp_filter[$notice_type]->callbacks as $priority => $hooks) {
                        foreach ($hooks as $name => $arr) {
                            if (is_object($arr['function']) && is_callable($arr['function'])) {
                                if ($remove_all_filters) {
                                    unset($wp_filter[$notice_type]->callbacks[$priority][$name]);
                                }
                                continue;
                            }
                            $class = ! empty($arr['function'][0]) && is_object($arr['function'][0]) ? strtolower(get_class($arr['function'][0])) : '';
                            // Remove all callbacks except WPForms notices.
                            if ($remove_all_filters && strpos($class, 'wpforms') === false) {
                                unset($wp_filter[$notice_type]->callbacks[$priority][$name]);
                                continue;
                            }
                            $cb = is_array($arr['function']) ? $arr['function'][1] : $arr['function'];
                            // Remove a specific callback.
                            if (! $remove_all_filters) {
                                if (in_array($cb, $rules[$notice_type], true)) {
                                    unset($wp_filter[$notice_type]->callbacks[$priority][$name]);
                                }
                                continue;
                            }
                        }
                    }
                }
            }
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
			if (!$events_pages && !$is_post_type_page) {

				// ✅ GLOBAL LOCK SYSTEM
				if (!defined('ECT_ADMIN_NOTICE_HOOKED')) {

					define('ECT_ADMIN_NOTICE_HOOKED', true);

					add_action(
						'admin_notices',
						array($this, 'ect_dash_admin_notices'),
						PHP_INT_MAX
					);
				}
			}
        }
		
		public function ect_dash_admin_notices() {

			// ✅ Double render protection
			if (defined('ECT_ADMIN_NOTICE_RENDERED')) {
				return;
			}

			define('ECT_ADMIN_NOTICE_RENDERED', true);

			do_action('ect_display_admin_notices');
		}
		
		/*** Load Text domain */
		//phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		public function ect_load_textdomain()
		{
			load_plugin_textdomain('template-events-calendar', false, basename(dirname(__FILE__)) . '/languages/');
			if (!get_option('ect-initial-save-version')) {
				add_option('ect-initial-save-version', ECT_VERSION);
			}
			if (!get_option('ect-install-date')) {
				add_option('ect-install-date', gmdate('Y-m-d h:i:s'));
			}
		}

		public function cpfm_load_files() {
			require_once ECT_PLUGIN_DIR . 'admin/cpfm-feedback/cron/class-cron.php';
		}
		/*** Load required files */
		public function ect_load_files()
		{
			
			
			if (class_exists('Tribe__Events__Main') or defined('Tribe__Events__Main::VERSION')) {
				if (defined('WPB_VC_VERSION')) {
					require_once ECT_PLUGIN_DIR . 'admin/visual-composer/ect-class-vc.php';
				}
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
				if (! is_plugin_active('events-block-for-the-events-calendar/events-block-for-the-event-calender.php')) {
					require ECT_PLUGIN_DIR . '/includes/events-shortcode-block/includes/ebec-functions.php';
					require ECT_PLUGIN_DIR . '/includes/events-shortcode-block/includes/ebec-block.php';
				}
			}

			if (is_admin()) {
				/*** Plugin review notice file */
				require_once ECT_PLUGIN_DIR . 'admin/marketing/ect-marketing.php';
				require_once ECT_PLUGIN_DIR . '/admin/feedback-notice/feedback-notice.php';
				require_once ECT_PLUGIN_DIR . '/admin/feedback/admin-feedback-form.php';
				require_once ECT_PLUGIN_DIR . 'admin/cpfm-feedback/cron/class-cron.php';
				
				if (!class_exists('CPFM_Feedback_Notice')) {
					require_once ECT_PLUGIN_DIR . '/admin/cpfm-feedback/cpfm-feedback-notice.php';
				}


				add_action('cpfm_register_notice', function () {

					if (!class_exists('CPFM_Feedback_Notice') || !current_user_can('manage_options')) {
						return;
					}
					$notice = [

						'title' => __('Events Addons By Cool Plugins', 'template-events-calendar'),
						'message' => __('Help us make this plugin more compatible with your site by sharing non-sensitive site data.', 'template-events-calendar'),
						'pages' => ['cool-plugins-events-addon', 'tribe_events-events-template-settings'],
						'always_show_on' => ['cool-plugins-events-addon', 'tribe_events-events-template-settings'], // This enables auto-show
						'plugin_name' => 'ect',

					];

					CPFM_Feedback_Notice::cpfm_register_notice('cool_events', $notice);

					if (!isset($GLOBALS['cool_plugins_feedback'])) {
						$GLOBALS['cool_plugins_feedback'] = [];
					}

					$GLOBALS['cool_plugins_feedback']['cool_events'][] = $notice;
				});

				add_action('cpfm_after_opt_in_ect', function ($category) {

					$ects_options = get_option('ects_options');


					if ($category === 'cool_events') {

						ECT_cronjob::ect_send_data();
						$ects_options['ect_cpfm_feedback_data'] = true;
						update_option('ects_options', $ects_options);
						
					}
				});

				require_once __DIR__ . '/admin/events-addon-page/events-addon-page.php';
				cool_plugins_events_addon_settings_page('the-events-calendar', 'cool-plugins-events-addon', '📅 Events Addons For The Events Calendar');

				require_once ECT_PLUGIN_DIR . 'admin/codestar-framework/codestar-framework.php';
				require_once ECT_PLUGIN_DIR . 'admin/ect-codestar-settings.php';
				$settings_panel = new ECTSettings();
			}

			/*** Include helpers functions*/
			require_once ECT_PLUGIN_DIR . 'includes/ect-functions.php';

			require_once ECT_PLUGIN_DIR . 'includes/events-shortcode.php';
			EventsShortcode::registers();
			require_once ECT_PLUGIN_DIR . 'admin/ect-event-shortcode.php';
		}
		//phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralText
		public static function onInit()
		{
			if (version_compare(get_option('ect-v'), '2.4.0', '<')) {
				ect_create_admin_notice(
					array(
						'id'              => 'ect-pro-setting-change',
						'message' => wp_kses(
										sprintf(
											/* translators: %s: Settings page URL. */
											__(
												'<strong>Major design update</strong> for <strong>Events Shortcodes</strong> plugin in version 2.4.0! Update or reset <a href="%s">style settings</a> if you face any design issues.',
												'template-events-calendar'
											),
											esc_url( admin_url( 'admin.php?page=tribe_events-events-template-settings' ) )
										),
										array(
											'a'      => array(
												'href' => array(),
											),
											'strong' => array(),
										)
									),
						'review_interval' => 0,
					)
				);
			}

			if (version_compare(get_option('ect-v'), '1.8', '<')) {
				ect_create_admin_notice(
					array(
						'id'              => 'ect-free-setting-migration',
						'message'         => wp_kses_post(__('<strong>Important Update</strong>:- <strong>Events Shortcodes & Templates</strong> plugin has integrated new settings panel. Please save your settings and check events views.', 'template-events-calendar')),
						'review_interval' => 0,
					)
				);
			}
			/*** Plugin review notice file */
			ect_create_admin_notice(
				array(
					'id'              => 'ect_review_box',  // required and must be unique
					'slug'            => 'ect',      // required in case of review box
					'review'          => true,     // required and set to be true for review box
					'review_url'      => esc_url('https://wordpress.org/support/plugin/template-events-calendar/reviews/'), // required
					'plugin_name'     => 'Events Shortcodes  Addon',    // required
					'review_interval' => 3,                    // optional: this will display review notice
					// after 5 days from the installation_time	
					// default is 3
				)
			);
		}

		public function shortcodes_submenu()
		{
			add_submenu_page('cool-plugins-events-addon', 'Shortcodes & Template', '<strong>Shortcodes & Template</strong>', 'manage_options', 'admin.php?page=tribe_events-events-template-settings', false, 15);
		}

		/*** Check The Events calender is installled or not. If user has not installed yet then show notice */
		public function ect_check_event_calender_installed()
		{
			if (! class_exists('Tribe__Events__Main') or ! defined('Tribe__Events__Main::VERSION')) {
				add_action('ect_display_admin_notices', array($this, 'Install_ECT_Notice'));
			}
		}
		public function Install_ECT_Notice()
		{

			if (current_user_can('activate_plugins')) {

				printf(
					'<div class="error CTEC_Msz"><p>%1$s %2$s</p></div>',
					esc_html__( 'In order to use this addon, Please first install the latest version of', 'template-events-calendar' ),
					sprintf(
						'<a href="%s">%s</a>',
						esc_url( 'plugin-install.php?tab=plugin-information&plugin=the-events-calendar&TB_iframe=true' ),
						esc_html__( 'The Events Calendar', 'template-events-calendar' )
					)
				);
			}
		}
			
		/*** Admin side shortcode generator style CSS */
		public function ect_tc_css()
		{
			$current_screen = get_current_screen();
			$screen_name    = isset($current_screen->base) ? esc_html($current_screen->base) : '';
			if ($screen_name == 'events-addons_page_tribe_events-events-template-settings') {
				// Use WordPress core code editor (CodeMirror) instead of bundled library.
				wp_enqueue_code_editor( array( 'type' => 'text/html' ) );
				$csf_script = wp_scripts()->query( 'csf' );
				if ( $csf_script && ! in_array( 'code-editor', $csf_script->deps, true ) ) {
					$csf_script->deps[] = 'code-editor';
				}
				wp_enqueue_script('ect-show-pro-setting', ECT_PLUGIN_URL . 'assets/js/ect-show-pro-setting.js', array(), ECT_VERSION, 'all');
				wp_enqueue_script('cpfm-settings-data-share', ECT_PLUGIN_URL . 'admin/cpfm-feedback/js/cpfm-admin-share-data.js', array('jquery'), ECT_VERSION, true);
			}
			wp_enqueue_style('sg-btn-css', plugins_url('assets/css/shortcode-generator.css', __FILE__), array(), ECT_VERSION,);
		}
		/*** Add links in plugin install list */
		public function ect_template_settings_page($links)
		{
			$links[] = '<a style="font-weight:bold" href="' . esc_url(get_admin_url(null, 'admin.php?page=tribe_events-events-template-settings')) . '">Shortcodes Settings</a>';
			// $links[] = '<a  style="font-weight:bold" href="https://eventscalendartemplates.com/" target="_blank">View Demos</a>';
			$plugin_visit_website = 'https://eventscalendaraddons.com/plugin/events-shortcodes-pro/?utm_source=ect_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=plugins_list';
			$links[]              = '<a  style="font-weight:bold" href="' . esc_url($plugin_visit_website) . '" target="_blank">' . __('Get Pro', 'template-events-calendar') . '</a>';
			return $links;
		}
		/**
		 * Add meta links to the Plugins list page.
		 *
		 * @param array  $links The current action links.
		 * @param string $file  The plugin to see if we are on Event Single Page.
		 *
		 * @return array The modified action links array.
		 */
		public function ect_addMeta_Links($links, $file)
		{
			if (strpos($file, basename(__FILE__))) {
				$ectanchor   = esc_html__('Video Tutorials', 'template-events-calendar');
				$ectvideourl = 'https://eventscalendaraddons.com/go/ect-video-tutorial/?utm_source=ect_plugin&utm_medium=inside&utm_campaign=video_tutorial&utm_content=plugins_list';
				$links[]     = '<a href="' . esc_url($ectvideourl) . '" target="_blank">' . $ectanchor . '</a>';
			}

			return $links;
		}

		// set settings on plugin activation
		public static function activate()
		{
			update_option('ect-v', ECT_VERSION);
			update_option('ect-type', 'FREE');
			update_option('ect-free-installDate', gmdate('Y-m-d h:i:s'));
			update_option('ect-ratingDiv', 'no');			
			if (!get_option('ect-initial-save-version')) {
				add_option('ect-initial-save-version', ECT_VERSION);
			}
			if (!get_option('ect-install-date')) {
				add_option('ect-install-date', gmdate('Y-m-d h:i:s'));
			}
			$ects_options = get_option('ects_options');
			$val = !empty($ects_options['ect_cpfm_feedback_data'])?$ects_options['ect_cpfm_feedback_data']:'';
			if ( ! empty($val) && ! wp_next_scheduled('ect_extra_data_update') ) {
				wp_schedule_event(time(), 'every_30_days', 'ect_extra_data_update');
			}
		}

		public static function deactivate() {
			
			delete_option('settings_migration_status');
			delete_option('ect-v');
			delete_option('ect-type');
			delete_option('ect-free-installDate');
			delete_option('ect-ratingDiv');

			if (wp_next_scheduled('ect_extra_data_update')) {
				wp_clear_scheduled_hook('ect_extra_data_update');
			}
			
		}


		public function ect_rest_url()
		{
?>
			<!-- TinyMCE Shortcode Plugin -->
			<script type='text/javascript'>
				var ectRestUrl = '<?php echo esc_url(get_rest_url(null, '/tribe/events/v1/')); ?>'
			</script>
			<!-- TinyMCE Shortcode Plugin -->
<?php
		}

		/*
			Old settings migration
		*/

		// old titan settings panel fields data
		function get_titan_settings() {
			$new_settings = array();
		
			$titan_raw_data = get_option('ect_options', false);
		
			if ($titan_raw_data === false) {
				return false;
			}
		
			if (is_array($titan_raw_data)) {
				return $titan_raw_data;
			}
		
			$titan_settings = json_decode($titan_raw_data, true);
		
			if (json_last_error() === JSON_ERROR_NONE && is_array($titan_settings)) {
				return $titan_settings;
			}

			if (is_serialized($titan_raw_data)) {
				$titan_settings = @unserialize($titan_raw_data, ['allowed_classes' => false]);
				if (is_array($titan_settings)) {
					foreach ($titan_settings as $key => $val) {
						$new_settings[$key] = is_string($val) ? json_decode($val, true) ?? $val : $val;
					}
					return $new_settings;
				}
			}
		
			return false;
		}
		

		function ect_settings_migration()
		{
			if ( 'done' === get_option( 'settings_migration_status' ) ) {
				return;
			}

			if (version_compare(get_option('ect-v'), '1.8', '>')) {
				return;
			}

			$old_settings = $this->get_titan_settings();
			if ($old_settings == false) {
				return;
			}
			if (is_array($old_settings)) {

				$req_settings = array(
					'font-family',
					'font-size',
					'font-weight',
					'font-style',
					'line-height',
					'letter-spacing',
					'text-transform',
					'color',
					'font-type',
				);
				$webSafeFonts = array(
					'Arial, Helvetica, sans-serif'         => 'Arial',
					'"Arial Black", Gadget, sans-serif'    => 'Arial Black',
					'"Comic Sans MS", cursive, sans-serif' => 'Comic Sans MS',
					'"Courier New", Courier, monospace'    => 'Courier New',
					'Georgia, serif'                       => 'Geogia',
					'Impact, Charcoal, sans-serif'         => 'Impact',
					'"Lucida Console", Monaco, monospace'  => 'Lucida Console',
					'"Lucida Sans Unicode", "Lucida Grande", sans-serif' => 'Lucida Sans Unicode',
					'"Palatino Linotype", "Book Antiqua", Palatino, serif' => 'Palatino Linotype',
					'Tahoma, Geneva, sans-serif'           => 'Tahoma',
					'"Times New Roman", Times, serif'      => 'Times New Roman',
					'"Trebuchet MS", Helvetica, sans-serif' => 'Trebuchet MS',
					'Verdana, Geneva, sans-serif'          => 'Verdana',
				);
				$old_font_arr = array_flip($webSafeFonts);

				$new_settings = array();
				foreach ($old_settings as $key => $field_val) {
					if (is_array($field_val)) {
						foreach ($field_val as $index => $val) {
							if (in_array($index, $req_settings)) {
								if ($index == 'font-type') {
									$index = 'type';
								} elseif ($index == 'font-size') {
									$val = str_replace('px', '', $val);
								} elseif ($index == 'line-height') {
									$val = str_replace('em', '', $val);
								} elseif ($index == 'letter-spacing') {
									$val = str_replace('em', '', $val);
								} elseif ($index == 'font-family') {
									$found = array_search($val, $old_font_arr);
									$val   = $found ? $found : $val;
								}

								$new_settings[$key][$index] = $val;
							}
						}
						$new_settings[$key]['line_height_unit'] = 'em';
						$new_settings[$key]['unit']             = 'px';
						$new_settings[$key]['subset']           = '';
						$new_settings[$key]['text-align']       = '';
						$new_settings[$key]['font-variant']     = '';
					} else {
						$new_settings[$key] = $field_val;
					}
				}
				update_option('ects_options', $new_settings);
				update_option('settings_migration_status', 'done');
				delete_option('ect_options');
			}
		}
		public function ect_plugin_redirection( $plugin ) {
			if ( plugin_basename( __FILE__ ) !== $plugin ) {
				return;
			}
			wp_safe_redirect(
				admin_url( 'admin.php?page=tribe_events-events-template-settings#tab=get-started' )
			);
			exit;
		}
	}
}
/*** EventsCalendarTemplates main class - END */


/*** THANKS - CoolPlugins.net ) */
$ect = EventsCalendarTemplates::get_instance();
$ect->registers();
