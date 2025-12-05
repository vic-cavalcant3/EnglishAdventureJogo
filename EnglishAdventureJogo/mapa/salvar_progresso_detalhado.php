<?php
header('Content-Type: application/json');
require_once 'config.php';

// ⭐ LOG INICIAL
error_log("📥 salvar_progresso_detalhado.php chamado");
error_log("📦 POST recebido: " . json_encode($_POST));

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

error_log("✅ Dados processados: usuario_id=$usuario_id, fase=$fase, atividade=$atividade, gramatica=$tipo_gramatica, habilidade=$tipo_habilidade, acertou=$acertou");

// Validações
if ($usuario_id <= 0) {
    error_log("❌ Usuário inválido: $usuario_id");
    echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário inválido']);
    exit;
}

if ($fase < 1 || $fase > 10) {
    error_log("❌ Fase inválida: $fase");
    echo json_encode(['sucesso' => false, 'mensagem' => 'Fase inválida']);
    exit;
}

if (empty($atividade)) {
    error_log("❌ Atividade vazia");
    echo json_encode(['sucesso' => false, 'mensagem' => 'Atividade não especificada']);
    exit;
}

if (!in_array($tipo_gramatica, ['afirmativa', 'interrogativa', 'negativa'])) {
    error_log("❌ Tipo gramática inválido: $tipo_gramatica");
    echo json_encode(['sucesso' => false, 'mensagem' => 'Tipo de gramática inválido']);
    exit;
}

if (!in_array($tipo_habilidade, ['speaking', 'reading', 'listening', 'writing', 'choice'])) {
    error_log("❌ Tipo habilidade inválido: $tipo_habilidade");
    echo json_encode(['sucesso' => false, 'mensagem' => 'Tipo de habilidade inválido']);
    exit;
}

error_log("✅ Todas validações passaram, chamando registrarProgressoDetalhado()");

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

error_log("📊 Resultado de registrarProgressoDetalhado: " . ($sucesso ? 'SUCESSO' : 'FALHA'));

if ($sucesso) {
    // Obter resumo atualizado
    $resumo = obterResumoProgresso($pdo, $usuario_id);
    
    error_log("✅ Resumo obtido: " . json_encode($resumo));
    
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Progresso registrado com sucesso',
        'resumo' => $resumo
    ]);
} else {
    error_log("❌ Falha ao registrar progresso");
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao registrar progresso'
    ]);
}
?>