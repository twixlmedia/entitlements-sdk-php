<?php

final class TWXHttpException extends Exception {
}

final class TWXHttp {

    private function __construct() {}

    public static function postRequest($url, $data) {

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

        $response = curl_exec($ch);
        $errno    = curl_errno($ch);
        $errmsg   = curl_error($ch);

        curl_close($ch);

        if ($errno != 0) {
            throw new TWXHttpException($errmsg, $errno);
        }

        return $response;

    }

    public static function getRequest($url, $params=array(), $file=null) {

        if (count($params) > 0) {
            $url = $url . '?' . http_build_query($params);
        }

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        if (!empty($file)) {
            $fp = fopen($file, 'w+');
            curl_setopt($ch, CURLOPT_FILE, $fp);
        } else {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        }

        $response = curl_exec($ch);

        $errno  = curl_errno($ch);
        $errmsg = curl_error($ch);

        curl_close($ch);
        if (!empty($file)) {
            fclose($fp);
        }

        if ($errno != 0) {
            throw new TWXHttpException($errmsg, $errno);
        }

        if (empty($file)) {
            return $response;
        } else {
            return null;
        }

    }

    public static function getHeaders($url) {

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $response = TMString::splitLines($response);

        $headers = array('status' => $status);
        for ($i = 0; $i < sizeof($response); $i++) {

            $data = trim($response[$i]);
            if ($i == 0 || empty($data)) {
                continue;
            }

            $parts = explode(':', $data);

            $key   = sizeof($parts) > 0 ? trim($parts[0]) : '';
            $value = sizeof($parts) > 1 ? trim($parts[1]) : '';

            if (!empty($key)) {
                $headers[$key] = $value;
            }

        }

        ksort($headers);

        return $headers;

    }

}
