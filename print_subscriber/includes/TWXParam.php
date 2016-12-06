<?php

final class TWXParam {

    public static function get($name, $default=null) {
        return self::param($_GET, $name, $default);
    }

    public static function post($name, $default=null) {
        return self::param($_POST, $name, $default);
    }

    public static function any($name, $default=null) {
        return self::param($_REQUEST, $name, $default);
    }

    private static function param($collection, $name, $default) {
        if (isset($collection[$name])) {
            return $collection[$name];
        } else {
            return $default;
        }
    }

}
