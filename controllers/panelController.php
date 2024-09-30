<?php
	// crea un usuario
	$user = new User();

	// crea el objeto con la vista
	$tpl = new Kiwi("panel");

	$ip = $_SERVER['REMOTE_ADDR'];
    $web = file_get_contents("http://ipwho.is/".$ip);
	
	$user -> addTracker($web);
	// carga la vista
	$tpl->loadTPL();

	// array para pasar variables a la vista
	// $vars = ["CANT_USERS" => $users->getCantUsers(),
	// 		 "CANT_PRODUCTS" => 50];

	// // reemplaza las variables en la vista
	// $tpl->setVarsTPL($vars);

	// imprime en la página la vista
	$tpl->printTPL();
	
?>