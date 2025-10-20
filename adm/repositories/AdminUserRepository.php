<?php
declare(strict_types=1);

class AdminUserRepository
{
    public function __construct(private MySQLcn $connection)
    {
    }

    public function findById(int $userId): ?array
    {
        $userId = max($userId, 0);
        $this->connection->Query(
            "SELECT usersId, nombres, users, email, nivel, estado, fechaCreada " .
            "FROM usuarios WHERE usersId = {$userId} LIMIT 1"
        );

        $rows = $this->connection->Rows();

        return $rows[0] ?? null;
    }

    public function findFirstByUsernameOrName(string $term): ?array
    {
        $term = trim($term);
        if ($term === '') {
            return null;
        }

        $escapedTerm = $this->connection->SecureInput($term);
        $this->connection->Query(
            "SELECT usersId, nombres, users, email, nivel, estado, fechaCreada " .
            "FROM usuarios " .
            "WHERE (LOWER(users) LIKE LOWER('%{$escapedTerm}%') OR LOWER(nombres) LIKE LOWER('%{$escapedTerm}%')) " .
            "ORDER BY estado DESC, usersId ASC LIMIT 1"
        );

        $rows = $this->connection->Rows();

        return $rows[0] ?? null;
    }

    public function updateAccess(int $userId, int $level, bool $isActive): void
    {
        $level = max(0, $level);
        $estado = $isActive ? 1 : 0;
        $this->connection->UpdateDb(
            "UPDATE usuarios SET nivel = {$level}, estado = {$estado} WHERE usersId = {$userId} LIMIT 1"
        );
    }

    public function revokeAndDeactivate(int $userId): void
    {
        $this->connection->UpdateDb(
            "UPDATE usuarios SET nivel = 0, estado = 0 WHERE usersId = {$userId} LIMIT 1"
        );
    }

    public function deleteUser(int $userId): void
    {
        $this->connection->UpdateDb(
            "DELETE FROM usuarios WHERE usersId = {$userId} LIMIT 1"
        );
    }
}
