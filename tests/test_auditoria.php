<?php
// Test cases for auditoria function

echo "Testing auditoria...\n";

class MockStmtAuditoria {
    public $query;
    public $params = [];
    public $bound_result_vars = [];
    public $extintor_id_to_return = null;

    public function __construct($query) {
        $this->query = $query;
    }

    public function bind_param($types, &...$vars) {
        $this->params = [];
        foreach ($vars as $key => $var) {
            $this->params[] = $var;
        }
    }

    public function execute() {}

    public function bind_result(&...$vars) {
        $this->bound_result_vars[0] = &$vars[0];
    }

    public function fetch() {
        if ($this->extintor_id_to_return !== null && array_key_exists(0, $this->bound_result_vars)) {
            $this->bound_result_vars[0] = $this->extintor_id_to_return;
        }
    }

    public function close() {}
}

class MockConnectionAuditoria {
    public $queries = [];
    public $statements = [];
    public $extintor_id_to_return = null;

    public function prepare($query) {
        $this->queries[] = $query;
        $stmt = new MockStmtAuditoria($query);
        if (strpos($query, 'SELECT id FROM bd_extintores') !== false) {
            $stmt->extintor_id_to_return = $this->extintor_id_to_return;
        }
        $this->statements[] = $stmt;
        return $stmt;
    }

    public function reset() {
        $this->queries = [];
        $this->statements = [];
        $this->extintor_id_to_return = null;
    }
}

global $conn;
$conn = new MockConnectionAuditoria();

require_once __DIR__ . '/../public/auditoria.php';

// Test 1: Happy path with $codigo_extintor
$conn->reset();
$conn->extintor_id_to_return = 123;
auditoria('UPDATE', 'EXT-001', 1, 'admin', 'Details');

assertEquals(2, count($conn->queries), "Should execute 2 queries");
assertEquals(123, $conn->statements[1]->params[3], "Should use fetched extintor_id (123) in INSERT query");
assertEquals('EXT-001', $conn->statements[0]->params[0], "Should bind codigo_extintor to SELECT query");

// Test 2: Null $codigo_extintor
$conn->reset();
auditoria('LOGIN', null, 2, 'bombeiro');

assertEquals(1, count($conn->queries), "Should execute only 1 query (INSERT) when codigo_extintor is null");
assertEquals(null, $conn->statements[0]->params[3], "Should bind null as extintor_id in INSERT query");
assertEquals('', $conn->statements[0]->params[4], "Should use default empty string for detalhes");

// Test 3: No extintor found for non-null $codigo_extintor
$conn->reset();
$conn->extintor_id_to_return = null; // Fetch will not set id, so it remains null
auditoria('DELETE', 'INVALID', 1, 'admin', 'Deleted');

assertEquals(2, count($conn->queries), "Should execute 2 queries");
assertEquals(null, $conn->statements[1]->params[3], "Should use null as extintor_id since fetch returned nothing");
