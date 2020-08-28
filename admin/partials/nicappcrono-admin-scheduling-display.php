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
	<h2><?php _e( 'Nic-app Crono Scheduling', 'nicappcrono' ); ?></h2>  
    <!--NEED THE settings_errors below so that the errors/success messages are shown after submission - wasn't working once we started using add_menu_page and stopped using add_options_page so needed this-->
	<p>
		<?php _e( 'Next schedule: ', 'nicappcrono' ); ?>
		<?php $this->scheduledJob(); ?>
	</p>
	<hr/>
	<p>
		<?php _e( 'Log files: ', 'nicappcrono' ); ?>
		<?php $this->logFiles(); ?>
		<?php $this->ShowLogFile(); ?>
	</p>	
</div>

