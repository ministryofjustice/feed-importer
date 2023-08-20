<?php

/**
 * Plugin name: Feed Importer
 * Plugin URI:  https://github.com/ministryofjustice/feed-importer
 * Description: Imports a JSON data feed into Wordpress
 * Version:     1.0.0
 * Author:      Ministry of Justice - Adam Brown, Malcolm Butler & Robert Lowe
 * Text domain: feed-importer
 * Author URI:  https://github.com/ministryofjustice
 * License:     MIT Licence
 * License URI: https://opensource.org/licenses/MIT
 * Copyright:   Crown Copyright (c) Ministry of Justice
 **/

 include 'inc/settings.php'; 
 include 'inc/cron.php'; 
 include 'inc/cpt-job.php'; 
 include 'inc/custom-taxonomies.php'; 
 include 'inc/job-details-metabox.php';
 include 'inc/import.php'; 

 // Plugin deactivation hook
register_deactivation_hook(__FILE__, 'feedimporter_plugin_deactivate');

// Deactivation callback
function feedimporter_plugin_deactivate() {
    wp_clear_scheduled_hook('feedimporter_import_feeds_cron_hook');
}