<?php
namespace Prompt2Image\Core;

class Admin {
    public function __construct() {
        add_action('admin_enqueue_scripts', [ $this, 'enqueue_assets'] );
        add_action('media_buttons', [ $this, 'add_ai_button'] );
    }

    public function enqueue_assets() {
        wp_enqueue_style( 'prompt2image', P2I_PLUGIN_URL . 'assets/css/admin.css', [], P2I_VERSION );
        wp_enqueue_script( 'prompt2image', P2I_PLUGIN_URL . 'assets/js/admin.js', ['jquery'], P2I_VERSION, true );


        wp_localize_script( 'prompt2image', 'PROMPT2IMAGE', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('prompt2image_nonce')
        ] );
    }

    public function add_ai_button() {
        echo '<button type="button" class="button prompt2image-btn">🪄 Generate AI Image</button>';
    }
}
