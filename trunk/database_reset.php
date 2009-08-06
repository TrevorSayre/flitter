<?php
  require 'php/flitter/flitter_library.php';

  $flitter = new FlitterLibrary();

  //Reset the DB as needed
  $tables = array('twitter_accounts','connections','events');
  foreach($tables as $table) {
    $flitter->reset_table($table);
    echo "Reset table $table<br/>";
  }
  echo "<br/><br/>";
  echo "Successful Completion";

?>