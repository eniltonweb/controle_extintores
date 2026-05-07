<?php
$iterations = 100000;
$row_data = [
    'codigo' => 'EXT-001',
    'Predio' => 'Predio A',
    'Local_Exato' => 'Corredor 1',
    'proxima_manutencao_n2' => '12-12-2024',
    'dias_para_expirar_n2' => '10'
];

// Baseline
$start = microtime(true);
$html1 = '';
for ($i = 0; $i < $iterations; $i++) {
    $row = $row_data;
    $row = array_map('htmlspecialchars', $row);
    $html1 .= '<tr>
                <td>' . $row['codigo'] . '</td>
                <td>' . $row['Predio'] . '</td>
                <td>' . $row['Local_Exato'] . '</td>
                <td>' . $row['proxima_manutencao_n2'] . '</td>
                <td>' . $row['dias_para_expirar_n2'] . '</td>
            </tr>';
}
$time1 = microtime(true) - $start;

// Optimized
$start = microtime(true);
$html2 = '';
for ($i = 0; $i < $iterations; $i++) {
    $row = $row_data;
    $html2 .= '<tr>
                <td>' . htmlspecialchars($row['codigo'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['Predio'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['Local_Exato'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['proxima_manutencao_n2'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['dias_para_expirar_n2'] ?? '') . '</td>
            </tr>';
}
$time2 = microtime(true) - $start;

echo "Baseline: " . number_format($time1, 4) . " seconds\n";
echo "Optimized: " . number_format($time2, 4) . " seconds\n";
echo "Improvement: " . number_format((($time1 - $time2) / $time1 * 100), 2) . "%\n";
