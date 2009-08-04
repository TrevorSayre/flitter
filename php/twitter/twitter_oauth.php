<?php

require_once 'twitter_library.php'; 
require_once '../OAuth.php';

/**
 * TwitterOAuth Class extending the TwitterBase for OAuth Support
 * Based on on an OAuth class called 
 *   twitterOAuth by Abraham Williams (abraham@abrah.am) http://abrah.am
 * 
 * @author Graylin Kim <graylin.kim@gmail.com>
 * @package twitterlibphp
 */
class TwitterOAuth extends TwitterLibrary {

  /* Set up the API root URL */
  public static $TO_API_ROOT = "https://twitter.com/";
 
  /**
   * Get the authorize URL
   *
   * @returns a string
   */
  function getAuthorizeURL($token) {
    if (is_array($token))
      $token = $token['oauth_token'];
    return self::$TO_API_ROOT . 'oauth/authorize' . '?oauth_token=' . $token;
  }
 
  function getAuthenticationURL($token) {
    if (is_array($token)) $token = $token['oauth_token'];
    return self::$TO_API_ROOT . 'oauth/authenticate' . '?oauth_token=' . $token;    
  }

  /**
   * construct TwitterOAuth object
   */
  function __construct($consumer_key, $consumer_secret, $oauth_token = NULL, $oauth_token_secret = NULL) {
    $this->sha1_method = new OAuthSignatureMethod_HMAC_SHA1();
    $this->consumer = new OAuthConsumer($consumer_key, $consumer_secret);
    if (!empty($oauth_token) && !empty($oauth_token_secret)) {
      $this->token = new OAuthConsumer($oauth_token, $oauth_token_secret);
    } else {
      $this->token = NULL;
    }
  }
 
  /**
   * Get a request_token from Twitter
   *
   * @returns a key/value array containing oauth_token and oauth_token_secret
   */
  function getRequestToken() {
    $r = $this->apiCall('oauth/request_token','get', '');
    $token = $this->oAuthParseResponse($r);
    $this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
    return $token;
  }
 
  /**
   * Exchange the request token and secret for an access token and
   * secret, to sign API calls.
   *
   * @returns array("oauth_token" => the access token,
   * "oauth_token_secret" => the access secret)
   */
  function getAccessToken($token = NULL) {
    $r = $this->apiCall('oauth/access_token','get', '');
    $token = $this->oAuthParseResponse($r);
    $this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
    return $token;
  }
  /**
   * Parse a URL-encoded OAuth response
   *
   * @return a key/value array
   */
  function oAuthParseResponse($responseString) {
    $r = array();
    foreach (explode('&', $responseString) as $param) {
      $pair = explode('=', $param, 2);
      if (count($pair) != 2) continue;
      $r[urldecode($pair[0])] = urldecode($pair[1]);
    }
    return $r;
  }
 
  /**
   * Executes an API call
   * @param string $twitter_method The Twitter method to call
   * @param string $http_method The HTTP method to use
   * @param string $format Return format, '' for non formated
   * @param array $options Options to pass to the Twitter method
   * @param boolean $require_credentials Whether or not credentials are required
   * @return string
   */
  protected function apiCall($twitter_method, $http_method, $format = 'xml', $options = array() ) {
    $curl_handle = curl_init();
    $api_url = self::$TO_API_ROOT.$twitter_method;
    if($format != '') $api_url .= '.'.$format;
    $req = OAuthRequest::from_consumer_and_token($this->consumer, $this->token, strtoupper($http_method), $api_url, $options);
    $req->sign_request($this->sha1_method, $this->consumer, $this->token);

    switch( $http_method ) {
      case 'get': 
	curl_setopt($curl_handle, CURLOPT_URL, $req->to_url());
	break;
      case 'post': 
	curl_setopt($curl_handle, CURLOPT_URL, $req->get_normalized_http_url());
	curl_setopt($curl_handle, CURLOPT_POST, true);
	curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $req->to_post_data());
	break;
      default: die("invalid http method '$http_method' requested");
    }

    curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($curl_handle, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
    //////////////////////////////////////////////////
    ///// Set to 1 to verify Twitter's SSL Cert //////
    //////////////////////////////////////////////////
    curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, 0);

    $twitter_data = curl_exec($curl_handle);
    $this->http_status = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
    $this->last_api_call = $api_url;
    curl_close($curl_handle);
    return $twitter_data;
  }
};

?>