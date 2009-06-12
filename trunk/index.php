<?php require_once "templates.php"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" 
"http://www.w3.org/TR/html4/strict.dtd">
<html>

<head>
  <title>Flitter</title>
  <link href="css/index.css" type="text/css" rel="stylesheet" />
  <script src="js/jquery-1.3.2.js" type="text/javascript"></script>
  <script src="js/index.js" type="text/javascript"></script>
</head>

<body>
  <div id="container">
    <?php load_template("global_navigation.tmpl", array( 'site_logo' => 'Flitter', 
                                                         'active_section' => 'About', 
                                                         'button_class' => 'navigation_area',
                                                         'buttons' => array('login'   =>'Login Area',
                                                                            'find'    =>'Find',
                                                                            'create'  =>'Create',
                                                                            'about'   =>'About',
                                                                            'contact' =>'Contact',
                                                                            'devs'    =>'Developers',
                                                                            'legals'  =>'Legal Docs') ) ); ?>
    <div id="section_content">
      
      <div id="section_header">
        <div id="section_logo">What is Flitter?</div>
        <div id="section_navigation">
          <a href="#" class="section_nav_link">
            Sublink3
          </a>
          <a href="#" class="section_nav_link">
            Sublink2
          </a>
          <a href="#" class="section_nav_link">
            Sublink1
          </a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>