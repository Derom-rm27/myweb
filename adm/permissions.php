<?php
session_start();

if (!isset($_SESSION['login'])) {
    header('location:login.php');
    exit();
}

$nivelUsuario = (int)($_SESSION['nivel'] ?? 0);
if ($nivelUsuario !== 1) {
    $mensaje = urlencode('No tiene permisos para administrar otros usuarios.');
    header("Location: user.php?error={$mensaje}");
    exit();
}

require_once __DIR__ . '/script/conex.php';

$conexion = new MySQLcn();

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
        $error = urlencode('Debe proporcionar un ID de usuario válido.');
        header("Location: permissions.php?error={$error}");
        exit();
    }

    $userId = (int)$userIdSan;

    if ($userId <= 0) {
        $error = urlencode('El ID de usuario proporcionado no es válido.');
        header("Location: permissions.php?error={$error}");
        exit();
    }

    $conexion->Query("SELECT usersId, nombres, users, email, nivel, estado, fechaCreada FROM usuarios WHERE usersId = {$userId} LIMIT 1");
    $rows = $conexion->Rows();

    if (count($rows) === 0) {
        $error = urlencode('No se encontró un usuario con ese ID.');
        header("Location: permissions.php?error={$error}");
        exit();
    }

    $usuarioObjetivo = $rows[0];
    $nivelObjetivo   = (int)($usuarioObjetivo['nivel'] ?? 0);

    if ($nivelObjetivo === 1 && $userId !== (int)($_SESSION['idUser'] ?? 0)) {
        $error = urlencode('No puede modificar los permisos de otro superusuario.');
        header("Location: permissions.php?error={$error}&userId={$userId}");
        exit();
    }

    if ($action === 'update_permissions') {
        $nivelRaw = $_POST['nivel'] ?? '';
        $nivel    = is_numeric($nivelRaw) ? (int)$nivelRaw : -1;

        if (!in_array($nivel, [0, 1, 2, 3], true)) {
            $error = urlencode('Debe seleccionar un nivel válido.');
            header("Location: permissions.php?error={$error}&userId={$userId}");
            exit();
        }

        $estado = isset($_POST['estado']) ? 1 : 0;

        $conexion->UpdateDb("UPDATE usuarios SET nivel = {$nivel}, estado = {$estado} WHERE usersId = {$userId} LIMIT 1");

        $mensaje = urlencode('Permisos actualizados correctamente.');
        header("Location: permissions.php?mensaje={$mensaje}&userId={$userId}");
        exit();
    }

    if ($action === 'revoke_permissions') {
        if ($userId === (int)($_SESSION['idUser'] ?? 0)) {
            $error = urlencode('No puede revocar sus propios permisos.');
            header("Location: permissions.php?error={$error}&userId={$userId}");
            exit();
        }

        $conexion->UpdateDb("UPDATE usuarios SET nivel = 0, estado = 0 WHERE usersId = {$userId} LIMIT 1");

        $mensaje = urlencode('Permisos revocados y usuario desactivado.');
        header("Location: permissions.php?mensaje={$mensaje}");
        exit();
    }

    if ($action === 'delete_user') {
        if ($userId === (int)($_SESSION['idUser'] ?? 0)) {
            $error = urlencode('No puede eliminar su propio usuario.');
            header("Location: permissions.php?error={$error}&userId={$userId}");
            exit();
        }

        if ($nivelObjetivo === 1) {
            $error = urlencode('No puede eliminar a otro superusuario.');
            header("Location: permissions.php?error={$error}&userId={$userId}");
            exit();
        }

        $conexion->UpdateDb("DELETE FROM usuarios WHERE usersId = {$userId} LIMIT 1");

        $mensaje = urlencode('Usuario eliminado correctamente.');
        header("Location: permissions.php?mensaje={$mensaje}");
        exit();
    }

    $error = urlencode('Acción no reconocida.');
    header("Location: permissions.php?error={$error}");
    exit();
}

if ($userIdQuery !== '' || $usernameQuery !== '') {
    if ($userIdQuery !== '') {
        $userId = (int)$userIdQuery;
        if ($userId > 0) {
            $conexion->Query("SELECT usersId, nombres, users, email, nivel, estado, fechaCreada FROM usuarios WHERE usersId = {$userId} LIMIT 1");
            $rows = $conexion->Rows();
            if (count($rows) === 1) {
                $userData       = $rows[0];
                $usernameInput = $userData['users'] ?? $usernameInput;
            } else {
                $errorFlash = $errorFlash !== '' ? $errorFlash : 'No se encontró un usuario con ese ID.';
            }
        }
    } elseif ($usernameQuery !== '') {
        $usernameEscaped = $conexion->SecureInput($usernameQuery);
        $conexion->Query(
            "SELECT usersId, nombres, users, email, nivel, estado, fechaCreada FROM usuarios " .
            "WHERE (LOWER(users) LIKE LOWER('%{$usernameEscaped}%') OR LOWER(nombres) LIKE LOWER('%{$usernameEscaped}%')) " .
            "ORDER BY estado DESC, usersId ASC LIMIT 1"
        );
        $rows = $conexion->Rows();
        if (count($rows) === 1) {
            $userData    = $rows[0];
            $userIdQuery = (string)($userData['usersId'] ?? $userIdQuery);
            $usernameInput = $userData['users'] ?? $usernameInput;
        } else {
            $errorFlash = $errorFlash !== '' ? $errorFlash : 'No se encontró un usuario con ese nombre.';
        }
    }
} elseif ($searchRequested) {
    $errorFlash = $errorFlash !== '' ? $errorFlash : 'Debe ingresar un ID o nombre de usuario para realizar la búsqueda.';
}

$nivelesDisponibles = [
    ['valor' => 0, 'etiqueta' => 'Sin permisos (deshabilitado)'],
    ['valor' => 1, 'etiqueta' => 'Nivel 1 - Superadministrador'],
    ['valor' => 2, 'etiqueta' => 'Nivel 2 - Puede subir banners'],
    ['valor' => 3, 'etiqueta' => 'Nivel 3 - Puede publicar noticias'],
];

$canUploadBanner     = true;
$canPublishNews      = true;
$canManageBanners    = true;
$canManageNews       = true;
$canGrantPermissions = true;

$nombreUsuario = $_SESSION['nombre'] ?? 'Super Admin';
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
<div class="dashboard-shell">
    <?php
    $activeSidebar = 'grant_permissions';
    require __DIR__ . '/includes/dashboard_sidebar.php';
    ?>

    <main class="dashboard-main">
        <div class="dashboard-main-inner">
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
                    $usuarioActualId = (int)($_SESSION['idUser'] ?? 0);
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
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
