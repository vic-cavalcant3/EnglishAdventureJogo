<?php
require_once("conexao.php");
require_once("../mapa/config.php");

header('Content-Type: application/json; charset=utf-8');

error_log("📥 salvar_xp2.php (JOGO 2) chamado com: " . json_encode($_POST));

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
        
        // 2️⃣ Registrar XP no JOGO 2
        $sucesso = registrarXPCompleto2($pdo, $usuario_id, $fase, $xp);
        
        if (!$sucesso) {
            error_log("❌ Erro ao salvar XP no Jogo 2");
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Erro ao salvar XP'
            ]);
            exit;
        }
        
        // 3️⃣ Obter XP atualizado do JOGO 2
        $xp_fase = obterXPFase2($pdo, $usuario_id, $fase);
        $xp_total = obterXPTotal2($pdo, $usuario_id);
        $xp_proximo_inicio = obterXPInicialFase2($pdo, $usuario_id, $fase + 1);
        
        // 🌟 4️⃣ CALCULAR ESTRELAS BASEADO NO XP (0-3 estrelas)
        $estrelas = calcularEstrelasPorXP($xp_fase);
        
        // 🌟 5️⃣ SALVAR ESTRELAS NA TABELA fase_estrelas2
        salvarEstrelasFase2($pdo, $usuario_id, $fase, $estrelas);
        
        error_log("✅ XP e Estrelas salvos (JOGO 2) - Usuário: $nomeAluno | Fase: $fase | XP: $xp_fase | Estrelas: $estrelas");
        
        // 6️⃣ Retornar sucesso com ESTRELAS
        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'XP e estrelas salvos com sucesso no Jogo 2!',
            'xp_fase' => $xp_fase,
            'xp_total' => $xp_total,
            'xp_proximo_inicio' => $xp_proximo_inicio,
            'estrelas' => $estrelas,
            'fase' => $fase,
            'jogo' => 2, // Identificador do jogo
            'debug' => [
                'usuario_id' => $usuario_id,
                'xp_recebido' => $xp,
                'tabela' => 'xp_jogo2'
            ]
        ]);

    } catch (PDOException $e) {
        error_log("❌ Erro no banco de dados (JOGO 2): " . $e->getMessage());
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Erro no banco de dados: ' . $e->getMessage()
        ]);
    }

} else {
    error_log("❌ Dados incompletos recebidos (JOGO 2): " . json_encode($_POST));
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Dados incompletos',
        'recebido' => $_POST
    ]);
}

// 🌟 FUNÇÃO PARA CALCULAR ESTRELAS BASEADO NO XP
function calcularEstrelasPorXP($xp_fase) {
    if ($xp_fase >= 32) return 3;      // 9-10 XP = 3 estrelas
    if ($xp_fase >= 20) return 2;      // 6-8 XP = 2 estrelas
    if ($xp_fase >= 8) return 1;      // 3-5 XP = 1 estrela
    return 0;                          // 0-2 XP = sem estrelas
}

// 🌟 FUNÇÃO PARA SALVAR ESTRELAS NA TABELA fase_estrelas2
function salvarEstrelasFase2($pdo, $usuario_id, $fase, $estrelas) {
    try {
        // Verificar se já existe registro
        $stmt = $pdo->prepare("
            SELECT id FROM fase_estrelas2 
            WHERE usuario_id = ? AND fase = ?
        ");
        $stmt->execute([$usuario_id, $fase]);
        $existe = $stmt->fetch();
        
        if ($existe) {
            // Atualizar apenas se as novas estrelas forem MAIORES
            $stmt = $pdo->prepare("
                UPDATE fase_estrelas2 
                SET estrelas = GREATEST(estrelas, ?)
                WHERE usuario_id = ? AND fase = ?
            ");
            $stmt->execute([$estrelas, $usuario_id, $fase]);
            error_log("⭐ Estrelas atualizadas (JOGO 2) - Fase $fase: $estrelas estrelas");
        } else {
            // Criar novo registro
            $stmt = $pdo->prepare("
                INSERT INTO fase_estrelas2 (usuario_id, fase, estrelas)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$usuario_id, $fase, $estrelas]);
            error_log("⭐ Estrelas criadas (JOGO 2) - Fase $fase: $estrelas estrelas");
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("❌ Erro ao salvar estrelas (JOGO 2): " . $e->getMessage());
        return false;
    }
}
?>