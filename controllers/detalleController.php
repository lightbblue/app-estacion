<?php
	$tpl = new Kiwi("detalle");
	// carga la vista
	$tpl->loadTPL();
	// Reemplaza las variables de la vista
	$tpl->setVarsTPL(["CHIPID"=>explode("/", $_GET['slug'])[1]]);
	// imprime en la vista en la página
	$tpl->printTPL();
 ?>