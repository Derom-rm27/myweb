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
     * Fetch every news entry ordered by the most recent publication date.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAll(): array
    {
        $sql = 'SELECT idNoticia, usersId, titulo, cuerpo, imagen, estado, fecha '
             . 'FROM noticias ORDER BY fecha DESC';

        $this->connection->Query($sql);

        return $this->connection->Rows();
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

    /**
     * Retrieve a news entry by its identifier.
     */
    public function findById(int $newsId): ?array
    {
        $sql = 'SELECT idNoticia, usersId, titulo, cuerpo, imagen, estado, fecha '
             . 'FROM noticias WHERE idNoticia = ? LIMIT 1';

        $link = $this->connection->GetLink();
        $statement = $link->prepare($sql);
        if ($statement === false) {
            return null;
        }

        $statement->bind_param('i', $newsId);
        if (!$statement->execute()) {
            $statement->close();
            return null;
        }

        $result = $statement->get_result();
        $news = $result ? $result->fetch_assoc() : null;
        $statement->close();

        return $news ?: null;
    }

    /**
     * Update the stored news entry.
     */
    public function update(
        int $newsId,
        string $title,
        string $body,
        int $status
    ): bool {
        $sql = 'UPDATE noticias SET titulo = ?, cuerpo = ?, estado = ? WHERE idNoticia = ?';

        $link = $this->connection->GetLink();
        $statement = $link->prepare($sql);
        if ($statement === false) {
            return false;
        }

        $statement->bind_param('ssii', $title, $body, $status, $newsId);
        $success = $statement->execute();
        $statement->close();

        return $success;
    }

    /**
     * Persist a new image reference for the news entry.
     */
    public function updateImage(int $newsId, ?string $fileName): bool
    {
        $sql = 'UPDATE noticias SET imagen = ? WHERE idNoticia = ?';

        $link = $this->connection->GetLink();
        $statement = $link->prepare($sql);
        if ($statement === false) {
            return false;
        }

        $statement->bind_param('si', $fileName, $newsId);
        $success = $statement->execute();
        $statement->close();

        return $success;
    }

    /**
     * Remove the specified news entry from storage.
     */
    public function delete(int $newsId): bool
    {
        $sql = 'DELETE FROM noticias WHERE idNoticia = ?';

        $link = $this->connection->GetLink();
        $statement = $link->prepare($sql);
        if ($statement === false) {
            return false;
        }

        $statement->bind_param('i', $newsId);
        $success = $statement->execute();
        $statement->close();

        return $success;
    }
}
