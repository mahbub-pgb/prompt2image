<?php
namespace Prompt2Image\Core;

class Settings {
    const OPTION_GROUP = 'prompt2image_settings_group';
    const OPTION_NAME  = 'prompt2image_settings';
    const PAGE_SLUG    = 'prompt2image-settings';

    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function add_settings_page() {
        add_options_page(
            __('Prompt2Image Settings', 'prompt2image'),
            __('Prompt2Image', 'prompt2image'),
            'manage_options',
            self::PAGE_SLUG,
            [$this, 'render_settings_page']
        );
    }

    public function register_settings() {
        register_setting(self::OPTION_GROUP, self::OPTION_NAME, [
            'sanitize_callback' => [$this, 'sanitize'],
        ]);

        add_settings_section(
            'prompt2image_main_section',
            __('API Configuration', 'prompt2image'),
            '__return_false',
            self::PAGE_SLUG
        );

        add_settings_field(
            'api_key',
            __('API Key', 'prompt2image'),
            [$this, 'field_api_key'],
            self::PAGE_SLUG,
            'prompt2image_main_section'
        );

        add_settings_field(
            'model',
            __('Model', 'prompt2image'),
            [$this, 'field_model'],
            self::PAGE_SLUG,
            'prompt2image_main_section'
        );

        add_settings_field(
            'size',
            __('Default Image Size', 'prompt2image'),
            [$this, 'field_size'],
            self::PAGE_SLUG,
            'prompt2image_main_section'
        );
    }

    public function sanitize($input) {
        return [
            'api_key' => sanitize_text_field($input['api_key'] ?? ''),
            'model'   => sanitize_text_field($input['model'] ?? 'gemini-pro-vision'),
            'size'    => sanitize_text_field($input['size'] ?? '1024x1024'),
        ];
    }

    public function get_option($key, $default = '') {
        $options = get_option(self::OPTION_NAME, []);
        return $options[$key] ?? $default;
    }

    // === Fields === //
    public function field_api_key() {
        $options = get_option(self::OPTION_NAME);
        $value = esc_attr($options['api_key'] ?? '');
        echo "<input type='password' name='" . self::OPTION_NAME . "[api_key]' value='$value' class='regular-text' />";
        echo "<p class='description'>Enter your Google Gemini or OpenAI API key.</p>";
    }

    public function field_model() {
        $options = get_option(self::OPTION_NAME);
        $value = esc_attr($options['model'] ?? 'gemini-pro-vision');
        echo "<input type='text' name='" . self::OPTION_NAME . "[model]' value='$value' class='regular-text' />";
        echo "<p class='description'>Default model to use for image generation.</p>";
    }

    public function field_size() {
        $options = get_option(self::OPTION_NAME);
        $value = esc_attr($options['size'] ?? '1024x1024');
        echo "<input type='text' name='" . self::OPTION_NAME . "[size]' value='$value' class='regular-text' />";
        echo "<p class='description'>Image size (e.g., 512x512, 1024x1024).</p>";
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Prompt2Image Settings', 'prompt2image'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields(self::OPTION_GROUP);
                do_settings_sections(self::PAGE_SLUG);
                submit_button(__('Save Settings', 'prompt2image'));
                ?>
            </form>
        </div>
        <?php
    }
}
