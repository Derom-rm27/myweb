<?php

declare(strict_types=1);

require_once __DIR__ . '/repositories/BannerRepository.php';
require_once __DIR__ . '/repositories/NewsRepository.php';

$banners    = [];
$latestNews = [];

try {
    $databaseConnection = new MySQLcn();
    $bannerRepository   = new BannerRepository($databaseConnection);
    $newsRepository     = new NewsRepository($databaseConnection);

    $banners    = $bannerRepository->getActiveBanners();
    $latestNews = $newsRepository->getLatestPublished(3);

    $databaseConnection->Close();
} catch (Throwable $exception) {
    $banners    = [];
    $latestNews = [];
}
