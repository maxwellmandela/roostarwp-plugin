<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://github.com/maxwellmandela
 * @since      1.0.0
 *
 * @package    WRP
 * @subpackage WRP/admin/partials
 */

// settings and sections
function wpr_config_register_settings()
{
    add_settings_section('wpr_config_section', 'General Settings', 'wpr_config_section_callback', 'wpr_config_settings_page');

    add_settings_section('wpr_config_realtime_section', 'Realtime chat Settings', 'wpr_config_realtime_section_callback', 'wpr_config_settings_page');

    register_setting('wpr_config_settings', 'wpr_openai_key', 'sanitize_text_field');

    /**
     * Chat settings
     */
    register_setting('wpr_config_settings', 'wpr_enable_auto_reply', 'sanitize_text_field');
    register_setting('wpr_config_settings', 'wpr_enable_conversation_memory', 'sanitize_text_field');


    /**
     * Pusher API settings
     */
    register_setting('wpr_config_settings', 'wpr_pusher_api_id', 'sanitize_text_field');
    register_setting('wpr_config_settings', 'wpr_pusher_api_key', 'sanitize_text_field');
    register_setting('wpr_config_settings', 'wpr_pusher_api_secret', 'sanitize_text_field');
    register_setting('wpr_config_settings', 'wpr_pusher_api_cluster', 'sanitize_text_field');
}

function wpr_config_section_callback()
{
    echo 'General settings';
}

function wpr_config_realtime_section_callback()
{
    echo 'Configure the realtime values here, these enables realtime replies to the customer';
}

// settings page
function wpr_config_create_settings_page()
{
    add_submenu_page(
        'wpr-home',
        'Roostar Settings',
        'Settings',
        'manage_options',
        'wpr_config_settings_page',
        'wpr_config_render_settings_page',
        7
    );
}

// render the settings page
function wpr_config_render_settings_page()
{
?>
    <div class="wrap">
        <form method="post" action="options.php">
            <?php
            settings_fields('wpr_config_settings');
            do_settings_sections('wpr_config_settings_page');
            submit_button();
            ?>
        </form>
    </div>
<?php
}


/**
 * OpenAI
 */
function wpr_config_wpr_openai_key_field_callback()
{
    $wpr_openai_key = get_option('wpr_openai_key');
    echo '<input type="text" name="wpr_openai_key" value="' . esc_attr($wpr_openai_key) . '" />';
}



/**
 * Chat settings
 */
function wpr_enable_auto_reply_field_callback()
{
    $wpr_enable_auto_reply = get_option('wpr_enable_auto_reply');
?>
    <label>
        <input type="radio" name="wpr_enable_auto_reply" value="enable" <?php checked('enable', $wpr_enable_auto_reply); ?> />
        Enable
    </label>
    <br>
    <label>
        <input type="radio" name="wpr_enable_auto_reply" value="disable" <?php checked('disable', $wpr_enable_auto_reply); ?> />
        Disable
    </label>
<?php
}

function wpr_enable_conversation_memory_field_callback()
{
    $wpr_enable_auto_reply = get_option('wpr_enable_conversation_memory');
?>
    <label>
        <input type="radio" name="wpr_enable_conversation_memory" value="enable" <?php checked('enable', $wpr_enable_auto_reply); ?> />
        Enable
    </label>
    <br>
    <label>
        <input type="radio" name="wpr_enable_conversation_memory" value="disable" <?php checked('disable', $wpr_enable_auto_reply); ?> />
        Disable
    </label>
<?php
}


/**
 * Pusher
 */
function wpr_pusher_api_id_field_callback()
{
    $wpr_pusher_api_id = get_option('wpr_pusher_api_id');
    echo '<input type="text" name="wpr_pusher_api_id" value="' . esc_attr($wpr_pusher_api_id) . '" />';
}
function wpr_pusher_api_key_field_callback()
{
    $wpr_pusher_api_key = get_option('wpr_pusher_api_key');
    echo '<input type="text" name="wpr_pusher_api_key" value="' . esc_attr($wpr_pusher_api_key) . '" />';
}
function wpr_pusher_api_secret_field_callback()
{
    $wpr_pusher_api_secret = get_option('wpr_pusher_api_secret');
    echo '<input type="text" name="wpr_pusher_api_secret" value="' . esc_attr($wpr_pusher_api_secret) . '" />';
}
function wpr_pusher_api_cluster_field_callback()
{
    $wpr_pusher_api_cluster = get_option('wpr_pusher_api_cluster');
    echo '<input type="text" name="wpr_pusher_api_cluster" value="' . esc_attr($wpr_pusher_api_cluster) . '" />';
}

/**
 * Run hooks
 */

add_action('admin_init', 'wpr_config_register_settings');
add_action('admin_menu', 'wpr_config_create_settings_page');

add_action('admin_init', function () {
    add_settings_field('wpr_openai_key_field', 'OpenAI API Key', 'wpr_config_wpr_openai_key_field_callback', 'wpr_config_settings_page', 'wpr_config_section');

    add_settings_field('wpr_auto_reply_field', 'Enable AI in livechat?(Disabling will stop auto-reply)', 'wpr_enable_auto_reply_field_callback', 'wpr_config_settings_page', 'wpr_config_section');

    $field_desc = 'Enable Chatbot memory in livechat?(Makes the bot remember the conversation context. Enabling this may increase costs a bit)';
    add_settings_field('wpr_enable_conversation_memory_field', $field_desc, 'wpr_enable_conversation_memory_field_callback', 'wpr_config_settings_page', 'wpr_config_section');

    add_settings_field('wpr_pusher_api_id_field', "Pusher API ID", 'wpr_pusher_api_id_field_callback', 'wpr_config_settings_page', 'wpr_config_realtime_section');
    add_settings_field('wpr_pusher_api_key_field', "Pusher API Key", 'wpr_pusher_api_key_field_callback', 'wpr_config_settings_page', 'wpr_config_realtime_section');
    add_settings_field('wpr_pusher_api_secret_field', "Pusher API Secret", 'wpr_pusher_api_secret_field_callback', 'wpr_config_settings_page', 'wpr_config_realtime_section');
    add_settings_field('wpr_pusher_api_cluster_field', "Pusher API Cluster", 'wpr_pusher_api_cluster_field_callback', 'wpr_config_settings_page', 'wpr_config_realtime_section');
});
