<?php
namespace Prompt2Image\Core;

use GuzzleHttp\Client;

class ImageGenerator {
    private $api_key;
    private $model;
    private $size;

    public function __construct() {
        $settings = get_option(Settings::OPTION_NAME, []);
        $this->api_key = $settings['api_key'] ?? '';
        $this->model   = $settings['model'] ?? 'gemini-pro-vision';
        $this->size    = $settings['size'] ?? '1024x1024';
    }

    public function create_image($prompt) {
        if (empty($this->api_key)) {
            return null;
        }

        try {
            $client = new Client();
            $response = $client->post('https://api.gemini.google/v1/images:generate', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Content-Type'  => 'application/json'
                ],
                'json' => [
                    'model'  => $this->model,
                    'prompt' => $prompt,
                    'size'   => $this->size
                ]
            ]);

            $body = json_decode($response->getBody(), true);
            return $body['data']['image_url'] ?? null;

        } catch (\Exception $e) {
            error_log('Prompt2Image error: ' . $e->getMessage());
            return null;
        }
    }
}
