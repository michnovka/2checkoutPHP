<?php

class Twocheckout_Util extends Twocheckout
{

    static function returnResponse($contents, $format=null) {
        $format = $format == null ? Twocheckout::$format : $format;
        switch ($format) {
            case "array":
                $response = self::objectToArray($contents);
                self::checkError($response);
                break;
            case "force_json":
                $response = self::objectToJson($contents);
                break;
            default:
                $response = self::objectToArray($contents);
                self::checkError($response);
                $response = json_encode($contents);
                $response = json_decode($response);
        }
        return $response;
    }

    public static function objectToArray($object)
    {
        $object = json_decode($object, true);
        $array=array();
        foreach($object as $member=>$data)
        {
            $array[$member]=$data;
        }
        return $array;
    }

    public static function objectToJson($object)
    {
        return json_encode($object);
    }

    public static function checkError($contents)
    {
        if (isset($contents['errors'])) {
            throw new Twocheckout_Error($contents['errors'][0]['message']);
        } elseif (isset($contents['exception'])) {
            throw new Twocheckout_Error($contents['exception']['errorMsg'], $contents['exception']['errorCode']);
        }
    }

}
