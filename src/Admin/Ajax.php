<?php
namespace Prompt2Image\Admin;

use Prompt2Image\Trait\Hook;

class Ajax {
    use Hook;

    public function __construct() {
        $this->ajax_priv( 'generate_ai_image', [ $this, 'generate_ai_image'] );
        $this->ajax_priv( 'p2i_connect_server', [ $this, 'connect_server'] );
        $this->ajax_priv( 'p2i_save_setting', [ $this, 'save_setting'] );
        $this->ajax_priv( 'disconnect_server', [ $this, 'disconnect_server'] );
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

    public function connect_server() {
        // Check nonce
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'prompt2image_nonce' ) ) {
            wp_send_json_error( ['message' => 'Invalid nonce'] );
        }

        // Get current user info
        $current_user = wp_get_current_user();
        if ( ! $current_user || 0 === $current_user->ID ) {
            wp_send_json_error( ['message' => 'User not logged in'] );
        }

        $user_data = [
            'username' => 'test',
            'email'    => 'test@gmail.com',
        ];

        // Prepare API request
        $api_url = P2I_API_BASE_URL . 'register';
        $args = [
            'body'        => wp_json_encode( $user_data ),
            'headers'     => [
                'Content-Type' => 'application/json',
            ],
            'timeout'     => 20,
        ];

        // Make API request
        $response = wp_remote_post( $api_url, $args );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( ['message' => $response->get_error_message()] );
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( isset( $data['api_key'] ) ) {
            // Optionally, save API key in user meta
            update_user_meta( $current_user->ID, '_prompt2image_api_key', sanitize_text_field( $data['api_key'] ) );

            wp_send_json_success([
                'message' => 'Connected to the server successfully!',
                'user'    => $user_data,
                'api_key' => $data['api_key'],
            ]);
        } else {
            $error_message = $data['message'] ?? 'Failed to connect to the server!';
            wp_send_json_error( ['message' => $error_message] );
        }
    }

    public function disconnect_server() {
        // Check nonce
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'prompt2image_nonce' ) ) {
            wp_send_json_error( ['message' => 'Invalid nonce'] );
        }

        // Get current logged-in user
        $current_user = wp_get_current_user();
        if ( ! $current_user || 0 === $current_user->ID ) {
            wp_send_json_error( ['message' => 'User not logged in'] );
        }

        // Prepare user data for API request
        $user_data = [
            'email' => $current_user->user_email,
        ];

        // Prepare API request
        $api_url = P2I_API_BASE_URL . 'disconnect';
        $args = [
            'body'    => wp_json_encode( $user_data ),
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'timeout' => 20,
        ];

        // Make API request
        $response = wp_remote_post( $api_url, $args );

        // Check for request error
        if ( is_wp_error( $response ) ) {
            wp_send_json_error( ['message' => $response->get_error_message()] );
        }

        // Parse response
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        // Validate response
        if ( is_array( $data ) && ! empty( $data['status'] ) ) {
            // Remove API key from user meta
            delete_user_meta( $current_user->ID, '_prompt2image_api_key' );

            wp_send_json_success([
                'message' => 'Disconnected from the server successfully!',
            ]);
        } else {
            $error_message = $data['message'] ?? 'Failed to disconnect from the server!';
            wp_send_json_error( ['message' => $error_message] );
        }
    }

    function save_setting() {
        if ( ! isset($_POST['_wpnonce']) || ! wp_verify_nonce($_POST['_wpnonce'], 'prompt2image_nonce') ) {
            wp_send_json_error('Invalid nonce');
        }

        $input = $_POST['prompt2image'] ?? [];
        $saved = array_map('sanitize_text_field', $input);
        update_option('prompt2image-settings', $saved);

        wp_send_json_success('Settings saved successfully!');
    }
}
