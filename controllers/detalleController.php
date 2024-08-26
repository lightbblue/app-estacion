<?php
	// crea un usuario
	// $users = new User();

	// crea el objeto con la vista
	$tpl = new Kiwi("detalle");

	// carga la vista
	$tpl->loadTPL();

	// array para pasar variables a la vista
	$vars = ["CHIPID" => explode("/",$_GET['slug'])[1]];

	// reemplaza las variables en la vista
	$tpl->setVarsTPL($vars);

	// imprime en la página la vista
	$tpl->printTPL();

?>