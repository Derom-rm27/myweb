<?php
//========================================================================//
//  PROYEC : ADMINISTRADOR DE CONTENIDOS WEB
//	AUTOR  : JUAN CARLOS PINTO LARICO
//	FECHA  : DICIEMBRE   2014
//	VERSION: 1.0.0
//  E-MAIL : jcpintol@hotmail.com
//========================================================================//

// Iniciar la sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Limpiar todas las variables de sesión
$_SESSION = array();

// Destruir la cookie de sesión si existe
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destruir la sesión
session_destroy();

// Asegurarse de que no haya salida antes de la redirección
ob_clean();

// Redirigir al index principal
header("Location: ../index.php");
exit();
?>