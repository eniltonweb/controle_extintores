<?php
// Test cases for verifyCSRFToken function

echo "Testing verifyCSRFToken...\n";

// Case 1: Session token is not set
if (isset($_SESSION['csrf_token'])) {
    unset($_SESSION['csrf_token']);
}
assertEquals(false, verifyCSRFToken("any_token"), "Should return false when session token is not set");

// Case 2: Tokens do not match
$_SESSION['csrf_token'] = "valid_token_123";
assertEquals(false, verifyCSRFToken("invalid_token_456"), "Should return false when tokens do not match");

// Case 3: Tokens match exactly
$_SESSION['csrf_token'] = "valid_token_123";
assertEquals(true, verifyCSRFToken("valid_token_123"), "Should return true when tokens match exactly");

// Case 4: Token is empty string but matches
$_SESSION['csrf_token'] = "";
assertEquals(true, verifyCSRFToken(""), "Should return true when both tokens are empty strings");

// Case 5: Token is not matching empty string
$_SESSION['csrf_token'] = "";
assertEquals(false, verifyCSRFToken("not_empty"), "Should return false when session token is empty but provided token is not");

// Cleanup
unset($_SESSION['csrf_token']);
