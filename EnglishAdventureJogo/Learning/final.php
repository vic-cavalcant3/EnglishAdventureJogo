<?php 
require_once("../mapa/config.php"); 
verificarLogin();

// Obter informações do usuário
$usuario_id = $_SESSION['usuario_id'] ?? $_SESSION['id'] ?? null;

// Buscar nome do usuário do banco
$stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch();
$nomeAluno = $usuario['nome'];

// ⭐ CORREÇÃO: Contar estrelas da tabela estrelas (apenas onde acertou = 1)
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total_acertos
    FROM estrelas 
    WHERE nomeAluno = ? 
    AND atividade LIKE 'fase1_%'
    AND acertou = 1
");
$stmt->execute([$nomeAluno]);
$resultado = $stmt->fetch();
$total_acertos = intval($resultado['total_acertos'] ?? 0);

// DEBUG
error_log("=== DEBUG RESULTADO.PHP ===");
error_log("Usuário: $nomeAluno (ID: $usuario_id)");
error_log("Total de acertos (acertou=1): $total_acertos");

// Ver detalhes das estrelas
$stmt = $pdo->prepare("
    SELECT atividade, acertou, total_estrelas 
    FROM estrelas 
    WHERE nomeAluno = ? 
    AND atividade LIKE 'fase1_%'
");
$stmt->execute([$nomeAluno]);
$estrelas_detalhes = $stmt->fetchAll();

error_log("📋 Detalhes das estrelas:");
foreach ($estrelas_detalhes as $estrela) {
    $status = $estrela['acertou'] ? '✅' : '❌';
    error_log("  - {$estrela['atividade']}: $status (total_estrelas: {$estrela['total_estrelas']})");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Irish+Grover&family=Itim&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="icon" type="image/png" href="img/logo.png">

  <title>English Adventure</title>

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', cursive;
    }

    html, body {
      overflow: hidden;
      width: 100%;
      height: 100%;
    }

    .fundo {
      width: 100%;
      height: 100vh;
      object-fit: cover;
      position: absolute;
      top: 0;
      left: 0;
      z-index: 0;
    }

    .quadro {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 1;
    }

    .quadrado {
      background-color: rgba(255, 240, 210, 0.9); 
      width: 90%;
      max-width: 900px;
      padding: 80px;
      border-radius: 20px;
      text-align: center;
      font-size: 25px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .quadrado p{
      font-size: 22px;
    }

    .topo {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 40px;
      background: transparent;
      color: white;
    
      z-index: 5;
    }

    .sair img {
      margin-bottom: 0px;
      width: 25px;
      height: 25px;
      display: block;
      filter: none;
      transition: transform 0.2s;
    }

    .sair img:hover {
      transform: scale(1.1);
    }

    .titulo {
      margin-right: 43%;
      font-size: 38px;
      color: #ffffffd8;
      text-align: center;
     
      letter-spacing: 2px;
      margin-top: 25px;
    }

    .botoes {
      display: flex;
      justify-content: center;
      gap: 20px;
      margin-top: 40px;
    }

    .btn.sim {
      background-color: #B9794C;
      color: #fff;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
      text-decoration: none;
      font-size: 13px;
      font-weight: 500;
      padding: 12px 50px;
      border: none;
      border-radius: 25px;
      cursor: pointer;
      transition: all 0.3s ease;
      font-family: 'Poppins', sans-serif;
      margin-top: 20px;
    }

    .btn.sim:hover {
      background-color: #b48962;
      transform: scale(1.05);
    }

    .estrelas {
      display: flex;
      justify-content: center;
      margin-bottom: 15px;
      gap: 10px;
    }

    .estrela {
      font-size: 60px;
      color: #ccc;
      transition: color 0.3s ease, transform 0.3s ease;
      filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.2));
    }

    .estrela.ativa {
      color: #FFD700;
      transform: scale(1.1);
      animation: brilho 1s ease-in-out;
    }

    @keyframes brilho {
      0%, 100% { filter: drop-shadow(0 0 5px #FFD700); }
      50% { filter: drop-shadow(0 0 20px #FFD700); }
    }
  </style>
</head>

<body>
  <img class="fundo" src="img/fundo2.png" alt="Fundo Viking">

  <header class="topo">
    <a href="../mapa/fases.php" class="sair">
      <img src="img/voltar.png" alt="Sair">
    </a>

    <h1 class="titulo">Learning</h1>
  </header>

  <section class="quadro">
    <div class="quadrado">
      <div class="estrelas">
        <?php
        // ⭐ CORREÇÃO: Loop de 1 a 3 para mostrar as estrelas
        for ($i = 1; $i <= 3; $i++) {
          $classe = ($i <= $total_acertos) ? "estrela ativa" : "estrela";
          echo "<span class='$classe'>★</span>";
        }
        ?>
      </div>

      <p>
        <?php
        if ($total_acertos === 0) {
          echo "Ops, você acabou errando as questões, vamos revisar para te deixar mais forte!";
        } elseif ($total_acertos === 1) {
          echo "Ops, você acabou errando duas, vamos dar uma revisada apenas para te deixar mais forte!";
        } elseif ($total_acertos === 2) {
          echo "Muito bem! Você errou uma questão, você quer dar uma pequena revisada?";
        } else {
          echo "Muito bem! Você acertou todas as questões! Vamos avançar para o próximo nível e começar a verdadeira luta.";
        }
        ?>
      </p>

      <div class="botoes">
        <a href="../mapa/fases.php" class="btn sim">Vamos Lá</a>
      </div>
    </div>
  </section>
</body>
</html>
