<?php

include_once "Text/Highlighter.php";

if (substr($_GET['file'],strpos($_GET['file'],'.')) == '.phps') {
	//highlight_file($_GET['file']);
	if(strpos($_GET['file'],"constants") === FALSE) {
	  ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" 
"http://www.w3.org/TR/html4/strict.dtd"> 
<html>
<head> 
<title><?php echo $_GET['file']; ?></title> 
<script src="js/testing.js" type="text/javascript"></script> 
<link href="css/sample.css" type="text/css" rel="stylesheet" />

</head> 
<body>
<?php
    $code = file_get_contents(substr($_GET['file'],0,strpos($_GET['file'],'.')).".php");
    $hlPHP =& Text_Highlighter::factory("HTML");   
    echo $hlPHP->highlight($code);
    
    /*
    //<span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;include&nbsp;</span><span style="color: #DD0000">"include/eventDefinition.php"</span>
    //<span style="color: #007700">include&nbsp;</span><span style="color: #DD0000">"include/session.php"</span>
    //<span style="color: #007700">include&nbsp;</span><span style="color: #DD0000">"include/session.php"&nbsp;</span>
    $match_string   = '/(<span style="color: #007700">)((\w|;|&)*(<br \/>)*(\w|;|&)*)(include(&nbsp;)+<\/span><span style="color: #DD0000">)"([A-Za-z0-9_\/]+)\.php"(&nbsp;)*<\/span>/';
    $replace_string = '$2<a href="$8.phps">$1$6"$8.php"</a>$9';
    $code = preg_replace( $match_string, $replace_string, $code);
    echo $code;
    */
?>

</body>
</html>
    <?php
    
//    echo $code;
  }  
	else echo "<img src=\"../images/gibbon_sticker.png\" alt=\"Angry Gibbon says, 'This page is forbidden!'\"><br />403 FORBIDDEN";
}
if (substr($_GET['file'],strpos($_GET['file'],'.')) == '.js') {
  
}
?>