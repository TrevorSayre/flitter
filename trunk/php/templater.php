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
  private  $app_root;

  //Used for setting the page title from pretty much anywhere
  public  $page_title_prefix;
  public  $page_title;
  public  $page_title_postfix;
  //Holds the generated warnings until a dump is requested
  private $warnings = array();
  
  //These arrays hold the script and style links for the html header
  //This storage is required so they can be dumped out before the 
  //display code is sent to the broswer
  private $js_paths = array();
  private $css_paths = array();
  
  private $roots = array();

  function __construct() {
    $this->dump_warnings = true;
    
    //Some basic defaults people will probably use.
    $this->page_title = $_SERVER['SERVER_NAME'];
    $this->page_title_prefix = "";
    $this->page_title_postfix = "";
  }
  
  public function set_app_root($root) { 
    if(file_exists($root))
      $this->roots['app'] = $root.'/'; 
    else
      die("Invalid relative path ($root) to app_root. Does not exist");
  }

  public function set_http_root($root) {
    $this->http_root = $root;
  }
  public function get_http_path($path) {
    if(strstr($path,'http://')===FALSE) { //Local
      if(isset($this->http_root))
	return $this->http_root.$path;
      else
	die('Cannot get_http_path with setting http_root');	
    }
    return $path;
  }

  public function set_directory($path,$label,$sub_dir_label=NULL) {
    if(isset($this->roots['app'])) {
      if($sub_dir_label==NULL)
	$path = $this->roots['app'].$path.'/';
      else
	$path = $this->roots[$sub_dir_label].$path.'/';
    }
    else
      die("Setting $label directory requires an app_root");

    if(file_exists($path))
      $this->roots[$label] = $path;
    else
      die("Invalid relative path ($path) to $label directory. Does not exist");
  }

  //Should handle everything
  public function get_path_str($file_name,$dir_label) {
    // Might need this stuff for php
    //    foreach($GLOBALS as $key => $value) { global $$key; }
    //    return eval("require_once \"$this->php_root$link\";"); 
    if(isset($this->roots[$dir_label]))
      return $this->roots[$dir_label].$file_name;
    else
      die("get_path: $dir_label has not been defined. Cannot link $file_name.");
  }

  public function set_layout($layout,$dir_label='tmpl') {
    if(isset($this->roots[$dir_label]))
      $path = $this->roots[$dir_label].$layout.'/';
    else
      die("Setting layout requires a tmpl_root");

    if(file_exists($path))
      $this->roots['layout'] = $path;
    else
      die("Layout ($layout) does not exist in $_dir_label (".$this->roots[$dir_label].")");
  }

 /**
  *This function loads a template file and returns its <HTML> output
  * $args is an array of variables for the template 
  */
  public function load_file( $file_name, $args, $dir_label='layout' ) {
    //Construct the path. layout must be previously set has been choosen
    if( !isset($this->roots[$dir_label]))
      die("load_body requires layout_root to be set");
    $file_name = $this->roots[$dir_label].$file_name;

    //If the file doesn't exist, dump warnings, exit with error
    if( !file_exists($file_name) ) {
      if ($this->dump_warnings) $this->show_warnings();
      die( "<b>".$file_name." could not be located</b><br/><br/>" );
    }
    $contents = file_get_contents($file_name);

    //Find all the variable references and make sure those variables exist
    preg_match_all('/\$template\[["\'](\w+)["\']\]/s',$contents,$matches);
    foreach($matches[1] as $match) {
      //If the a variable required by the template is missing
      //Create an appropriate warning and push it on the stack
      if( !array_key_exists($match,$args) )
        array_push($this->warnings,"'$match' variable value was not supplied to $label");
    } 

    // Declare all globals used by the template script as found between the
    //  #USED_GLOBALS and END_GLOBALS# pgp tags                  
    $globals = array( 'templater' ); //$templater is always global
    if(preg_match('/#USED_GLOBALS (.*)? END_GLOBALS#/',$contents,$matches))
      $globals = $globals+explode(',',$matches[1]);
    foreach($globals as $var) { global $$var; }
    
    //  Here we are setting up an output buffer to hold the output from the
    //  template file.  We then retreive the buffer contents and clear to so
    //  it does not get sent to the server until we wish for it to.
    ob_start();
      include $file_name;
      $ret = ob_get_contents();
    ob_end_clean();

    return $ret;
  }
  
  //This function allows a user to request a warning dump
  public function show_warnings() {
      echo "<b>Showing ",count($this->warnings)," Warnings:<br/>\n",
            implode("<br/>\n",$this->warnings),"<br/>\n</b>";
  }
  
  /**
   *  Use this function in template files to add css and js links
   *  into the header section of the main template.  This output is
   *  not automatic.  Use the linking_code() function in the header
   *  to output the code you need for linking.         
   */
  public function add_js($file_name,$dir_label='js') {
    if(isset($this->roots[$dir_label]))
      $file_name = $this->roots[$dir_label].$file_name;
    else
      die($dir_label.' directory has not been set up.');

    if( file_exists($file_name) )
        array_push( $this->js_paths, $file_name);
    else
      array_push($this->warnings,"\"$file_name\": required file cannot be found");
  }
  public function get_js_paths() { return $this->js_paths; }

  public function add_css($file_name,$dir_label='css') {
    if(isset($this->roots[$dir_label]))
      $file_name = $this->roots[$dir_label].$file_name;
    else
      die($dir_label.' directory has not been set up.');

    if( file_exists($file_name) )
      array_push( $this->css_paths, $file_name );
    else
      array_push($this->warnings,"\"$file_name\": required file cannot be found");
  }
  public function get_css_paths() { return $this->css_paths; }
  
  public function get_page_title () {
    return $this->page_title_prefix.$this->page_title.$this->page_title_postfix;
  }
};

?>
