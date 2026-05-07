<?php
session_start();
include '../config/db_conexao.php';
include 'auditoria.php';
include 'includes/functions.php';

// Verificar se o usuário está logado e se tem permissão para acessar esta página
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] != 'admin') {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        header('Location: registrar_usuario.php?error=Token de segurança inválido.');
        exit();
    }

    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    if (!$id) {
        header('Location: registrar_usuario.php?error=ID do usuário não fornecido.');
        exit();
    }

    // Obter o nome do usuário antes de deletar
    $sql_user = "SELECT username FROM usuarios WHERE id = ?";
    $stmt_user = $conn->prepare($sql_user);
    if (!$stmt_user) {
        header('Location: registrar_usuario.php?error=Erro ao preparar a consulta: ' . urlencode($conn->error));
        exit();
    }
    $stmt_user->bind_param("i", $id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();

    if ($result_user->num_rows > 0) {
        $user = $result_user->fetch_assoc();
        $username = $user['username'];

        // Deletar registros associados na tabela auditoria_logs
        $sql_auditoria = "DELETE FROM auditoria_logs WHERE user_id = ?";
        $stmt_auditoria = $conn->prepare($sql_auditoria);
        if (!$stmt_auditoria) {
            header('Location: registrar_usuario.php?error=Erro ao preparar a consulta de deleção de auditoria: ' . urlencode($conn->error));
            exit();
        }
        $stmt_auditoria->bind_param("i", $id);
        if (!$stmt_auditoria->execute()) {
            header('Location: registrar_usuario.php?error=Erro ao deletar registros de auditoria: ' . urlencode($stmt_auditoria->error));
            exit();
        }
        $stmt_auditoria->close();

        // Deletar usuário
        $sql = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            header('Location: registrar_usuario.php?error=Erro ao preparar a consulta de deleção: ' . urlencode($conn->error));
            exit();
        }
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            auditoria('Deletar um usuário', null, $_SESSION['user_id'], $_SESSION['user_level'], 'Usuário ' . $username . ' deletado com sucesso.');
            header('Location: registrar_usuario.php?message=Usuário deletado com sucesso');
            exit();
        } else {
            header('Location: registrar_usuario.php?error=Erro ao deletar usuário: ' . urlencode($stmt->error));
            exit();
        }

        $stmt->close();
    } else {
        header('Location: registrar_usuario.php?error=Usuário não encontrado.');
        exit();
    }

    $stmt_user->close();
} else {
    header('Location: registrar_usuario.php');
    exit();
}

$conn->close();
?>
