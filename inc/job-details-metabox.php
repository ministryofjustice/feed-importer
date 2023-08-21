<?php
add_action('add_meta_boxes', 'feedimporter_add_job_details_metabox');

/**
 * Adds a metabox for displaying job details on the job edit screen.
 *
 * This function adds a metabox to the 'job' post type's edit screen.
 *
 * @return void
 */
function feedimporter_add_job_details_metabox() {
    add_meta_box(
        'feedimporter-job-metabox',
        __('Job Details', 'hale'),
        'feedimporter_render_job_details_metabox',
        'job',
        'normal',
        'high'
    );
}

/**
 * Renders the job details metabox on the job edit screen.
 *
 * This function renders the content of the job details metabox on the 'job' post type's edit screen.
 *
 * @param WP_Post $post The current post object.
 *
 * @return void
 */
function feedimporter_render_job_details_metabox($post)
{

    $job_id = get_post_meta($post->ID, 'job_id', true);
    $job_hash = get_post_meta($post->ID, 'job_hash', true);
    $job_url = get_post_meta($post->ID, 'job_url', true);
    $closing_date = get_post_meta($post->ID, 'job_closing_date', true);
    $salary_min = get_post_meta($post->ID, 'job_salary_min', true);
    $salary_max = get_post_meta($post->ID, 'job_salary_max', true);
    $salary_london = get_post_meta($post->ID, 'job_salary_london', true);
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

    if(!empty($salary_london)){
        echo 'London Weighting Allowance: £' . number_format($salary_london) . '<br/>';
    }

    if(!empty($available_positions)){
        echo 'Number of positions available: ' . $available_positions  . '<br/>';
    }
}