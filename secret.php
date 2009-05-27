<?php
include("auth/include/session.php");
if(!$session->logged_in){
  $_SESSION['login_ref'] = '../../..'.$_SERVER['REQUEST_URI'];
  header("Location: auth/login.php");
}
else {
$name = $session->username;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" 
"http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>Secret Page</title>
</head>
<body>
This page required you to login.  You're logged in.<br />
Welcome to Flitter, <strong><? echo $name; ?></strong>!<br />
<a href="auth/login.php">Login Info</a><br />
<a href="auth/logout.php">Logout</a>
</body>
</html>
<?php } ?>