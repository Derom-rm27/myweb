<?PHP
// VERSION DE PARCHE  1.5.0.1
session_start();
if(!isset($_SESSION['login'])){
header("location:login.php");
}else{
  //sino, calculamos el tiempo transcurrido
  $fecGuar = $_SESSION["hora"];
  $idweb="<input type='hidden' name='idweb' id='idweb' value='".$_SESSION["idGrupo"]."' />";
  $iduser="<input type='hidden' name='iduser' id='iduser' value='".$_SESSION["idUser"]."' />";
  $idnivel="<input type='hidden' name='idnivel' id='idnivel' value='".$_SESSION["nivel"]."' />";
  $ahora = date("Y-n-j H:i:s");
  $tmpTrans = (strtotime($ahora)-strtotime($fecGuar));
  //comparamos el tiempo transcurrido
  if($tmpTrans >= 12000){
  //si pasaron 10 minutos o más
        session_destroy(); // destruyo la sesión
        header("Location: login.php"); //envío al usuario a la pag. de autenticación
  }else{ //sino, actualizo la fecha de la sesión
        $_SESSION["hora"] = $ahora;
  }
}

$nivelUsuario = (int)($_SESSION['nivel'] ?? 0);
if (!in_array($nivelUsuario, [1, 2], true)) {
    header('Location: news.php');
    exit();
}

$usuarioId = (int) ($_SESSION['idUser'] ?? 0);
$nombreUsuario = $_SESSION["nombre"] ?? 'Usuario';
$mensajeFlash = isset($_GET['mensaje']) ? trim((string)$_GET['mensaje']) : '';
$errorFlash = isset($_GET['error']) ? trim((string)$_GET['error']) : '';
$canUploadBanner = in_array($nivelUsuario, [1, 2], true);
$canPublishNews = in_array($nivelUsuario, [1, 3], true);
$canGrantPermissions = ($nivelUsuario === 1);
$canManageBanners = ($nivelUsuario === 1);
$canManageNews = ($nivelUsuario === 1);

require_once __DIR__ . '/../includes/repositories/PermissionRepository.php';

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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Imagen</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard-theme.css">
</head>
<body class="dashboard-body">
<div class="dashboard-shell">
    <?php
    $activeSidebar = 'upload_banner';
    require __DIR__ . '/includes/dashboard_sidebar.php';
    ?>

    <main class="dashboard-main">
        <div class="dashboard-main-inner">
            <div class="row justify-content-center">
                <div class="col-md-8">
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
                    <div class="card-header">
                        <h4 class="mb-0">Subir Nueva Imagen</h4>
                    </div>
                    <div class="card-body">
                        <form action="procesar_imagen.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label for="titulo" class="form-label mb-0">Título</label>
                                    <span>Estado</span>
                                </div>
                                <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3">
                                    <input type="text" class="form-control flex-grow-1" id="titulo" name="titulo" required>
                                    <div class="form-check form-switch ms-sm-2">
                                        <input class="form-check-input" type="checkbox" id="toggleSwitch" name="estado">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="titulo" class="form-label">Link</label>
                                <input type="text" class="form-control" id="titulo" name="link" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Imagen</label>
                                <div class="upload-area" id="uploadArea">
                                    <i class="fas fa-cloud-upload-alt fa-3x mb-3"></i>
                                    <p class="mb-0">Arrastra y suelta una imagen aquí o haz clic para seleccionar</p>
                                    <input type="file" class="d-none" id="imagen" name="imagen" accept="image/*" required>
                                </div>
                                <div id="preview" class="text-center mt-3"></div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload me-2"></i>Subir Imagen
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
        // Manejo de la previsualización de la imagen
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('imagen');
        const preview = document.getElementById('preview');

        uploadArea.addEventListener('click', () => fileInput.click());

        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
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
                preview.innerHTML = `<img src="${e.target.result}" class="preview-image" alt="Vista previa">`;
            };
            reader.readAsDataURL(file);
        }
    </script>
</body>
</html>

