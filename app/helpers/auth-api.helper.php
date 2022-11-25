<?php

class AuthApiHelper
{
    private const key = '`UNH.T}hi;p@*KASDyz*zlC_B%#"S(cmZ))=aIk*j1rC9Jd=65mdf{wYH]f<Vng';

    function getAuthHeader()
    {
        $header = "";
        if (isset($_SERVER['HTTP_AUTHORIZATION']))
            $header = $_SERVER['HTTP_AUTHORIZATION'];
        if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']))
            $header = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        return $header;
    }

    function getPayload()
    {
        $auth = $this->getAuthHeader(); // Bearer header.payload.signature
        $auth = explode(" ", $auth);
        if ($auth[0] != "Bearer" || count($auth) != 2) {
            return array();
        }
        $token = explode(".", $auth[1]);
        $header = $token[0];
        $payload = $token[1];
        $signature = $token[2];

        $new_signature = hash_hmac('SHA256', "$header.$payload", self::key, true);
        $new_signature = base64url_encode($new_signature);
        if ($signature != $new_signature)
            return array();

        $payload = json_decode(base64_decode($payload));
        if (!isset($payload->exp) || $payload->exp < time())
            return array();

        return $payload;
    }

    function isLoggedIn()
    {
        $payload = $this->getPayload();
        if (isset($payload->id))
            return true;
        else
            return false;
    }
}
