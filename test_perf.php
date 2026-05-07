<?php
session_start();
$_SESSION['user_id'] = 1;
$start = microtime(true);
ob_start();
chdir('public');
include 'dashboard2.php';
ob_end_clean();
$end = microtime(true);
echo "Execution time: " . ($end - $start) . " seconds\n";
?>
