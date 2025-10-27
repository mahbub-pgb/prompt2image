<?php

abstract class Helper {
    /**
     * Debug and pretty print a variable
     *
     * @param mixed $var The variable to print
     * @param bool  $exit Whether to exit after printing
     */
    public static function pri($var, $exit = false) {
        echo '<pre style="background:#1e1e1e;color:#b5faff;padding:10px;border-radius:5px;">';
        var_dump($var);
        echo '</pre>';
        if ($exit) {
            exit;
        }
    }
}