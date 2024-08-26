<?php 


	/**
	 * 
	 * Class Kiwi Motor de plantillas
	 * 
	 * */
	class Kiwi{

		public $buffer;
		private $tpl;
		private $name_tpl;

		/**
		 * 
		 * Se ejecuta al instanciar el objeto
		 * 
		 * @param string $name_tpl nombre de la vista
		 * 
		 * */
		function __construct($name_tpl){

			$this->name_tpl = $name_tpl;
		}


		/**
		 * 
		 * Carga la vista dentro de buffer
		 * 
		 * @return bool existe|no existe la vista
		 * 
		 * */
		function loadTPL(){

			if(!file_exists("views/".$this->name_tpl."View.html")){
				echo "No existe la vista <b>".$this->name_tpl."</b>";
				exit();
			}

			$this->buffer = file_get_contents("views/".$this->name_tpl."View.html");


			return true;
		}

		/**
		 * 
		 * Altera el buffer con los valores de las variables
		 * 
		 * @param array $vars esta indexado asociativa key es nombre de la variable
		 * 
		 * */
		function setVarsTPL($vars){
			foreach ($vars as $needle => $str) {
				if($this->testVarTPL($needle)){
				$this->buffer = str_replace("{{".$needle."}}", $str, $this->buffer);
				}else{
					echo "no existe la variable <b>$needle</b>";
					exit();
				}
			}	
		}

		/**
		 * 
		 * Verifica si la variable existe en el buffer
		 * @return bool false si no existe la variable
		 * 
		 * */
		function testVarTPL($name_var){
			return strpos( $this->buffer, $name_var);
		}

		/**
		 * 
		 * imprime el buffer en pantalla
		 * 
		 * */
		function printTPL(){
			echo $this->buffer;
		}
	}

 ?>