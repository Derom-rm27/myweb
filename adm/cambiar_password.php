<?php
session_start();
if(!isset($_SESSION['login'])){
    header("location:login.php");
    exit();
}
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
    <style>
        .navbar {
            margin-bottom: 2rem;
        }
        .dropdown-menu {
            min-width: 200px;
        }
        .user-info {
            color: white;
            margin-right: 1rem;
        }
    </style>
</head>
<body>
    <!-- Barra de navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Panel de Control</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="user-info">
                            <i class="fas fa-user me-2"></i><?php echo $_SESSION["nombre"]; ?>
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
                                <a class="dropdown-item active" href="cambiar_password.php">
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

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Cambiar Contraseña</h4>
                    </div>
                    <div class="card-body">
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