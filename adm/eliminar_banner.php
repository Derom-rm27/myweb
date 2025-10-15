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
    header('Location: banners.php?error=' . urlencode('No tienes permisos para eliminar este banner.'));
    exit();
}

$cn->UpdateDb("DELETE FROM banner WHERE idBanner = $idBanner LIMIT 1");
$cn->Close();

if ($currentImage) {
    $rutaImagen = __DIR__ . '/../images/banner/' . $currentImage;
    if (is_file($rutaImagen)) {
        @unlink($rutaImagen);
    }
}

header('Location: banners.php?mensaje=' . urlencode('El banner se eliminó correctamente.'));
exit();
