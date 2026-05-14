<?php
// Adding simple Test runner functions if not present
if (!function_exists('assertEquals')) {
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
}

$cwd = realpath(__DIR__ . '/..');

// Test script
$test_script = <<<EOT
<?php
require_once '$cwd/public/enviar_emails.php';
enviarEmail('test_capture@example.com', 'Test Subject 123', 'Test Body 456');
EOT;

$temp_script = tempnam(sys_get_temp_dir(), 'test_mail_');
file_put_contents($temp_script, $test_script);

$temp_mail = tempnam(sys_get_temp_dir(), 'mail_out_');

// Execute the script with sendmail_path capturing to $temp_mail
$cmd = 'php -d sendmail_path=' . escapeshellarg('cat >> ' . escapeshellarg($temp_mail)) . ' ' . escapeshellarg($temp_script);
exec($cmd);

$mail_output = file_get_contents($temp_mail);

$expected_to = 'To: test_capture@example.com';
$expected_subject = 'Subject: Test Subject 123';
$expected_body = 'Test Body 456';
$expected_from = 'From: no-reply@enilton.com.br';

$is_passed = strpos($mail_output, $expected_to) !== false &&
             strpos($mail_output, $expected_subject) !== false &&
             strpos($mail_output, $expected_body) !== false &&
             strpos($mail_output, $expected_from) !== false;

assertEquals(true, $is_passed, 'enviarEmail should send an email with correct To, Subject, Body, and From headers');

// Clean up
unlink($temp_script);
unlink($temp_mail);
