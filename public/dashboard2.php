<?php
session_start();
include '../config/db_conexao.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$cache_file = '../cache/dashboard2_data.json';
$cache_ttl = 300; // 5 minutos

if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_ttl)) {
    $cache_data = json_decode(file_get_contents($cache_file), true);
    $manutencoes = $cache_data['manutencoes'];
    $proximas_manutencoes = $cache_data['proximas_manutencoes'];
} else {
    // Consultar dados de manutenções realizadas
    $sql_manutencao = "SELECT tipo_manutencao, COUNT(*) AS total FROM historico_manutencao GROUP BY tipo_manutencao";
    $result_manutencao = $conn->query($sql_manutencao);

    $manutencoes = [];
    while ($row = $result_manutencao->fetch_assoc()) {
        $manutencoes[] = $row;
    }

    // Consultar dados de próximas manutenções
    $sql_proximas = "SELECT proxima_manutencao_n2, COUNT(*) AS total FROM bd_extintores WHERE proxima_manutencao_n2 IS NOT NULL GROUP BY proxima_manutencao_n2";
    $result_proximas = $conn->query($sql_proximas);

    $proximas_manutencoes = [];
    while ($row = $result_proximas->fetch_assoc()) {
        $proximas_manutencoes[] = $row;
    }

    $cache_data = [
        'manutencoes' => $manutencoes,
        'proximas_manutencoes' => $proximas_manutencoes
    ];
    // Ensure cache directory exists
    if (!is_dir('../cache')) {
        mkdir('../cache', 0777, true);
    }
    file_put_contents($cache_file, json_encode($cache_data), LOCK_EX);
}

$conn->close();
include '../templates/header.php';
?>

<h2>Dashboard</h2>
<div class="container">
    <div class="chart-container">
        <h3>Manutenções Realizadas</h3>
        <canvas id="manutencaoChart"></canvas>
    </div>
    <div class="chart-container">
        <h3>Próximas Manutenções</h3>
        <canvas id="proximasChart"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Dados de manutenções realizadas
    var manutencaoLabels = <?php echo json_encode(array_column($manutencoes, 'tipo_manutencao')); ?>;
    var manutencaoData = <?php echo json_encode(array_column($manutencoes, 'total')); ?>;

    var ctxManutencao = document.getElementById('manutencaoChart').getContext('2d');
    var manutencaoChart = new Chart(ctxManutencao, {
        type: 'bar',
        data: {
            labels: manutencaoLabels,
            datasets: [{
                label: 'Manutenções Realizadas',
                data: manutencaoData,
                backgroundColor: ['rgba(75, 192, 192, 0.2)', 'rgba(255, 159, 64, 0.2)'],
                borderColor: ['rgba(75, 192, 192, 1)', 'rgba(255, 159, 64, 1)'],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Dados de próximas manutenções
    var proximasLabels = <?php echo json_encode(array_column($proximas_manutencoes, 'proxima_manutencao_n2')); ?>;
    var proximasData = <?php echo json_encode(array_column($proximas_manutencoes, 'total')); ?>;

    var ctxProximas = document.getElementById('proximasChart').getContext('2d');
    var proximasChart = new Chart(ctxProximas, {
        type: 'line',
        data: {
            labels: proximasLabels,
            datasets: [{
                label: 'Próximas Manutenções',
                data: proximasData,
                backgroundColor: 'rgba(153, 102, 255, 0.2)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1,
                fill: true
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

<?php include '../templates/footer.php'; ?>
