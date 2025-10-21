<?php
/**
 * Plugin Name: Prompt2Image - AI Image Generator
 * Description: Generate AI images directly from the WordPress media library using an AI API.
 * Version: 1.0.0
 * Author: Mahbub
 * Author URI: https://techwithmahbub.com/
 * Plugin URI: https://techwithmahbub.com/
 */


if (!defined('ABSPATH')) exit;

// === Define Constants === //
define( 'P2I_VERSION', time() );
define( 'P2I_PLUGIN_FILE', __FILE__ );
define( 'P2I_PLUGIN_DIR', plugin_dir_path(__FILE__) );
define( 'P2I_PLUGIN_URL', plugin_dir_url(__FILE__) );
define( 'P2I_PLUGIN_BASENAME', plugin_basename(__FILE__) );

// === Autoload Classes === //
require_once __DIR__ . '/vendor/autoload.php';

use Prompt2Image\Bootstrap\Load;

// === Boot Load === //
function prompt2image_init() {
    $plugin = new Load();
    $plugin->init();
}
add_action( 'plugins_loaded', 'prompt2image_init' );
