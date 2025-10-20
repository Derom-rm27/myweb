<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/navigation.php';
require_once __DIR__ . '/repositories/AdminUserRepository.php';

adminRequireSuperuser();

$conexion    = adminCreateConnection();
$usuarioRepo = new AdminUserRepository($conexion);

$mensajeFlash = isset($_GET['mensaje']) ? trim((string)$_GET['mensaje']) : '';
$errorFlash   = isset($_GET['error']) ? trim((string)$_GET['error']) : '';

$userIdQuery = isset($_GET['userId']) ? trim((string)$_GET['userId']) : '';
$userIdQuery = $userIdQuery !== '' ? preg_replace('/[^0-9]/', '', $userIdQuery) : '';

$usernameInput = isset($_GET['username']) ? trim((string)$_GET['username']) : '';
$usernameQuery = $usernameInput !== ''
    ? preg_replace('/[^\p{L}\p{N}@._\-\s]/u', '', $usernameInput)
    : '';

$searchRequested = isset($_GET['search']);

$userData = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actionRaw = $_POST['action'] ?? '';
    $action    = is_string($actionRaw) ? trim($actionRaw) : '';

    $userIdRaw = $_POST['user_id'] ?? '';
    $userIdSan = is_string($userIdRaw) ? preg_replace('/[^0-9]/', '', $userIdRaw) : '';

    if ($userIdSan === '') {
        adminRedirect('permissions.php', ['error' => 'Debe proporcionar un ID de usuario válido.']);
    }

    $userId = (int)$userIdSan;

    if ($userId <= 0) {
        adminRedirect('permissions.php', ['error' => 'El ID de usuario proporcionado no es válido.']);
    }

    $usuarioObjetivo = $usuarioRepo->findById($userId);

    if ($usuarioObjetivo === null) {
        adminRedirect('permissions.php', ['error' => 'No se encontró un usuario con ese ID.']);
    }

    $nivelObjetivo   = (int)($usuarioObjetivo['nivel'] ?? 0);
    $usuarioActualId = adminCurrentUserId();

    if ($nivelObjetivo === 1 && $userId !== $usuarioActualId) {
        adminRedirect('permissions.php', [
            'error' => 'No puede modificar los permisos de otro superusuario.',
            'userId' => (string)$userId,
        ]);
    }

    if ($action === 'update_permissions') {
        $nivelRaw = $_POST['nivel'] ?? '';
        $nivel    = is_numeric($nivelRaw) ? (int)$nivelRaw : -1;

        if (!in_array($nivel, [0, 1, 2, 3], true)) {
            adminRedirect('permissions.php', [
                'error'  => 'Debe seleccionar un nivel válido.',
                'userId' => (string)$userId,
            ]);
        }

        $estado = isset($_POST['estado']);

        $usuarioRepo->updateAccess($userId, $nivel, $estado);

        adminRedirect('permissions.php', [
            'mensaje' => 'Permisos actualizados correctamente.',
            'userId'  => (string)$userId,
        ]);
    }

    if ($action === 'revoke_permissions') {
        if ($userId === $usuarioActualId) {
            adminRedirect('permissions.php', [
                'error'  => 'No puede revocar sus propios permisos.',
                'userId' => (string)$userId,
            ]);
        }

        $usuarioRepo->revokeAndDeactivate($userId);

        adminRedirect('permissions.php', ['mensaje' => 'Permisos revocados y usuario desactivado.']);
    }

    if ($action === 'delete_user') {
        if ($userId === $usuarioActualId) {
            adminRedirect('permissions.php', [
                'error'  => 'No puede eliminar su propio usuario.',
                'userId' => (string)$userId,
            ]);
        }

        if ($nivelObjetivo === 1) {
            adminRedirect('permissions.php', [
                'error'  => 'No puede eliminar a otro superusuario.',
                'userId' => (string)$userId,
            ]);
        }

        $usuarioRepo->deleteUser($userId);

        adminRedirect('permissions.php', ['mensaje' => 'Usuario eliminado correctamente.']);
    }

    adminRedirect('permissions.php', ['error' => 'Acción no reconocida.']);
}

if ($userIdQuery !== '' || $usernameQuery !== '') {
    if ($userIdQuery !== '') {
        $userId = (int)$userIdQuery;
        if ($userId > 0) {
            $userData = $usuarioRepo->findById($userId);
            if ($userData !== null) {
                $usernameInput = $userData['users'] ?? $usernameInput;
            } else {
                $errorFlash = $errorFlash !== '' ? $errorFlash : 'No se encontró un usuario con ese ID.';
            }
        }
    } elseif ($usernameQuery !== '') {
        $userData = $usuarioRepo->findFirstByUsernameOrName($usernameQuery);
        if ($userData !== null) {
            $userIdQuery    = (string)($userData['usersId'] ?? $userIdQuery);
            $usernameInput = $userData['users'] ?? $usernameInput;
        } else {
            $errorFlash = $errorFlash !== '' ? $errorFlash : 'No se encontró un usuario con ese nombre.';
        }
    }
} elseif ($searchRequested) {
    $errorFlash = $errorFlash !== '' ? $errorFlash : 'Debe ingresar un ID o nombre de usuario para realizar la búsqueda.';
}

if (is_object($conexion) && method_exists($conexion, 'Close')) {
    $conexion->Close();
}

$nivelesDisponibles = [
    ['valor' => 0, 'etiqueta' => 'Sin permisos (deshabilitado)'],
    ['valor' => 1, 'etiqueta' => 'Nivel 1 - Superadministrador'],
    ['valor' => 2, 'etiqueta' => 'Nivel 2 - Puede subir banners'],
    ['valor' => 3, 'etiqueta' => 'Nivel 3 - Puede publicar noticias'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar permisos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard-theme.css">
</head>
<body class="dashboard-body">
<?php adminRenderNavbar('', 'permissions'); ?>

<main class="dashboard-main">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-end mb-3">
                <a class="btn btn-outline-light" href="../index.php">
                    <i class="fas fa-arrow-left me-2"></i>Volver al inicio
                </a>
            </div>
            <?php if ($mensajeFlash !== ''): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($mensajeFlash, ENT_QUOTES, 'UTF-8'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if ($errorFlash !== ''): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($errorFlash, ENT_QUOTES, 'UTF-8'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <div class="card dashboard-card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-search me-2"></i>Buscar usuario</h5>
                </div>
                <div class="card-body">
                    <form class="row g-3" method="get" action="permissions.php">
                        <input type="hidden" name="search" value="1">
                        <div class="col-md-6">
                            <label for="userId" class="form-label">ID de usuario</label>
                            <input type="text" class="form-control" id="userId" name="userId" value="<?php echo htmlspecialchars($userIdQuery, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ej. 5">
                        </div>
                        <div class="col-md-6">
                            <label for="username" class="form-label">Nombre de usuario</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($usernameInput, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ej. juan.perez">
                        </div>
                        <div class="col-12">
                            <small class="form-text text-white-50">Complete al menos uno de los campos para realizar la búsqueda.</small>
                        </div>
                        <div class="col-sm-4 col-md-3 ms-auto d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Buscar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($userData !== null): ?>
                <?php
                    $usuarioActualId = adminCurrentUserId();
                    $puedeEliminarUsuario = ((int)($userData['usersId'] ?? 0) !== $usuarioActualId) && ((int)($userData['nivel'] ?? 0) !== 1);
                ?>
                <div class="card dashboard-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Usuario: <?php echo htmlspecialchars($userData['nombres'], ENT_QUOTES, 'UTF-8'); ?></h5>
                            <small class="text-white-50">ID <?php echo (int)$userData['usersId']; ?> · <?php echo htmlspecialchars($userData['users'], ENT_QUOTES, 'UTF-8'); ?></small>
                        </div>
                        <span class="badge badge-level">Nivel actual: <?php echo (int)$userData['nivel']; ?></span>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-4">
                            <dt class="col-sm-4">Correo electrónico</dt>
                            <dd class="col-sm-8"><?php echo htmlspecialchars($userData['email'] ?? 'Sin especificar', ENT_QUOTES, 'UTF-8'); ?></dd>
                            <dt class="col-sm-4">Estado</dt>
                            <dd class="col-sm-8">
                                <?php if ((int)$userData['estado'] === 1): ?>
                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger"><i class="fas fa-ban me-1"></i>Inactivo</span>
                                <?php endif; ?>
                            </dd>
                            <dt class="col-sm-4">Registrado</dt>
                            <dd class="col-sm-8"><?php echo htmlspecialchars($userData['fechaCreada'], ENT_QUOTES, 'UTF-8'); ?></dd>
                        </dl>

                        <form method="post" class="mb-3">
                            <input type="hidden" name="action" value="update_permissions">
                            <input type="hidden" name="user_id" value="<?php echo (int)$userData['usersId']; ?>">
                            <div class="mb-3">
                                <label for="nivel" class="form-label">Nivel de acceso</label>
                                <select class="form-select" id="nivel" name="nivel" required>
                                    <?php foreach ($nivelesDisponibles as $nivel): ?>
                                        <option value="<?php echo (int)$nivel['valor']; ?>" <?php echo ((int)$userData['nivel'] === (int)$nivel['valor']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($nivel['etiqueta'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="estado" name="estado" <?php echo ((int)$userData['estado'] === 1) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="estado">Usuario activo</label>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-grow-1">
                                    <i class="fas fa-save me-2"></i>Guardar cambios
                                </button>
                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#revokeModal">
                                    <i class="fas fa-user-slash me-2"></i>Revocar permisos
                                </button>
                                <?php if ($puedeEliminarUsuario): ?>
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                        <i class="fas fa-user-times me-2"></i>Eliminar usuario
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-danger" disabled>
                                        <i class="fas fa-user-times me-2"></i>Eliminar usuario
                                    </button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="modal fade" id="revokeModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2 text-danger"></i>Confirmar revocación</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-0">Esta acción desactivará al usuario y quitará todos sus permisos. ¿Desea continuar?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancelar</button>
                                <form method="post">
                                    <input type="hidden" name="action" value="revoke_permissions">
                                    <input type="hidden" name="user_id" value="<?php echo (int)$userData['usersId']; ?>">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-user-slash me-2"></i>Revocar permisos
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="fas fa-user-times me-2 text-danger"></i>Eliminar usuario</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-0">Esta acción eliminará al usuario de forma permanente. ¿Desea continuar?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancelar</button>
                                <?php if ($puedeEliminarUsuario): ?>
                                    <form method="post">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="user_id" value="<?php echo (int)$userData['usersId']; ?>">
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-user-times me-2"></i>Eliminar usuario
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
