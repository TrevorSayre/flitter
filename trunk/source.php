<?php

  //require_once("Text/Highlighter.php");
  require_once "geshi.php";
  
  $file = "";
  if( preg_match('/(.*?)(\w.*)/',$_GET['file'],$matches) )
    $file = $matches[2];
  $code = file_get_contents($file);
  $dirPath = "";
  if( preg_match('/(.*?)(\w.*)/',dirname($file),$matches) )
    $dirPath = $matches[2];  
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" 
"http://www.w3.org/TR/html4/strict.dtd"> 
<html>
<head> 
<title><?php echo $file; ?></title> 
<link href="css/highlighter.css" type="text/css" rel="stylesheet" />
<link href="css/source.css" type="text/css" rel="stylesheet" />
<script src="jquery-1.3.2.js" type="text/javascript"></script>
<script>
  $(document).ready( function() {
    alert("Page Loaded");
  });
</script>
</head> 

<body>
  <div id="container">
    <div id="header">
      <div id="feedback_button" class="header_button">Feedback</div>
      <a href="<?php ?>" class="div_button"><div id="flip_over_button" class="header_button">Flip Over</div></a>
      <h1><?php echo $file; ?></h1>
    </div>
    <div id="left_col">
      This is some stuff in the right column<br/><br/>
      More and more stuff!!
    </div>
    <div id="line_numbers_col">
      <?php
        $num_lines = count(file($file));
        echo "<pre>";
        for($i=1; $i<=$num_lines; $i++)
          echo $i.PHP_EOL;
        echo "</pre>";
      ?>
    </div>
    <div id="code_col">
      <?php
      
            $finalcode = "";
            $geshi = new GeSHi($code,'php');
            $geshi->enable_strict_mode(GESHI_ALWAYS);
            
            $match_string .= '/(<\?php.*?\?[>])/s';
            if(preg_match($match_string,$code,$matches) ){
              echo "\n\nSuccess!!!\n\n";
            }
            else
              echo "\n\nFail!!!\n\n";
            echo "\n\n";
            
            /*
            for($i=0; $i < 20; $i++)
              echo "$code[$i] ";
            echo "\n\n\nSome Middle Area Stuff\n\n\n";
            for($i=0; $i < -20; $i--)
              echo "$code[$i] ";
            echo "\n\n";
            */
            
            
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
            
            
            echo "\n\n\n\nSplitting and ripping the code apart\n\n";
            while( preg_match($match_string,$code,$matches) ) {
            
              // If the code segement found has preceding code
              // And all that code is not white space
              if( $matches[1]!="" ) {// && !preg_match('/^\s*$/', $matches[1]) ) {
                  $geshi->set_source($matches[1]);
                  $geshi->set_language('html4strict');
                  $finalcode .= $geshi->parse_code();
                  echo "\nMatching HTML\n:";//$matches[1]\n\n";
                  //echo "\n\n\nAltered HTML Code:\n".$geshi->parse_code()."\n\n\n";
              }
              
              if( preg_match( '/<\?php/',$matches[2] ) ) {
                $geshi->set_source($matches[2]);
                $geshi->set_language('php');
                $finalcode .= $geshi->parse_code();
                
                echo "\nMatching PHP\n:";
                /*
                for($i=0;$i<strlen($matches[2]);$i++) 
                  echo $matches[2][$i]." ";
                echo "\n\n";
                */
              }
              else if( preg_match( '/<script>/',$matches[2] ) ) {
                $geshi->set_source($matches[2]);
                $geshi->set_language('javascript');
                $finalcode .= $geshi->parse_code();
                echo "\nMatching Javascript\n:";//$matches[2]\n\n";
              }
              else if( preg_match( '/<style>/',$matches[2] ) ) {
                $geshi->set_source($matches[2]);
                $geshi->set_language('css');
                $finalcode .= $geshi->parse_code();
                echo "\nMatching CSS\n:";//$matches[2]\n\n";
              }
              
              // Reset code to be whats left over for further processing
              $code = $matches[6];              
            }
        
        if(strlen($code)>0) {
          $geshi->set_source($code);
          $geshi->set_language('html4strict');
          $finalcode .= $geshi->parse_code();
          echo "\nMatching Ending HTML\n:";//$code\n\n";
        }
        
        // </pre><pre class="php" style="font-family:monospace;">
        $match_string = '/<\/pre>\s*<pre .*?'.'>/';
        $finalcode = preg_replace($match_string,'',$finalcode);

        //  <span style="color: #ff0000;">&quot;text/css&quot;</span>
        $match_string = '/(<span .*?'.'>&quot;)([\w\/\-\.:]+?\.(php|js|css|html))(&quot;<\/span>)/';
        $replace_string = '$1<a href="source.php?file='.$dirPath.'/$2">$2</a>$4';
        $finalcode = preg_replace($match_string,$replace_string,$finalcode);
        
        echo $finalcode;
        
      ?>
    </div>
    <div id="footer"></div>
  </div>
</body>
</html>




