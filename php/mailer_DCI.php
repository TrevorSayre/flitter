<?php 

$mail = new eMail();

class eMail {

  public $eol;
  
  private $attachments;
  private $recipients;

  private $from;
  private $reply_to;
  private $return_path;
  private $subject;

  // RFC 2821 (faqs.org/rfcs/rfc2821.html) dictates that new lines in SMTP 
  // email must be denoted with CRLF (i.e. \r\n)
  public function __construct($eol = "\r\n") {
    $this->eol = $eol;
    $this->attachments = array();
    $this->recipients = array();
    $this->from = NULL;
    $this->reply_to = NULL;
    $this->return_path = NULL;
    $this->subject = "";
  }

  public function makeEmail( $email, $alias=NULL ) {
    //Still need to validate the emails and aliases here!!!
    //
    //

    if($alias==NULL)
      $recipient = "<$email>";
    else
      $recipient = "$alias <$email>";
  }

  public function add_recipient( $email, $alias=NULL ) {
    array_push($this->recipients,$this->makeEmail($email,$alias));
  }
  
  public setFrom($email, $alias=NULL) {
    $this->from = makeEmail($email,$alias);
  }
  public setSubject($string) {
    $this->subject = $string;
  }
  public setReplyTo($email, $alias=NULL) {
    $this->reply_to = makeEmail($email,$alias);
  }
  public setReturnPath($email, $alias=NULL) {
    $this->return_path = makeEmail($email,$alias);
  }

  public function send() {
    // Common Headers 
    $headers = 'From: '.$this->from.$this->eol;
    // Used for human replys. Use from if not set
    $reply = ($this->reply_to==NULL) ? $this->from : $this->reply_to;
    $headers .= 'Reply-To: '.$reply.$this->eol;
    // Used for automated replys. Use from if not set
    $return = ($this->return_path==NULL) ? $this->from : $this->return_path;
    $headers .= 'Return-Path: '.$return.$this->eol;
    // These two to help avoid spam-filters by marking the email as ours
    // and including the time to make sure its always a unique id
    $headers .= "Message-ID: <".time();
    $headers .= " TheSystem@".$_SERVER['SERVER_NAME'].">".$this->eol;
    //identifies software that sent the mail, microsoft only standard
    $headers .= "X-Mailer: PHP v".phpversion().$this->eol;
    // Boundry for marking the split & Multitype Headers
    $headers .= 'MIME-Version: 1.0'.$this->eol;
    $headers .= "Content-Type: multipart/related;";
    //Set the boundry that will be used to separate each peice
    $mime_boundary=md5(time());
    $headers .= "boundary=\"".$mime_boundary."\"".$this->eol;

    $msg = "";     //Initialize the message to empty
    //Loop through each attachment and output the mime info
    foreach($this->attachments as $attach) {
      // mark the start of a new mime
      $msg .= "--".$mime_boundary.$this->eol; 
      // identify the type of mime content
      $msg .= "Content-Type: application/".$attach['file_type']."; ";
      $msg .= "name=\"".$attach['file_name']."\"".$this->eol;
      $msg .= "Content-Transfer-Encoding: base64".$this->eol; 
      $msg .= "Content-Disposition: attachment;";
      $msg .= "filename=\"".$attach['file_name']."\"";
      // Needs TWO end of lines !! IMPORTANT !!
      $msg .= $this->eol.$this->eol;
      $msg .= $attach['file_contents'].$this->eol.$this->eol; 
    }

    // Setup the next part for text and HTML versions
    $msg .= "Content-Type: multipart/alternative".$this->eol;     


/* Should Possibly use this
ob_start(); 
  require("emailbody.php"); // Pretty E-mail body
$body=ob_get_contents(); ob_end_clean(); 
*/
//Need to figure out the text body somehow
    // Text Version 
    $msg .= "--".$mime_boundary.$eol; 
    $msg .= "Content-Type: text/plain; charset=iso-8859-1".$eol; 
    $msg .= "Content-Transfer-Encoding: 8bit".$eol; 
    $msg .= "This is a multi-part message in MIME format.".$eol; 
    $msg .= "If you are reading this, please update your E-mail client.".$eol; 
    $msg .= "+ + Text Only E-mail from Sender + +".$eol.$eol; 


//Need to figure out the HTML version
    // HTML Version 
    $msg .= "--".$mime_boundary.$eol; 
    $msg .= "Content-Type: text/html; charset=iso-8859-1".$eol; 
    $msg .= "Content-Transfer-Encoding: 8bit".$eol; 
    $msg .= $body.$eol.$eol; 

    // Finish with two eol's for better security. Injection Protection! 
    $msg .= "--".$mime_boundary."--".$eol.$eol;


    //Prepare the recipient email addresses
    if(count($this->recipients)==0) {
      //The message has to have atleast one recipient
    }
    //Collapse the emails into a comma separated list
    $to = implode(', ',$this->recipients);

    // Send the E-mail 
    // The INI lines are to force the From Address to be used
    //Can we still use an alias here?
    ini_set(sendmail_from,$this->from);

//Need to handle the email subject still
      if($this->subject == "") {
	//Warning, no subject set
      }
      mail($to, $this->subject, $msg, $headers); 
    ini_restore(sendmail_from); 
  }

  //Takes relative file path
  public addAttachment($file_path) {
    //User supplied filename checking
    if(is_file($file_path) === FALSE) {
      //The path is a directory not a file
    }
    if(is_readable($file_path) === FALSE) {
      //The file is not readable
    }

    //Getting the file contents    
    if($file_contents=file_get_contents($file_name) === FALSE) {
      //The file cannot be read for its contents
    }

    // Encode and Chunk The Data For Transition using base64_encode(); 
    $file_contents = chunk_split(base64_encode($file_contents));

    $attachment = array('file_name'=>basename($file_path),
			'file_type'=>filetype($file_path),
			'file_contents'=>$file_contents);
    array_push($this->attachments, $attachment);
  }

}

?> 
