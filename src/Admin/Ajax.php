<?php
namespace Prompt2Image\Admin;

class Ajax {
    public function __construct() {
        add_action('wp_ajax_generate_ai_image', [ $this, 'generate_ai_image'] );
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
}
