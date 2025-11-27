<?php
require_once("conexao.php");
require_once("config.php");

header('Content-Type: application/json; charset=utf-8');

if (isset($_POST['nomeAluno'], $_POST['fase'], $_POST['estrelas'])) {
    
    $nomeAluno = $_POST['nomeAluno'];
    $fase = intval($_POST['fase']);
    $estrelas = intval($_POST['estrelas']);

    error_log("⭐ Recebendo estrelas - Nome: $nomeAluno, Fase: $fase, Estrelas: $estrelas");

    try {
        // Buscar ID do usuário
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE nome = ?");
        $stmt->execute([$nomeAluno]);
        $usuario = $stmt->fetch();
        
        if (!$usuario) {
            error_log("❌ Usuário não encontrado: $nomeAluno");
            echo json_encode(['sucesso' => false, 'mensagem' => "Usuário não encontrado"]);
            exit;
        }
        
        $usuario_id = $usuario['id'];
        
        // Verificar se a tabela fase_estrelas existe
        $table_exists = $pdo->query("SHOW TABLES LIKE 'fase_estrelas'")->fetch();
        if (!$table_exists) {
            error_log("❌ Tabela fase_estrelas não existe");
            echo json_encode(['sucesso' => false, 'mensagem' => 'Tabela não configurada']);
            exit;
        }
        
        // Salvar/atualizar estrelas da fase
        $stmt = $pdo->prepare("
            INSERT INTO fase_estrelas (usuario_id, fase, estrelas) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE 
            estrelas = VALUES(estrelas),
            data_atualizacao = NOW()
        ");
        
        $sucesso = $stmt->execute([$usuario_id, $fase, $estrelas]);
        
        if ($sucesso) {
            error_log("✅ Estrelas salvas com sucesso - Usuário ID: $usuario_id, Fase: $fase, Estrelas: $estrelas");
            
            echo json_encode([
                'sucesso' => true,
                'mensagem' => 'Estrelas da fase salvas!',
                'estrelas' => $estrelas,
                'fase' => $fase
            ]);
        } else {
            error_log("❌ Erro ao executar query de estrelas");
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao salvar estrelas']);
        }

    } catch (PDOException $e) {
        error_log("❌ Erro PDO ao salvar estrelas: " . $e->getMessage());
        echo json_encode(['sucesso' => false, 'mensagem' => 'Erro: ' . $e->getMessage()]);
    }

} else {
    error_log("❌ Dados incompletos para salvar estrelas");
    echo json_encode(['sucesso' => false, 'mensagem' => 'Dados incompletos']);
}
?>