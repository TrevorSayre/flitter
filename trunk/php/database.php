<?php
/**
 * Database.php
 * 
 * The Database class is meant to simplify the task of accessing
 * information from the website's database.
 *
 */

class MySQLDB
{
   public $connection;         //The MySQL database connection
   public $last_query;         //Last executed query
   public $error_msg;	       //Last Error Code

   /* Class constructor */
   function MySQLDB($server,$user,$password,$database){
      /* Make connection to database */
      $this->connection = mysql_connect($server, $user, $password) or die(mysql_error());
      mysql_select_db($database, $this->connection) or die(mysql_error());
   }

   /**
    * query - Performs the given query on the database and
    * returns the result, which may be false, true or a
    * resource identifier.
    */
   function query($query){
      $result = mysql_query($query, $this->connection);
      $this->last_query = $query;
      $this->error_msg = mysql_error();
      if(!$result) {
	die("Query Failed: $query<br/><br/>Error: ".mysql_error()."<br/>");
      }
      return $result;
   }

};

?>
