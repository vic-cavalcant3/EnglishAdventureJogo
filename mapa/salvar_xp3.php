<?php
require_once("conexao.php");
require_once("config.php");

header('Content-Type: application/json; charset=utf-8');

error_log("🎯 SALVAR_XP3.PHP INICIADO - JOGO 4");

if (isset($_POST['nomeAluno'], $_POST['fase'], $_POST['xp'])) {

    $nomeAluno = $_POST['nomeAluno'];
    $fase = intval($_POST['fase']);
    $xp = intval($_POST['xp']);

    error_log("📥 Dados recebidos - Nome: $nomeAluno, Fase: $fase, XP: $xp");

    try {
        // 1️⃣ Buscar ID do usuário
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE nome = ?");
        $stmt->execute([$nomeAluno]);
        $usuario = $stmt->fetch();
        
        if (!$usuario) {
            error_log("❌ Usuário não encontrado: $nomeAluno");
            echo json_encode(['sucesso' => false, 'mensagem' => "Usuário não encontrado"]);
            exit;
        }
        
        $usuario_id = $usuario['id'];
        error_log("✅ Usuário ID: $usuario_id");
        
        // 2️⃣ Registrar XP no JOGO 3 (que é o Jogo 4 na verdade)
        $acertou = ($xp > 0);
        $sucesso = registrarXPJogo3($pdo, $usuario_id, $fase, $acertou);
        
        if (!$sucesso) {
            error_log("❌ Erro ao registrar XP");
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao salvar XP']);
            exit;
        }
        
        // 3️⃣ Obter XP atualizado
        $xp_fase = obterXPFase3($pdo, $usuario_id, $fase);
        $xp_total = obterXPTotal3($pdo, $usuario_id);
        
        error_log("✅ XP salvo - Fase: $xp_fase, Total: $xp_total");
        
        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'XP salvo com sucesso!',
            'xp_fase' => $xp_fase,
            'xp_total' => $xp_total,
            'fase' => $fase
        ]);

    } catch (PDOException $e) {
        error_log("❌ Erro PDO: " . $e->getMessage());
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Erro no banco: ' . $e->getMessage()
        ]);
    }

} else {
    error_log("❌ Dados incompletos");
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Dados incompletos',
        'recebido' => $_POST
    ]);
}
?>