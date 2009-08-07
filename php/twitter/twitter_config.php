<?php


//Contains calls to get and manipulate flitter data
require_once '../flitter/flitter_library.php';
//Authentication Procedures
require_once '../user_session.php';

define('FLITTERTO_CONSUMER_KEY', 'z8uOi3D6YG7CiDWDkQ8FQ');
define('FLITTERTO_CONSUMER_SECRET','k9TcYRcyQEkeuq2KNImGOIAXiZvS8SUwTw5EuffbIJs');

function twitter_oauth_start_hook($token) {

  //If the user isn't logged in, force them to
  $user = startUserSession();
  if($user->get_id() == -1) {
    header('Location: http://'.$_SERVER['SERVER_NAME'].'/account/login');
  }

  // If the reffering page contains this server set that as login return point
  if( strstr($_SERVER['HTTP_REFERER'],$_SERVER['SERVER_NAME']) )
    $_SESSION['refering_page'] = $_SERVER['HTTP_REFERER'];
  // Otherwise use the server root
  else
    $_SESSION['refering_page'] = $_SERVER['SERVER_NAME'];
}

function twitter_oauth_valid_auth_hook($token,$content) {
  $flitter = new FlitterLibrary();

  //Should be logged-in from earlier
  $user = startUserSession();

  //If the account doesn't exist add it
  $account = $flitter->getNetworkAccountInfo('twitter',$token['user_id']);
  if(!$account) {
    $success = $flitter->addNetworkAccount(
		  'twitter', $token['user_id'],
		  array('oauth_key'=>$token['oauth_token'],
			'oauth_secret'=>$token['oauth_token_secret']));
    if( !$success ) die("Failure to add network account");
    $makeConnect = TRUE;
  }
  else {
    //Check if connection is present between user and account
    $connection = $flitter->getUserNetworkAccountConnection($user->get_id(),'twitter',$token['user_id']);
    if($connection == NULL) $makeConnect = TRUE;
  }

  if($makeConnect === TRUE)
    $flitter->addUserNetworkAccountConnection($user->get_id(),'twitter',$token['user_id']);
}

function twitter_oauth_finish_hook($token,$content) {
  //Redirect to Origional Page
  header("Location: ".$_SESSION['refering_page']);
  //header("Location: http://flitter/show_session.php");
}

function twitter_oauth_invalid_auth_hook($token,$content,$statusCode) {
  
  die("Authentication Error $statusCode:<br/>\n$content");
}

function twitter_oauth_invalid_state_hook() {
  die('Attempt to access invalid state "<b>'.$_GET['state'].'</b>" failed');
}
?>