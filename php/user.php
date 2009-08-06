<?php

class User {

  private $user_info;

  public function __construct($args = array()) {
    $this->user_info = new userInfo();
    $this->user_info->email = (isset($args['email'])) ? $args['email'] : NULL;
    $this->user_info->user_id = (isset($args['user_id'])) ? $args['user_id'] : NULL;
    $this->user_info->accounts = array();
  }

  public function get_email() { return $this->user_info->email; }
  public function get_id() { return $this->user_info->user_id; }
  public function get_info() { return $this->user_info; }

  public function set_name($name) { $this->user_info->user_name = $name; }
  public function set_id($id) { $this->user_info->user_id = $id; }

  public function addAccount($type, $values) {
    //Should probably add some sort of error checking here, maybe not    

    $this->user_info->setAr($values,'accounts',$type);
/*  echo "<b>Values:</b><br/>: ";
    print_r($values);
    echo "<br/><br/>";
    echo "<b>Accounts:</b><br/>: ";
    print_r($this->user_info->accounts);
    echo "<br/><br/>";*/
  }

  public function getNetworkAccount($network,$id) {
    foreach($this->user_info->accounts[$network] as $account) {
      if($account['twitter_id']==$id)
	return $account['twitter_id'];
    }
    return FALSE;
  }

  public function getNumAccounts( $type = NULL ) {
    //All Accounts 
    if($type == NULL) { 
      $count = 0;
      foreach($this->user_info->accounts as $acct_type) {
	$count += count($acct_type);
      }
      return $count;
    }
    if(array_key_exists($type, $this->user_info->accounts))
      return count($this->user_info->accounts[$type]);
    //No such account type
    return FALSE;
  }

  public function getAccounts($type, $index = NULL) {
    if(!array_key_exists($type, $this->user_info->accounts)) {
      return FALSE; //No such account type
    }
    if($index==NULL) {
      return $this->user_info->accounts[$type];
    }
    if( abs($index) < count($this->user_info->accounts[$type]))
      return $this->user_info->accounts[$type][$index];
  }

}

?>