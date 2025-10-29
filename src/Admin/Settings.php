<?php
namespace Prompt2Image\Admin;

use Prompt2Image\Class\FieldGenerator;
use Prompt2Image\Trait\Hook;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Settings {
    use Hook;

    const OPTION_GROUP = 'prompt2image_settings_group';
    const OPTION_NAME  = 'prompt2image_settings';
    const PAGE_SLUG    = 'prompt2image-settings';


    public function __construct() {
        $this->action( 'admin_menu', [ $this, 'add_settings_page' ] );
        $this->action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ] );
    }

    /**
     * Add Settings Page
     */
    public function add_settings_page() {
        add_submenu_page(
            'upload.php', 
            __( 'Prompt2Image Settings', 'prompt2image' ),
            __( 'Prompt2Image', 'prompt2image' ),          
            'manage_options',                              
            self::PAGE_SLUG,                               
            [ $this, 'render_settings_page' ]              
        );
    }


    /**
     * Sanitize input from settings page
     */
    public function sanitize( $input ) {
        // Get existing values
        $existing = get_option( self::OPTION_NAME, [] );

        foreach ( $this->fields as $tab => $fields ) {
            foreach ( $fields as $field ) {
                $key     = $field['key'];
                $type    = $field['type'];
                $default = $field['default'] ?? '';

                // Take submitted value (if any)
                $has_value = array_key_exists( $key, $input );
                $value     = $has_value ? $input[ $key ] : null;

                switch ( $type ) {
                    case 'url':
                        $existing[ $key ] = esc_url_raw( $value ?? $default );
                        break;
                    case 'number':
                        $existing[ $key ] = floatval( $value ?? $default );
                        break;
                    case 'checkbox':
                        $existing[ $key ] = $has_value ? 1 : 0;
                        break;
                    case 'textarea':
                        $existing[ $key ] = sanitize_textarea_field( $value ?? $default );
                        break;
                    default:
                        $existing[ $key ] = sanitize_text_field( $value ?? $default );
                        break;
                }
            }
        }

        return $existing;
    }


    /**
     * Render settings page with tabs
     */
    public function render_settings_page() {
    // Get stored Google Gemini API key from options
    $google_gemeni = get_option('prompt2image-settings', []);

    // Get current logged-in user
    $current_user   = wp_get_current_user();
    $server_api_key = get_user_meta($current_user->ID, '_prompt2image_api_key', true);
    ?>
    <div class="prompt2image-settings-wrap">
        <h1><?php esc_html_e('Prompt2Image Settings', 'prompt2image-api'); ?></h1>

        <form id="prompt2image-settings-form">

            <?php if (empty($server_api_key)) : ?>
                <!-- Connect via Our Server -->
                <div class="prompt2image-field-wrap">
                    <button type="button" id="connect-server" class="button button-connect">Connect with us</button>
                </div>

                <!-- Hidden API Key Section -->
                <div id="api-key-section" >
                    <!-- Toggle switch -->
                    <div class="switch-wrapper">
                        <label class="switch">
                            <input type="checkbox" id="show-api-key-input">
                            <span class="slider round"></span>
                        </label>
                        <span><?php esc_html_e('Use your own API key', 'prompt2image-api'); ?></span>
                    </div>

                    <div id="api-key-input-wrap" style="display:none; margin-top:15px;">
                        <label for="api_key"><?php esc_html_e('API Key', 'prompt2image-api'); ?></label>
                        <input 
                            type="password" 
                            id="api_key" 
                            name="prompt2image[api_key]" 
                            value="<?php echo esc_attr($google_gemeni['api_key'] ?? ''); ?>"
                        >
                        <span id="toggle-api-key" class="dashicons dashicons-visibility"></span>
                        <p class="description">
                            <?php esc_html_e('Enter your Google Gemini API key provided by the service.', 'prompt2image-api'); ?>
                        </p>
                        <button type="submit" class="button button-primary">Save Settings</button>
                    </div>
                </div>

            <?php else : ?>
                <!-- Show Connected / Disconnect buttons -->
                <div class="prompt2image-field-wrap prompt2image-buttons-wrapper">
                    <button type="button" class="button button-connected">Connected</button>
                    <button type="button" id="disconnect-server" class="button button-disconnect">Disconnect</button>
                </div>
            <?php endif; ?>

        </form>
    </div>

    <script>
        jQuery(document).ready(function($){
            // Click Connect with us button
            $('#connect-server').on('click', function(){
                $('#api-key-section').slideDown();
            });

            // Toggle API key input visibility via switch
            $('#show-api-key-input').on('change', function(){
                if($(this).is(':checked')){
                    $('#api-key-input-wrap').slideDown();
                } else {
                    $('#api-key-input-wrap').slideUp();
                }
            });

            // Toggle password visibility
            $('#toggle-api-key').on('click', function(){
                const input = $('#api_key');
                if(input.attr('type') === 'password'){
                    input.attr('type', 'text');
                } else {
                    input.attr('type', 'password');
                }
            });
        });
    </script>
    <?php
}






    /**
     * Enqueue CSS
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'prompt2image-settings-css',
            P2I_PLUGIN_URL . 'assets/css/settings.css',
            [],
            P2I_VERSION
        );
    }
}
