<?php
namespace Prompt2Image\Admin;

use Prompt2Image\Trait\Hook;

class Main {

    use Hook;
    public function __construct() {
        $this->action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets'] );
        $this->action( 'admin_footer', [$this, 'render_modal'] );
    }

    public function enqueue_assets() {
        wp_enqueue_style( 'prompt2image', P2I_PLUGIN_URL . 'assets/css/admin.css', [], P2I_VERSION );
        wp_enqueue_script( 'prompt2image', P2I_PLUGIN_URL . 'assets/js/admin.js', ['jquery'], P2I_VERSION, true );


        wp_localize_script( 'prompt2image', 'PROMPT2IMAGE', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('prompt2image_nonce'),
            'buttonTitle' => __( 'Generate with AI', 'prompt2image' ),
        ] );
    }
    public function render_modal() {
            // Only show modal in Media Library
            $screen = get_current_screen();
            if ($screen && $screen->post_type === 'attachment') : ?>
                <div id="prompt2image-modal" style="display:none;">
                    <div class="prompt2image-overlay"></div>
                    <div class="prompt2image-content">
                        <div class="prompt2image-header">
                            <span class="dashicons dashicons-art"></span>
                            <h2>Generate AI Image</h2>
                        </div>
                        <textarea id="prompt2image-text" rows="4" placeholder="Write your prompt here..." style="width:100%;"></textarea>
                        <div class="prompt2image-footer">
                            <button class="button button-secondary" id="prompt2image-cancel">Cancel</button>
                            <button class="button button-primary" id="prompt2image-generate">Generate</button>
                            <span id="prompt2image-loader" style="display:none;"><span class="spinner is-active"></span> </span>
                        </div>
                    </div>
                </div>

            <?php endif;

            // pri( $screen->id );

            if( $screen && $screen->id === 'media_page_prompt2image-settings' ){ 
                ?>
                <!-- Server Connect Modal -->
                <div id="server-connect-modal" style="display:none;">
                    <div class="server-modal-overlay"></div>

                    <div class="server-modal-content">
                        <h2>Connect to Prompt2Image Server</h2>
                        <p>Do you want to connect via our server? We collect your email and username for authentication.</p>

                        <div class="server-modal-actions">
                            <button id="confirm-connect" class="button button-primary">
                                <span class="btn-text">Connect</span>
                                <span id="server-connect-loader" class="spinner" style="display:none; margin-left:6px;"></span>
                            </button>

                            <button id="cancel-connect" class="button">Cancel</button>
                        </div>
                    </div>
                </div>


                <?php  
            }


        }
}
