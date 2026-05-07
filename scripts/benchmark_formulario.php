<?php
include __DIR__ . '/../config/db_conexao.php';

// Benchmark existing approach
function benchmark_existing($conn, $predios) {
    $start = microtime(true);
    foreach ($predios as $predio) {
        $sql = "SELECT * FROM bd_extintores WHERE Predio = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $predio);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();
    }
    return microtime(true) - $start;
}

function benchmark_optimized($conn, $predios) {
    $start = microtime(true);
    // Fetch all needed in one go. If we need to preserve the exact logic (all extintores of the predios that are in liberacao_manutencao)
    // Actually, just fetch all extintores
    $sql = "SELECT codigo, Predio, Atividade, Local_Exato FROM bd_extintores";
    $result = $conn->query($sql);
    $all_data = [];
    while ($row = $result->fetch_assoc()) {
        $all_data[$row['Predio']][] = $row;
    }

    // Simulate user selecting each predio using the loaded data
    foreach ($predios as $predio) {
        $data = $all_data[$predio] ?? [];
    }
    return microtime(true) - $start;
}

// Get predios
$sql_liberados = "SELECT DISTINCT be.Predio FROM liberacao_manutencao lm JOIN bd_extintores be ON lm.codigo_extintor = be.codigo WHERE lm.liberado_para = 'fornecedor'";
$res = $conn->query($sql_liberados);
$predios = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $predios[] = $row['Predio'];
    }
}
// Add some dummy predios if empty
if (empty($predios)) {
    for($i=0; $i<50; $i++) $predios[] = "Predio " . $i;
}

$time_existing = benchmark_existing($conn, $predios);
$time_optimized = benchmark_optimized($conn, $predios);

echo "Simulated selecting " . count($predios) . " predios sequentially.\n";
echo "Existing (N queries): " . number_format($time_existing, 4) . "s\n";
echo "Optimized (1 query + JS array access): " . number_format($time_optimized, 4) . "s\n";
