<?php
// Test cases for logAuditAction function

echo "Testing logAuditAction...\n";

class MockStmt {
    public $bind_param_called = false;
    public $execute_called = false;
    public $bound_types;
    public $bound_params = [];

    public function bind_param($types, ...$vars) {
        $this->bind_param_called = true;
        $this->bound_types = $types;
        $this->bound_params = $vars;
    }

    public function execute() {
        $this->execute_called = true;
    }

    public function reset() {
        $this->bind_param_called = false;
        $this->execute_called = false;
        $this->bound_types = null;
        $this->bound_params = [];
    }
}

class MockConn {
    public $prepare_called = false;
    public $prepared_sql;
    public $stmt;

    public function __construct() {
        $this->stmt = new MockStmt();
    }

    public function prepare($sql) {
        $this->prepare_called = true;
        $this->prepared_sql = $sql;
        return $this->stmt;
    }

    public function reset() {
        $this->prepare_called = false;
        $this->prepared_sql = null;
        $this->stmt->reset();
    }
}

global $conn;
$original_conn = $conn;
$conn = new MockConn();

// Test 1: Standard logging
logAuditAction(1, 'login', 'User logged in successfully');

assertEquals(true, $conn->prepare_called, "Should call prepare");
assertEquals("INSERT INTO audit_log (user_id, action, details, timestamp) VALUES (?, ?, ?, NOW())", $conn->prepared_sql, "Should prepare correct SQL statement");
assertEquals(true, $conn->stmt->bind_param_called, "Should call bind_param");
assertEquals('iss', $conn->stmt->bound_types, "Should bind parameters with 'iss' types");
assertEquals(1, $conn->stmt->bound_params[0], "Should bind user_id correctly");
assertEquals('login', $conn->stmt->bound_params[1], "Should bind action correctly");
assertEquals('User logged in successfully', $conn->stmt->bound_params[2], "Should bind details correctly");
assertEquals(true, $conn->stmt->execute_called, "Should call execute");

$conn->reset();

// Test 2: Logging with null details
logAuditAction(2, 'logout', null);

assertEquals(true, $conn->prepare_called, "Should call prepare (null details)");
assertEquals('iss', $conn->stmt->bound_types, "Should bind parameters with 'iss' types (null details)");
assertEquals(2, $conn->stmt->bound_params[0], "Should bind user_id correctly (null details)");
assertEquals('logout', $conn->stmt->bound_params[1], "Should bind action correctly (null details)");
assertEquals(null, $conn->stmt->bound_params[2], "Should bind null details correctly");
assertEquals(true, $conn->stmt->execute_called, "Should call execute (null details)");

// Restore original connection
$conn = $original_conn;
