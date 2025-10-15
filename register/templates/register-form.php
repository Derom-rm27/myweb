<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear una cuenta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/home.css">
    <link rel="stylesheet" href="assets/css/register.css">
</head>
<body>
    <div class="register-card">
        <h1>Crea tu cuenta</h1>
        <p>Elige el nivel adecuado para tu cuenta y obtén acceso inmediato.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($successMessage !== ''): ?>
            <div class="alert alert-success" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" novalidate>
            <div class="mb-3">
                <label for="nombres" class="form-label">Nombre completo</label>
                <input type="text" class="form-control" id="nombres" name="nombres" data-initial-focus="true" value="<?php echo htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="mb-3">
                <label for="users" class="form-label">Nombre de usuario</label>
                <input type="text" class="form-control" id="users" name="users" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Correo electrónico</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" placeholder="tu@correo.com">
            </div>
            <div class="mb-3">
                <label for="nivel" class="form-label">Nivel de acceso</label>
                <select class="form-select" id="nivel" name="nivel" required>
                    <option value="2" <?php echo $nivelSeleccionado === '2' ? 'selected' : ''; ?>>Nivel 2 - Puede subir banners</option>
                    <option value="3" <?php echo $nivelSeleccionado === '3' ? 'selected' : ''; ?>>Nivel 3 - Puede publicar noticias</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="perfil" class="form-label">Perfil o descripción (opcional)</label>
                <textarea class="form-control" id="perfil" name="perfil" rows="3" placeholder="Cuéntanos un poco sobre ti..."><?php echo htmlspecialchars($perfil, ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="pass" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="pass" name="pass" required>
            </div>
            <div class="mb-3">
                <label for="confirm_pass" class="form-label">Confirmar contraseña</label>
                <input type="password" class="form-control" id="confirm_pass" name="confirm_pass" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Registrarme</button>
        </form>

        <div class="existing-account">
            ¿Ya tienes una cuenta? <a href="../index.php?login=1" class="login-link">Inicia sesión</a>
        </div>
    </div>

    <script src="assets/js/register.js"></script>
</body>
</html>
