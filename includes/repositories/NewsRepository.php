<?php

declare(strict_types=1);

require_once __DIR__ . '/../../adm/script/conex.php';

/**
 * Repository responsible for interacting with the news storage.
 */
class NewsRepository
{
    private MySQLcn $connection;

    public function __construct(MySQLcn $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Fetch the most recent published news entries.
     *
     * @param int $limit Maximum number of news rows to return.
     *
     * @return array<int, array<string, string|null>>
     */
    public function getLatestPublished(int $limit = 3): array
    {
        $limit = max(1, $limit);
        $sql = sprintf(
            "SELECT titulo, cuerpo, imagen, enlace, fecha FROM noticias WHERE estado = 1 ORDER BY fecha DESC LIMIT %d",
            $limit
        );

        $this->connection->Query($sql);

        return $this->connection->Rows();
    }
}
