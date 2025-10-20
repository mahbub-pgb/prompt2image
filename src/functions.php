<?php
namespace Prompt2Image;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Example helper function
 */
function p2i_sanitize_text( $text ) {
    return sanitize_text_field( $text );
}

/**
 * Another helper function
 */
function p2i_get_plugin_version() {
    return defined('P2I_VERSION') ? P2I_VERSION : '1.0.0';
}
/**
 * Pretty var dump function
 *
 * @param mixed $var Variable to print
 * @param bool  $exit Exit after printing
 */
function pri( $var, $exit = false ) {
    echo '<div class="p2i-pri">';
    echo '<h4 style="margin:0 0 10px;font-family:sans-serif;color:#fff;background:#0073aa;padding:5px 10px;border-radius:4px;">PRI Debug Output</h4>';
    echo '<pre style="background:#f7f7f7;border:1px solid #ddd;color:#333;padding:15px;border-radius:4px;overflow:auto;">';
    var_dump( $var );
    echo '</pre>';
    echo '</div>';

    // Optional exit
    if ( $exit ) {
        exit;
    }
}
