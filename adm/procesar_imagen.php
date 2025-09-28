<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['login'])) {
    header('Location: login.php');
    exit();
}

// Incluir archivo de conexión
require_once("script/conex.php");

// Verificar si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $enlace = trim($_POST['link']);
    $estado = isset($_POST['estado']) ? 1 : 0;
    $fecha = date('Y-m-d H:i:s');
    $usersId = $_SESSION['idUser'];
    
    // Sanitizar datos para prevenir SQL injection
    $titulo = addslashes($titulo);
    $descripcion = addslashes($descripcion);
    $enlace = addslashes($enlace);
    
    // Verificar si se subió una imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $imagen = $_FILES['imagen'];
        $nombre_archivo = $imagen['name'];
        $tipo_archivo = $imagen['type'];
        $tamano_archivo = $imagen['size'];
        $archivo_temporal = $imagen['tmp_name'];
        
        // Verificar el tipo de archivo
        $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($tipo_archivo, $tipos_permitidos)) {
            die('Error: Solo se permiten archivos JPG, PNG y GIF.');
        }
        
        // Verificar el tamaño del archivo (máximo 5MB)
        if ($tamano_archivo > 5 * 1024 * 1024) {
            die('Error: El archivo es demasiado grande. Máximo 5MB permitido.');
        }
        
        // Crear directorio de banner si no existe
        $directorio_banner = '../images/banner/';
        if (!file_exists($directorio_banner)) {
            mkdir($directorio_banner, 0777, true);
        }

        // Obtener el siguiente número de banner
        $cn = new MySQLcn();
        $sql = "SELECT MAX(CAST(SUBSTRING_INDEX(Imagen, '_', -1) AS UNSIGNED)) as max_num FROM banner WHERE Imagen LIKE 'banner_%'";
        $cn->Query($sql);
        $result = $cn->FetRows();
        $next_num = ($result[0] === null) ? 1 : $result[0] + 1;
        $cn->Close();

        // Generar nombre único para el archivo
        $extension = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));
        $nuevo_nombre = 'banner_' . $next_num . '.' . $extension;
        $ruta_destino = $directorio_banner . $nuevo_nombre;

        // Redimensionar la imagen si es necesario
        list($ancho_orig, $alto_orig) = getimagesize($archivo_temporal);
        $max_ancho = 1920;
        $max_alto = 1080;

        // Calcular nuevas dimensiones manteniendo la proporción
        if ($ancho_orig > $max_ancho || $alto_orig > $max_alto) {
            $ratio_orig = $ancho_orig / $alto_orig;
            if ($max_ancho / $max_alto > $ratio_orig) {
                $max_ancho = $max_alto * $ratio_orig;
            } else {
                $max_alto = $max_ancho / $ratio_orig;
            }
        } else {
            $max_ancho = $ancho_orig;
            $max_alto = $alto_orig;
        }

        // Crear imagen según el tipo
        switch($tipo_archivo) {
            case 'image/jpeg':
                $imagen_orig = imagecreatefromjpeg($archivo_temporal);
                break;
            case 'image/png':
                $imagen_orig = imagecreatefrompng($archivo_temporal);
                break;
            case 'image/gif':
                $imagen_orig = imagecreatefromgif($archivo_temporal);
                break;
            default:
                die('Error: Formato de imagen no soportado.');
        }

        // Crear nueva imagen
        $imagen_nueva = imagecreatetruecolor($max_ancho, $max_alto);

        // Preservar transparencia para PNG
        if ($tipo_archivo == 'image/png') {
            imagealphablending($imagen_nueva, false);
            imagesavealpha($imagen_nueva, true);
        }

        // Redimensionar
        imagecopyresampled($imagen_nueva, $imagen_orig, 0, 0, 0, 0, $max_ancho, $max_alto, $ancho_orig, $alto_orig);

        // Guardar la imagen
        switch($tipo_archivo) {
            case 'image/jpeg':
                imagejpeg($imagen_nueva, $ruta_destino, 80);
                break;
            case 'image/png':
                imagepng($imagen_nueva, $ruta_destino, 6);
                break;
            case 'image/gif':
                imagegif($imagen_nueva, $ruta_destino);
                break;
        }

        // Liberar memoria
        imagedestroy($imagen_orig);
        imagedestroy($imagen_nueva);

        try {
            // Crear instancia de conexión
            $cn = new MySQLcn();
            
            // Preparar la consulta SQL
            $sql = "INSERT INTO banner (usersId, Titulo, Describir, Enlace, Imagen, estado, fecha) 
                    VALUES ('$usersId', '$titulo', '$descripcion', '$enlace', '$nuevo_nombre', '$estado', '$fecha')";
            
            // Ejecutar la consulta usando InsertaDb
            $cn->InsertaDb($sql);
            
            // Cerrar la conexión
            $cn->Close();
            
            // Redirigir con mensaje de éxito
            header('Location: user.php?mensaje=¡La imagen se ha subido exitosamente! El banner ha sido creado y guardado en la base de datos.');
            exit();
        } catch (Exception $e) {
            // Si hay error, eliminar la imagen subida
            if (file_exists($ruta_destino)) {
                unlink($ruta_destino);
            }
            die('Error al guardar en la base de datos: ' . $e->getMessage());
        }
    } else {
        die('Error: No se subió ninguna imagen.');
    }
} else {
    // Si alguien intenta acceder directamente a este archivo
    header('Location: user.php');
    exit();
}
?> 