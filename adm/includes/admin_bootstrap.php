<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../script/conex.php';

function adminIsAuthenticated(): bool
{
    return isset($_SESSION['login']);
}

function adminEnsureAuthenticated(): void
{
    if (!adminIsAuthenticated()) {
        header('Location: login.php');
        exit();
    }
}

function adminCurrentUserLevel(): int
{
    return (int)($_SESSION['nivel'] ?? 0);
}

function adminRequireSuperuser(): void
{
    adminEnsureAuthenticated();

    if (adminCurrentUserLevel() !== 1) {
        header('Location: user.php');
        exit();
    }
}

function adminCurrentUserName(): string
{
    return (string)($_SESSION['nombre'] ?? 'Usuario');
}

function adminCurrentUserId(): int
{
    return (int)($_SESSION['idUser'] ?? 0);
}

function adminRedirect(string $path, array $params = []): void
{
    $location = $path;

    if (!empty($params)) {
        $query = http_build_query($params);
        if ($query !== '') {
            $separator = strpos($location, '?') === false ? '?' : '&';
            $location .= $separator . $query;
        }
    }

    header("Location: {$location}");
    exit();
}

function adminCreateConnection(): MySQLcn
{
    return new MySQLcn();
}
