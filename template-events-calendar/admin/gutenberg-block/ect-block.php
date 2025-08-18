<?php
if (!defined('ABSPATH')) {
    exit;
} 
function ect_gutenberg_scripts() {
	$blockPath = '/dist/block.js';
	$stylePath = '/dist/block.css';

	// Enqueue the bundled block JS file
	wp_enqueue_script(
		'ect-block-js',
		plugins_url( $blockPath, __FILE__ ),
		[ 'wp-i18n', 'wp-blocks', 'wp-edit-post', 'wp-element', 'wp-editor', 'wp-components', 'wp-data', 'wp-plugins', 'wp-edit-post', 'wp-api' ],
		filemtime( plugin_dir_path(__FILE__) . $blockPath )
	);
	wp_localize_script( 'ect-block-js', 'ectUrl',array(ECT_PLUGIN_URL));

	// Enqueue frontend and editor block styles
	wp_enqueue_style(
		'ect-block-css',
		plugins_url( $stylePath, __FILE__ ),
		'',
		filemtime( plugin_dir_path(__FILE__) . $stylePath )
	);
	
}

// Hook scripts function into block editor hook
add_action( 'enqueue_block_editor_assets', 'ect_gutenberg_scripts' );

/**
 * Block Initializer
 * */
add_action( 'plugins_loaded', function () {
	if ( function_exists( 'register_block_type' ) ) {
		// Hook server side rendering into render callback

		register_block_type(
			'ect/shortcode', array(
				'render_callback' => 'ect_block_callback',
				'attributes' => array(
					'category' => array(
						'type' => 'string',
						'default' =>'all'
					),
					'template'	 => array(
						'type' => 'string',
						'default' =>'default'
					),
					'style'	 => array(
						'type' => 'string',
						'default' =>'style-1'
					),
					'dateformat'	=> array(
						'type'	=> 'string',
						'default' => 'default'
					),
					'limit'	=> array(
						'type'	=> 'string',
						'default' => '10'
					),				
					'order'	 => array(
						'type' => 'string',
						'default' =>'ASC'
					),
					'hideVenue'	=> array(
						'type'	=> 'string',
						'default' =>'no'
					),
					'time'	 => array(
						'type' => 'string',
						'default' =>'future'
					),					
					'startDate'	=> array(
						'type'	=> 'string',
						'default' => ''
					),
					'endDate'	=> array(
						'type'	=> 'string',
						'default' => ''
					),
					'socialshare'=> array(
						'type'	=> 'string',
						'default' =>'no',
					)		
				),
			)
		);
	}
} );

/**
 * Block Output
 * */
function ect_block_callback( $attr ) {
    $category    = isset( $attr['category'] )    ? sanitize_text_field( $attr['category'] )    : 'all';
    $template    = isset( $attr['template'] )    ? sanitize_text_field( $attr['template'] )    : 'default';
    $style       = isset( $attr['style'] )       ? sanitize_text_field( $attr['style'] )       : 'style-1';
    $dateformat  = isset( $attr['dateformat'] )  ? sanitize_text_field( $attr['dateformat'] )  : 'default';
    $limit       = isset( $attr['limit'] )       ? intval( $attr['limit'] )                    : 10;
    $order       = isset( $attr['order'] )       ? sanitize_text_field( $attr['order'] )       : 'ASC';
    $hideVenue   = isset( $attr['hideVenue'] )   ? sanitize_text_field( $attr['hideVenue'] )   : 'no';
    $time        = isset( $attr['time'] )        ? sanitize_text_field( $attr['time'] )        : 'future';
    $startDate   = isset( $attr['startDate'] )   ? sanitize_text_field( $attr['startDate'] )   : '';
    $endDate     = isset( $attr['endDate'] )     ? sanitize_text_field( $attr['endDate'] )     : '';
    $socialshare = isset( $attr['socialshare'] ) ? sanitize_text_field( $attr['socialshare'] ) : 'no';

    if ( ! empty( $template ) ) {
        $shortcode_string = '[events-calendar-templates
            category="%s"
            template="%s"
            style="%s" 
            date_format="%s"
            limit="%s"
            order="%s"
            hide-venue="%s"
            time="%s"
            start_date="%s"
            end_date="%s"
            socialshare="%s"]';

        return sprintf(
            $shortcode_string,
            esc_attr( $category ),
            esc_attr( $template ),
            esc_attr( $style ),
            esc_attr( $dateformat ),
            esc_attr( $limit ),
            esc_attr( $order ),
            esc_attr( $hideVenue ),
            esc_attr( $time ),
            esc_attr( $startDate ),
            esc_attr( $endDate ),
            esc_attr( $socialshare )
        );
    }
}
