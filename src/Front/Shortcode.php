<?php
namespace Prompt2Image\Front;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Shortcode {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'wp_head', [ $this, 'head' ] );
        add_shortcode( 'root', [ $this, 'root_shortcode_callback' ] );
    }

    /**
     * Add custom HTML or meta in <head>
     */
    public function head() {
        // pri( P2I_PLUGIN_URL . '/src/functions.php' );
    }

    function root_shortcode_callback($atts = [], $content = null) {


        return '<div id="root"></div>';
    }

    
}
