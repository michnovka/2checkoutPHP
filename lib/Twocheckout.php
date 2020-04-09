<?php

abstract class Twocheckout
{
    public static $merchantCode;
    public static $secretKey;
    public static $sandbox;
    public static $verifySSL = true;
    public static $baseUrl = 'https://api.2checkout.com/rest/6.0/';
    public static $error;
    public static $format = 'array';
    const VERSION = '0.0.1';

    public static function merchantCode($value = null) {
        self::$merchantCode = $value;
    }

    public static function secretKey($value = null) {
        self::$secretKey = $value;
    }

    public static function sandbox($value = null) {
        if ($value == 1 || $value == true) {
            self::$sandbox = true;
            self::$baseUrl = 'https://api.sandbox.2checkout.com/rest/6.0/';
        } else {
            self::$sandbox = false;
            self::$baseUrl = 'https://api.2checkout.com/rest/6.0/';
        }
    }

    public static function verifySSL($value = null) {
        if ($value == 0 || $value == false) {
            self::$verifySSL = false;
        } else {
            self::$verifySSL = true;
        }
    }

    public static function format($value = null) {
        self::$format = $value;
    }
}

require(dirname(__FILE__) . '/Twocheckout/Api/TwocheckoutApi.php');
require(dirname(__FILE__) . '/Twocheckout/Api/TwocheckoutUtil.php');
require(dirname(__FILE__) . '/Twocheckout/Api/TwocheckoutError.php');
require(dirname(__FILE__) . '/Twocheckout/Api/TwocheckoutPayouts.php');
require(dirname(__FILE__) . '/Twocheckout/TwocheckoutMessage.php');
