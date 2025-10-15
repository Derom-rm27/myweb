<?php
$newsItems = is_array($latestNews ?? null) ? $latestNews : [];
$newsItems = array_slice($newsItems, 0, 3);
$truncate = static function (string $text, int $length = 180): string {
    if (function_exists('mb_strlen')) {
        if (mb_strlen($text, 'UTF-8') <= $length) {
            return $text;
        }

        return rtrim(mb_substr($text, 0, $length, 'UTF-8')) . '…';
    }

    if (strlen($text) <= $length) {
        return $text;
    }

    return rtrim(substr($text, 0, $length)) . '…';
};
?>
<section id="news" class="news-section py-5">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <p class="text-uppercase text-muted mb-1">Noticias</p>
                <h2 class="mb-0">Últimas publicaciones</h2>
            </div>
            <a href="#news" class="btn btn-outline-primary mt-3 mt-md-0 disabled" aria-disabled="true">
                Gestión en construcción
            </a>
        </div>
        <?php if (!empty($newsItems)): ?>
            <div class="row g-4">
                <?php foreach ($newsItems as $news): ?>
                    <?php
                    $title   = htmlspecialchars((string) ($news['titulo'] ?? ''), ENT_QUOTES, 'UTF-8');
                    $body    = (string) ($news['cuerpo'] ?? '');
                    $excerpt = htmlspecialchars($truncate($body), ENT_QUOTES, 'UTF-8');
                    $date    = !empty($news['fecha']) ? date_create((string) $news['fecha']) : null;
                    $image   = !empty($news['imagen'])
                        ? 'images/news/' . ltrim((string) $news['imagen'], '/')
                        : 'images/news/news_1.png';
                    $linkRaw = trim((string) ($news['enlace'] ?? ''));
                    $link    = $linkRaw !== '' ? htmlspecialchars($linkRaw, ENT_QUOTES, 'UTF-8') : '';
                    ?>
                    <div class="col-sm-6 col-lg-4">
                        <article class="card h-100 shadow-sm border-0">
                            <div class="ratio ratio-16x9">
                                <img
                                    src="<?php echo htmlspecialchars($image, ENT_QUOTES, 'UTF-8'); ?>"
                                    class="card-img-top object-fit-cover"
                                    alt="<?php echo $title; ?>"
                                >
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo $title; ?></h5>
                                <?php if ($date instanceof DateTimeInterface): ?>
                                    <p class="card-subtitle text-muted small mb-3">
                                        <?php echo htmlspecialchars($date->format('d \d\e F \d\e Y'), ENT_QUOTES, 'UTF-8'); ?>
                                    </p>
                                <?php endif; ?>
                                <p class="card-text flex-grow-1">
                                    <?php echo $excerpt; ?>
                                </p>
                                <div class="mt-3">
                                    <?php if ($link !== ''): ?>
                                        <a
                                            class="btn btn-link p-0"
                                            href="<?php echo $link; ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            Leer más
                                        </a>
                                    <?php else: ?>
                                        <span class="btn btn-link p-0 disabled" aria-disabled="true">Leer más próximamente</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <h3 class="fw-semibold">Todavía no hay noticias publicadas</h3>
                <p class="text-muted mb-0">En cuanto se carguen noticias aparecerán automáticamente aquí.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
