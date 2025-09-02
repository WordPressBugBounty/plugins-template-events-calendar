<?php
if (!defined('ABSPATH')) {
    exit();
}

if (!class_exists('ECT_cronjob')) {
    class ECT_cronjob
    {

        public function __construct() {
           
       
          // Register cron jobs
            add_filter('cron_schedules', array($this, 'ect_cron_schedules'));
            add_action('ect_extra_data_update', array($this, 'ect_cron_extra_data_autoupdater'));
        }
        
        function ect_cron_extra_data_autoupdater() {
       
            $settings       = get_option('ects_options', []);
            $ect_response  = !empty($settings['ect_cpfm_feedback_data']) ? $settings['ect_cpfm_feedback_data'] : '';
            
            if (!empty($ect_response) || $ect_response === true){
          
                if (class_exists('ECT_cronjob')) {
                    self::ect_send_data();
                }
            }
        }
           
       static public function ect_send_data() {
                   
            $feedback_url = ECT_FEEDBACK_URL.'wp-json/coolplugins-feedback/v1/site';
            require_once ECT_PLUGIN_DIR . 'admin/feedback/admin-feedback-form.php';
        
            if (!defined('ECT_PLUGIN_DIR')  || !class_exists('\ECT\feedback\ect_feedback') ) {
                return;
            }
            
            $extra_data         = new \ECT\feedback\ect_feedback();
            $extra_data_details = $extra_data->cpfm_get_user_info();


            $server_info    = $extra_data_details['server_info'];
            $extra_details  = $extra_data_details['extra_details'];
            $site_url       = esc_url(get_site_url());
            $install_date   = get_option('ect-install-date');
            $uni_id         = '20';
            $site_id        = $site_url . '-' . $install_date . '-' . $uni_id;
            
            $initial_version = get_option('ect-initial-save-version');
            $initial_version = is_string($initial_version) ? sanitize_text_field($initial_version) : 'N/A';
            $plugin_version = defined('ECT_VERSION') ? ECT_VERSION : 'N/A';
            $admin_email    = sanitize_email(get_option('admin_email') ?: 'N/A');
            
            $post_data = array(

                'site_id'           => md5($site_id),
                'plugin_version'    => $plugin_version,
                'plugin_name'       => 'template-events-calendar',
                'plugin_initial'    => $initial_version,
                'email'             => $admin_email,
                'site_url'          => esc_url_raw($site_url),
                'server_info'       => $server_info,
                'extra_details'     => $extra_details,
            );
            
            $response = wp_remote_post($feedback_url, array(

                'method'    => 'POST',
                'timeout'   => 30,
                'headers'   => array(
                    'Content-Type' => 'application/json',
                ),
                'body'      => wp_json_encode($post_data),
            ));
            
            if (is_wp_error($response)) {
                return;
            }
            
            $response_body  = wp_remote_retrieve_body($response);
            $decoded        = json_decode($response_body, true);
          
            if (!wp_next_scheduled('ect_extra_data_update')) {

             
                wp_schedule_event(time(), 'every_30_days', 'ect_extra_data_update');
              
            }
        }
          
        /**
         * Cron status schedule(s).
         */
        public function ect_cron_schedules($schedules)
        {
            // 30days schedule for update information

            if (!isset($schedules['every_30_days'])) {

                $schedules['every_30_days'] = array(
                    'interval' => 30 * 24 * 60 * 60, // 2,592,000 seconds
                    'display'  => __('Once every 30 days'),
                );
            }

            return $schedules;
        }


    }

    $cron_init = new ECT_cronjob();
}