<?php
/**
 * Tiendy HTTP Client
 * processes Http requests using curl
 *
 */
class Tiendy_Http
{
    static public $last_headers = null, $last_headers_out = null;
    
    public static function delete($path)
    {
        $response = self::_doRequest('DELETE', $path);
        if($response['status'] === 200) {
            return true;
        } else {
            Tiendy_Util::throwStatusCodeException($response['status']);
        }
    }

    public static function get($path, $params = null)
    {
        if ($params) {
            $parts = parse_url(Tiendy_Configuration::baseUrl() . $path);
            if (isset($parts['query']) && $parts['query']) {
                $path .= '&';
            } else {
                $path .= '?';
            }
            $path .= http_build_query($params);
        }
        $response = self::_doRequest('GET', $path);
        if($response['status'] === 200) {
            return json_decode($response['body'], true);
        } else {
            Tiendy_Util::throwStatusCodeException($response['status'], self::$last_headers_out . "\n" . $response['body']);
        }
    }

    public static function post($path, $params = null)
    {
        $response = self::_doRequest('POST', $path, http_build_query($params));
        $responseCode = $response['status'];
        if($responseCode === 200 || $responseCode === 201 || $responseCode === 422) {
            return json_decode($response['body'], true);
        } else {
            Tiendy_Util::throwStatusCodeException($responseCode, self::$last_headers_out . "\n" . $response['body']);
        }
    }

    public static function put($path, $params = null)
    {
        $response = self::_doRequest('PUT', $path, http_build_query($params));
        $responseCode = $response['status'];
        if($responseCode === 200 || $responseCode === 201 || $responseCode === 422) {
            return json_decode($response['body'], true);
        } else {
            Tiendy_Util::throwStatusCodeException($responseCode);
        }
    }


    private static function _doRequest($httpVerb, $path, $requestBody = null)
    {
        return self::_doUrlRequest($httpVerb, Tiendy_Configuration::baseUrl() . $path, $requestBody);
    }

    public static function _doUrlRequest($httpVerb, $url, $requestBody = null)
    {
        $token = Tiendy_Configuration::token();
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $httpVerb);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
   		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
   		$headers = array(
   		    'Accept: application/json',
   		    'User-Agent: Tiendy PHP API Library ' . Tiendy_Version::get()
   		);
   		if ($token) {    
   		    $headers[] = 'X-Tiendy-Access-Token: ' . $token;
   		} 
        
        if (!$token) {
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_USERPWD, Tiendy_Configuration::client_id() . ':' . Tiendy_Configuration::client_secret());
        }
        // curl_setopt($curl, CURLOPT_VERBOSE, true);

        if(!empty($requestBody)) {
            $headers[] = 'Content-Length: ' . strlen($requestBody);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $requestBody);
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        list(self::$last_headers, $message_body) = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);
        self::$last_headers_out = curl_getinfo($curl, CURLINFO_HEADER_OUT);
        // die ($sent_headers);
        $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        return array('status' => $httpStatus, 'body' => $message_body);
    }
}