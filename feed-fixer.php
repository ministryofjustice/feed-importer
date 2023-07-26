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


 function ff_import_override()
 {

        $query = get_query_var('feed_fixer');

        if($query == 'fixme'){
            $s3client = new Aws\S3\S3Client(['region' => S3_UPLOADS_REGION, 'version' => 'latest']);

            $bucket_name = S3_UPLOADS_BUCKET;

            $file_name = 'randomtest' . time() . '.txt';
            try {
                $s3client->putObject([
                    'Bucket' => $bucket_name,
                    'Key' => $file_name,
                    'SourceFile' => ABSPATH .'license.txt'
                ]);
                echo "Uploaded $file_name to $bucket_name.\n";
            } catch (Exception $exception) {
                echo "Failed to upload $file_name with error: " . $exception->getMessage();
                exit("Please fix error with file upload before continuing.");
            }
        }
 }

add_action('wp', 'ff_import_override', 1);

function ff_add_query_vars_filter($vars)
{
    $vars[] = "feed_fixer";
    return $vars;
}

add_filter('query_vars', 'ff_add_query_vars_filter');


