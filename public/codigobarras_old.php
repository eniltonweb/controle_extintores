<?php
session_start();
include '../config/db_conexao.php';

if (!isset($_GET['codigo'])) {
    die('Código de barras não fornecido.');
}

$codigo = $_GET['codigo'];
$user_level = isset($_SESSION['user_level']) ? $_SESSION['user_level'] : null;
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Consulta para obter as informações do extintor e o nome do usuário que fez a última inspeção de nível 1
$sql = "
    SELECT e.*,
           u.username AS usuario_inspecao_nivel1,
           um.username AS usuario_manutencao_nivel2
    FROM bd_extintores e
    LEFT JOIN historico_manutencao hm1 ON e.id = hm1.extintor_id AND hm1.tipo_manutencao = 'nivel_1'
    LEFT JOIN usuarios u ON hm1.usuario_id = u.id
    LEFT JOIN historico_manutencao hm2 ON e.id = hm2.extintor_id AND hm2.tipo_manutencao = 'nivel_2'
    LEFT JOIN usuarios um ON hm2.usuario_id = um.id
    WHERE e.codigo = ?
    ORDER BY hm1.data_manutencao DESC, hm2.data_manutencao DESC
    LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $codigo);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    if ($result->num_rows > 0) {
        $extintor = $result->fetch_assoc();
        $is_logged_in = isset($_SESSION['user_id']);

        // Verificar se o bombeiro tem permissão para inspeção de nível 1
        $liberado_inspecao = false;
        if ($user_level == 'bombeiro') {
            $sql_inspecao = "SELECT * FROM liberacao_inspecao WHERE codigo_extintor = ? AND liberado_para = 'bombeiro'";
            $stmt_inspecao = $conn->prepare($sql_inspecao);
            $stmt_inspecao->bind_param("s", $codigo);
            $stmt_inspecao->execute();
            $result_inspecao = $stmt_inspecao->get_result();
            if ($result_inspecao && $result_inspecao->num_rows > 0) {
                $liberado_inspecao = true;
            }
        }

        // Verificar se o fornecedor tem permissão para manutenção de nível 2
        $liberado_manutencao = false;
        if ($user_level == 'fornecedor') {
            $sql_manutencao = "SELECT * FROM liberacao_manutencao WHERE codigo_extintor = ? AND liberado_para = 'fornecedor'";
            $stmt_manutencao = $conn->prepare($sql_manutencao);
            $stmt_manutencao->bind_param("s", $codigo);
            $stmt_manutencao->execute();
            $result_manutencao = $stmt_manutencao->get_result();
            if ($result_manutencao && $result_manutencao->num_rows > 0) {
                $liberado_manutencao = true;
            }
        }
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Detalhes do Extintor</title>
            <link rel="stylesheet" type="text/css" href="styles.css">
        </head>
        <body>
            <header>
                <h1>Detalhes do Extintor</h1>
            </header>
            <div class="container">
                <p><strong>Código:</strong> <?php echo htmlspecialchars($extintor['codigo']); ?></p>
                <p><strong>Prédio:</strong> <?php echo htmlspecialchars($extintor['Predio']); ?></p>
                <p><strong>Atividade:</strong> <?php echo htmlspecialchars($extintor['Atividade']); ?></p>
                <p><strong>Local Exato:</strong> <?php echo htmlspecialchars($extintor['Local_Exato']); ?></p>
                <p><strong>Tipo de Extintor:</strong> <?php echo htmlspecialchars($extintor['tip_extintor']); ?></p>
                <p><strong>Carga:</strong> <?php echo htmlspecialchars($extintor['carga']); ?></p>
                <p><strong>Última Manutenção Nível 1:</strong> <?php echo !empty($extintor['inspecao_trimestral_nivel1']) ? htmlspecialchars($extintor['inspecao_trimestral_nivel1']) : 'Não disponível'; ?></p>
                <p><strong>Inspecionado por:</strong> <?php echo !empty($extintor['usuario_inspecao_nivel1']) ? htmlspecialchars($extintor['usuario_inspecao_nivel1']) : 'Não disponível'; ?></p>
                <?php if ($is_logged_in) : ?>
				<p><strong>Última Manutenção Nível 2:</strong> <?php echo !empty($extintor['manutencao_n2']) ? htmlspecialchars($extintor['manutencao_n2']) : 'Não disponível'; ?></p>
                <p><strong>Próxima Manutenção Nível 2:</strong> <?php echo ($extintor['proxima_manutencao_n2'] != '0000-00-00') ? htmlspecialchars($extintor['proxima_manutencao_n2']) : 'Não disponível'; ?></p>
                <p><strong>Usuário Última Manutenção Nível 2:</strong> <?php echo !empty($extintor['usuario_manutencao_nivel2']) ? htmlspecialchars($extintor['usuario_manutencao_nivel2']) : 'Não disponível'; ?></p>
                    <p><strong>Comentários:</strong> <?php echo !empty($extintor['comentarios']) ? htmlspecialchars($extintor['comentarios']) : 'Nenhum'; ?></p>
                    <p><strong>Foto:</strong>
                        <?php
                        if (!empty($extintor['foto'])) {
                            echo '<img src="../uploads/' . htmlspecialchars($extintor['foto']) . '" alt="Foto do Extintor">';
                        } else {
                            echo 'Nenhuma foto disponível';
                        }
                        ?>
                    </p>

                    <?php if ($user_level == 'bombeiro' && $liberado_inspecao) : ?>
                        <p><a href="inspecao_nivel1.php?codigo=<?php echo $codigo; ?>">Realizar Inspeção de Nível 1</a></p>
                    <?php endif; ?>

                    <?php if ($user_level == 'fornecedor' && $liberado_manutencao) : ?>
                        <p><a href="manutencao_nivel2.php?codigo=<?php echo $codigo; ?>">Realizar Manutenção de Nível 2</a></p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <footer class="footer">
                <p>&copy; 2024 Enilton Extintores</p>
            </footer>
        </body>
        </html>
        <?php
    } else {
        echo "Nenhum extintor encontrado com o código fornecido.";
    }
} else {
    echo "Erro ao executar a consulta: " . $conn->error;
}

$conn->close();
?>
