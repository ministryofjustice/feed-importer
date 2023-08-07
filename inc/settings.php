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
        'fi_feed_url',
        __('Feed URL', 'wordpress'),
        'fi_input_field_render',
        'fi_plugin',
        'fi_settings_section',
        array( 'field_id'=> 'fi_feed_url')
    );

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

function fi_plugin_settings()
{
    ?>
    <form action='options.php' method='post'>

        <h1>Feed Importer</h1>

        <?php
        settings_fields('fi_plugin');
        do_settings_sections('fi_plugin');
        submit_button();
        ?>

    </form>
    <?php
}
