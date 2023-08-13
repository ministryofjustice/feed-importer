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

 function fi_import_override()
 {

        $query = get_query_var('feed_importer');

        if($query == 'import'){
            fi_import();
        }

        if($query == 'upload-file'){
            fi_upload_file_to_s3(ABSPATH .'license.txt', 'test/local-test-' . time() . '.txt');
        }

        if($query == 'delete-file'){
            $s3client = new Aws\S3\S3Client(['region' => S3_UPLOADS_REGION, 'version' => 'latest']);

            $bucket_name = S3_UPLOADS_BUCKET;

            $result = $s3client->deleteObject([
                'Bucket' => $bucket_name,
                'Key' => 'local-test-1690382371.txt'
            ]);


        }
    
 }

add_action('wp', 'fi_import_override', 1);

function fi_add_query_vars_filter($vars)
{
    $vars[] = "feed_importer";
    return $vars;
}

add_filter('query_vars', 'fi_add_query_vars_filter');

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

            if($count >= 10){
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

                if(array_key_exists('salaryMin', $job) && !empty($job['salaryMin'])){
                    update_post_meta($post_id, 'job_salary_min', $job['salaryMin']);
                }
                else {
                    delete_post_meta($post_id, 'job_salary_min');
                }

                if(array_key_exists('salaryMax', $job) && !empty($job['salaryMax'])){
                    update_post_meta($post_id, 'job_salary_max', $job['salaryMax']);
                }
                else {
                    delete_post_meta($post_id, 'job_salary_max');
                }

                if(array_key_exists('salaryLondon', $job) && !empty($job['salaryLondon'])){
                    update_post_meta($post_id, 'job_salary_london', $job['salaryLondon']);
                }
                else {
                    delete_post_meta($post_id, 'job_salary_london');
                }

                $taxononies = [
                    [
                        'taxSlug' => 'role_type',
                        'arrayKey' => 'roleTypes'
                    ],
                    [
                        'taxSlug' => 'contract_type',
                        'arrayKey' => 'contractTypes'
                    ],
                    [
                        'taxSlug' => 'job_address',
                        'arrayKey' => 'addresses'
                    ],
                    [
                        'taxSlug' => 'job_city',
                        'arrayKey' => 'cities'
                    ],
                    [
                        'taxSlug' => 'job_region',
                        'arrayKey' => 'regions'
                    ]
                ];

                foreach($taxononies as $job_tax){

                    if(array_key_exists($job_tax['arrayKey'], $job) && !empty($job[$job_tax['arrayKey']])){
                        wp_set_object_terms($post_id, $job[$job_tax['arrayKey']], $job_tax['taxSlug']);
                    }
                    else {
                        wp_set_object_terms($post_id, false, $job_tax['taxSlug']);
                    }

                }
            }


        }

        fi_delete_old_jobs($active_jobs);
    }
}

function fi_upload_file_to_s3($source_file, $dest_file){

    $s3client = new Aws\S3\S3Client(['region' => S3_UPLOADS_REGION, 'version' => 'latest']);

    $bucket_name = S3_UPLOADS_BUCKET;

    $result = [];
    try {
        $result = $s3client->putObject([
            'Bucket' => $bucket_name,
            'Key' => $dest_file,
            'SourceFile' => $source_file,
            'ACL' => 'public-read'
        ]);

        echo "Uploaded $dest_file to $bucket_name.\n";
    } catch (Exception $exception) {
        echo "Failed to upload $dest_file with error: " . $exception->getMessage();
        exit("Please fix error with file upload before continuing.");
    }    
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

    echo 'Job ID: ' . $job_id . '<br/>';
    echo 'Job Hash: ' . $job_hash . '<br/>';
    echo 'Job URL: ' . $job_url . '<br/>';
    date_default_timezone_set('Europe/London');
    echo 'Closing Date: ' . date('j M Y H:i' , $closing_date) . '<br/>';

    if(!empty($salary_min)){
        echo 'Salary Min: £' . number_format($salary_min) . '<br/>';
    }

    if(!empty($salary_max)){
        echo 'Salary Max: £' . number_format($salary_max) . '<br/>';
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