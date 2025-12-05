<?php 
require_once("../mapa/config.php"); 
verificarLogin();

// Obter informações do usuário
$usuario_id = $_SESSION['usuario_id'];

// Buscar nome do usuário do banco
$stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch();
$nomeAluno = $usuario['nome'];

// DEBUG: Verificar o que está no banco
echo "<!-- DEBUG: Nome do usuário: $nomeAluno -->";

// Buscar todas as estrelas da fase 1 diretamente
$stmt = $pdo->prepare("
    SELECT atividade, acertou 
    FROM estrelas 
    WHERE nomeAluno = ? 
    AND atividade LIKE 'fase1_%'
");
$stmt->execute([$nomeAluno]);
$estrelas_fase1 = $stmt->fetchAll();

echo "<!-- DEBUG: Estrelas fase 1: " . print_r($estrelas_fase1, true) . " -->";

// Contar manualmente as estrelas (acertou = 1)
$total_acertos = 0;
foreach ($estrelas_fase1 as $estrela) {
    if ($estrela['acertou'] == 1) {
        $total_acertos++;
    }
}

echo "<!-- DEBUG: Total acertos calculado: $total_acertos -->";

// Também usar a função do config.php para comparar
$total_funcao = contarEstrelasFase($pdo, $usuario_id, 1);
echo "<!-- DEBUG: Total pela função: $total_funcao -->";
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

    .boneca {
      position: absolute;
      bottom: 120px;
      left: 380px;
      width: 150px;
      z-index: 2;
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
      font-family: 'Irish Grover', cursive;
      z-index: 5;
    }

    .sair img {
      margin-bottom: 0px;
      width: 150%;
      height: 150%;
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
      font-family: 'Irish Grover';
      letter-spacing: 2px;
      margin-top: 25px;
    }

    .perfil {
      display: flex;
      align-items: center;
      background: #865f3b;
      padding: 5px 15px;
      border-radius: 30px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.3);
      margin-top: 25px;
    }

    .perfil img {
      width: 35px;
      height: 35px;
      border-radius: 50%;
      margin-right: 10px;
    }

    .perfil span {
      color: #fff;
      font-weight: 500;
      font-size: 14px;
    }

    .botoes {
      display: flex;
      justify-content: center;
      gap: 20px;
      margin-top: 40px;
    }

    .btn {
      font-size: 18px;
      font-weight: 500;
      padding: 12px 35px;
      border: none;
      border-radius: 25px;
      cursor: pointer;
      transition: all 0.3s ease;
      font-family: 'Poppins', sans-serif;
      text-decoration: none;
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
    }

    .estrela {
      font-size: 40px;
      color: #ccc;
      margin: 0 5px;
      transition: color 0.3s ease, transform 0.3s ease;
    }

    .estrela.ativa {
      color: #FFD700;
      transform: scale(1.1);
    }

    /* Estilo para debug (aparece no topo da página) */
    .debug-info {
      position: fixed;
      top: 10px;
      left: 10px;
      background: rgba(0,0,0,0.8);
      color: white;
      padding: 10px;
      border-radius: 5px;
      font-size: 12px;
      z-index: 1000;
      display: none; /* Mude para 'block' se quiser ver */
    }
  </style>
</head>

<body>
  <!-- Debug info - mude display: none para display: block para ver -->
  <div class="debug-info">
    DEBUG:<br>
    Usuário: <?php echo $nomeAluno; ?><br>
    Total Acertos: <?php echo $total_acertos; ?><br>
    Total Função: <?php echo $total_funcao; ?>
  </div>

  <img class="fundo" src="img/fundo2.png" alt="Fundo Viking">

  <header class="topo">
    <a href="fase5.php" class="sair">
      <img src="img/voltar.png" alt="Sair">
    </a>

    <h1 class="titulo">Learning</h1>
  </header>

  <section class="quadro">
    <div class="quadrado">
      <div class="estrelas">
        <?php
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

  <script>
    // Para ver o debug no console também
    console.log("DEBUG - Total de acertos: <?php echo $total_acertos; ?>");
    console.log("DEBUG - Total pela função: <?php echo $total_funcao; ?>");
  </script>
</body>
</html>