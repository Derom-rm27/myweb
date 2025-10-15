<?php
session_start();

if (!isset($_SESSION['login'])) {
    header('Location: login.php');
    exit();
}

$nivelUsuario = (int) ($_SESSION['nivel'] ?? 0);
if (!in_array($nivelUsuario, [1, 3], true)) {
    $mensaje = urlencode('No tiene permisos para gestionar noticias.');
    header("Location: news.php?error={$mensaje}");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: news.php');
    exit();
}

require_once 'script/conex.php';

$titulo    = trim($_POST['titulo'] ?? '');
$contenido = trim($_POST['contenido'] ?? '');
$enlace    = trim($_POST['enlace'] ?? '');
$estado    = isset($_POST['estado']) ? 1 : 0;
$fecha     = date('Y-m-d H:i:s');
$usersId   = (int) ($_SESSION['idUser'] ?? 0);

if ($usersId <= 0) {
    header('Location: news.php?error=' . urlencode('No se pudo determinar el usuario autenticado.'));
    exit();
}

if ($titulo === '' || $contenido === '') {
    header('Location: news.php?error=' . urlencode('Debe completar el título y el contenido de la noticia.'));
    exit();
}

if ($enlace !== '' && filter_var($enlace, FILTER_VALIDATE_URL) === false) {
    header('Location: news.php?error=' . urlencode('La URL proporcionada no es válida.'));
    exit();
}

$imagenNombre = null;

if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
        header('Location: news.php?error=' . urlencode('No se pudo cargar la imagen seleccionada.'));
        exit();
    }

    $archivo   = $_FILES['imagen'];
    $tipo      = $archivo['type'];
    $tamano    = (int) $archivo['size'];
    $temporal  = $archivo['tmp_name'];

    $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($tipo, $tiposPermitidos, true)) {
        header('Location: news.php?error=' . urlencode('Formato de imagen inválido. Solo se permiten JPG, PNG o GIF.'));
        exit();
    }

    if ($tamano > 5 * 1024 * 1024) {
        header('Location: news.php?error=' . urlencode('La imagen supera el tamaño máximo permitido de 5MB.'));
        exit();
    }

    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    if ($extension === '') {
        $extension = $tipo === 'image/png' ? 'png' : ($tipo === 'image/gif' ? 'gif' : 'jpg');
    }

    try {
        $nombreBase = 'news_' . date('YmdHis') . '_' . bin2hex(random_bytes(3));
    } catch (Exception $exception) {
        header('Location: news.php?error=' . urlencode('No se pudo generar un nombre para la imagen.'));
        exit();
    }

    $imagenNombre = $nombreBase . '.' . $extension;
    $directorio   = realpath(__DIR__ . '/../images/news');

    if ($directorio === false) {
        $directorio = __DIR__ . '/../images/news';
    }

    if (!is_dir($directorio)) {
        if (!mkdir($directorio, 0777, true) && !is_dir($directorio)) {
            header('Location: news.php?error=' . urlencode('No fue posible preparar la carpeta de noticias.'));
            exit();
        }
    }

    $destino = rtrim($directorio, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $imagenNombre;

    if (!move_uploaded_file($temporal, $destino)) {
        header('Location: news.php?error=' . urlencode('No se pudo guardar la imagen en el servidor.'));
        exit();
    }
}

$cn = new MySQLcn();

$tituloDb    = $cn->SecureInput($titulo);
$contenidoDb = $cn->SecureInput($contenido);
$enlaceDb    = $enlace !== '' ? "'" . $cn->SecureInput($enlace) . "'" : 'NULL';
$imagenDb    = $imagenNombre !== null ? "'" . $cn->SecureInput($imagenNombre) . "'" : 'NULL';

$sql = "INSERT INTO noticias (usersId, titulo, cuerpo, imagen, enlace, estado, fecha)
        VALUES ('$usersId', '$tituloDb', '$contenidoDb', $imagenDb, $enlaceDb, '$estado', '$fecha')";

try {
    $cn->InsertaDb($sql);
    $cn->Close();
} catch (Throwable $throwable) {
    $cn->Close();
    if ($imagenNombre !== null) {
        $rutaImagen = __DIR__ . '/../images/news/' . $imagenNombre;
        if (file_exists($rutaImagen)) {
            unlink($rutaImagen);
        }
    }
    header('Location: news.php?error=' . urlencode('Ocurrió un problema al guardar la noticia.'));
    exit();
}

header('Location: news.php?mensaje=' . urlencode('La noticia se publicó correctamente.'));
exit();
