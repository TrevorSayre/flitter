<?php
  require_once 'php/user_session.php';
  $user = startUserSession();  
  //Registered user went the wrong way, send user home
  if($user->get_id() != -1) {
    header('Location: http://flitter/show_session.php');
  }
  
  session_destroy();
  session_start();
?>

<form method="POST" action="php/native_login.php">
  Email:   <input type="textbox" name="email" /><br/>
  Password:<input type="textbox" name="password" /><br/>
  <input type="submit" value="submit" />
</form>