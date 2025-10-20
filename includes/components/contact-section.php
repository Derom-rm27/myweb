<?php
$contactFeedback = null;
$contactOldInput = ['nombre' => '', 'correo' => '', 'mensaje' => ''];

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['contact_feedback'])) {
    $contactFeedback = $_SESSION['contact_feedback'];
    unset($_SESSION['contact_feedback']);
}

if (isset($_SESSION['contact_old_input'])) {
    $contactOldInput = array_merge($contactOldInput, $_SESSION['contact_old_input']);
    unset($_SESSION['contact_old_input']);
}
?>
<section id="contact" class="contact-section py-5">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <div class="section-header mb-4">
                    <p class="section-eyebrow">Contacto</p>
                    <h2 class="section-title">¿Necesitas ayuda de un superusuario?</h2>
                </div>
                <p class="section-subtitle">
                    Completa el siguiente formulario y uno de nuestros superusuarios recibirá tu consulta directamente
                    en su correo electrónico. Te responderemos lo antes posible.
                </p>
                <ul class="list-unstyled contact-benefits mb-0">
                    <li class="mb-2">
                        <i class="fas fa-check-circle" aria-hidden="true"></i>
                        Soporte para recuperación de accesos y permisos.
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle" aria-hidden="true"></i>
                        Orientación sobre publicación de noticias y banners.
                    </li>
                    <li>
                        <i class="fas fa-check-circle" aria-hidden="true"></i>
                        Acompañamiento en la configuración de cuentas.
                    </li>
                </ul>
            </div>
            <div class="col-lg-6">
                <div class="contact-card shadow-sm">
                    <div class="contact-card__body">
                        <h3 class="contact-card__title">Escríbenos</h3>
                        <?php if ($contactFeedback !== null): ?>
                            <?php
                            $alertClass = ($contactFeedback['type'] ?? '') === 'success' ? 'alert-success' : 'alert-danger';
                            $message     = htmlspecialchars((string)($contactFeedback['message'] ?? ''), ENT_QUOTES, 'UTF-8');
                            ?>
                            <div class="alert <?php echo $alertClass; ?>" role="alert">
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>
                        <form action="includes/actions/contact-superuser.php" method="post" novalidate class="contact-form">
                            <div class="form-floating mb-3">
                                <input
                                    type="text"
                                    class="form-control"
                                    id="contact-name"
                                    name="nombre"
                                    placeholder=" "
                                    required
                                    value="<?php echo htmlspecialchars($contactOldInput['nombre'], ENT_QUOTES, 'UTF-8'); ?>"
                                >
                                <label for="contact-name">Nombre completo</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input
                                    type="email"
                                    class="form-control"
                                    id="contact-email"
                                    name="correo"
                                    placeholder=" "
                                    required
                                    value="<?php echo htmlspecialchars($contactOldInput['correo'], ENT_QUOTES, 'UTF-8'); ?>"
                                >
                                <label for="contact-email">Correo electrónico</label>
                            </div>
                            <div class="form-floating mb-4">
                                <textarea
                                    class="form-control"
                                    id="contact-message"
                                    name="mensaje"
                                    rows="5"
                                    placeholder=" "
                                    required
                                    style="height: 160px;"
                                ><?php echo htmlspecialchars($contactOldInput['mensaje'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                                <label for="contact-message">Mensaje</label>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Enviar mensaje</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
