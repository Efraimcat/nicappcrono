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

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
	<h2><?php esc_html_e( get_admin_page_title() .' '.$this->version); ?></h2>
	 <!--NEED THE settings_errors below so that the errors/success messages are shown after submission - wasn't working once we started using add_menu_page and stopped using add_options_page so needed this-->
	<?php settings_errors(); ?>
	<h3><?php _e( 'Nic-app Crono Support', 'nicappcrono' )?></h3>
	<hr/>
	<p><a href="https://nic-app.com/nic-app-crono/" target="_blank"><?php _e('Contact us','nicappcrono')?></a> <?php _e( 'with any questions.', 'nicappcrono' ); ?></p>
</div>
