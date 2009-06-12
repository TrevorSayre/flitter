<?php

//File for our template system
function load_template( $template_name, $template = array(), $show_warnings = true ) {
  
  $warnings = array();
  $vars = array();
  
  if(file_exists($template_name))
    $contents = file_get_contents($template_name);
  else {
    $path = dirname($_SERVER['PHP_SELF']);
    if (substr($path,-1)=='\\') $path = substr($path,0,count($path)-1);
    die( "<b>$path/$template_name could not be located</b><br/><br/>" );
  }  
  
  include $template_name;
/*
  //Record all expected replacements
  preg_match_all('/\$__(\w+)__/s',$contents,$matches);
  foreach($matches[1] as $match) {
    array_key_exists($match,$vars) ? $vars[$match]++ : $vars[$match]=1;
  }
  
  //Go through and do the replacements
  //Subtract from var count each time its found
  foreach($template_vars as $key => $value) {
    $contents = str_replace('$__'.$key.'__',$value,$contents,$count);
    if($count==0)
      array_push($warnings,"$key is not a variable in this template");  
    unset($vars[$key]); //Already set warning, remove variable
  }
  
  //Make sure all template variables were given
  foreach($vars as $key => $value)
    array_push($warnings,"Template is missing $key variable value");
  
  preg_match_all('/%__(\w+)__func(.*?)%__end_func__/s');
  for($i=0; $i<count($matches[1]); $i++) {
    switch($matches[1][$i]) {
      case 'load_template':
        
        break;
      default:
        array_push($warnings,"Template Engine does not support the ".$matches[2][$i]." function.");
        break;
    }
  }
  
  
  
  
  
  
  if($show_warnings && count($warnings)!=0)
    die( "<b>".implode("<br/>\n",$warnings)."</br><br/></b>" );
  
  echo $contents;
*/
}

?>
