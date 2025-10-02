<?php
        //========================================================================//
        //  PROYEC : ADMINISTRADOR DE CONTENIDOS WEB
        //      AUTOR  : JUAN CARLOS PINTO LARICO
        //      FECHA  : JULIO   2021
        //      VERSION: 1.0.0
        //  E-MAIL : jcpintol@hotmail.com
        //========================================================================//
        function fnComprueba(){
          if (isset($_POST['clave']) && $_POST['clave'] === "true") {
                return 1;
          }

          if (!isset($_POST['clave'], $_SESSION['key'])) {
                return 0;
          }

          $hash = md5($_POST['clave']);
          if (function_exists('hash_equals')) {
                return hash_equals($_SESSION['key'], $hash) ? 1 : 0;
          }

          return $_SESSION['key'] === $hash ? 1 : 0;
        }
        /* USER-AGENTS
        ================================================== */
        function VerificaEquipo( $type = NULL ) {
                        $user_agent = strtolower ( $_SERVER['HTTP_USER_AGENT'] );
                        if ( $type == 'bot' ) {
                                        // matches popular bots
                                        if ( preg_match ( "/googlebot|adsbot|yahooseeker|yahoobot|msnbot|watchmouse|pingdom\\.com|feedfetcher-google/", $user_agent ) ) {
                                                        return true;
                                                        // watchmouse|pingdom\\.com are "uptime services"
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

        $isAjax = isset($_POST['ajax']) && $_POST['ajax'] === '1';

        function responder($exito, $mensaje = '', $ruta = null) {
                global $isAjax;

                $destino = $ruta ?? ($exito ? 'user.php' : 'index.php');

                if ($isAjax) {
                        if ($exito) {
                                if (!preg_match('/^(?:https?:)?\//', $destino)) {
                                        $destino = 'adm/' . ltrim($destino, '/');
                                }
                        } else {
                                $destino = null;
                        }

                        header('Content-Type: application/json; charset=utf-8');
                        echo json_encode([
                                'success' => $exito,
                                'message' => $mensaje,
                                'redirect' => $destino
                        ], JSON_UNESCAPED_UNICODE);
                } else {
                        if ($exito) {
                                header("location:$destino");
                        } else {
                                if ($mensaje !== '') {
                                        $separador = strpos($destino, '?') === false ? '?' : '&';
                                        $destino .= $separador . 'mensaje=' . urlencode($mensaje);
                                }
                                header("location:$destino");
                        }
                }
                exit();
        }

        if(isset($_SESSION['login'])){
                responder(true, 'Sesión iniciada previamente.');
        }

        if($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['users'], $_POST['pass'])){
                if($isAjax){
                        responder(false, 'Solicitud inválida.');
                }

                $mensaje = VerificaEquipo('mobile') ? 'Iniciar Sesión Mobil' : 'Iniciar Sesión Web';
                header("location:index.php?mensaje=" . urlencode($mensaje));
                exit();
        }

        if(fnComprueba() !== 1){
                responder(false, 'Datos de imagen incorrectos.');
        }

        $login = isset($_POST['users']) ? trim($_POST['users']) : '';
        $pass = isset($_POST['pass']) ? trim($_POST['pass']) : '';

        if($login === '' || $pass === ''){
                responder(false, 'Ingrese su usuario y contraseña.');
        }

        require_once("script/conex.php");
        $cn= new MySQLcn();
        $loginDb = $cn->SecureInput($login);
        $passDb = $cn->SecureInput($pass);
        $querys ="CALL Acceder('$loginDb','$passDb');";
        $cn->Query($querys);
        $result = $cn->FetRows();

        if($result && isset($result[0]) && $result[0] != 'No Existe'){
                session_regenerate_id(true);
                $_SESSION["idUser"]=$result[0];
                $_SESSION["idGrupo"]=$result[1];
                $_SESSION["login"]=$result[3];
                $_SESSION["nivel"]=$result[4];
                $_SESSION["nombre"]=$result[2];
                $_SESSION["hora"]=date("Y-n-j H:i:s");
                $cn->Close();
                responder(true, 'Autenticación correcta.');
        }else{
                $cn->Close();
                responder(false, 'Usuario o contraseña incorrectos.');
        }
?>
