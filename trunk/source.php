<?php

  require_once "geshi.php";
  
  function get_files($dir) {
    $files = scandir($dir);
    $file_structure = array();
    foreach($files as $file) {
      if($file!='.' && $file!='..' && $file!='.svn' && $file!='images') {
        $path = "$dir/$file";
        if( is_dir($path)=='dir')
          $file_structure[$file] = get_files($path);
        else
          $file_structure[$file] = $file;
      }
    }
    return $file_structure;
  }
  
  function tree_view_list($file_struct,$id_it=false, $path='.') {
    if($id_it == true)  $ret = "<ul id=\"browser\">";
    else                $ret = "<ul>";
    foreach($file_struct as $name => $branch ) {
      if( is_array($branch) )
        $ret .= "<li><span class=\"folder\">$name</span>".tree_view_list($branch,false,$path."/".$name);
      else
        $ret .= "<li><span class=\"file\"><a class=\"file_link\" href=\"source.php?file=$path/$name\">$name</a></span></li>";
    }
    $ret .= "</ul>";
    return $ret;
  }
  
  
  /** Need to really do this differently
   *  RegEx is waaaaay overkill here
   */
  $file = "";
  if( preg_match('/(.*?)(\w.*)/',$_GET['file'],$matches) )
    $file = $matches[2];
  $code = file_get_contents($file);
  $dirPath = "";
  if( preg_match('/(.*?)(\w.*)/',dirname($file),$matches) )
    $dirPath = $matches[2];  
  
  $finalcode = "";
  $geshi = new GeSHi($code,'php');
  $geshi->enable_strict_mode(GESHI_ALWAYS);
  $geshi->enable_classes();
  $geshi->set_header_type(GESHI_HEADER_NONE);
  
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" 
"http://www.w3.org/TR/html4/strict.dtd"> 
<html>

  <head>
    <title><?php echo $file; ?></title> 
    
    <link type="text/css" rel="stylesheet" href="css/highlighter.css" />
    <link type="text/css" rel="stylesheet" href="css/jquery.treeview.css" />
    <link type="text/css" rel="stylesheet" href="css/source.css" />
    
    <script src="js/jquery-1.3.2.js" type="text/javascript"></script>
    <script src="js/jquery.cookie.js" type="text/javascript"></script>
    <script src="js/jquery.treeview.js" type="text/javascript"></script>
    <script src="js/source.js" type="text/javascript"></script>
  </head> 

<body>
    <div id="header">
      <div id="feedback_button" class="header_button">Feedback</div>
      <a href="#" class="header_link"><div id="flip_over_button" class="header_button">Flip Over</div></a>
      <h1 id="filepath"><?php echo $file; ?></h1>
    </div>
    <div id="left_col">
      <?php echo tree_view_list(get_files('.'),true); ?>
    </div>
    <div id="code_col">
      <?php      
        
        //Match anything preceding our starting tags, it is HTML code
        // The .* gets everything, the ? keeps it from being greedy
        // This way it won't eat up the whole code piece
        // I've moved the opening < here as a hack
        // The ending \n char doesn't get captured so make it not the end
        $match_string = '/(.*?)(';
        // Match <\?php and anything between it and the first \?\>
        // ? / are special characters so we need to \ them
        // Backslash the > as well to prevent forming php close tag
        // ' *?\n?' grabs all spaces non greedy up till a option \n char
        // used to remove endlines to prevent <pre> tag problems
        // The ( ) pair captures the code for later highlighting
        $match_string .= '(<\?php.*?\?\> *?\n?)';
        // Match the script and style tags similar as above
        $match_string .= '|(<script>.*?<\/script> *?\n?)';
        $match_string .= '|(<style>.*?<\/style> *?\n?)';
        // Grab everything else for further processing.
        // /s allows multi line matching 
        $match_string .= ')(.*)/s';
        
        $pinfo = pathinfo($_GET['file']);
        $ftype = $pinfo['extension'];

        if($ftype == 'js') {
          $geshi->set_language('javascript');
          $finalcode = $geshi->parse_code();
        }
        
        else if ($ftype == 'css') {
          $geshi->set_language('css');
          $finalcode = $geshi->parse_code();
        }
        else if( strlen($code) > 12499 ) {
          if( $ftype == 'php' ) {
            $geshi->set_language('php');
            $finalcode = $geshi->parse_code();
          }
          else if ( $ftype == 'html' ) {
            $geshi->set_language('html4strict');
            $finalcode = $geshi->parse_code();
          }
        }
        else {
        
          while( preg_match($match_string,$code,$matches) ) {
            // If the code segement found has preceding code
            // And all that code is not white space
            if( $matches[1]!="" ) {// && !preg_match('/^\s*$/', $matches[1]) ) {
                $geshi->set_source($matches[1]);
                $geshi->set_language('html4strict');
                $finalcode .= $geshi->parse_code();
                //echo "\nMatching HTML\n:";//$matches[1]\n\n";
            }
            
            if( preg_match( '/<\?php/',$matches[2] ) ) {
              $geshi->set_source($matches[2]);
              $geshi->set_language('php');
              $finalcode .= $geshi->parse_code();
              //echo "\nMatching PHP\n:";
            }
            else if( preg_match( '/<script>/',$matches[2] ) ) {
              $geshi->set_source($matches[2]);
              $geshi->set_language('javascript');
              $finalcode .= $geshi->parse_code();
              //echo "\nMatching Javascript\n:";//$matches[2]\n\n";
            }
            else if( preg_match( '/<style>/',$matches[2] ) ) {
              $geshi->set_source($matches[2]);
              $geshi->set_language('css');
              $finalcode .= $geshi->parse_code();
              //echo "\nMatching CSS\n:";//$matches[2]\n\n";
            }
            
            // Reset code to be whats left over for further processing
            $code = $matches[6];              
          }
          
          if(strlen($code)>0) {
            $geshi->set_source($code);
            $geshi->set_language('html4strict');
            $finalcode .= $geshi->parse_code();
            //echo "\nMatching Ending HTML\n:";//$code\n\n";
          }
          
        }
        
        $match_string = '/<\/pre>\s*<pre .*?'.'>/';
        $finalcode = preg_replace($match_string,'',$finalcode);

        //  <span style="color: #ff0000;">&quot;text/css&quot;</span>
        $match_string = '/(<span .*?'.'>&quot;)([\w\/\-\.:]+?\.(php|js|css|html))(&quot;<\/span>)/';
        $replace_string = '$1<a href="source.php?file='.$dirPath.'/$2">$2</a>$4';
        $finalcode = preg_replace($match_string,$replace_string,$finalcode);
        
        $lines = explode("\n",$finalcode);
        echo "<ol class=\"php\" id=\"code\">\n";
        foreach($lines as $line) {
          //echo "<li><pre>$line</pre></li>";
          echo "<li>$line</li>";
        }
        echo "</ol>\n";
        
      ?>
    </div>
</body>
</html>