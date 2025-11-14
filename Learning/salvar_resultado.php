<?php
require_once("conexao.php");
require_once("../mapa/config.php");


header('Content-Type: text/plain; charset=utf-8');

if (isset($_POST['nomeAluno'], $_POST['atividade'], $_POST['acertou'])) {

    $nomeAluno = $_POST['nomeAluno'];
    $atividade = $_POST['atividade'];
    $acertou = intval($_POST['acertou']);

    echo "🔹 Recebido: nomeAluno=$nomeAluno, atividade=$atividade, acertou=$acertou\n";

    try {
        // 1️⃣ Buscar ID do usuário pelo nome
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE nome = ?");
        $stmt->execute([$nomeAluno]);
        $usuario = $stmt->fetch();
        
        if (!$usuario) {
            die("❌ Usuário não encontrado: $nomeAluno");
        }
        
        $usuario_id = $usuario['id'];
        echo "🔹 Usuario ID: $usuario_id\n";
        
        // 2️⃣ Verificar se já existe registro para esta atividade
        $stmt = $pdo->prepare("
            SELECT id FROM estrelas 
            WHERE nomeAluno = ? AND atividade = ?
        ");
        $stmt->execute([$nomeAluno, $atividade]);
        $jaExiste = $stmt->fetch();

        if ($jaExiste) {
            // Atualizar registro existente
            echo "🔹 Atualizando registro existente (ID: {$jaExiste['id']})\n";
            $stmt = $pdo->prepare("
                UPDATE estrelas 
                SET acertou = ?, dataRegistro = NOW()
                WHERE nomeAluno = ? AND atividade = ?
            ");
            $stmt->execute([$acertou, $nomeAluno, $atividade]);
        } else {
            // Inserir novo registro
            echo "🔹 Inserindo novo registro\n";
            $stmt = $pdo->prepare("
                INSERT INTO estrelas (nomeAluno, atividade, acertou, dataRegistro)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$nomeAluno, $atividade, $acertou]);
        }

        echo "✅ Registro salvo na tabela estrelas!\n";

        // 3️⃣ Verificar se usuário existe na tabela jogo
        $stmt = $pdo->prepare("SELECT id FROM jogo WHERE nome = ?");
        $stmt->execute([$nomeAluno]);
        $existeNoJogo = $stmt->fetch();
        
        if (!$existeNoJogo) {
            echo "🔹 Criando usuário na tabela jogo...\n";
            $stmt = $pdo->prepare("
                INSERT INTO jogo (nome, xp, estrelas, pagina_atual)
                VALUES (?, 0, 0, 1)
            ");
            $stmt->execute([$nomeAluno]);
            echo "✅ Usuário adicionado à tabela jogo!\n";
        }
        
        // 4️⃣ Atualizar contagem de estrelas na tabela jogo
        echo "🔹 Atualizando total de estrelas...\n";
        atualizarEstrelasJogo($pdo, $usuario_id);

        echo "\n✅ PROCESSO COMPLETO!\n";
        
    } catch (PDOException $e) {
        echo "❌ Erro ao salvar: " . $e->getMessage();
    }

} else {
    echo "⚠️ Dados incompletos.";
    var_dump($_POST);
}
?>