<?php
session_start();

if (!isset($_SESSION['login'])) {
    header('Location: login.php');
    exit();
}

$nivelUsuario = (int) ($_SESSION['nivel'] ?? 0);
if ($nivelUsuario !== 1) {
    $mensaje = urlencode('No tiene permisos para crear nuevos usuarios.');
    header("Location: user.php?error={$mensaje}");
    exit();
}

require_once __DIR__ . '/../includes/repositories/PermissionRepository.php';

$usuarioId        = (int) ($_SESSION['idUser'] ?? 0);
$nombreUsuario    = $_SESSION['nombre'] ?? 'Superusuario';
$errors           = [];
$successMessage   = '';
$fullName         = '';
$username         = '';
$email            = '';
$perfil           = '';
$nivelSeleccionado = '2';

$canAccessManageBanners = true;
$canAccessManageNews    = true;

$permissionConnection = null;
try {
    $permissionConnection = new MySQLcn();
    $permissionRepository = new PermissionRepository($permissionConnection);
    $canAccessManageBanners = $permissionRepository->userCanManageBanners($usuarioId);
    $canAccessManageNews    = $permissionRepository->userCanManageNews($usuarioId);
} catch (Throwable $exception) {
    $permissionRepository = null;
}

if ($permissionConnection !== null) {
    $permissionConnection->Close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim((string) ($_POST['nombres'] ?? ''));
    $username = trim((string) ($_POST['users'] ?? ''));
    $email    = trim((string) ($_POST['email'] ?? ''));
    $password = trim((string) ($_POST['pass'] ?? ''));
    $confirm  = trim((string) ($_POST['confirm_pass'] ?? ''));
    $nivel    = isset($_POST['nivel']) ? (int) $_POST['nivel'] : 0;
    $perfil   = trim((string) ($_POST['perfil'] ?? ''));

    $nivelSeleccionado = (string) $nivel;

    if ($fullName === '' || mb_strlen($fullName) < 3) {
        $errors[] = 'Ingresa un nombre completo válido (mínimo 3 caracteres).';
    }

    if ($username === '' || mb_strlen($username) < 4) {
        $errors[] = 'Ingresa un nombre de usuario válido (mínimo 4 caracteres).';
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Ingresa un correo electrónico válido.';
    }

    if (!in_array($nivel, [1, 2, 3], true)) {
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

            $statement = $link->prepare('SELECT usersId FROM usuarios WHERE users = ? LIMIT 1');
            if ($statement === false) {
                $errors[] = 'No fue posible validar el usuario. Intenta más tarde.';
            } else {
                $statement->bind_param('s', $username);
                $statement->execute();
                $statement->store_result();

                if ($statement->num_rows > 0) {
                    $errors[] = 'El nombre de usuario ya está registrado. Elige otro.';
                }

                $statement->close();
            }

            if ($email !== '' && empty($errors)) {
                $statement = $link->prepare('SELECT usersId FROM usuarios WHERE email = ? LIMIT 1');
                if ($statement === false) {
                    $errors[] = 'No fue posible validar el correo electrónico. Intenta más tarde.';
                } else {
                    $statement->bind_param('s', $email);
                    $statement->execute();
                    $statement->store_result();

                    if ($statement->num_rows > 0) {
                        $errors[] = 'El correo electrónico ya está registrado. Usa otro.';
                    }

                    $statement->close();
                }
            }

            if (empty($errors)) {
                $grupoId      = 1;
                $estadoActivo = 1;

                $insertStatement = $link->prepare(
                    'INSERT INTO usuarios (grupoId, nombres, users, clave, nivel, estado, email, perfil, fechaCreada) ' .
                    'VALUES (?, ?, ?, ?, ?, ?, NULLIF(?, \'\'), NULLIF(?, \'\'), NOW())'
                );

                if ($insertStatement === false) {
                    $errors[] = 'No fue posible registrar al usuario en este momento.';
                } else {
                    $insertStatement->bind_param(
                        'isssiiss',
                        $grupoId,
                        $fullName,
                        $username,
                        $password,
                        $nivel,
                        $estadoActivo,
                        $email,
                        $perfil
                    );

                    if ($insertStatement->execute()) {
                        $successMessage = 'Usuario creado correctamente.';
                        $fullName = $username = $email = $perfil = '';
                        $nivelSeleccionado = '2';
                    } else {
                        $errors[] = 'Ocurrió un problema al guardar los datos. Inténtalo nuevamente.';
                    }

                    $insertStatement->close();
                }
            }

            $database->Close();
        } catch (Throwable $exception) {
            $errors[] = 'Ocurrió un error inesperado. Inténtalo nuevamente más tarde.';
        }
    }
}

$nivelesDisponibles = [
    ['valor' => '1', 'etiqueta' => 'Nivel 1 - Superadministrador'],
    ['valor' => '2', 'etiqueta' => 'Nivel 2 - Puede subir banners'],
    ['valor' => '3', 'etiqueta' => 'Nivel 3 - Puede publicar noticias'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard-theme.css">
</head>
<body class="dashboard-body">
<nav class="navbar navbar-expand-lg dashboard-navbar">
    <div class="container">
        <a class="navbar-brand" href="#">Panel de Control</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="user.php">Subir banner</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="news.php">Publicar noticia</a>
                </li>
                <li class="nav-item">
                    <?php if ($canAccessManageBanners): ?>
                        <a class="nav-link" href="manage_banners.php">Gestionar banners</a>
                    <?php else: ?>
                        <a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true">Gestionar banners</a>
                    <?php endif; ?>
                </li>
                <li class="nav-item">
                    <?php if ($canAccessManageNews): ?>
                        <a class="nav-link" href="manage_news.php">Gestionar noticias</a>
                    <?php else: ?>
                        <a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true">Gestionar noticias</a>
                    <?php endif; ?>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <span class="user-info">
                        <i class="fas fa-user me-2"></i><?php echo htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cog me-2"></i>Opciones
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="../index.php">
                                <i class="fas fa-home me-2"></i>Página Principal
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="cambiar_password.php">
                                <i class="fas fa-key me-2"></i>Cambiar Contraseña
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="permissions.php">
                                <i class="fas fa-user-shield me-2"></i>Otorgar permisos
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="create_user.php">
                                <i class="fas fa-user-plus me-2"></i>Crear usuario
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="visit_logs.php">
                                <i class="fas fa-chart-bar me-2"></i>Registro de visitas
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="dashboard-main">
    <div class="container">
        <?php if ($successMessage !== ''): ?>
            <div class="alert alert-success alert-dismissible fade show dashboard-alert" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show dashboard-alert" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <ul class="mb-0 ps-3">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="dashboard-card card">
            <div class="card-header">
                <h4 class="dashboard-section-title mb-0">Crear nuevo usuario</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="create_user.php">
                    <div class="mb-3">
                        <label for="nombres" class="form-label">Nombre completo</label>
                        <input type="text" class="form-control" id="nombres" name="nombres" value="<?php echo htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="users" class="form-label">Nombre de usuario</label>
                        <input type="text" class="form-control" id="users" name="users" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="form-text">Opcional, pero recomendado para notificaciones.</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="pass" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="pass" name="pass" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="confirm_pass" class="form-label">Confirmar contraseña</label>
                            <input type="password" class="form-control" id="confirm_pass" name="confirm_pass" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="nivel" class="form-label">Nivel de acceso</label>
                        <select class="form-select" id="nivel" name="nivel" required>
                            <?php foreach ($nivelesDisponibles as $nivel): ?>
                                <option value="<?php echo $nivel['valor']; ?>" <?php echo $nivelSeleccionado === $nivel['valor'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($nivel['etiqueta'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="perfil" class="form-label">Perfil o rol</label>
                        <input type="text" class="form-control" id="perfil" name="perfil" value="<?php echo htmlspecialchars($perfil, ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="form-text">Opcional. Útil para identificar el área del usuario.</div>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Crear usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
</body>
</html>
