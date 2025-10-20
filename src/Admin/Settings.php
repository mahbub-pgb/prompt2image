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
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ] );
    }

    /**
     * Add Settings Page
     */
    public function add_settings_page() {
        add_options_page(
            __( 'Prompt2Image Settings', 'prompt2image' ),
            __( 'Prompt2Image', 'prompt2image' ),
            'manage_options',
            self::PAGE_SLUG,
            [ $this, 'render_settings_page' ]
        );
    }

    /**
     * Register Settings
     */
    public function register_settings() {
        register_setting(
            self::OPTION_GROUP,
            self::OPTION_NAME,
            [ 'sanitize_callback' => [ $this, 'sanitize' ] ]
        );

        add_settings_section(
            'prompt2image_main_section',
            '__return_false',
            '__return_false',
            self::PAGE_SLUG
        );

        // Render all fields dynamically, no need to specify tab here
        foreach ( $this->fields as $tab => $fields ) {
            foreach ( $fields as $field ) {
                add_settings_field(
                    $field['key'],
                    $field['label'],
                    function() use ( $field ) {
                        FieldGenerator::render_field( self::OPTION_NAME, $field );
                    },
                    self::PAGE_SLUG,
                    'prompt2image_main_section'
                );
            }
        }
    }

    /**
     * Sanitize input from settings page
     */
    public function sanitize( $input ) {
    // Get existing values
    $existing = get_option( self::OPTION_NAME, [] );

    foreach ( $this->fields as $tab => $fields ) {
        foreach ( $fields as $field ) {
            $key = $field['key'];
            $type = $field['type'];
            $default = $field['default'] ?? '';

            // Take submitted value or default
            $value = $input[ $key ] ?? $existing[ $key ] ?? $default;

            switch ( $type ) {
                case 'url':
                    $existing[ $key ] = esc_url_raw( $value );
                    break;
                case 'number':
                    $existing[ $key ] = floatval( $value );
                    break;
                case 'checkbox':
                    $existing[ $key ] = $value ? 1 : 0;
                    break;
                case 'textarea':
                    $existing[ $key ] = sanitize_textarea_field( $value );
                    break;
                default:
                    $existing[ $key ] = sanitize_text_field( $value );
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
        $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'tab1';
        ?>
        <div class="wrap prompt2image-settings-wrap">
            <h1><?php esc_html_e( 'Prompt2Image Settings', 'prompt2image' ); ?></h1>

            <h2 class="nav-tab-wrapper">
                <?php foreach ( $this->fields as $tab_key => $fields ) : ?>
                    <a href="?page=<?php echo self::PAGE_SLUG; ?>&tab=<?php echo esc_attr( $tab_key ); ?>"
                       class="nav-tab <?php echo $active_tab === $tab_key ? 'nav-tab-active' : ''; ?>">
                        <?php echo esc_html( ucfirst($tab_key) ); ?>
                    </a>
                <?php endforeach; ?>
            </h2>

            <form method="post" action="options.php">
                <?php
                settings_fields( self::OPTION_GROUP );

                // Show only fields for active tab
                foreach ( $this->fields[ $active_tab ] as $field ) {
                    FieldGenerator::render_field( self::OPTION_NAME, $field );
                }

                submit_button( __( 'Save Settings', 'prompt2image' ) );
                ?>
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
