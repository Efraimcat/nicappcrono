<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://efraim.cat
 * @since      1.0.0
 *
 * @package    Nicappcrono
 * @subpackage Nicappcrono/admin/partials
 */
?>
<div class="wrap">
	<div id="icon-themes" class="icon32"></div>  
	<h2><?php esc_html_e( get_admin_page_title() .' '.$this->version); ?></h2>
	<h2><?php _e( 'Nic-app Crono Settings', $this->plugin_name ); ?></h2>  
	<?php settings_errors(); ?>  
	<form method="POST" action="options.php">  
		<?php 
        settings_fields( 'nicappcrono_general_settings' );
        do_settings_sections( 'nicappcrono_general_settings' ); 
        ?>             
        <hr/><p><?php _e( 'When creating the developer account in the chosen data center and later when creating our app, we already authorize the use of the master calendar. To authorize the following calendars, it is necessary to define a page from which to carry out this authorization.', $this->plugin_name ); ?></p>
		<p><?php _e( 'It can be any page that contains the shortcode [NicappAuth]. In that case we will simply enter the ID of the page. We can create a new page with the shortcode in it if we check the option to create a new page.', $this->plugin_name ); ?></p><hr/>
		<?php submit_button(); ?>  
	</form> 
</div>