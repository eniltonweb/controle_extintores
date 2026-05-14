<?php
$rows = [];
for ($i = 0; $i < 10000; $i++) {
    $rows[] = [
        'Predio' => 'P'.$i, 'codigo' => 'C'.$i, 'Atividade' => 'A'.$i, 'Local_Exato' => 'L'.$i,
        'tip_extintor' => 'T'.$i, 'carga' => 'C'.$i, 'manutencao_n2' => 'M'.$i,
        'proxima_manutencao_n2' => 'P'.$i, 'dias_para_expirar_n2' => 'D'.$i,
        'inspecao_trimestral_nivel1' => 'I'.$i, 'selo_do_Inmetro' => 'S'.$i,
        'sinalizacao_vertical' => 'S'.$i, 'sinalizacao_piso' => 'S'.$i,
        'ficha_inspecao_trimestral' => 'F'.$i, 'lacre' => 'L'.$i,
        'pressao_manometro' => 'P'.$i, 'anel_identificacao' => 'A'.$i,
        'pesagem_co2_semestral' => 'P'.$i, 'usuario' => 'U'.$i, 'comentarios' => 'C'.$i,
    ];
}
$start = microtime(true);
ob_start();
foreach ($rows as $row) {
    echo '<tr>';
    foreach ($row as $value) {
        echo '<td>' . htmlspecialchars($value ?? '') . '</td>';
    }
    echo '</tr>';
}
ob_end_clean();
$time_foreach = microtime(true) - $start;

$start = microtime(true);
ob_start();
foreach ($rows as $row) {
    echo '<tr>',
        '<td>', htmlspecialchars($row['Predio'] ?? ''), '</td>',
        '<td>', htmlspecialchars($row['codigo'] ?? ''), '</td>',
        '<td>', htmlspecialchars($row['Atividade'] ?? ''), '</td>',
        '<td>', htmlspecialchars($row['Local_Exato'] ?? ''), '</td>',
        '<td>', htmlspecialchars($row['tip_extintor'] ?? ''), '</td>',
        '<td>', htmlspecialchars($row['carga'] ?? ''), '</td>',
        '<td>', htmlspecialchars($row['manutencao_n2'] ?? ''), '</td>',
        '<td>', htmlspecialchars($row['proxima_manutencao_n2'] ?? ''), '</td>',
        '<td>', htmlspecialchars($row['dias_para_expirar_n2'] ?? ''), '</td>',
        '<td>', htmlspecialchars($row['inspecao_trimestral_nivel1'] ?? ''), '</td>',
        '<td>', htmlspecialchars($row['selo_do_Inmetro'] ?? ''), '</td>',
        '<td>', htmlspecialchars($row['sinalizacao_vertical'] ?? ''), '</td>',
        '<td>', htmlspecialchars($row['sinalizacao_piso'] ?? ''), '</td>',
        '<td>', htmlspecialchars($row['ficha_inspecao_trimestral'] ?? ''), '</td>',
        '<td>', htmlspecialchars($row['lacre'] ?? ''), '</td>',
        '<td>', htmlspecialchars($row['pressao_manometro'] ?? ''), '</td>',
        '<td>', htmlspecialchars($row['anel_identificacao'] ?? ''), '</td>',
        '<td>', htmlspecialchars($row['pesagem_co2_semestral'] ?? ''), '</td>',
        '<td>', htmlspecialchars($row['usuario'] ?? ''), '</td>',
        '<td>', htmlspecialchars($row['comentarios'] ?? ''), '</td>',
        '</tr>';
}
ob_end_clean();
$time_explicit = microtime(true) - $start;
echo "Time foreach: " . $time_foreach . "\n";
echo "Time explicit: " . $time_explicit . "\n";
