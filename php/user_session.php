<?php

//various useful constants (like DB tables) here
require_once 'constants.php';
//The user class definition
require_once 'user.php';
//Resources are the provide a standard way for dealing with information
require_once 'resource.php';
//FlitterLibrary for querying the database
require_once 'flitter/flitter_library.php';

function getRefererURL() {
/*
  // If the reffering page contains this server set that as login return point
  echo 'HTTP REFERER = '.$_SERVER['HTTP_REFERER'].'<br/><br/>';
  if( strstr($_SERVER['HTTP_REFERER'],$_SERVER['SERVER_NAME']) )
    return $_SERVER['HTTP_REFERER'];
  // Otherwise use the server root
  else
    return $_SERVER['SERVER_NAME'];
*/
  if(strstr($_SESSION['referer'],'http://'.$_SERVER['SERVER_NAME']))
    return $_SESSION['referer'];
  else
    return 'http://'.$_SERVER['SERVER_NAME'];
}

function startUserSession($user_id = NULL) {
  global $database;
  //echo "<br/>startUserSession<br/>";
  $session = getSessionResource();
  $cookie = getCookieResource();
  $flitter = new FlitterLibrary();
  
  //make sure the supplied user_id is valid, otherwise NULL
  if($user_id!=NULL) {
    $info = $flitter->getUserInfo($user_id);
    if($info===FALSE) $user_id = NULL;
  }
  //If user_id is NULL, look in session and cookie for info
  else {
    foreach(array($session,$cookie) as $resource) {
      //Check for valid resource params  
      if(isResourceValid($resource,array('user_id','email'))===TRUE) {
	//Make sure the user_id is still valid
	$info = $flitter->getUserInfo($resource->user_id);
	if($info!==FALSE) {
	  $user_id = $resource->user_id;
	  break; //No need to look further
	}
      }
    }
  }
  //If user_id is still NULL, just load the guest account
  if($user_id==NULL)  {
    //load the guest account
    $user = new User( array('user_id'=>-1,'email'=>'Anonymous') );
  }
  else {
    //echo "<br/>CreatingUser<br/>";
    //Create a new user from info
    $user = new User($info);
    //Loop through supported networks to add their accounts
    foreach( array('twitter') as $type ) {
	//Get all information about any accounts the user might have and add them
	$accounts = $flitter->getUserNetworkAccounts($user_id,$type);
	if(is_array($accounts)) 
	  foreach($accounts as $account) {
	    //echo "<br/>AddingNewAccount<br/>";
	    $user->addAccount( $type, $account);
	  }
    }
  }

  //Mark this page for the next page to get referer
  $session->referer  = $session->cur_page;
  $session->cur_page =  'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];

  //Store the user we just loaded, guest or otherwise
  //Includes list of fields to exclude
  $session->storeResource($user->get_info(),'accounts');  
  //echo "<br/>Exit startUserSession<br/>";
  return $user;
}

?>