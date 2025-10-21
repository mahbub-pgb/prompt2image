<?php

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
if ( ! function_exists( 'pri' ) ) {
    function pri( $var, $exit = false ) {
        echo '<pre style="background:#1e1e1e;color:#b5faff;padding:10px;border-radius:5px;">';
        var_dump( $var );
        echo '</pre>';
        if ( $exit ) exit;
    }
}


