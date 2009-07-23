<?php

require_once "php/constants.php";

abstract class Resource {
  abstract public function __get($field);
  abstract public function __set($field,$value);
  abstract public function __isset($field);
  abstract public function __unset($field);
}

class sessionResource extends Resource {
  public function __get($field) 	{ return $_SESSION[$field]; }
  public function __set($field,$value) 	{ $_SESSION[$field] = $value; }
  public function __isset($field) 	{ return isset($_SESSION[$field]); }
  public function __unset($field)	{ unset($_SESSION[$field]); }
}

class cookieResource extends Resource {
  public function __get($field) 	{ return $_COOKIE[$field]; }
  public function __set($field,$value) 	{ $_COOKIE[$field] = $value; setcookie($field,$value,time()+COOKIE_EXPIRE,COOKIE_PATH); }
  public function __isset($field) 	{ return isset($_COOKIE[$field]); }
  public function __unset($field)	{ unset($_COOKIE[$field]); setcookie($field,NULL,time()-9999); }
}

class userResource extends Resource {
  private $user_info;
  
  // Info is a key => value array of initial values
  public __construct( $info ) {
    this->$user_info = $info
  }
  public function __get($field) 	{ return $this->user_info[$field]; }
  public function __set($field,$value) 	{ $this->user_info[$field] = $value; }
  public function __isset($field) 	{ return isset($this->user_info[$field]); }
  public function __unset($field)	{ unset($this->user_info[$field]); }
}

class User {
  private name;
  private id;

  public function get_name() { return $this->name; }
  public function get_id() { return $this->id; }

  public function set_name($name) { $this->name = $name; }
  public function set_id($id) { $this->id = $id; }
}

global $_RESOURCE;
$_RESOURCE = new Array();

$_RESOURCE['session'] = new sessionResource();
$_RESOURCE['cookie'] = new cookieResource();
$_RESOURCE['guestUser'] = new userResource( array( 'username' => 'anonymous', 'userid' => 1 ) );

function loadUserFromResourceDatabase($resource,$database) {
  //Attempt to indentify the user from the given resource
  //
  //Check for User settings in resource
  if( !isset($resource->username) || !isset($resource->userid) )
    return false;

  //Create a new user
  $user = new User();
  //Transfer information from resource
  $user->set_name( $resource['username'] );
  $user->set_id( $resource['userid'] );

  //Check if we have information for this user just in case
  $result = $database->query('SELECT * FROM '.TBL_USERS." 
			      WHERE user_sn='".$_COOKIE['username']."' and 
				    user_id=".$_COOKIE['userid']);
  //Should have only 1 match
  if(mysql_num_rows($result)!=1) {
    //Load failure
    //Destroy the faulty resource information
    unset($resource->username);
    unset($resource->userid);
    return false;
  }
  $user->set_oauth_key( mysql_result($result,0,'oauth_key') );
  $user->set_oauth_secret( mysql_result($result,0,'oauth_secret') );
  return $user;
}

function startUserSession() {
  global $database;
  $session_start();
  
  //Try loading the user from each of these resources one at a time
  //until we find a good place to load from
  // Guest resource is set up to be the default resource when others fail
  if (	($user = loadUserFromResourceDatabase($GLOBALS['RESOURCE']['session'],	$database)) ||
	($user = loadUserFromResourceDatabase($GLOBALS['RESOURCE']['cookie'],	$database)) ||
	($user = loadUserFromResourceDatabase($GLOBALS['RESOURCE']['guest'],	$database)) ) {
      //Set session resource variables to match user
      $GLOBALS['RESOURCE']['session']->username = $user->get_name();
      $GLOBALS['RESOURCE']['session']->userid = $user->get_id();
      //return user for session
      return $user;
  }
  //None of the resources supplied had valid user information, not even guest
  //This should only happen if you set things up wrong
  else
    die("There has been a failure to load any user profile");
}

function saveUserSessionToResource($user,$resource) {
  $resource->userid = $user->get_id();
  $resource->username = $user->get_name();
}

function endUserSession($user) {
  
}

?>