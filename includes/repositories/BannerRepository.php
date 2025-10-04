<?php

declare(strict_types=1);

require_once __DIR__ . '/../../adm/script/conex.php';

/**
 * Repository responsible for interacting with the banner storage.
 */
class BannerRepository
{
    private MySQLcn $connection;

    public function __construct(MySQLcn $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Fetch all active banners sorted by most recent first.
     *
     * @return array<int, array<string, string|null>>
     */
    public function getActiveBanners(): array
    {
        $sql = "SELECT Titulo, Describir, Enlace, Imagen FROM banner WHERE estado = 1 ORDER BY fecha DESC";
        $this->connection->Query($sql);
        return $this->connection->Rows();
    }
}
