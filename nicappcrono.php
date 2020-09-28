<?php

/**
 * @link              https://github.com/Efraimcat/nicappcrono
 * @since             1.0.0
 * @package           Nicappcrono
 *
 * @wordpress-plugin
 * Plugin Name:       Nic-app Crono
 * Plugin URI:        https://nic-app.com/nic-app-crono/
 * Description:       Nic-app Crono is a plugin that allows you to unify different calendars (Google Calendar, Apple iCloud, Exchange, Office 365 / Outlook) into a single calendar.
 * Version:           1.0.1
 * Author:            Efraim Bayarri
 * Author URI:        https://efraim.cat
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       nicappcrono
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die();
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('NICAPPCRONO_VERSION', '1.0.2');

/**
 * Currently only php 7.1 and higher is supported
 */
if (version_compare(phpversion(), '7.1.0', '<')) {
    // php version isn't high enough
    die();
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-nicappcrono-activator.php
 */
function activate_nicappcrono()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-nicappcrono-activator.php';
    Nicappcrono_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-nicappcrono-deactivator.php
 */
function deactivate_nicappcrono()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-nicappcrono-deactivator.php';
    Nicappcrono_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_nicappcrono');
register_deactivation_hook(__FILE__, 'deactivate_nicappcrono');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-nicappcrono.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */
function run_nicappcrono()
{
    $plugin = new Nicappcrono();
    $plugin->run();
}
run_nicappcrono();
