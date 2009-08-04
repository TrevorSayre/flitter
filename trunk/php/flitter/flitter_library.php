<?php

if(file_exists('../constants.php'))
  require_once '../constants.php';
elseif(file_exists('constants.php'))
  require_once 'constants.php';
else
  require_once 'php/constants.php';

if(file_exists('../database.php'))
  require_once '../database.php';
elseif(file_exists('database.php'))
  require_once 'database.php';
else
  require_once 'php/database.php';

class FlitterLibrary {
  
  protected $database;

  protected $http_status;
  function lastStatusCode() {
    return $this->http_status;
  }

  protected $last_api_call;
  function lastApiCall() {
    return $this->last_api_call;
  }
  
  public function __construct() {
    $this->database = new MySQLDB(DB_SERVER,DB_USER,DB_PASS,DB_NAME);
  }

  public function addUser($args) {
    $query = 'INSERT INTO '.TBL_USERS;
    $query .= ' ('.implode(',',array_keys($args)).') ';
    $query .= 'VALUES (';
    if(count($args)==0) {
      //Missing arguements
      return FALSE;
    }
    $val = array_pop($args);

    if( is_string($val) )
      $end = '"'.$val.'")';
    else
      $end = $val.')';

    foreach($args as $value) {
      if(is_string($value))
	$query .= '"'.$value.'",';
      else
	$query .= $value.',';
    }

    //Should return new user_id on success FALSE on Failure
    if($this->database->query($query.$end))
      return mysql_insert_id($this->database->connection);
    else
      return FALSE;
  }

  public function getUserNetworkAccounts($user_id,$network) {
    switch(strtolower($network)) {
      case 'twitter': $table = 'twitter_accounts'; break;
      default: $table = 'Unsupported_Network'; break;
    }
    $result = $this->database->query('SELECT * FROM '.$table.' WHERE user_id='.$user_id);
    $networks = array();
    while( ($networks[] = mysql_fetch_assoc($result)) !== FALSE )
      continue;
    array_pop($networks); //Remove the NULL array from end
    return $networks;
  }

  //Network args is a assoc array of extra args
  public function addNetworkAccount($network,$network_id,$user_id,$network_args=array() ) {
    switch(strtolower($network)) {
      case 'twitter':
	if(count($network_args)!=2) die("missing oauth arguements for twitter account");
	$table = 'twitter_accounts'; break;
      default: $table = 'Unsupported_Network'; break;
    }

    $result = $this->database->query('INSERT INTO '.$table.'
				(acct_id,oauth_key,oauth_secret,user_id)
				VALUES ( '.$network_id.',
					"'.$network_args['oauth_token'].'",
					"'.$network_args['oauth_secret'].'",
					  '.$user_id.')');

    //Should be TRUE on successful account insert, FALSE otherwise
    return $result;
  }

  public function getNetworkAccountInfo($network,$network_id) {
    switch(strtolower($network)) {
      case 'twitter': $table = 'twitter_accounts'; break;
      default: $table = 'Unsupported_Network'; break;
    }
    //network_id is a primary key, will only return one row
    $result = $this->database->query('SELECT * FROM '.$table.' WHERE acct_id='.$network_id);
    return mysql_fetch_assoc($result);
  }

  public function getUserInfo($user_id) {
    $result = $this->database->query('SELECT * FROM '.TBL_USERS.' WHERE user_id='.$user_id);
    return mysql_fetch_assoc($result);
  }

}

?>