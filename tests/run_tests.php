<?php
ob_start();
// Simple Test Runner

$tests_passed = 0;
$tests_failed = 0;
$failures = [];

function assertEquals($expected, $actual, $message = '') {
    global $tests_passed, $tests_failed, $failures;
    if ($expected === $actual) {
        $tests_passed++;
        echo ".";
    } else {
        $tests_failed++;
        echo "F";
        $failures[] = "Failed asserting that " . var_export($actual, true) . " matches expected " . var_export($expected, true) . ". " . $message;
    }
}

echo "Running tests...\n\n";

// Include files to test
require_once __DIR__ . '/../public/includes/functions.php';
require_once __DIR__ . '/../public/includes/auth.php';
require_once __DIR__ . '/../public/auditoria.php';

// Include test cases
require_once __DIR__ . '/test_sanitize_input.php';
require_once __DIR__ . '/test_verify_csrf_token.php';
require_once __DIR__ . '/test_is_authenticated.php';
require_once __DIR__ . '/test_auditoria.php';

echo "\n\n";

if ($tests_failed > 0) {
    echo "FAILURES!\n";
    echo "Tests: " . ($tests_passed + $tests_failed) . ", Passed: $tests_passed, Failed: $tests_failed\n\n";
    foreach ($failures as $failure) {
        echo "- $failure\n";
    }
    exit(1);
} else {
    echo "OK (" . ($tests_passed + $tests_failed) . " tests)\n";
    exit(0);
}
