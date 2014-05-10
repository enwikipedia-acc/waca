<?php

function wfDebugLog($section, $message)
{
    // no op, needed by the mediawiki oauth library.   
}

class OAuthUtility
{
    private $consumerToken;
    private $consumerSecret;
    private $baseUrl;
    private $baseUrlInternal;
    
    public function __construct($consumerToken, $consumerSecret, $baseUrl, $baseUrlInternal)
    {
        $this->consumerSecret = $consumerSecret;
        $this->consumerToken = $consumerToken;
        $this->baseUrl = $baseUrl;
        $this->baseUrlInternal = $baseUrlInternal;
    }
    
    public function getRequestToken()
    {    
        global $toolUserAgent;
        
        $endpoint = $this->baseUrlInternal . '/initiate&format=json&oauth_callback=oob';
        
        $c = new OAuthConsumer( $this->consumerToken, $this->consumerSecret );
        $parsed = parse_url( $endpoint );
        $params = array();
        parse_str($parsed['query'], $params);
        $req_req = OAuthRequest::from_consumer_and_token($c, NULL, "GET", $endpoint, $params);
        $hmac_method = new OAuthSignatureMethod_HMAC_SHA1();
        $req_req->sign_request($hmac_method, $c, NULL);

        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, (string) $req_req );
        curl_setopt( $ch, CURLOPT_PORT , 443 );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
        curl_setopt( $ch, CURLOPT_HEADER, 0 );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_USERAGENT, $toolUserAgent);
        $data = curl_exec( $ch );
        
        if( !$data ) 
        {
	        throw new Exception('Curl error: ' . curl_error( $ch ));
        }
        
        $token = json_decode( $data );
        
        if(isset($token->error))
        {
            throw new Exception("Error encountered while getting token.");
        }
        
        return $token;
    }
    
    public function getAuthoriseUrl($requestToken)
    {
        return "{$this->baseUrl}/authorize&oauth_token={$requestToken->key}&oauth_consumer_key={$this->consumerToken}";
    }
    
    public function callbackCompleted($requestToken, $requestSecret, $verifyToken)
    {
        global $toolUserAgent;
        
        $endpoint = $this->baseUrlInternal . '/token&format=json';

        $c = new OAuthConsumer( $this->consumerToken, $this->consumerSecret );
        $rc = new OAuthConsumer( $requestToken, $requestSecret );
        $parsed = parse_url( $endpoint );
        parse_str($parsed['query'], $params);
        $params['oauth_verifier'] = trim($verifyToken);

        $acc_req = OAuthRequest::from_consumer_and_token($c, $rc, "GET", $endpoint, $params);
        
        $hmac_method = new OAuthSignatureMethod_HMAC_SHA1();
        $acc_req->sign_request($hmac_method, $c, $rc);

        unset( $ch );
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, (string) $acc_req );
        curl_setopt( $ch, CURLOPT_PORT , 443 );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
        curl_setopt( $ch, CURLOPT_HEADER, 0 );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_USERAGENT, $toolUserAgent);
        
        $data = curl_exec( $ch );
        
        if( !$data )
        {
	        throw new Exception('Curl error: ' . curl_error( $ch ));
        }

        $token = json_decode( $data );
        
        return $token;
    }
    
    public function apiCall($apiParams, $accessToken, $accessSecret)
    {
        global $mediawikiWebServiceEndpoint, $toolUserAgent;
        
        $userToken = new OAuthToken( $accessToken, $accessSecret );
        
        $apiParams['format'] = 'json';
        
        $c = new OAuthConsumer( $this->consumerToken, $this->consumerSecret );
        
        $api_req = OAuthRequest::from_consumer_and_token(
            $c,           // Consumer
            $userToken, // User Access Token
            "GET",        // HTTP Method
            $mediawikiWebServiceEndpoint,      // Endpoint url
            $apiParams    // Extra signed parameters
        );
        
        $hmac_method = new OAuthSignatureMethod_HMAC_SHA1();
        
        $api_req->sign_request( $hmac_method, $c, $userToken );
        
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $mediawikiWebServiceEndpoint . "?" . http_build_query( $apiParams ) );
        curl_setopt( $ch, CURLOPT_HEADER, 0 );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array( $api_req->to_header() ) ); // Authorization header required for api
        curl_setopt( $ch, CURLOPT_USERAGENT, $toolUserAgent);
        
        $data = curl_exec( $ch );
        
        if( !$data ) 
        {
	        throw new Exception('Curl error: ' . curl_error( $ch ));
        }
        
        return json_decode($data);
    }
    
    public function getIdentity($accessToken, $accessSecret)
    {
       
        global $toolUserAgent;
        
        $endpoint = $this->baseUrlInternal . '/identify&format=json';

        $c = new OAuthConsumer( $this->consumerToken, $this->consumerSecret );
        $rc = new OAuthToken( $accessToken, $accessSecret );
        $parsed = parse_url( $endpoint );
        parse_str($parsed['query'], $params);

        $acc_req = OAuthRequest::from_consumer_and_token($c, $rc, "GET", $endpoint, $params);
        
        $hmac_method = new OAuthSignatureMethod_HMAC_SHA1();
        $acc_req->sign_request($hmac_method, $c, $rc);

        unset( $ch );
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, (string) $acc_req );
        curl_setopt( $ch, CURLOPT_PORT , 443 );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
        curl_setopt( $ch, CURLOPT_HEADER, 0 );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_USERAGENT, $toolUserAgent);
        
        $data = curl_exec( $ch );
        
        if( !$data )
        {
	        throw new Exception('Curl error: ' . curl_error( $ch ));
        }

        $identity = JWT::decode( $data , $this->consumerSecret );
        
        return $identity;
    }
}