<?php
// ⭐ IMPORTANTE: Não pode ter NADA antes desta linha!
session_start();
require_once 'config.php';

// ⭐ DEFINIR HEADER LOGO NO INÍCIO
header('Content-Type: application/json');

// Log de entrada
error_log("🔍 ===== salvar_xp2.php =====");
error_log("📥 POST: " . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método inválido'
    ]);
    exit;
}

// Pegar parâmetros
$nomeAluno = $_POST['nomeAluno'] ?? null;
$jogo = $_POST['jogo'] ?? null;
$fase = $_POST['fase'] ?? null;
$xp = $_POST['xp'] ?? null;

// Validar
if (!$nomeAluno || !$jogo || !$fase || $xp === null) {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Parâmetros incompletos',
        'recebido' => [
            'nomeAluno' => $nomeAluno,
            'jogo' => $jogo,
            'fase' => $fase,
            'xp' => $xp
        ]
    ]);
    exit;
}

// Converter para números
$jogo = intval($jogo);
$fase = intval($fase);
$xp = intval($xp);

try {
    // Buscar usuario_id pelo nome
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE nome = ?");
    $stmt->execute([$nomeAluno]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Usuário não encontrado'
        ]);
        exit;
    }
    
    $usuario_id = $usuario['id'];
    
    // Registrar XP do Jogo 2 (usa xp_jogo2)
    $sucesso = registrarXPJogo2($pdo, $usuario_id, $fase, $xp);
    
    if ($sucesso) {
        // Buscar valores atualizados
        $xp_fase = obterXPFaseJogo2($pdo, $usuario_id, $fase);
        $xp_total = obterXPTotalJogo2($pdo, $usuario_id);
        
        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'XP salvo com sucesso',
            'xp_fase' => $xp_fase,
            'xp_total' => $xp_total
        ]);
        
        error_log("✅ XP salvo - Usuário: $nomeAluno | Fase: $fase | XP Fase: $xp_fase | Total: $xp_total");
    } else {
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Erro ao salvar XP'
        ]);
    }
    
} catch (Exception $e) {
    error_log("❌ ERRO: " . $e->getMessage());
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro: ' . $e->getMessage()
    ]);
}

error_log("===================================");
?>