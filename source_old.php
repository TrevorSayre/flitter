<?php
        // If this file is a PHP file go in here
        // PHP files can contain bits of other languages in their code
        // Must split the doc apart to highlight pieces correctly
        if (substr($file,strpos($file,'.')) == '.php') {
          // Check to make sure they are no accessing a protected file
          // We're denying access to our constants (db username and pass)
        	if(strpos($file,"constants") === FALSE) {
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
            
            //echo "Starting the splitter<br/>";
            while( preg_match($match_string,$code,$matches) ) {
            
              // If the code segement found has preceding code
              // And all that code is not white space
              if( $matches[1]!="" && !preg_match('/^\s*$/', $matches[1]) ) {
                  //Highlight the code as HTML and add it to the output
                  $htmlcode = "<CODETOHIGHLIGHT>$matches[1]</CODETOHIGHLIGHT>";
                  $finalcode .= $hl_HTML->highlight($htmlcode);
              }
              /*
              for($i=0; $i<strlen($matches[2]); $i++)
                echo "$matches[2] ";
               */ 
              //Add our hack tags, w00t
              $matches[2] = "<CODETOHIGHLIGHT>$matches[2]</CODETOHIGHLIGHT>";
              /*
              for($i=0; $i<strlen($matches[2]); $i++)
                echo $matches[2][$i]." ";
              */
              if( preg_match( '/<\?php/',$matches[2] ) ) {
                $finalcode .= $hl_PHP->highlight($matches[2]);
              }
              else if( preg_match( '/<script>/',$matches[2] ) ) {
                $finalcode .= $hl_JS->highlight($matches[2]);
              }
              else if( preg_match( '/<style>/',$matches[2] ) ) {
                $finalcode .= $hl_CSS->highlight($matches[2]);
              }
              
              // Reset code to be whats left over for further processing
              $code = $matches[6];              
            }
            
            // If there is anything left over,its HTML
            // Highlight and add it like the rest
            if(strlen($code)>0) {
              $code = "<CODETOHIGHLIGHT>$code</CODETOHIGHLIGHT>";
              $finalcode .= $hl_HTML->highlight($code);
            }
            
            //Remove Needless </pre><pre> tags from highlights
            $match_string = '/<\/pre><\/div><div class="hl-main"><pre>/';
            $finalcode = preg_replace($match_string,'',$finalcode);
            
            //Remove our hack tags at the end
            //<span class="hl-brackets">&lt;/</span><span class="hl-reserved">CODETOHIGHLIGHT</span><span class="hl-brackets">&gt;</span>
            $match_string  = '/<span class="hl-brackets">&lt;\/?<\/span>';
            $match_string .= '<span class="hl-reserved">\/?CODETOHIGHLIGHT<\/span>'; 
            $match_string .= '<span class="hl-brackets">&gt;<\/span>/';
            $finalcode = preg_replace($match_string,'',$finalcode);
            
            //Remove the other version of hack tags at the end
            $match_string  = '/<span class="hl-(code|default)">(\s*)';
            $match_string .= '&lt;\/?CODETOHIGHLIGHT&gt;<\/span>/';
            $finalcode = preg_replace($match_string,'$2',$finalcode);
            
            /*
            //Removing the extra < we used as a hack to keep the EOL chars
            $match_string = '/<span class="hl-brackets">&lt;<\/span>';
            $match_string .= '<span class="hl-inlinetags">&lt;\?php<\/span>/';
            $replace_string = '<span class="hl-inlinetags">&lt;?php</span>';
            $finalcode = preg_replace($match_string,$replace_string,$finalcode);

            //Removing the ENDOFLIVECODE tag used as end of file EOL hack
            $match_string = '/<span class="hl-brackets">&lt;<\/span>';
            $match_string .= '<span class="hl-reserved">ENDOFLIVECODE<\/span>';
            $match_string .= '<span class="hl-brackets">&gt;<\/span>/';
            $finalcode = preg_replace($match_string,'',$finalcode);
            */
            
            //Make all of the document links in " " live
            $match_string = '/(<span class="hl-quotes">(&quot;|\')<\/span>)';
            $match_string .= '(<span class="hl-string">([\.\-\w\/]+\.(php|js|css|html))';
            $match_string .= '<\/span>)(<span class="hl-quotes">(&quot;|\')<\/span>)/';
            $replace_string = '$1<a href="source.php?file='.$dirPath.'/$4">$3</a>$6';
            $finalcode = preg_replace($match_string,$replace_string,$finalcode);
            
            $match_string = '/((src|href)=&quot;)([\.\w\/]+\.(php|js|css|html))(&quot;)/';
            $replace_string = '$1<a href="source.php?file='.$dirPath.'/$3">$3</a>$5';
            $finalcode = preg_replace($match_string,$replace_string,$finalcode);
 
            //echo "Outputing final code<br/>";
            echo $finalcode;
          }  
        	else echo "<img src=\"../images/gibbon_sticker.png\" alt=\"Angry Gibbon says, 'This page is forbidden!'\"><br />403 FORBIDDEN";
        }
        if (substr($file,strpos($file,'.')) == '.js') {
            $hl =& Text_Highlighter::factory("JAVASCRIPT");   
            echo $hl->highlight($code);
        }
        if (substr($file,strpos($file,'.')) == '.css') {
            $hl =& Text_Highlighter::factory("CSS");   
            echo $hl->highlight($code);
        }
?>
