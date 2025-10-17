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

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar permisos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background: #f5f7fb;
            min-height: 100vh;
        }
        .navbar {
            box-shadow: 0 2px 8px rgba(13, 110, 253, 0.2);
        }
        .dropdown-menu {
            min-width: 220px;
        }
        .page-hero {
            background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
            color: #fff;
            padding: 3rem 0 2rem;
            border-bottom-left-radius: 30px;
            border-bottom-right-radius: 30px;
            box-shadow: 0 12px 30px rgba(13, 110, 253, 0.35);
        }
        .page-hero .lead {
            opacity: 0.85;
        }
        .card {
            border: none;
            border-radius: 18px;
        }
        .table thead th {
            background-color: #f1f4fb;
            color: #1f3b73;
            text-transform: uppercase;
            font-size: 0.85rem;
            border-top: none;
        }
        .table tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.05);
        }
        .empty-state {
            padding: 3rem 1rem;
        }
        .empty-state i {
            font-size: 2.5rem;
            color: #0d6efd;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
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
                    <a class="nav-link" href="manage_banners.php">Gestionar banners</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_news.php">Gestionar noticias</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="manage_permissions.php">Dar permisos</a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <span class="text-white me-3">
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

<header class="page-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-6 fw-bold mb-2">Gestión de permisos</h1>
                <p class="lead mb-0">Define quién puede administrar los banners y noticias del portal.</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <span class="badge bg-light text-primary fs-6 py-2 px-3 shadow-sm">Solo superusuarios</span>
            </div>
        </div>
    </div>
</header>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12">
            <?php if ($flashMessage !== ''): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($flashMessage, ENT_QUOTES, 'UTF-8'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if ($flashError !== ''): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-lg">
                <div class="card-body p-0">
                    <?php if (empty($users)): ?>
                        <div class="empty-state text-center text-muted">
                            <i class="fas fa-users mb-3"></i>
                            <p class="mb-0">No hay otros usuarios registrados.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
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
                                            <span class="fw-semibold text-primary d-block"><?php echo htmlspecialchars((string) ($user['nombres'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                                            <small class="text-muted">@<?php echo htmlspecialchars((string) ($user['users'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($levelLabels[(int) ($user['nivel'] ?? 0)] ?? 'Nivel desconocido', ENT_QUOTES, 'UTF-8'); ?></span>
                                        </td>
                                        <td class="text-center">
                                            <form method="POST" class="permission-form d-inline">
                                                <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                                                <input type="hidden" name="resource" value="BANNERS">
                                                <input type="hidden" name="allow" value="<?php echo !empty($user['can_manage_banners']) ? '1' : '0'; ?>">
                                                <div class="form-check form-switch d-inline-flex align-items-center">
                                                    <input class="form-check-input permission-toggle" type="checkbox" role="switch" <?php echo !empty($user['can_manage_banners']) ? 'checked' : ''; ?>>
                                                </div>
                                            </form>
                                        </td>
                                        <td class="text-center">
                                            <form method="POST" class="permission-form d-inline">
                                                <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                                                <input type="hidden" name="resource" value="NEWS">
                                                <input type="hidden" name="allow" value="<?php echo !empty($user['can_manage_news']) ? '1' : '0'; ?>">
                                                <div class="form-check form-switch d-inline-flex align-items-center">
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
