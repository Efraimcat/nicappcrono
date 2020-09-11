<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://efraim.cat
 * @since      1.0.0
 *
 * @package    Nicappcrono
 */

// If uninstall not called from WordPress, then exit.
if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

// delete plugin options
$options = array(
    'nicappcrono_DataCenter',
    'nicappcrono_clientId',
    'nicappcrono_clientSecret',
    'nicappcrono_masterCalendar',
    'nicappcrono_masterAccessToken',
    'nicappcrono_masterRefreshToken',
    'nicappcrono_AuthorizationPageId',
    'nicappcrono_CreateAuthPage'
);
foreach ($options as $option) {
    if (get_option($option))
        delete_option($option);
}

// delete calendar type posts
$loop = new WP_Query(array(
    'post_type' => 'nicappcronocalendars',
    'posts_per_page' => 5000,
    'orderby' => 'rand'
));
while ($loop->have_posts()) :
    $loop->the_post();
    wp_delete_post($loop->post->ID, false);
endwhile
;
wp_reset_query();