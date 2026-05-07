<?php
function benchmark_original($num_rows) {
    $data = [];
    for ($i = 0; $i < $num_rows; $i++) {
        $data[] = [
            'codigo' => '100-' . $i,
            'Predio' => 'Predio ' . ($i % 10),
            'Local_Exato' => 'Local ' . $i,
            'usuario_nome' => 'Usuario ' . $i,
            'tip_extintor' => 'AP',
            'carga' => '10L',
            'manutencao_n2' => '2024-01-01',
            'proxima_manutencao_n2' => '2025-01-01',
            'dias_para_expirar_n2' => 365,
            'cobertura' => '1'
        ];
    }

    $start = microtime(true);
    $html = '';
    foreach ($data as $row) {
        $row = array_map('htmlspecialchars', $row);
        $html .= '<tr>
                    <td>' . $row['codigo'] . '</td>
                    <td>' . $row['Predio'] . '</td>
                    <td>' . $row['Local_Exato'] . '</td>
                    <td>' . $row['usuario_nome'] . '</td>
                    <td>' . $row['tip_extintor'] . '</td>
                    <td>' . $row['carga'] . '</td>
                    <td>' . $row['manutencao_n2'] . '</td>
                    <td>' . $row['proxima_manutencao_n2'] . '</td>
                    <td>' . $row['dias_para_expirar_n2'] . '</td>
                    <td>' . $row['cobertura'] . '</td>
                </tr>';
    }
    $end = microtime(true);
    return $end - $start;
}

function benchmark_optimized($num_rows) {
    $data = [];
    for ($i = 0; $i < $num_rows; $i++) {
        $data[] = [
            'codigo' => '100-' . $i,
            'Predio' => 'Predio ' . ($i % 10),
            'Local_Exato' => 'Local ' . $i,
            'usuario_nome' => 'Usuario ' . $i,
            'tip_extintor' => 'AP',
            'carga' => '10L',
            'manutencao_n2' => '2024-01-01',
            'proxima_manutencao_n2' => '2025-01-01',
            'dias_para_expirar_n2' => 365,
            'cobertura' => '1'
        ];
    }

    $start = microtime(true);
    $html = '';
    foreach ($data as $row) {
        $html .= '<tr>
                    <td>' . htmlspecialchars($row['codigo'] ?? '') . '</td>
                    <td>' . htmlspecialchars($row['Predio'] ?? '') . '</td>
                    <td>' . htmlspecialchars($row['Local_Exato'] ?? '') . '</td>
                    <td>' . htmlspecialchars($row['usuario_nome'] ?? '') . '</td>
                    <td>' . htmlspecialchars($row['tip_extintor'] ?? '') . '</td>
                    <td>' . htmlspecialchars($row['carga'] ?? '') . '</td>
                    <td>' . htmlspecialchars($row['manutencao_n2'] ?? '') . '</td>
                    <td>' . htmlspecialchars($row['proxima_manutencao_n2'] ?? '') . '</td>
                    <td>' . htmlspecialchars($row['dias_para_expirar_n2'] ?? '') . '</td>
                    <td>' . htmlspecialchars($row['cobertura'] ?? '') . '</td>
                </tr>';
    }
    $end = microtime(true);
    return $end - $start;
}

$num_rows = 100000;
$time_original = benchmark_original($num_rows);
$time_optimized = benchmark_optimized($num_rows);

echo "Number of rows: $num_rows\n";
echo "Original time:  " . number_format($time_original, 6) . " seconds\n";
echo "Optimized time: " . number_format($time_optimized, 6) . " seconds\n";
echo "Improvement:    " . number_format(($time_original - $time_optimized) / $time_original * 100, 2) . "%\n";
