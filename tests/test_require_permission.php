<?php
echo "Testing requirePermission...\n";

$endpoint_code = <<<'PHP'
<?php
require_once __DIR__ . '/../public/includes/auth.php';

if (isset($_GET['level']) && $_GET['level'] !== 'none') {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_level'] = $_GET['level'];
}

requirePermission($_GET['req']);
echo "DID_NOT_EXIT";
PHP;

file_put_contents(__DIR__ . '/test_require_permission_endpoint.php', $endpoint_code);

$port = 8124;
$pid = shell_exec('php -S localhost:' . $port . ' -t ' . escapeshellarg(__DIR__) . ' > /dev/null 2>&1 & echo $!');

$started = false;
for ($i=0; $i<20; $i++) {
    $fp = @fsockopen('localhost', $port, $errCode, $errStr, 0.1);
    if ($fp) {
        fclose($fp);
        $started = true;
        break;
    }
    usleep(100000);
}

if ($started) {
    $context = stream_context_create(['http' => ['follow_location' => false]]);

    // Case 1: Unauthenticated
    $content = file_get_contents('http://localhost:' . $port . '/test_require_permission_endpoint.php?level=none&req=admin', false, $context);
    $location_found = false;
    foreach ($http_response_header as $header) {
        if (strpos($header, 'Location: access_denied.php') !== false) {
            $location_found = true;
            break;
        }
    }
    assertEquals(true, $location_found, "Unauthenticated user should be redirected");
    assertEquals("", trim($content), "Unauthenticated user should not see DID_NOT_EXIT output");

    // Case 2: Insufficient permission
    $content = file_get_contents('http://localhost:' . $port . '/test_require_permission_endpoint.php?level=bombeiro&req=admin', false, $context);
    $location_found = false;
    foreach ($http_response_header as $header) {
        if (strpos($header, 'Location: access_denied.php') !== false) {
            $location_found = true;
            break;
        }
    }
    assertEquals(true, $location_found, "User without required permission should be redirected");
    assertEquals("", trim($content), "User without required permission should not see DID_NOT_EXIT output");

    // Case 3: Sufficient permission
    $content = file_get_contents('http://localhost:' . $port . '/test_require_permission_endpoint.php?level=admin&req=admin', false, $context);
    assertEquals("DID_NOT_EXIT", trim($content), "User with required permission should not exit or redirect");
} else {
    assertEquals(true, false, "Failed to start local PHP server for header tests");
}

shell_exec('kill -9 ' . escapeshellarg(trim($pid)));
@unlink(__DIR__ . '/test_require_permission_endpoint.php');
echo "\n";
