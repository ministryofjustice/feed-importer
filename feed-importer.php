<?php

/**
 * Plugin name: Feed Importer
 * Plugin URI:  https://github.com/ministryofjustice/feed-importer
 * Description: Parses a XML/JSON data feed into Wordpress
 * Version:     1.0.0
 * Author:      Ministry of Justice - Adam Brown, Malcolm Butler & Robert Lowe
 * Text domain: feed-importer
 * Author URI:  https://github.com/ministryofjustice
 * License:     MIT Licence
 * License URI: https://opensource.org/licenses/MIT
 * Copyright:   Crown Copyright (c) Ministry of Justice
 **/

 include 'inc/settings.php'; 

 function fi_import_override()
 {

        $query = get_query_var('feed_importer');

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


function fi_upload_file_to_s3($source_file, $dest_file){

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