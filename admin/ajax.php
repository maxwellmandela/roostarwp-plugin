<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/maxwellmandela
 * @since      0.2.1
 *
 * @package    WRP
 * @subpackage WRP/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WRP
 * @subpackage WRP/admin
 * @author     Maxwell Mandela <mxmandela@gmail.com>
 */
function sssclient_completion_endpoint()
{
    register_rest_route('wpr/v1', '/chat', array(
        'methods' => 'POST',
        'callback' => 'completion',
    ));
}

add_action('rest_api_init', 'sssclient_completion_endpoint');

function sssclient_completion()
{
    wp_send_json_success("Here is a nice reply!");
}

add_action('wp_ajax_save_api_token', 'sssclient_save_api_token');
function sssclient_save_api_token()
{
    try {
        check_ajax_referer('title_example');
        $token = sanitize_text_field($_POST['api_token']);
        $is_accepted_terms = sanitize_text_field($_POST['accept_terms']);

        if ($is_accepted_terms != 'on') {
            return wp_send_json_error('Please the terms and conditions');
        }

        add_user_meta(wp_get_current_user()->ID, 'sss_client_service_api_token', $token, true);
        wp_send_json_success("Finished saving configurations");
    } catch (Exception $e) {
        wp_send_json_error("Sorry, an error occured");
    }
}
