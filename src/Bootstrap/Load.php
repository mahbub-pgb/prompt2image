<?php
namespace Prompt2Image\Bootstrap;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Load {

    public function init() {
        // === Dynamically load Admin and Front classes === //
        self::load_and_instantiate( P2I_PLUGIN_DIR . 'src/Admin', 'Prompt2Image\\Admin' );
        self::load_and_instantiate( P2I_PLUGIN_DIR . 'src/Front', 'Prompt2Image\\Front' );
    }

    /**
     * Dynamically load all PHP files and instantiate classes
     *
     * @param string $folder_path Absolute path to folder
     * @param string $namespace Namespace of classes
     */
    public static function load_and_instantiate( $folder_path, $namespace ) {
        if ( ! is_dir( $folder_path ) ) {
            return;
        }

        $files = glob( trailingslashit( $folder_path ) . '*.php' );
        if ( empty( $files ) ) return;

        foreach ( $files as $file ) {
            require_once $file;

            $class_name = pathinfo( $file, PATHINFO_FILENAME );
            $full_class = $namespace . '\\' . $class_name;

            if ( class_exists( $full_class ) ) {
                new $full_class();
            }
        }
    }
}
