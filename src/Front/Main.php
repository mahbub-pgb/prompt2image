<?php
namespace Prompt2Image\Front;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Main {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'wp_head', [ $this, 'add_custom_head' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    /**
     * Add custom HTML or meta in <head>
     */
    public function add_custom_head() {
        var_dump( P2I_PLUGIN_DIR . 'src/Admin' );
    }

    /**
     * Enqueue front-end scripts and styles
     */
    public function enqueue_assets() {
        wp_enqueue_style(
            'prompt2image-frontend',
            P2I_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            P2I_VERSION
        );

        wp_enqueue_script(
            'prompt2image-frontend',
            P2I_PLUGIN_URL . 'assets/js/frontend.js',
            ['jquery'],
            P2I_VERSION,
            true
        );
    }
}
