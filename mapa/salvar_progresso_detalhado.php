<?php
header('Content-Type: application/json');
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método inválido']);
    exit;
}

$usuario_id = intval($_POST['usuario_id'] ?? 0);
$fase = intval($_POST['fase'] ?? 0);
$atividade = $_POST['atividade'] ?? '';
$tipo_gramatica = $_POST['tipo_gramatica'] ?? '';
$tipo_habilidade = $_POST['tipo_habilidade'] ?? '';
$acertou = intval($_POST['acertou'] ?? 0);

// Validações
if ($usuario_id <= 0) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário inválido']);
    exit;
}

if ($fase < 1 || $fase > 10) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Fase inválida']);
    exit;
}

if (empty($atividade)) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Atividade não especificada']);
    exit;
}

if (!in_array($tipo_gramatica, ['afirmativa', 'interrogativa', 'negativa'])) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Tipo de gramática inválido']);
    exit;
}

if (!in_array($tipo_habilidade, ['speaking', 'reading', 'listening', 'writing', 'choice'])) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Tipo de habilidade inválido']);
    exit;
}

// Registrar progresso
$sucesso = registrarProgressoDetalhado(
    $pdo, 
    $usuario_id, 
    $fase, 
    $atividade, 
    $tipo_gramatica, 
    $tipo_habilidade, 
    $acertou == 1
);

if ($sucesso) {
    // Obter resumo atualizado
    $resumo = obterResumoProgresso($pdo, $usuario_id);
    
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Progresso registrado com sucesso',
        'resumo' => $resumo
    ]);
} else {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao registrar progresso'
    ]);
}
?>