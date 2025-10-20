<?php
/**
 * Sidebar layout shared across admin dashboard modules.
 */
$activeSidebar = $activeSidebar ?? '';
$nombreUsuario = $nombreUsuario ?? 'Usuario';
$nivelUsuario = isset($nivelUsuario) ? (int) $nivelUsuario : 0;
$canUploadBanner = $canUploadBanner ?? false;
$canPublishNews = $canPublishNews ?? false;
$canManageBanners = $canManageBanners ?? false;
$canManageNews = $canManageNews ?? false;
?>
<aside class="dashboard-sidebar">
    <div class="sidebar-header">
        <h1 class="sidebar-title">Panel de control</h1>
        <p class="sidebar-subtitle">Gestiona el contenido de manera intuitiva.</p>
    </div>
    <div class="sidebar-user">
        <div class="sidebar-user-icon">
            <i class="fas fa-user-circle"></i>
        </div>
        <div class="sidebar-user-info">
            <span class="sidebar-user-label">Conectado como</span>
            <span class="sidebar-user-name"><?php echo htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
    </div>
    <nav class="sidebar-nav">
        <h2 class="sidebar-section-title">Acciones rápidas</h2>
        <ul class="sidebar-menu">
            <li>
                <?php if ($canUploadBanner): ?>
                    <a class="sidebar-link<?php echo $activeSidebar === 'upload_banner' ? ' active' : ''; ?>" href="user.php">
                        <i class="fas fa-upload"></i>
                        <span>Subir banner</span>
                    </a>
                <?php else: ?>
                    <span class="sidebar-link disabled">
                        <i class="fas fa-upload"></i>
                        <span>Subir banner</span>
                    </span>
                <?php endif; ?>
            </li>
            <li>
                <?php if ($canPublishNews): ?>
                    <a class="sidebar-link<?php echo $activeSidebar === 'publish_news' ? ' active' : ''; ?>" href="news.php">
                        <i class="fas fa-newspaper"></i>
                        <span>Publicar noticia</span>
                    </a>
                <?php else: ?>
                    <span class="sidebar-link disabled">
                        <i class="fas fa-newspaper"></i>
                        <span>Publicar noticia</span>
                    </span>
                <?php endif; ?>
            </li>
            <li>
                <?php if ($canManageBanners): ?>
                    <a class="sidebar-link<?php echo $activeSidebar === 'manage_banners' ? ' active' : ''; ?>" href="manage_banners.php">
                        <i class="fas fa-images"></i>
                        <span>Gestionar banners</span>
                    </a>
                <?php else: ?>
                    <span class="sidebar-link disabled">
                        <i class="fas fa-images"></i>
                        <span>Gestionar banners</span>
                    </span>
                <?php endif; ?>
            </li>
            <li>
                <?php if ($canManageNews): ?>
                    <a class="sidebar-link<?php echo $activeSidebar === 'manage_news' ? ' active' : ''; ?>" href="manage_news.php">
                        <i class="fas fa-edit"></i>
                        <span>Gestionar noticias</span>
                    </a>
                <?php else: ?>
                    <span class="sidebar-link disabled">
                        <i class="fas fa-edit"></i>
                        <span>Gestionar noticias</span>
                    </span>
                <?php endif; ?>
            </li>
        </ul>
    </nav>
    <div class="sidebar-footer">
        <h2 class="sidebar-section-title">Configuración</h2>
        <ul class="sidebar-menu">
            <li>
                <a class="sidebar-link" href="../index.php">
                    <i class="fas fa-home"></i>
                    <span>Página principal</span>
                </a>
            </li>
            <li>
                <a class="sidebar-link<?php echo $activeSidebar === 'change_password' ? ' active' : ''; ?>" href="cambiar_password.php">
                    <i class="fas fa-key"></i>
                    <span>Cambiar contraseña</span>
                </a>
            </li>
            <?php if ($nivelUsuario === 1): ?>
                <li>
                    <a class="sidebar-link<?php echo $activeSidebar === 'grant_permissions' ? ' active' : ''; ?>" href="permissions.php">
                        <i class="fas fa-user-shield"></i>
                        <span>Otorgar permisos</span>
                    </a>
                </li>
                <li>
                    <a class="sidebar-link<?php echo $activeSidebar === 'visit_logs' ? ' active' : ''; ?>" href="visit_logs.php">
                        <i class="fas fa-chart-bar"></i>
                        <span>Registro de visitas</span>
                    </a>
                </li>
            <?php endif; ?>
            <li>
                <a class="sidebar-link text-danger" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Cerrar sesión</span>
                </a>
            </li>
        </ul>
    </div>
</aside>
