<?php
/**
 * Checks if debug mode is active based on the plugin settings.
 *
 * @return bool `true` if debug mode is active, `false` otherwise.
 */
function feedimporter_is_debug_mode_active(){


    $options = get_option('feedimporter_settings');

    if (empty($options) || !array_key_exists('debug_mode', $options)) {
        return false;
    }

    if($options['debug_mode'] == 'debug'){
        return true;
    }

    return false;
}

/**
 * Retrieves the feed URL from the plugin settings.
 *
 * @return string|false The feed URL if found, `false` if not found or empty.
 */
function feedimporter_get_feed_url(){

    $options = get_option('feedimporter_settings');

    if (empty($options) || !array_key_exists('feed_url', $options) || empty($options['feed_url'])) {
        return false;
    }

    return $options['feed_url'];
}

/**
 * Retrieves the maximum number of import items from the plugin settings.
 *
 * @return int|false The maximum number of import items if found and valid, `false` otherwise.
 */
function feedimporter_get_max_items(){

    $options = get_option('feedimporter_settings');

    if (empty($options) || !array_key_exists('max_import_items', $options) || empty($options['max_import_items']) || !is_numeric($options['max_import_items']) ) {
        return false;
    }

    return (int) $options['max_import_items'];
}

add_action('admin_menu', 'feedimporter_settings_page');
add_action('admin_init', 'feedimporter_settings_init');

function feedimporter_settings_page()
{
    add_options_page(
        'Feed Importer',
        'Feed Importer',
        'manage_options',
        'feed-importer',
        'feedimporter_plugin_settings'
    );
}

function feedimporter_settings_init()
{
    register_setting('feedimporter_plugin', 'feedimporter_settings');
    add_settings_section(
        'feedimporter_settings_section',
        __('Settings', 'wordpress'),
        'feedimporter_section_intro',
        'feedimporter_plugin'
    );

    add_settings_field(
        'feed_url',
        __('Feed URL', 'wordpress'),
        'feedimporter_feed_url_field_render',
        'feedimporter_plugin',
        'feedimporter_settings_section'
    );

    add_settings_field(
        'max_import_items',
        __('Max Import Items', 'wordpress'),
        'feedimporter_input_field_render',
        'feedimporter_plugin',
        'feedimporter_settings_section',
        array( 'field_id'=> 'max_import_items', 'field_hint' => '(leave empty to bring in all items)')
    );

    add_settings_field(
        'debug_mode',
        __('Debug Mode', 'wordpress'),
        'feedimporter_checkbox_field_render',
        'feedimporter_plugin',
        'feedimporter_settings_section',
        array( 'field_id'=> 'debug_mode', 'field_value' => 'debug')
    );
}

function feedimporter_section_intro()
{
    echo __('Please select the feed you wish to import', 'wordpress');
}

function feedimporter_input_field_render($args)
{
    if(!empty($args) && array_key_exists('field_id', $args)) {
        $options = get_option('feedimporter_settings');
        $field_value = '';
        if (!empty($options) && array_key_exists($args['field_id'], $options)) {
            $field_value = $options[$args['field_id']];
        }
        ?>
        <input type="text" value="<?= $field_value ?>" name='feedimporter_settings[<?= $args['field_id'] ?>]'>
        <?php

        echo $args['field_hint'];
    }
}

function feedimporter_checkbox_field_render($args)
{
    if(!empty($args) && array_key_exists('field_id', $args) && array_key_exists('field_value', $args)) {
        $options = get_option('feedimporter_settings');
        $field_value = '';
        if (!empty($options) && array_key_exists($args['field_id'], $options)) {
            $field_value = $options[$args['field_id']];
        }
        ?>
        <input type="checkbox" value="<?= $args['field_value'] ?>" name='feedimporter_settings[<?= $args['field_id'] ?>]' <?php if($field_value == $args['field_value']){ echo 'checked="checked"';}?>>
        <?php
    }
}

function feedimporter_get_feed_options()
{
    $options = [];


    if (getenv('WP_ENVIRONMENT_TYPE') == 'local') {
        $upload_dir = wp_upload_dir();

       
        $feeds_file = $upload_dir['basedir'] . '/feed-parser/feeds.json';

        $json = file_get_contents($feeds_file);

        if(!$json){
            return $options;
        }
    }
    else {
        if(!defined('S3_UPLOADS_BUCKET') || !defined('S3_UPLOADS_REGION')){
            return $options;
        }
        

        //use bucket secrets to create url 
        $feeds_url = "https://" . S3_UPLOADS_BUCKET . ".s3." . S3_UPLOADS_REGION . ".amazonaws.com/feed-parser/feeds.json";

        $response = wp_remote_get($feeds_url);

        if (is_wp_error($response)) {
            return $options;
        }
    
        $json = $response['body'];

    }

    $data_array = json_decode($json, true);

    if(is_array($data_array)){
        $options = $data_array;
    }
    

    return $options;
}

function feedimporter_feed_url_field_render($args)
{
    $field_value = feedimporter_get_feed_url();

    $feed_options = feedimporter_get_feed_options();

    ?>
    <select name='feedimporter_settings[feed_url]'>
        <option value="">None</option>

        <?php foreach($feed_options as $feed_option){ ?>
            <option value="<?php echo $feed_option['url'];?>" <?php if($field_value == $feed_option['url']){ echo 'selected="selected"';}?>><?php echo $feed_option['name'];?></option>
        <?php
        }
        ?>
    </select>
    <?php
    
}

function feedimporter_plugin_settings()
{
    if(!empty($_POST) && array_key_exists('feed-importer-action', $_POST) && $_POST['feed-importer-action'] == "import" ){
       $result = feedimporter_trigger_import();
       
       if($result){
        
        ?>
            <div id="manual-import-success" class="notice notice-success"> 
<p><strong>Feed has been imported</strong></p></div>
        <?php
       }
    }

    $last_imported = "Not Found";

    $last_import_date = get_option('feedimporter_last_imported_date');

    if(!empty($last_import_date)){
        date_default_timezone_set('Europe/London');
        $last_imported = date('j M Y H:i' , $last_import_date);
    }
    ?>
    <h1>Feed Importer</h1>
    <p><strong>Last Imported:</strong> <?php echo $last_imported; ?></p>
    <form method='post'>
        <input type="hidden" id="feed-importer-action" name="feed-importer-action" value="import"/>
        <?php
         submit_button('Manual Import', 'primary', 'import');
        ?>
    </form>
    <form action='options.php' method='post'>
        
        <?php
        settings_fields('feedimporter_plugin');
        do_settings_sections('feedimporter_plugin');
        submit_button();
        ?>

    </form>
    <?php
}
