<?php

require_once 'php/user_session.php';

session_start();

echo "Session:<br/>";
print_r($_SESSION);
echo "<br/>";

$user = startUserSession();

$info = $user->get_info();

foreach($info->getFields() as $field) {
  if(is_array($info->$field)) {
    echo "<br/><b>$field:</b> ";
    print_r($info->$field);
  }
  else {
    echo "<br/><b>$field</b>: ".$info->$field;
  }
}

?>
<br/><br/>
<a href="http://flitter.to"> Go Home </a>