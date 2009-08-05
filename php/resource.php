<?php

abstract class Resource {
  abstract public function __get($field);
  abstract public function __set($field,$value);
  abstract public function setAr($value,$field); //used for arrays
  abstract public function __isset($field);
  abstract public function issetAr($field); //used for arrays
  abstract public function __unset($field);
  
  abstract public function getFields();
}

abstract class httpResource extends Resource {
  abstract public function sign();
  abstract public function storeResource($resource);
}

class sessionResource extends httpResource {
  public function __construct() { session_start(); }

  public function __get($field) 	{ return $_SESSION[$field]; }
  public function __set($field,$value) 	{ $_SESSION[$field] = $value; $this->sign(); }
  public function __isset($field) 	{ return isset($_SESSION[$field]); }
  public function __unset($field)	{ unset($_SESSION[$field]); $this->sign(); }

  public function setAr($value,$field)	{ 
    $args = func_get_args();
    array_shift($args); array_shift($args);
    $ar = &$_SESSION[$field];
    foreach($args as $field) {
      $ar = &$ar[$field];
    }
    $ar = $value;
  }
  public function issetAr($field) {
    $args = func_get_args();
    array_shift($args);
    if(!empty($args)) $last = array_pop($args);
    $ar = &$_SESSION[$field];
    foreach($args as $field) {
      $ar = &$ar[$field];
    }
    return isset($ar[$last]);
  }

  public function getFields() { return array_keys($_SESSION); }
  public function sign() { $_SESSION['flit_sig'] = get_sig($this); }
  public function storeResource($resource) {
    $skip = func_get_args(); array_shift($skip);
    foreach($resource->getFields() as $field) {
      if(in_array($field,$skip) || $field == 'flit_sig') continue;
      $_SESSION[$field] = $resource->$field;
    }
    $this->sign();
  }
}

function &getSessionResource() {
  static $resource = NULL;
  if($resource == NULL)
    $resource = new sessionResource();
  return $resource;
}

class cookieResource extends httpResource {
  public function __get($field) 	{ return $_COOKIE[$field]; }
  public function __set($field,$value) 	{ $_COOKIE[$field] = $value; setcookie($field,$value,time()+COOKIE_EXPIRE,COOKIE_PATH); $this->sign(); }
  public function __isset($field) 	{ return isset($_COOKIE[$field]); }
  public function __unset($field)	{ unset($_COOKIE[$field]); setcookie($field,NULL,time()-9999); $this->sign(); }

  public function setAr($value,$field)	{ 
    $args = func_get_args();
    array_shift($args); array_shift($args);
    $ar = &$_COOKIE[$field];
    foreach($args as $field) {
      $ar = &$ar[$field];
    }
    $ar = $value;
  }
  public function issetAr($field) {
    $args = func_get_args();
    array_shift($args);
    if(!empty($args)) $last = array_pop($args);
    $ar = &$_COOKIE[$field];
    foreach($args as $field) {
      $ar = &$ar[$field];
    }
    return isset($ar[$last]);
  }

  public function sign() { $_COOKIE['flit_sig'] = get_sig($this); }
  public function getFields() { return array_keys($_COOKIE); }
  public function storeResource($resource) {
    $skip = func_get_args(); array_shift($skip);
    foreach($resource->getFields() as $field) {
      if(in_array($field,$skip)) continue;
      $_COOKIE[$field] = $resource->$field;
    }
    $this->sign();
  }
}
function &getCookieResource() {
  static $resource = NULL;
  if($resource == NULL)
    $resource = new cookieResource();
  return $resource;
}

class userInfo extends Resource {
  private $user_info;
  
  // Info is a key => value array of initial values
  public function __construct( $info = array() ) {
    $this->user_info = $info;
  }
  public function __get($field) 	{ return $this->user_info[$field]; }
  public function __set($field,$value) 	{ $this->user_info[$field] = $value; }
  public function __isset($field) 	{ return isset($this->user_info[$field]); }
  public function __unset($field)	{ unset($this->user_info[$field]); }

  public function setAr($value,$field)	{ 
    $args = func_get_args();
    array_shift($args); array_shift($args);
    $ar = &$this->user_info[$field];
    foreach($args as $field) {
      $ar = &$ar[$field];
    }
    $ar = $value;
  }
  public function issetAr($field) {
    $args = func_get_args();
    array_shift($args);
    if(!empty($args)) $last = array_pop($args);
    $ar = &$this->user_info[$field];
    foreach($args as $field) {
      $ar = &$ar[$field];
    }
    return isset($ar[$last]);
  }

  public function getFields() { return array_keys($this->user_info); }
}


//takes optional array of required fields and validates the resource
//This is done because http resources cannot be trusted
function isResourceValid($resource, $reqs = array()) {
  //flit_sig is always required
  array_unshift($reqs,'flit_sig');
  //make sure required fields are present
  foreach($reqs as $field) {
    if(isset($resource->$field)===FALSE) {
      //Required field is missing
      return FALSE;
    }
  }
  $resource->sign();
  //Signatures must match for session params to be safe
  if($resource->flit_sig != get_sig($resource)) {
    //Signatures don't match, its forged
    echo "<br/><br/>Signatures don't match!<br/><br/>";
    return FALSE;
  }
  //Everything checks out
  return TRUE;
}

//Used field is generated to validate the resources
function get_sig($resource) {
  //A little bit of extra salt to start us off
  $str = "salt=3pD879yK63ABx3flit_sig=";
//  echo "<br/>Resource:<br/>";
  foreach($resource->getFields() as $field) {
    //Skip the signiture to make it work
    if($field == 'flit_sig') continue;
    //Build our hash string from resource
    $str .= $field.':<'.$resource->$field.'>';
//    echo "$field: ".$resource->$field."<br/>";
  }
//  echo "sha1: ".sha1($str).'<br/>';
//  echo "<br/>";
  
  //sha2-Family would be stronger, but may not be native, look into it more
  return sha1($str);
}


?>