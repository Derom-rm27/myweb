<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$sessionActive = false;
$sessionTarget = 'adm/user.php';

if (isset($_SESSION['login'])) {
    $sessionLifetime = 12000; // 200 minutos, como en el panel de administración
    $lastActivityRaw = $_SESSION['hora'] ?? null;
    $lastActivity = $lastActivityRaw !== null ? strtotime((string) $lastActivityRaw) : false;

    $sessionActive = true;

    if ($lastActivity !== false) {
        $elapsed = time() - $lastActivity;

        if ($elapsed >= $sessionLifetime) {
            $sessionActive = false;
        }
    }

    if ($sessionActive) {
        $_SESSION['hora'] = date('Y-n-j H:i:s');

        $nivel = isset($_SESSION['nivel']) ? (int) $_SESSION['nivel'] : 0;
        if (!in_array($nivel, [1, 2], true)) {
            $sessionTarget = 'adm/news.php';
        }
    }
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">Calidad de Software</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="#news">Noticias</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#services">Servicios</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#contact">Contacto</a>
                </li>
                <li class="nav-item">
                    <?php if ($sessionActive): ?>
                        <a class="nav-link" href="<?php echo htmlspecialchars($sessionTarget, ENT_QUOTES, 'UTF-8'); ?>">Iniciar Sesión</a>
                    <?php else: ?>
                        <a class="nav-link" href="#" onclick="openLoginModal(); return false;">Iniciar Sesión</a>
                    <?php endif; ?>
                </li>
            </ul>
        </div>
    </div>
</nav>
