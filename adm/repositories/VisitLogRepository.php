<?php
declare(strict_types=1);

class VisitLogRepository
{
    public function __construct(private MySQLcn $connection)
    {
    }

    public function fetchVisits(DateTimeImmutable $inicio, DateTimeImmutable $fin): array
    {
        $link = $this->connection->GetLink();
        if (!is_object($link) || !method_exists($link, 'prepare')) {
            return [];
        }

        $sql = "SELECT v.visitaId, v.usersId, v.nivel, v.dispositivo, v.navegador, v.ip, v.user_agent, v.fecha_visita, u.nombres, u.users " .
            "FROM registro_visitas v " .
            "LEFT JOIN usuarios u ON u.usersId = v.usersId " .
            "WHERE v.fecha_visita BETWEEN ? AND ? " .
            "ORDER BY v.fecha_visita DESC, v.visitaId DESC";

        $inicioCadena = $inicio->format('Y-m-d H:i:s');
        $finCadena    = $fin->format('Y-m-d H:i:s');

        $stmt = @$link->prepare($sql);
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('ss', $inicioCadena, $finCadena);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $visitas = [];
        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $visitas[] = $fila;
            }
        }

        $stmt->close();

        return $visitas;
    }

    public function fetchDailySummary(DateTimeImmutable $inicio, DateTimeImmutable $fin): array
    {
        $link = $this->connection->GetLink();
        if (!is_object($link) || !method_exists($link, 'prepare')) {
            return [];
        }

        $sql = "SELECT DATE(v.fecha_visita) AS fecha, COUNT(*) AS total " .
            "FROM registro_visitas v " .
            "WHERE v.fecha_visita BETWEEN ? AND ? " .
            "GROUP BY DATE(v.fecha_visita) " .
            "ORDER BY fecha ASC";

        $inicioCadena = $inicio->format('Y-m-d H:i:s');
        $finCadena    = $fin->format('Y-m-d H:i:s');

        $stmt = @$link->prepare($sql);
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('ss', $inicioCadena, $finCadena);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $resumen = [];
        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $resumen[] = $fila;
            }
        }

        $stmt->close();

        return $resumen;
    }
}
