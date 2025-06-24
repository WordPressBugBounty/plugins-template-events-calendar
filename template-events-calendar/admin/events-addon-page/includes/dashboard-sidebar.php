<?php
if (!defined('ABSPATH')) {
   exit;
} 
/**
 * 
 * Addon dashboard sidebar.
 */

 if( !isset($this->main_menu_slug) ):
    return false;
 endif;

 $event_support = esc_url("https://eventscalendaraddons.com/support/?utm_source=ect_plugin&utm_medium=inside&utm_campaign=support&utm_content=dashboard");
 $pluginwebsite = esc_url("https://eventscalendaraddons.com/?utm_source=events_dashboard&utm_medium=inside&utm_campaign=homepage&utm_content=sidebar");
 $pro_plugins_visit_website = esc_url("https://eventscalendaraddons.com/plugins/?utm_source=events_dashboard&utm_medium=inside&utm_campaign=addons&utm_content=sidebar");
 $companywebsite = esc_url("https://coolplugins.net/?utm_source=events_dashboard&utm_medium=inside&utm_campaign=coolplugins&utm_content=sidebar");
 $tec_pro = esc_url("https://theeventscalendar.pxf.io/events-calendar-pro");
?>

<div class="cool-body-right">
<ul>
<li>Display the events list on any page or post via shortcode.</li>
      <li>Showcase <strong>The Events Calendar</strong> directly inside Elementor.</li>
      <li>Design stunning single-event pages using pre-built templates.</li>
      <li>Display events from <strong>The Events Calendar</strong> directly inside the Divi builder.</li>
      <li>Add speakers’ and sponsors’ details on your event pages.</li>
      </ul>    
      <br/>
      <a href="<?php echo esc_url($event_support); ?>" target="_blank" class="button button-primary">👉 Plugin Support</a>
      <br/><br/>
      <hr>
      <p> Our addons also work smoothly with <a  href="<?php echo esc_url($tec_pro);?>" target="_blank">Events Calendar Pro ⇗</a> <b>(official premium plugin by The Events Calendar)</b></p>
      <a href="<?php echo esc_url($tec_pro);?>" target="_blank"><img src="<?php echo esc_url(plugin_dir_url( $this->addon_file ) .'/assets/images/events-calendar-pro.png'); ?>"  width="200"></a>
      <br/><br/>
      <hr>
</div>

</div><!-- End of main container-->