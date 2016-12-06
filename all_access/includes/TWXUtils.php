<?php

final class TWXHttpUtils {

    public static function redirect($url) {
        header('Location: ' . $url);
        die();
    }

    public static function scheme() {
        if (
            isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&  $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'
        ) {
            return 'https';
        } else {
            return 'http';
        }
    }

    public static function host() {
        $host = $_SERVER['HTTP_HOST'];
        $port = self::port();
        if (self::scheme() == 'http' && $port != 80) {
            $host .= ':' . $port;
        }
        if (self::scheme() == 'https' && $port != 443) {
            $host .= ':' . $port;
        }
        return $host;
    }

    public static function port() {
        $port = $_SERVER['SERVER_PORT'];
        return intval($port);
    }

    public static function requestUri() {
        $requestUri = $_SERVER['REQUEST_URI'];
        return $requestUri;
    }

    public static function currentUrl() {
        $scheme     = self::scheme();
        $host       = self::host();
        $requestUri = self::requestUri();
        return $scheme . '://' . $host . $requestUri;
    }

}

final class TWXStringUtils {

    public static function camelize($input, $separator = '_') {
        $input = strtolower(trim($input));
        $input = str_replace($separator, ' ', $input);
        $input = str_replace(' ', '', ucwords($input));
        $input = lcfirst($input);
        return $input;
    }

}

final class TWXHtmlUtils {

    public static function css($path) {
        return '<link rel="stylesheet" type="text/css" href="' . $path . '">';
    }

    public static function charset($charset='utf-8') {
        return '<meta charset="' . $charset . '">';
    }

    public static function viewport($viewport='user-scalable=no, initial-scale=1.0, maximum-scale=1.0, width=device-width') {
        return '<meta name="viewport" content="' . $viewport . '">';
    }

    public static function link($url, $text='', $attributes=[]) {
        $attributes['href'] = $url;
        $attributes = self::attributes($attributes);
        return "<a {$attributes}>{$text}</a>";
    }


    private static function inputElement($name, $attributes=[]) {
        if (!isset($attributes['value'])) {
            $attributes['id'] = $name;
        }
        $attributes['name'] = $name;
        if (!isset($attributes['value'])) {
            $attributes['value'] = TWXParam::any($name, '');
        }
        if (!isset($attributes['class'])) {
            $attributes['class'] = '';
        }
        $attributes['class'] .= 'form-input-' . $attributes['type'];
        $attributes = self::attributes($attributes);
        return "<input ${attributes} />";
    }

    private static function attributes($attributes) {
        $out = [];
        if (is_array($attributes)) {
            foreach ($attributes as $key => $value) {
                if (!empty($value)) {
                    $value = htmlspecialchars($value);
                    $out[] = "{$key}=\"{$value}\"";
                }
            }
        }
        return implode(' ', $out);
    }

    public static function startForm($name='', $url='', $method='POST', $attributes=[]) {
        $attributes['id']      = $name;
        $attributes['name']    = $name;
        $attributes['method']  = $method;
        $attributes['action']  = $url;
        $attributes = self::attributes($attributes);
        return "<form {$attributes}>";
    }

    public static function startMultipart($name='', $url='', $method='POST', $attributes=[]) {
        $attributes['enctype'] = 'multipart/form-data';
        return self::start($name, $url, $method, $attributes);
    }

    public static function endForm() {
        return '</form>';
    }

    public static function startFieldset($legend, $attributes=[]) {
        $attributes = self::attributes($attributes);
        return "<fieldset {$attributes}><legend>{$legend}</legend>";
    }

    public static function endFieldset() {
        return '</fieldset>';
    }

    public static function label($for, $text, $attributes=[]) {
        $attributes['for'] = $for;
        $attributes = self::attributes($attributes);
        return "<label ${attributes}>{$text}</label>";
    }

    public static function error($errors, $for, $attributes=array('class' => 'error')) {
        if (isset($errors[$for])) {
            $attributes = self::attributes($attributes);
            return "<span ${attributes}>{$errors[$for]}</span>";
        } else {
            return '';
        }
    }

    public static function inputText($name, $attributes=[]) {
        $attributes['type'] = 'text';
        return self::inputElement($name, $attributes);
    }

    public static function inputPassword($name, $attributes=[]) {
        $attributes['type'] = 'password';
        return self::inputElement($name, $attributes);
    }

    public static function inputRadio($name, $values=[], $separator='<br/>', $attributes=[]) {
        $attributes['type'] = 'radio';
        $output = [];
        foreach ($values as $key => $value) {
            if (TWXParam::param($name, '') == $key) {
                $attributes['selected'] = 'selected';
            } else {
                unset($attributes['selected']);
            }
            $attributes['id'] = $name . '-' . $key;
            $attributes['value'] = $key;
            $output[] = self::inputElement($name, $attributes)
                      . ' ' . self::label($attributes['id'], $value);
        }
        return implode($separator, $output);
    }

    public static function inputSelect($name, $values=[], $includeBlank=true, $attributes=[]) {
        $output = '';
        if ($includeBlank === true) {
            $values = array_reverse($values, true);
            $values[''] = '';
            $values = array_reverse($values, true);
        }
        foreach ($values as $key => $value) {
            if (TWXParam::any($name, '') == $key) {
                $attributes['selected'] = 'selected';
            } else {
                unset($attributes['selected']);
            }
            $attributes['id'] = $name . '-' . $key;
            $attributes['value'] = $key;
            $attributesTxt = self::attributes($attributes);
            $output .= "<option ${attributesTxt}>{$value}</option>";

        }
        $attributes['id']   = $name;
        $attributes['name'] = $name;
        unset($attributes['value']);
        unset($attributes['selected']);
        $attributes = self::attributes($attributes);
        return "<select ${attributes}>{$output}</select>";
    }

    public static function inputHidden($name, $attributes=[]) {
        $attributes['type'] = 'hidden';
        return self::inputElement($name, $attributes);
    }

    public static function inputFile($name, $attributes=[]) {
        $attributes['type'] = 'file';
        return self::inputElement($name, $attributes);
    }

    public static function inputSubmit($name, $label, $attributes=[]) {
        $attributes['type']  = 'submit';
        $attributes['value'] = $label;
        return self::inputElement($name, $attributes);
    }

    public static function inputCheckbox($name, $attributes=[]) {
        $attributes['type'] = 'checkbox';
        if (TMParam::exists($name)) {
            $attributes['checked'] = 'checked';
        }
        return self::inputElement($name, $attributes);
    }

    public static function inputButton($name, $label, $attributes=[]) {
        $attributes['type']  = 'button';
        $attributes['value'] = $label;
        return self::inputElement($name, $attributes);
    }

    public static function inputReset($name, $label, $attributes=[]) {
        $attributes['type']  = 'reset';
        $attributes['value'] = $label;
        return self::inputElement($name, $attributes);
    }

    public static function inputTextarea($name, $attributes=[]) {
        $value = TWXParam::any($name, '');
        $attributes['id']     = $name;
        $attributes['name']   = $name;
        $attributes['class'] .= 'form-input-textarea';
        $attributes = self::attributes($attributes);
        return "<textarea ${attributes}>{$value}</textarea>";
    }

}
