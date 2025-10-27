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
        $this->ajax_priv( 'p2i_save_image_media', [ $this, 'save_image_media'] );
    }

    public function generate_image() {
        // Verify AJAX nonce
        check_ajax_referer( 'prompt2image_nonce', 'nonce' );

        $prompt = sanitize_text_field( $_POST['prompt'] ?? '' );

        // Check for uploaded file
        $uploaded_image = $_FILES['image'] ?? null;
        $image_data_url = null;

        if ( $uploaded_image && ! empty( $uploaded_image['tmp_name'] ) ) {
            $file_type = wp_check_filetype( $uploaded_image['name'] );
            if ( strpos( $file_type['type'], 'image' ) !== false ) {
                $image_content = file_get_contents( $uploaded_image['tmp_name'] );
                $base64_image = base64_encode( $image_content );

                // Prepare a data URL for browser display
                $image_data_url = 'data:' . $file_type['type'] . ';base64,' . $base64_image;
            } else {
                wp_send_json_error( esc_html__( 'Uploaded file is not a valid image.', 'prompt2image' ) );
            }
        }

        if ( empty( $prompt ) && empty( $image_data_url ) ) {
            wp_send_json_error( esc_html__( 'Prompt and image cannot both be empty.', 'prompt2image' ) );
        }

        // Return the image as base64 so it can be shown in browser
        wp_send_json_success( [
            'prompt' => $prompt,
            'image'  => $uploaded_image['tmp_name'],
        ] );
    }

    /**
     * Handle AJAX request to generate AI image using Google Gemini API.
     *
     * @return void
     */
    public function generate_ai_image() {
        // Verify AJAX nonce
        check_ajax_referer( 'prompt2image_nonce', 'nonce' );

        $prompt = sanitize_text_field( $_POST['prompt'] ?? '' );

        // Check for uploaded image
        $image_url = '';
        if ( ! empty( $_FILES['image']['tmp_name'] ) ) {
            $uploaded_file = $_FILES['image'];

            $file_type = wp_check_filetype( $uploaded_file['name'] );
            if ( strpos( $file_type['type'], 'image' ) === false ) {
                wp_send_json_error( esc_html__( 'Uploaded file is not a valid image.', 'prompt2image' ) );
            }

            // // Upload image to WordPress media
            // require_once ABSPATH . 'wp-admin/includes/file.php';
            // require_once ABSPATH . 'wp-admin/includes/media.php';
            // require_once ABSPATH . 'wp-admin/includes/image.php';

            // $attachment_id = media_handle_upload( 'image', 0 ); // 0 = no parent post
            // if ( is_wp_error( $attachment_id ) ) {
            //     wp_send_json_error( esc_html__( 'Failed to upload image.', 'prompt2image' ) );
            // }

            // $image_url = wp_get_attachment_url( $attachment_id );
        }

        if ( empty( $prompt ) && empty( $image_url ) ) {
            wp_send_json_error( esc_html__( 'Prompt and image cannot both be empty.', 'prompt2image' ) );
        }

        // Add image URL to prompt if exists
        if ( $image_url ) {
            $prompt .= " Refer to this image: $image_url";
        }

        // Get API key
        $settings = get_option( 'prompt2image-settings', [] );
        $api_key  = 'AIzaSyBNXcqRubHqWorc2fA2fJm9lw9Ex4SZJa8';
        if ( empty( $api_key ) ) {
            wp_send_json_error( esc_html__( 'API key is missing.', 'prompt2image' ) );
        }

        // Google Gemini API endpoint
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-preview-image-generation:generateContent';

        // Prepare request body (text only)
        $body = [
            'contents' => [
                [ 'parts' => [ [ 'text' => $prompt ] ] ]
            ],
            'generationConfig' => [
                'responseModalities' => [ 'IMAGE', 'TEXT' ]
            ]
        ];

        $response = wp_remote_post(
            $url,
            [
                'headers' => [
                    'Content-Type'   => 'application/json',
                    'X-goog-api-key' => $api_key,
                ],
                'body'    => wp_json_encode( $body ),
                'timeout' => 120,
            ]
        );

        wp_send_json_success( $response );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( esc_html( $response->get_error_message() ) );
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        wp_send_json_success( $data );
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
            'username'  => $current_user->user_login,
            'email'     => $current_user->user_email,
        ];
        // $user_data = [
        //     'username'  => 'Mahbub1',
        //     'email'     => 'mahbub1@gmail.com',
        // ];

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

            delete_option( 'prompt2image-settings' );
 
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
        if ( is_array( $data ) && isset( $data['status'] ) && $data['status'] === 'success' ) {
            // Remove API key from user meta
            delete_user_meta( $current_user->ID, '_prompt2image_api_key' );

            wp_send_json_success([
                'message' => 'Disconnected from the server successfully!',
                'api_response' => $data,
            ]);
        } else {
            $error_message = $data['message'] ?? 'Failed to disconnect from the server!';
            wp_send_json_error( ['message' => $error_message] );
        }
    }


    public function save_setting() {
        if ( ! isset($_POST['_wpnonce']) || ! wp_verify_nonce($_POST['_wpnonce'], 'prompt2image_nonce') ) {
            wp_send_json_error('Invalid nonce');
        }

        $input = $_POST['prompt2image'] ?? [];
        $saved = array_map('sanitize_text_field', $input);
        update_option('prompt2image-settings', $saved);

        wp_send_json_success('Settings saved successfully!');
    }

    public function save_image_media(){
        check_ajax_referer( 'prompt2image_nonce', '_wpnonce' );

        if ( empty( $_POST['image_data'] ) || empty( $_POST['filename'] ) ) {
            wp_send_json_error( esc_html__( 'No image data received.', 'text-domain' ) );
        }

        $image_data = wp_unslash( $_POST['image_data'] ); // Decode slashes if present
        $filename   = sanitize_file_name( wp_unslash( $_POST['filename'] ) );
        $mime_type  = sanitize_text_field( $_POST['mime_type'] ?? 'image/png' );

        // Decode base64
        $decoded = base64_decode( $image_data );
        if ( false === $decoded ) {
            wp_send_json_error( esc_html__( 'Invalid base64 data.', 'text-domain' ) );
        }

        // Save to WordPress uploads
        $upload = wp_upload_bits( $filename, null, $decoded );

        if ( ! empty( $upload['error'] ) ) {
            wp_send_json_error( esc_html( $upload['error'] ) );
        }

        // Prepare attachment data
        $attachment = array(
            'post_mime_type' => $mime_type,
            'post_title'     => pathinfo( $filename, PATHINFO_FILENAME ),
            'post_content'   => '',
            'post_status'    => 'inherit',
        );

        // Insert attachment
        $attach_id = wp_insert_attachment( $attachment, $upload['file'] );

        if ( is_wp_error( $attach_id ) ) {
            wp_send_json_error( esc_html( $attach_id->get_error_message() ) );
        }

        require_once ABSPATH . 'wp-admin/includes/image.php';

        $attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
        wp_update_attachment_metadata( $attach_id, $attach_data );

        wp_send_json_success(
            array(
                'url' => esc_url( wp_get_attachment_url( $attach_id ) ),
                'id'  => absint( $attach_id ),
            )
        );
    }
}
