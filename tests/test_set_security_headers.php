<?php
// Test cases for setSecurityHeaders function

echo "Testing setSecurityHeaders...\n";

// To test headers reliably in a CLI environment without extensions like xdebug or uopz,
// we can spawn a small PHP dev server process, request a script that calls the function,
// and then parse the headers from the HTTP response.

// Use a dynamically assigned port or a sufficiently random one to avoid collisions
$port = 8999 + rand(1, 100);
$temp_file = 'temp_header_test_' . uniqid() . '.php';

$script = <<<'CODE'
<?php
require_once __DIR__ . '/../public/includes/functions.php';
setSecurityHeaders();
echo "OK";
CODE;

file_put_contents(__DIR__ . '/' . $temp_file, $script);

// Start PHP built-in server using proc_open for better process management
$cmd = "php -S localhost:$port -t " . escapeshellarg(__DIR__);
$descriptorspec = [
    0 => ["pipe", "r"],
    1 => ["pipe", "w"],
    2 => ["pipe", "w"]
];

$process = proc_open($cmd, $descriptorspec, $pipes);

// Ensure cleanup on script exit or fatal error
register_shutdown_function(function() use ($process, $temp_file) {
    @unlink(__DIR__ . '/' . $temp_file);
    if (is_resource($process)) {
        proc_terminate($process);
        proc_close($process);
    }
});

if (is_resource($process)) {
    // Wait for server to be ready
    $max_retries = 20;
    $server_ready = false;
    for ($i = 0; $i < $max_retries; $i++) {
        usleep(100000); // 100ms
        $fp = @fsockopen("localhost", $port, $errno, $errstr, 0.1);
        if ($fp) {
            fclose($fp);
            $server_ready = true;
            break;
        }
    }

    if (!$server_ready) {
        global $failures, $tests_failed;
        $tests_failed++;
        echo "F";
        $failures[] = "Failed to start PHP dev server for header testing";
    } else {
        $ch = curl_init("http://localhost:$port/$temp_file");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true); // just headers
        $response = curl_exec($ch);
        curl_close($ch);

        $headers_string = $response;

        $expected_csp = "Content-Security-Policy: default-src 'self'; img-src 'self' http://www.enilton.com.br; script-src 'self' https://code.jquery.com https://cdn.jsdelivr.net https://maxcdn.bootstrapcdn.com; style-src 'self' https://maxcdn.bootstrapcdn.com 'unsafe-inline';";

        assertEquals(true, strpos($headers_string, $expected_csp) !== false, "Should contain correct CSP header");
        assertEquals(true, strpos($headers_string, "X-Content-Type-Options: nosniff") !== false, "Should contain X-Content-Type-Options");
        assertEquals(true, strpos($headers_string, "X-Frame-Options: SAMEORIGIN") !== false, "Should contain X-Frame-Options");
        assertEquals(true, strpos($headers_string, "X-XSS-Protection: 1; mode=block") !== false, "Should contain X-XSS-Protection");
    }
} else {
    global $failures, $tests_failed;
    $tests_failed++;
    echo "F";
    $failures[] = "Failed to start PHP dev server process";
}
