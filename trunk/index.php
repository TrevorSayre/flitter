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
  $templater->img_root  = "img/";
  $templater->page_title_prefix = "Flitter - ";
  //Default Page title, can be overridden by loaded template files
  $templater->page_title = "Coming Soon...";
  
  //Load our layout into the body variable
  $body_tmpl_args = array()+$_POST+$_GET;//Allows for pass through to $template variable
  $body = $templater->load_template( 'flitter.layout',  $body_tmpl_args, "flitter_layout", true );
  
  //Now that the page has been processed produce the header
  $head_tmpl_args = array()+$_POST+$_GET;//Allows for pass through to $template variable
  $head = $templater->load_template(  'head.tmpl', array(), "head", true ); 
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <?=$head?> <!-- php shorthand for echo -->
  <?=$body?>
</html>
