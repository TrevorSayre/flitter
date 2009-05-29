<?php
  require_once("Text/Highlighter.php");
  $code = file_get_contents($_GET['file']);
  $code .= "<ENDOFLIVECODE>";
  $url = explode('=',$_GET['file']);
  $fname = $url[1];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" 
"http://www.w3.org/TR/html4/strict.dtd"> 
<html>
<head> 
<title><?php echo $_GET['file']; ?></title> 
<link href="css/highlighter.css" type="text/css" rel="stylesheet" />
<link href="css/source.css" type="text/css" rel="stylesheet" />
<script>
</script>
</head> 

<body>
  <div id="container">
    <div id="header">
      <div id="feedback_button" class="header_button">Feedback</div>
      <div id="flip_over_button" class="header_button">Flip Over</div>
      <h1><?php echo $_GET['file']; ?></h1>
    </div>
    <div id="left_col">
      This is some stuff in the right column<br/><br/>
      More and more stuff!!
    </div>
    <div id="line_numbers_col">
      <?php
        $num_lines = count(file($_GET['file']));
        echo "<pre>";
        for($i=1; $i<=$num_lines; $i++)
          echo $i.PHP_EOL;
        echo "</pre>";
      ?>
    </div>
    <div id="code_col">
      <?php
        // If this file is a PHP file go in here
        // PHP files can contain bits of other languages in their code
        // Must split the doc apart to highlight pieces correctly
        if (substr($_GET['file'],strpos($_GET['file'],'.')) == '.php') {
          // Check to make sure they are no accessing a protected file
          // We're denying access to our constants (db username and pass)
        	if(strpos($_GET['file'],"constants") === FALSE) {
            //Creating our various highlighters here   
            $hl_HTML =& Text_Highlighter::factory("HTML");
            $hl_PHP =& Text_Highlighter::factory("PHP");
            $hl_JS =& Text_Highlighter::factory("JAVASCRIPT");
            $hl_CSS =& Text_Highlighter::factory("CSS");
            
            //We'll use $finalcode to build or page output
            $finalcode = "";
            
            //Match anything preceding our starting tags, it is HTML code
            // The .* gets everything, the ? keeps it from being greedy
            // This way it won't eat up the whole code piece
            // I've moved the opening < here as a hack
            // The ending \n char doesn't get captured so make it not the end
            $match_string = '/(.*?<)(';
            // Match <\?php and anything between it and the first \?\>
            // ? / are special characters so we need to \ them
            // Backslash the > as well to prevent forming php close tag
            // ' *?\n?' grabs all spaces non greedy up till a option \n char
            // used to remove endlines to prevent <pre> tag problems
            // The ( ) pair captures the code for later highlighting
            $match_string .= '(\?php.*?\?\> *?\n?)';
            // Match the script and style tags similar as above
            $match_string .= '|(script>.*?<\/script> *?\n?)';
            $match_string .= '|(style>.*?<\/style> *?\n?)';
            // Grab everything else for further processing.
            // /s allows multi line matching 
            $match_string .= ')(.*)/s';
            
            //echo "Starting the splitter<br/>";
            while( preg_match($match_string,$code,$matches) ) {
            
              // If the code segement found has preceding code
              // And all that code is not white space
              if( $matches[1]!="" && !preg_match('/^\s*$/', $matches[1]) ) {
                  //Highlight the code as HTML and add it to the output
                  /*
                  for($i=0; $i<strlen($matches[1]); $i++) {
                    if ($matches[1][$i]=='\n') { echo '\n'."<br/>";}
                    else echo $matches[1][$i];
                  }
                  echo "<br/><br/>";
                  */
                  $finalcode .= $hl_HTML->highlight($matches[1]);//substr($matches[1],0,-1));
                  //echo "The Second to last charactor is : ".$matches[1][-2]." so yeah..";
                  if( $matches[1][-2]==PHP_EOL )
                    $finalcode .= "<br/>";
                  //if( preg_match( '/\n$/s', $matches[1]) )
                  //  $finalcode .= "<br/>";
                  /*
                  for($i=0; $i<strlen($matches[1]); $i++)
                    echo $matches[1][$i]." ";
                  echo "<br/><br/>";
                  if( preg_match( '/'.$matches[1].'[\n]'.$matches[2].'/s', $code) )
                    $finalcode .= "<br/>";
                  /*
                  for($i=0; $i<strlen($matches[1]); $i++) {
                    if($matches[1][$i]=='\n')
                      echo "<br/>Found EOL<br/>";
                    else
                      echo $matches[1][$i]." ";
                  }
                  echo "<br/><br/>";
                  */
              }
              // Determine the type of code we captured
              // Highlight it accordingly and add it to output
              $matches[2] = "<".$matches[2];
              
              if( preg_match( '/<\?php/',$matches[2] ) ) {
                $finalcode .= $hl_PHP->highlight($matches[2]);
                /*for($i=0; $i<strlen($matches[2]); $i++) {
                    echo $matches[2][$i]." ";
                }*/
              }
              else if( preg_match( '/<script>/',$matches[2] ) )
                $finalcode .= $hl_JS->highlight($matches[2]);
              else if( preg_match( '/<style>/',$matches[2] ) )
                $finalcode .= $hl_CSS->highlight($matches[2]);
              
              // Reset code to be whats left over for further processing
              $code = $matches[6];              
            }
            
            // If there is anything left over,its HTML
            // Highlight and add it like the rest
            if(strlen($code)>0)
              $finalcode .= $hl_HTML->highlight($code);

            //Remove Needless </pre><pre> tags from the document
            $match_string = '/<\/pre><\/div><div class="hl-main"><pre>/';
            $finalcode = preg_replace($match_string,'',$finalcode);

            //Removing the extra < we used as a hack to keep the EOL chars
            $match_string = '/<span class="hl-brackets">&lt;<\/span>';
            $match_string .= '<span class="hl-inlinetags">&lt;\?php<\/span>/';
            $replace_string = '<span class="hl-inlinetags">&lt;?php</span>';
            $finalcode = preg_replace($match_string,$replace_string,$finalcode);

            $match_string = '/<span class="hl-brackets">&lt;<\/span>';
            $match_string .= '<span class="hl-reserved">ENDOFLIVECODE<\/span>';
            $match_string .= '<span class="hl-brackets">&gt;<\/span>/';
            $finalcode = preg_replace($match_string,'',$finalcode);

            $match_string = '/(<span class="hl-quotes">(&quot;|\')<\/span>)';
            $match_string .= '(<span class="hl-string">([\.\w\/]+\.(php|js|css|html))';
            $match_string .= '<\/span>)(<span class="hl-quotes">(&quot;|\')<\/span>)/';
            $replace_string = '$1<a href="source.php?file=$4">$3</a>$6';

            $str = '<span class="hl-quotes">&quot;</span><span class="hl-string">Hi/Bye.js</span><span class="hl-quotes">&quot;</span>';
            $finalcode = preg_replace($match_string,$replace_string,$finalcode);
 
            //echo "Outputing final code<br/>";
            echo $finalcode;
          }  
        	else echo "<img src=\"../images/gibbon_sticker.png\" alt=\"Angry Gibbon says, 'This page is forbidden!'\"><br />403 FORBIDDEN";
        }
        if (substr($_GET['file'],strpos($_GET['file'],'.')) == '.js') {
            $hl =& Text_Highlighter::factory("JAVASCRIPT");   
            echo $hl->highlight($code);
        }
        if (substr($_GET['file'],strpos($_GET['file'],'.')) == '.css') {
            $hl =& Text_Highlighter::factory("CSS");   
            echo $hl->highlight($code);
        }
        
      ?>
    </div>
    <div id="footer"></div>
  </div>
</body>
</html>





