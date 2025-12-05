<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// ⭐ LOG TUDO QUE RECEBEU
error_log("🔍 ===== marcar_fase_jogada.php =====");
error_log("📥 POST recebido: " . print_r($_POST, true));

// Verificar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método inválido'
    ]);
    exit;
}

// Pegar parâmetros
$usuario_id = $_POST['usuario_id'] ?? null;
$jogo = $_POST['jogo'] ?? null;
$fase = $_POST['fase'] ?? null;

// ⭐ LOG OS VALORES RECEBIDOS
error_log("🎮 Valores recebidos:");
error_log("   usuario_id: " . var_export($usuario_id, true));
error_log("   jogo: " . var_export($jogo, true));
error_log("   fase: " . var_export($fase, true));

// Validar parâmetros
if (!$usuario_id || !$jogo || !$fase) {
    $erro = [
        'sucesso' => false,
        'mensagem' => 'Parâmetros incompletos',
        'recebido' => [
            'usuario_id' => $usuario_id,
            'jogo' => $jogo,
            'fase' => $fase
        ]
    ];
    error_log("❌ " . json_encode($erro));
    echo json_encode($erro);
    exit;
}

// Converter para inteiro (IMPORTANTE!)
$usuario_id = intval($usuario_id);
$jogo = intval($jogo);
$fase = intval($fase);

error_log("🔄 Valores convertidos:");
error_log("   usuario_id: $usuario_id");
error_log("   jogo: $jogo");
error_log("   fase: $fase");

try {
    // Chamar a função do config.php
    $sucesso = registrarFaseJogada($pdo, $usuario_id, $jogo, $fase);
    
    if ($sucesso) {
        $resposta = [
            'sucesso' => true,
            'mensagem' => "Fase $fase do Jogo $jogo registrada com sucesso",
            'dados' => [
                'usuario_id' => $usuario_id,
                'jogo' => $jogo,
                'fase' => $fase
            ]
        ];
        error_log("✅ " . json_encode($resposta));
        echo json_encode($resposta);
    } else {
        $erro = [
            'sucesso' => false,
            'mensagem' => 'Erro ao registrar fase no banco'
        ];
        error_log("❌ " . json_encode($erro));
        echo json_encode($erro);
    }
    
} catch (Exception $e) {
    $erro = [
        'sucesso' => false,
        'mensagem' => 'Erro: ' . $e->getMessage()
    ];
    error_log("❌ EXCEPTION: " . $e->getMessage());
    echo json_encode($erro);
}

error_log("===================================");
?>