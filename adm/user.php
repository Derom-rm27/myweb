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
            <div class="col-md-8">
                <?php if (isset($_GET['mensaje'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($_GET['mensaje']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Subir Nueva Imagen</h4>
                    </div>
                    <div class="card-body">
                        <form action="procesar_imagen.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label for="titulo" class="form-label mb-0">Título</label>
                                    <span>Estado</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <input type="text" class="form-control" id="titulo" name="titulo" required style="width: 70%;">
                                    <div class="form-check form-switch ms-3">
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

