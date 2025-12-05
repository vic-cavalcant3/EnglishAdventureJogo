<?php
require_once("conexao.php");
require_once("../mapa/config.php");

header('Content-Type: text/plain; charset=utf-8');

// ⭐ DEBUG: Ver o que está chegando
error_log("📥 POST recebido no salvar_resultado: " . print_r($_POST, true));

if (isset($_POST['nomeAluno'], $_POST['atividade'], $_POST['acertou'])) {
    $nomeAluno = trim($_POST['nomeAluno']);
    $atividade = trim($_POST['atividade']);
    $acertou = intval($_POST['acertou']); // 0 ou 1
    
    echo "🔹 Recebido: nomeAluno='$nomeAluno', atividade='$atividade', acertou=$acertou\n";

    try {
        // 1️⃣ Buscar ID do usuário
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE nome = ?");
        $stmt->execute([$nomeAluno]);
        $usuario = $stmt->fetch();
        
        if (!$usuario) {
            echo "❌ ERRO: Usuário '$nomeAluno' não encontrado!\n";
            
            // Listar usuários disponíveis
            $stmt = $pdo->prepare("SELECT id, nome FROM usuarios LIMIT 5");
            $stmt->execute();
            $todos = $stmt->fetchAll();
            
            echo "📋 Usuários no banco:\n";
            foreach ($todos as $u) {
                echo "  - ID: {$u['id']}, Nome: {$u['nome']}\n";
            }
            die();
        }
        
        $usuario_id = $usuario['id'];
        echo "✅ Usuário ID: $usuario_id\n";
        
        // 2️⃣ ⭐⭐ CORREÇÃO CRÍTICA: Atualizar tabela estrelas com total_estrelas
        // Se acertou=1, total_estrelas deve ser 1, senão 0
        $total_estrelas_valor = $acertou; // acertou=1 → total_estrelas=1
        
        echo "🔹 Configurando total_estrelas para: $total_estrelas_valor\n";
        
        // Verificar se já existe registro
        $stmt = $pdo->prepare("SELECT id FROM estrelas WHERE nomeAluno = ? AND atividade = ?");
        $stmt->execute([$nomeAluno, $atividade]);
        $registro_existente = $stmt->fetch();
        
        if ($registro_existente) {
            // Atualizar registro existente
            echo "🔹 Atualizando registro existente (ID: {$registro_existente['id']})\n";
            
            $stmt = $pdo->prepare("
                UPDATE estrelas 
                SET acertou = ?, 
                    total_estrelas = ?,
                    dataRegistro = NOW()
                WHERE nomeAluno = ? AND atividade = ?
            ");
            $stmt->execute([$acertou, $total_estrelas_valor, $nomeAluno, $atividade]);
            
            echo "✅ Registro ATUALIZADO na tabela estrelas\n";
        } else {
            // Inserir novo registro
            echo "🔹 Inserindo NOVO registro\n";
            
            $stmt = $pdo->prepare("
                INSERT INTO estrelas (nomeAluno, atividade, acertou, total_estrelas, dataRegistro)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$nomeAluno, $atividade, $acertou, $total_estrelas_valor]);
            
            echo "✅ NOVO registro INSERIDO na tabela estrelas\n";
        }
        
        // 3️⃣ Verificar se foi salvo corretamente
        $stmt = $pdo->prepare("
            SELECT acertou, total_estrelas 
            FROM estrelas 
            WHERE nomeAluno = ? AND atividade = ?
        ");
        $stmt->execute([$nomeAluno, $atividade]);
        $verificar = $stmt->fetch();
        
        echo "🔹 Verificação pós-salvamento:\n";
        echo "   - acertou: {$verificar['acertou']}\n";
        echo "   - total_estrelas: {$verificar['total_estrelas']}\n";
        
        if ($verificar['acertou'] == 1 && $verificar['total_estrelas'] == 0) {
            echo "⚠️ AVISO: acertou=1 mas total_estrelas=0! Corrigindo...\n";
            
            $stmt = $pdo->prepare("
                UPDATE estrelas SET total_estrelas = 1 
                WHERE nomeAluno = ? AND atividade = ? AND acertou = 1
            ");
            $stmt->execute([$nomeAluno, $atividade]);
            echo "✅ Correção aplicada\n";
        }
        
        // 4️⃣ Atualizar tabela jogo
        echo "🔹 Atualizando tabela jogo...\n";
        
        // Primeiro, contar total de estrelas (acertou=1)
        $stmt = $pdo->prepare("
            SELECT SUM(acertou) as total_estrelas
            FROM estrelas 
            WHERE nomeAluno = ? 
            AND (atividade LIKE 'fase1_%' OR atividade LIKE 'fase1%')
        ");
        $stmt->execute([$nomeAluno]);
        $result = $stmt->fetch();
        
        $total_estrelas = $result['total_estrelas'] ?? 0;
        echo "🔹 Total de estrelas calculado: $total_estrelas\n";
        
        // Verificar se existe na tabela jogo
        $stmt = $pdo->prepare("SELECT id FROM jogo WHERE nome = ?");
        $stmt->execute([$nomeAluno]);
        $existe_jogo = $stmt->fetch();
        
        if ($existe_jogo) {
            // Atualizar
            $stmt = $pdo->prepare("UPDATE jogo SET estrelas = ? WHERE nome = ?");
            $stmt->execute([$total_estrelas, $nomeAluno]);
            echo "✅ Tabela jogo ATUALIZADA: $total_estrelas estrelas\n";
        } else {
            // Criar novo registro
            $stmt = $pdo->prepare("INSERT INTO jogo (nome, xp, estrelas, pagina_atual) VALUES (?, 0, ?, 1)");
            $stmt->execute([$nomeAluno, $total_estrelas]);
            echo "✅ NOVO registro criado na tabela jogo\n";
        }
        
        // 5️⃣ Mostrar relatório final
        echo "\n📊 RELATÓRIO FINAL:\n";
        echo "====================\n";
        echo "Usuário: $nomeAluno\n";
        echo "Atividade: $atividade\n";
        echo "Acertou: " . ($acertou ? '✅ SIM' : '❌ NÃO') . "\n";
        echo "Total estrelas Fase 1: $total_estrelas\n";
        
        // Mostrar todas as atividades da fase 1
        $stmt = $pdo->prepare("
            SELECT atividade, acertou, total_estrelas 
            FROM estrelas 
            WHERE nomeAluno = ? 
            AND (atividade LIKE 'fase1_%' OR atividade LIKE 'fase1%')
            ORDER BY atividade
        ");
        $stmt->execute([$nomeAluno]);
        $atividades = $stmt->fetchAll();
        
        echo "\n📋 Atividades da Fase 1:\n";
        foreach ($atividades as $ativ) {
            $status = $ativ['acertou'] ? '✅' : '❌';
            echo "  - {$ativ['atividade']}: $status (total_estrelas: {$ativ['total_estrelas']})\n";
        }
        
        echo "\n🎉 PROCESSO CONCLUÍDO COM SUCESSO!\n";
        
    } catch (PDOException $e) {
        echo "❌ Erro ao salvar: " . $e->getMessage() . "\n";
        error_log("❌ Erro em salvar_resultado.php: " . $e->getMessage());
    }

} else {
    echo "⚠️ Dados incompletos no POST.\n";
    echo "Dados recebidos: " . print_r($_POST, true) . "\n";
}
?>