<?php

if(!function_exists('hmac')){

	function hmac ($key, $data){
		$b = 64; // byte length for md5
		if (strlen($key) > $b) {
			$key = pack("H*",md5($key));
		}
		$key  = str_pad($key, $b, chr(0x00));
		$ipad = str_pad('', $b, chr(0x36));
		$opad = str_pad('', $b, chr(0x5c));
		$k_ipad = $key ^ $ipad ;
		$k_opad = $key ^ $opad;
		return md5($k_opad  . pack("H*",md5($k_ipad . $data)));
	}

}

class Twocheckout_Api_Return_Object{
	protected $httpResponseCode;
	protected $httpResponseHeaders;
	protected $httpResponseData;

	public function __construct($httpResponseCode, $httpResponseHeaders, $httpResponseData)
	{
		$this->httpResponseCode = $httpResponseCode;
		$this->httpResponseHeaders = $httpResponseHeaders;
		$this->httpResponseData = $httpResponseData;
	}

	public function httpResponseCode(){
		return $this->httpResponseCode;
	}

	public function httpResponseHeaders(){
		return $this->httpResponseHeaders;
	}

	public function httpResponseData(){
		return $this->httpResponseData;
	}

	public function httpResponseErrorMessage(){
		$error_data = json_decode($this->httpResponseData, true);
		return $error_data['error_code'].": ".$error_data['message'];
	}
}

class Twocheckout_Api_Requester
{

	protected $merchantCode;
	protected $secretKey;
	protected $sandbox;
	protected $return_full_object = false;
	protected $verifySSL = true;
	protected $baseUrl = 'https://api.2checkout.com/rest/6.0/';
	const TIMEOUT = 60;

	public static $responseHeaders;

	function __construct($return_full_object = false) {
        $this->baseUrl = Twocheckout::$baseUrl;
        $this->verifySSL = Twocheckout::$verifySSL;
        $this->secretKey = Twocheckout::$secretKey;
        $this->merchantCode = Twocheckout::$merchantCode;
        $this->sandbox = Twocheckout::$sandbox;
        $this->return_full_object = $return_full_object;
    }

	/**
	 * @return string
	 */
    protected function getAuthHeader(){
		$date = gmdate('Y-m-d H:i:s');

		$hash = hmac($this->secretKey, strlen($this->merchantCode).$this->merchantCode.strlen($date).$date);

		return 'X-Avangate-Authentication: code="'.$this->merchantCode.'" date="'.$date.'" hash="'.$hash.'"';
	}


	/**
	 * @param string $urlSuffix
	 * @param array $params
	 * @param array $data
	 * @param string $method
	 * @param bool $return_full_object
	 * @return string|Twocheckout_Api_Return_Object
	 * @throws Twocheckout_Error
	 */
	public function doCall($urlSuffix, $params = array(), $data=array(), $method = 'GET')
    {
		$ch = curl_init();

        $url = $this->baseUrl . $urlSuffix;


        if(!empty($params)){
        	$url .= '?'.http_build_query($params);
		}

		curl_setopt($ch, CURLOPT_URL, $url);

		$header = array("Content-Type:application/json","Accept:application/json");

		if(!empty($data)){
			$data = json_encode($data);
			$header[] = "Content-Length:".strlen($data);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}else{
			$header[] = "Content-Length:0";
		}

		$header[] = $this->getAuthHeader();

		if($method != 'GET') {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		}

        if (!$this->verifySSL) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, "2Checkout PHP/".Twocheckout::VERSION);

		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);
		curl_setopt($ch, CURLOPT_HEADERFUNCTION,'header_callback');

		Twocheckout_Api_Requester::$responseHeaders = array();

		function header_callback($ch, $header_line)
		{
			Twocheckout_Api_Requester::$responseHeaders[] = $header_line;
			return strlen($header_line);
		}

        $resp = curl_exec($ch);

		curl_close($ch);

		$responseHeaders = array();
		$responseStatusCode = null;

        foreach (Twocheckout_Api_Requester::$responseHeaders as $i => $responseHeader){

			$responseHeader = trim($responseHeader);

			if(!$responseHeader)
        		continue;

			if($i === 0){
				// http code
				preg_match('@HTTP/[0-9]\.[0-9] ([0-9]+) (.*)@i', $responseHeader, $m);
				$responseStatusCode = $m[1];
			}else{
				preg_match('/^([a-z0-9\-]+):(.*)/i', $responseHeader, $m);
				$responseHeaders[trim($m[1])] = trim($m[2]);
			}
        }

		unset($responseHeader);
		Twocheckout_Api_Requester::$responseHeaders = null;

        if ($resp === FALSE) {
        	var_dump(curl_errno($ch));
        	var_dump(curl_error($ch));

            throw new Twocheckout_Error("cURL call failed", "403");
        } else {
        	if($this->return_full_object){
        		return new Twocheckout_Api_Return_Object($responseStatusCode, $responseHeaders, utf8_encode($resp));
			}else {
				return utf8_encode($resp);
			}
        }
	}

}
