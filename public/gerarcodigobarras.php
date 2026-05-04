<?php
// Habilitar exibição de erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'cod/fpdf.php';     // Certifique-se de que o caminho está correto
require_once 'cod/barcode.php';  // Certifique-se de que o caminho está correto

// Criar uma nova instância de PDF
$pdf = new PDF_Code128();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

// Consultar extintores do banco de dados
include '../config/db_conexao.php';

// Query para obter os extintores
$sql = "SELECT codigo FROM bd_extintores";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $codigo = $row['codigo'];
        $url = "https://enilton.com.br/extintores/public/codigobarras.php?codigo=" . urlencode($codigo);

        // Gerar o código de barras
        $pdf->Code128(50, $pdf->GetY(), $url, 100, 20); // 50, 100, 20 são x, largura, altura respectivamente
        $pdf->Ln(25); // Avançar para a próxima linha, ajusta se necessário
        $pdf->Cell(0, 10, $url, 0, 1);
    }
} else {
    $pdf->Cell(0, 10, 'Nenhum extintor encontrado.', 0, 1);
}

// Fechar a conexão
$conn->close();

// Saída do PDF
$pdf->Output('D', 'extintores_codigos_de_barras.pdf'); // Mudar para 'I' se quiser visualizar no navegador
?>
