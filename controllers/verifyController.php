<?php 
	
	/*< Captura de de la url el valor de la variable token*/
	$vars = ["TOKEN_EMAIL" => explode("=", $_SERVER["REQUEST_URI"])[1]];

	$user = new User();
	$user->verify(explode("=", $_SERVER["REQUEST_URI"])[1]);

	header("location:login");
 ?>