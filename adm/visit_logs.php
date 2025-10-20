<?php
session_start();

if (!isset($_SESSION['login'])) {
    header('Location: login.php');
    exit();
}

$nivelUsuario = (int) ($_SESSION['nivel'] ?? 0);
if ($nivelUsuario !== 1) {
    header('Location: user.php');
    exit();
}

$nombreUsuario = $_SESSION['nombre'] ?? 'Usuario';

$fechaFinInput    = isset($_GET['fecha_fin']) ? trim((string) $_GET['fecha_fin']) : '';
$fechaInicioInput = isset($_GET['fecha_inicio']) ? trim((string) $_GET['fecha_inicio']) : '';

$hoy = new DateTimeImmutable('today');
$fechaFin = $hoy;
if ($fechaFinInput !== '') {
    $fechaTemporal = DateTimeImmutable::createFromFormat('Y-m-d', $fechaFinInput);
    if ($fechaTemporal instanceof DateTimeImmutable) {
        $fechaFin = $fechaTemporal;
    }
}

$fechaInicio = $fechaFin->modify('-6 days');
if ($fechaInicioInput !== '') {
    $fechaTemporal = DateTimeImmutable::createFromFormat('Y-m-d', $fechaInicioInput);
    if ($fechaTemporal instanceof DateTimeImmutable) {
        $fechaInicio = $fechaTemporal;
    }
}

if ($fechaInicio > $fechaFin) {
    $aux = $fechaInicio;
    $fechaInicio = $fechaFin;
    $fechaFin = $aux;
}

$inicioFormValue = $fechaInicio->format('Y-m-d');
$finFormValue    = $fechaFin->format('Y-m-d');

$inicioConsulta = $fechaInicio->format('Y-m-d 00:00:00');
$finConsulta    = $fechaFin->format('Y-m-d 23:59:59');

require_once __DIR__ . '/script/conex.php';

$visitas = [];
$resumenVisitas = [];
$errorDb = '';
$conexion = null;

try {
    $conexion = new MySQLcn();
    $link = $conexion->GetLink();

    if (is_object($link) && method_exists($link, 'prepare')) {
        $sqlVisitas = "SELECT v.visitaId, v.usersId, v.nivel, v.dispositivo, v.navegador, v.ip, v.user_agent, v.fecha_visita, u.nombres, u.users
                        FROM registro_visitas v
                        LEFT JOIN usuarios u ON u.usersId = v.usersId
                        WHERE v.fecha_visita BETWEEN ? AND ?
                        ORDER BY v.fecha_visita DESC, v.visitaId DESC";

        $stmtVisitas = $link->prepare($sqlVisitas);
        if ($stmtVisitas) {
            $stmtVisitas->bind_param('ss', $inicioConsulta, $finConsulta);
            if ($stmtVisitas->execute()) {
                $resultado = $stmtVisitas->get_result();
                if ($resultado) {
                    while ($fila = $resultado->fetch_assoc()) {
                        $visitas[] = $fila;
                    }
                }
            }
            $stmtVisitas->close();
        }

        $sqlResumen = "SELECT DATE(v.fecha_visita) AS fecha, COUNT(*) AS total
                        FROM registro_visitas v
                        WHERE v.fecha_visita BETWEEN ? AND ?
                        GROUP BY DATE(v.fecha_visita)
                        ORDER BY fecha ASC";

        $stmtResumen = $link->prepare($sqlResumen);
        if ($stmtResumen) {
            $stmtResumen->bind_param('ss', $inicioConsulta, $finConsulta);
            if ($stmtResumen->execute()) {
                $resultadoResumen = $stmtResumen->get_result();
                if ($resultadoResumen) {
                    while ($fila = $resultadoResumen->fetch_assoc()) {
                        $resumenVisitas[] = $fila;
                    }
                }
            }
            $stmtResumen->close();
        }
    }
} catch (Throwable $exception) {
    $errorDb = 'No fue posible obtener el registro de visitas.';
}

if (is_object($conexion) && method_exists($conexion, 'Close')) {
    $conexion->Close();
}

$levelLabels = [
    1 => 'Superusuario',
    2 => 'Banners',
    3 => 'Noticias',
];

$chartLabels = [];
$chartDataPoints = [];
$diaMasVisitado = '';
$maximoVisitas = 0;

foreach ($resumenVisitas as $fila) {
    $fechaCadena = (string) ($fila['fecha'] ?? '');
    $conteo = (int) ($fila['total'] ?? 0);

    $fechaObjeto = DateTimeImmutable::createFromFormat('Y-m-d', $fechaCadena);
    $etiqueta = $fechaObjeto instanceof DateTimeImmutable ? $fechaObjeto->format('d/m/Y') : $fechaCadena;

    $chartLabels[] = $etiqueta;
    $chartDataPoints[] = $conteo;

    if ($conteo > $maximoVisitas) {
        $maximoVisitas = $conteo;
        $diaMasVisitado = $etiqueta;
    }
}

$totalVisitas = 0;
foreach ($chartDataPoints as $valor) {
    $totalVisitas += (int) $valor;
}

$promedioVisitas = 0;
if (count($chartDataPoints) > 0) {
    $promedioVisitas = $totalVisitas / count($chartDataPoints);
}

$canUploadBanner     = in_array($nivelUsuario, [1, 2], true);
$canPublishNews      = in_array($nivelUsuario, [1, 3], true);
$canManageBanners    = ($nivelUsuario === 1);
$canManageNews       = ($nivelUsuario === 1);
$canGrantPermissions = ($nivelUsuario === 1);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de visitas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard-theme.css">
</head>
<body class="dashboard-body">
<div class="dashboard-shell">
    <?php
    $activeSidebar = 'visit_logs';
    require __DIR__ . '/includes/dashboard_sidebar.php';
    ?>

    <main class="dashboard-main">
        <div class="dashboard-main-inner">
            <?php if ($errorDb !== ''): ?>
                <div class="alert alert-danger alert-dismissible fade show dashboard-alert" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($errorDb, ENT_QUOTES, 'UTF-8'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <div class="row g-4">
            <div class="col-12">
                <div class="card dashboard-card">
                    <div class="card-header">
                        <div>
                            <h4 class="dashboard-section-title mb-1">Registro de visitas</h4>
                            <p class="dashboard-section-subtitle mb-0">Analiza los accesos al panel por dispositivo, navegador y fecha.</p>
                        </div>
                        <button type="button" class="btn btn-outline-primary" id="toggleChart">Ver gráfico</button>
                    </div>
                    <div class="card-body">
                        <form class="row g-3 align-items-end" method="get">
                            <div class="col-md-4">
                                <label for="fecha_inicio" class="form-label">Desde</label>
                                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($inicioFormValue, ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="fecha_fin" class="form-label">Hasta</label>
                                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?php echo htmlspecialchars($finFormValue, ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-md-4 col-lg-3">
                                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                            </div>
                        </form>
                        <div class="row g-3 mt-4">
                            <div class="col-md-4">
                                <div class="dashboard-stat-card">
                                    <p class="dashboard-stat-title">Total de visitas</p>
                                    <p class="dashboard-stat-value mb-1"><?php echo number_format($totalVisitas, 0, ',', '.'); ?></p>
                                    <p class="dashboard-stat-detail mb-0">Rango: <?php echo htmlspecialchars($inicioFormValue, ENT_QUOTES, 'UTF-8'); ?> a <?php echo htmlspecialchars($finFormValue, ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="dashboard-stat-card">
                                    <p class="dashboard-stat-title">Promedio diario</p>
                                    <p class="dashboard-stat-value mb-1"><?php echo number_format($promedioVisitas, 2, ',', '.'); ?></p>
                                    <p class="dashboard-stat-detail mb-0">Visitas por día en el periodo</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="dashboard-stat-card">
                                    <p class="dashboard-stat-title">Día con más visitas</p>
                                    <p class="dashboard-stat-value mb-1"><?php echo $diaMasVisitado !== '' ? htmlspecialchars($diaMasVisitado, ENT_QUOTES, 'UTF-8') : 'Sin datos'; ?></p>
                                    <p class="dashboard-stat-detail mb-0"><?php echo $diaMasVisitado !== '' ? number_format($maximoVisitas, 0, ',', '.') . ' accesos' : 'Esperando nuevos registros'; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card dashboard-card dashboard-chart-card mb-0 d-none" id="chartCard">
                    <div class="card-body">
                        <h5 class="dashboard-section-title mb-3">Visitas por día</h5>
                        <div class="dashboard-chart-container">
                            <canvas id="visitsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card dashboard-card">
                    <div class="card-header">
                        <div>
                            <h5 class="dashboard-section-title mb-1">Detalle de accesos</h5>
                            <p class="dashboard-section-subtitle mb-0">Registros por dispositivo, navegador y dirección IP.</p>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($visitas)): ?>
                            <div class="dashboard-empty-state">
                                <i class="fas fa-chart-line"></i>
                                <p class="mb-0">No se registran visitas en el periodo seleccionado.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive dashboard-table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                    <tr>
                                        <th scope="col">Usuario</th>
                                        <th scope="col">Nivel</th>
                                        <th scope="col">Dispositivo</th>
                                        <th scope="col">Navegador</th>
                                        <th scope="col">IP</th>
                                        <th scope="col">Fecha y hora</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($visitas as $visita): ?>
                                        <?php
                                        $nombre      = $visita['nombres'] ?? 'Usuario eliminado';
                                        $usuario     = $visita['users'] ?? 'Cuenta no disponible';
                                        $nivel       = (int) ($visita['nivel'] ?? 0);
                                        $dispositivo = $visita['dispositivo'] ?? 'Desconocido';
                                        $navegador   = $visita['navegador'] ?? 'Desconocido';
                                        $ip          = $visita['ip'] ?? '';
                                        $userAgent   = $visita['user_agent'] ?? '';
                                        $fechaRaw    = $visita['fecha_visita'] ?? '';

                                        $timestamp = strtotime((string) $fechaRaw);
                                        $fechaFormateada = $timestamp ? date('d/m/Y H:i', $timestamp) : (string) $fechaRaw;
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?></div>
                                                <div class="text-muted small">@<?php echo htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8'); ?></div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?php echo htmlspecialchars($levelLabels[$nivel] ?? ('Nivel ' . $nivel), ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($dispositivo, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($navegador, ENT_QUOTES, 'UTF-8'); ?></div>
                                                <?php if ($userAgent !== ''): ?>
                                                    <div class="text-muted small text-break"><?php echo htmlspecialchars($userAgent, ENT_QUOTES, 'UTF-8'); ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($ip, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($fechaFormateada, ENT_QUOTES, 'UTF-8'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const labels = <?php echo json_encode($chartLabels, JSON_UNESCAPED_UNICODE); ?>;
        const dataPoints = <?php echo json_encode($chartDataPoints, JSON_UNESCAPED_UNICODE); ?>;
        const toggleButton = document.getElementById('toggleChart');
        const chartCard = document.getElementById('chartCard');
        const hasData = Array.isArray(labels) && labels.length > 0 && Array.isArray(dataPoints) && dataPoints.length > 0;
        let chartInstance = null;

        if (!toggleButton) {
            return;
        }

        if (!hasData) {
            toggleButton.classList.add('disabled');
            toggleButton.setAttribute('disabled', 'disabled');
        }

        toggleButton.addEventListener('click', function () {
            if (!hasData) {
                return;
            }

            chartCard.classList.toggle('d-none');
            const mostrar = !chartCard.classList.contains('d-none');
            toggleButton.textContent = mostrar ? 'Ocultar gráfico' : 'Ver gráfico';

            if (mostrar && !chartInstance) {
                const canvas = document.getElementById('visitsChart');
                if (!canvas) {
                    return;
                }

                const contexto = canvas.getContext('2d');
                chartInstance = new Chart(contexto, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Visitas',
                                data: dataPoints,
                                fill: true,
                                backgroundColor: 'rgba(249, 115, 22, 0.18)',
                                borderColor: 'rgba(249, 115, 22, 0.85)',
                                pointBackgroundColor: '#f97316',
                                pointBorderColor: '#facc15',
                                tension: 0.35,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    color: '#f1f5ff',
                                    precision: 0
                                },
                                grid: {
                                    color: 'rgba(255, 255, 255, 0.08)'
                                }
                            },
                            x: {
                                ticks: {
                                    color: '#f1f5ff'
                                },
                                grid: {
                                    color: 'rgba(255, 255, 255, 0.04)'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(17, 21, 28, 0.96)',
                                borderColor: 'rgba(249, 115, 22, 0.35)',
                                borderWidth: 1
                            }
                        }
                    }
                });
            }
        });
    });
</script>
</body>
</html>
