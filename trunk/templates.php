<?php
/** The Templater Class
 *  
 *  Used to string together different template files. 
 *   
 *  To use, create an instance of the templater:
 *    $templater = new Templater();
 *   
 *  Then, *before* the HTML doc starts load up the templates
 *  That you would like to use like below
 *    $section_header = $templater->load_template(  "section_header",
                              array(  'name' => 'section_header.tmpl',
                                      'heading' => 'What is Flitter?',
                                      'sublinks' => array( 'Sublink1' => '#',
                                                           'Sublink2' => '#',
                                                           'Sublink3' => '#' ),
                                      'sublink_class' => 'section_nav_link') );
 *  The second arguement to load template is an array of template variables
 *  
 *  The name variable is reserved for the filename of the template and must
 *  be included in the template arguements: 'name' => 'section_header.tmpl'
 *  
 *  All other fields should map to values used in the templates themselves  
 */
class Templater {

  //This flag determines whether warnings are dumped out when
  //templater encounters a fatal error (such as a missing file)
  public  $dump_warnings;
  
  //Holds the generated warnings until a dump is requested
  private $warnings;
  
  //These arrays hold the script and style links for the html header
  //This storage is required so they can be dumped out before the 
  //display code is sent to the broswer
  private $scripts;
  private $styles;
  
  /**
   *  Constructor to provide default values for the class
   */     
  function __construct() {
    $this->dump_warnings = true;
    $this->warnings = array();
    $this->scripts = array();
    $this->styles = array();
  }
  
  //Get the path of the current file in pretty form for viewing
  //It just has an akward backslash sometimes that I want to get rid of
  private function get_pretty_path() {
    $path = dirname($_SERVER['PHP_SELF']);
    if (substr($path,-1)=='\\') $path = substr($path,0,count($path)-1);
    return $path;
  }
  
  //This function allows a user to request a warning dump
  public function show_warnings() {
      echo "<b>Showing ".count($this->warnings)." Warnings:</br>\n".implode("<br/>\n",$this->warnings)."<br/>\n</b>";
  }
  
  /**This function loads a template file and returns its <HTML> output
  * $label is used only for debugging purposes to differentiate different
  * instances of the same template in a file (it might be useful)
  *
  * $template is an array of variables for the template and must include
  * 'name' => template_file_name so that the function knows where to find
  * the template you want it to load.
  */       
  public function load_template( $label, $template, $base = false ) {
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
    
    /**
     *  Here we are setting up an output buffer to hold the output from the
     *  template file.  We then retreive the buffer contents and clear to so
     *  it does not get sent to the server until we wish for it to.
     */         
    foreach($GLOBALS as $key => $value) { global $$key; }
    if($base) ob_start();
    eval(" global \$templater; ?".">".file_get_contents($template['name']));
    #include $template['name'];
    if($base) $ret = ob_get_contents();
    if($base) ob_clean();
    return $ret;
  }
  
  /**
   *  Use this function in template files to add css and js links
   *  into the header section of the main template.  This output is
   *  not automatic.  Use the linking_code() function in the header
   *  to output the code you need for linking.         
   */     
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
  
  /**
   *  This function will dump out the code needed to link all of
   *  the files that embedded templates have said that they require   
   */     
  public function linking_code() {
    foreach($this->styles as $file)
      echo "<link href=\"$file\" type=\"text/css\" rel=\"stylesheet\" />\n";
    foreach($this->scripts as $file)
      echo "<script src=\"$file\" type=\"text/javascript\"></script>\n";
  }
};

?>
