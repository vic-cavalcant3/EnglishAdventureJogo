<?php
/**
 * CONTROLE DE REPLAY DE FASES
 */

function podeRefazerFase($pdo, $usuario_id, $fase, $jogo_numero) {
    // ✅ VALIDAÇÕES INICIAIS
    if (!$usuario_id || !$fase) {
        return [
            'pode_refazer' => true, 
            'motivo' => 'Primeira tentativa',
            'estrelas_atuais' => 0,
            'tentativas_restantes' => 3
        ];
    }
    
    try {
        // 📊 BUSCAR ESTRELAS ATUAIS
        $estrelas_atuais = obterEstrelasFase3($pdo, $usuario_id, $fase);
        
        // 📅 CONTAR TENTATIVAS HOJE
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as tentativas_hoje 
            FROM progresso_detalhado 
            WHERE usuario_id = ? 
            AND fase = ? 
            AND atividade LIKE 'jogo4_fase%'
            AND DATE(dataUltimaAtualizacao) = CURDATE()
        ");
        $stmt->execute([$usuario_id, $fase]);
        $result = $stmt->fetch();
        $tentativas_hoje = $result['tentativas_hoje'] ?? 0;
        
        // 🎯 REGRAS DE REPLAY
        if ($estrelas_atuais >= 3) {
            return [
                'pode_refazer' => false, 
                'motivo' => '🎉 Você já tem 3 estrelas! Fase dominada!',
                'estrelas_atuais' => $estrelas_atuais,
                'tentativas_restantes' => 0
            ];
        }
        
        if ($tentativas_hoje >= 3) {
            return [
                'pode_refazer' => false, 
                'motivo' => '📊 Você já usou todas as 3 tentativas de hoje!',
                'estrelas_atuais' => $estrelas_atuais,
                'tentativas_restantes' => 0
            ];
        }
        
        $tentativas_restantes = 3 - $tentativas_hoje;
        return [
            'pode_refazer' => true, 
            'tentativas_restantes' => $tentativas_restantes,
            'estrelas_atuais' => $estrelas_atuais,
            'motivo' => "Pode jogar! Tentativas restantes: $tentativas_restantes"
        ];
        
    } catch (PDOException $e) {
        error_log("❌ Erro no replay_controller: " . $e->getMessage());
        return [
            'pode_refazer' => true,
            'motivo' => 'Sistema temporariamente indisponível',
            'estrelas_atuais' => 0,
            'tentativas_restantes' => 3
        ];
    }
}

function verificarRedirecionamentoReplay($pdo, $usuario_id, $fase, $jogo_numero, $xp_atual) {
    $info = podeRefazerFase($pdo, $usuario_id, $fase, $jogo_numero);
    
    // Se não pode refazer E já tem algum XP (já jogou antes)
    if (!$info['pode_refazer'] && $xp_atual > 0) {
        $_SESSION['mensagem_bloqueio'] = $info['motivo'];
        header('Location: ../mapa/mapa.php');
        exit;
    }
    
    return $info;
}
?>