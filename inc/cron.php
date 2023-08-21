<?php

add_filter('cron_schedules', 'feedimporter_add_cron_interval');
function feedimporter_add_cron_interval($schedules)
{
    $schedules['thirty_minutes'] = [
        'interval' => 1800,
        'display' => esc_html__('Every Thirty Minutes')
    ];

    $schedules['fifteen_minutes'] = [
        'interval' => 900,
        'display' => esc_html__('Every Fifteen Minutes')
    ];

    $schedules['five_minutes'] = [
        'interval' => 300,
        'display' => esc_html__('Every Five Minutes')
    ];

    return $schedules;
}

add_action('feedimporter_import_feeds_cron_hook', 'feedimporter_trigger_import');
if (!wp_next_scheduled('feedimporter_import_feeds_cron_hook')) {
    wp_schedule_event(time(), 'fifteen_minutes', 'feedimporter_import_feeds_cron_hook');
}