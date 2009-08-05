<?php 
  require_once "php/templater.php";

  $templater = new Templater();

//This should probably be pulled out of some kinda of config file
//But not right now

  //Base Root, everything is relative to this root
  $templater->set_app_root(".");

  //Set up Directories for linking
  //We can shortcut because our labels and folders match
  foreach(array('tmpl','img','js','css','php') as $type)
    $templater->set_directory($type,$type);  
  //HTTP is special, Set it here using the server name
  //Get current directory, fix it to forward slash (dirname is messed up)  
  $ext = dirname($_SERVER['SCRIPT_NAME']); if($ext=='\\') $ext='/';
  $templater->set_http_root('http://'.$_SERVER['SERVER_NAME'].$ext);

  
  $templater->set_layout("flitterv2");
  
  $templater->page_title_prefix = "Flitter - ";
  //Default Page title, can be overridden by loaded template files
  $templater->page_title = "Coming Soon...";


//Done with set up
//Now let it run!

  //Load our page body first, it well specify its dependencies
  $app_args = array();//Allows for pass through to $template variable
  $body = $templater->load_file('layout.tmpl',$app_args+$_POST+$_GET);
  
  //Now that dependencies have been loaded form the header
  $app_args = array();//Allows for pass through to $template variable
  $head = $templater->load_file('head.tmpl',$app_args+$_POST+$_GET);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <?=$head?> <!-- php shorthand for echo -->
  <?=$body?>
</html>