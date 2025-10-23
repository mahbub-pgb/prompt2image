<?php
namespace Prompt2Image\Front;

use Prompt2Image\Trait\Hook;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Main {

    use Hook;

    /**
     * Constructor
     */
    public function __construct() {
        $this->action( 'wp_head', [ $this, 'head' ] );
        $this->action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    /**
     * Add custom HTML or meta in <head>
     */
    public function head() {
        // pri( P2I_PLUGIN_URL . '/src/functions.php' );
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
