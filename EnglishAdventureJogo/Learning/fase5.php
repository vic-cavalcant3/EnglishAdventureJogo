<?php 
require_once("conexao.php"); 
require_once("../mapa/config.php");

// ⭐ INICIAR SESSÃO COM VERIFICAÇÃO
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ⭐ VERIFICAR LOGIN (usa função do config.php)
verificarLogin();

// ⭐ OBTER USUÁRIO ID (compatibilidade)
$usuario_id = $_SESSION['usuario_id'] ?? $_SESSION['id'] ?? null;

if (!$usuario_id) {
    error_log("❌ ERRO CRÍTICO: Usuário não está logado! Redirecionando...");
    header('Location: ../auth/login.php');
    exit;
}

// ⭐ BUSCAR DADOS DO USUÁRIO COM FALLBACK
$nomeAluno = 'Desconhecido';

$stmt = $pdo->prepare("SELECT nome, email FROM usuarios WHERE id = ?");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch();

if ($usuario) {
    $nomeAluno = $usuario['nome'];
} else {
    // Tentar recuperar da sessão
    $nomeAluno = $_SESSION['nome'] ?? $_SESSION['usuario_nome'] ?? 'Desconhecido';
    error_log("⚠️ Usuário ID $usuario_id não encontrado no banco. Usando nome da sessão: $nomeAluno");
}

// ⭐ VERIFICAR CRISTAL DE REPLAY
$tem_cristal = verificarCristal($pdo, $usuario_id);

// ⭐ VERIFICAR SE É PRIMEIRA VEZ COMPLETANDO O JOGO 1
$jogo1_completo = false;
try {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_fases 
        FROM fases_jogadas 
        WHERE usuario_id = ? AND jogo = 1
    ");
    $stmt->execute([$usuario_id]);
    $result = $stmt->fetch();
    
    // Se já jogou todas as 5 fases do Jogo 1
    if (($result['total_fases'] ?? 0) >= 5) {
        $jogo1_completo = true;
    }
} catch (Exception $e) {
    error_log("❌ Erro ao verificar progresso do Jogo 1: " . $e->getMessage());
}

// ⭐ REGISTRAR QUE ESTÁ JOGANDO ESTA FASE (mesmo se não puder, para tracking)
registrarFaseJogada($pdo, $usuario_id, 1, 5);

// ⭐ LOG PARA DEBUG
error_log("🔍 fase5.php - Usuario ID: $usuario_id, Nome: $nomeAluno, Pode jogar: " . ($pode_jogar ? 'Sim' : 'Não') . ", Jogo 1 Completo: " . ($jogo1_completo ? 'Sim' : 'Não'));
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
    a {
      display: inline-block;
      padding-top: 30px;
      color: #865f3b;
    }
    a:hover {
      color: #b48962;
      transform: scale(0.9);
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
      height: 70px;
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
      margin-bottom: 10px;

  width: 25px;        /* ⭐ Tamanho fixo */
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
    .quadrado {
      background-color: rgba(255, 240, 210, 0.9);
      width: 90%;
      max-width: 750px;
      padding: 60px;
      border-radius: 25px;
      text-align: center;
      font-size: 24px;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
    }
    .quadrado h2{
      font-weight: 100;
      font-size: 18px;
      color: #865f3b;
      margin-bottom: 20px;
    }
    .frase {
      font-family: 'Poppins', sans-serif;
      font-size: 26px;  
      font-weight: 500;
      margin-bottom: 45px;
      color: #333;
    }
    .botoes-verificacao {
      display: flex;
      justify-content: center;
      gap: 40px;
      margin-top: 40px;
    }
    .btn-opcao {
      background-color: transparent;
      color:#865f3b;
      border: #865f3b solid;
      padding: 12px 30px;
      border-radius: 30px;
      font-size: 14px;
      cursor: pointer;
      font-weight: 500;
      transition: transform 0.2s, background-color 0.2s, opacity 0.2s;
    }
    .btn-opcao:hover {
      transform: scale(1.05);
      background-color: #dab38f;
    }
    .mensagem {
      font-weight: 600;
      font-size: 20px;
      margin-top: 40px;
      transition: opacity 0.3s ease;
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
      margin-top: 30px;
    }
    .btn.sim:hover {
      background-color: #b48962;
      transform: scale(1.05);
    }
  </style>
</head>
<body>



  <img class="fundo" src="img/fundo2.png" alt="">
  <header class="topo">
    <a href="../mapa/fases.php" class="sair">
      <img src="img/voltar.png" alt="Sair">
    </a>
    <h1 class="titulo">Learning</h1>
  </header>
  <section class="quadro">
    <div class="quadrado">
      <h2>Marque a opção correta</h2>
      <p class="frase">O dragão é forte</p>
      <div class="botoes-verificacao">
        <button class="btn-opcao" data-resposta="correta">A) the dragon is strong</button>
        <button class="btn-opcao" data-resposta="errada">B) the dragon isn't strong</button>
      </div>
      <p class="mensagem"></p>
      <a href="final.php" class="btn sim">Próximo</a>
    </div>
  </section>

  <!-- Áudios de feedback -->
  <audio id="somAcerto" src="sounds/acerto.mp4" preload="auto"></audio>
  <audio id="somErro" src="sounds/erro.mp4" preload="auto"></audio>

  <script>
    // ✅ Passar o nome do usuário do PHP para o JavaScript
    const nomeAluno = "<?php echo $nomeAluno; ?>";
    
    const botoes = document.querySelectorAll('.btn-opcao');
    const msg = document.querySelector('.mensagem');

    // Sons
    const somAcerto = document.getElementById("somAcerto");
    const somErro = document.getElementById("somErro");

    let respondido = false;

    function verificar(respostaClicada) {
      if (respondido) return;
      respondido = true;

      botoes.forEach(btn => {
        btn.style.pointerEvents = 'none';
        btn.style.transition = 'all 0.3s ease';
      });

      let acertou = 0;

      if (respostaClicada.dataset.resposta === 'correta') {
        respostaClicada.style.backgroundColor = '#4CAF50';
        respostaClicada.style.color = '#fff';
        respostaClicada.style.border = 'none';
        respostaClicada.style.transform = 'scale(1.08) translateY(-3px)';
        respostaClicada.style.boxShadow = '0 4px 10px rgba(0,0,0,0.2)';
        msg.textContent = 'Muito bem! Você acertou!';
        msg.style.color = '#1b5e20';
        somAcerto.play(); // ⭐ Toca som de acerto

        botoes.forEach(btn => {
          if (btn !== respostaClicada) {
            btn.style.opacity = '0.4';
            btn.style.transform = 'scale(0.95) translateY(3px)';
            btn.style.boxShadow = 'inset 0 2px 6px rgba(0,0,0,0.2)';
          }
        });

        let acertos = parseInt(localStorage.getItem("acertos")) || 0;
        localStorage.setItem("acertos", acertos + 1);
        acertou = 1;

      } else {
        respostaClicada.style.backgroundColor = '#E53935';
        respostaClicada.style.color = '#fff';
        respostaClicada.style.border = 'none';
        respostaClicada.style.opacity = '0.5';
        respostaClicada.style.transform = 'scale(0.95) translateY(3px)';
        msg.textContent = 'Não foi dessa vez...';
        msg.style.color = '#b71c1c';
        somErro.play(); // ⭐ Toca som de erro

        const correta = document.querySelector('[data-resposta="correta"]');
        correta.style.backgroundColor = '#4CAF50';
        correta.style.color = '#fff';
        correta.style.border = 'none';
        correta.style.transform = 'scale(1.08) translateY(-3px)';
        correta.style.boxShadow = '0 4px 10px rgba(0,0,0,0.2)';
        acertou = 0;
      }

      salvarResultado(3, acertou);
    }

    botoes.forEach(btn => btn.addEventListener('click', () => verificar(btn)));

    function salvarResultado(atividadeNumero, acertou) {
      console.log(`🔹 Salvando fase1_atividade${atividadeNumero} para ${nomeAluno}...`, acertou);

      fetch("salvar_resultado.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `nomeAluno=${encodeURIComponent(nomeAluno)}&atividade=fase1_atividade${atividadeNumero}&acertou=${acertou}`
      })
      .then(r => r.text())
      .then(t => console.log("🔸 Resposta do PHP:", t))
      .catch(e => console.error("🔻 Erro ao salvar:", e));
    }
  </script>
</body>
</html>