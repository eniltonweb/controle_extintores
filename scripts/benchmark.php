<?php
// Benchmark for public/exportar_vencidos.php

function benchmark_original($num_rows) {
    // Simulate data from database
    $data = [];
    for ($i = 0; $i < $num_rows; $i++) {
        $data[] = [
            'codigo' => '100-' . $i,
            'Predio' => 'Predio ' . ($i % 10),
            'Local_Exato' => 'Local ' . $i,
            'proxima_manutencao_n2' => '2025-01-30',
            'dias_para_expirar_n2' => -459,
            'other_null_col' => null
        ];
    }

    $start = microtime(true);
    $html = '';
    foreach ($data as $row) {
        // Original logic
        foreach ($row as $key => $value) {
            if (is_null($value)) {
                $row[$key] = '';
            }
        }
        $row['proxima_manutencao_n2'] = date_format(date_create($row['proxima_manutencao_n2']), 'd-m-Y');
        $row = array_map('htmlspecialchars', $row);

        $html .= '<tr>
                    <td>' . $row['codigo'] . '</td>
                    <td>' . $row['Predio'] . '</td>
                    <td>' . $row['Local_Exato'] . '</td>
                    <td>' . $row['proxima_manutencao_n2'] . '</td>
                    <td>' . $row['dias_para_expirar_n2'] . '</td>
                </tr>';
    }
    $end = microtime(true);
    return $end - $start;
}

function benchmark_optimized($num_rows) {
    // Simulate data from database ALREADY FORMATTED BY SQL
    $data = [];
    for ($i = 0; $i < $num_rows; $i++) {
        $data[] = [
            'codigo' => '100-' . $i,
            'Predio' => 'Predio ' . ($i % 10),
            'Local_Exato' => 'Local ' . $i,
            'proxima_manutencao_n2' => '30-01-2025', // Formatted by SQL
            'dias_para_expirar_n2' => -459,
            'other_null_col' => '' // COALESCE by SQL
        ];
    }

    $start = microtime(true);
    $html = '';
    foreach ($data as $row) {
        // Optimized logic: skip the null check loop and date parsing
        $row = array_map('htmlspecialchars', $row);

        $html .= '<tr>
                    <td>' . $row['codigo'] . '</td>
                    <td>' . $row['Predio'] . '</td>
                    <td>' . $row['Local_Exato'] . '</td>
                    <td>' . $row['proxima_manutencao_n2'] . '</td>
                    <td>' . $row['dias_para_expirar_n2'] . '</td>
                </tr>';
    }
    $end = microtime(true);
    return $end - $start;
}

$num_rows = 10000;
$time_original = benchmark_original($num_rows);
$time_optimized = benchmark_optimized($num_rows);

echo "Number of rows: $num_rows\n";
echo "Original time:  " . number_format($time_original, 6) . " seconds\n";
echo "Optimized time: " . number_format($time_optimized, 6) . " seconds\n";
echo "Improvement:    " . number_format(($time_original - $time_optimized) / $time_original * 100, 2) . "%\n";
