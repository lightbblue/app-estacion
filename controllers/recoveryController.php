<?php
	// crea el objeto con la vista
	$tpl = new Kiwi("recovery");
	
	// carga la vista
	$tpl->loadTPL();

	$tpl->setVarsTPL(["ERROR" => ""]);
	// imprime en la página la vista
	$tpl->printTPL();
?>