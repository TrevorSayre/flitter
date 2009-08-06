<?php


//Contains calls to get and manipulate flitter data
require_once '../flitter/flitter_library.php';
//Authentication Procedures
require_once '../user_session.php';

define('FLITTERTO_CONSUMER_KEY', 'z8uOi3D6YG7CiDWDkQ8FQ');
define('FLITTERTO_CONSUMER_SECRET','k9TcYRcyQEkeuq2KNImGOIAXiZvS8SUwTw5EuffbIJs');

function twitter_oauth_start_hook($token) {
  // Save rememberme user prefference for cookies (or not) later on
  $_SESSION['rememberme'] = $_GET['rememberme'];

  // If the reffering page contains this server set that as login return point
  if( strstr($_SERVER['HTTP_REFERER'],$_SERVER['SERVER_NAME']) )
    $_SESSION['refering_page'] = $_SERVER['HTTP_REFERER'];
  // Otherwise use the server root
  else
    $_SESSION['refering_page'] = $_SERVER['SERVER_NAME'];
}

function twitter_oauth_valid_auth_hook($token,$content) {
  $flitter = new FlitterLibrary();

  //Get account information from token
  $account = $flitter->getNetworkAccountInfo('twitter',$token['user_id']);
  //If account exists, load that user. Otherwise load form resources
  $id = ($account) ? $account['user_id'] : NULL;
  $user = startUserSession($id); //NULL = cookie and session loading

  //If guest user was loaded, must create a new one from token
  if($user->get_id() == -1) {
    //create new user account. Returns its user_id in the table
    $user_id = $flitter->addUser(array('user_name'=>$token['screen_name']));
    if($user_id === FALSE) die("insertion error");
    $user = new User( array('user_id' => $user_id, 'user_name' => $token['screen_name'] ) );    
  }
  
  //If the account doesn't exist add it to this user
  if(!$account) {
    //Add account for the user
    $success = $flitter->addNetworkAccount(
		  'twitter', $token['user_id'], $user->get_id(),
		  array('oauth_key'=>$token['oauth_token'],
			'oauth_secret'=>$token['oauth_token_secret']));
    if( !$success ) die("Failure to add network account to user");
  }

  //write the completed user to session to mark login
  getSessionResource()->storeResource($user->get_info(),'accounts');
  //Set the cookies as well if they requested it at the beginning of login
  if($_SESSION['rememberme']=='true') {
    getSessionCookie()->storeResource($user->get_info(),'accounts');
  }
}

function twitter_oauth_finish_hook($token,$content) {
  //Redirect to Origional Page
  //header("Location: ".$_SESSION['refering_page']);
  header("Location: http://flitter/show_session.php");
}

function twitter_oauth_invalid_auth_hook($token,$content) {
  die("Authentication Error:<br/>\n$content");
}

function twitter_oauth_invalid_state_hook() {
  die('Attempt to access invalid state "<b>'.$_GET['state'].'</b>" failed');
}
?>