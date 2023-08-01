<?php

/**
 * Plugin name: Feed Fixer
 * Plugin URI:  https://github.com/ministryofjustice/feed-fixer
 * Description: Parses a XML/JSON data feed into Wordpress
 * Version:     1.0.0
 * Author:      Ministry of Justice - Adam Brown, Malcolm Butler & Robert Lowe
 * Text domain: feed-fixer
 * Author URI:  https://github.com/ministryofjustice
 * License:     MIT Licence
 * License URI: https://opensource.org/licenses/MIT
 * Copyright:   Crown Copyright (c) Ministry of Justice
 **/

 include 'inc/settings.php'; 

 function ff_import_override()
 {

        $query = get_query_var('feed_fixer');

        if($query == 'upload-file'){
            ff_upload_file_to_s3(ABSPATH .'license.txt', 'test/local-test-' . time() . '.txt');
        }

        if($query == 'delete-file'){
            $s3client = new Aws\S3\S3Client(['region' => S3_UPLOADS_REGION, 'version' => 'latest']);

            $bucket_name = S3_UPLOADS_BUCKET;

            $result = $s3client->deleteObject([
                'Bucket' => $bucket_name,
                'Key' => 'local-test-1690382371.txt'
            ]);


        }

        if($query == 'get-xml'){
            ff_import_feed();


        }
 }

add_action('wp', 'ff_import_override', 1);

function ff_add_query_vars_filter($vars)
{
    $vars[] = "feed_fixer";
    return $vars;
}

add_filter('query_vars', 'ff_add_query_vars_filter');


function ff_import_feed()
{

    $ff_settings = get_option('ff_settings');
    if(!empty($ff_settings) && array_key_exists('ff_feed_url', $ff_settings)) {
        set_time_limit(600);

        $url = $ff_settings['ff_feed_url'];

        //$url = "https://justicejobs.tal.net/vx/mobile-0/appcentre-1/brand-2/candidate/jobboard/vacancy/3/feed";
        //$url = "https://justicejobs.tal.net/vx/mobile-0/appcentre-1/brand-2/candidate/jobboard/vacancy/3/feed/structured";
        $tmp = get_temp_dir() . "jobs.xml";

        // get the uploads directory path
        $upload_dir = wp_get_upload_dir();
        $file = $upload_dir['basedir'] . "/jobs.xml";

        $response = wp_remote_get($url, [
            'timeout' => 1800,
            'stream' => true,
            'filename' => $file
        ]);

        // let's check the data is xml
        if (simplexml_load_file($tmp)) {
            ff_upload_file_to_s3($tmp, 'feed-fixer/jobs.xml');
        
        }
    }
}

function ff_upload_file_to_s3($source_file, $dest_file){

    $s3client = new Aws\S3\S3Client(['region' => S3_UPLOADS_REGION, 'version' => 'latest']);

    $bucket_name = S3_UPLOADS_BUCKET;

    try {
        $s3client->putObject([
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