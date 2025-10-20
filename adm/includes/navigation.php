<?php
declare(strict_types=1);

require_once __DIR__ . '/admin_bootstrap.php';

function adminGetPrimaryNavItems(): array
{
    return [
        [
            'key'    => 'upload_banner',
            'label'  => 'Subir banner',
            'href'   => 'user.php',
            'levels' => [1, 2],
        ],
        [
            'key'    => 'publish_news',
            'label'  => 'Publicar noticia',
            'href'   => 'news.php',
            'levels' => [1, 3],
        ],
        [
            'key'    => 'manage_banners',
            'label'  => 'Gestionar banners',
            'href'   => 'manage_banners.php',
            'levels' => [1],
        ],
        [
            'key'    => 'manage_news',
            'label'  => 'Gestionar noticias',
            'href'   => 'manage_news.php',
            'levels' => [1],
        ],
    ];
}

function adminGetDropdownItems(): array
{
    return [
        [
            'type'   => 'link',
            'key'    => 'home',
            'label'  => 'Página Principal',
            'href'   => '../index.php',
            'icon'   => 'fas fa-home',
            'levels' => [1, 2, 3],
        ],
        [
            'type'   => 'link',
            'key'    => 'change_password',
            'label'  => 'Cambiar Contraseña',
            'href'   => 'change_password.php',
            'icon'   => 'fas fa-key',
            'levels' => [1, 2, 3],
        ],
        [
            'type'   => 'link',
            'key'    => 'permissions',
            'label'  => 'Otorgar permisos',
            'href'   => 'permissions.php',
            'icon'   => 'fas fa-user-shield',
            'levels' => [1],
        ],
        [
            'type'   => 'link',
            'key'    => 'visit_logs',
            'label'  => 'Registro de visitas',
            'href'   => 'visit_logs.php',
            'icon'   => 'fas fa-chart-bar',
            'levels' => [1],
        ],
        [
            'type' => 'divider',
        ],
        [
            'type'   => 'link',
            'key'    => 'logout',
            'label'  => 'Cerrar Sesión',
            'href'   => 'logout.php',
            'icon'   => 'fas fa-sign-out-alt',
            'levels' => [1, 2, 3],
            'class'  => 'text-danger',
        ],
    ];
}

function adminRenderNavbar(string $activePrimary = '', string $activeDropdown = ''): void
{
    $nivelUsuario  = adminCurrentUserLevel();
    $nombreUsuario = adminCurrentUserName();
    $primaryItems  = adminGetPrimaryNavItems();
    $dropdownItems = adminGetDropdownItems();
    ?>
    <nav class="navbar navbar-expand-lg dashboard-navbar">
        <div class="container">
            <a class="navbar-brand" href="#">Panel de Control</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php foreach ($primaryItems as $item): ?>
                        <?php
                        $available = in_array($nivelUsuario, $item['levels'], true);
                        $isActive  = $activePrimary === $item['key'];
                        $linkClass = 'nav-link';
                        if ($isActive) {
                            $linkClass .= ' active';
                        }
                        if (!$available) {
                            $linkClass .= ' disabled';
                        }
                        $href = $available ? $item['href'] : '#';
                        ?>
                        <li class="nav-item">
                            <a class="<?php echo $linkClass; ?>" href="<?php echo htmlspecialchars($href, ENT_QUOTES, 'UTF-8'); ?>"<?php echo $available ? '' : ' tabindex="-1" aria-disabled="true"'; ?>>
                                <?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
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
                            <?php foreach ($dropdownItems as $item): ?>
                                <?php if (($item['type'] ?? '') === 'divider'): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <?php continue; ?>
                                <?php endif; ?>
                                <?php
                                $levels    = $item['levels'] ?? [];
                                $available = empty($levels) || in_array($nivelUsuario, $levels, true);
                                if (!$available) {
                                    continue;
                                }
                                $linkClass = 'dropdown-item';
                                if (!empty($item['class'])) {
                                    $linkClass .= ' ' . $item['class'];
                                }
                                if ($activeDropdown === ($item['key'] ?? '')) {
                                    $linkClass .= ' active';
                                }
                                ?>
                                <li>
                                    <a class="<?php echo $linkClass; ?>" href="<?php echo htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php if (!empty($item['icon'])): ?>
                                            <i class="<?php echo htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8'); ?> me-2"></i>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php
}
