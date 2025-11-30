<?php
require_once("conexao.php");
require_once("config.php");

header('Content-Type: application/json; charset=utf-8');

error_log("📥 salvar_xp3.php (JOGO 3 - Espelhos de Midgard) chamado com: " . json_encode($_POST));

if (isset($_POST['nomeAluno'], $_POST['fase'], $_POST['xp'])) {

    $nomeAluno = $_POST['nomeAluno'];
    $fase = intval($_POST['fase']);
    $xp = intval($_POST['xp']);

    try {
        // 1️⃣ Buscar ID do usuário pelo nome
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE nome = ?");
        $stmt->execute([$nomeAluno]);
        $usuario = $stmt->fetch();
        
        if (!$usuario) {
            error_log("❌ Usuário não encontrado: $nomeAluno");
            echo json_encode([
                'sucesso' => false,
                'mensagem' => "Usuário não encontrado: $nomeAluno"
            ]);
            exit;
        }
        
        $usuario_id = $usuario['id'];
        
        // 2️⃣ Registrar XP no JOGO 3 (Espelhos de Midgard)
        $sucesso = registrarXPCompleto3($pdo, $usuario_id, $fase, $xp);
        
        if (!$sucesso) {
            error_log("❌ Erro ao salvar XP no Jogo 3");
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Erro ao salvar XP'
            ]);
            exit;
        }
        
        // 3️⃣ Obter XP atualizado do JOGO 3
        $xp_fase = obterXPFase3($pdo, $usuario_id, $fase);
        $xp_total = obterXPTotal3($pdo, $usuario_id);
        
        // 🌟 4️⃣ CALCULAR ESTRELAS BASEADO NO XP TOTAL (50 XP máximo)
        $estrelas = calcularEstrelasPorXPTotalJogo3($xp_total);
        
        // 🌟 5️⃣ SALVAR ESTRELAS NA TABELA fase_estrelas3
        salvarEstrelasFase3($pdo, $usuario_id, $fase, $estrelas);
        
        error_log("✅ XP e Estrelas salvos (JOGO 3) - Usuário: $nomeAluno | Fase: $fase | XP Fase: $xp_fase | XP Total: $xp_total | Estrelas: $estrelas");
        
        // 6️⃣ Retornar sucesso com ESTRELAS
        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'XP e estrelas salvos com sucesso no Jogo 3!',
            'xp_fase' => $xp_fase,
            'xp_total' => $xp_total,
            'estrelas' => $estrelas,
            'fase' => $fase,
            'jogo' => 3,
            'debug' => [
                'usuario_id' => $usuario_id,
                'xp_recebido' => $xp,
                'tabela' => 'xp_jogo3',
                'calculo_estrelas' => "XP Total: $xp_total -> Estrelas: $estrelas"
            ]
        ]);

    } catch (PDOException $e) {
        error_log("❌ Erro no banco de dados (JOGO 3): " . $e->getMessage());
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Erro no banco de dados: ' . $e->getMessage()
        ]);
    }

} else {
    error_log("❌ Dados incompletos recebidos (JOGO 3): " . json_encode($_POST));
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Dados incompletos',
        'recebido' => $_POST
    ]);
}

// 🌟 FUNÇÃO PARA CALCULAR ESTRELAS DO JOGO 3 (50 XP máximo)
function calcularEstrelasPorXPTotalJogo3($xp_total) {
    // 50 XP máximo = 100%
    if ($xp_total >= 40) return 3;      // 80%+ = 40-50 XP = 3 estrelas
    if ($xp_total >= 25) return 2;      // 50%+ = 25-39 XP = 2 estrelas  
    if ($xp_total >= 10) return 1;      // 20%+ = 10-24 XP = 1 estrela
    return 0;                            // 0-9 XP = sem estrelas
}

// 🌟 FUNÇÃO PARA SALVAR ESTRELAS NA TABELA fase_estrelas3
function salvarEstrelasFase3($pdo, $usuario_id, $fase, $estrelas) {
    try {
        // Verificar se já existe registro
        $stmt = $pdo->prepare("
            SELECT id, estrelas FROM fase_estrelas3 
            WHERE usuario_id = ? AND fase = ?
        ");
        $stmt->execute([$usuario_id, $fase]);
        $existe = $stmt->fetch();
        
        if ($existe) {
            // Atualizar apenas se as novas estrelas forem MAIORES
            $stmt = $pdo->prepare("
                UPDATE fase_estrelas3 
                SET estrelas = GREATEST(estrelas, ?), dataRegistro = NOW()
                WHERE usuario_id = ? AND fase = ?
            ");
            $stmt->execute([$estrelas, $usuario_id, $fase]);
            error_log("⭐ Estrelas atualizadas (JOGO 3) - Fase $fase: {$existe['estrelas']} -> $estrelas estrelas");
        } else {
            // Criar novo registro
            $stmt = $pdo->prepare("
                INSERT INTO fase_estrelas3 (usuario_id, fase, estrelas, dataRegistro)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$usuario_id, $fase, $estrelas]);
            error_log("⭐ Estrelas criadas (JOGO 3) - Fase $fase: $estrelas estrelas");
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("❌ Erro ao salvar estrelas (JOGO 3): " . $e->getMessage());
        return false;
    }
}
?>