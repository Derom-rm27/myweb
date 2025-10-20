<?php
session_start();

if (!isset($_SESSION['login'])) {
    header('Location: login.php');
    exit();
}

$nivelUsuario = (int) ($_SESSION['nivel'] ?? 0);
$usuarioId = (int) ($_SESSION['idUser'] ?? 0);
$nombreUsuario = $_SESSION['nombre'] ?? 'Usuario';

require_once __DIR__ . '/../includes/repositories/PermissionRepository.php';
require_once __DIR__ . '/../includes/repositories/BannerRepository.php';

$flashMessage = isset($_GET['mensaje']) ? trim((string) $_GET['mensaje']) : '';
$flashError   = isset($_GET['error']) ? trim((string) $_GET['error']) : '';

$redirectBase = 'manage_banners.php';

$canManageBanners = ($nivelUsuario === 1);
$canManageNews = ($nivelUsuario === 1);
$canGrantPermissions = ($nivelUsuario === 1);
$canUploadBanner = in_array($nivelUsuario, [1, 2], true);
$canPublishNews = in_array($nivelUsuario, [1, 3], true);

$permissionConnection = null;
try {
    $permissionConnection = new MySQLcn();
    $permissionRepository = new PermissionRepository($permissionConnection);
    $canManageBanners = $permissionRepository->userCanManageBanners($usuarioId);
    $canManageNews = $permissionRepository->userCanManageNews($usuarioId);
} catch (Throwable $exception) {
    // Si no es posible consultar permisos, se mantiene el fallback basado en el nivel del usuario.
    $permissionRepository = null;
}

if ($permissionConnection !== null) {
    $permissionConnection->Close();
}

if (!$canManageBanners) {
    header('Location: user.php');
    exit();
}

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
    <link rel="stylesheet" href="css/dashboard-theme.css">
</head>
<body class="dashboard-body">
<div class="dashboard-shell">
    <?php
    $activeSidebar = 'manage_banners';
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
                    <h4 class="dashboard-section-title mb-1">Gestión de banners</h4>
                    <p class="dashboard-section-subtitle mb-0">Administra las piezas destacadas del sitio con una vista clara y moderna.</p>
                </div>
                <div>
                    <?php if ($canUploadBanner): ?>
                        <a class="btn btn-primary" href="user.php">
                            <i class="fas fa-plus me-2"></i>Nuevo banner
                        </a>
                    <?php else: ?>
                        <span class="btn btn-outline-secondary disabled">
                            <i class="fas fa-lock me-2"></i>Nuevo banner
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($banners)): ?>
                    <div class="dashboard-empty-state">
                        <i class="fas fa-image"></i>
                        <p class="mb-0">No hay banners registrados por el momento.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive dashboard-table-responsive">
                        <table class="table align-middle">
                            <thead>
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
                                            <img class="dashboard-banner-thumb" src="<?php echo '../images/banner/' . htmlspecialchars($banner['Imagen'], ENT_QUOTES, 'UTF-8'); ?>" alt="Banner">
                                        <?php else: ?>
                                            <span class="text-muted">Sin imagen</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-semibold"><?php echo htmlspecialchars((string) $banner['Titulo'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="small text-muted"><?php echo htmlspecialchars((string) $banner['Describir'], ENT_QUOTES, 'UTF-8'); ?></td>
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
                                            <span class="badge bg-success">Publicado</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Oculto</span>
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
    </main>
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
