<?php
// Test cases for generatePasswordResetToken function

echo "Testing generatePasswordResetToken...\n";

class MockStmtToken {
    public $sql;
    public $types;
    public $user_id;
    public $token;
    public $expires;
    public $executed = false;

    public function __construct($sql) {
        $this->sql = $sql;
    }

    public function bind_param($types, &$user_id, &$token, &$expires) {
        $this->types = $types;
        $this->user_id = $user_id;
        $this->token = $token;
        $this->expires = $expires;
    }

    public function execute() {
        $this->executed = true;
    }
}

class MockConnToken {
    public $last_stmt;

    public function prepare($sql) {
        $this->last_stmt = new MockStmtToken($sql);
        return $this->last_stmt;
    }
}

global $conn;
$original_conn = $conn;
$conn = new MockConnToken();

$user_id_test = 42;
$start_time = time();
$token = generatePasswordResetToken($user_id_test);
$end_time = time();

// Test 1: Token format
assertEquals(64, strlen($token), "Token should be 64 characters long");
assertEquals(true, ctype_xdigit($token), "Token should be hexadecimal");

// Test 2: Database interaction
$stmt = $conn->last_stmt;
assertEquals("INSERT INTO password_reset_tokens (user_id, token, expires) VALUES (?, ?, ?)", $stmt->sql, "SQL statement should match");
assertEquals("iss", $stmt->types, "Types should be 'iss'");
assertEquals($user_id_test, $stmt->user_id, "User ID should be bound correctly");
assertEquals($token, $stmt->token, "Token should be bound correctly");
assertEquals(true, $stmt->executed, "Statement should be executed");

// Test 3: Expiration time
$expires_time = strtotime($stmt->expires);
$expected_min = strtotime('+1 hour', $start_time) - 2;
$expected_max = strtotime('+1 hour', $end_time) + 2;

$is_expires_valid = ($expires_time >= $expected_min && $expires_time <= $expected_max);
assertEquals(true, $is_expires_valid, "Expiration time should be approximately +1 hour. Got: " . $stmt->expires);

// Restore original connection
$conn = $original_conn;
