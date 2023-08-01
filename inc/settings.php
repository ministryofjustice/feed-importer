<?php

add_action('admin_menu', 'ff_settings_page');
add_action('admin_init', 'ff_settings_init');

function ff_settings_page()
{
    add_options_page(
        'Feed Fixer',
        'Feed Fixer',
        'manage_options',
        'feed-fixer',
        'ff_plugin_settings'
    );
}

function ff_settings_init()
{
    register_setting('ff_plugin', 'ff_settings');
    add_settings_section(
        'ff_settings_section',
        __('Settings', 'wordpress'),
        'ff_section_intro',
        'ff_plugin'
    );

    add_settings_field(
        'ff_notify_feed_url',
        __('Feed URL', 'wordpress'),
        'ff_input_field_render',
        'ff_plugin',
        'ff_settings_section',
        array( 'field_id'=> 'ff_feed_url')
    );

}

function ff_section_intro()
{

    echo __('Please enter the URL of the feed you wish to import', 'wordpress');
}

function ff_input_field_render($args)
{
    if(!empty($args) && array_key_exists('field_id', $args)) {
        $options = get_option('ff_settings');
        $field_value = '';
        if (!empty($options) && array_key_exists($args['field_id'], $options)) {
            $field_value = $options[$args['field_id']];
        }
        ?>
        <input type="text" value="<?= $field_value ?>" name='ff_settings[<?= $args['field_id'] ?>]'>
        <?php
    }
}

function ff_plugin_settings()
{
    ?>
    <form action='options.php' method='post'>

        <h1>Feed Fixer</h1>

        <?php
        settings_fields('ff_plugin');
        do_settings_sections('ff_plugin');
        submit_button();
        ?>

    </form>
    <?php
}
