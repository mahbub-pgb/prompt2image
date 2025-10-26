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
                <!-- AI Image Generator Modal -->
            <div id="prompt2image-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999;">
                <div class="prompt2image-modal-content" style="max-width:800px; margin:50px auto; background:#fff; border-radius:8px; padding:20px; position:relative;">
                    <span id="prompt2image-cancel" style="position:absolute; top:10px; right:15px; cursor:pointer; font-size:20px;">&times;</span>
                    <h2>Generate AI Image</h2>
                    <textarea id="prompt2image-text" style="width:100%; height:100px; padding:10px; margin-bottom:10px;" placeholder="Enter prompt here..."></textarea>
                    <div style="text-align:right;">
                        <span id="prompt2image-loader" style="display:none;">Generating...</span>
                        <button id="prompt2image-generate" class="button">Generate</button>
                        <button id="prompt2image-cancel" class="button">Cancel</button>
                    </div>
                </div>
            </div>

            <!-- Image Modal -->

            <div id="gemini-preview-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:9999; text-align:center;">
                <div class="prompt2image-overlay" style="position:absolute; width:100%; height:100%; top:0; left:0;"></div>
                <div style="position:relative; display:inline-block; max-width:90%; max-height:90%; margin-top:5%;">
                    <img src="" style="max-width:100%; max-height:100%; border-radius:8px; box-shadow:0 0 10px #000;">
                    <button class="close-preview" style="position:absolute; top:-10px; right:-10px; background:red; color:white; border:none; border-radius:50%; width:30px; height:30px; cursor:pointer;">×</button>
                </div>
            </div>



            <!-- History container -->
            <div id="gemini-output-single" style="margin-top:20px; width:80%; margin-left:auto; margin-right:auto;"></div>





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
