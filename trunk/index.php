<?php 
  require_once "templates.php";
  $templater = new Templater();
  
  ($_GET['area']=="") ? $area = 'about' : $area = strtolower($_GET['area']);  
  
  $global_nav = $templater->load_template(  "global_nav",
                              array(  'name' => 'global_navigation.tmpl',
                                      'active_section' => $area,
                                      'active_section_class' => 'navigation_area_active', 
                                      'button_class' => 'navigation_area',
                                      'buttons' => array('login'      =>'Login Area',
                                                        'find'        =>'Find',
                                                        'create'      =>'Create',
                                                        'about'       =>'About',
                                                        'contact'     =>'Contact',
                                                        'developers'  =>'Developers',
                                                        'legals'      =>'Legal Docs') ) ); 
                                                        
  $section_header = $templater->load_template(  "section_header",
                              array(  'name' => 'section_header.tmpl',
                                      'heading' => 'What is Flitter?',
                                      'sublinks' => array( 'Sublink1' => '#',
                                                           'Sublink2' => '#',
                                                           'Sublink3' => '#' ),
                                      'sublink_class' => 'section_nav_link') );
                                      
  $section_content = $templater->load_template(  "section_content",
                              array( 'name' => $area.'.tmpl' ) );
                                    
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" 
"http://www.w3.org/TR/html4/strict.dtd">
<html>

<head>
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
    
      <?php //Section Header Include
        echo $section_header;
      ?>
      <?php //Section Content Include
        echo $section_content;
      ?>
    </div>
    
  </div>
  
</body>
</html>