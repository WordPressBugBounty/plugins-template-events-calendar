<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Admin notice class for wordpress plugin.
 * This class can not be initialized or extended.
 */

if (!class_exists('ect_admin_notices')):

    final class ect_admin_notices
    {

        private static $instance = null;
        private $messages = array();
        private $version = '1.0.0';

        /**
         * initialize the class with single instance
         */
        public static function ect_create_notice()
        {
            if (!empty(self::$instance)) {
                return self::$instance;
            }
            return self::$instance = new self;
        }

        /**
         * add messages for admin notice
         * @param array $notice this array contains $id,$message,$type,$class,$id
         *
         */
        public function ect_add_message($notice)
        {
            if( !isset( $notice['id']) || empty($notice['id']) ){
                $this->ect_show_error('id is required for integrating admin notice.');
                return;
            }
            if ( isset($notice['review']) && true != (bool)$notice['review'] && ( !isset($notice['message']) || empty($notice['message']) )) {
                $this->ect_show_error('message can not be null. You must provide some text for message field');
                return;
            }
            $message = (isset($notice['message']) && !empty($notice['message'])) ?  wp_kses( $notice['message'], 'post' ) : null ;
            $type = (isset($notice['type']) && !empty($notice['type'])) ? 'notice-' . sanitize_text_field( $notice['type'] ) : 'notice-success' ;
            $class = (isset($notice['class']) && !empty($notice['class'])) ? sanitize_text_field( $notice['class'] ): '';
            $review = (bool)(isset($notice['review'] ) && !empty( $notice['review'] ) ) ? sanitize_text_field( $notice['review'] ) : false;
            $slug = (isset($notice['slug']) && !empty($notice['slug'])) ? sanitize_text_field( $notice['slug'] ): '' ;
            $plugin_name = (isset($notice['plugin_name']) && !empty($notice['plugin_name'])) ? sanitize_text_field( $notice['plugin_name'] ) : '' ;
            $review_url = (isset($notice['review_url']) && !empty($notice['review_url'])) ? esc_url( $notice['review_url'] ) : '' ;
            $review_interval = (isset($notice['review_interval']) && !empty($notice['review_interval'])) ? sanitize_text_field( $notice['review_interval'] ) : '3' ;
            if( $review == true && ( empty( $slug ) || empty( $plugin_name ) || empty( $review_url ) )){
                $this->ect_show_error( 'slug / plugin_name / review_url can not be empty if admin notice is set to review' );
                return;
            }
            $this->messages[$notice['id']] = array(
                                            'message' => $message,
                                            'type' => $type,
                                            'class' => $class,
                                            'review' => $review,
                                            'slug' => $slug,
                                            'plugin_name' => $plugin_name,
                                            'review_url' => $review_url,
                                            'review_interval' => $review_interval
                                        );

            add_action('admin_notices', array($this, 'ect_show_notice'));
            add_action( 'admin_print_scripts', array($this, 'ect_load_script' ) );
            add_action('wp_ajax_cool_plugins_admin_review_notice_dismiss', array($this, 'ect_admin_review_notice_dismiss'));
        }

        /**
    	 * Load script to dismiss notices.
    	 *
    	 * @return void
    	 */
    	public function ect_load_script() {    	
            wp_enqueue_script( 'ect-hide-notice-js',ECT_PLUGIN_URL .'admin/feedback-notice/js/ect-notice.js', array('jquery'),ECT_VERSION, true );
            wp_register_style( 'ect-feedback-notice-styles', ECT_PLUGIN_URL.'admin/feedback-notice/css/ect-admin-notices.css',array(),ECT_VERSION,'all' );
            wp_enqueue_style( 'ect-feedback-notice-styles' );
        }

        /**
         * Create simple admin notice
         */
        public function ect_show_notice()
        {
            if (count($this->messages) > 0) {
                
                foreach ($this->messages as $id => $message) {
                    if( true == (bool) $message['review'] ){
                        $this->ect_admin_notice_for_review( $id, $message);
                    }
                }
            }
        }

        /**
         * This function decides if its good to show the review notice or not
         * Review notice will only be displayed if $slug_activation_time is greater or equals to the 3 days
         */
        private function ect_admin_notice_for_review( $id, $messageObj ){
            // Everyone should not be able see the review message
            if( !current_user_can( 'update_plugins' ) ){
                return;
            }
            $slug = $messageObj['slug'];
            $days = $messageObj['review_interval'];
            if(get_option( 'ect-free-installDate' )){
                // get installation dates and rated settings
              $installation_date =date( 'Y-m-d h:i:s', strtotime(get_option( 'ect-free-installDate' )) );
            }else{
              
                return;
            }
                       
                $alreadyRated =get_option( 'ect-ratingDiv' )!=false?get_option( 'ect-ratingDiv'):"no";

                // check user already rated 
                if( $alreadyRated=="yes") {
                    return;
                }
                
                // grab plugin installation date and compare it with current date
                $display_date = date( 'Y-m-d h:i:s' );
                $install_date= new DateTime( $installation_date );
                $current_date = new DateTime( $display_date );
                $difference = $install_date->diff($current_date);
                $diff_days= $difference->days;
              
                // check if installation days is greator then week
                if (isset($diff_days) && $diff_days>= $days ) {
                    echo wp_kses_post($this->ect_create_notice_content( $id, $messageObj ));
                }
        }

        /**
         * Generate review notice HTML with CSS & JS
         */
        function ect_create_notice_content( $id, $messageObj ) {

            $ajax_url       = admin_url( 'admin-ajax.php' );
            $ajax_callback  = 'cool_plugins_admin_review_notice_dismiss';
            $wrap_cls       = 'notice notice-info is-dismissible';

            $slug           = sanitize_text_field( $messageObj['slug'] ?? '' );
            $plugin_name    = sanitize_text_field( $messageObj['plugin_name'] ?? '' );
            $plugin_link    = esc_url( $messageObj['review_url'] ?? '' );

            $like_it_text        = esc_html__( 'Rate Now! ★★★★★', 'ect2' );
            $already_rated_text  = esc_html__( 'Already Reviewed', 'ect2' );
            $not_like_it_text    = esc_html__( 'Not Interested', 'ect2' );

            $review_nonce = wp_create_nonce( $id . '_review_nonce' );

            $web_url = esc_url( 'https://coolplugins.net/?utm_source=ect_plugin&utm_medium=inside&utm_campaign=coolplugins&utm_content=review_notice' );

            $message = "Thanks for using <b>" . esc_html( $plugin_name ) . "</b> - WordPress plugin.
            We hope you liked it! <br/>Please give us a quick rating, it motivates us to keep working on more 
            <a href='" . esc_url( $web_url ) . "' target='_blank'><strong>Cool Plugins</strong></a>!<br/>";

            // Clean HTML + correct numbering (12 placeholders)
            $html = '
            <div class="ect-main-notice-wrp" id="%12$s" data-slug="%10$s">
                <div class="%1$s %10$s-feedback-notice-wrapper"
                    data-ajax-url="%6$s"
                    data-ajax-callback="%7$s"
                    data-plugin-slug="%10$s"
                    data-wp-nonce="%11$s"
                    id="%12$s">

                    <div class="message_container">
                        %3$s
                        <div class="callto_action">
                            <ul>
                                <li class="love_it">
                                    <a href="%4$s" class="like_it_btn button button-primary" target="_new">%5$s</a>
                                </li>
                                <li class="already_rated">
                                    <a href="#" class="already_rated_btn button %10$s_dismiss_notice">%8$s</a>
                                </li>
                                <li class="already_rated">
                                    <a href="#" class="already_rated_btn button %10$s_dismiss_notice">%9$s</a>
                                </li>
                            </ul>
                            <div class="clrfix"></div>
                        </div>
                    </div>

                </div>
            </div>';

            return sprintf(
                $html,
                $wrap_cls,            // 1
                $plugin_name,         // 2 
                $message,             // 3
                $plugin_link,         // 4
                $like_it_text,        // 5
                $ajax_url,            // 6
                $ajax_callback,       // 7
                $already_rated_text,  // 8
                $not_like_it_text,    // 9
                $slug,                // 10
                $review_nonce,        // 11
                $id                   // 12
            );
        }


       /**
        * This function will dismiss the review notice.
        * This is called by a wordpress ajax hook
        */
        public function ect_admin_review_notice_dismiss(){
            $id = isset($_REQUEST['id'])?sanitize_text_field($_REQUEST['id']):'';
            $nonce_key = $id . '_review_nonce' ;
            if ( ! check_ajax_referer($nonce_key,'_nonce', false ) ) {
                echo wp_json_encode( array("error"=>"nonce verification failed!"));
                die();
               
            }else{
                update_option( 'ect-ratingDiv','yes' );
                echo wp_json_encode( array("success"=>"true"));
                die();
            }
           
        }

        /**************************************************************
         * This function is used by the class for displaying error    *
         *  in case of wrong implementation of the class.             *
         **************************************************************/
        private function ect_show_error($error_text){
            $er = "<div style='text-align:center;margin-left:20px;padding:10px;background-color: #cc0000; color: #fce94f; font-size: x-large;'>";
            $er .= "Error: ".$error_text;
            $er .= "</div>";
            echo wp_kses_post($er);
        }

    }   // end of main class ect_admin_notices;
endif;
    /********************************************************************************
     * A global function to create admin notice/review box using the above class.   *
     * This function makes it easy to use above class                               *
     ********************************************************************************/
    function ect_create_admin_notice($notice)
    {
        // Do not initialize anything if it's not wordpress admin dashboard
        if (!is_admin()) {
            return;
        }
        $main_class = ect_admin_notices::ect_create_notice();
        $main_class->ect_add_message($notice);
        return $main_class;
    }