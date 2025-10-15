<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../adm/script/conex.php';

$errors = [];
$successMessage = '';

$fullName          = '';
$username          = '';
$email             = '';
$nivelSeleccionado = '2';
$perfil            = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim((string)($_POST['nombres'] ?? ''));
    $username = trim((string)($_POST['users'] ?? ''));
    $email    = trim((string)($_POST['email'] ?? ''));
    $password = trim((string)($_POST['pass'] ?? ''));
    $confirm  = trim((string)($_POST['confirm_pass'] ?? ''));
    $nivel    = isset($_POST['nivel']) ? (int)$_POST['nivel'] : 0;
    $perfil   = trim((string)($_POST['perfil'] ?? ''));

    $nivelSeleccionado = (string)$nivel;

    if ($fullName === '' || mb_strlen($fullName) < 3) {
        $errors[] = 'Ingresa un nombre completo válido (mínimo 3 caracteres).';
    }

    if ($username === '' || mb_strlen($username) < 4) {
        $errors[] = 'Ingresa un nombre de usuario válido (mínimo 4 caracteres).';
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Ingresa un correo electrónico válido.';
    }

    if (!in_array($nivel, [2, 3], true)) {
        $errors[] = 'Selecciona un nivel válido.';
    }

    if ($password === '' || mb_strlen($password) < 6) {
        $errors[] = 'La contraseña debe tener al menos 6 caracteres.';
    }

    if ($password !== $confirm) {
        $errors[] = 'Las contraseñas no coinciden.';
    }

    if (empty($errors)) {
        try {
            $database = new MySQLcn();
            $link     = $database->GetLink();

            $stmt = $link->prepare('SELECT usersId FROM usuarios WHERE users = ? LIMIT 1');
            if ($stmt) {
                $stmt->bind_param('s', $username);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $errors[] = 'El nombre de usuario ya está registrado. Elige otro.';
                }

                $stmt->close();
            } else {
                $errors[] = 'No fue posible validar el usuario. Intenta más tarde.';
            }

            if ($email !== '' && empty($errors)) {
                $stmt = $link->prepare('SELECT usersId FROM usuarios WHERE email = ? LIMIT 1');
                if ($stmt) {
                    $stmt->bind_param('s', $email);
                    $stmt->execute();
                    $stmt->store_result();

                    if ($stmt->num_rows > 0) {
                        $errors[] = 'El correo electrónico ya está registrado. Usa otro.';
                    }

                    $stmt->close();
                }
            }

            if (empty($errors)) {
                $grupoId        = 1;
                $estadoActivo   = 1;
                $emailToStore   = $email;
                $perfilToStore  = $perfil;

                $stmt = $link->prepare('INSERT INTO usuarios (grupoId, nombres, users, clave, nivel, estado, email, perfil, fechaCreada) VALUES (?, ?, ?, ?, ?, ?, NULLIF(?, \'\'), NULLIF(?, \'\'), NOW())');

                if (!$stmt) {
                    $errors[] = 'No fue posible registrar al usuario en este momento.';
                } else {
                    $stmt->bind_param(
                        'isssiiss',
                        $grupoId,
                        $fullName,
                        $username,
                        $password,
                        $nivel,
                        $estadoActivo,
                        $emailToStore,
                        $perfilToStore
                    );

                    if ($stmt->execute()) {
                        $successMessage = 'Cuenta creada correctamente. Ya puedes iniciar sesión.';
                        $fullName = $username = $email = $perfil = '';
                        $nivelSeleccionado = '2';
                    } else {
                        $errors[] = 'Ocurrió un problema al guardar tus datos. Inténtalo nuevamente.';
                    }

                    $stmt->close();
                }
            }

            $database->Close();
        } catch (Throwable $exception) {
            $errors[] = 'Ocurrió un error inesperado. Inténtalo nuevamente más tarde.';
        }
    }
}

require __DIR__ . '/templates/register-form.php';
