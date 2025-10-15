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
                Don't have an account? <a href="register/index.php">Register</a>
            </div>
        </form>
    </div>
</div>
