<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../config/db_conexao.php';

$username = getenv('ADMIN_USERNAME') ?: 'admin';
$admin_password = getenv('ADMIN_PASSWORD');

if (!$admin_password) {
    die("Erro: A variável de ambiente ADMIN_PASSWORD não está configurada.");
}

$password_hashed = password_hash($admin_password, PASSWORD_DEFAULT);
$nivel_acesso = 'admin';

// Verificar se o usuário administrador já existe usando Prepared Statements
$sql_check = "SELECT id FROM usuarios WHERE username = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("s", $username);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check) {
    if ($result_check->num_rows == 0) {
        // Inserir o usuário administrador usando Prepared Statements
        $sql = "INSERT INTO usuarios (username, password, nivel_acesso) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $password_hashed, $nivel_acesso);

        if ($stmt->execute() === TRUE) {
            echo "Usuário administrador registrado com sucesso.";
        } else {
            echo "Erro ao registrar usuário administrador: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Usuário administrador já existe.";
    }
} else {
    echo "Erro ao verificar existência do usuário administrador.";
}

$stmt_check->close();
$conn->close();
?>
