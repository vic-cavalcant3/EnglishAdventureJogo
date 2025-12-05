<?php
require_once 'config.php';
verificarLogin();

header('Content-Type: application/json');

// Receber dados via POST
$dados = json_decode(file_get_contents('php://input'), true);

if (!isset($dados['atividade']) || !isset($dados['acertou'])) {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Dados incompletos'
    ]);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$atividade = $dados['atividade']; // Ex: "fase1_atividade1", "fase2_atividade3"
$acertou = $dados['acertou'] ? 1 : 0;

// Verificar se já existe registro para esta atividade
$stmt = $pdo->prepare("
    SELECT id FROM estrelas 
    WHERE nomealuno = (SELECT nome FROM usuarios WHERE id = ?)
    AND atividade = ?
");
$stmt->execute([$usuario_id, $atividade]);
$jaExiste = $stmt->fetch();

if ($jaExiste) {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Atividade já foi registrada anteriormente'
    ]);
    exit;
}

// Registrar a estrela
$sucesso = registrarEstrela($pdo, $usuario_id, $atividade, $acertou);

if ($sucesso) {
    // Verificar quantas estrelas o usuário tem agora
    $numero_fase = (int) filter_var($atividade, FILTER_SANITIZE_NUMBER_INT);
    $total_estrelas = contarEstrelasFase($pdo, $usuario_id, $numero_fase);
    
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Estrela registrada com sucesso!',
        'total_estrelas_fase' => $total_estrelas
    ]);
} else {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao registrar estrela'
    ]);
}
?>