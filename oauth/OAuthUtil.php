<?php

class OAuthUtil
{
    private $consumerToken;
    private $consumerSecret;
    private $baseUrl;
    
    public function __construct($consumerToken, $consumerSecret, $baseUrl)
    {
        $this->consumerSecret = $consumerSecret;
        $this->consumerToken = $consumerToken;
        $this->baseUrl = $baseUrl;
    }
    
    public function getRequestToken()
    {    
        global $toolUserAgent;
        
        $endpoint = $this->baseUrl . '/initiate&format=json&oauth_callback=oob';
        
        $c = new OAuthConsumer( $this->consumerKey, $this->consumerSecret );
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
        
        return $token;
    }
    
    public function getAuthoriseUrl($requestToken)
    {
        return "{$baseUrl}/authorize&oauth_token={$requestToken}&oauth_consumer_key={$this->consumerToken}";
    }
    
    public function callbackCompleted($requestToken, $verifyToken)
    {
        global $toolUserAgent;
        
        $endpoint = $this->baseUrl . '/token&format=json';

        $c = new OAuthConsumer( $this->consumerKey, $this->consumerSecret );
        $rc = new OAuthConsumer( $requestToken->key, $requestToken->secret );
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
        
        if( !$data ) {
	        throw new Exception('Curl error: ' . curl_error( $ch ));
        }

        $token = json_decode( $data );
        
        return $token;
    }
}