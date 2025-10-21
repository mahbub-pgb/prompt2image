<?php
/**
 * Image Generator Class
 *
 * @package Prompt2Image
 */

namespace Prompt2Image\Admin;

use GuzzleHttp\Client;

/**
 * Handles image generation via Gemini API.
 */
class ImageGenerator {

    /**
     * API key for Gemini.
     *
     * @var string
     */
    private $api_key;

    /**
     * Model name used for image generation.
     *
     * @var string
     */
    private $model;

    /**
     * Default image size.
     *
     * @var string
     */
    private $size;

    /**
     * Constructor.
     * Loads settings from the options table.
     */
    public function __construct() {
        $settings       = get_option( Settings::OPTION_NAME, [] );
        $this->api_key  = isset( $settings['api_key'] ) ? sanitize_text_field( $settings['api_key'] ) : '';
        $this->model    = isset( $settings['model'] ) ? sanitize_text_field( $settings['model'] ) : 'gemini-pro-vision';
        $this->size     = isset( $settings['size'] ) ? sanitize_text_field( $settings['size'] ) : '1024x1024';
    }

    /**
     * Creates an image based on the given prompt.
     *
     * @param string $prompt The text prompt to generate the image from.
     * @return string|null The generated image URL, or null on failure.
     */
    public function create_image( $prompt ) {
        if ( empty( $this->api_key ) ) {
            error_log( 'Prompt2Image error: Missing API key.' );
            return null;
        }

        try {
            $client   = new Client();
            $response = $client->post(
                'https://api.gemini.google/v1/images:generate',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->api_key,
                        'Content-Type'  => 'application/json',
                    ],
                    'json'    => [
                        'model'  => $this->model,
                        'prompt' => sanitize_text_field( $prompt ),
                        'size'   => $this->size,
                    ],
                ]
            );

            $body = json_decode( wp_remote_retrieve_body( (object) [ 'body' => $response->getBody() ] ), true );

            if ( isset( $body['data']['image_url'] ) ) {
                return esc_url_raw( $body['data']['image_url'] );
            }

            error_log( 'Prompt2Image error: No image URL returned from API.' );
            return null;

        } catch ( \Exception $e ) {
            error_log( 'Prompt2Image API error: ' . esc_html( $e->getMessage() ) );
            return null;
        }
    }
}
