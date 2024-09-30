<?php 

	/**
	* @file User.php
	* @brief Declaraciones de la clase User para la conexión con la base de datos.
	* @author Matias Leonardo Baez
	* @date 2024
	* @contact elmattprofe@gmail.com
	*/

	// incluye la libreria para conectar con la db
	include_once 'DBAbstract.php';

	/*< incluye la clase Mailer.php para enviar correo electrónico*/
	include_once 'Mailer.php';

	// se crea la clase User que hereda de DBAbstract
	class User extends DBAbstract{

		private $nameOfFields = array();

		/**
		 * 
		 * @brief Es el constructor de la clase User
		 * 
		 * Al momento de instanciar User se llama al padre para que ejecute su constructor
		 * 
		 * */
		function __construct(){
		
			// quiero salir de la clase actual e invocar al constructor
			parent::__construct();

			/**< Obtiene la estructura de la tabla */
			$result = $this->query('DESCRIBE appestacion__users');

			foreach ($result as $key => $row) {
				$buff =$row["Field"];
				
				/**< Almacena los nombres de los campos*/
				$this->nameOfFields[] = $buff;

				/**< Autocarga de atributos a la clase */
				$this->$buff=NULL;
			}
			

		}

		function getOS($userAgent) {
				    $osArray = [
				        'Windows 10' => 'Windows NT 10.0',
				        'Windows 8.1' => 'Windows NT 6.3',
				        'Windows 8' => 'Windows NT 6.2',
				        'Windows 7' => 'Windows NT 6.1',
				        'Windows Vista' => 'Windows NT 6.0',
				        'Windows XP' => 'Windows NT 5.1',
				        'Mac OS X' => '(Mac_PowerPC)|(Macintosh)',
				        'Linux' => 'Linux',
				        'Android' => 'Android',
				        'iOS' => '(iPhone)|(iPad)',
				    ];

				    foreach ($osArray as $os => $pattern) {
				        if (preg_match("/$pattern/i", $userAgent)) {
				            return $os;
				        }
				    }

				    return 'Unknown OS';
				}

		function getBrowser($userAgent) {
            $browserArray = [
                'Google Chrome' => 'Chrome',
                'Mozilla Firefox' => 'Firefox',
                'Internet Explorer' => 'MSIE|Trident',
                'Safari' => 'Safari',
                'Opera' => 'Opera|OPR',
                'Microsoft Edge' => 'Edg', // Edge usa 'Edg' como parte del User-Agent
            ];

            foreach ($browserArray as $browser => $pattern) {
                if (preg_match("/$pattern/i", $userAgent)) {
                    return $browser;
                }
            }

            return 'Unknown Browser';
        }
		function dataCants(){
            $accesos = $this -> query("SELECT COUNT(*) AS cantidad_accesos FROM appestacion__tracker"); 
            $registros = $this -> query("SELECT COUNT(*) AS cantidad_registros FROM appestacion__users"); 
            return [$accesos[0] ,$registros[0]];
        }
        function list_clients_location(){
            return $this -> query("SELECT ip, latitud, longitud,COUNT(*) AS cantidad_accesos FROM appestacion__tracker GROUP BY ip, latitud, longitud"); 
        }
        function addTracker($web){
            $data = json_decode($web);

            $userAgent = $_SERVER['HTTP_USER_AGENT'];

            $os = $this->getOS($userAgent);
			$browser = $this->getBrowser($userAgent);
            $latitud = $data -> latitude;
            $longitud = $data -> longitude;
            $pais = $data -> country;
            $ip = $_SERVER['REMOTE_ADDR'];
            $token = md5($_ENV['PROJECT_WEB_TOKEN'].$_SESSION['morphyx']['user'] -> email);

            $ssql = "INSERT INTO appestacion__tracker (token, ip, latitud, longitud, pais, navegador, sistema, add_date) VALUES ('$token','$ip','$latitud', '$longitud','$pais','$browser','$os',NOW())";
            $this->query($ssql); 
        }
		/**
		 * 
		 * Nose q hace pero es el blocked
		 * @return bool siempre verdadero
		 * 
		 * */
		function blocked($token){
			$ssql = "SELECT * from appestacion__users WHERE token_action='$token'";
			$email= $this->query($ssql);
			if(count($email)==0){
				return 	["errno" => 499, "error" => "El
token no corresponde a un usuario"];
			}

			$ssql = "UPDATE appestacion__users SET token_action='',bloqueado=1 WHERE token_action='$token'";

			$this->query($ssql);

			$correo = new Mailer();

				/*< plantilla de email para validar cuenta*/
				$cuerpo_email = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuenta Bloqueada</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            padding: 10px 0;
        }
        .header h1 {
            margin: 0;
            color: #cff;
        }
        .content {
            padding: 20px;
        }
        .content p {
            line-height: 1.6;
        }
        .button {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px 0;
            background-color: #cff;
            color: white;
            text-align: center;
            text-decoration: none;
            user-select: none;
            border-radius: 5px;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #999999;
            padding: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>App-Estación</h1>
        </div>
        <div class="content">
            <p>Hola,</p>
            <p>Su cuenta ha sido bloqueada debido a una reciente actividad sospechosa:</p>
            <a href="http://www.mattprofe.com.ar/alumno/10059/app-estacion/reset?token='.$token_action.'" class="button">Click aquí para cambiar contraseña</a>
        </div>
        <div class="footer">
            <p>&copy; 2024 App-Estación by lightbblue. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
';

				/*< envia el correo electrónico de validación*/
				$correo->send(["destinatario" => $email, "motivo" => "Su cuenta ha sido bloqueada", "contenido" => $cuerpo_email] );

			return true;
		}

		/**
		 * 
		 * Nose q hace pero es el verify
		 * @return bool siempre verdadero
		 * 
		 * */
		function validate($token){
			$ssql = "SELECT * from appestacion__users WHERE token_action='$token'";
			$email= $this->query($ssql);
			if(count($email)==0){
				return 	["errno" => 499, "error" => "El
token no corresponde a un usuario"];
			}
			$tkn = md5($_ENV['PROJECT_WEB_TOKEN'].$email[0]['email']);
			$ssql = "UPDATE appestacion__users SET token='$tkn',activo=1,token_action='',active_date=NOW() WHERE token_action='$token'";

			$this->query($ssql);
			
			$correo = new Mailer();

				/*< plantilla de email para validar cuenta*/
				$cuerpo_email = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gracias por validarte</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            padding: 10px 0;
        }
        .header h1 {
            margin: 0;
            color: #cff;
        }
        .content {
            padding: 20px;
        }
        .content p {
            line-height: 1.6;
        }
        .button {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px 0;
            background-color: #cff;
            color: white;
            text-align: center;
            text-decoration: none;
            user-select: none;
            border-radius: 5px;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #999999;
            padding: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>App-Estación</h1>
        </div>
        <div class="content">
            <p>Hola,</p>
            <p>Gracias por validar tu cuenta de App-Estación, ya eres un usuario activo.</p>
        </div>
        <div class="footer">
            <p>&copy; 2024 App-Estación by lightbblue. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
';

				/*< envia el correo electrónico de validación*/
				$correo->send(["destinatario" => $email[0]['email'], "motivo" => "Gracias por validarte", "contenido" => $cuerpo_email] );
			return 	["errno" => 498, "error" => "Token validado correctamente"];
		}

		/**
		 * 
		 * Finaliza la sesión
		 * @return bool true
		 * 
		 * */
		function logout(){
			return true;
		}

		/**
		 * 
		 * Intenta loguear al usuario mediante email y contraseña
		 * @param array $form indexado de forma asociativa
		 * @return array que posee códigos de error especiales
		 * 
		 * */
		function login($form){

			/*< recupera el method http*/
			$request_method = $_SERVER["REQUEST_METHOD"];

			/* si el method es invalido*/
			if($request_method!="GET"){
				return ["errno" => 410, "error" => "Metodo invalido"];
			}

			/*< recupera el email del formulario*/
			$email = $form["txt_email"];

			/*< consultamos si existe el email*/
			$result = $this->query("CALL `login`('$email')");

			// el email no existe
			if(count($result)==0){
				return ["error" => "Credenciales no válidas", "errno" => 404];
			}

			/*< seleccionamos solo la primer fila de la matriz*/
			$result = $result[0];

			if ($result['activo']==0) {
				return ["error" => "Su usuario aún no
se ha validado, revise su casilla de correo", "errno" => 406];
			}

			if ($result['bloqueado']==1||$result['recupero']==1) {
				return ["error" => "Usuario bloqueado, revise su correo electrónico", "errno" => 407];
			}

			$ip = $_SERVER['REMOTE_ADDR'];
			$userAgent = $_SERVER['HTTP_USER_AGENT'];

			// si el email existe y la contraseña es valida
			if($result["password"]==md5($form["txt_pass"]."morphyx")){

				/**< autocarga de valores en los atributos de la clase */
				foreach ($this->nameOfFields as $key => $value) {
					$this->$value = $result[$value];
				}

				// para que los avatares sean gatitos
				// $this->avatar = str_replace("set5", "set4", $this->avatar); 

				/*< carga la clase en la sesión*/
				$_SESSION["morphyx"]['user'] = $this;

				
				
				


				$token_action = md5($_ENV['PROJECT_WEB_TOKEN'].$email);

				/*< consulta para volver a activar el usuario que se había ido*/
				$ssql = "UPDATE appestacion__users SET token_action='".$token_action."' WHERE id=".$result['id'];

				/*< ejecuta la consulta*/
				$result = $this->query($ssql);

				// Función para detectar el navegador
				$os = $this->getOS($userAgent);
				$browser = $this->getBrowser($userAgent);

				$correo = new Mailer();

				/*< plantilla de email para validar cuenta*/
				$cuerpo_email = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de sesión</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            padding: 10px 0;
        }
        .header h1 {
            margin: 0;
            color: #cff;
        }
        .content {
            padding: 20px;
        }
        .content p {
            line-height: 1.6;
        }
        .button {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px 0;
            background-color: #cff;
            color: white;
            text-align: center;
            text-decoration: none;
            user-select: none;
            border-radius: 5px;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #999999;
            padding: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>App-Estación</h1>
        </div>
        <div class="content">
            <p>Hola,</p>
            <p>Hemos detectado un inicio de sesión en tu cuenta de App-Estación desde un dispositivo nuevo o una ubicación diferente. Estos son los detalles:</p>
            <p>IP: '.$ip.'</p>
            <p>SO: '.$os.'</p>
            <p>Navegador Web: '.$browser.'</p>
            <a href="http://www.mattprofe.com.ar/alumno/10059/app-estacion/blocked?token='.$token_action.'" class="button">No fui yo,
bloquear cuenta</a>
            <p>Si has sido tú en App-Estación, ignora este correo electrónico.</p>
        </div>
        <div class="footer">
            <p>&copy; 2024 App-Estación by lightbblue. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
';

				/*< envia el correo electrónico de validación*/
				$correo->send(["destinatario" => $email, "motivo" => "Aviso de inicio de sesión en tu cuenta", "contenido" => $cuerpo_email] );

				/*< usuario valido*/
				return ["error" => "Acceso valido", "errno" => 200];
			}
			$os = $this->getOS($userAgent);
			$browser = $this->getBrowser($userAgent);
			$token_action = md5($_ENV['PROJECT_WEB_TOKEN'].$email);
			$correo = new Mailer();

			$cuerpo_email = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intento de inicio de sesión</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            padding: 10px 0;
        }
        .header h1 {
            margin: 0;
            color: #cff;
        }
        .content {
            padding: 20px;
        }
        .content p {
            line-height: 1.6;
        }
        .button {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px 0;
            background-color: #cff;
            color: white;
            text-align: center;
            text-decoration: none;
            user-select: none;
            border-radius: 5px;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #999999;
            padding: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>App-Estación</h1>
        </div>
        <div class="content">
            <p>Hola,</p>
            <p>Hemos detectado un inicio de sesión en tu cuenta de App-Estación con contraseña inválida desde un dispositivo nuevo o una ubicación diferente. Estos son los detalles:</p>
            <p>IP: '.$ip.'</p>
            <p>SO: '.$os.'</p>
            <p>Navegador Web: '.$browser.'</p>
            <a href="http://www.mattprofe.com.ar/alumno/10059/app-estacion/blocked?token='.$token_action.'" class="button">No fui yo,
bloquear cuenta</a>
            <p>Si has sido quien se ha logeado en App-Estación, ignora este correo electrónico.</p>
        </div>
        <div class="footer">
            <p>&copy; 2024 App-Estación by lightbblue. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
';

				/*< envia el correo electrónico de validación*/
				$correo->send(["destinatario" => $email, "motivo" => "Aviso de inicio de sesión en tu cuenta", "contenido" => $cuerpo_email] );
			// email existe pero la contraseña invalida
			return ["error" => "Error de contraseña", "errno" => 405];

		}

		/**
		 * 
		 * Agrega un nuevo usuario si no existe el correo electronico en la tabla users
		 * @param array $form es un arreglo assoc con los datos del formulario
		 * @return array que posee códigos de error especiales 
		 * 
		 * */
		function register($form){
			/*< recupera el email*/
			$email = $form["txt_email"];

			/*< consulta si el email ya esta en la tabla de usuarios*/
			$result = $this->query("SELECT * FROM appestacion__users WHERE email = '$email'");

			// el email no existe entonces se registra
			if($result==[]){

				/*< encripta la contraseña*/
				$pass = md5($form["txt_pass"]."morphyx");

				/*< se crea el token único para validar el correo electrónico*/
				$token_action = md5($_ENV['PROJECT_WEB_TOKEN'].$email);

				/*< agrega el nuevo usuario y deja en pendiente de validar su email*/
				$ssql = "INSERT INTO appestacion__users (email, password, token_action) VALUES ('$email','$pass', '$token_action')";

				/*< ejecuta la consulta*/
				$result = $this->query($ssql);

				/*< se recupera el id del nuevo usuario*/
				$this->id = $this->db->insert_id;

				/*< instancia la clase Mailer para enviar el correo electrónico de validación de correo electrónico*/
				$correo = new Mailer();

				/*< plantilla de email para validar cuenta*/
				$cuerpo_email = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Registro</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            padding: 10px 0;
        }
        .header h1 {
            margin: 0;
            color: #cff;
        }
        .content {
            padding: 20px;
        }
        .content p {
            line-height: 1.6;
        }
        .button {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px 0;
            background-color: #cff;
            color: white;
            text-align: center;
            text-decoration: none;
            user-select: none;
            border-radius: 5px;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #999999;
            padding: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>App-Estación</h1>
        </div>
        <div class="content">
            <p>Hola,</p>
            <p>Gracias por registrarte en App-Estación. Para completar tu registro, por favor confirma tu dirección de correo electrónico haciendo clic en el botón de abajo:</p>
            <a href="http://www.mattprofe.com.ar/alumno/10059/app-estacion/validate?token='.$token_action.'" class="button">Click aquí para
activar tu usuario</a>
            <p>Si no te registraste en App-Estación, ignora este correo electrónico.</p>
        </div>
        <div class="footer">
            <p>&copy; 2024 App-Estación by lightbblue. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
';

				/*< envia el correo electrónico de validación*/
				$correo->send(["destinatario" => $email, "motivo" => "Confirmación de registro", "contenido" => $cuerpo_email] );


				/*< aviso de registro exitoso*/
				return ["error" => "Usuario registrado", "errno" => 200];
			}

			$date_zero = '0000-00-00 00:00:00';

			// El usuario volvio a la aplicacion
			// if($result['delete_date']!=$date_zero){

			// 	/*< recupera el id del usuario que quiere volver a nuestra app*/
			// 	$id=$result["id"];
			// 	$this->id = $result["id"];

			// 	/*< encripta la nueva contraseña*/
			// 	$pass = md5($form["txt_pass"]."morphyx");

			// 	/*< consulta para volver a activar el usuario que se había ido*/
			// 	$ssql = "UPDATE appestacion__users SET first_name='', last_name='', `password`='$pass', delete_at='0000-00-00 00:00:00' WHERE id=$id";

			// 	/*< ejecuta la consulta*/
			// 	$result = $this->query($ssql);

			// 	/*< mensaje de usuario volvio a la app*/
			// 	return ["error" => "Usuario que abandono volvio a la app", "errno" => 201];
			// }

			// si el email existe 
			return ["error" => "Correo ya registrado", "errno" => 405];

		}


		/**
		 * 
		 * Actualiza los datos del usuario con los datos de un formulario
		 * @param array $form es un arregle asociativo con los datos a actualizar
		 * @return array arreglo con el código de error y descripción
		 * 
		 * */
		function update($form){
			$nombre = $form["txt_first_name"];
			$apellido = $form["txt_last_name"];
			$id = $this->id;


			$this->first_name = $nombre;
			$this->last_name = $apellido;

			$ssql = "UPDATE appestacion__users SET first_name='$nombre', last_name='$apellido' WHERE id=$id";
			$this->query($ssql);

			return ["error" => "Se actualizo correctamente", "errno" => 200];
		}

		/**
		 * 
		 * Cantidad de usuarios registrados
		 * @return int cantidad de usuarios registrados
		 * 
		 * */
		function getCantUsers(){

			$result = $this->query("SELECT * FROM appestacion__users");

			return $this->db->affected_rows;
		}

		function check($form){
			$email = $form['txt_email'];
			$result = $this->query("SELECT * FROM appestacion__users WHERE email='$email'");
			if(isset($result[0])){
				$token_action = md5($_ENV['PROJECT_WEB_TOKEN'].$email);
				$ssql = "UPDATE appestacion__users SET recupero=1, recover_date=NOW(),token_action='$token_action' WHERE email='$email'";
				$this->query($ssql);

				$correo = new Mailer();

				/*< plantilla de email para validar cuenta*/
				$cuerpo_email = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperación de contraseña</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            padding: 10px 0;
        }
        .header h1 {
            margin: 0;
            color: #cff;
        }
        .content {
            padding: 20px;
        }
        .content p {
            line-height: 1.6;
        }
        .button {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px 0;
            background-color: #cff;
            color: white;
            text-align: center;
            text-decoration: none;
            user-select: none;
            border-radius: 5px;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #999999;
            padding: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>App-Estación</h1>
        </div>
        <div class="content">
            <p>Hola,</p>
            <p>Se inició el proceso de restablecimiento de contraseña:</p>
            <a href="http://www.mattprofe.com.ar/alumno/10059/app-estacion/reset?token='.$token_action.'" class="button">Click aquí para restablecer contraseña</a>
            <p>Si no fuiste tú, ignora este correo electrónico.</p>
        </div>
        <div class="footer">
            <p>&copy; 2024 App-Estación by lightbblue. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
';

				/*< envia el correo electrónico de validación*/
				$correo->send(["destinatario" => $email, "motivo" => "Recuperación de contraseña", "contenido" => $cuerpo_email] );
				return ["error"=>"Envío exitoso"];
			}else{
				return ["error"=>"El email no se encuentra registrado"];
			}
		}
        function reset($form){
            $token_action = $form['token'];  
            $pass = md5($form['pass']."morphyx");
            $result = $this->query("SELECT * FROM appestacion__users WHERE token_action='$token_action'");
            if(isset($result[0])){
                $email = $result[0]['email'];
                $ssql = "UPDATE appestacion__users SET recupero=0,bloqueado=0,token_action='',password='$pass' WHERE email='$email'";
                $this->query($ssql);

                $token_action = md5($_ENV['PROJECT_WEB_TOKEN'].$email);
                $ip = $_SERVER['REMOTE_ADDR'];
                $userAgent = $_SERVER['HTTP_USER_AGENT'];
                $os = $this->getOS($userAgent);
                $browser = $this->getBrowser($userAgent);
                
                $correo = new Mailer();

                $cuerpo_email = '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Contraseña reestablecida</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
                color: #333;
                margin: 0;
                padding: 0;
            }
            .container {
                width: 100%;
                max-width: 600px;
                margin: 0 auto;
                background-color: #ffffff;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            }
            .header {
                text-align: center;
                padding: 10px 0;
            }
            .header h1 {
                margin: 0;
                color: #cff;
            }
            .content {
                padding: 20px;
            }
            .content p {
                line-height: 1.6;
            }
            .button {
                display: block;
                width: 200px;
                margin: 20px auto;
                padding: 10px 0;
                background-color: #cff;
                color: white;
                text-align: center;
                text-decoration: none;
                user-select: none;
                border-radius: 5px;
            }
            .footer {
                text-align: center;
                font-size: 12px;
                color: #999999;
                padding: 10px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>App-Estación</h1>
            </div>
            <div class="content">
                <p>Hola,</p>
                <p>Se ha reestablecido tu contraseña correctamente desde:</p>
                <p>IP: '.$ip.'</p>
                <p>SO: '.$os.'</p>
                <p>Navegador Web: '.$browser.'</p>
                <a href="http://www.mattprofe.com.ar/alumno/10059/app-estacion/blocked?token='.$token_action.'" class="button">No fui yo,
    bloquear cuenta</a>
                <p>Si has sido quien se ha logeado en App-Estación, ignora este correo electrónico.</p>
            </div>
            <div class="footer">
                <p>&copy; 2024 App-Estación by lightbblue. Todos los derechos reservados.</p>
            </div>
        </div>
    </body>
    </html>
    ';

                    /*< envia el correo electrónico de validación*/
                    $correo->send(["destinatario" => $email, "motivo" => "Contraseña reestablecida", "contenido" => $cuerpo_email] );
                    return ["error"=>"Contraseña reestablecida correctamente"];
            }else{
                return ["error"=>"El usuario no está registrado"];
            }  
        }

        function validarToken($token_action){
            $result = $this->query("SELECT * FROM appestacion__users WHERE token_action='".$token_action['token_action']."'");
			if(isset($result[0])){
                return ["errno"=>200];
            }else{
                return ["error"=>"El token no corresponde a un usuario"];
            }
        }
		/**
		 * 
		 * @brief Retorna un listado limitado
		 * @param string $request_method espera a GET
		 * @param array $request [inicio][cantidad]
		 * @return array lista con los datos de los usuarios 
		 * 
		 * */
		function getAllUsers($request){

			$request_method = $_SERVER["REQUEST_METHOD"];

			/*< Es el método correcto en HTTP?*/
			if($request_method!="GET"){
				return ["errno" => 410, "error" => "Metodo invalido"];
			}

			/*< Solo un usuario logueado puede ver el listado */
			if(!isset($_SESSION["morphyx"])){
				return ["errno" => 411, "error" => "Para usar este método debe estar logueado"];
			}

			/*

			if(!isset($_SESSION["morphyx"]['user_level'])){

				if($_SESSION["morphyx"]['user_level']!='admin'){
				return ["errno" => 412, "error" => "Solo el 	administrador puede utilizar el metodo"];
				}
			}

			*/


			$inicio = 0;

			if(isset($request["inicio"])){
				$inicio = $request["inicio"];
			}

			if(!isset($request["cantidad"])){
				return ["errno" => 404, "error" => "falta cantidad por GET"];
			}

			$cantidad = $request["cantidad"];

			$result = $this->query("SELECT * FROM appestacion__users LIMIT $inicio, $cantidad");

			return $result;
		}


	}

 ?>