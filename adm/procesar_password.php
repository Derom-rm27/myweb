<?php
session_start();

if (!isset($_SESSION['login'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cambiar_password.php');
    exit();
}

require_once 'script/conex.php';

$usuarioId        = (int) ($_SESSION['idUser'] ?? 0);
$passwordActual   = trim((string) ($_POST['password_actual'] ?? ''));
$passwordNueva    = trim((string) ($_POST['password_nueva'] ?? ''));
$passwordConfirma = trim((string) ($_POST['password_confirmar'] ?? ''));

if ($usuarioId <= 0) {
    header('Location: cambiar_password.php?error=' . urlencode('No se pudo identificar al usuario autenticado.'));
    exit();
}

if ($passwordActual === '' || $passwordNueva === '' || $passwordConfirma === '') {
    header('Location: cambiar_password.php?error=' . urlencode('Debe completar todos los campos del formulario.'));
    exit();
}

if ($passwordNueva !== $passwordConfirma) {
    header('Location: cambiar_password.php?error=' . urlencode('Las contraseñas nuevas no coinciden.'));
    exit();
}

if (mb_strlen($passwordNueva) < 6) {
    header('Location: cambiar_password.php?error=' . urlencode('La nueva contraseña debe tener al menos 6 caracteres.'));
    exit();
}

$conexion = new MySQLcn();
$enlace   = $conexion->GetLink();

$consulta = $enlace->prepare('SELECT 1 FROM usuarios WHERE usersId = ? AND clave = ? LIMIT 1');
if (!$consulta) {
    $conexion->Close();
    header('Location: cambiar_password.php?error=' . urlencode('No fue posible validar la contraseña actual.'));
    exit();
}

$consulta->bind_param('is', $usuarioId, $passwordActual);

if (!$consulta->execute()) {
    $consulta->close();
    $conexion->Close();
    header('Location: cambiar_password.php?error=' . urlencode('No fue posible validar la contraseña actual.'));
    exit();
}

$resultado = $consulta->get_result();

if (!$resultado || $resultado->num_rows === 0) {
    $consulta->close();
    $conexion->Close();
    header('Location: cambiar_password.php?error=' . urlencode('La contraseña actual no es correcta.'));
    exit();
}

$consulta->close();

$actualizacion = $enlace->prepare('UPDATE usuarios SET clave = ? WHERE usersId = ? LIMIT 1');
if (!$actualizacion) {
    $conexion->Close();
    header('Location: cambiar_password.php?error=' . urlencode('No fue posible actualizar la contraseña.'));
    exit();
}

$actualizacion->bind_param('si', $passwordNueva, $usuarioId);

if (!$actualizacion->execute()) {
    $actualizacion->close();
    $conexion->Close();
    header('Location: cambiar_password.php?error=' . urlencode('No fue posible actualizar la contraseña.'));
    exit();
}

$actualizacion->close();
$conexion->Close();

header('Location: cambiar_password.php?mensaje=' . urlencode('La contraseña se cambió con éxito.'));
exit();
