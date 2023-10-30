<?php

/**
 * Defines all conversation logic
 *
 * @since      1.0.0
 * @package    WRP
 * @subpackage WRP/includes
 * @author     Maxwell Mandela <mxmandela@gmail.com>
 */

namespace WPR;

require_once plugin_dir_path(__FILE__) . "../vendor/autoload.php";

use Pusher\Pusher;

class Conversation
{

    public function wpr_chat_history()
    {
        $wprchat_session = sanitize_text_field($_POST['wprchat_session']);
        $chat_history = $this->load_chat_history($wprchat_session);
        wp_send_json_success($chat_history);
        wp_die();
    }

    private function load_chat_history($wprchat_session)
    {
        global $wpdb;
        $chat_history =  $wpdb->get_results("SELECT ft_user, chat FROM {$wpdb->base_prefix}wpr_chats WHERE ft_user='{$wprchat_session}'");
        if (!count($chat_history)) {
            $chat_history = $this->create_chat_history($wprchat_session, []);
        } else {
            $chat_history = unserialize($chat_history[0]->chat);
        }

        return $chat_history;
    }

    private function create_chat_history($wprchat_session, $chat_history)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpr_chats';
        $data = array(
            'ft_user' => $wprchat_session,
            'chat' => serialize($chat_history),
            'created_at' => current_time('mysql')
        );
        $wpdb->insert($table_name, $data);
        if ($wpdb->insert_id) {
            $chat_history = [];
        } else {
            echo "Error inserting data: " . $wpdb->last_error;
        }

        return $chat_history;
    }

    public function wpr_reply($wprchat_session, $msg)
    {
        $chat_history = $this->load_chat_history($wprchat_session);

        $data = array_push($chat_history, [
            'Question' => "",
            'Answer'   => $msg
        ]);

        $this->update_chat_history($wprchat_session, $chat_history);
        $this->trigger_msg_push($wprchat_session, $msg);
    }

    private function update_chat_history($wprchat_session, $chat_history)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpr_chats';
        $data = array(
            'chat' => serialize($chat_history)
        );
        $where = array(
            'ft_user' => $wprchat_session
        );
        $wpdb->update($table_name, $data, $where);
        if (!$wpdb->rows_affected) {
            echo "Error udate data: " . $wpdb->last_error;
        }

        return $chat_history;
    }

    public function update_chat($user, $data)
    {
        try {
            global $wpdb;
            $table_name = $wpdb->prefix . 'wpr_chats';
            $where = array(
                'ft_user' => $user
            );
            $wpdb->update($table_name, $data, $where);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function delete_chat($user)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpr_chats';
        $where = array(
            'ft_user' => $user
        );
        $wpdb->delete($table_name, $where);
        if (!$wpdb->rows_affected) {
            echo "Error deleting data: " . $wpdb->last_error;
        }
    }

    public function trigger_msg_push($wprchat_session, $msg)
    {

        if (get_option('wpr_pusher_api_id')) {
            $app_id = get_option('wpr_pusher_api_id');
            $key = get_option('wpr_pusher_api_key');
            $secret = get_option('wpr_pusher_api_secret');
            $cluster = get_option('wpr_pusher_api_cluster');

            $pusher = new Pusher($key, $secret, $app_id, array('cluster' => $cluster));
            $pusher->trigger($wprchat_session, 'wpr-new-msg--event', array('message' => $msg));
        }
    }
}
