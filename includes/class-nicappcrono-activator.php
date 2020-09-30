<?php

/**
 * Fired during plugin activation
 *
 * @link       https://efraim.cat
 * @since      1.0.0
 *
 * @package    Nicappcrono
 * @subpackage Nicappcrono/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since 1.0.0
 * @package Nicappcrono
 * @subpackage Nicappcrono/includes
 * @author Efraim Bayarri <efraim@efraim.cat>
 */
class Nicappcrono_Activator
{
    
    /**
     * Short Description.
     *
     * Long Description.
     *
     * @since 1.0.0
     */
    public static function activate()
    {
        if (! wp_next_scheduled('nicappcronoCronJob')) {
            wp_schedule_event(time(), 'hourly', 'nicappcronoCronJob');
        }
    }
}