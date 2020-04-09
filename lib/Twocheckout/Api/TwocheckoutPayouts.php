<?php

class Twocheckout_Payouts extends Twocheckout
{

	/**
	 * @param array $params
	 * @return array|false|mixed|string
	 * @throws Twocheckout_Error
	 */
    public static function retrieve($params=array())
    {
		$request = new Twocheckout_Api_Requester(true);

		$urlSuffix = 'payouts/';

		/** @var Twocheckout_Api_Return_Object $result */
		$result = $request->doCall($urlSuffix, $params);

		if($result->httpResponseCode() != 200){
			throw new Twocheckout_Error($result->httpResponseErrorMessage(), $result->httpResponseCode());
		}else {
			return Twocheckout_Util::returnResponse($result->httpResponseData());
		}
    }

	/**
	 * @param array $params
	 * @return array|false|mixed|string
	 * @throws Twocheckout_Error
	 */
    public static function retrievePending($params=array())
    {
        $request = new Twocheckout_Api_Requester(true);

        $urlSuffix = 'payouts/pending/';

        /** @var Twocheckout_Api_Return_Object $result */
        $result = $request->doCall($urlSuffix, $params);

        if($result->httpResponseCode() != 200){
			throw new Twocheckout_Error($result->httpResponseErrorMessage(), $result->httpResponseCode());
		}else {
			return Twocheckout_Util::returnResponse($result->httpResponseData());
		}
    }


}
