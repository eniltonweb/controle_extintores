<?php
// Standalone benchmark using SQLite for simulation
$db = new SQLite3('db.sqlite');
$db->exec("CREATE TABLE IF NOT EXISTS bd_extintores (id INTEGER PRIMARY KEY, codigo TEXT, Predio TEXT, Atividade TEXT, Local_Exato TEXT);");

// Insert 5000 rows
$db->exec("BEGIN TRANSACTION;");
for ($i=0; $i<5000; $i++) {
    $predio = "Predio " . ($i % 50);
    $db->exec("INSERT INTO bd_extintores (codigo, Predio, Atividade, Local_Exato) VALUES ('EXT-$i', '$predio', 'Ativ', 'Local');");
}
$db->exec("COMMIT;");

$predios = [];
for($i=0; $i<50; $i++) $predios[] = "Predio " . $i;

function benchmark_existing($db, $predios) {
    $start = microtime(true);
    for ($iter=0; $iter<10; $iter++) { // Simulate 10 page loads/changes
        foreach ($predios as $predio) {
            $stmt = $db->prepare("SELECT * FROM bd_extintores WHERE Predio = ?");
            $stmt->bindValue(1, $predio);
            $result = $stmt->execute();
            $data = [];
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $data[] = $row;
            }
        }
    }
    return microtime(true) - $start;
}

function benchmark_optimized($db, $predios) {
    $start = microtime(true);
    for ($iter=0; $iter<10; $iter++) { // Simulate 10 page loads/changes
        // Single query
        $result = $db->query("SELECT codigo, Predio, Atividade, Local_Exato FROM bd_extintores");
        $all_data = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $all_data[$row['Predio']][] = $row;
        }

        foreach ($predios as $predio) {
            $data = $all_data[$predio] ?? [];
        }
    }
    return microtime(true) - $start;
}

$time_existing = benchmark_existing($db, $predios);
$time_optimized = benchmark_optimized($db, $predios);

echo "Simulated 10 iterations of selecting " . count($predios) . " predios sequentially from 5000 records.\n";
echo "Existing (N queries): " . number_format($time_existing, 4) . "s\n";
echo "Optimized (1 query + JS mapping pattern): " . number_format($time_optimized, 4) . "s\n";
@unlink('db.sqlite');
