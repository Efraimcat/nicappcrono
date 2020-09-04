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
	<?php settings_errors(); ?>
	<p>
		<?php _e( 'Next schedule (UTC): ', 'nicappcrono' ); ?>
		<?php $this->scheduledJob(); ?>
	</p>
	<hr/>
	<p>
		<?php _e( 'Log files: ', 'nicappcrono' ); ?>
		<?php $this->logFiles(); ?>
		<?php $this->ShowLogFile(); ?>
	</p>	
</div>
