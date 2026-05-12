<?php
// tests/test_set_security_headers.php

echo "Testing setSecurityHeaders()... ";

if (!function_exists('xdebug_get_headers')) {
    echo "S (skipped: xdebug is not installed)\n";
    return;
}

// Call the function
setSecurityHeaders();

// Get the headers set in this request/script run
$headers = xdebug_get_headers();

$expectedHeaders = [
    "Content-Security-Policy: default-src 'self'; img-src 'self' http://www.enilton.com.br; script-src 'self' https://code.jquery.com https://cdn.jsdelivr.net https://maxcdn.bootstrapcdn.com; style-src 'self' https://maxcdn.bootstrapcdn.com 'unsafe-inline';",
    "X-Content-Type-Options: nosniff",
    "X-Frame-Options: SAMEORIGIN",
    "X-XSS-Protection: 1; mode=block"
];

foreach ($expectedHeaders as $expected) {
    assertEquals(true, in_array($expected, $headers), "Expected header '$expected' to be set.");
}

echo " \n";
