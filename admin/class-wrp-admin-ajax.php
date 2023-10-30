<?php

/**
 * Ajax handler
 *
 * @link       https://github.com/maxwellmandela
 * @since      1.0.0
 *
 * @package    WRP
 * @subpackage WRP/admin
 */
/**
 * Ajax handler
 *
 * Ajax handler for all http requests
 *
 * @package    WRP
 * @subpackage WRP/admin
 * @author     Maxwell Mandela <mxmandela@gmail.com>
 */
// Load your project's composer autoloader (if you aren't already doing so).
require_once plugin_dir_path( __FILE__ ) . "../vendor/autoload.php";
require_once plugin_dir_path( __FILE__ ) . "../includes/class-wpr-conversation.php";
use  Tectalic\OpenAi\Authentication ;
use  Tectalic\OpenAi\Client ;
use  Tectalic\OpenAi\Manager ;
use  GuzzleHttp\Client as HttpClient ;
use  GuzzleHttp\Exception\RequestException ;
use  WPR\Conversation ;
class WRP_Admin_Ajax extends Conversation
{
    // Build a Tectalic OpenAI REST API Client manually.
    private  $auth ;
    private  $httpClient ;
    private  $client ;
    // You can change this but you must handle the rest of the tokens past this limit yourself
    // We plan to include this in the next release so look out for our mail!
    private  $maxTokenLength = 8000 ;
    public function __construct()
    {
        $actions = [ 'wpr_chat_update', 'wpr_load_chat_history', 'wpr_send_reply' ];
        foreach ( $actions as $key ) {
            add_action( 'wp_ajax_' . $key, array( $this, $key ) );
        }
        // Build a Tectalic OpenAI REST API Client manually.
        $this->auth = new Authentication( get_option( 'wpr_openai_key' ) );
        $this->httpClient = new HttpClient();
        $this->client = new Client( $this->httpClient, $this->auth, Manager::BASE_URI );
    }
    
    function get_current_url()
    {
        $currentURL = 'http';
        if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ) {
            $currentURL .= 's';
        }
        $currentURL .= '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        return $currentURL;
    }
    
    public function wpr_send_reply()
    {
        $msg = sanitize_text_field( $_POST['msg'] );
        $wprchat_session = sanitize_text_field( $_POST['wprchat_session'] );
        $this->wpr_reply( $wprchat_session, $msg );
        wp_send_json_success( [
            'success' => true,
        ] );
    }
    
    public function wpr_chat_update()
    {
        $action = sanitize_text_field( $_POST['perform'] );
        $wprchat_session = sanitize_text_field( $_POST['wprchat_session'] );
        if ( $action == 'pause' ) {
            $this->update_chat( $wprchat_session, [
                'ft_enabled' => 0,
            ] );
        }
        if ( $action == 'resume' ) {
            $this->update_chat( $wprchat_session, [
                'ft_enabled' => 1,
            ] );
        }
        if ( $action == 'hide' ) {
            $this->update_chat( $wprchat_session, [
                'is_hidden' => 1,
            ] );
        }
        if ( $action == 'unhide' ) {
            $this->update_chat( $wprchat_session, [
                'is_hidden' => 0,
            ] );
        }
        if ( $action == 'delete' ) {
            $this->delete_chat( $wprchat_session );
        }
        wp_send_json_success( [
            'success' => true,
        ] );
    }

}
$ajax = new WRP_Admin_Ajax();