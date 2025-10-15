<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = 'Calidad de Software';
include __DIR__ . '/includes/layout/header.php';
?>

<main>
    <?php include __DIR__ . '/includes/components/banner-carousel.php'; ?>
    <?php include __DIR__ . '/includes/components/news-section.php'; ?>
    <?php include __DIR__ . '/includes/components/services-section.php'; ?>
    <?php include __DIR__ . '/includes/components/contact-section.php'; ?>
</main>

<?php include __DIR__ . '/includes/components/site-footer.php'; ?>
<?php include __DIR__ . '/includes/components/login-modal.php'; ?>
<?php include __DIR__ . '/includes/layout/footer.php'; ?>
