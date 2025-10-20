<?php
declare(strict_types=1);

class VisitLogAnalytics
{
    public static function buildChartData(array $dailySummary): array
    {
        $labels      = [];
        $dataPoints  = [];
        $peakDay     = '';
        $peakVisits  = 0;

        foreach ($dailySummary as $row) {
            $fechaCadena = (string)($row['fecha'] ?? '');
            $conteo      = (int)($row['total'] ?? 0);

            $fechaObjeto = DateTimeImmutable::createFromFormat('Y-m-d', $fechaCadena);
            $etiqueta    = $fechaObjeto instanceof DateTimeImmutable
                ? $fechaObjeto->format('d/m/Y')
                : $fechaCadena;

            $labels[]     = $etiqueta;
            $dataPoints[] = $conteo;

            if ($conteo > $peakVisits) {
                $peakVisits = $conteo;
                $peakDay    = $etiqueta;
            }
        }

        return [
            'labels'     => $labels,
            'data'       => $dataPoints,
            'peak_day'   => $peakDay,
            'peak_value' => $peakVisits,
        ];
    }

    public static function summarize(array $dataPoints): array
    {
        $total = 0;
        foreach ($dataPoints as $value) {
            $total += (int)$value;
        }

        $average = 0.0;
        $count   = count($dataPoints);
        if ($count > 0) {
            $average = $total / $count;
        }

        return [
            'total'   => $total,
            'average' => $average,
        ];
    }
}
