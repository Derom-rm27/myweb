<?php
session_start();

if (!isset($_SESSION['login'])) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/script/conex.php';

$nivelUsuario = (int)($_SESSION['nivel'] ?? 0);
if (!in_array($nivelUsuario, [1, 2], true)) {
    header('Location: news.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: banners.php');
    exit();
}

$idBanner = isset($_POST['idBanner']) ? (int)$_POST['idBanner'] : 0;
if ($idBanner <= 0) {
    header('Location: banners.php?error=' . urlencode('Identificador de banner inválido.'));
    exit();
}

$titulo = trim($_POST['titulo'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$enlace = trim($_POST['enlace'] ?? '');
$estado = isset($_POST['estado']) ? 1 : 0;

if ($titulo === '' || $descripcion === '') {
    header('Location: banners.php?error=' . urlencode('Completa todos los campos obligatorios.'));
    exit();
}

$cn = new MySQLcn();

$cn->Query("SELECT usersId, Imagen FROM banner WHERE idBanner = $idBanner LIMIT 1");
if ($cn->NumRows() === 0) {
    $cn->Close();
    header('Location: banners.php?error=' . urlencode('El banner indicado no existe.'));
    exit();
}

$bannerData = $cn->Rows()[0];
$ownerId = (int)($bannerData['usersId'] ?? 0);
$currentImage = $bannerData['Imagen'] ?? '';

$usuarioId = (int)($_SESSION['idUser'] ?? 0);
if ($nivelUsuario !== 1 && $ownerId !== $usuarioId) {
    $cn->Close();
    header('Location: banners.php?error=' . urlencode('No tienes permisos para editar este banner.'));
    exit();
}

$tituloDb = $cn->SecureInput($titulo);
$descripcionDb = $cn->SecureInput($descripcion);
$enlaceDb = $cn->SecureInput($enlace);

$nuevoNombre = null;
$tipoArchivo = '';

if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
    $archivo = $_FILES['imagen'];

    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        $cn->Close();
        header('Location: banners.php?error=' . urlencode('Hubo un problema al subir la imagen. Inténtalo nuevamente.'));
        exit();
    }

    $tipoArchivo = $archivo['type'];
    $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($tipoArchivo, $tiposPermitidos, true)) {
        $cn->Close();
        header('Location: banners.php?error=' . urlencode('Solo se permiten imágenes JPG, PNG o GIF.'));
        exit();
    }

    if ($archivo['size'] > 5 * 1024 * 1024) {
        $cn->Close();
        header('Location: banners.php?error=' . urlencode('La imagen supera el tamaño máximo de 5MB.'));
        exit();
    }

    $directorio = __DIR__ . '/../images/banner/';
    if (!is_dir($directorio) && !mkdir($directorio, 0777, true) && !is_dir($directorio)) {
        $cn->Close();
        header('Location: banners.php?error=' . urlencode('No se pudo preparar la carpeta de destino.'));
        exit();
    }

    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    if ($extension === '') {
        switch ($tipoArchivo) {
            case 'image/jpeg':
                $extension = 'jpg';
                break;
            case 'image/png':
                $extension = 'png';
                break;
            case 'image/gif':
                $extension = 'gif';
                break;
        }
    }

    $nuevoNombre = 'banner_' . time() . '_' . mt_rand(1000, 9999) . '.' . $extension;
    $rutaDestino = $directorio . $nuevoNombre;

    list($anchoOrig, $altoOrig) = getimagesize($archivo['tmp_name']);
    $maxAncho = 1920;
    $maxAlto = 1080;

    if ($anchoOrig > $maxAncho || $altoOrig > $maxAlto) {
        $ratioOrig = $anchoOrig / $altoOrig;
        if ($maxAncho / $maxAlto > $ratioOrig) {
            $maxAncho = (int)round($maxAlto * $ratioOrig);
        } else {
            $maxAlto = (int)round($maxAncho / $ratioOrig);
        }
    } else {
        $maxAncho = $anchoOrig;
        $maxAlto = $altoOrig;
    }

    switch ($tipoArchivo) {
        case 'image/jpeg':
            $imagenOriginal = imagecreatefromjpeg($archivo['tmp_name']);
            break;
        case 'image/png':
            $imagenOriginal = imagecreatefrompng($archivo['tmp_name']);
            break;
        case 'image/gif':
            $imagenOriginal = imagecreatefromgif($archivo['tmp_name']);
            break;
        default:
            $cn->Close();
            header('Location: banners.php?error=' . urlencode('Formato de imagen no soportado.'));
            exit();
    }

    if (!$imagenOriginal) {
        $cn->Close();
        header('Location: banners.php?error=' . urlencode('No se pudo procesar la imagen subida.'));
        exit();
    }

    $imagenRedimensionada = imagecreatetruecolor($maxAncho, $maxAlto);

    if ($tipoArchivo === 'image/png') {
        imagealphablending($imagenRedimensionada, false);
        imagesavealpha($imagenRedimensionada, true);
    }

    imagecopyresampled($imagenRedimensionada, $imagenOriginal, 0, 0, 0, 0, $maxAncho, $maxAlto, $anchoOrig, $altoOrig);

    switch ($tipoArchivo) {
        case 'image/jpeg':
            imagejpeg($imagenRedimensionada, $rutaDestino, 80);
            break;
        case 'image/png':
            imagepng($imagenRedimensionada, $rutaDestino, 6);
            break;
        case 'image/gif':
            imagegif($imagenRedimensionada, $rutaDestino);
            break;
    }

    imagedestroy($imagenOriginal);
    imagedestroy($imagenRedimensionada);

    if ($currentImage) {
        $rutaAnterior = __DIR__ . '/../images/banner/' . $currentImage;
        if (is_file($rutaAnterior)) {
            @unlink($rutaAnterior);
        }
    }
}

$setImagen = $nuevoNombre !== null ? ", Imagen = '$nuevoNombre'" : '';
$fechaActual = date('Y-m-d H:i:s');
$updateSql = "UPDATE banner SET Titulo = '$tituloDb', Describir = '$descripcionDb', Enlace = '$enlaceDb', estado = $estado, fecha = '$fechaActual' $setImagen WHERE idBanner = $idBanner";
$cn->UpdateDb($updateSql);
$cn->Close();

header('Location: banners.php?mensaje=' . urlencode('El banner se actualizó correctamente.'));
exit();
