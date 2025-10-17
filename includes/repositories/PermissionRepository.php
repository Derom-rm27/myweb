<?php

declare(strict_types=1);

require_once __DIR__ . '/../../adm/script/conex.php';

/**
 * Repository dedicated to checking and managing user permissions for admin modules.
 */
class PermissionRepository
{
    private MySQLcn $connection;

    public function __construct(MySQLcn $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Determine if the provided user can manage the banners module.
     */
    public function userCanManageBanners(int $userId): bool
    {
        return $this->userHasPermission($userId, 'BANNERS', 'UPDATE');
    }

    /**
     * Determine if the provided user can manage the news module.
     */
    public function userCanManageNews(int $userId): bool
    {
        return $this->userHasPermission($userId, 'NEWS', 'UPDATE');
    }

    /**
     * Retrieve the list of active users with their management capabilities.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getManageableUsers(int $excludeUserId = 0): array
    {
        $sql = 'SELECT usersId, nombres, users, nivel FROM usuarios WHERE estado = 1 ORDER BY nivel, nombres';
        $this->connection->Query($sql);
        $rows = $this->connection->Rows();

        $users = [];
        foreach ($rows as $row) {
            $userId = (int) ($row['usersId'] ?? 0);
            if ($excludeUserId > 0 && $userId === $excludeUserId) {
                continue;
            }

            $row['can_manage_banners'] = $this->userCanManageBanners($userId);
            $row['can_manage_news'] = $this->userCanManageNews($userId);
            $users[] = $row;
        }

        return $users;
    }

    /**
     * Assign or revoke the manage capability for a given resource.
     */
    public function setManageAccess(int $userId, string $resource, bool $allow): bool
    {
        $actions = ['UPDATE', 'DELETE'];
        $link = $this->connection->GetLink();
        $success = true;

        foreach ($actions as $action) {
            $statement = $link->prepare(
                'INSERT INTO user_permisos (usersId, recurso, accion, permitido) VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE permitido = VALUES(permitido)'
            );

            if ($statement === false) {
                $success = false;
                break;
            }

            $permitValue = $allow ? 1 : 0;
            $statement->bind_param('issi', $userId, $resource, $action, $permitValue);

            if (!$statement->execute()) {
                $success = false;
            }

            $statement->close();

            if (!$success) {
                break;
            }
        }

        return $success;
    }

    /**
     * Check if the user has the provided permission according to the effective permissions view.
     */
    public function userHasPermission(int $userId, string $resource, string $action): bool
    {
        $link = $this->connection->GetLink();
        $statement = $link->prepare(
            'SELECT permitido FROM v_permisos_efectivos WHERE usersId = ? AND recurso = ? AND accion = ? LIMIT 1'
        );

        if ($statement === false) {
            return false;
        }

        $statement->bind_param('iss', $userId, $resource, $action);

        if (!$statement->execute()) {
            $statement->close();
            return false;
        }

        $result = $statement->get_result();
        if ($result === false) {
            $statement->close();
            return false;
        }

        $row = $result->fetch_assoc();
        $result->free();
        $statement->close();

        if ($row === null) {
            return false;
        }

        return (int) ($row['permitido'] ?? 0) === 1;
    }
}
