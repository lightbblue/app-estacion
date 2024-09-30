<?php 

    $email = $_SESSION["morphyx"]['user'] -> email;

    if($email!='admin-estacion'){
        header('location:error404');
    }

    $tpl = new Kiwi("map");

    $tpl->loadTPL();

    $tpl->printTPL();
	
 ?>