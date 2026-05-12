<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
include '../config/db_conexao.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] != 'admin') {
    header('Location: index.php');
    exit();
}

$sql = "SELECT codigo, proxima_manutencao_n2 FROM bd_extintores WHERE dias_para_expirar_n2 <= 30";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = getenv('SMTP_HOST') ?: 'smtp.example.com';
            $mail->SMTPAuth = true;
            $mail->Username = getenv('SMTP_USER') ?: 'seu_email@example.com';
            $mail->Password = getenv('SMTP_PASS') ?: 'sua_senha';
            $mail->SMTPSecure = getenv('SMTP_SECURE') ?: 'tls';
            $mail->Port = (int)(getenv('SMTP_PORT') ?: 587);

            $mail->setFrom(getenv('SMTP_FROM_EMAIL') ?: 'seu_email@example.com', getenv('SMTP_FROM_NAME') ?: 'Sistema de Manutenção');
            $mail->addAddress(getenv('SMTP_TO_EMAIL') ?: 'destinatario@example.com');

            $mail->isHTML(true);
            $mail->Subject = 'Alerta de Manutenção Pendente';
            $mail->Body = 'O extintor com código ' . $row['codigo'] . ' está com manutenção pendente. Próxima manutenção: ' . $row['proxima_manutencao_n2'];

            $mail->send();
            echo 'Mensagem enviada para ' . $row['codigo'] . '<br>';
        } catch (Exception $e) {
            echo "A mensagem não pôde ser enviada. Erro: {$mail->ErrorInfo}";
        }
    }
} else {
    echo "Nenhum extintor com manutenção pendente.";
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Envio de Alertas</title>
</head>
<body>
    <h2>Envio de Alertas por Email</h2>
</body>
</html>