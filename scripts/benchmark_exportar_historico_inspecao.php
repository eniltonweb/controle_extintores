<?php
function benchmark_original($num_rows) {
    $data = [];
    for ($i = 0; $i < $num_rows; $i++) {
        $data[] = [
            'extintor_codigo' => '100-' . $i,
            'local_exato' => 'Local ' . $i,
            'predio' => 'Predio ' . ($i % 10),
            'usuario_nome' => 'Admin',
            'tipo_extintor' => 'AP',
            'data_inspecao' => '2023-10-15',
            'selo_do_Inmetro' => 'Ok',
            'sinalizacao_vertical' => 'Ok',
            'sinalizacao_piso' => 'Ok',
            'ficha_inspecao_trimestral' => 'Ok',
            'lacre' => 'Ok',
            'pressao_manometro' => 'Ok',
            'anel_identificacao' => 'Ok',
            'pesagem_co2_semestral' => 'Ok',
            'foto_inspecao' => 'foto.jpg'
        ];
    }

    $start = microtime(true);
    $html = '';
    foreach ($data as $row) {
        $row = array_map('htmlspecialchars', $row);

        $row['data_inspecao'] = date_format(date_create($row['data_inspecao']), 'd-m-Y');

        $foto_html = 'Sem Foto';
        if (!empty($row['foto_inspecao'])) {
            $foto_url = 'http://www.enilton.com.br/uploads/' . $row['foto_inspecao'];
            $foto_html = '<a href="' . $foto_url . '" target="_blank">
                            <img src="' . $foto_url . '" style="max-width: 80px; max-height: 80px; border-radius: 4px; border: 1px solid #ccc;">
                          </a>';
        }

        $html .= '<tr>
                    <td>' . $row['extintor_codigo'] . '</td>
                    <td>' . $row['local_exato'] . '</td>
                    <td>' . $row['predio'] . '</td>
                    <td>' . $row['usuario_nome'] . '</td>
                    <td>' . $row['tipo_extintor'] . '</td>
                    <td>' . $row['data_inspecao'] . '</td>
                    <td>' . $row['selo_do_Inmetro'] . '</td>
                    <td>' . $row['sinalizacao_vertical'] . '</td>
                    <td>' . $row['sinalizacao_piso'] . '</td>
                    <td>' . $row['ficha_inspecao_trimestral'] . '</td>
                    <td>' . $row['lacre'] . '</td>
                    <td>' . $row['pressao_manometro'] . '</td>
                    <td>' . $row['anel_identificacao'] . '</td>
                    <td>' . $row['pesagem_co2_semestral'] . '</td>
                    <td>' . $foto_html . '</td>
                </tr>';
    }
    $end = microtime(true);
    return $end - $start;
}

function benchmark_optimized($num_rows) {
    $data = [];
    for ($i = 0; $i < $num_rows; $i++) {
        $data[] = [
            'extintor_codigo' => '100-' . $i,
            'local_exato' => 'Local ' . $i,
            'predio' => 'Predio ' . ($i % 10),
            'usuario_nome' => 'Admin',
            'tipo_extintor' => 'AP',
            'data_inspecao' => '2023-10-15',
            'selo_do_Inmetro' => 'Ok',
            'sinalizacao_vertical' => 'Ok',
            'sinalizacao_piso' => 'Ok',
            'ficha_inspecao_trimestral' => 'Ok',
            'lacre' => 'Ok',
            'pressao_manometro' => 'Ok',
            'anel_identificacao' => 'Ok',
            'pesagem_co2_semestral' => 'Ok',
            'foto_inspecao' => 'foto.jpg'
        ];
    }

    $start = microtime(true);
    $html = '';
    foreach ($data as $row) {
        $data_inspecao = htmlspecialchars(date_format(date_create($row['data_inspecao']), 'd-m-Y') ?? '');

        $foto_html = 'Sem Foto';
        if (!empty($row['foto_inspecao'])) {
            // Note: In original code, the URL building used htmlspecialchars implicitly on foto_inspecao
            // since it was in array_map, but we can just do it here or apply it in HTML.
            $foto_url = 'http://www.enilton.com.br/uploads/' . htmlspecialchars($row['foto_inspecao'] ?? '');
            $foto_html = '<a href="' . $foto_url . '" target="_blank">
                            <img src="' . $foto_url . '" style="max-width: 80px; max-height: 80px; border-radius: 4px; border: 1px solid #ccc;">
                          </a>';
        }

        $html .= '<tr>
                    <td>' . htmlspecialchars($row['extintor_codigo'] ?? '') . '</td>
                    <td>' . htmlspecialchars($row['local_exato'] ?? '') . '</td>
                    <td>' . htmlspecialchars($row['predio'] ?? '') . '</td>
                    <td>' . htmlspecialchars($row['usuario_nome'] ?? '') . '</td>
                    <td>' . htmlspecialchars($row['tipo_extintor'] ?? '') . '</td>
                    <td>' . $data_inspecao . '</td>
                    <td>' . htmlspecialchars($row['selo_do_Inmetro'] ?? '') . '</td>
                    <td>' . htmlspecialchars($row['sinalizacao_vertical'] ?? '') . '</td>
                    <td>' . htmlspecialchars($row['sinalizacao_piso'] ?? '') . '</td>
                    <td>' . htmlspecialchars($row['ficha_inspecao_trimestral'] ?? '') . '</td>
                    <td>' . htmlspecialchars($row['lacre'] ?? '') . '</td>
                    <td>' . htmlspecialchars($row['pressao_manometro'] ?? '') . '</td>
                    <td>' . htmlspecialchars($row['anel_identificacao'] ?? '') . '</td>
                    <td>' . htmlspecialchars($row['pesagem_co2_semestral'] ?? '') . '</td>
                    <td>' . $foto_html . '</td>
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
?>
