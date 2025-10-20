<?php
namespace Prompt2Image\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FieldGenerator {

    /**
     * Render any field dynamically
     *
     * @param string $option_name Option name in DB
     * @param array  $field Field config:
     *   [
     *       'type' => 'text|url|number|password|textarea|checkbox|select',
     *       'key' => 'field_key',
     *       'label' => 'Field Label',
     *       'description' => 'Field description',
     *       'default' => 'default value',
     *       'options' => ['key' => 'label'], // for select or checkbox group
     *   ]
     */
    public static function render_field( $option_name, array $field ) {
        $type        = $field['type'] ?? 'text';
        $key         = $field['key'] ?? '';
        $label       = $field['label'] ?? '';
        $description = $field['description'] ?? '';
        $default     = $field['default'] ?? '';
        $options     = $field['options'] ?? [];

        $value = get_option( $option_name, [] );
        $value = $value[ $key ] ?? $default;

        // Print label
        if ( $label ) {
            echo '<label for="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label><br>';
        }

        // Render field based on type
        switch ( $type ) {
            case 'text':
            case 'password':
            case 'url':
            case 'number':
                $input_type = $type === 'url' ? 'url' : ( $type === 'password' ? 'password' : ( $type === 'number' ? 'number' : 'text' ) );
                printf(
                    '<input type="%1$s" id="%2$s" name="%3$s[%2$s]" value="%4$s" class="regular-text" />',
                    esc_attr( $input_type ),
                    esc_attr( $key ),
                    esc_attr( $option_name ),
                    esc_attr( $value )
                );
                break;

            case 'textarea':
                printf(
                    '<textarea id="%1$s" name="%2$s[%1$s]" rows="5" class="large-text">%3$s</textarea>',
                    esc_attr( $key ),
                    esc_attr( $option_name ),
                    esc_textarea( $value )
                );
                break;

            case 'checkbox':
                $checked = checked( 1, $value, false );
                printf(
                    '<input type="checkbox" id="%1$s" name="%2$s[%1$s]" value="1" %3$s />',
                    esc_attr( $key ),
                    esc_attr( $option_name ),
                    $checked
                );
                break;

            case 'select':
                if ( ! empty( $options ) && is_array( $options ) ) {
                    echo sprintf('<select id="%1$s" name="%2$s[%1$s]">', esc_attr( $key ), esc_attr( $option_name ));
                    foreach ( $options as $opt_value => $opt_label ) {
                        $selected = selected( $value, $opt_value, false );
                        echo sprintf('<option value="%1$s" %2$s>%3$s</option>', esc_attr( $opt_value ), $selected, esc_html( $opt_label ));
                    }
                    echo '</select>';
                }
                break;

            default:
                echo '<input type="text" id="' . esc_attr( $key ) . '" name="' . esc_attr( $option_name ) . '[' . esc_attr( $key ) . ']" value="' . esc_attr( $value ) . '" class="regular-text" />';
                break;
        }

        // Print description
        if ( $description ) {
            echo '<p class="description">' . esc_html( $description ) . '</p>';
        }
    }

    /**
     * Get sanitized field value
     *
     * @param string $option_name
     * @param string $key
     * @param string $type
     * @param mixed  $default
     * @return mixed
     */
    public static function get_value( $option_name, $key, $type = 'text', $default = '' ) {
        $options = get_option( $option_name, [] );
        $value   = $options[ $key ] ?? $default;

        switch ( $type ) {
            case 'url':
                return esc_url_raw( $value );
            case 'number':
                return floatval( $value );
            case 'checkbox':
                return $value ? 1 : 0;
            case 'textarea':
                return sanitize_textarea_field( $value );
            default:
                return sanitize_text_field( $value );
        }
    }
}
