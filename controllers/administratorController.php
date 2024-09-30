<?php 

    $email = $_SESSION["morphyx"]['user'] -> email;

    if($email!='admin-estacion'){
        header('location:error404');
    }

    $tpl = new Kiwi("administrator");

    $tpl->loadTPL();

    $tpl->printTPL();
	
 ?>