<?php 
	
	/*< Captura de de la url el valor de la variable token*/
    
    $token_action =  explode("=", $_SERVER["REQUEST_URI"])[1];
    $vars = ["TOKEN_ACTION"=>$token_action];
    
    $tpl = new Kiwi("reset");
	// carga la vista
	$tpl->loadTPL();
	// Reemplaza las variables de la vista
	$tpl->setVarsTPL($vars);
	// imprime en la vista en la página
	$tpl->printTPL();
 ?>