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
            <!-- 🪄 AI Prompt Modal -->
            <div id="prompt2image-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999;">
                <div class="prompt2image-modal-content" style="max-width:700px; margin:50px auto; background:#fff; border-radius:8px; padding:20px; position:relative;">
                    <span id="prompt2image-cancel" style="position:absolute; top:10px; right:15px; cursor:pointer; font-size:20px;">&times;</span>
                    <h2>Generate AI Image</h2>
                    <textarea id="prompt2image-text" style="width:100%; height:100px; padding:10px; margin-bottom:10px;" placeholder="Enter prompt here..."></textarea>
                    <!-- <div style="text-align:right; position: relative;">
                        <span id="prompt2image-loader" style="display:none; align-items:center; gap:6px;">
                            <span class="p2i-spinner"></span>
                            Generating...
                        </span>
                        <button id="prompt2image-generate" class="button">Generate</button>
                        <button id="prompt2image-cancel" class="button">Cancel</button>
                    </div> -->

                    <!-- Loader -->
                    <div id="prompt2image-loader" style="display:none; text-align:center; margin-bottom:10px;">
                        <div class="p2i-spinner"></div>
                        <p>Generating AI image, please wait...</p>
                    </div>

                    <div style="text-align:right;">
                        <button id="prompt2image-generate" class="button">Generate</button>
                        <button id="prompt2image-cancel" class="button">Cancel</button>
                    </div>

                </div>
            </div>

            <!-- 🖼️ AI Result Modal -->
            <div id="prompt2image-result-modal" 
                 style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
                        background:rgba(0,0,0,0.65); z-index:9999; backdrop-filter: blur(3px);">
                <div class="prompt2image-result-content" 
                     style="max-width:750px; margin:60px auto; background:#fff; border-radius:10px; 
                            padding:20px 25px; position:relative; box-shadow:0 0 30px rgba(0,0,0,0.2);">
                    <span class="prompt2image-result-close close-preview" 
                          style="position:absolute; top:10px; right:15px; cursor:pointer; font-size:24px;">&times;</span>
                    <div id="prompt2image-result-body" style="text-align:center;"></div>
                </div>
            </div>

            <!-- 🔍 Image Full Preview Modal -->
            <div id="gemini-preview-modal" 
                 style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
                        background:rgba(0,0,0,0.85); z-index:10000;">
                <span class="close-preview" 
                      style="position:absolute; top:20px; right:30px; font-size:32px; color:#fff; cursor:pointer;">&times;</span>
                <img src="" alt="AI Preview" 
                     style="max-width:90%; max-height:90%; margin:auto; display:block; position:relative; top:50%; transform:translateY(-50%); border-radius:10px; box-shadow:0 0 30px rgba(255,255,255,0.3);">
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
