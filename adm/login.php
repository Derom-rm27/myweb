<?php
        //========================================================================//
        //  PROYEC : ADMINISTRADOR DE CONTENIDOS WEB
        //      AUTOR  : JUAN CARLOS PINTO LARICO
        //      FECHA  : JULIO   2021
        //      VERSION: 1.0.0
        //  E-MAIL : jcpintol@hotmail.com
        //========================================================================//
        function fnComprueba(){
          if (!isset($_POST['clave']) || !isset($_SESSION['key'])) {
                return 0;
          }

          $valor = strtolower(trim($_POST['clave']));
          if ($valor === '') {
                return 0;
          }

          $hash = md5($valor);
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

        unset($_SESSION['key']);

        $login = isset($_POST['users']) ? trim($_POST['users']) : '';
        $pass = isset($_POST['pass']) ? trim($_POST['pass']) : '';

        if($login === '' || $pass === ''){
                responder(false, 'Ingrese su usuario y contraseña.');
        }

        require_once("script/conex.php");
        $cn= new MySQLcn();
        $link = $cn->GetLink();

        $sql = "SELECT u.usersId, u.grupoId, u.nombres, u.users, u.nivel, u.estado
                 FROM usuarios u
                 INNER JOIN grupos g ON g.grupoId = u.grupoId
                 WHERE u.users = ?
                   AND u.clave = ?
                   AND g.fechaFinal > NOW()
                 LIMIT 1";

        $stmt = $link->prepare($sql);
        if(!$stmt){
                $cn->Close();
                responder(false, 'No fue posible validar las credenciales.');
        }

        $stmt->bind_param('ss', $login, $pass);

        if(!$stmt->execute()){
                $stmt->close();
                $cn->Close();
                responder(false, 'No fue posible validar las credenciales.');
        }

        $resultado = $stmt->get_result();
        $datos = $resultado ? $resultado->fetch_assoc() : null;

        $stmt->close();
        $cn->Close();

        if($datos){
                if((int)$datos['estado'] !== 1){
                        responder(false, 'Su cuenta está deshabilitada. Comuníquese con su administrador.');
                }

                session_regenerate_id(true);
                $_SESSION["idUser"]=$datos['usersId'];
                $_SESSION["idGrupo"]=$datos['grupoId'];
                $_SESSION["login"]=$datos['users'];
                $_SESSION["nivel"]=$datos['nivel'];
                $_SESSION["nombre"]=$datos['nombres'];
                $_SESSION["hora"]=date("Y-n-j H:i:s");
                responder(true, 'Autenticación correcta.');
        }

        responder(false, 'Usuario o contraseña incorrectos.');
?>
