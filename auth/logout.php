<?
/**
 * logout.php
 *
 * By visiting this page, a user will be logged out
 * if already logged in.
 *
 */
include("include/session.php");
if(!$session->logged_in){
  header("Location: login.php");
}
else {
header("Location: process.php");
}
?>