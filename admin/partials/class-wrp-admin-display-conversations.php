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
?>


<div class="sss-client">
    <h3>Conversations</h3>
    <p>Reply to user messages, close, delete conversations and more</p>


    <?php echo isset($_GET['wpr_show_hidden']) && sanitize_text_field($_GET['wpr_show_hidden']) == 1 ? '<a href="/wp-admin/admin.php?page=wpr_conversations&wpr_show_hidden=0">Show all</a>' : '<a href="/wp-admin/admin.php?page=wpr_conversations&wpr_show_hidden=1">Show hidden</a>' ?>

    &nbsp;
    &nbsp;

    <a href="#" class="wpr--clear-chats">Clear all chats</a>

    <br>
    <br>

    <div class="wpr--notifications--view">

    </div>

    <div class="wpr-conversations">
        <div class="wpr-chat-sidebar">

            <?php
            // Set the number of items to display per page
            $items_per_page = 15;

            // Get the current page number
            $current_page = isset($_GET['cp']) ? absint($_GET['cp']) : 1;
            $chat_session = isset($_GET['chat_session']) ? sanitize_text_field($_GET['chat_session']) : "";

            // Calculate the offset for the SQL query
            $offset = max(0, ($current_page - 1) * $items_per_page);

            // Query the database to get the paginated items
            global $wpdb;
            $table_name = $wpdb->prefix . 'wpr_chats'; // Replace with your table name
            $is_hidden = isset($_GET['wpr_show_hidden']) ? (sanitize_text_field($_GET['wpr_show_hidden']) == 1 ? 1 : 0) : 0;
            $query = "SELECT * FROM $table_name WHERE is_hidden = $is_hidden LIMIT $items_per_page OFFSET $offset";
            $results = $wpdb->get_results($query);

            $results_json = [];

            // Display the paginated items
            if (!empty($results)) {
                foreach ($results as $item) {
                    $active_class = ($chat_session == $item->ft_user) ? 'active' : '';
                    $user = add_query_arg(['chat_session' =>  $item->ft_user]);
                    echo '<div class="wpr-chat-sidebar--user">
                        <a href="' . esc_url($user) . '" class="' . $active_class . '">' . $item->ft_user . '</a>
                        <small class="wpr-msg--date">' . $item->created_at . '</small>
                    </div>';

                    array_push($results_json, [
                        'ft_user' => $item->ft_user,
                        'chat' => unserialize($item->chat),
                        'ft_enabled' => $item->ft_enabled,
                        'is_hidden' => $item->is_hidden,
                    ]);
                }
            } else {
                echo 'No messages to show yet';
            }

            $results_json = json_encode($results_json);

            // Calculate the total number of items
            $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

            // Calculate the total number of pages
            $total_pages = ceil($total_items / $items_per_page);

            // Generate the pagination links
            if ($total_pages > 1) {
                echo '
                <br>
                <br>
                <div class="pagination">';
                for ($i = 1; $i <= $total_pages; $i++) {
                    $active_class = ($current_page == $i) ? 'active' : '';
                    $pagination_link = add_query_arg(['cp' =>  $i]);
                    echo '<a href="' . esc_url($pagination_link) . '" class="' . $active_class . '">' . $i . '</a>';
                }
                echo '</div>';
            }
            ?>
        </div>

        <div class="wpr-chat-view">
            <?php if ($chat_session) {
                echo '<div class="wpr-chat-view--header"> </div>';
            }
            ?>

            <div class="wpr-chat-view-messages"></div>
            <div class="wpr-chat-view-input"></div>
        </div>
    </div>

    <br>


    <script>
        jQuery(document).ready(function($) {
            let ajaxurl = '<?php echo admin_url('admin-ajax.php') ?>';
            let wprchat_session = '<?php echo $chat_session ?>';
            let conversations = <?php echo $results_json; ?>;

            if (!conversations.length) {
                $(".wpr-chat-view-messages").html("No conversations to show")
            } else {
                $('.wpr-chat-view--header').html(`
                    <button title="Enables you to take or give reply control to the bot" class="wpr-${ conversations[0].ft_enabled == 1 ? 'pause' : 'resume'}-ai--btn">${ conversations[0].ft_enabled == 1 ? 'Pause AI auto-reply for this chat' : 'Resume AI auto-reply for this chat'}</button>
                    <button title="Removes chat from default chats view" class="wpr-${ conversations[0].is_hidden == 0 ? 'hide' : 'unhide'}-chat--btn">${ conversations[0].is_hidden == 1 ? 'Unhide chat' : 'Hide chat'}</button>
                    <button class="wpr-delete-chat--btn">Delete chat?</button>
                `)
            }

            let key = '<?php echo get_option("wpr_pusher_api_key") ?>';
            let cluster = '<?php echo get_option("wpr_pusher_api_cluster") ?>';
            var pusher = new Pusher(key, {
                cluster: cluster,
            });

            var channel = pusher.subscribe('<?php echo get_option("wpr_pusher_channel_key") ?>');
            channel.bind("wpr-admin--notification--event", (data) => {
                $('.wpr--notifications--view').html(`
                    <div class="wpr--notifications">
                        <strong>You have anew message <i class="fa fa-bell"></i></strong>
                        <span class="wpr--notification-message">${data.message}</span>
                        <a href="/wp-admin/admin.php?page=wpr_conversations&chat_session=${data.from}">View message</a>
                    </div>
                `)
            });

            let ba = [
                'pause-ai',
                'resume-ai',
                'delete-chat',
                'hide-chat',
                'unhide-chat'
            ];
            ba.forEach(i => {
                $(`.wpr-${i}--btn`).click((e) => {
                    update_chat(i.split('-')[0], '<?php echo $_GET['chat_session'] ?>');
                })
            })

            if (wprchat_session) {
                let messages = []
                conversations[0].chat.map(item => {
                    Object.keys(item).map(i => {
                        messages.push({
                            content: item[i],
                            owner: i == 'Question' ? 'me' : 'other'
                        })
                    })
                })

                paint_messages({
                    user: wprchat_session,
                    messages
                })
            }

            function paint_messages(data, single = false) {
                let chat_container = $(".wpr-chat-view-messages")
                let chat_container_ = document.getElementsByClassName("wpr-chat-view-messages")[0]

                if (single && data && data.content.length) {
                    let lsc = "wrp-chat-bubble " + data.owner;
                    chat_container.append(`<div class="${lsc}"><p>${data.content}</p></div>`)
                    chat_container_.scrollTop = chat_container_.scrollHeight

                    return;
                }


                chat_container.html("")

                if (!data || !data.messages) return;
                data.messages.forEach((i) => {
                    if (i.content.length) {
                        let lsc = "wrp-chat-bubble " + i.owner;
                        chat_container.append(`<div class="${lsc}"><p>${i.content}</p></div>`)
                    }
                })

                $(".wpr-chat-view-input").html(`
                    <div class="tool-menu">
                        <input type="text" class="chat" placeholder="Type your message here ... " autofocus />
                        <button type="submit" class="send button">Send</button>
                    </div>
                `)

                $("button.send").click((e) => {
                    $("button.send").html("Sending...")
                    $(this).attr('disabled', true)
                    sendmessage($("input.chat").val(), data.user)
                })

                chat_container_.scrollTop = chat_container_.scrollHeight
            }

            function update_chat(todo, user) {
                let data = {
                    action: "wpr_chat_update",
                    perform: todo,
                    wprchat_session: user
                }

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data,
                    success: function(response) {
                        window.location.reload()
                    }
                });
            }

            function sendmessage(msg, user) {
                $("input.chat").val("")
                paint_messages({
                    content: msg,
                    owner: "other"
                }, true)

                let data = {
                    action: "wpr_send_reply",
                    msg,
                    wprchat_session: user
                }

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data,
                    success: function(response) {
                        $("button.send").attr('disabled', false)
                        $("button.send").html("Send")
                    }
                });
            }
        })
    </script>