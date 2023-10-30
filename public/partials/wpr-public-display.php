<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://github.com/maxwellmandela
 * @since      0.3.1
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/public/partials
 */
add_action('wp_footer', 'my_ajax_without_file');
function my_ajax_without_file()
{ ?>

    <script type="text/javascript">
        jQuery(document).ready(function($) {

            ajaxurl = '<?php echo admin_url('admin-ajax.php') ?>'; // get ajaxurl

            let messages = [{
                content: "Hello there, what can I help you with today?",
                owner: "other"
            }]

            let random_room_number = localStorage.getItem('wpr_chat_session')
            if (!random_room_number) {
                random_room_number = 'wprchat_' + Math.random().toString(20).substr(2, 10) + Date.now()
                localStorage.setItem('wpr_chat_session', random_room_number)
                paint_messages()
            } else {
                jQuery.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: "wpr_chat_history",
                        wprchat_session: random_room_number

                    },
                    success: function(response) {
                        response.data.map(item => {
                            Object.keys(item).map(i => {
                                messages.push({
                                    content: item[i],
                                    owner: i == 'Question' ? 'me' : 'other'
                                })
                            })
                        })

                        paint_messages()
                    }
                });
            }

            let key = '<?php echo get_option("wpr_pusher_api_key") ?>';
            let cluster = '<?php echo get_option("wpr_pusher_api_cluster") ?>';
            var pusher = new Pusher(key, {
                cluster: cluster,
            });
            var channel = pusher.subscribe(random_room_number);
            channel.bind("wpr-new-msg--event", (data) => {
                paint_messages({
                    content: data.message,
                    owner: 'other'
                })
            });


            $("body").append(`
				<div class="wrp-chat-widget">
					<div class="wrp-chat-widget-header">
						<button class="wrp-toggle-chat">Talk to us!</button>
					</div>

					<div class="wrp-chat-view hidden">
						<div class="wrp-container">
						</div>

						<div class="tool-menu">
							<input type="text" class="chat" placeholder="Type your message here ... " autofocus />
							<button type="submit" class="send button">Send</button>
						</div>
						<style id="custom-style" scoped>
						</style>
					</div>
				</div>
			`)

            $(".wrp-toggle-chat").click((event) => {
                let action = $(".wrp-chat-view").hasClass("hidden") ? "show" : "hidden"
                $(".wrp-chat-view").addClass(action)
                $(".wrp-chat-view").removeClass(action == "hidden" ? "show" : "hidden")
                $(".wrp-toggle-chat").html(action == "hidden" ? "Open chat" : "Close X")

                $(".wrp-chat-widget-header").addClass(action)
                $(".wrp-chat-widget-header").removeClass(action == "hidden" ? "show" : "hidden")
            })

            $("button.send").click((e) => {
                $(this).attr('disabled', true)
                sendmessage($("input.chat").val())
            })

            $("form.wrp-cf").submit((e) => {
                e.preventDefault()
                sendmessage($("input.chat").val())
            })


            var data = {
                'action': 'run_completion', // your action name 
                'wprchat_session': random_room_number
            };


            function paint_messages(msg = null) {
                let chat_container_ = document.getElementsByClassName("wrp-container")[0]
                let chat_container = $(".wrp-container")

                if (msg && msg.content.length) {
                    messages.push(msg)
                    let lsc = "wrp-chat-bubble " + msg.owner;
                    chat_container.append(`<div class="${lsc}"><p>${msg.content}</p></div>`)
                    chat_container_.scrollTop = chat_container_.scrollHeight
                    return;
                }

                chat_container.html("")
                messages.forEach((i) => {
                    if (i.content.length) {
                        let lsc = "wrp-chat-bubble " + i.owner;
                        chat_container.append(`<div class="${lsc}"><p>${i.content}</p></div>`)
                    }
                })

                chat_container_.scrollTop = chat_container_.scrollHeight
            }


            function sendmessage(msg) {
                $("button.send").html("Sending...")
                if (msg.length < 3 || messages[messages.length - 1].owner == "me") return;

                $("input.chat").val("")

                paint_messages({
                    content: msg,
                    owner: "me"
                })

                jQuery.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        ...data,
                        msg
                    },
                    success: function(response) {
                        if (response.data && response.data.completion) {
                            let msg = {
                                content: response.data.completion,
                                owner: "other"
                            }
                            paint_messages(msg)
                        }

                        $("button.send").attr('disabled', false)
                        $("button.send").html("Send")
                    }
                });
            }
        });
    </script>
<?php
}
