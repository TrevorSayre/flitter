<?php 

class eMail {

  //Used to denote new lines
  public $eol;
  // If true and no text is supplied, will use a tag stripped html body
  public $strip_tags;

  private $attachments;
  private $recipients;

  private $from;
  private $reply_to;
  private $return_path;
  private $subject;

  private $html_body;
  private $text_body;
  
  private $mime_boundry;

  // RFC 2821 (faqs.org/rfcs/rfc2821.html) dictates that new lines in SMTP 
  // email must be denoted with CRLF (i.e. \r\n)
  public function __construct($strip_tags=TRUE, $eol = "\r\n") {
    $this->eol = $eol;
    $this->strip_tags = TRUE;
    $this->attachments = $this->recipients = array();
    $this->from = $this->reply_to = $this->return_path = NULL;
    $this->subject = $this->html_body = $this->text_body = NULL;
    $this->mime_boundary=md5(time());
  }

  public function setHTMLBody($body) {
    //Strip all Javascript
    $m_str = '/(on(blur|c(hange|lick)|dblclick|focus|keypress|(key|mouse)(down|up)|(un)?load|mouse(move|o(ut|ver))|reset|s(elect|ubmit))="(.*)?")/i'
    preg_replace($m_str,"",$body);
    $m_str = '/(<script>.*?<\/script>)/i';
    preg_replace($m_str,"",$body);
    $this->html_body = $body;
  }
  
  public function setTextBody($body) {
    $this->text_body = $body;
  }

  public function makeEmailAddress( $email, $alias=NULL ) {
    //Propper regex for validating an email
    $m_str  = '/^[\x20-\x2D\x2F-\x7E]+(\.[\x20-\x2D\x2F-\x7E]+)*';
    $m_str .='@(([a-z0-9]([-a-z0-9]*[a-z0-9]+)?){1,63}\.)+[a-z0-9]{2,6}$/i';
    //If the email address doesn't validate
    if( preg_match( $m_str, $email ) == 0 ) {
      //Impropper email address exception
    }
    //Don't allow new lines/returns
    $m_str = "/\r|\n/";
    //If the alias doesn't validate
    if( $alias!=NULL && preg_match(,$alias) == 0 ) {
      //We don't allow newlines inside of the alias
    }

    if($alias==NULL)
      $recipient = "<$email>";
    else
      $recipient = "\"$alias\" <$email>";
  }

  public function add_recipient( $email, $alias=NULL ) {
    array_push($this->recipients,$this->makeEmail($email,$alias));
  }
  
  public setFrom($email, $alias=NULL) {
    $this->from = makeEmailAddress($email,$alias);
  }
  public setSubject($string) {
    $this->subject = $string;
  }
  public setReplyTo($email, $alias=NULL) {
    $this->reply_to = makeEmailAddress($email,$alias);
  }
  public setReturnPath($email, $alias=NULL) {
    $this->return_path = makeEmailAddress($email,$alias);
  }

  public function getHeadersText() {
    // Used for automated replys. Use from if not set
    $return = ($this->return_path==NULL) ? $this->from : $this->return_path;
    $headers .= 'Return-Path: '.$return.$this->eol;
    //Marks the sender
    $headers = 'From: '.$this->from.$this->eol;
    // Used for human replys. Use from if not set
    $reply = ($this->reply_to==NULL) ? $this->from : $this->reply_to;
    $headers .= 'Reply-To: '.$reply.$this->eol;

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
    
    $headers .= "boundary=\"".$this->mime_boundary."\"".$this->eol;
    return $headers;
  }

  public function getAttachmentsText() {
    $msg = "";
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
    return $msg;
  }

  public function getHTMLText() {
    // HTML Version
    $msg  = "--".$mime_boundary.$eol; 
    $msg .= "Content-Type: text/html; charset=iso-8859-1".$eol; 
    $msg .= "Content-Transfer-Encoding: 8bit".$eol; 
    $msg .= $body.$eol.$eol;
    return $msg;
  }

  public function getTextText() {
    // Text Version 
    $msg .= "--".$mime_boundary.$eol; 
    $msg .= "Content-Type: text/plain; charset=iso-8859-1".$eol; 
    $msg .= "Content-Transfer-Encoding: 8bit".$eol; 
    $msg .= "This is a multi-part message in MIME format.".$eol; 
    $msg .= "If you are reading this, please update your E-mail client.".$eol; 
    $msg .= "+ + Text Only E-mail from Sender + +".$eol.$eol; 
  }

  public function getSubject() {
    //Handle the email Subject
    if($this->subject == NULL) {
      //Warning, no subject set

      //Return subject as some default
      return "";
    }
    return $this->subject;
  }

  public function getRecipientsText() {
    //Prepare the recipient email addresses
    if(count($this->recipients)==0) {
      //The message has to have atleast one recipient
    }
    //Collapse the emails into a comma separated list
    return implode(', ',$this->recipients);
  }

  public function send() {
    $to = $this->getRecipientsText();
    $subject = $this->getSubject();
    $headers = $this->getHeadersText();

    //Build the msg
    $msg = $this->getAttachmentsText();
    // Setup the next part for text and HTML versions
    $msg .= "Content-Type: multipart/alternative".$this->eol;     
    //If an HTML body exists, output it into the message
    if($this->html_body != NULL) {
      $msg = $this->getHTMLText();

      if($this->text_body == NULL) {
	//Warning there is no text body supplied
	
	//Strip tags to provide a default text body
	$this->text_body = strip_tags($this->html_body);
      }
    }
    //If neither body exists, give warning and supply default text body
    if($this->text_body == NULL) {
      //Warning there is no body to this email

      // Supply a default body
      $this->text_body = "";
    }
    //If there is a text body set
    if($this->text_body != NULL) {
      $msg .= $this->getTextText()
    }
    // Finish with two eol's for better security. Injection Protection! 
    $msg .= "--".$mime_boundary."--".$eol.$eol;

    // Send the E-mail 
    // The INI lines are to force the From Address to be used
    //Can we still use an alias here?
    ini_set(sendmail_from,$this->from);
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
