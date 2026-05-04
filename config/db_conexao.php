<?php
$servername = "eniltonbd.mysql.dbaas.com.br";
$username = "eniltonbd";
$password = "Nil2024#";
$dbname = "eniltonbd";

// Criar conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>
