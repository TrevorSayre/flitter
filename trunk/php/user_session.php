<?php

//various useful constants (like DB tables) here
require_once 'constants.php';
//Resources are the provide a standard way for dealing with information
require_once 'resource.php';
//FlitterLibrary for querying the database
require_once 'flitter/flitter_library.php';

function getRefererURL() {
  if(strstr($_SESSION['referer'],'http://'.$_SERVER['SERVER_NAME']))
    return $_SESSION['referer'];
  else
    return 'http://'.$_SERVER['SERVER_NAME'];
}

function startUserSession() {
  $session = getSessionResource();
  $cookie = getCookieResource();

  $flitter = new FlitterLibrary();
  
  $user = FALSE;
  foreach(array($session,$cookie) as $resource) {
    //Check for valid resource params  
    if(isResourceValid($resource,array('user_id','email'))===TRUE) {
      //Make sure the user_id is still valid
      $user = $flitter->getUserInfo($resource->user_id);
      if($user!==FALSE)
	break; //No need to look further
    }
  }
  //If user still not loaded, just load the guest account
  if($user===FALSE)  {
    //load the guest account
    $user = array('user_id'=>-1,'email'=>'Anonymous');
  }

  //Mark this page for the next page to get referer
  $session->referer  = $session->cur_page;
  $session->cur_page =  'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];

  //Store the user we just loaded, guest or otherwise
  //Includes list of fields to exclude
  $session->store($user);  

  return $user;
}

?>