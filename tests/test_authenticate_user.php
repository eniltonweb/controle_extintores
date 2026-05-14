<?php
// Test cases for authenticateUser function

echo "Testing authenticateUser...\n";

class MockResultAuth {
    public $num_rows;
    public $data;

    public function __construct($num_rows, $data = null) {
        $this->num_rows = $num_rows;
        $this->data = $data;
    }

    public function fetch_assoc() {
        return $this->data;
    }
}

class MockStmtAuth {
    public $result;

    public function __construct($result) {
        $this->result = $result;
    }

    public function bind_param($types, &$param1) {
    }

    public function execute() {
        return true;
    }

    public function get_result() {
        return $this->result;
    }
}

class MockConnAuth {
    public $resultToReturn;

    public function prepare($sql) {
        return new MockStmtAuth($this->resultToReturn);
    }
}

$conn = new MockConnAuth();

// Case 1: Successful authentication
$conn->resultToReturn = new MockResultAuth(1, ['id' => 456, 'password' => password_hash('mysecret', PASSWORD_DEFAULT), 'level' => 'fornecedor']);
$_SESSION = [];
$result = authenticateUser('user@example.com', 'mysecret');
assertEquals(true, $result, "Should return true on successful authentication");
assertEquals(456, $_SESSION['user_id'] ?? null, "Should set user_id in session on successful authentication");
assertEquals('fornecedor', $_SESSION['user_level'] ?? null, "Should set user_level in session on successful authentication");

// Case 2: Wrong password
$conn->resultToReturn = new MockResultAuth(1, ['id' => 456, 'password' => password_hash('mysecret', PASSWORD_DEFAULT), 'level' => 'fornecedor']);
$_SESSION = [];
$result = authenticateUser('user@example.com', 'wrongpassword');
assertEquals(false, $result, "Should return false on wrong password");
assertEquals(null, $_SESSION['user_id'] ?? null, "Should not set user_id in session on wrong password");

// Case 3: User not found
$conn->resultToReturn = new MockResultAuth(0);
$_SESSION = [];
$result = authenticateUser('notfound@example.com', 'anypassword');
assertEquals(false, $result, "Should return false when user is not found");
assertEquals(null, $_SESSION['user_id'] ?? null, "Should not set user_id in session when user is not found");

// Case 4: Database query fails (result is false)
$conn->resultToReturn = false;
$_SESSION = [];
$result = authenticateUser('error@example.com', 'anypassword');
assertEquals(false, $result, "Should return false when database query fails");
