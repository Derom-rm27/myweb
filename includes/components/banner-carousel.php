<?php $totalBanners = is_array($banners ?? null) ? count($banners) : 0; ?>
<div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-indicators">
        <?php for ($i = 0; $i < $totalBanners; $i++): ?>
            <button
                type="button"
                data-bs-target="#bannerCarousel"
                data-bs-slide-to="<?php echo $i; ?>"
                <?php echo $i === 0 ? 'class="active"' : ''; ?>
            ></button>
        <?php endfor; ?>
    </div>

    <div class="carousel-inner">
        <?php if ($totalBanners > 0): ?>
            <?php foreach ($banners as $index => $banner): ?>
                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                    <img
                        src="images/banner/<?php echo htmlspecialchars((string) ($banner['Imagen'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                        class="d-block w-100"
                        alt="<?php echo htmlspecialchars((string) ($banner['Titulo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                    >
                    <div class="carousel-caption">
                        <h3><?php echo htmlspecialchars((string) ($banner['Titulo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p><?php echo htmlspecialchars((string) ($banner['Describir'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <?php if (!empty($banner['Enlace'])): ?>
                        <div class="banner-cta banner-cta--overlay">
                            <a
                                href="<?php echo htmlspecialchars((string) $banner['Enlace'], ENT_QUOTES, 'UTF-8'); ?>"
                                class="btn btn-primary banner-cta__button"
                                target="_blank"
                            >
                                Descubre más <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    <?php endif; ?>
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

    <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Anterior</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Siguiente</span>
    </button>
</div>
