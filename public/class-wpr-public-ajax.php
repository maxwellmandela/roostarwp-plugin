<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/maxwellmandela
 * @since      1.0.0
 *
 * @package    WRP
 * @subpackage WRP/public
 */
/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    WRP
 * @subpackage WRP/public
 * @author     Maxwell Mandela <mxmandela@gmail.com>
 */
require_once plugin_dir_path( __FILE__ ) . "../vendor/autoload.php";
require_once plugin_dir_path( __FILE__ ) . "partials/wpr-public-display.php";
use  Tectalic\OpenAi\Authentication ;
use  Tectalic\OpenAi\Client ;
use  Tectalic\OpenAi\Manager ;
use  GuzzleHttp\Client as HttpClient ;
use  GuzzleHttp\Exception\RequestException ;
use  Pusher\Pusher ;
class WRP_Ajax
{
    // Build a Tectalic OpenAI REST API Client manually.
    private  $auth ;
    private  $httpClient ;
    private  $client ;
    // private $chat_history = [];
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private  $plugin_name ;
    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private  $version ;
    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version )
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        // Build a Tectalic OpenAI REST API Client manually.
        $this->auth = new Authentication( get_option( 'wpr_openai_key' ) );
        $this->httpClient = new HttpClient();
        $this->client = new Client( $this->httpClient, $this->auth, Manager::BASE_URI );
    }
    
    public function wpr_chat_history()
    {
        $wprchat_session = sanitize_text_field( $_POST['wprchat_session'] );
        $chat_history = $this->load_chat_history( $wprchat_session );
        wp_send_json_success( $chat_history['history'] );
        wp_die();
    }
    
    public function run_completion()
    {
        try {
            $agent = file_get_contents( plugin_dir_path( dirname( __FILE__ ) ) . 'admin/data/embedding.json' );
            $embeddings = json_decode( $agent, true );
            $wprchat_session = sanitize_text_field( $_POST['wprchat_session'] );
            $hist = "";
            $chat_history = [];
            $is_chat_mem_enabled = get_option( 'wpr_enable_conversation_memory' ) == 'enable';
            $is_wpr_enable_auto_reply = get_option( 'wpr_enable_auto_reply' ) == 'enable';
            $chistory = $this->load_chat_history( $wprchat_session );
            $chat_history = $chistory['history'];
            for ( $i = 0 ;  $i < count( $chat_history ) ;  $i++ ) {
                if ( isset( $chat_history[$i]['Question'] ) ) {
                    $hist = $hist . " \n Question: " . $chat_history[$i]['Question'] . ", Answer: " . $chat_history[$i]['Answer'];
                }
            }
            $uquestion = sanitize_text_field( $_POST['msg'] );
            $completion = "";
            $davanci = "";
            /**
             * Save chat to history, useful for conversation context in the prompt
             */
            array_push( $chat_history, [
                'Question' => $uquestion,
                'Answer'   => $completion,
            ] );
            $this->update_chat_history( $wprchat_session, $chat_history );
            // send to admin
            $this->trigger_msg_push( $wprchat_session, $uquestion );
            wp_send_json_success( [
                'completion'        => $completion,
                'usage'             => json_encode( $result->usage ),
                'prompt'            => $davanci,
                'history'           => $chat_history,
                'enable_ai_mem'     => get_option( 'wpr_enable_conversation_memory' ) == 'enable',
                'enable_auto_reply' => get_option( 'wpr_enable_auto_reply' ) == 'enable',
            ] );
            wp_die();
        } catch ( \Throwable $th ) {
            throw $th;
            wp_send_json_success( "Thank you, we shall reach out ASAP!" );
            wp_die();
        }
    }
    
    private function load_chat_history( $wprchat_session )
    {
        global  $wpdb ;
        $res = $wpdb->get_results( "SELECT ft_user, chat, ft_enabled FROM {$wpdb->base_prefix}wpr_chats WHERE ft_user='{$wprchat_session}'" );
        $chat_history = [];
        
        if ( !count( $res ) ) {
            $chat_history = $this->create_chat_history( $wprchat_session, [] );
        } else {
            $chat_history = unserialize( $res[0]->chat );
        }
        
        return [
            'history'    => $chat_history,
            'ai_enabled' => ( $res[0]->ft_enabled == 1 ? true : false ),
        ];
    }
    
    private function create_chat_history( $wprchat_session, $chat_history )
    {
        global  $wpdb ;
        $table_name = $wpdb->prefix . 'wpr_chats';
        $data = array(
            'ft_user'    => $wprchat_session,
            'chat'       => serialize( $chat_history ),
            'created_at' => current_time( 'mysql' ),
        );
        $wpdb->insert( $table_name, $data );
        
        if ( $wpdb->insert_id ) {
            $chat_history = [];
        } else {
            echo  "Error inserting data: " . $wpdb->last_error ;
        }
        
        return $chat_history;
    }
    
    private function update_chat_history( $wprchat_session, $chat_history )
    {
        global  $wpdb ;
        $table_name = $wpdb->prefix . 'wpr_chats';
        $data = array(
            'chat' => serialize( $chat_history ),
        );
        $where = array(
            'ft_user' => $wprchat_session,
        );
        $wpdb->update( $table_name, $data, $where );
        if ( !$wpdb->rows_affected ) {
            echo  "Error inserting data: " . $wpdb->last_error ;
        }
        return $chat_history;
    }
    
    public function trigger_msg_push( $from, $msg )
    {
        
        if ( get_option( 'wpr_pusher_api_id' ) ) {
            $app_id = get_option( 'wpr_pusher_api_id' );
            $key = get_option( 'wpr_pusher_api_key' );
            $secret = get_option( 'wpr_pusher_api_secret' );
            $cluster = get_option( 'wpr_pusher_api_cluster' );
            $pusher = new Pusher(
                $key,
                $secret,
                $app_id,
                array(
                'cluster' => $cluster,
            )
            );
            $channel = get_option( 'wpr_pusher_channel_key' );
            $pusher->trigger( $channel, 'wpr-admin--notification--event', array(
                'message' => $msg,
                'from'    => $from,
            ) );
        }
    
    }

}