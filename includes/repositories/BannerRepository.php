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
     * Fetch every banner stored in the system ordered by most recent first.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAll(): array
    {
        $sql = 'SELECT idBanner, usersId, Titulo, Describir, Enlace, Imagen, estado, fecha '
             . 'FROM banner ORDER BY fecha DESC';

        $this->connection->Query($sql);

        return $this->connection->Rows();
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

    /**
     * Retrieve a banner by its identifier.
     */
    public function findById(int $bannerId): ?array
    {
        $sql = 'SELECT idBanner, usersId, Titulo, Describir, Enlace, Imagen, estado, fecha '
             . 'FROM banner WHERE idBanner = ? LIMIT 1';

        $link = $this->connection->GetLink();
        $statement = $link->prepare($sql);
        if ($statement === false) {
            return null;
        }

        $statement->bind_param('i', $bannerId);
        if (!$statement->execute()) {
            $statement->close();
            return null;
        }

        $result = $statement->get_result();
        $banner = $result ? $result->fetch_assoc() : null;
        $statement->close();

        return $banner ?: null;
    }

    /**
     * Update the banner data for the provided identifier.
     */
    public function update(
        int $bannerId,
        string $title,
        string $description,
        string $link,
        int $status
    ): bool {
        $sql = 'UPDATE banner SET Titulo = ?, Describir = ?, Enlace = ?, estado = ? WHERE idBanner = ?';

        $linkConnection = $this->connection->GetLink();
        $statement = $linkConnection->prepare($sql);
        if ($statement === false) {
            return false;
        }

        $statement->bind_param('sssii', $title, $description, $link, $status, $bannerId);
        $success = $statement->execute();
        $statement->close();

        return $success;
    }

    /**
     * Persist a new image name for the banner.
     */
    public function updateImage(int $bannerId, string $fileName): bool
    {
        $sql = 'UPDATE banner SET Imagen = ? WHERE idBanner = ?';

        $link = $this->connection->GetLink();
        $statement = $link->prepare($sql);
        if ($statement === false) {
            return false;
        }

        $statement->bind_param('si', $fileName, $bannerId);
        $success = $statement->execute();
        $statement->close();

        return $success;
    }

    /**
     * Remove the banner from the storage.
     */
    public function delete(int $bannerId): bool
    {
        $sql = 'DELETE FROM banner WHERE idBanner = ?';

        $link = $this->connection->GetLink();
        $statement = $link->prepare($sql);
        if ($statement === false) {
            return false;
        }

        $statement->bind_param('i', $bannerId);
        $success = $statement->execute();
        $statement->close();

        return $success;
    }
}
