<?php
namespace Prompt2Image\Class;
class Helper {
    /**
     * Debug and pretty print a variable
     *
     * @param mixed $var The variable to print
     * @param bool  $exit Whether to exit after printing
     */
    public static function pri( $data, $admin_only = true, $hide_adminbar = true ) {

        if ( $admin_only && ! current_user_can( 'manage_options' ) ) {
            return;
        }

        echo wp_kses_post( '<pre>' );
        if ( is_object( $data ) || is_array( $data ) ) {
            print_r( $data );
        } else {
            var_dump( $data );
        }
        echo wp_kses_post( '</pre>' );

        if ( is_admin() && $hide_adminbar ) {
            ?>
                <style>#adminmenumain{display:none;}</style>
            <?php
        }
    }
}