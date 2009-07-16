<?php
/**
 * UserSession.php
 * 
 * The UserSession class is meant to simplify the task of keeping
 * track of logged in users and also guests.
 *
 */

require_once 'constants.php';
require_once 'database.php';

class UserSession
{
  public $time;
  public $logged_in;
  public $user_name;
  public $user_id;
  public $user_level;
  public $oauth_key;
  public $oauth_secret;

  /* Class constructor */
  function __construct($loadUser=TRUE){
    if($loadUser) {
      //Load a Stored User if Available and Valid
      $this->logged_in = $this->loadUser();
    }
/*
    //The database connection
    global $database;

    //If a user account was loaded
    if($this->logged_in){
      // Add/Update the account timestamp
      $database->addActiveUser($this->username, time());
    }
    else{
      //Mark user has guest, add active guest
      $this->username = $_SESSION['username'] = GUEST_NAME;
      $database->addActiveGuest($_SERVER['REMOTE_ADDR'], time() );
    }
    
    // Remove inactive visitors from database
    $database->removeInactiveUsers();
    $database->removeInactiveGuests();
*/    
  }

  /**
  * loadStoredUser - Checks if the user has already previously
  * logged in, and a session with the user has already been
  * established. Also checks to see if user has been remembered.
  * If so, the database is queried to make sure of the user's 
  * authenticity. Returns true if the user has logged in.
  */
  function loadUser(){
    //The database connection
    global $database;
    // Grant access to session variables
    session_start();   //Tell PHP to start the session

    //Check for cookie variables, they should be safe
    if( isset($_COOKIE['username']) && isset($_COOKIE['userid'])){
      //Check if we have information for this user just in case
      $result = $database->query('SELECT * FROM '.TBL_USERS." 
				  WHERE user_sn='".$_COOKIE['username']."' and 
					user_id=".$_COOKIE['userid']);
      //Should have only 1 match
      if(mysql_num_rows($result)!=1) {
	//Destroy the faulty information
	setcookie('username',NULL,time()-9999);
	setcookie('userid',NULL,time()-9999);
	return false; //The user has an invalid username and userid pair
      }
    }

    //Check for session vars next, they are less safe
    else if (isset($_SESSION['username']) && isset($_SESSION['userid'])) {
      //Check if we have information for this user
      $result = $database->query('SELECT * FROM '.TBL_USERS." 
				  WHERE user_sn='".$_SESSION['username']."' and 
					user_id=".$_SESSION['userid']);
      //Should only have one match
      if(mysql_num_rows($result)!=1) {
	//Destroy the faulty information
	unset($_SESSION['username']);
	unset($_SESSION['userid']);
	return false; //The user has an invalid username and userid pair
      }
    }

    //The user does not have session or cookie variables set
    else
      return false; 

    //Store User Info
    this->$user_name = $_SESSION['username'] = mysql_result($result,0,'user_sn');
    this->$user_id = $_SESSION['userid'] = mysql_result($result,0,'user_id');
    this->$oauth_key = mysql_result($result,0,'oauth_key');
    this->$oauth_secret = mysql_result($result,0,'oauth_secret');
    return true;
  }


   /**
    * logout - Gets called when the user wants to be logged out of the
    * website. It deletes any cookies that were stored on the users
    * computer as a result of him wanting to be remembered, and also
    * unsets session variables and demotes his user level to guest.
    */
   function logout(){
      //The database connection
      global $database;  

      // NULL our cookies and mark them as expired (set expiration time to the past)
      // the browser will clean them up on its own
      if(isset($_COOKIE['cookname']) && isset($_COOKIE['cookid'])){
         setcookie("cookname", NULL, time()-9999);
         setcookie("cookid",   NULL, time()-9999);
      }

      // Unset our PHP session variables 
      unset($_SESSION['username']);
      unset($_SESSION['userid']);

      // Mark the logout in this UserSession instance
      $this->logged_in = false;
      
      // Move from active user to active guest
      $database->removeActiveUser($this->username);
      $database->addActiveGuest($_SERVER['REMOTE_ADDR'], time());
      
      // Mark user as a guest
      $this->username  = GUEST_NAME;
   }
};

?>
