<?php
/**
 * This script handles the entire Twitter OAuth login process from start to finish
 * using session variables and provides hooks for app specific code
 */

//Contains all the hooks and consumer key/secrets
require_once 'twitter_config.php';
// Libarary used to access and manipulate twitter Via OAuth
require_once 'twitter_oauth.php';
// Grant access to session variables for storage during Twitter Authorization
session_start();

/**
 * Calling this section of code with ...login.php?state=start starts the OAuth Process
 *
 * You need to make sure that you have your application consumer key and secret defined
 * with calls similar to define('CONSUMER_KEY',-your key here-) so that Twitter can
 * recognize your application.
 */
if($_GET['state']=='start') {

  // Create TwitterOAuth object with app key/secret so twitter can recognize our application
  $twitter = new TwitterOAuth(FLITTERTO_CONSUMER_KEY, FLITTERTO_CONSUMER_SECRET);
  // Get Request token, used to validate on Twitter's login authorize page
  $token = $twitter->getRequestToken();
  // Save request tokens for use in grabbing the access tokens in finish section
  $_SESSION['oauth_request_token'] = $token['oauth_token'];
  $_SESSION['oauth_request_token_secret'] = $token['oauth_token_secret'];

  //Put code you would like to execute before redirecting the user here
  //For instance rememberme and reference redirect code
  twitter_oauth_start_hook($token);
  
  if($_GET['method']=='ajax') {
    echo $twitter->getAuthorizeURL($token); exit;
  }
  // Send the user to Twitter's authorization page to grant us access tokens
  header("Location: ".$twitter->getAuthorizeURL($token));
}

/**
 * You should setup your Twitter app profile to redirect to this section after successful
 * authorization by setting the redirect to ...login.php?state=finish. 
 *
 * The url that you directed the user to when starting the login process will then direct
 * the user to this section so you can acquire the access tokens and verify their account.
 * Finally you can mark the user as logged in via session variables and cookies as needed.
 */
else if($_GET['state']=='finish') {
  // Create TwitterOAuth object with app key/secret and token key/secret login start
  // The second token pair allows us to get access tokens provided the user granted
  // us access on Twitter's authorization page (by clicking 'Allow')
  $twitter = new TwitterOAuth(FLITTERTO_CONSUMER_KEY,FLITTERTO_CONSUMER_SECRET, $_SESSION['oauth_request_token'], $_SESSION['oauth_request_token_secret']);
  // Get the access tokens, they are used in place of username/password for login
  $token = $twitter->getAccessToken(); 

  // Create TwitterOAuth with app key/secret and user access key/secret
  // This should effectively log us in as the user
  $twitter = new TwitterOAuth(FLITTERTO_CONSUMER_KEY, FLITTERTO_CONSUMER_SECRET, $token['oauth_token'], $token['oauth_token_secret']);
  // Check for successful credential verification (see if we are actually logged in)
  $content = $twitter->verifyCredentials();

  //if verification is good,
  if( $twitter->lastStatusCode() == 200 ) {

    //Call App Specific Code
    twitter_oauth_valid_auth_hook($token,$content);
  }
  //Unsuccessful validation
  else {

    //output the error content for debugging
    twitter_oauth_invalid_auth_hook($token,$content);
  }

  //Put app specific code here
  //Like redirect stuff for after this finishes. That's a good idea.
  twitter_oauth_finish_hook($token,$content);
}
/**
 * This will just let you know when you've messed up one of your URL's linking to the script
 */
else {
  twitter_oauth_invalid_state_hook();
}

?>