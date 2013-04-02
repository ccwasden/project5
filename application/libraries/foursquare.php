<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Codeigniter Foursquare API library
 * Built by Winceup development team
 * http://www.winceup.com
 */

class Foursquare 
{
	const API_URI = 'https://api.foursquare.com/v2/';
    const AUTHORIZE_URI = 'https://foursquare.com/oauth2/authenticate';
    const ACCESS_URI = 'https://foursquare.com/oauth2/access_token';
    const VERSION = '20120228';
       
	private $client_id = FALSE;
	private $client_secret = FALSE;
    private $method;
    private $algorithm;
    
    private $access_token;
  	private $CI;
	
	public function __construct($params) 
	{
    	$this->CI =& get_instance();
    	$this->CI->load->helper('oauth');
    	
		$this->client_id = $params['client_id'];
		$this->client_secret = $params['client_secret'];

        if(!array_key_exists('method', $params))
        {
        	$params['method'] = 'GET';
        }
        if(!array_key_exists('algorithm', $params))
        {
        	$params['algorithm'] = OAUTH_ALGORITHMS::HMAC_SHA1;
        }

        $this->method = $params['method'];
        $this->algorithm = $params['algorithm'];  
	}
	
	public function get_request_token($callback)
	{
        $params = array('client_id' => $this->client_id,
                        'response_type' => "code",
                        'redirect_uri' => $callback);                         
                                 
        $url = self::AUTHORIZE_URI.'?'.http_build_query($params,'','&');
        
        return $url;
	}
	
	public function get_access_token($code,$redirect)
	{
		$params = array('client_id' => $this->client_id,
						'client_secret' => $this->client_secret,
						'grant_type' => "authorization_code",
						'redirect_uri' => $redirect,
						'code' => $code);
		
		$url = self::ACCESS_URI.'?'.http_build_query($params,'','&');
		
		//Include the token and verifier into the header request.
        $auth = get_auth_header(self::ACCESS_URI,
        						$this->client_id,
        						$this->client_secret,
                                $params,
                                $this->method,
                                $this->algorithm);
                                
        $response = connect($url);

        //Parse the json response
        $json = json_decode($response);

		if (property_exists($json,'access_token')) 
		{
			return $json->access_token;
		}
		
		return 0;
	}
	
	public function get_current_token()
	{
		return $this->access_token;
	}
  
	public function set_current_token($token)
	{
		$this->access_token = $token;
	}	
	
	public function api($url)
	{
		$response = connect(self::API_URI.$url.'?oauth_token='.$this->get_current_token().'&v='.self::VERSION,FALSE);
		$result = json_decode($response,TRUE);
		return $result['response'];
	}
	
	
}
