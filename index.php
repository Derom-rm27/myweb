<?php
require_once("adm/script/conex.php");

// Obtener banners activos
$cn = new MySQLcn();
$sql = "SELECT Titulo, Describir, Enlace, Imagen FROM banner WHERE estado = 1 ORDER BY fecha DESC";
$cn->Query($sql);
$banners = $cn->Rows();
$cn->Close();

// Debug para verificar los datos
// echo "<pre>"; print_r($banners); echo "</pre>";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calidad de Software</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
    <style>
        /* HEADER MEJORADO CON ESTILO CYBERPUNK */
        .navbar {
            background: linear-gradient(135deg, #1a237e 0%, #3949ab 50%, #5c6bc0 100%) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(26, 35, 126, 0.3);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .navbar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(ellipse 200px 100px at 20% 50%, rgba(64, 196, 255, 0.1) 0%, transparent 50%),
                radial-gradient(ellipse 150px 80px at 80% 30%, rgba(255, 23, 68, 0.1) 0%, transparent 50%);
            pointer-events: none;
            z-index: 1;
        }

        .navbar-brand {
            font-weight: 700 !important;
            font-size: 1.8rem !important;
            color: white !important;
            text-shadow: 0 0 20px rgba(64, 196, 255, 0.5);
            position: relative;
            z-index: 2;
        }

        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            z-index: 2;
            margin: 0 5px;
            padding: 10px 15px !important;
            border-radius: 8px;
        }

        .navbar-nav .nav-link:hover {
            color: #40c4ff !important;
            background: rgba(64, 196, 255, 0.1);
            text-shadow: 0 0 10px rgba(64, 196, 255, 0.8);
            transform: translateY(-1px);
        }

        .navbar-nav .nav-link.active {
            color: #40c4ff !important;
            background: rgba(64, 196, 255, 0.15);
            text-shadow: 0 0 15px rgba(64, 196, 255, 1);
        }

        .navbar-toggler {
            border: none;
            padding: 4px 8px;
            position: relative;
            z-index: 2;
        }

        .navbar-toggler:focus {
            box-shadow: 0 0 0 0.25rem rgba(64, 196, 255, 0.3);
        }

        /* CARRUSEL MEJORADO */
        .carousel-item {
            height: 500px;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
        }

        .carousel-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(26, 35, 126, 0.2) 0%, rgba(57, 73, 171, 0.1) 100%);
            z-index: 1;
        }

        .carousel-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .carousel-caption {
            background: rgba(26, 35, 126, 0.8);
            backdrop-filter: blur(10px);
            padding: 25px;
            border-radius: 15px;
            max-width: 80%;
            margin: 0 auto;
            border: 1px solid rgba(64, 196, 255, 0.2);
            position: relative;
            z-index: 2;
        }

        .carousel-caption h3 {
            font-size: 2.2rem;
            margin-bottom: 15px;
            color: white;
            text-shadow: 0 0 20px rgba(64, 196, 255, 0.5);
            font-weight: 600;
        }

        .carousel-caption p {
            font-size: 1.2rem;
            margin-bottom: 20px;
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.6;
        }

        .carousel-caption .btn-primary {
            background: linear-gradient(135deg, #40c4ff 0%, #00bcd4 100%);
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .carousel-caption .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(64, 196, 255, 0.4);
        }

        /* INDICADORES DEL CARRUSEL */
        .carousel-indicators [data-bs-target] {
            background-color: rgba(64, 196, 255, 0.6);
            border: none;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin: 0 4px;
            transition: all 0.3s ease;
        }

        .carousel-indicators [data-bs-target].active {
            background-color: #40c4ff;
            box-shadow: 0 0 10px rgba(64, 196, 255, 0.8);
            transform: scale(1.2);
        }

        /* CONTROLES DEL CARRUSEL */
        .carousel-control-prev,
        .carousel-control-next {
            width: 5%;
            transition: all 0.3s ease;
        }

        .carousel-control-prev:hover,
        .carousel-control-next:hover {
            background: rgba(64, 196, 255, 0.1);
        }

        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            background-size: 100% 100%;
            filter: drop-shadow(0 0 10px rgba(64, 196, 255, 0.8));
        }

        /* SECCIÓN DE NOTICIAS MEJORADA */
        .news-section {
            background: linear-gradient(135deg, #f8f9ff 0%, #e3f2fd 100%);
            position: relative;
        }

        .news-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(ellipse 300px 150px at 20% 80%, rgba(64, 196, 255, 0.05) 0%, transparent 50%),
                radial-gradient(ellipse 200px 100px at 80% 20%, rgba(57, 73, 171, 0.05) 0%, transparent 50%);
            pointer-events: none;
        }

        .news-section h2 {
            color: #1a237e;
            font-weight: 700;
            text-shadow: 0 0 20px rgba(26, 35, 126, 0.3);
            position: relative;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(64, 196, 255, 0.2);
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #40c4ff, #00bcd4);
            z-index: 1;
        }

        .card-body .btn-primary {
            background: linear-gradient(135deg, #4c6ef5 0%, #364fc7 100%);
            border: none;
            border-radius: 25px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .card-body .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(76, 110, 245, 0.3);
        }

        /* ESTILOS DEL MODAL DE LOGIN */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .login-modal {
            background: linear-gradient(135deg, #4c6ef5 0%, #364fc7 100%);
            border-radius: 20px;
            padding: 40px;
            width: 400px;
            max-width: 90vw;
            position: relative;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
            transform: scale(0.7) translateY(50px);
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .modal-overlay.active .login-modal {
            transform: scale(1) translateY(0);
        }

        .close-btn {
            position: absolute;
            top: 20px;
            right: 25px;
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.8);
            font-size: 24px;
            cursor: pointer;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .close-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: rotate(90deg);
        }

        .modal-title {
            color: white;
            font-size: 32px;
            font-weight: 400;
            margin-bottom: 40px;
            text-align: center;
            letter-spacing: 1px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-input {
            width: 100%;
            padding: 18px 50px 18px 20px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            color: white;
            font-size: 16px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.6);
            font-weight: 300;
        }

        .form-input:focus {
            outline: none;
            border-color: rgba(255, 255, 255, 0.5);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .input-icon {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.5);
            font-size: 18px;
            pointer-events: none;
        }

        .form-message {
            min-height: 24px;
            text-align: center;
            color: rgba(255, 255, 255, 0.85);
            font-size: 15px;
            margin-bottom: 20px;
            font-weight: 400;
        }

        .form-message.error {
            color: #ffb4b4;
        }

        .form-message.success {
            color: #c8f7c5;
        }

        .captcha-group {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 10px 12px;
            gap: 12px;
        }

        .captcha-group img {
            flex: 1;
            height: 44px;
            border-radius: 10px;
            background: #ffffff;
            object-fit: cover;
            cursor: pointer;
        }

        .refresh-captcha {
            background: rgba(64, 196, 255, 0.2);
            border: none;
            color: #ffffff;
            font-size: 20px;
            width: 48px;
            height: 44px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .refresh-captcha:hover {
            background: rgba(64, 196, 255, 0.35);
            transform: rotate(90deg);
        }

        .captcha-hint {
            display: block;
            margin-top: 8px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
            text-align: center;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 300;
        }

        .remember-me input[type="checkbox"] {
            margin-right: 10px;
            width: 18px;
            height: 18px;
            accent-color: #40c4ff;
            cursor: pointer;
        }

        .forgot-password {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 300;
        }

        .forgot-password:hover {
            color: #40c4ff;
            text-shadow: 0 0 10px rgba(64, 196, 255, 0.5);
        }

        .login-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #40c4ff 0%, #00bcd4 100%);
            color: #1a237e;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(64, 196, 255, 0.4);
            background: linear-gradient(135deg, #00bcd4 0%, #40c4ff 100%);
        }

        .login-btn:active {
            transform: translateY(-1px);
        }

        .login-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .register-link {
            text-align: center;
            color: rgba(255, 255, 255, 0.8);
            font-size: 15px;
            font-weight: 300;
        }

        .register-link a {
            color: #40c4ff;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .register-link a:hover {
            color: white;
            text-shadow: 0 0 10px rgba(64, 196, 255, 0.8);
        }

        .loading {
            position: relative;
            overflow: hidden;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        @media (max-width: 480px) {
            .login-modal {
                width: 95vw;
                padding: 30px 25px;
            }
            
            .modal-title {
                font-size: 28px;
                margin-bottom: 30px;
            }
            
            .form-input {
                padding: 16px 45px 16px 18px;
                font-size: 15px;
            }
            
            .login-btn {
                padding: 16px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Menu -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Calidad de Software</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Noticias</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Servicios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Contacto</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="openLoginModal()">Iniciar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Banner Slider -->
    <div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel">
        <!-- Indicadores -->
        <div class="carousel-indicators">
            <?php for($i = 0; $i < count($banners); $i++): ?>
            <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="<?php echo $i; ?>" 
                    <?php echo $i === 0 ? 'class="active"' : ''; ?>></button>
            <?php endfor; ?>
        </div>

        <!-- Slides -->
        <div class="carousel-inner">
            <?php if(!empty($banners)): ?>
                <?php foreach($banners as $index => $banner): ?>
                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                    <img src="images/banner/<?php echo htmlspecialchars($banner['Imagen']); ?>" 
                         class="d-block w-100" alt="<?php echo htmlspecialchars($banner['Titulo']); ?>">
                    <div class="carousel-caption">
                        <h3><?php echo htmlspecialchars($banner['Titulo']); ?></h3>
                        <p><?php echo htmlspecialchars($banner['Describir']); ?></p>
                        <?php if(!empty($banner['Enlace'])): ?>
                        <a href="<?php echo htmlspecialchars($banner['Enlace']); ?>" class="btn btn-primary" target="_blank">
                            Ver más <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="carousel-item active">
                    <img src="images/news/news_1.png" class="d-block w-100" alt="Banner por defecto">
                    <div class="carousel-caption">
                        <h3>Bienvenido</h3>
                        <p>No hay banners activos en este momento.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Controles -->
        <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Anterior</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Siguiente</span>
        </button>
    </div>

    <!-- News Section -->
    <section class="news-section py-5">
        <div class="container">
            <h2 class="text-center mb-4">Últimas Noticias</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="images/news/news_1.png" class="card-img-top" alt="Testing Automatizado">
                        <div class="card-body">
                            <h5 class="card-title">Testing Automatizado</h5>
                            <p class="card-text">Descubre las mejores herramientas y prácticas para implementar pruebas automatizadas en tu proyecto.</p>
                            <a href="#" class="btn btn-primary">Leer más</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="images/news/news_2.jpg" class="card-img-top" alt="DevOps">
                        <div class="card-body">
                            <h5 class="card-title">DevOps y Calidad</h5>
                            <p class="card-text">Integración continua y entrega continua: claves para mantener la calidad del software.</p>
                            <a href="#" class="btn btn-primary">Leer más</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="images/news/news_3.jpg" class="card-img-top" alt="Seguridad">
                        <div class="card-body">
                            <h5 class="card-title">Seguridad en el Desarrollo</h5>
                            <p class="card-text">Mejores prácticas para implementar seguridad desde las primeras fases del desarrollo.</p>
                            <a href="#" class="btn btn-primary">Leer más</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p>&copy; 2024 Calidad de Software. Todos los derechos reservados.</p>
        </div>
    </footer>

    <!-- MODAL DE LOGIN -->
    <div class="modal-overlay" id="loginModal">
        <div class="login-modal">
            <button class="close-btn" onclick="closeLoginModal()">×</button>
            
            <h2 class="modal-title">Login</h2>
            
            <form id="loginForm" action="adm/login.php" method="POST" autocomplete="off">
                <div id="loginMessage" class="form-message" role="alert" aria-live="polite"></div>

                <div class="form-group">
                    <input type="text" class="form-input" name="users" placeholder="Usuario" required autocomplete="username">
                    <span class="input-icon">👤</span>
                </div>

                <div class="form-group">
                    <input type="password" class="form-input" name="pass" placeholder="Contraseña" required autocomplete="current-password">
                    <span class="input-icon">🔒</span>
                </div>

                <div class="form-group">
                    <div class="captcha-group">
                        <img src="adm/script/generax.php?img=true" alt="Captcha" id="captchaImage" data-base-src="adm/script/generax.php?img=true">
                        <button type="button" class="refresh-captcha" id="refreshCaptcha" aria-label="Actualizar código de seguridad">⟳</button>
                    </div>
                    <span class="captcha-hint">Haz clic en la imagen o en el botón para actualizar el código.</span>
                </div>

                <div class="form-group">
                    <input type="text" class="form-input" name="clave" placeholder="Ingrese el código de seguridad" required autocomplete="off">
                    <span class="input-icon">🔐</span>
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox">
                        Remember me
                    </label>
                    <a href="#" class="forgot-password">Forgot Password?</a>
                </div>
                
                <button type="submit" class="login-btn">Login</button>
                
                <div class="register-link">
                    Don't have an account? <a href="#" onclick="showRegister()">Register</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="js/main.js"></script>

    <!-- JAVASCRIPT DEL MODAL -->
    <script>
        // Funciones del modal
        function openLoginModal() {
            document.getElementById('loginModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeLoginModal() {
            document.getElementById('loginModal').classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        function showRegister() {
            alert('Aquí iría el formulario de registro o redirección');
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('loginModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeLoginModal();
            }
        });

        // Cerrar modal con tecla ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLoginModal();
            }
        });

        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            const loginBtn = loginForm.querySelector('.login-btn');
            const messageEl = document.getElementById('loginMessage');
            const captchaImage = document.getElementById('captchaImage');
            const refreshCaptchaBtn = document.getElementById('refreshCaptcha');
            const originalButtonText = loginBtn ? loginBtn.textContent : '';

            const setMessage = (text, type = 'error') => {
                if (!messageEl) {
                    return;
                }

                messageEl.textContent = text || '';
                messageEl.classList.remove('error', 'success');

                if (text && type) {
                    messageEl.classList.add(type);
                }
            };

            const refreshCaptcha = () => {
                if (!captchaImage) {
                    return;
                }

                const baseSrc = captchaImage.dataset.baseSrc || captchaImage.src;
                const separator = baseSrc.includes('?') ? '&' : '?';
                captchaImage.src = `${baseSrc}${separator}t=${Date.now()}`;
            };

            if (captchaImage) {
                captchaImage.addEventListener('click', refreshCaptcha);
            }

            if (refreshCaptchaBtn) {
                refreshCaptchaBtn.addEventListener('click', refreshCaptcha);
            }

            loginForm.addEventListener('submit', async function (e) {
                e.preventDefault();

                if (!loginBtn) {
                    return;
                }

                setMessage('', '');
                loginBtn.textContent = 'Ingresando...';
                loginBtn.classList.add('loading');
                loginBtn.disabled = true;

                try {
                    const formData = new FormData(loginForm);
                    formData.append('ajax', '1');

                    const response = await fetch(loginForm.getAttribute('action') || 'adm/login.php', {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Respuesta no válida del servidor.');
                    }

                    const data = await response.json();

                    if (data.success) {
                        setMessage('Acceso concedido. Redirigiendo...', 'success');
                        const target = data.redirect && data.redirect.length > 0 ? data.redirect : 'adm/user.php';
                        window.location.href = target;
                        return;
                    }

                    refreshCaptcha();
                    setMessage(data.message || 'No fue posible iniciar sesión.', 'error');
                } catch (error) {
                    refreshCaptcha();
                    setMessage('Ocurrió un error al iniciar sesión. Intente nuevamente.', 'error');
                    console.error(error);
                } finally {
                    loginBtn.textContent = originalButtonText || 'Login';
                    loginBtn.classList.remove('loading');
                    loginBtn.disabled = false;
                }
            });
        }

        // Funciones globales para usar desde otros lugares
        window.showLoginModal = openLoginModal;
        window.hideLoginModal = closeLoginModal;
    </script>
</body>
</html>