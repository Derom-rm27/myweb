<?php
	//========================================================================//
	//  PROYEC : ADMINISTRADOR DE CONTENIDOS WEB
	//	AUTOR  : JUAN CARLOS PINTO LARICO
	//	FECHA  : JULIO   2021
	//	VERSION: 1.0.0
	//  E-MAIL : jcpintol@hotmail.com
	//========================================================================//
	function fnComprueba(&$msg){
	  if( md5($_POST ['clave'])!= $_SESSION['key'])	return 0;
	  else	return 1;   
	}
	/* USER-AGENTS
	================================================== */
	function VerificaEquipo( $type = NULL ) {
			$user_agent = strtolower ( $_SERVER['HTTP_USER_AGENT'] );
			if ( $type == 'bot' ) {
					// matches popular bots
					if ( preg_match ( "/googlebot|adsbot|yahooseeker|yahoobot|msnbot|watchmouse|pingdom\.com|feedfetcher-google/", $user_agent ) ) {
							return true;
							// watchmouse|pingdom\.com are "uptime services"
					}
			} else if ( $type == 'browser' ) {
					// matches core browser types
					if ( preg_match ( "/mozilla\/|opera\//", $user_agent ) ) {
							return true;
					}
			} else if ( $type == 'mobile' ) {
					// matches popular mobile devices that have small screens and/or touch inputs
					// mobile devices have regional trends; some of these will have varying popularity in Europe, Asia, and America
					// detailed demographics are unknown, and South America, the Pacific Islands, and Africa trends might not be represented, here
					if ( preg_match ( "/phone|iphone|itouch|ipod|symbian|android|htc_|htc-|palmos|blackberry|opera mini|iemobile|windows ce|nokia|fennec|hiptop|kindle|mot |mot-|webos\/|samsung|sonyericsson|^sie-|nintendo/", $user_agent ) ) {
							// these are the most common
							return true;
					} else if ( preg_match ( "/mobile|pda;|avantgo|eudoraweb|minimo|netfront|brew|teleca|lg;|lge |wap;| wap /", $user_agent ) ) {
							// these are less common, and might not be worth checking
							return true;
					}
			}
			return false;
	}
	//-------------------------------------------------------------------------//
	session_start();
	if(isset($_SESSION['login'])){ 
		header("location:user.php"); 
	}else{
		if(isset($_POST['users'])){
			//HOJA PROCESADA
			if( $_POST['clave'] == "true" || fnComprueba($msg) == 1){
			  require_once("script/conex.php");
			  $cn= new MySQLcn();
			  $login = htmlspecialchars(trim($_POST['users']));
			  $pass = trim($_POST['pass']);
			  $querys ="CALL Acceder('$login','$pass');";
			  $cn->Query($querys);
			  $result = $cn->FetRows();
			  if($result && $result[0] != 'No Existe'){
				$data = $result;
				$cn->Close();
				$_SESSION["idUser"]=$data[0];
				$_SESSION["idGrupo"]=$data[1];
				$_SESSION["login"]=$data[3];
				$_SESSION["nivel"]=$data[4];
				$_SESSION["nombre"]=$data[2];
				$_SESSION["hora"]=date("Y-n-j H:i:s");
				header("location:user.php");
				exit(); 
			  }else{
				$mensaje="Usuario o Contraseña Incorrecto";
				header("location:index.php?mensaje=$mensaje");
				exit();
			  }
			}else{
				$mensaje="Datos de Imagen Incorrecta";
				header("location:index.php?mensaje=$mensaje");
				exit();
			}			
		}else{
			if(VerificaEquipo('mobile')){
				$mensaje="Iniciar&nbsp;&nbsp;Sesi&oacute;n Mobil";
			}else{
				$mensaje="Iniciar&nbsp;&nbsp;Sesi&oacute;n Web";
			}
		}
		header("location:index.php?mensaje=$mensaje");
		exit();
	}
?>
