<?php
session_start();

include '../config/db_conexao.php';
include 'auditoria.php';
include_once 'includes/functions.php'; // Incluindo o arquivo que contém a função auditoria

include_once 'includes/auth.php';
requirePermission('bombeiro');

$user_id = $_SESSION['user_id']; // Obter o ID do usuário da sessão

// Preparar a consulta SQL para obter o nome de usuário
$sql = "SELECT username FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username);

// Buscar o resultado da consulta
if ($stmt->fetch()) {
    // Definir a variável de sessão com o nome de usuário
    $_SESSION['username'] = $username;
    $stmt->close();
} else {
    echo "Usuário não encontrado.";
    $stmt->close();
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        header('Location: formulario_inspecao.php?error=' . urlencode('Erro de validação CSRF.'));
        exit();
    }
    $codigo = filter_input(INPUT_POST, 'codigo', FILTER_SANITIZE_SPECIAL_CHARS);
    $Local_Exato = filter_input(INPUT_POST, 'Local_Exato', FILTER_SANITIZE_SPECIAL_CHARS); // Novo campo
    $selo_do_Inmetro = filter_input(INPUT_POST, 'selo_do_Inmetro', FILTER_SANITIZE_SPECIAL_CHARS);
    $sinalizacao_vertical = filter_input(INPUT_POST, 'sinalizacao_vertical', FILTER_SANITIZE_SPECIAL_CHARS);
    $sinalizacao_piso = filter_input(INPUT_POST, 'sinalizacao_piso', FILTER_SANITIZE_SPECIAL_CHARS);
    $ficha_inspecao_trimestral = filter_input(INPUT_POST, 'ficha_inspecao_trimestral', FILTER_SANITIZE_SPECIAL_CHARS);
    $lacre = filter_input(INPUT_POST, 'lacre', FILTER_SANITIZE_SPECIAL_CHARS);
    $pressao_manometro = filter_input(INPUT_POST, 'pressao_manometro', FILTER_SANITIZE_SPECIAL_CHARS);
    $anel_identificacao = filter_input(INPUT_POST, 'anel_identificacao', FILTER_SANITIZE_SPECIAL_CHARS);
    $pesagem_co2_semestral = filter_input(INPUT_POST, 'pesagem_co2_semestral', FILTER_SANITIZE_SPECIAL_CHARS);
    $comentarios = filter_input(INPUT_POST, 'comentarios', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    // Lidar com o upload de fotos (Modificado para gerar nome único e evitar sobrescrita)
    $foto_nome = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $extensao = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto_nome = uniqid() . '_' . time() . '.' . $extensao; 
        $foto_destino = "../uploads/" . $foto_nome;
        move_uploaded_file($_FILES['foto']['tmp_name'], $foto_destino);
    }

    // Atualizar inspeção no banco de dados
    $sql = "UPDATE bd_extintores SET 
            Local_Exato = ?, 
            selo_do_Inmetro = ?, 
            sinalizacao_vertical = ?, 
            sinalizacao_piso = ?, 
            ficha_inspecao_trimestral = ?, 
            lacre = ?, 
            pressao_manometro = ?, 
            anel_identificacao = ?, 
            pesagem_co2_semestral = ?, 
            comentarios = ?, 
            foto = ?, 
            usuario = ?, 
            inspecao_trimestral_nivel1 = NOW()
			WHERE codigo = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param('sssssssssssss', $Local_Exato, $selo_do_Inmetro, $sinalizacao_vertical, $sinalizacao_piso, $ficha_inspecao_trimestral, $lacre, $pressao_manometro, $anel_identificacao, $pesagem_co2_semestral, $comentarios, $foto_nome, $_SESSION['username'], $codigo);

    if ($stmt->execute()) {
        auditoria('Inspeção de nível 1 realizada', $codigo, $_SESSION['user_id'], $_SESSION['user_level'], 'Inspeção realizada com sucesso.');
        header('Location: formulario_inspecao.php?codigo=' . $codigo . '&message=Inspeção salva com sucesso');
        exit();
    } else {
        echo "Erro ao salvar a inspeção: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>