<?php
// auth.php

session_start();

/**
 * Verifica se o usuário está autenticado
 */
function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

/**
 * Encerra a sessão do usuário
 */
function logoutUser() {
    session_unset();
    session_destroy();
}

/**
 * Verifica se o usuário tem permissão para acessar uma página
 */
function hasPermission($required_level) {
    if (!isAuthenticated()) {
        return false;
    }

    $user_level = $_SESSION['user_level'];

    switch ($required_level) {
        case 'admin':
            return $user_level == 'admin';
        case 'bombeiro':
            return $user_level == 'admin' || $user_level == 'bombeiro';
        case 'fornecedor':
            return $user_level == 'admin' || $user_level == 'fornecedor';
        default:
            return true;
    }
}


/**
 * Gera um token de recuperação de senha
 */
function generatePasswordResetToken($user_id) {
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    global $conn;
    $sql = "INSERT INTO password_reset_tokens (user_id, token, expires) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iss', $user_id, $token, $expires);
    $stmt->execute();

    return $token;
}
