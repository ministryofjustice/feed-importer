<?php

// Register Job Role Type Custom Taxonomy
function feedimporter_job_role_type_custom_taxonomy() {

	$labels = array(
		'name'                       => 'Role Types',
		'singular_name'              => 'Role Type',
		'menu_name'                  => 'Role Types',
		'all_items'                  => 'All Types',
		'parent_item'                => 'Parent Type',
		'parent_item_colon'          => 'Parent Type:',
		'new_item_name'              => 'New Type Name',
		'add_new_item'               => 'Add New Type',
		'edit_item'                  => 'Edit Type',
		'update_item'                => 'Update Type',
		'view_item'                  => 'View Type',
		'separate_items_with_commas' => 'Separate types with commas',
		'add_or_remove_items'        => 'Add or remove types',
		'choose_from_most_used'      => 'Choose from the most used',
		'popular_items'              => 'Popular Types',
		'search_items'               => 'Search Types',
		'not_found'                  => 'Not Found',
		'no_terms'                   => 'No types',
		'items_list'                 => 'Types list',
		'items_list_navigation'      => 'Types list navigation',
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
	);
	register_taxonomy( 'role_type', array( 'job' ), $args );

}
add_action( 'init', 'feedimporter_job_role_type_custom_taxonomy', 0 );

// Register Job Contract Type Custom Taxonomy
function feedimporter_job_contract_type_custom_taxonomy() {

	$labels = array(
		'name'                       => 'Contract Types',
		'singular_name'              => 'Contract Type',
		'menu_name'                  => 'Contract Types',
		'all_items'                  => 'All Types',
		'parent_item'                => 'Parent Type',
		'parent_item_colon'          => 'Parent Type:',
		'new_item_name'              => 'New Type Name',
		'add_new_item'               => 'Add New Type',
		'edit_item'                  => 'Edit Type',
		'update_item'                => 'Update Type',
		'view_item'                  => 'View Type',
		'separate_items_with_commas' => 'Separate types with commas',
		'add_or_remove_items'        => 'Add or remove types',
		'choose_from_most_used'      => 'Choose from the most used',
		'popular_items'              => 'Popular Types',
		'search_items'               => 'Search Types',
		'not_found'                  => 'Not Found',
		'no_terms'                   => 'No types',
		'items_list'                 => 'Types list',
		'items_list_navigation'      => 'Types list navigation',
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
	);
	register_taxonomy( 'contract_type', array( 'job' ), $args );

}
add_action( 'init', 'feedimporter_job_contract_type_custom_taxonomy', 0 );

// Register Job Organisation Custom Taxonomy
function feedimporter_job_organisation_custom_taxonomy() {

	$labels = array(
		'name'                       => 'Organisations',
		'singular_name'              => 'Organisation',
		'menu_name'                  => 'Organisations',
		'all_items'                  => 'All Organisations',
		'parent_item'                => 'Parent Organisation',
		'parent_item_colon'          => 'Parent Organisation:',
		'new_item_name'              => 'New Organisation Name',
		'add_new_item'               => 'Add New Organisation',
		'edit_item'                  => 'Edit Organisation',
		'update_item'                => 'Update Organisation',
		'view_item'                  => 'View Organisation',
		'separate_items_with_commas' => 'Separate organisations with commas',
		'add_or_remove_items'        => 'Add or remove organisations',
		'choose_from_most_used'      => 'Choose from the most used',
		'popular_items'              => 'Popular Organisations',
		'search_items'               => 'Search TyOrganisationspes',
		'not_found'                  => 'Not Found',
		'no_terms'                   => 'No organisations',
		'items_list'                 => 'Organisations list',
		'items_list_navigation'      => 'Organisations list navigation',
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
	);
	register_taxonomy( 'organisation', array( 'job' ), $args );

}
add_action( 'init', 'feedimporter_job_organisation_custom_taxonomy', 0 );

// Register Job Address Custom Taxonomy
function feedimporter_job_address_custom_taxonomy() {

	$labels = array(
		'name'                       => 'Job Addresses',
		'singular_name'              => 'Job Address',
		'menu_name'                  => 'Job Addresses',
		'all_items'                  => 'All Addresses',
		'parent_item'                => 'Parent Address',
		'parent_item_colon'          => 'Parent Address:',
		'new_item_name'              => 'New Address Name',
		'add_new_item'               => 'Add New Address',
		'edit_item'                  => 'Edit Address',
		'update_item'                => 'Update Address',
		'view_item'                  => 'View Address',
		'separate_items_with_commas' => 'Separate addresses with commas',
		'add_or_remove_items'        => 'Add or remove addresses',
		'choose_from_most_used'      => 'Choose from the most used',
		'popular_items'              => 'Popular Addresses',
		'search_items'               => 'Search Addresses',
		'not_found'                  => 'Not Found',
		'no_terms'                   => 'No Addresses',
		'items_list'                 => 'Addresses list',
		'items_list_navigation'      => 'Addresses list navigation',
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
	);
	register_taxonomy( 'job_address', array( 'job' ), $args );

}
add_action( 'init', 'feedimporter_job_address_custom_taxonomy', 0 );

// Register Job City Custom Taxonomy
function feedimporter_job_city_custom_taxonomy() {

	$labels = array(
		'name'                       => 'Job Cities',
		'singular_name'              => 'Job City',
		'menu_name'                  => 'Job Cities',
		'all_items'                  => 'All Cities',
		'parent_item'                => 'Parent City',
		'parent_item_colon'          => 'Parent City:',
		'new_item_name'              => 'New City Name',
		'add_new_item'               => 'Add New City',
		'edit_item'                  => 'Edit City',
		'update_item'                => 'Update City',
		'view_item'                  => 'View City',
		'separate_items_with_commas' => 'Separate cities with commas',
		'add_or_remove_items'        => 'Add or remove Cities',
		'choose_from_most_used'      => 'Choose from the most used',
		'popular_items'              => 'Popular Cities',
		'search_items'               => 'Search Cities',
		'not_found'                  => 'Not Found',
		'no_terms'                   => 'No Cities',
		'items_list'                 => 'Cities list',
		'items_list_navigation'      => 'Cities list navigation',
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
	);
	register_taxonomy( 'job_city', array( 'job' ), $args );

}
add_action( 'init', 'feedimporter_job_city_custom_taxonomy', 0 );

// Register Job Region Custom Taxonomy
function feedimporter_job_region_custom_taxonomy() {

	$labels = array(
		'name'                       => 'Job Regions',
		'singular_name'              => 'Job Region',
		'menu_name'                  => 'Job Regions',
		'all_items'                  => 'All Regions',
		'parent_item'                => 'Parent Region',
		'parent_item_colon'          => 'Parent Region:',
		'new_item_name'              => 'New Region Name',
		'add_new_item'               => 'Add New Region',
		'edit_item'                  => 'Edit Region',
		'update_item'                => 'Update Region',
		'view_item'                  => 'View Region',
		'separate_items_with_commas' => 'Separate region with commas',
		'add_or_remove_items'        => 'Add or remove Regions',
		'choose_from_most_used'      => 'Choose from the most used',
		'popular_items'              => 'Popular Regions',
		'search_items'               => 'Search Regions',
		'not_found'                  => 'Not Found',
		'no_terms'                   => 'No Regions',
		'items_list'                 => 'Regions list',
		'items_list_navigation'      => 'Regions list navigation',
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
	);
	register_taxonomy( 'job_region', array( 'job' ), $args );

}
add_action( 'init', 'feedimporter_job_region_custom_taxonomy', 0 );