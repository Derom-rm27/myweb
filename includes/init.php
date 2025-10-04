<?php

declare(strict_types=1);

require_once __DIR__ . '/repositories/BannerRepository.php';

$databaseConnection = new MySQLcn();
$bannerRepository   = new BannerRepository($databaseConnection);
$banners            = $bannerRepository->getActiveBanners();
$databaseConnection->Close();
