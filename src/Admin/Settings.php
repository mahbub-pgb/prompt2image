<?php
namespace Prompt2Image\Admin;

use Prompt2Image\Class\FieldGenerator;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Settings {

    const OPTION_GROUP = 'prompt2image_settings_group';
    const OPTION_NAME  = 'prompt2image_settings';
    const PAGE_SLUG    = 'prompt2image-settings';


    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ] );
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
        ?>
        <div class="prompt2image-settings-wrap">
        <h1>Prompt2Image Settings</h1>

        <form id="prompt2image-settings-form">
            <!-- Tab 1: API Key -->
            <div class="prompt2image-field-wrap active">
                <label for="api_key">API Key</label>
                <input type="password" id="api_key" name="prompt2image[api_key]" value="<?php echo esc_attr($saved_data['api_key'] ?? ''); ?>">
                <p class="description">Enter your Google Gemini API key provided by the service.</p>
            </div>               

            <!-- Enable Feature -->
            <div class="prompt2image-field-wrap">
                <label>
                    <input type="checkbox" id="enable_feature" name="prompt2image[enable_feature]" value="1" <?php checked( $saved_data['enable_feature'] ?? 0, 1 ); ?>>
                    Enable Feature
                </label>
            </div>  

            <!-- Connect via Our Server -->
            <div class="prompt2image-field-wrap">
                <button type="button" id="connect-server" class="button">Connect via Our Server</button>
            </div>

            <button type="submit" class="button button-primary">Save Settings</button>
        </form>
    </div>

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
