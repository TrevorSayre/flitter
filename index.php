<?php 
  require_once "php/templates.php";
  
  $templater = new Templater();
  $templater->app_root = "/flitter/";
  
  //Rest of paths are relative to the app root here
  $ext = dirname($_SERVER['SCRIPT_NAME']);
  if($ext=='\\') $ext='/';
  $templater->http_root = "http://".$_SERVER['SERVER_NAME'].$ext;
  $templater->tmpl_root = "tmpl/";
  $templater->css_root  = "css/";
  $templater->js_root   = "js/";
  $templater->php_root  = "php/";
  $templater->img_root  = "img/"
  //Default Page title, can be overridden by loaded template files
  $templater->page_title = "Flitter";
  
  //Load our layout into the body variable
  //3 Args - Name Relative to tmpl_root, args to pass, and use output buffers
  $body_tmpl_name = 'index';
  //Pass $_GET args through but allow for manual override
  $body_tmpl_args = array()+$_GET;
  $body = $templater->load_template( $body_tmpl_name,  $body_tmpl_args, "body", true );
  
  //Now that the page has been processed produce the header
  $head_tmpl_name = 'head';
  //Pass $_GET args through but allow for manual override
  $head_tmpl_args = array()+$_GET;
  $head = $templater->load_template(  $head_tmpl_name, array(), "head", true ); 
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <?=$head?> <!-- php shorthand for echo -->
  <?=$body?>
</html>