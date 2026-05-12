<?php

$iterations = 50000;

// Mock row
$row = [
    'extintor_codigo' => 'EXT-001',
    'local_exato' => 'Hall de entrada',
    'predio' => 'Prédio Principal',
    'usuario_nome' => 'João da Silva',
    'tipo_extintor' => 'AP',
    'data_inspecao' => '2023-10-01',
    'selo_do_Inmetro' => 'OK',
    'sinalizacao_vertical' => 'OK',
    'sinalizacao_piso' => 'NOK',
    'ficha_inspecao_trimestral' => 'OK',
    'lacre' => 'OK',
    'pressao_manometro' => 'OK',
    'anel_identificacao' => 'OK',
    'pesagem_co2_semestral' => 'N/A'
];

// Array of rows
$rows = array_fill(0, $iterations, $row);

// Baseline: array_map
$start1 = microtime(true);
$html1 = '';
foreach ($rows as $r) {
    $r = array_map('htmlspecialchars', $r);
    $html1 .= '<tr>
                <td>' . $r['extintor_codigo'] . '</td>
                <td>' . $r['local_exato'] . '</td>
                <td>' . $r['predio'] . '</td>
                <td>' . $r['usuario_nome'] . '</td>
				<td>' . $r['tipo_extintor'] . '</td>
			    <td>' . $r['data_inspecao'] . '</td>
                <td>' . $r['selo_do_Inmetro'] . '</td>
                <td>' . $r['sinalizacao_vertical'] . '</td>
                <td>' . $r['sinalizacao_piso'] . '</td>
                <td>' . $r['ficha_inspecao_trimestral'] . '</td>
                <td>' . $r['lacre'] . '</td>
                <td>' . $r['pressao_manometro'] . '</td>
				<td>' . $r['anel_identificacao'] . '</td>
				<td>' . $r['pesagem_co2_semestral'] . '</td>
            </tr>';
}
$end1 = microtime(true);
$time1 = $end1 - $start1;

// Optimized: inline htmlspecialchars
$start2 = microtime(true);
$html2 = '';
foreach ($rows as $r) {
    $html2 .= '<tr>
                <td>' . htmlspecialchars($r['extintor_codigo'] ?? '') . '</td>
                <td>' . htmlspecialchars($r['local_exato'] ?? '') . '</td>
                <td>' . htmlspecialchars($r['predio'] ?? '') . '</td>
                <td>' . htmlspecialchars($r['usuario_nome'] ?? '') . '</td>
				<td>' . htmlspecialchars($r['tipo_extintor'] ?? '') . '</td>
			    <td>' . htmlspecialchars($r['data_inspecao'] ?? '') . '</td>
                <td>' . htmlspecialchars($r['selo_do_Inmetro'] ?? '') . '</td>
                <td>' . htmlspecialchars($r['sinalizacao_vertical'] ?? '') . '</td>
                <td>' . htmlspecialchars($r['sinalizacao_piso'] ?? '') . '</td>
                <td>' . htmlspecialchars($r['ficha_inspecao_trimestral'] ?? '') . '</td>
                <td>' . htmlspecialchars($r['lacre'] ?? '') . '</td>
                <td>' . htmlspecialchars($r['pressao_manometro'] ?? '') . '</td>
				<td>' . htmlspecialchars($r['anel_identificacao'] ?? '') . '</td>
				<td>' . htmlspecialchars($r['pesagem_co2_semestral'] ?? '') . '</td>
            </tr>';
}
$end2 = microtime(true);
$time2 = $end2 - $start2;

echo "Baseline (array_map): " . number_format($time1, 4) . "s\n";
echo "Optimized (inline htmlspecialchars): " . number_format($time2, 4) . "s\n";
$improvement = ($time1 - $time2) / $time1 * 100;
echo "Improvement: " . number_format($improvement, 2) . "%\n";
