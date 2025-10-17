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

$nombreUsuario = $_SESSION['nombre'] ?? 'Usuario';

require_once __DIR__ . '/../includes/repositories/BannerRepository.php';

$flashMessage = isset($_GET['mensaje']) ? trim((string) $_GET['mensaje']) : '';
$flashError   = isset($_GET['error']) ? trim((string) $_GET['error']) : '';

$redirectBase = 'manage_banners.php';

try {
    $connection = new MySQLcn();
    $repository = new BannerRepository($connection);
} catch (Throwable $exception) {
    $flashError = 'No fue posible conectar con la base de datos.';
    $connection = null;
    $repository = null;
}

if ($repository !== null && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action   = $_POST['action'] ?? '';
    $bannerId = isset($_POST['id']) ? (int) $_POST['id'] : 0;

    if ($bannerId <= 0) {
        $connection?->Close();
        header("Location: {$redirectBase}?error=" . urlencode('Identificador de banner inválido.'));
        exit();
    }

    if ($action === 'update') {
        $title       = trim((string) ($_POST['title'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $link        = trim((string) ($_POST['link'] ?? ''));
        $status      = isset($_POST['estado']) ? 1 : 0;

        if ($title === '' || $description === '') {
            $connection->Close();
            header("Location: {$redirectBase}?error=" . urlencode('Debe completar los campos obligatorios.'));
            exit();
        }

        $updated = $repository->update($bannerId, $title, $description, $link, $status);
        $connection->Close();

        if ($updated) {
            header("Location: {$redirectBase}?mensaje=" . urlencode('El banner se actualizó correctamente.'));
        } else {
            header("Location: {$redirectBase}?error=" . urlencode('No fue posible actualizar el banner.'));
        }
        exit();
    }

    if ($action === 'delete') {
        $existingBanner = $repository->findById($bannerId);
        if ($existingBanner === null) {
            $connection->Close();
            header("Location: {$redirectBase}?error=" . urlencode('El banner seleccionado no existe.'));
            exit();
        }

        $deleted = $repository->delete($bannerId);

        if ($deleted) {
            $imageName = $existingBanner['Imagen'] ?? '';
            if ($imageName !== '') {
                $imagePath = realpath(__DIR__ . '/../images/banner');
                if ($imagePath !== false) {
                    $fullPath = $imagePath . DIRECTORY_SEPARATOR . $imageName;
                    if (is_file($fullPath)) {
                        @unlink($fullPath);
                    }
                }
            }

            $connection->Close();
            header("Location: {$redirectBase}?mensaje=" . urlencode('El banner se eliminó correctamente.'));
        } else {
            $connection->Close();
            header("Location: {$redirectBase}?error=" . urlencode('No fue posible eliminar el banner.'));
        }
        exit();
    }

    $connection->Close();
    header("Location: {$redirectBase}?error=" . urlencode('Acción no soportada.'));
    exit();
}

$banners = [];
if ($repository !== null) {
    $banners = $repository->getAll();
    $connection->Close();
}

function formatDate(?string $date): string
{
    if ($date === null || $date === '') {
        return '';
    }

    $timestamp = strtotime($date);
    return $timestamp ? date('d/m/Y H:i', $timestamp) : $date;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Banners</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .table-responsive {
            max-height: 60vh;
        }
        .badge-status {
            font-size: 0.9rem;
        }
        .img-thumb {
            max-width: 120px;
            max-height: 70px;
            object-fit: cover;
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
                    <a class="nav-link active" aria-current="page" href="manage_banners.php">Gestionar banners</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_news.php">Gestionar noticias</a>
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

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Listado de banners</h1>
        <a class="btn btn-success" href="user.php">
            <i class="fas fa-plus me-2"></i>Nuevo banner
        </a>
    </div>

    <?php if ($flashMessage !== ''): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo htmlspecialchars($flashMessage, ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if ($flashError !== ''): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($banners)): ?>
                <div class="p-4 text-center text-muted">No hay banners registrados.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th scope="col">Imagen</th>
                            <th scope="col">Título</th>
                            <th scope="col">Descripción</th>
                            <th scope="col">Enlace</th>
                            <th scope="col" class="text-center">Estado</th>
                            <th scope="col">Fecha</th>
                            <th scope="col" class="text-end">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($banners as $banner): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($banner['Imagen'])): ?>
                                        <img class="img-thumbnail img-thumb" src="<?php echo '../images/banner/' . htmlspecialchars($banner['Imagen'], ENT_QUOTES, 'UTF-8'); ?>" alt="Banner">
                                    <?php else: ?>
                                        <span class="text-muted">Sin imagen</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars((string) $banner['Titulo'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="text-muted small"><?php echo htmlspecialchars((string) $banner['Describir'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <?php if (!empty($banner['Enlace'])): ?>
                                        <a href="<?php echo htmlspecialchars($banner['Enlace'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">
                                            <?php echo htmlspecialchars($banner['Enlace'], ENT_QUOTES, 'UTF-8'); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Sin enlace</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ((int) $banner['estado'] === 1): ?>
                                        <span class="badge bg-success badge-status">Publicado</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary badge-status">Oculto</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars(formatDate($banner['fecha'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="text-end">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary me-2 edit-banner-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editBannerModal"
                                        data-id="<?php echo (int) $banner['idBanner']; ?>"
                                        data-title="<?php echo htmlspecialchars((string) $banner['Titulo'], ENT_QUOTES, 'UTF-8'); ?>"
                                        data-description="<?php echo htmlspecialchars((string) $banner['Describir'], ENT_QUOTES, 'UTF-8'); ?>"
                                        data-link="<?php echo htmlspecialchars((string) $banner['Enlace'], ENT_QUOTES, 'UTF-8'); ?>"
                                        data-status="<?php echo (int) $banner['estado']; ?>"
                                    >
                                        <i class="fas fa-edit me-1"></i>Editar
                                    </button>
                                    <form action="<?php echo $redirectBase; ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que desea eliminar este banner?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo (int) $banner['idBanner']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash-alt me-1"></i>Eliminar
                                        </button>
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

<div class="modal fade" id="editBannerModal" tabindex="-1" aria-labelledby="editBannerLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?php echo $redirectBase; ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="editBannerLabel">Editar banner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="banner-id">
                    <div class="mb-3">
                        <label for="banner-title" class="form-label">Título</label>
                        <input type="text" class="form-control" id="banner-title" name="title" maxlength="250" required>
                    </div>
                    <div class="mb-3">
                        <label for="banner-description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="banner-description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="banner-link" class="form-label">Enlace</label>
                        <input type="text" class="form-control" id="banner-link" name="link" maxlength="250">
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="banner-status" name="estado">
                        <label class="form-check-label" for="banner-status">Publicado</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('.edit-banner-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            const modal = document.getElementById('editBannerModal');
            modal.querySelector('#banner-id').value = this.dataset.id || '';
            modal.querySelector('#banner-title').value = this.dataset.title || '';
            modal.querySelector('#banner-description').value = this.dataset.description || '';
            modal.querySelector('#banner-link').value = this.dataset.link || '';
            modal.querySelector('#banner-status').checked = parseInt(this.dataset.status || '0', 10) === 1;
        });
    });
</script>
</body>
</html>
