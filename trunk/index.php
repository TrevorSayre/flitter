<?php 
  require_once "templates.php";
  $templater = new Templater();
  
  ($_GET['area']=="") ? $area = 'about' : $area = strtolower($_GET['area']);  
  $global_nav = $templater->load_template(  "global_nav",
                              array(  'name' => 'templates/global_navigation.tmpl',
                                      'active_section' => $area,
                                      'active_section_class' => 'navigation_area_active', 
                                      'button_class' => 'navigation_area',
                                      'buttons' => array('account'      =>'Accounts',
                                                        'find'        =>'Find',
                                                        'create'      =>'Create',
                                                        'about'       =>'About',
                                                        'contact'     =>'Contact',
                                                        'developers'  =>'Developers',
                                                        'legals'      =>'Legal Docs') ), true ); 
  
  $section_content = $templater->load_template(  "section_content",
                              array( 'name' => 'templates/'.$area.'.tmpl' ), true );

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" 
"http://www.w3.org/TR/html4/strict.dtd">
<html>

<head>
  <base href="http://<?php echo $_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']; ?>">
  <title>Flitter</title>
  <link href="css/index.css" type="text/css" rel="stylesheet" />
  <script src="js/jquery-1.3.2.js" type="text/javascript"></script>
  <script src="js/index.js" type="text/javascript"></script>
  <?php $templater->linking_code(); ?>
</head>

<body>
  <div id="container">
    
    <?php //Global Navigation Include
      echo $global_nav;
    ?>

    <div id="section_content">
      <?php //Section Content Include
        echo $section_content;
      ?>
    </div>
    
  </div>
  
</body>
</html>