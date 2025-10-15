<?php 
//========================================================================//
//	AUTOR  : JUAN CARLOS PINTO LARICO
//	FECHA  : MAYO  2018
//	VERSION: 2.0.0.0
//	E-MAIL : jcpintol@hotmail.com
//  hoja de seccion personalizada para su busqueda
//========================================================================//
class MySQLcn {
	protected $_link;
	protected $_result;
	protected $_numRows;
	private $_host = "localhost";
	private $_username = "root";
	private $_password = "";
	private $_database = "myweb";
        private $_port = 3306;
	
	// Establezca una conexión a la base de datos, cuando se crea una instancia de clase
	public function __construct() {
		$this->_link = new mysqli($this->_host, $this->_username, $this->_password, $this->_database, $this->_port);
		if(mysqli_connect_errno()) {
			echo "Connection Failed: " . mysqli_connect_errno();
			exit();
		}
	}
	
	// Envía la consulta a la conexión
	public function Query($sql) {
		$this->_result = $this->_link->query($sql) or die(mysqli_error($this->_result));
		$this->_numRows = mysqli_num_rows($this->_result);
	}
	
	// Inserta en databse
	public function UpdateDb($sql) {
		$this->_result = $this->_link->query($sql) or die(mysqli_error($this->_result));
		return $this->_result;
	}
	
	// Inserta y retorna ID
	public function InsertaDb($sql) {
		$this->_link->query($sql) or die(mysqli_error($this->_result));
		return mysqli_insert_id($this->_link);
	}
	
	// Devuelve el número de filas
	public function NumRows() {
		return $this->_numRows;
	}
	
	// Trae las filas y las devuelve
	public function Rows() {
		$rows = array();
		
		for($x = 0; $x < $this->NumRows(); $x++) {
			$rows[] = mysqli_fetch_assoc($this->_result);
		}
		return $rows;
	}
	
	// Trae las filas y las devuelve
	public function FetRows() {
		return mysqli_fetch_row($this->_result);
	}
	
	// Utilizado por otras clases para obtener la conexión
	public function GetLink() {
		return $this->_link;
	}
	
	// Asegurar datos de entrada
	public function SecureInput($value) {
		return mysqli_real_escape_string($this->_link, $value);
	}
	
	//Cerrar la conexion
	public function Close() {
        mysqli_close($this->_link);
    }
}
?>