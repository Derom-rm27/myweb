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
<div class="dashboard-shell">
    <?php
    $activeSidebar = 'grant_permissions';
    require __DIR__ . '/includes/dashboard_sidebar.php';
    ?>

    <main class="dashboard-main">
        <div class="dashboard-main-inner">
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
    </main>
</div>

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
