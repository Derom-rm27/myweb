(function () {
    document.addEventListener('DOMContentLoaded', () => {
        const modalOverlay = document.getElementById('loginModal');
        const loginForm = document.getElementById('loginForm');
        const loginBtn = loginForm ? loginForm.querySelector('.login-btn') : null;
        const messageEl = document.getElementById('loginMessage');
        const captchaImage = document.getElementById('captchaImage');
        const refreshCaptchaBtn = document.getElementById('refreshCaptcha');
        const originalButtonText = loginBtn ? loginBtn.textContent : '';

        if (!modalOverlay) {
            return;
        }

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

        const resetLoginForm = () => {
            if (!loginForm) {
                return;
            }

            loginForm.reset();
            setMessage('', '');

            if (loginBtn) {
                loginBtn.textContent = originalButtonText || 'Login';
                loginBtn.classList.remove('loading');
                loginBtn.disabled = false;
            }

            refreshCaptcha();
        };

        const clearFormFields = () => {
            if (!loginForm) {
                return;
            }

            loginForm.reset();
        };

        const openLoginModal = () => {
            resetLoginForm();
            modalOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        };

        const closeLoginModal = () => {
            modalOverlay.classList.remove('active');
            document.body.style.overflow = 'auto';
            resetLoginForm();
        };

        const shouldAutoOpen = () => {
            const params = new URLSearchParams(window.location.search);
            const loginParam = (params.get('login') || '').toLowerCase();

            return loginParam === '1' || loginParam === 'true' || loginParam === 'yes' || loginParam === 'open';
        };

        const clearAutoOpenParam = () => {
            const params = new URLSearchParams(window.location.search);

            if (!params.has('login')) {
                return;
            }

            params.delete('login');
            const newSearch = params.toString();
            const newUrl = `${window.location.pathname}${newSearch ? `?${newSearch}` : ''}${window.location.hash}`;
            window.history.replaceState({}, document.title, newUrl);
        };

        if (captchaImage) {
            captchaImage.addEventListener('click', refreshCaptcha);
        }

        if (refreshCaptchaBtn) {
            refreshCaptchaBtn.addEventListener('click', refreshCaptcha);
        }

        modalOverlay.addEventListener('click', (event) => {
            if (event.target === modalOverlay) {
                closeLoginModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeLoginModal();
            }
        });

        if (shouldAutoOpen()) {
            openLoginModal();
            clearAutoOpenParam();
        }

        if (loginForm && loginBtn) {
            loginForm.addEventListener('submit', async (event) => {
                event.preventDefault();

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

                    clearFormFields();
                    refreshCaptcha();
                    setMessage(data.message || 'No fue posible iniciar sesión.', 'error');
                } catch (error) {
                    clearFormFields();
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

        window.openLoginModal = openLoginModal;
        window.closeLoginModal = closeLoginModal;
        window.showLoginModal = openLoginModal;
        window.hideLoginModal = closeLoginModal;
    });
})();
