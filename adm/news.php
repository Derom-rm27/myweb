<?php
session_start();

if (!isset($_SESSION['login'])) {
    header('location:login.php');
    exit();
}

$nivelUsuario = (int) ($_SESSION['nivel'] ?? 0);
if (!in_array($nivelUsuario, [1, 3], true)) {
    $mensaje = urlencode('No tiene permisos para gestionar noticias.');
    header("Location: user.php?mensaje={$mensaje}");
    exit();
}

$nombreUsuario = $_SESSION['nombre'] ?? 'Usuario';
$mensajeFlash = isset($_GET['mensaje']) ? trim((string) $_GET['mensaje']) : '';
$errorFlash   = isset($_GET['error']) ? trim((string) $_GET['error']) : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publicar noticia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .preview-image {
            max-width: 300px;
            max-height: 300px;
            margin-top: 10px;
        }
        .upload-area {
            border: 2px dashed #ccc;
            border-radius: 5px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.3s ease;
        }
        .upload-area:hover {
            border-color: #0d6efd;
        }
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
                    <a class="nav-link active" aria-current="page" href="news.php">Publicar noticia</a>
                </li>
                <?php if ($nivelUsuario === 1): ?>
                <li class="nav-item">
                    <a class="nav-link" href="manage_banners.php">Gestionar banners</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_news.php">Gestionar noticias</a>
                </li>
                <?php endif; ?>
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
        <div class="col-lg-8">
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
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Publicar nueva noticia</h4>
                </div>
                <div class="card-body">
                    <form action="procesar_noticia.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="titulo" class="form-label mb-0">Título</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="estado" name="estado" checked>
                                    <label class="form-check-label" for="estado">Publicar inmediatamente</label>
                                </div>
                            </div>
                            <input type="text" class="form-control" id="titulo" name="titulo" maxlength="250" required>
                        </div>

                        <div class="mb-3">
                            <label for="contenido" class="form-label">Contenido</label>
                            <textarea class="form-control" id="contenido" name="contenido" rows="8" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Imagen destacada (opcional)</label>
                            <div class="upload-area" id="uploadArea">
                                <i class="fas fa-cloud-upload-alt fa-3x mb-3"></i>
                                <p class="mb-0">Arrastra y suelta una imagen aquí o haz clic para seleccionar</p>
                                <input type="file" class="d-none" id="imagen" name="imagen" accept="image/*">
                            </div>
                            <div id="preview" class="text-center mt-3"></div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Publicar noticia
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('imagen');
    const preview = document.getElementById('preview');

    uploadArea.addEventListener('click', () => fileInput.click());

    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.style.borderColor = '#0d6efd';
    });

    uploadArea.addEventListener('dragleave', () => {
        uploadArea.style.borderColor = '#ccc';
    });

    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.style.borderColor = '#ccc';
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) {
            fileInput.files = e.dataTransfer.files;
            showPreview(file);
        }
    });

    fileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            showPreview(file);
        }
    });

    function showPreview(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            preview.innerHTML = `<img src="${e.target.result}" class="preview-image img-thumbnail" alt="Vista previa">`;
        };
        reader.readAsDataURL(file);
    }
</script>
</body>
</html>
