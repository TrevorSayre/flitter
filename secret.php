<?php
include("auth/include/session.php");
// is the user not logged in?
if(!$session->logged_in){
  // set the referrer for redirection from future pages
  $_SESSION['login_ref'] = '..'.$_SERVER['REQUEST_URI'];
  // throw this user to a login page, they shouldn't be here
  header("Location: auth/login.php");
}
// the user is logged in
else {
// set the session variables to something more useful
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
<a href="index.php">Index Page</a><br />
<a href="auth/logout.php">Logout</a>
</body>
</html>
<?php } ?>