<?php
// Register Job Post Type
function fi_register_job_post_type()
{

    $labels = array(
        'name' => _x('Jobs', 'Post Type General Name', 'feed-importer'),
        'singular_name' => _x('Job', 'Post Type Singular Name', 'feed-importer'),
        'menu_name' => __('Jobs', 'feed-importer'),
        'name_admin_bar' => __('Job', 'feed-importer'),
        'archives' => __('Job archives', 'feed-importer'),
        'attributes' => __('Job attributes', 'feed-importer'),
        'parent_item_colon' => __('Parent job:', 'feed-importer'),
        'all_items' => __('All Jobs', 'feed-importer'),
        'add_new_item' => __('Add new jobs', 'feed-importer'),
        'add_new' => __('Add New', 'feed-importer'),
        'new_item' => __('New Job', 'feed-importer'),
        'edit_item' => __('Edit Job', 'feed-importer'),
        'update_item' => __('Update Job', 'feed-importer'),
        'view_item' => __('View Job', 'feed-importer'),
        'view_items' => __('View Jobs', 'feed-importer'),
        'search_items' => __('Search Jobs', 'feed-importer'),
        'not_found' => __('Not found', 'feed-importer'),
        'not_found_in_trash' => __('Not found in Trash', 'feed-importer'),
        'featured_image' => __('Featured Image', 'feed-importer'),
        'set_featured_image' => __('Set featured image', 'feed-importer'),
        'remove_featured_image' => __('Remove featured image', 'feed-importer'),
        'use_featured_image' => __('Use as featured image', 'feed-importer'),
        'insert_into_item' => __('Insert into job', 'feed-importer'),
        'uploaded_to_this_item' => __('Uploaded to this job', 'feed-importer'),
        'items_list' => __('Job list', 'feed-importer'),
        'items_list_navigation' => __('Job list navigation', 'feed-importer'),
        'filter_items_list' => __('Filter job list', 'feed-importer'),
    );
    $args = array(
        'label' => __('Job', 'feed-importer'),
        'description' => __('Contains details of documents', 'feed-importer'),
        'labels' => $labels,
        'supports' => array('title'),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => fi_is_debug_mode_active(),
        'menu_position' => 5,
        'menu_icon' => 'dashicons-portfolio',
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'show_in_rest' => true,
        'can_export' => true,
        'has_archive' => false,
        'exclude_from_search' => false,
        'publicly_queryable' => false,
        'capability_type' => 'page',
        'rewrite' => array(
            'slug' => 'job',
            'with_front' => false
        ),
    );

    register_post_type('job', $args);

}

add_action('init', 'fi_register_job_post_type', 0);


