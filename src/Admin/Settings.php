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

    /**
     * Fields configuration with tabs
     */
    private $fields = [
        'tab1' => [
            [
                'type'        => 'password',
                'key'         => 'api_key',
                'label'       => 'API Key',
                'description' => 'Enter API key',
                'default'     => '',
            ],
            [
                'type'        => 'text',
                'key'         => 'model',
                'label'       => 'Model',
                'description' => 'Default model',
                'default'     => 'gemini-pro-vision',
            ],
            [
                'type'        => 'checkbox',
                'key'         => 'enable_feature',
                'label'       => 'Enable Feature',
                'description' => 'Check this box to enable the feature.',
                'default'     => 0, // unchecked by default
            ],
        ],
        'tab2' => [
            [
                'type'        => 'text',
                'key'         => 'size',
                'label'       => 'Default Image Size',
                'description' => 'Image size (e.g., 512x512, 1024x1024)',
                'default'     => '1024x1024',
            ],
            [
                'type'        => 'url',
                'key'         => 'url',
                'label'       => 'Website',
                'description' => 'Enter your website',
                'default'     => '',
            ],
        ],

    ];

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
        $saved_data = get_option( self::OPTION_NAME, [] );
        ?>
        <div class="prompt2image-settings-wrap">
            <h1>Prompt2Image Settings</h1>

            <!-- Tabs -->
            <div class="nav-tab-wrapper">
                <a href="#" class="nav-tab nav-tab-active" data-tab="1">General</a>
                <a href="#" class="nav-tab" data-tab="2">Advanced</a>
            </div>

            <form id="prompt2image-settings-form">
                <!-- Tab 1 -->
                <div class="prompt2image-field-wrap active" data-tab="1">
                    <label for="api_key">API Key</label>
                    <input type="password" id="api_key" name="prompt2image[api_key]" value="<?php echo esc_attr($saved_data['api_key'] ?? ''); ?>">
                    <p class="description">Enter your API key provided by the service.</p>
                </div>

                <div class="prompt2image-field-wrap" data-tab="1">
                    <label for="model">Model</label>
                    <input type="text" id="model" name="prompt2image[model]" value="<?php echo esc_attr($saved_data['model'] ?? 'gemini-pro-vision'); ?>">
                    <p class="description">Select the model to generate images.</p>
                </div>

                <div class="prompt2image-field-wrap" data-tab="1">
                    <label>
                        <input type="checkbox" id="enable_feature" name="prompt2image[enable_feature]" value="1" <?php checked( $saved_data['enable_feature'] ?? 0, 1 ); ?>>
                        Enable Feature
                    </label>
                </div>

                <!-- Tab 2 -->
                <div class="prompt2image-field-wrap" data-tab="2">
                    <label for="size">Default Image Size</label>
                    <input type="text" id="size" name="prompt2image[size]" value="<?php echo esc_attr($saved_data['size'] ?? '1024x1024'); ?>">
                    <p class="description">Set the default size for generated images.</p>
                </div>

                <div class="prompt2image-field-wrap" data-tab="2">
                    <label for="url">Website URL</label>
                    <input type="url" id="url" name="prompt2image[url]" value="<?php echo esc_attr($saved_data['url'] ?? ''); ?>">
                    <p class="description">Enter your website URL.</p>
                </div>

                <input type="hidden" name="action" value="prompt2image_save_settings">
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('prompt2image_nonce'); ?>">

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
