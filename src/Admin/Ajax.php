<?php
namespace Prompt2Image\Admin;

class Ajax {
    public function __construct() {
        add_action('wp_ajax_generate_ai_image', [ $this, 'generate_ai_image'] );
        add_action('wp_ajax_p2i_connect_server', [ $this, 'connect_server'] );
    }

    public function generate_ai_image() {
        check_ajax_referer('prompt2image_nonce', 'nonce');

        $prompt = sanitize_text_field($_POST['prompt'] ?? '');

        if (empty($prompt)) {
            wp_send_json_error(['message' => 'Prompt cannot be empty']);
        }

        wp_send_json_success(['message' => 'Working']);

        // $generator = new ImageGenerator();
        // $image_url = $generator->create_image($prompt);

        // if ($image_url) {
        //     wp_send_json_success(['image_url' => $image_url]);
        // } else {
        //     wp_send_json_error(['message' => 'Failed to generate image']);
        // }
    }

    function connect_server() {
        // Check nonce
        if ( ! isset($_POST['_wpnonce']) || ! wp_verify_nonce($_POST['_wpnonce'], 'prompt2image_nonce') ) {
            wp_send_json_error(['message' => 'Invalid nonce']);
        }

        // Simulate server connection (replace with real API call)
        $connected = true; // change to actual connection logic

        if($connected){
            wp_send_json_success(['message' => 'Connected to the server successfully!']);
        } else {
            wp_send_json_error(['message' => 'Failed to connect to the server!']);
        }
    }
}
