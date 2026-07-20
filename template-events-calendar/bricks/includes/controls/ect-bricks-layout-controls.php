<?php
/**
 * ECT_Bricks_Layout_Controls service.
 *
 * @package template-events-calendar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
//phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
if ( ! class_exists( 'ECT_Bricks_Layout_Controls', false ) ) {

	final class ECT_Bricks_Layout_Controls {

		private static function ect_bricks_register_template_controls( $element ) {
			$element->controls['layout_template'] = array(
				'tab'               => 'content',
				'group'             => 'layouts',
				'label'             => esc_html__( 'Template', 'template-events-calendar' ),
				'type'              => 'select',
				'options'           => array(	
					'list' => esc_html__( 'List', 'template-events-calendar' ),
					'grid' => esc_html__( 'Grid (PRO only)', 'template-events-calendar' ),
				),
				'inline'   => true,
				'default'  => 'list',
			);

			$element->controls['list_item_style'] = array(
				'tab'      => 'content',
				'group'    => 'layouts',
				'label'    => esc_html__( 'List style', 'template-events-calendar' ),
				'type'     => 'select',
				'options'  => array(
					'style-1' => esc_html__( 'Style 1', 'template-events-calendar' ),
					'style-2' => esc_html__( 'Style 2', 'template-events-calendar' ),
				),
				'default'  => 'style-1',
				'required' => ECT_Bricks_Hover_Controls::ect_bricks_req_layout_is_list(),
			);
		}

		private static function ect_bricks_register_layout_display_controls( $element ) {
			$element->controls['hide_event_image'] = array(
				'tab'      => 'content',
				'group'    => 'layouts',
				'label'    => esc_html__( 'Hide featured image', 'template-events-calendar' ),
				'type'     => 'checkbox',
				'default'  => false,
				'rerender' => true,
			);

			ECT_Bricks_Card_Style_Controls::ect_bricks_register_category_badge_layout_toggle( $element );

			$date_column_order_options = array(
				'month_day' => esc_html__( 'Month above, date below', 'template-events-calendar' ),
				'day_month' => esc_html__( 'Date above, month below', 'template-events-calendar' ),
			);

			$element->controls['list1_show_date_column'] = array(
				'tab'      => 'content',
				'group'    => 'layouts',
				'label'    => esc_html__( 'Hide date column', 'template-events-calendar' ),
				'type'     => 'checkbox',
				'default'  => false,
				'rerender' => true,
				'required' => ECT_Bricks_Hover_Controls::ect_bricks_req_list_style( 'style-1' ),
			);

			$element->controls['list1_date_column_order'] = array(
				'tab'      => 'content',
				'group'    => 'layouts',
				'label'    => esc_html__( 'Date column order', 'template-events-calendar' ),
				'type'     => 'select',
				'options'  => $date_column_order_options,
				'default'  => 'day_month',
				'inline'   => true,
				'rerender' => true,
				'required' => ECT_Bricks_Hover_Controls::ect_bricks_req_list1_date_column_style(),
			);

			$element->controls['style2_show_date_badge'] = array(
				'tab'      => 'content',
				'group'    => 'layouts',
				'label'    => esc_html__( 'Hide date badge on image', 'template-events-calendar' ),
				'type'     => 'checkbox',
				'default'  => false,
				'rerender' => true,
				'required' => ECT_Bricks_Hover_Controls::ect_bricks_req_list_style_with_image( 'style-2' ),
			);

			$element->controls['style2_date_badge_order'] = array(
				'tab'      => 'content',
				'group'    => 'layouts',
				'label'    => esc_html__( 'Date badge order', 'template-events-calendar' ),
				'type'     => 'select',
				'options'  => $date_column_order_options,
				'default'  => 'month_day',
				'inline'   => true,
				'rerender' => true,
				'required' => ECT_Bricks_Hover_Controls::ect_bricks_req_shell_date_badge_style(),
			);

			$element->controls['item_gap'] = array(
				'tab'         => 'content',
				'group'       => 'layouts',
				'label'       => esc_html__( 'Gap between events', 'template-events-calendar' ),
				'type'        => 'number',
				'min'         => 0,
				'step'        => 1,
				'placeholder' => '24',
				'default'     => 24,
				'units'       => array(
					'px'  => 'px',
					'rem' => 'rem',
					'em'  => 'em',
				),
				'unit'        => 'px',
				'responsive'  => true,
				'rerender'    => true,
				'description' => esc_html__( 'Space between each event card. Click the device icon on this control for tablet/mobile.', 'template-events-calendar' ),
				'css'         => array(
					array(
						'property' => '--ect-bricks-gap',
						'selector' => '&',
					),
				),
			);
		}

		public static function ect_bricks_register_layout_controls( $element ) {
			self::ect_bricks_register_template_controls( $element );
			self::ect_bricks_register_layout_display_controls( $element );
		}
	}
}
