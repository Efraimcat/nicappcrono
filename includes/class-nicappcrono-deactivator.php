<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://efraim.cat
 * @since      1.0.0
 *
 * @package    Nicappcrono
 * @subpackage Nicappcrono/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Nicappcrono
 * @subpackage Nicappcrono/includes
 * @author     Efraim Bayarri <efraim@efraim.cat>
 */
class Nicappcrono_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		wp_clear_scheduled_hook( 'nicappcronoCronJob' );
	}

}
