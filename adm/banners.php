<?php
// VERSION DE PARCHE 1.5.0.1
session_start();
if (!isset($_SESSION['login'])) {
    header('Location: login.php');
    exit();
} else {
    $fecGuar = $_SESSION['hora'];
    $ahora = date('Y-n-j H:i:s');
    $tmpTrans = (strtotime($ahora) - strtotime($fecGuar));
    if ($tmpTrans >= 12000) {
        session_destroy();
        header('Location: login.php');
        exit();
    } else {
        $_SESSION['hora'] = $ahora;
    }
}

require_once __DIR__ . '/script/conex.php';

$nivelUsuario = (int)($_SESSION['nivel'] ?? 0);
if (!in_array($nivelUsuario, [1, 2], true)) {
    header('Location: news.php');
    exit();
}

$usuarioId = (int)($_SESSION['idUser'] ?? 0);
$nombreUsuario = $_SESSION['nombre'] ?? 'Usuario';
$mensajeFlash = isset($_GET['mensaje']) ? trim((string)$_GET['mensaje']) : '';
$errorFlash = isset($_GET['error']) ? trim((string)$_GET['error']) : '';
$canUploadBanner  = in_array($nivelUsuario, [1, 2], true);
$canPublishNews   = in_array($nivelUsuario, [1, 3], true);
$canManageBanners = ($nivelUsuario === 1);
$canManageNews    = ($nivelUsuario === 1);

$cn = new MySQLcn();

if ($nivelUsuario === 1) {
    $query = "SELECT b.idBanner, b.Titulo, b.Describir, b.Enlace, b.Imagen, b.estado, b.fecha, u.nombres " .
        "FROM banner b LEFT JOIN usuarios u ON u.usersId = b.usersId ORDER BY b.fecha DESC";
} else {
    $query = "SELECT b.idBanner, b.Titulo, b.Describir, b.Enlace, b.Imagen, b.estado, b.fecha, u.nombres " .
        "FROM banner b LEFT JOIN usuarios u ON u.usersId = b.usersId WHERE b.usersId = $usuarioId ORDER BY b.fecha DESC";
}

$cn->Query($query);
$banners = $cn->Rows();
$cn->Close();
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
            <div class="row justify-content-center">
                <div class="col-12">
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
                    <div class="card dashboard-card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h4 class="mb-0">Mis banners</h4>
                        <a class="btn btn-primary" href="user.php">
                            <i class="fas fa-plus-circle me-2"></i>Subir nuevo banner
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (count($banners) === 0): ?>
                            <p class="text-center mb-0">Aún no has registrado banners.</p>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th scope="col">Imagen</th>
                                        <th scope="col">Título</th>
                                        <th scope="col">Enlace</th>
                                        <th scope="col">Estado</th>
                                        <th scope="col">Actualizado</th>
                                        <?php if ($nivelUsuario === 1): ?>
                                        <th scope="col">Autor</th>
                                        <?php endif; ?>
                                        <th scope="col" class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($banners as $banner): ?>
                                    <?php
                                        $imagen = $banner['Imagen'] ?? '';
                                        $estadoActivo = ((int)($banner['estado'] ?? 0)) === 1;
                                        $fecha = $banner['fecha'] ?? '';
                                        $fechaFormateada = $fecha !== '' ? date('d/m/Y H:i', strtotime($fecha)) : '';
                                    ?>
                                    <tr>
                                        <td>
                                            <?php if ($imagen !== ''): ?>
                                                <img src="<?php echo '../images/banner/' . htmlspecialchars($imagen, ENT_QUOTES, 'UTF-8'); ?>" alt="Banner" class="img-thumbnail" style="max-width: 120px;">
                                            <?php else: ?>
                                                <span class="text-muted">Sin imagen</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($banner['Titulo'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <?php if (!empty($banner['Enlace'])): ?>
                                                <a href="<?php echo htmlspecialchars($banner['Enlace'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">
                                                    <?php echo htmlspecialchars($banner['Enlace'], ENT_QUOTES, 'UTF-8'); ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">Sin enlace</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($estadoActivo): ?>
                                                <span class="badge bg-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($fechaFormateada, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <?php if ($nivelUsuario === 1): ?>
                                        <td><?php echo htmlspecialchars($banner['nombres'] ?? 'Sin registro', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <?php endif; ?>
                                        <td class="text-end">
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary js-edit-banner" data-bs-toggle="modal" data-bs-target="#editBannerModal"
                                                    data-id="<?php echo (int)$banner['idBanner']; ?>"
                                                    data-title="<?php echo htmlspecialchars($banner['Titulo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-description="<?php echo htmlspecialchars($banner['Describir'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-link="<?php echo htmlspecialchars($banner['Enlace'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-status="<?php echo $estadoActivo ? '1' : '0'; ?>"
                                                    data-image="<?php echo htmlspecialchars($imagen, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <i class="fas fa-edit me-1"></i>Editar
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger js-delete-banner" data-bs-toggle="modal" data-bs-target="#deleteBannerModal"
                                                    data-id="<?php echo (int)$banner['idBanner']; ?>"
                                                    data-title="<?php echo htmlspecialchars($banner['Titulo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                    <i class="fas fa-trash-alt me-1"></i>Eliminar
                                                </button>
                                            </div>
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
</div>

<div class="modal fade" id="editBannerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="actualizar_banner.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Editar banner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="idBanner" id="editBannerId" value="">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="editBannerTitle" class="form-label">Título</label>
                            <input type="text" class="form-control" id="editBannerTitle" name="titulo" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editBannerLink" class="form-label">Enlace</label>
                            <input type="url" class="form-control" id="editBannerLink" name="enlace" placeholder="https://ejemplo.com">
                        </div>
                        <div class="col-12">
                            <label for="editBannerDescription" class="form-label">Descripción</label>
                            <textarea class="form-control" id="editBannerDescription" name="descripcion" rows="3" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estado</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="editBannerStatus" name="estado">
                                <label class="form-check-label" for="editBannerStatus">Activo</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="editBannerImage" class="form-label">Actualizar imagen</label>
                            <input class="form-control" type="file" id="editBannerImage" name="imagen" accept="image/jpeg,image/png,image/gif">
                            <div class="form-text">Opcional. Máximo 5MB.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Vista previa actual</label>
                            <div class="d-flex align-items-center gap-3">
                                <img id="editBannerPreview" src="" alt="Vista previa" class="img-thumbnail d-none" style="max-width: 200px;">
                                <span id="editBannerPreviewFallback" class="text-muted">Sin imagen disponible</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteBannerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="eliminar_banner.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Eliminar banner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="idBanner" id="deleteBannerId" value="">
                    <p class="mb-0">¿Seguro que deseas eliminar el banner <strong id="deleteBannerTitle"></strong>? Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/banner-management.js"></script>
</body>
</html>
