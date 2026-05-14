<?php
// Test cases for isAuthenticated function

echo "Testing isAuthenticated...\n";

// Case 1: user_id is not set
if (isset($_SESSION['user_id'])) {
    unset($_SESSION['user_id']);
}
assertEquals(false, isAuthenticated(), "Should return false when user_id is not set in session");

// Case 2: user_id is set
$_SESSION['user_id'] = 123;
assertEquals(true, isAuthenticated(), "Should return true when user_id is set in session");

// Cleanup
unset($_SESSION['user_id']);
