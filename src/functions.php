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


