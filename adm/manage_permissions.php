<?php
session_start();

if (!isset($_SESSION['login'])) {
    header('Location: login.php');
    exit();
}

$nivelUsuario = (int) ($_SESSION['nivel'] ?? 0);
if ($nivelUsuario !== 1) {
    header('Location: user.php');
    exit();
}

$usuarioId = (int) ($_SESSION['idUser'] ?? 0);
$nombreUsuario = $_SESSION['nombre'] ?? 'Usuario';

require_once __DIR__ . '/../includes/repositories/PermissionRepository.php';

$flashMessage = isset($_GET['mensaje']) ? trim((string) $_GET['mensaje']) : '';
$flashError   = isset($_GET['error']) ? trim((string) $_GET['error']) : '';
$createFormData = [
    'full_name' => '',
    'username' => '',
    'email' => '',
    'level' => '2',
];

try {
    $connection = new MySQLcn();
    $permissionRepository = new PermissionRepository($connection);
} catch (Throwable $exception) {
    $permissionRepository = null;
    $connection = null;
    $flashError = 'No fue posible conectar con la base de datos.';
}

$allowedResources = ['BANNERS', 'NEWS'];
$redirectBase = 'manage_permissions.php';

if ($permissionRepository !== null && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';

    if ($action === 'create_user') {
        $fullName = isset($_POST['full_name']) ? trim((string) $_POST['full_name']) : '';
        $username = isset($_POST['username']) ? trim((string) $_POST['username']) : '';
        $email    = isset($_POST['email']) ? trim((string) $_POST['email']) : '';
        $password = isset($_POST['password']) ? trim((string) $_POST['password']) : '';
        $confirm  = isset($_POST['confirm_password']) ? trim((string) $_POST['confirm_password']) : '';
        $level    = isset($_POST['level']) ? (int) $_POST['level'] : 0;

        $createFormData = [
            'full_name' => $fullName,
            'username' => $username,
            'email' => $email,
            'level' => (string) $level,
        ];

        $errors = [];

        if ($fullName === '' || mb_strlen($fullName) < 3) {
            $errors[] = 'Ingresa un nombre completo válido (mínimo 3 caracteres).';
        }

        if ($username === '' || mb_strlen($username) < 4) {
            $errors[] = 'Ingresa un nombre de usuario válido (mínimo 4 caracteres).';
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Ingresa un correo electrónico válido.';
        }

        if (!in_array($level, [1, 2, 3], true)) {
            $errors[] = 'Selecciona un nivel válido.';
        }

        if ($password === '' || mb_strlen($password) < 6) {
            $errors[] = 'La contraseña debe tener al menos 6 caracteres.';
        }

        if ($password !== $confirm) {
            $errors[] = 'Las contraseñas no coinciden.';
        }

        if (empty($errors)) {
            $result = $permissionRepository->createUserAsAdmin(
                $usuarioId,
                $fullName,
                $username,
                $password,
                $email,
                $level
            );

            if ($result['success']) {
                $connection?->Close();
                header("Location: {$redirectBase}?mensaje=" . urlencode($result['message']));
                exit();
            }

            $flashError = $result['message'];
        } else {
            $flashError = implode(' ', $errors);
        }
    } else {
        $targetUserId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
        $resource     = isset($_POST['resource']) ? strtoupper(trim((string) $_POST['resource'])) : '';
        $allow        = isset($_POST['allow']) && (string) $_POST['allow'] === '1';

        if ($targetUserId <= 0 || $targetUserId === $usuarioId) {
            $connection?->Close();
            header("Location: {$redirectBase}?error=" . urlencode('Debe seleccionar un usuario válido.'));
            exit();
        }

        if (!in_array($resource, $allowedResources, true)) {
            $connection?->Close();
            header("Location: {$redirectBase}?error=" . urlencode('Recurso seleccionado inválido.'));
            exit();
        }

        $updated = $permissionRepository->setManageAccess($targetUserId, $resource, $allow);
        $connection?->Close();

        if ($updated) {
            $mensaje = $allow ? 'Permiso concedido correctamente.' : 'Permiso revocado correctamente.';
            header("Location: {$redirectBase}?mensaje=" . urlencode($mensaje));
        } else {
            header("Location: {$redirectBase}?error=" . urlencode('No fue posible actualizar el permiso.'));
        }
        exit();
    }
}

$users = [];
if ($permissionRepository !== null) {
    $users = $permissionRepository->getManageableUsers($usuarioId);
    $connection->Close();
}

$levelLabels = [
    1 => 'Superusuario',
    2 => 'Banners',
    3 => 'Noticias',
];

$canUploadBanner     = in_array($nivelUsuario, [1, 2], true);
$canPublishNews      = in_array($nivelUsuario, [1, 3], true);
$canManageBanners    = ($nivelUsuario === 1);
$canManageNews       = ($nivelUsuario === 1);
$canGrantPermissions = ($nivelUsuario === 1);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar permisos</title>
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
                    <?php if ($canUploadBanner): ?>
                        <a class="nav-link" href="user.php">Subir banner</a>
                    <?php else: ?>
                        <a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true">Subir banner</a>
                    <?php endif; ?>
                </li>
                <li class="nav-item">
                    <?php if ($canPublishNews || $canManageNews): ?>
                        <a class="nav-link" href="news.php">Publicar noticia</a>
                    <?php else: ?>
                        <a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true">Publicar noticia</a>
                    <?php endif; ?>
                </li>
                <li class="nav-item">
                    <?php if ($canManageBanners): ?>
                        <a class="nav-link" href="manage_banners.php">Gestionar banners</a>
                    <?php else: ?>
                        <a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true">Gestionar banners</a>
                    <?php endif; ?>
                </li>
                <li class="nav-item">
                    <?php if ($canManageNews): ?>
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
                        <?php if ($nivelUsuario === 1): ?>
                            <li>
                                <a class="dropdown-item" href="permissions.php">
                                    <i class="fas fa-user-shield me-2"></i>Otorgar permisos
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="visit_logs.php">
                                    <i class="fas fa-chart-bar me-2"></i>Registro de visitas
                                </a>
                            </li>
                        <?php endif; ?>
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
        <?php if ($flashMessage !== ''): ?>
            <div class="alert alert-success alert-dismissible fade show dashboard-alert" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($flashMessage, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($flashError !== ''): ?>
            <div class="alert alert-danger alert-dismissible fade show dashboard-alert" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="dashboard-card card">
            <div class="card-header">
                <div>
                    <h4 class="dashboard-section-title mb-1">Gestión de permisos</h4>
                    <p class="dashboard-section-subtitle mb-0">Define quién puede administrar los banners y noticias del portal.</p>
                </div>
                <span class="badge bg-primary text-uppercase">Solo superusuarios</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($users)): ?>
                    <div class="dashboard-empty-state">
                        <i class="fas fa-users"></i>
                        <p class="mb-0">No hay otros usuarios registrados.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive dashboard-table-responsive">
                        <table class="table align-middle">
                            <thead>
                            <tr>
                                <th scope="col">Usuario</th>
                                <th scope="col">Nivel</th>
                                <th scope="col" class="text-center">Gestionar banners</th>
                                <th scope="col" class="text-center">Gestionar noticias</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($users as $user): ?>
                                <?php $userId = (int) ($user['usersId'] ?? 0); ?>
                                <tr>
                                    <td>
                                        <span class="fw-semibold d-block"><?php echo htmlspecialchars((string) ($user['nombres'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                                        <small class="text-muted">@<?php echo htmlspecialchars((string) ($user['users'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($levelLabels[(int) ($user['nivel'] ?? 0)] ?? 'Nivel desconocido', ENT_QUOTES, 'UTF-8'); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <form method="POST" class="permission-form d-inline">
                                            <input type="hidden" name="action" value="toggle_permission">
                                            <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                                            <input type="hidden" name="resource" value="BANNERS">
                                            <input type="hidden" name="allow" value="<?php echo !empty($user['can_manage_banners']) ? '1' : '0'; ?>">
                                            <div class="form-check form-switch d-inline-flex align-items-center justify-content-center">
                                                <input class="form-check-input permission-toggle" type="checkbox" role="switch" <?php echo !empty($user['can_manage_banners']) ? 'checked' : ''; ?>>
                                            </div>
                                        </form>
                                    </td>
                                    <td class="text-center">
                                        <form method="POST" class="permission-form d-inline">
                                            <input type="hidden" name="action" value="toggle_permission">
                                            <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                                            <input type="hidden" name="resource" value="NEWS">
                                            <input type="hidden" name="allow" value="<?php echo !empty($user['can_manage_news']) ? '1' : '0'; ?>">
                                            <div class="form-check form-switch d-inline-flex align-items-center justify-content-center">
                                                <input class="form-check-input permission-toggle" type="checkbox" role="switch" <?php echo !empty($user['can_manage_news']) ? 'checked' : ''; ?>>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="dashboard-card card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">Crear nuevo usuario</h5>
                    <p class="dashboard-section-subtitle mb-0">Registra usuarios adicionales y asigna su nivel de acceso.</p>
                </div>
                <span class="badge bg-success text-uppercase">Conectado a la base de datos</span>
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <input type="hidden" name="action" value="create_user">
                    <div class="col-md-6">
                        <label for="full_name" class="form-label">Nombre completo</label>
                        <input
                            type="text"
                            class="form-control"
                            id="full_name"
                            name="full_name"
                            value="<?php echo htmlspecialchars($createFormData['full_name'], ENT_QUOTES, 'UTF-8'); ?>"
                            required
                        >
                    </div>
                    <div class="col-md-6">
                        <label for="username" class="form-label">Usuario</label>
                        <input
                            type="text"
                            class="form-control"
                            id="username"
                            name="username"
                            value="<?php echo htmlspecialchars($createFormData['username'], ENT_QUOTES, 'UTF-8'); ?>"
                            required
                        >
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            value="<?php echo htmlspecialchars($createFormData['email'], ENT_QUOTES, 'UTF-8'); ?>"
                            placeholder="Opcional"
                        >
                    </div>
                    <div class="col-md-3">
                        <label for="level" class="form-label">Nivel</label>
                        <select class="form-select" id="level" name="level" required>
                            <option value="1" <?php echo $createFormData['level'] === '1' ? 'selected' : ''; ?>>Superusuario</option>
                            <option value="2" <?php echo $createFormData['level'] === '2' ? 'selected' : ''; ?>>Banners</option>
                            <option value="3" <?php echo $createFormData['level'] === '3' ? 'selected' : ''; ?>>Noticias</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="col-md-3">
                        <label for="confirm_password" class="form-label">Confirmar contraseña</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>Crear usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('.permission-form .permission-toggle').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            const form = this.closest('form');
            if (!form) {
                return;
            }
            const hidden = form.querySelector('input[name="allow"]');
            if (hidden) {
                hidden.value = this.checked ? '1' : '0';
            }
            form.submit();
        });
    });
</script>
</body>
</html>
