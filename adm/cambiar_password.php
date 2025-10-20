<?php
session_start();
if(!isset($_SESSION['login'])){
    header("location:login.php");
    exit();
}

$nivelUsuario   = (int)($_SESSION['nivel'] ?? 0);
$nombreUsuario  = $_SESSION['nombre'] ?? 'Usuario';
$mensajeExito   = isset($_GET['mensaje']) ? trim((string) $_GET['mensaje']) : '';
$mensajeError   = isset($_GET['error']) ? trim((string) $_GET['error']) : '';

$canUploadBanner  = in_array($nivelUsuario, [1, 2], true);
$canPublishNews   = in_array($nivelUsuario, [1, 3], true);
$canManageBanners = ($nivelUsuario === 1);
$canManageNews    = ($nivelUsuario === 1);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard-theme.css">
</head>
<body class="dashboard-body">
<div class="dashboard-shell">
    <?php
    $activeSidebar = 'change_password';
    require __DIR__ . '/includes/dashboard_sidebar.php';
    ?>

    <main class="dashboard-main">
        <div class="dashboard-main-inner">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card dashboard-card">
                        <div class="card-header">
                            <h4 class="mb-0">Cambiar Contraseña</h4>
                        </div>
                        <div class="card-body">
                            <?php if ($mensajeExito !== ''): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($mensajeExito, ENT_QUOTES, 'UTF-8'); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            <?php if ($mensajeError !== ''): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($mensajeError, ENT_QUOTES, 'UTF-8'); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            <form action="procesar_password.php" method="POST" id="passwordForm">
                            <div class="mb-3">
                                <label for="password_actual" class="form-label">Contraseña Actual</label>
                                <input type="password" class="form-control" id="password_actual" name="password_actual" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password_nueva" class="form-label">Nueva Contraseña</label>
                                <input type="password" class="form-control" id="password_nueva" name="password_nueva" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password_confirmar" class="form-label">Confirmar Nueva Contraseña</label>
                                <input type="password" class="form-control" id="password_confirmar" name="password_confirmar" required>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </main>
</div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const passwordNueva = document.getElementById('password_nueva').value;
            const passwordConfirmar = document.getElementById('password_confirmar').value;
            
            if (passwordNueva !== passwordConfirmar) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
            }
        });
    </script>
</body>
</html> 