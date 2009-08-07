<?php
  require_once dirname(__FILE__).'/'.'../constants.php';
  require_once dirname(__FILE__).'/'.'../database.php';

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
    if(count($args)==0) { return FALSE; }

    //Wrap up the strings
    foreach($args as $key => $val)
      if( is_string($val) ) $args[$key] = "'$val'";  

    $query = 'INSERT INTO '.TBL_USERS.'
	      ('.implode(',',array_keys($args)).')
	      VALUES ('. implode(',',$args).')';

    //Should return new user_id on success FALSE on Failure
    if($this->database->query($query.$end))
      return mysql_insert_id($this->database->connection);
    else
      return FALSE;
  }

  //Network args is a assoc array of extra args
  public function addNetworkAccount($network,$network_id,$network_args=array() ) {
    switch(strtolower($network)) {
      case 'twitter':
	if(count($network_args)!=2) die("missing oauth arguments for twitter account");
	$table = 'twitter_accounts'; break;
      default: $table = 'Unsupported_Network'; break;
    }

    $query = 'INSERT INTO '.$table.'
	      (acct_id,oauth_key,oauth_secret)
	      VALUES ( '.$network_id.',
		      "'.$network_args['oauth_key'].'",
		      "'.$network_args['oauth_secret'].'")';

    //Should return new user_id on success FALSE on Failure
    if($this->database->query($query))
      return $network_id;
    else {
      die($this->database->error_msg);
      return FALSE;
    }
  }
  public function addEvent($title,$start,$end,$location,$description) {
    $query = 'INSERT INTO events
	      (event_title,event_start,event_end,event_loc,event_desc)
	      VALUES ("'.$title.'",'.$start.','.$end.',"'.$location.'","'.$description.'")';

    //Should return new user_id on success FALSE on Failure
    if($this->database->query($query))
      return mysql_insert_id($this->database->connection);
    else
      return FALSE;    
  }
  public function addUserEventCommitment($user_id,$event_id,$type) {
    $query = 'INSERT INTO commitments
	      (user_id,event_id,type)
	      VALUES ('.$user_id.','.$event_id.',"'.$type.'")';

    if($this->database->query($query))
      return mysql_insert_id($this->database->connection);
    else
      return FALSE;
  }

  public function getEventCommitments($event_id,$type=NULL) {
    $query = 'SELECT * FROM commitments WHERE event_id='.$event_id;
    //Allow for filtering based on role
    if($type!=NULL)
      $query .= ' AND type='.$type;
    $result = $this->database->query($query);
    $commitments = array();
    while( ($commitment = mysql_fetch_assoc($result)) )
      $commitments[] = $commitment;

    return $commitments;
  }

  public function getAllEvents() {
    $result = $this->database->query('SELECT * FROM events');
    $events = array();
    while( ($event = mysql_fetch_assoc($result)) )
      $events[] = $event;
    return $events;
  }
  public function getUserCommitments($user_id,$type=NULL) {
    $query = 'SELECT * FROM commitments WHERE user_id='.$user_id;
    //Allow for filtering based on role
    if($type!=NULL)
      $query .= ' AND type='.$type;
    $result = $this->database->query($query);
    $commitments = array();
    while( ($commitment = mysql_fetch_assoc($result)) )
      $commitments[] = $commitment;

    return $commitments;
  }

  public function getUserEventCommitment($user_id,$event_id) {
    $query = "SELECT * FROM commitments WHERE user_id=$user_id AND event_id=$event_id";
    $result = $this->database->query($query);
    return mysql_fetch_array($result);
  }

  public function getEventById($event_id) {
    $result = $this->database->query('SELECT * FROM events WHERE event_id="'.$event_id.'"');
    return mysql_fetch_assoc($result);
  }
  public function getUserByEmail($email) {
    $result = $this->database->query('SELECT * FROM users WHERE email="'.$email.'"');
    return mysql_fetch_assoc($result);
  }
  public function getUserById($user_id) {
    $result = $this->database->query('SELECT * FROM users WHERE user_id="'.$user_id.'"');
    return mysql_fetch_assoc($result);
  }
  public function addUserNetworkAccountConnection($user_id,$network,$network_id) {
    $query = 'INSERT INTO connections
	      (user_id,acct_id,type)
	      VALUES ('.$user_id.',
		      '.$network_id.',
		      "'.$network.'")';

    if($this->database->query($query))
      return mysql_insert_id($this->database->connection);
    else
      return FALSE;
  }
  public function validateUser($email,$password) {
    $query = 'SELECT * FROM users WHERE email="'.$email.'" AND password="'.$password.'"';
    $result = $this->database->query($query);
    return mysql_fetch_assoc($result);
  }

  public function getUserNetworkAccounts($user_id,$network) {
    switch(strtolower($network)) {
      case 'twitter': $table = 'twitter_accounts'; break;
      default: $table = 'Unsupported_Network'; break;
    }
    $result = $this->database->query('SELECT acct_id FROM connections WHERE user_id='.$user_id.' AND type="'.$network.'"');
    while( ($connection = mysql_fetch_assoc($result)) ) {      
      $result2 = $this->database->query('SELECT * FROM '.$table.' WHERE acct_id='.$connection['acct_id']);
      $accounts[] = mysql_fetch_assoc($result2);
    }
    return $accounts;
  }
  public function getNetworkAccountUsers($network,$network_id) {
    switch(strtolower($network)) {
      case 'twitter': $table = 'twitter_accounts'; break;
      default: $table = 'Unsupported_Network'; break;
    }
    $result = $this->database->query('SELECT user_id FROM connections WHERE acct_id='.$network_id.' AND type="'.$network.'"');
    while( ($connection = mysql_fetch_assoc($result)) ) {    
      $result2 = $this->database->query('SELECT * FROM '.$table.' WHERE user_id='.$connection['user_id']);
      $users[] = mysql_fetch_assoc($result2);
    }
    return $users;
  }
  public function getUserEvents($user_id,$type=NULL) {
    $query = 'SELECT event_id FROM commitments WHERE user_id='.$user_id;
    //Allow for filtering based on role
    if($type!=NULL)
      $query .= ' AND type='.$type;
    $result = $this->database->query($query);
    $events = array();
    while( ($commitment = mysql_fetch_assoc($result)) ) {
      $result2 = $this->database->query('SELECT * FROM events WHERE event_id='.$commitment['event_id']);
      $events[] = mysql_fetch_assoc($result2);
    }
    return $events;
  }
  public function getUserNetworkAccountConnection($user_id,$network,$network_id) {
    $query = 'SELECT * FROM connections 
	      WHERE user_id='.$user_id.' 
		AND acct_id='.$network_id.'
		AND type="'.$network.'"';
    $result = $this->database->query($query);
    return mysql_fetch_assoc($result);
  }

  public function getNetworkAccountInfo($network,$network_id) {
    switch(strtolower($network)) {
      case 'twitter': $table = 'twitter_accounts'; break;
      default: $table = 'Unsupported_Network'; break;
    }
    $result = $this->database->query('SELECT * FROM '.$table.' WHERE acct_id='.$network_id);
    return mysql_fetch_assoc($result);
  }
  public function getEventInfo($event_id) {
    $result = $this->database->query('SELECT * FROM events WHERE event_id='.$event_id);
    return mysql_fetch_assoc($result);
  }
  public function getUserInfo($user_id) {
    $result = $this->database->query('SELECT * FROM '.TBL_USERS.' WHERE user_id='.$user_id);
    return mysql_fetch_assoc($result);
  }

  public function reset_table($table) {
    $this->database->query('TRUNCATE TABLE '.$table);
  }
}

?>