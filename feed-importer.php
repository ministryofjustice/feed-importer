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


 include 'inc/cpt-job.php'; 
 include 'inc/custom-taxonomies.php'; 

function fi_import(){

    
    //$url = "https://cloud-platform-e8ef9051087439cca56bf9caa26d0a3f.s3.eu-west-2.amazonaws.com/structured-feed.json";
    //$url = "https://cloud-platform-e8ef9051087439cca56bf9caa26d0a3f.s3.eu-west-2.amazonaws.com/feed-parser/moj-oleeo-structured.json";

    $url = fi_get_feed_url();

    if(!$url){
        return false;
    }
   
    $response = wp_remote_get($url);

    if (is_wp_error($response)) {
        return false;
    }

    $json = $response['body'];
    $data_array = json_decode($json, true);
    
    if(!$data_array){
        return false;
    }

    if($data_array['objectType'] == 'job'){
        
        $active_jobs = [];

        $count = 0;
        foreach($data_array['objects'] as $job){

            $job_id = $job['id'];
            $job_hash = $job['hash'];
            $job_title = $job['title'];
            $job_url = $job['url'];
            $closing_date = $job['closingDate'];

            if(empty($job_id) || empty($job_hash) || empty($job_url) || empty($closing_date)){
                continue;
            }

            if($count >= 20){
                break;
            }

            $args = array(
                'post_type' => 'job',
                'numberposts' => -1,
                'meta_query' => array(
                    array(
                        'key' => 'job_id',
                        'value' => $job_id,
                        'compare' => '=',
                    )
                )
            );

            $job_check = get_posts($args);

            if (count($job_check) === 0) {

                $job_post = array(
                    'post_title' => $job_title,
                    'post_content' => ' ',
                    'post_status' => 'publish',
                    'post_type' => 'job'
                );

                // Insert the post into the database
                $post_id = wp_insert_post($job_post);

                $active_jobs[] = $post_id;

                update_post_meta($post_id, 'job_id', $job_id);

                $count++;
            }
            else {
                $post_id = $job_check[0]->ID;

                $active_jobs[] = $post_id;

                $count++;

                $old_hash = get_post_meta($post_id, 'job_hash', true);

                if($old_hash == $job_hash){
                    continue;
                }

                $job_post = array(
                    'ID' => $post_id,
                    'post_title' => $job_title
                );

                wp_update_post( $my_post );
                
            }

            if ($post_id) {

                update_post_meta($post_id, 'job_hash', $job_hash);
                update_post_meta($post_id, 'job_url', $job_url);
                update_post_meta($post_id, 'job_closing_date', $closing_date);

                $fields = [
                    [
                        'type' => 'meta',
                        'jsonKey' => 'salaryMin',
                        'metaKey' => 'job_salary_min'
                    ],
                    [
                        'type' => 'meta',
                        'jsonKey' => 'salaryMax',
                        'metaKey' => 'job_salary_max'
                    ],
                    [
                        'type' => 'meta',
                        'jsonKey' => 'salaryLondon',
                        'metaKey' => 'job_salary_london'
                    ],
                    [
                        'type' => 'meta',
                        'jsonKey' => 'availablePositions',
                        'metaKey' => 'job_available_positions'
                    ],
                    [
                        'type' => 'tax',
                        'jsonKey' => 'roleTypes',
                        'taxKey' => 'role_type'
                    ],
                    [
                        'type' => 'tax',
                        'jsonKey' => 'contractTypes',
                        'taxKey' => 'contract_type',
                    ],
                    [
                        'type' => 'tax',
                        'jsonKey' => 'organisation',
                        'taxKey' => 'organisation',
                    ],
                    [
                        'type' => 'tax',
                        'jsonKey' => 'addresses',
                        'taxKey' => 'job_address',
                    ],
                    [
                        'type' => 'tax',
                        'jsonKey' => 'cities',
                        'taxKey' => 'job_city',
                    ],
                    [
                        'type' => 'tax',
                        'jsonKey' => 'regions',
                        'taxKey' => 'job_region',
                    ]
                ];

                foreach($fields as $field){
                
                    if(!array_key_exists('jsonKey', $field) || !array_key_exists('type', $field) || empty($field['jsonKey']) || empty($field['type'])){
                        continue;
                    }
                    
                    $jsonKey = $field['jsonKey'];

                    if($field['type'] == 'meta'){

                        if(!array_key_exists('metaKey', $field) || empty($field['metaKey'])){
                            continue;
                        }

                        if(array_key_exists($jsonKey, $job) && !empty($job[$jsonKey])){
                            update_post_meta($post_id, $field['metaKey'], $job[$jsonKey]);
                        }
                        else {
                            delete_post_meta($post_id, $field['metaKey']);
                        }
                    }
                    else if($field['type'] == 'tax'){

                        if(!array_key_exists('taxKey', $field) || empty($field['taxKey'])){
                            continue;
                        }

                        if(array_key_exists($jsonKey, $job) && !empty($job[$jsonKey])){
                            wp_set_object_terms($post_id, $job[$jsonKey], $field['taxKey']);
                        }
                        else {
                            wp_set_object_terms($post_id, false, $field['taxKey']);
                        }
                    }
                }
            }


        }

        fi_delete_old_jobs($active_jobs);

        return true;
    }

    return false;
}

// Add the new meta box to side of editor page
add_action('add_meta_boxes', 'fi_metabox_job');

function fi_metabox_job()
{
    add_meta_box(
        'hale-job-metabox',
        __('Job Details', 'hale'),
        'fi_render_job_metabox',
        'job',
        'normal',
        'high'
    );
}

/**
 * Render the metabox and it's contents to the page.
 */
function fi_render_job_metabox($post)
{

    $job_id = get_post_meta($post->ID, 'job_id', true);
    $job_hash = get_post_meta($post->ID, 'job_hash', true);
    $job_url = get_post_meta($post->ID, 'job_url', true);
    $closing_date = get_post_meta($post->ID, 'job_closing_date', true);
    $salary_min = get_post_meta($post->ID, 'job_salary_min', true);
    $salary_max = get_post_meta($post->ID, 'job_salary_max', true);
    $available_positions = get_post_meta($post->ID, 'job_available_positions', true);

    echo 'Job ID: ' . $job_id . '<br/>';
    echo 'Job Hash: ' . $job_hash . '<br/>';
    echo 'Job URL: ' . $job_url . '<br/>';

    if(!empty($closing_date)){
        date_default_timezone_set('Europe/London');
        echo 'Closing Date: ' . date('j M Y H:i' , $closing_date) . '<br/>';
    }

    if(!empty($salary_min)){
        echo 'Salary Min: £' . number_format($salary_min) . '<br/>';
    }

    if(!empty($salary_max)){
        echo 'Salary Max: £' . number_format($salary_max) . '<br/>';
    }

    if(!empty($available_positions)){
        echo 'Number of positions available: ' . $available_positions  . '<br/>';
    }
}

function fi_is_debug_mode_active(){


    $options = get_option('fi_settings');

    if (empty($options) || !array_key_exists('debug_mode', $options)) {
        return false;
    }

    if($options['debug_mode'] == 'debug'){
        return true;
    }

    return false;
}

function fi_get_feed_url(){

    $options = get_option('fi_settings');

    if (empty($options) || !array_key_exists('feed_url', $options) || empty($options['feed_url'])) {
        return false;
    }

    return $options['feed_url'];
}

function fi_delete_old_jobs($active_jobs){

    if (count($active_jobs) > 0) {
        $all_posts = get_posts(array('post_type' => 'job', 'post__not_in' => $active_jobs, 'numberposts' => -1));
        foreach ($all_posts as $each_post) {
            wp_delete_post($each_post->ID, true);
        }
    }
}

add_filter('cron_schedules', 'fi_add_cron_interval');
function fi_add_cron_interval($schedules)
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

add_action('fi_import_feeds_cron_hook', 'fi_import');
if (!wp_next_scheduled('fi_import_feeds_cron_hook')) {
    wp_schedule_event(time(), 'fifteen_minutes', 'fi_import_feeds_cron_hook');
}