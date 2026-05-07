<?php
session_start();
include '../config/db_conexao.php';

// Verificar se o usuário está logado e se tem permissão para acessar esta página
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] != 'admin') {
    header('Location: index.php');
    exit();
}

// Registrar a exportação no log de auditoria
function registrar_auditoria($conn, $user_id, $action, $details) {
    $sql = "INSERT INTO auditoria_logs (user_id, action, detalhes) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iss', $user_id, $action, $details);
    $stmt->execute();
    $stmt->close();
}

header('Content-Type: text/html; charset=utf-8');
header('Content-Disposition: attachment; filename=historico_inspecao_' . date('Y-m-d_H:i:s') . '.html');

// Construir consulta SQL (Adicionado a coluna bd_extintores.foto)
$sql = "
    SELECT
        bd_extintores.codigo AS extintor_codigo,
        bd_extintores.Local_Exato AS local_exato,
        bd_extintores.Predio AS predio,
        COALESCE(bd_extintores.usuario, 'Usuário removido') AS usuario_nome,
		bd_extintores.tip_extintor AS tipo_extintor,
        bd_extintores.inspecao_trimestral_nivel1 AS data_inspecao,
        bd_extintores.selo_do_Inmetro,
        bd_extintores.sinalizacao_vertical,
        bd_extintores.sinalizacao_piso,
        bd_extintores.ficha_inspecao_trimestral,
        bd_extintores.lacre,
        bd_extintores.pressao_manometro,
        bd_extintores.anel_identificacao,
        bd_extintores.pesagem_co2_semestral,
        bd_extintores.foto AS foto_inspecao
    FROM
        bd_extintores
    LEFT JOIN
        usuarios ON bd_extintores.usuario = usuarios.id
    WHERE
        bd_extintores.inspecao_trimestral_nivel1 IS NOT NULL
        AND bd_extintores.inspecao_trimestral_nivel1 >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
";

$result = $conn->query($sql);

// Iniciar a geração do conteúdo HTML
$html = '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Histórico de inspeções</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .table th {
            background-color: #0056b3;
            color: white;
            vertical-align: middle;
        }
        .table tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .table td {
            vertical-align: middle;
        }
        .header-img {
            width: 300px;
            height: auto;
        }
        h2 {
            color: #0056b3;
        }
        @media print { @page { size: landscape; } .d-print-none { display: none !important; } }
    </style>
</head>
<body>
    <div class="container-fluid mt-5">
        <div class="text-center mb-4">
            <img src="' . ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/') . '/img/michelin_logo.png' . '" alt="Michelin Logo" class="header-img">
            <h2 class="text-center">Relatório Inspeção de Nível 1</h2></div><div class="text-center mb-4 d-print-none"><button onclick="window.print()" class="btn btn-primary">Imprimir / Salvar como PDF</button></div><div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center">
                <thead>
                    <tr>
                        <th>Código do Extintor</th>
                        <th>Local Exato</th>
                        <th>Prédio</th>
                        <th>Usuário</th>
                        <th>Tipo de Extintor</th>
                        <th>Inspeção Trimestral Nivel 1</th>
                        <th>Selo do Inmetro</th>
                        <th>Sinalização Vertical</th>
                        <th>Sinalização Piso</th>
                        <th>Ficha de Inspeçao Trimestral</th>
                        <th>Lacre</th>
                        <th>Pressão do Mamometro</th>
                        <th>Anel de Identificação</th>
                        <th>Pesagem Semestral Co2</th>
                        <th>Foto da Inspeção</th>
                    </tr>
                </thead>
                <tbody>';

while ($row = $result->fetch_assoc()) {
    // Formatando a data para d-m-Y
    $data_inspecao = htmlspecialchars(date_format(date_create($row['data_inspecao']), 'd-m-Y') ?? '');

    // Lógica para exibir a foto
    $foto_html = 'Sem Foto';
    if (!empty($row['foto_inspecao'])) {
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

$html .= '</tbody>
            </table>
        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>';

echo $html;

// Registrar a auditoria
$user_id = $_SESSION['user_id'];
$action = 'Exportação de inspeções';
$details = 'Exportação inspeções realizada em ' . date('Y-m-d H:i:s');
registrar_auditoria($conn, $user_id, $action, $details);

$conn->close();
exit();
?>