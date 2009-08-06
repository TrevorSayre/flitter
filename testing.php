<?php
  require_once 'php/flitter/flitter_library.php';

  $flitter = new FlitterLibrary();
  
  //Reset the DB as needed
  $tables = array('users','twitter_accounts','connections','events');
  foreach($tables as $table) {
    $flitter->reset_table($table);
    echo "Reset table $table<br/>";
  }
  echo "<br/><br/>";


  //Add New users,accounts,and events
  $user_names = array('GraylinKim','Flitterto','TrevorSayre');
  $user_ids = array();
  foreach($user_names as $user_name) {
    $id = $flitter->addUser( array('user_name'=>$user_name) );
    $user_ids[$user_name] = $id;
    echo "Added user $user_name with $id<br/>";
  }
  echo "<br/><br/>";
  $twitter_ids = array(120938409,1928938948,12093409283);
  foreach($twitter_ids as $twitter_id) {
    $flitter->addNetworkAccount('twitter',$twitter_id,array('oauth_key'=>'random_stuff','oauth_secret'=>'secret_stuff'));
    echo "Added twitter account $twitter_id<br/>";
  }
  echo "<br/><br/>";
  $event_titles = array('EventA','NYSC Hard','Excellante','Muy Bueno');
  $event_ids = array();
  foreach($event_titles as $event_title) {
    $id = $flitter->addEvent($event_title,time(),time()+3600,'Right Here','This is a universally excellent testing event');
    $event_ids[$event_title] = $id;
    echo "Added event $event_title with id $id<br/>";
  }
  echo "<br/><br/>";

  //Adding connections and commitments
  foreach($user_ids as $user_name => $user_id) {
    foreach($twitter_ids as $twitter_id) {
      $flitter->addUserNetworkAccountConnection($user_id,'twitter',$twitter_id);
      echo "Connected $user_name to twitter account $twitter_id<br/>";
    }
  }
  echo "<br/><br/>";
  $type = 'attendee';
  foreach($user_ids as $user_name => $user_id) {
    foreach($event_ids as $event_name => $event_id) {
      $flitter->addUserEventCommitment($user_id,$event_id,$type);
      echo "Commited $user_name to $event_name as a(n) $type<br/>";
    }
  }
  echo "<br/><br/>";


  foreach($user_ids as $user_name => $user_id) {
    $accounts = $flitter->getUserNetworkAccounts($user_id,'twitter');
    echo "Selected ".count($accounts)." accounts for $user_name:<br/>";
    foreach($accounts as $account) {
      echo ' --> Account ',$account['acct_id'],' from twitter network selected<br/>';
    }
  }
  echo "<br/><br/>";
  echo "Test Finished";
?>