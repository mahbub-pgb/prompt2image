<?php
namespace Prompt2Image\Bootstrap;

use Prompt2Image\Admin\Main as Admin;
use Prompt2Image\Admin\Settings;
use Prompt2Image\Admin\Ajax;
use Prompt2Image\Front\Main as Front;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Load {

    public function init() {
        
        // Optional: dynamically load all classes in Admin and Front folders
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

        foreach ( glob( trailingslashit( $folder_path ) . '*.php' ) as $file ) {
            require_once $file;

            // Get the class name from file
            $class_name = pathinfo( $file, PATHINFO_FILENAME );
            $full_class = $namespace . '\\' . $class_name;

            // Instantiate only if class exists
            if ( class_exists( $full_class ) ) {
                new $full_class();
            }
        }
    }
}
