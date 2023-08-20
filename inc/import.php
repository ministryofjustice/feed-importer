<?php

/**
 * Triggers the execution of the feed import process.
 *
 * @return bool `true` if the import process was successful and the last imported date
 *              option was updated, `false` otherwise.
 */
function feedimporter_trigger_import() {
    // Call the feedimporter_import function to initiate the import process
    $importResult = feedimporter_import();
    
    // Check if the import process was successful
    if (!$importResult) {
        // Return false if the import process failed
        return false;
    }
    
    // Update the last imported date option with the current timestamp
    update_option('fi_last_imported_date', time());
    return true;
}

/**
 * Imports data from the fetched feed and initiates the appropriate import process.
 *
 * This function fetches feed data using the `feedimporter_fetch_feed_data` function and
 * processes the imported data based on the object type specified in the feed.
 *
 * @return bool `true` if the import process was successful, `false` otherwise.
 */
function feedimporter_import(){

    $feedDataArray = feedimporter_fetch_feed_data();

    if(!$feedDataArray || !is_array($feedDataArray) || !array_key_exists('objectType', $feedDataArray)){
        return false;
    }

    if(!array_key_exists('objects', $feedDataArray) || empty($feedDataArray['objects'])){
        return false;
    }

    if($feedDataArray['objectType'] == 'job'){
        
        $importResult = feedimporter_import_jobs($feedDataArray['objects']);

        if(!$importResult){
            return false;
        }

        return true;
    }

    return false;
}

/**
 * Fetches and retrieves feed data from a specified URL.
 *
 * This function fetches data from the provided feed URL, decodes the JSON response, 
 * and returns the data as an associative array.
 *
 * @return array|false An associative array containing the fetched feed data on success,
 *                    or `false` if any errors occur during the fetching and decoding process.
 */
function feedimporter_fetch_feed_data() {
    // Get the feed URL
    $url = feedimporter_get_feed_url();

    // Check if the feed URL is available
    if (!$url) {
        return false;
    }

    // Fetch data from the feed URL
    $response = wp_remote_get($url);

    // Check for WP Error during the HTTP request
    if (is_wp_error($response)) {
        return false;
    }

    // Extract JSON body from the response
    $json = $response['body'];

    // Decode JSON into an associative array
    $dataArray = json_decode($json, true);
    
    // Check if JSON decoding was successful
    if (!$dataArray) {
        return false;
    }

    // Return the fetched feed data
    return $dataArray;
}

/**
 * Imports job data from an array of jobs and manages their insertion or updates.
 *
 * This function imports job data from the provided array of jobs. It processes each job entry
 * and either inserts a new job post or updates an existing one, based on the job's unique ID. 
 * The maximum number of items to process is determined by the `feedimporter_get_max_items` function.
 * Any old jobs not present in the active jobs list will be deleted using `feedimporter_delete_old_jobs`.
 *
 * @param array $jobsArray An array containing job data to import.
 *
 * @return bool `true` if the import process was completed successfully, `false` otherwise.
 */
function feedimporter_import_jobs($jobsArray) {
    $activeJobs = [];
    $maxItems = feedimporter_get_max_items();

    $count = 0;
    foreach ($jobsArray as $job) {
        if ($maxItems !== false && $count >= $maxItems) {
            break;
        }

        $jobPostID = feedimporter_import_job($job);

        if (!is_numeric($jobPostID)) {
            continue;
        }

        $activeJobs[] = $jobPostID;
        $count++;
    }

    //Do we need check here 
    feedimporter_delete_old_jobs($activeJobs);

    return true;
}

/**
 * Imports a single job entry and manages its insertion or update.
 *
 * This function imports a single job entry by validating its data fields. If the data is valid,
 * it checks whether the job with the provided ID already exists. If the job does not exist,
 * a new job post is inserted and its details updated. If the job exists and its hash has changed,
 * the job post title and details are updated accordingly.
 *
 * @param array $job An array containing job data to import.
 *
 * @return int|false The ID of the imported or updated job post on success,
 *                   or `false` if any errors occur during the process.
 */
function feedimporter_import_job($job) {
    // Validate job data
    if (!array_key_exists('id', $job) || !array_key_exists('hash', $job) ||
        !array_key_exists('title', $job) || !array_key_exists('url', $job) ||
        !array_key_exists('closingDate', $job)) {
        return false;
    }

    // Extract job data
    $jobID = $job['id'];
    $jobHash = $job['hash'];
    $jobTitle = $job['title'];
    $jobURL = $job['url'];
    $closingDate = $job['closingDate'];

    // Check for empty required job data fields
    if (empty($jobID) || empty($jobHash) || empty($jobTitle) || empty($jobURL) || empty($closingDate)) {
        return false;
    }

    // Check if job exists
    $jobPostID = feedimporter_check_job_exists($jobID);

    // Insert or update job post and details
    if (!$jobPostID) {
        $jobPostID = feedimporter_insert_job($jobID, $jobTitle);
        if (is_numeric($jobPostID)) {
            feedimporter_update_job_details($jobPostID, $job);
        }
    } else {
        if (!feedimporter_compare_job_hash($jobPostID, $jobHash)) {
            feedimporter_update_job($jobPostID, $jobTitle);
            feedimporter_update_job_details($jobPostID, $job);
        }
    }

    // Return the ID of the imported or updated job post
    return $jobPostID;
}


/**
 * Checks if a job with the specified job ID already exists.
 *
 * This function queries the database to determine if a job post with the provided
 * job ID already exists. 
 *
 * @param string $jobID The unique identifier of the job.
 *
 * @return int|false The ID of the existing job post if found, `false` otherwise.
 */
function feedimporter_check_job_exists($jobID) {
    $args = array(
        'post_type'   => 'job',
        'numberposts' => -1,
        'meta_query'  => array(
            array(
                'key'     => 'job_id',
                'value'   => $jobID,
                'compare' => '=',
            )
        )
    );

    $jobsFoundArray = get_posts($args);

    if (count($jobsFoundArray) === 0) {
        return false;
    }

    $jobPostID = $jobsFoundArray[0]->ID;

    return $jobPostID;
}


/**
 * Inserts a new job post with the specified title
 *
 * This function creates a new job post with the given title.
 *
 * @param string $jobID The unique identifier for the job.
 * @param string $jobTitle The title of the job.
 *
 * @return int|false The Post ID of the newly inserted job post on success,
 *                   or `false` if the insertion fails.
 */
function feedimporter_insert_job($jobID, $jobTitle) {
    $newJobArgs = array(
        'post_title'   => $jobTitle,
        'post_content' => ' ',
        'post_status'  => 'publish',
        'post_type'    => 'job'
    );

    // Insert the new job post
    $jobPostID = wp_insert_post($newJobArgs);

    // Check if the insertion was successful
    if (!$jobPostID) {
        return false;
    }

    // Update the job post's meta information with the provided job ID
    update_post_meta($jobPostID, 'job_id', $jobID);

    // Return the ID of the newly inserted job post
    return $jobPostID;
}


/**
 * Compares the hash of a job post with a new job hash.
 *
 * This function retrieves the existing job hash from the specified job post
 * and compares it with the new job hash. 
 *
 * @param int $jobPostID The ID of the job post to compare.
 * @param string $newjobHash The new hash value to compare against.
 *
 * @return bool `true` if the existing job hash matches the new hash,
 *              `false` otherwise.
 */
function feedimporter_compare_job_hash($jobPostID, $newjobHash) {
    // Retrieve the old job hash from post meta
    $oldJobHash = get_post_meta($jobPostID, 'job_hash', true);

    // Compare the old job hash with the new job hash
    if ($oldJobHash != $newjobHash) {
        return false;
    }

    // Return true if the hashes match
    return true;
}


/**
 * Updates the title of a job post with the specified job ID.
 *
 * This function updates the title of the job post associated with the provided job ID. 
 *
 * @param int $jobPostID The ID of the job post to update.
 * @param string $jobTitle The new title for the job post.
 *
 * @return bool `true` if the job post was successfully updated, `false` otherwise.
 */
function feedimporter_update_job($jobPostID, $jobTitle) {
    $updateJobArgs = array(
        'ID'          => $jobPostID,
        'post_title'  => $jobTitle
    );

    // Update the job post with the new title
    $updateResult = wp_update_post($updateJobArgs);

    // Check if the update was successful
    if (!$updateResult) {
        return false;
    }

    // Return true if the update was successful
    return true;
}

/**
 * Updates the details and associated metadata of a job post.
 *
 * This function updates various details of a job post. The function iterates through a defined list of fields to update the
 * metadata and taxonomy terms of the job post based on the provided job data.
 *
 * @param int $jobPostID The ID of the job post to update.
 * @param array $job An array containing job data to update.
 *
 * @return void
 */
function feedimporter_update_job_details($jobPostID, $job){

    update_post_meta($jobPostID, 'job_hash', $job['hash']);
    update_post_meta($jobPostID, 'job_url', $job['url']);
    update_post_meta($jobPostID, 'job_closing_date', $job['closingDate']);

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
                update_post_meta($jobPostID, $field['metaKey'], $job[$jsonKey]);
            }
            else {
                delete_post_meta($jobPostID, $field['metaKey']);
            }
        }
        else if($field['type'] == 'tax'){

            if(!array_key_exists('taxKey', $field) || empty($field['taxKey'])){
                continue;
            }

            if(array_key_exists($jsonKey, $job) && !empty($job[$jsonKey])){
                wp_set_object_terms($jobPostID, $job[$jsonKey], $field['taxKey']);
            }
            else {
                wp_set_object_terms($jobPostID, false, $field['taxKey']);
            }
        }
    }
}

/**
 * Deletes old job posts that are not present in the active jobs list.
 *
 * @param array $activeJobs An array of active job post IDs.
 *
 * @return void
 */
function feedimporter_delete_old_jobs($activeJobs){

    if (count($activeJobs) > 0) {
        $expiredJobs = get_posts(array('post_type' => 'job', 'post__not_in' => $activeJobs, 'numberposts' => -1));
        foreach ($expiredJobs as $expiredJob) {
            wp_delete_post($expiredJob->ID, true);
        }
    }
}