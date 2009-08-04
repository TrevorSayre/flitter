<?php
/* The Templater Class
 *  
 *  Used to string together different template files. 
 *   
 *  To use, create an instance of the templater:
 *    $templater = new Templater();
 *   
 *  Then, *before* the HTML doc starts load up the templates
 *  That you would like to use like below
 *    $section_header = $templater->load_template(  "section_header",
                           array( 'name' => 'section_header.tmpl',
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
  
  //Shortcuts for absolute linking of files
  public  $app_root;
  public  $http_root;
  public  $css_root;
  public  $js_root;
  public  $tmpl_root;
  public  $php_root;
  public  $img_root;
  
  //Used for setting the page title from pretty much anywhere
  public  $page_title_prefix;
  public  $page_title;
  public  $page_title_postfix;
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
    
    //Some basic defaults people will probably use.
    $this->http_root = $_SERVER['SERVER_NAME'];
    $this->doc_root = "/";
    $this->css_root = "js/";
    $this->js_root = "css/";
    $this->img_root = "img/";
    $this->tmpl_root = "tmpl/";
    $this->page_title = $_SERVER['SERVER_NAME'];
    $this->php_root = "php/";
    $this->page_title_prefix = "";
    $this->page_title_postfix = "";
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
      echo "<b>Showing ",count($this->warnings)," Warnings:<br/>\n",
            implode("<br/>\n",$this->warnings),"<br/>\n</b>";
  }
  
  /**This function loads a template file and returns its <HTML> output
  * $label is used only for debugging purposes to differentiate different
  * instances of the same template in a file (it might be useful)
  *
  * $template is an array of variables for the template and must include
  * 'name' => template_file_name so that the function knows where to find
  * the template you want it to load.
  */       
  public function load_template( $tmpl_name, $template, $label) {
    //Make sure they supplied a template name
    if( strlen($tmpl_name)==0 ) {
      if ($this->dump_warnings) $this->show_warnings();
      die( "\n\n<br/><br/><b>Fatal Error:<br/><br/>\n
            Template Name was not supplied for $label</b><br/><br/>" );
    }
    
    //Construct the correct template file path and name
    $name = $this->tmpl_root.$tmpl_name;
    
    //If the file exists, get the contents so we can check it first
    if(file_exists($name))
      $contents = file_get_contents($name);
    //Else dump out any warnings generated so far and output fatal error
    else {
      if ($this->dump_warnings) $this->show_warnings();
      die( "<b>".$name." could not be located for $label</b><br/><br/>" );
    }
   
    //Find all the variable references and make sure those variables exist
    preg_match_all('/\$template\[["\'](\w+)["\']\]/s',$contents,$matches);
    foreach($matches[1] as $match) {
      //If the a variable required by the template is missing
      //Create an appropriate warning and push it on the stack
      if( !array_key_exists($match,$template) )
        array_push($this->warnings,"'$match' variable value was not supplied to $label");
    } 

    /**
     *  Declare all globals used by the template script as found between the
     *  #USED_GLOBALS and END_GLOBALS# pgp tags     
     */              
    $globals = array( 'templater' );
    if(preg_match('/#USED_GLOBALS (.*)? END_GLOBALS#/',$contents,$matches))
      $globals = $globals+explode(',',$matches[1]);
    foreach($globals as $var) { global $$var; }
    /**
     *  Here we are setting up an output buffer to hold the output from the
     *  template file.  We then retreive the buffer contents and clear to so
     *  it does not get sent to the server until we wish for it to.
     */         
    ob_start();
      include $name;
      $ret = ob_get_contents();
    ob_end_clean();
    return $ret;
  }
  
  /**
   *  Use this function in template files to add css and js links
   *  into the header section of the main template.  This output is
   *  not automatic.  Use the linking_code() function in the header
   *  to output the code you need for linking.         
   */
  public function add_script($filename) {
    $file = $this->js_root.$filename;
    if( file_exists($file) )
        array_push( $this->scripts, $file);
    else
      array_push($this->warnings,"\"$filename\" required file cannot be found in $this->js_root");
  }
  
  public function add_style($filename) {
    $file = $this->css_root.$filename;
    if( file_exists($file) )
        array_push( $this->styles, $file );
    else
      array_push($this->warnings,"\"$filename\" required file cannot be found in $this->css_root");
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
  
  public function img_link($link) {
    return "src=\"$this->img_root$link\"";
  }
  public function http_link($link) {
    if(strpos($link,'http://')===false)
      return "href=\"$this->http_root$link\"";
    else
      return "href=\"$link\" target=\"_blank\"";
  }
  public function php_link($link) {
    foreach($GLOBALS as $key => $value) { global $$key; }
    return eval("require_once \"$this->php_root$link\";"); 
  }
  
  public function get_page_title () {
    return $this->page_title_prefix.$this->page_title.$this->page_title_postfix;
  }

};

?>
