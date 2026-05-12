<?php
// Mock objects for the mysqli and mysqli_stmt
class MockAuditoriaMysqliStmt {
    public $query;
    public $params = [];
    public $resultToBind = null;

    public function __construct($query) {
        $this->query = $query;
    }

    public function bind_param($types, ...$vars) {
        $this->params = $vars;
    }

    public function execute() {
        return true;
    }

    public function bind_result(&$var1) {
        if ($this->resultToBind !== null) {
            $var1 = $this->resultToBind;
        }
    }

    public function fetch() {
    }

    public function close() {
        return true;
    }
}

class MockAuditoriaMysqli {
    public $extintorIdToReturn = null;
    public $queries = [];

    public function prepare($query) {
        $stmt = new MockAuditoriaMysqliStmt($query);
        if (strpos($query, "SELECT id FROM bd_extintores") !== false) {
            $stmt->resultToBind = $this->extintorIdToReturn;
        }
        $this->queries[] = $stmt;
        return $stmt;
    }
}

// Test 1: Com codigo_extintor
global $conn;
$conn = new MockAuditoriaMysqli();
$conn->extintorIdToReturn = 999;

auditoria('CREATE', 'EXT-123', 1, 'admin', 'Test details 1');

assertEquals(2, count($conn->queries), "Deveria executar 2 queries (SELECT e INSERT)");
assertEquals("EXT-123", $conn->queries[0]->params[0] ?? null, "Parametro do SELECT incorreto");
assertEquals(1, $conn->queries[1]->params[0] ?? null, "User ID no INSERT incorreto");
assertEquals('admin', $conn->queries[1]->params[1] ?? null, "User Level no INSERT incorreto");
assertEquals('CREATE', $conn->queries[1]->params[2] ?? null, "Acao no INSERT incorreto");
assertEquals(999, $conn->queries[1]->params[3] ?? null, "Extintor ID no INSERT incorreto");
assertEquals('Test details 1', $conn->queries[1]->params[4] ?? null, "Detalhes no INSERT incorreto");

// Test 2: Sem codigo_extintor (null)
$conn = new MockAuditoriaMysqli();

auditoria('LOGIN', null, 2, 'bombeiro', 'Test details 2');

assertEquals(1, count($conn->queries), "Deveria executar 1 query (apenas INSERT)");
assertEquals(2, $conn->queries[0]->params[0] ?? null, "User ID no INSERT incorreto");
assertEquals('bombeiro', $conn->queries[0]->params[1] ?? null, "User Level no INSERT incorreto");
assertEquals('LOGIN', $conn->queries[0]->params[2] ?? null, "Acao no INSERT incorreto");
assertEquals(null, $conn->queries[0]->params[3] ?? null, "Extintor ID no INSERT incorreto");
assertEquals('Test details 2', $conn->queries[0]->params[4] ?? null, "Detalhes no INSERT incorreto");
