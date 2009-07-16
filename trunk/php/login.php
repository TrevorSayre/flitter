<?php
/**
 * This script handles the entire Twitter OAuth login process from start to finish
 * using session variables and marks loggin via session and cookie variables
 */


// Include containing CONSUMER_KEY, CONSUMER_SECRET, COOKIE_EXPIRE, and COOKIE_PATH defines
require_once 'php/constants.php';  
// Libarary used to access and manipulate twitter Via OAuth
require_once 'php/twitter-lib.php';
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
  $twitter = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);

  // Get Request token, used to validate on Twitter's login authorize page
  $token = $twitter->getRequestToken();

  // Save request tokens for use in grabbing the access tokens in finish section
  $_SESSION['oauth_request_token'] = $token['oauth_token'];
  $_SESSION['oauth_request_token_secret'] = $token['oauth_token_secret'];

  // Save rememberme user prefference for cookies (or not) later on
  $_SESSION['rememberme'] = $_GET['rememberme'];

  // If the reffering page contains this server set that as login return point
  if( strstr($_SERVER['HTTP_REFERER'],$_SERVER['SERVER_NAME']) )
    $_SESSION['refering_page'] = $_SERVER['HTTP_REFERER'];
  // Otherwise use the server root
  else
    $_SESSION['refering_page'] = $_SERVER['SERVER_NAME'];

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
  $twitter = new TwitterOAuth(CONSUMER_KEY,CONSUMER_SECRET, $_SESSION['oauth_request_token'], $_SESSION['oauth_request_token_secret']);

  // Get the access tokens, they are used in place of username/password for login
  $token = $twitter->getAccessToken(); 

  // Create TwitterOAuth with app key/secret and user access key/secret
  // This should effectively log us in as the user
  $twitter = new TwitterOAuth($consumer_key, $consumer_secret, $token['oauth_token'], $token['oauth_token_secret']);

  // Check for successful credential verification (see if we are actually logged in)
  $content = $twitter->verifyCredentials();
  if( $twitter->lastStatusCode() == 200 ) {

    //Access tokens will never change for this user, so store them in the DB with userID
    $query = 'INSERT IGNORE INTO '.TBL_USERS.' VALUES('.$token['user_id'].',
						    "'.$token['screen_name'].'",
						    "'.$token['oauth_token'].'",
						    "'.$token['oauth_token_secret'].'")';
    //Check to make sure insert was successful
    if( !$database->query($query) )
      //If query fails die and output debugging information
      die($database->last_query."<br/><br/>Account Insertion Error:<br/>".$database->error_msg);

    //Set Session Variables to mark user as logged in (Session based login system)
    $_SESSION['username'] = $token['screen_name'];
    $_SESSION['userid'] = $token['user_id'];

    //Set the cookies as well if they requested it at the beginning of the login process
    if($_SESSION['rememberme']=='true') {
      setcookie('username',$token['screen_name'],time()+COOKIE_EXPIRE, COOKIE_PATH);
      setcookie('userid',$token['user_id'],time()+COOKIE_EXPIRE, COOKIE_PATH);
    }
  }
  //Unsuccessful validation
  else
    //output the error content for debugging
    die("Authentication Error:<br/>\n$content");

  //Redirect to Origional Page
  header("Location: ".$_SESSION['refering_page']);
}

/**
 * This will just let you know when you've messed up one of your URL's linking to the script
 */
else {
    die('Attempt to access invalid state <b>'.$_GET['state'].'</b> failed');
}

?>