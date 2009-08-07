<?php
  require_once 'flitter/flitter_library.php';
  require_once 'resource.php';
  require_once 'user.php';
  
  if(isset($_POST['email']) && isset($_POST['password'])) {
    $flitter = new FlitterLibrary();
    $user = $flitter->validateUser($_POST['email'],$_POST['password']);
    //Valid Credentials
    if($user!=NULL) {
      $user = new User($user);
      //Write user to session
      getSessionResource()->storeResource($user->get_info(),'accounts');
      header('Location: http://'.$_SERVER['SERVER_NAME']);
    }
    else
      die('invalid user credentials');
  }
  else
    header('Location: http://'.$_SERVER['SERVER_NAME'].'/login');

?>