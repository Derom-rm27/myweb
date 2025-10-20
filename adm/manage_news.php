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
require_once __DIR__ . '/../includes/repositories/NewsRepository.php';

$flashMessage = isset($_GET['mensaje']) ? trim((string) $_GET['mensaje']) : '';
$flashError   = isset($_GET['error']) ? trim((string) $_GET['error']) : '';

$redirectBase = 'manage_news.php';

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
    $permissionRepository = null;
}

if ($permissionConnection !== null) {
    $permissionConnection->Close();
}

if (!$canManageNews) {
    header('Location: news.php');
    exit();
}

try {
    $connection = new MySQLcn();
    $repository = new NewsRepository($connection);
} catch (Throwable $exception) {
    $flashError = 'No fue posible conectar con la base de datos.';
    $connection = null;
    $repository = null;
}

if ($repository !== null && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $newsId = isset($_POST['id']) ? (int) $_POST['id'] : 0;

    if ($newsId <= 0) {
        $connection?->Close();
        header("Location: {$redirectBase}?error=" . urlencode('Identificador de noticia inválido.'));
        exit();
    }

    if ($action === 'update') {
        $title  = trim((string) ($_POST['title'] ?? ''));
        $body   = trim((string) ($_POST['body'] ?? ''));
        $status = isset($_POST['estado']) ? 1 : 0;

        if ($title === '' || $body === '') {
            $connection->Close();
            header("Location: {$redirectBase}?error=" . urlencode('Debe completar los campos obligatorios.'));
            exit();
        }

        $updated = $repository->update($newsId, $title, $body, $status);
        $connection->Close();

        if ($updated) {
            header("Location: {$redirectBase}?mensaje=" . urlencode('La noticia se actualizó correctamente.'));
        } else {
            header("Location: {$redirectBase}?error=" . urlencode('No fue posible actualizar la noticia.'));
        }
        exit();
    }

    if ($action === 'delete') {
        $existingNews = $repository->findById($newsId);
        if ($existingNews === null) {
            $connection->Close();
            header("Location: {$redirectBase}?error=" . urlencode('La noticia seleccionada no existe.'));
            exit();
        }

        $deleted = $repository->delete($newsId);

        if ($deleted) {
            $imageName = $existingNews['imagen'] ?? '';
            if ($imageName !== '') {
                $imagePath = realpath(__DIR__ . '/../images/news');
                if ($imagePath === false) {
                    $imagePath = __DIR__ . '/../images/news';
                }

                $fullPath = rtrim($imagePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $imageName;
                if (is_file($fullPath)) {
                    @unlink($fullPath);
                }
            }

            $connection->Close();
            header("Location: {$redirectBase}?mensaje=" . urlencode('La noticia se eliminó correctamente.'));
        } else {
            $connection->Close();
            header("Location: {$redirectBase}?error=" . urlencode('No fue posible eliminar la noticia.'));
        }
        exit();
    }

    $connection->Close();
    header("Location: {$redirectBase}?error=" . urlencode('Acción no soportada.'));
    exit();
}

$newsEntries = [];
if ($repository !== null) {
    $newsEntries = $repository->getAll();
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
    <title>Gestionar Noticias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard-theme.css">
</head>
<body class="dashboard-body">
<div class="dashboard-shell">
    <?php
    $activeSidebar = 'manage_news';
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
                    <h4 class="dashboard-section-title mb-1">Gestión de noticias</h4>
                    <p class="dashboard-section-subtitle mb-0">Controla las publicaciones informativas con una experiencia visual renovada.</p>
                </div>
                <div>
                    <?php if ($canPublishNews): ?>
                        <a class="btn btn-primary" href="news.php">
                            <i class="fas fa-plus me-2"></i>Nueva noticia
                        </a>
                    <?php else: ?>
                        <span class="btn btn-outline-secondary disabled">
                            <i class="fas fa-lock me-2"></i>Nueva noticia
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($newsEntries)): ?>
                    <div class="dashboard-empty-state">
                        <i class="fas fa-newspaper"></i>
                        <p class="mb-0">No hay noticias registradas por el momento.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive dashboard-table-responsive">
                        <table class="table align-middle">
                            <thead>
                            <tr>
                                <th scope="col">Título</th>
                                <th scope="col" class="dashboard-news-content">Contenido</th>
                                <th scope="col">Imagen</th>
                                <th scope="col" class="text-center">Estado</th>
                                <th scope="col">Fecha</th>
                                <th scope="col" class="text-end">Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($newsEntries as $news): ?>
                                <tr>
                                    <td class="fw-semibold"><?php echo htmlspecialchars((string) $news['titulo'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="dashboard-news-content small"><?php echo nl2br(htmlspecialchars((string) $news['cuerpo'], ENT_QUOTES, 'UTF-8')); ?></td>
                                    <td>
                                        <?php if (!empty($news['imagen'])): ?>
                                            <img class="dashboard-news-thumb" src="<?php echo '../images/news/' . htmlspecialchars($news['imagen'], ENT_QUOTES, 'UTF-8'); ?>" alt="Imagen de noticia">
                                        <?php else: ?>
                                            <span class="text-muted">Sin imagen</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ((int) $news['estado'] === 1): ?>
                                            <span class="badge bg-success">Publicada</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Oculta</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars(formatDate($news['fecha'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="text-end">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary me-2 edit-news-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editNewsModal"
                                            data-id="<?php echo (int) $news['idNoticia']; ?>"
                                            data-title="<?php echo htmlspecialchars((string) $news['titulo'], ENT_QUOTES, 'UTF-8'); ?>"
                                            data-body="<?php echo htmlspecialchars((string) $news['cuerpo'], ENT_QUOTES, 'UTF-8'); ?>"
                                            data-status="<?php echo (int) $news['estado']; ?>"
                                        >
                                            <i class="fas fa-edit me-1"></i>Editar
                                        </button>
                                        <form action="<?php echo $redirectBase; ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que desea eliminar esta noticia?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo (int) $news['idNoticia']; ?>">
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
    </main>
</div>

<div class="modal fade" id="editNewsModal" tabindex="-1" aria-labelledby="editNewsLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?php echo $redirectBase; ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="editNewsLabel">Editar noticia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="news-id">
                    <div class="mb-3">
                        <label for="news-title" class="form-label">Título</label>
                        <input type="text" class="form-control" id="news-title" name="title" maxlength="250" required>
                    </div>
                    <div class="mb-3">
                        <label for="news-body" class="form-label">Contenido</label>
                        <textarea class="form-control" id="news-body" name="body" rows="6" required></textarea>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="news-status" name="estado">
                        <label class="form-check-label" for="news-status">Publicada</label>
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
    document.querySelectorAll('.edit-news-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            const modal = document.getElementById('editNewsModal');
            modal.querySelector('#news-id').value = this.dataset.id || '';
            modal.querySelector('#news-title').value = this.dataset.title || '';
            modal.querySelector('#news-body').value = this.dataset.body || '';
            modal.querySelector('#news-status').checked = parseInt(this.dataset.status || '0', 10) === 1;
        });
    });
</script>
</body>
</html>
