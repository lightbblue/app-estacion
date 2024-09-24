<?php
	// crea el objeto con la vista
	$tpl = new Kiwi("validate");
	$user = new User();
	$token_email = explode("=", $_SERVER["REQUEST_URI"])[1];
	$error=$user->validate($token_email);
	
	
	// carga la vista
	$tpl->loadTPL();

	$tpl->setVarsTPL(["ERROR" => $error['error']]);
	// imprime en la página la vista
	$tpl->printTPL();
?>