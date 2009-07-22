<?php 
// RFC 2821 (faqs.org/rfcs/rfc2821.html) dictates that new lines in SMTP 
// email must be denoted with CRLF (i.e. \r\n)
$eol = "\r\n";

// File for Attachment 
$f_name="../../letters/".$letter; 
$handle=fopen($f_name, 'rb'); 
$f_contents=fread($handle, filesize($f_name)); 
// Encode The Data For Transition using base64_encode(); 
$f_contents=chunk_split(base64_encode($f_contents));
$f_type=filetype($f_name); 
fclose($handle); 
// To E-mail Address 
$emailaddress="user@example.com"; 
// Message Subject 
$emailsubject="Heres an E-mail with a PDF".date("Y/m/d H:i:s"); 
// Message Body 
ob_start(); 
  require("emailbody.php"); // Pretty E-mail body
$body=ob_get_contents(); ob_end_clean(); 

// Common Headers 
$headers .= 'From: Jonny <jon@example.com>'.$eol; 
// These two to set reply address 
$headers .= 'Reply-To: Jonny <jon@example.com>'.$eol; 
$headers .= 'Return-Path: Jonny <jon@example.com>'.$eol;
// These two to help avoid spam-filters 
$headers .= "Message-ID: <".$now;
$headers .= " TheSystem@".$_SERVER['SERVER_NAME'].">".$eol; 
$headers .= "X-Mailer: PHP v".phpversion().$eol;
// Boundry for marking the split & Multitype Headers 
$mime_boundary=md5(time()); 
$headers .= 'MIME-Version: 1.0'.$eol; 
$headers .= "Content-Type: multipart/related;";
$headers .= "boundary=\"".$mime_boundary."\"".$eol; 
$msg = ""; 

// Attachment 
$msg .= "--".$mime_boundary.$eol; 
// To send MS Word, use 'msword' instead of 'pdf' 
$msg .= "Content-Type: application/pdf; name=\"".$letter."\"".$eol;
$msg .= "Content-Transfer-Encoding: base64".$eol; 
$msg .= "Content-Disposition: attachment;";
// Needs TWO end of lines !! IMPORTANT !!
$msg .= "filename=\"".$letter."\"".$eol.$eol;  
$msg .= $f_contents.$eol.$eol; 
// Setup for text OR HTML 
$msg .= "Content-Type: multipart/alternative".$eol; 

// Text Version 
$msg .= "--".$mime_boundary.$eol; 
$msg .= "Content-Type: text/plain; charset=iso-8859-1".$eol; 
$msg .= "Content-Transfer-Encoding: 8bit".$eol; 
$msg .= "This is a multi-part message in MIME format.".$eol; 
$msg .= "If you are reading this, please update your E-mail client.".$eol; 
$msg .= "+ + Text Only E-mail from Jonny + +".$eol.$eol; 

// HTML Version 
$msg .= "--".$mime_boundary.$eol; 
$msg .= "Content-Type: text/html; charset=iso-8859-1".$eol; 
$msg .= "Content-Transfer-Encoding: 8bit".$eol; 
$msg .= $body.$eol.$eol; 

// Finish with two eol's for better security. Injection Protection! 
$msg .= "--".$mime_boundary."--".$eol.$eol;

// Send the E-mail 
// The INI lines are to force the From Address to be used
ini_set(sendmail_from,'from@example.com');
  mail($emailaddress, $emailsubject, $msg, $headers); 
ini_restore(sendmail_from); 
?> 
