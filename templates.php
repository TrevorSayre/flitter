<?php

//This function checks a template file for errors
//Includes it if everything checks out
//Takes template_name -> string with the relative path of the template file
//      template -> an array (name => value) of variables for template usage
//      show_warnings if they want

class Templater {
  public  $dump_warnings;
  private $warnings;
  private $scripts;
  private $styles;
  
  function __construct() {
    $this->dump_warnings = true;
    $this->warnings = array();
    $this->scripts = array();
    $this->styles = array();
  }
  
  //Get the path of the current file in pretty form for viewing
  private function get_pretty_path() {
    $path = dirname($_SERVER['PHP_SELF']);
    if (substr($path,-1)=='\\') $path = substr($path,0,count($path)-1);
    return $path;
  }
  
  public function show_warnings() {
    //If they want to see the warnings then show them here
      echo "<b>Showing ".count($this->warnings)." Warnings:</br>".implode("<br/>\n",$this->warnings)."<br/></b>";
  }
  
  public function load_template( $label, $template ) {
  
    //Make sure they supplied a template name
    if( !array_key_exists('name',$template) ) {
      if ($this->dump_warnings) $this->show_warnings();
      die( "<b>Template Name was not supplied</b><br/><br/>" );
    }
    
    //If the file exists, get the contents so we can check it first
    if(file_exists($template['name']))
      $contents = file_get_contents($template['name']);
    else {
      if ($this->dump_warnings) $this->show_warnings();
      die( "<b>".$this->get_pretty_path()."/".$template['name']." could not be located for $label</b><br/><br/>" );
    }
   
    //Find all the variable references and make sure those variables exist
    preg_match_all('/\$template\[["\'](\w+)["\']\]/s',$contents,$matches);
    foreach($matches[1] as $match) {
      //If the a variable required by the template is missing
      //Create an appropriate warning and push it on the stack
      if( !array_key_exists($match,$template) )
        array_push($this->warnings,"\"$match\" variable value was not supplied to $label");
    } 
      
    preg_match('/<!\-\-REQUIRED_FILES(.*?)\-\->/s',$contents,$matches);
    preg_match_all("/(stylesheet|javascript) ([\w\/\.\-]+)/s",$matches[1],$matches);
    
    for($i=0; $i<count($matches[0]); $i++) {
      switch($matches[1][$i]) {
        case 'javascript':$filename='js/'.$matches[2][$i].'.js';break;
        case 'stylesheet':$filename='css/'.$matches[2][$i].'.css';break;
      }
      if( file_exists($filename) ) array_push( $this->req_files, $filename );
      else array_push($this->warnings,"\"$filename\" required file cannot be found for $label");
    }
    
    //Return the output from evaluating the template
    ob_start();
    eval("?".">".file_get_contents($template['name']));
    $ret = ob_get_contents();
    ob_clean();
    return $ret;
    //This throws the file contents into our page after we've checked everything out
    //include $template['name'];
  }
  
  public function add_link($file,$type) {
    if( file_exists($file) ) {
      if( $type == "js") 
        array_push( $this->scripts, $file );
      else if ($type == "css")
        array_push( $this->styles, $file );
    }
    else
      array_push($this->warnings,"\"$filename\" required file cannot be found");
  }
  
  public function linking_code() {
    foreach($this->styles as $file)
      echo "<link href=\"$file\" type=\"text/css\" rel=\"stylesheet\" />\n";
    foreach($this->scripts as $file)
      echo "<script src=\"$file\" type=\"text/javascript\"></script>\n";
  }
  
};

$templater = new Templater();
$templater->warnings_on = true;
?>
