<?php

add_action('admin_menu', 'fi_settings_page');
add_action('admin_init', 'fi_settings_init');

function fi_settings_page()
{
    add_options_page(
        'Feed Importer',
        'Feed Importer',
        'manage_options',
        'feed-importer',
        'fi_plugin_settings'
    );
}

function fi_settings_init()
{
    register_setting('fi_plugin', 'fi_settings');
    add_settings_section(
        'fi_settings_section',
        __('Settings', 'wordpress'),
        'fi_section_intro',
        'fi_plugin'
    );

    add_settings_field(
        'feed_url',
        __('Feed URL', 'wordpress'),
        'fi_feed_url_field_render',
        'fi_plugin',
        'fi_settings_section'
    );

    add_settings_field(
        'debug_mode',
        __('Debug Mode', 'wordpress'),
        'fi_checkbox_field_render',
        'fi_plugin',
        'fi_settings_section',
        array( 'field_id'=> 'debug_mode', 'field_value' => 'debug')
    );

    /*
    add_settings_field(
        'fi_feed_url',
        __('Feed URL', 'wordpress'),
        'fi_input_field_render',
        'fi_plugin',
        'fi_settings_section',
        array( 'field_id'=> 'fi_feed_url')
    );*/

}

function fi_section_intro()
{

    echo __('Please enter the URL of the feed you wish to import', 'wordpress');
}

function fi_input_field_render($args)
{
    if(!empty($args) && array_key_exists('field_id', $args)) {
        $options = get_option('fi_settings');
        $field_value = '';
        if (!empty($options) && array_key_exists($args['field_id'], $options)) {
            $field_value = $options[$args['field_id']];
        }
        ?>
        <input type="text" value="<?= $field_value ?>" name='fi_settings[<?= $args['field_id'] ?>]'>
        <?php
    }
}

function fi_checkbox_field_render($args)
{
    if(!empty($args) && array_key_exists('field_id', $args) && array_key_exists('field_value', $args)) {
        $options = get_option('fi_settings');
        $field_value = '';
        if (!empty($options) && array_key_exists($args['field_id'], $options)) {
            $field_value = $options[$args['field_id']];
        }
        ?>
        <input type="checkbox" value="<?= $args['field_value'] ?>" name='fi_settings[<?= $args['field_id'] ?>]' <?php if($field_value == $args['field_value']){ echo 'checked="checked"';}?>>
        <?php
    }
}

function fi_get_feed_options()
{
    $options = [];

    if(!defined('S3_UPLOADS_BUCKET') || !defined('S3_UPLOADS_REGION')){
        return $options;
    }

    //use bucket secrets to create url 
    $url = "https://" . S3_UPLOADS_BUCKET . ".s3." . S3_UPLOADS_REGION . ".amazonaws.com/feed-parser/feeds.json";

    $response = wp_remote_get($url);

    if (is_wp_error($response)) {
        return $options;
    }

    $json = $response['body'];
    $data_array = json_decode($json, true);

    if(is_array($data_array)){
        $options = $data_array;
    }

    return $options;
}

function fi_feed_url_field_render($args)
{
    $options = get_option('fi_settings');
    $field_value = '';
    if (!empty($options) && array_key_exists('feed_url', $options)) {
        $field_value = $options['feed_url'];
    }

    $feed_options = fi_get_feed_options();

    ?>
    <select name='fi_settings[feed_url]'>
        <option value="">None</option>

        <?php foreach($feed_options as $feed_option){ ?>
            <option value="<?php echo $feed_option['url'];?>" <?php if($field_value == $feed_option['url']){ echo 'selected="selected"';}?>><?php echo $feed_option['name'];?></option>
        <?php
        }
        ?>
    </select>
    <?php
    
}

function fi_plugin_settings()
{
    ?>
    <h1>Feed Importer</h1>

    <form action='options.php' method='post'>
        
        <?php
        settings_fields('fi_plugin');
        do_settings_sections('fi_plugin');
        submit_button();
        ?>

    </form>
    <?php
}
