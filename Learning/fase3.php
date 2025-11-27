<?php 
require_once("conexao.php"); 

// ✅ ADICIONAR: Buscar nome do usuário logado
session_start();
$usuario_id = $_SESSION['usuario_id'] ?? $_SESSION['id'] ?? null;

if (!$usuario_id) {
    die("Erro: Usuário não está logado!");
}


// Buscar nome do usuário do banco
$stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch();
$nomeAluno = $usuario['nome'] ?? 'Desconhecido';
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
      height: 450px;
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
    .linha-drop {
      width: 80%;
      min-height: 60px;
      background-color: transparent;
      margin: 40px auto;
      border-bottom: 3px solid #865f3b;
      border-radius: 3px;
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 15px;
      flex-wrap: wrap;
    }
    .botoesPalavras {
      display: flex;
      justify-content: center;
      gap: 20px;
      flex-wrap: wrap;
      margin-top: 30px;
    }
    .palavra {
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: #B9794C;
      width: 13%;
      height: 40px;
      color: white;
      padding: 12px 25px;
      border-radius: 25px;
      cursor: grab;
      font-weight: 100;
      font-size: 14px;
      transition: transform 0.2s, background-color 0.2s;
      box-shadow: 0 3px 5px rgba(0,0,0,0.2);
    }
    .palavra:hover {
      transform: scale(1.05);
      background-color: #b48962;
    }
    .palavra:active {
      cursor: grabbing;
    }
    .linha-drop.over {
      background-color: rgba(134, 95, 59, 0.3);
      border: 2px dashed #865f3b;
    }
    .btn.sim {
      bottom: 180px;
      position: absolute;
      right: 690px;
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
      margin-top: 40px;
    }
    .btn.sim:hover {
      background-color: #b48962;
      transform: scale(1.05);
    }
    .quadrado h2{
      font-weight: 100;
      font-size: 22px;
      color: #865f3b;
    }
  </style>
</head>
<body>
  <img class="fundo" src="img/fundo2.png" alt="">
  <header class="topo">
    <a href="fase2.php" class="sair">
      <img src="img/voltar.png" alt="Sair">
    </a>
    <h1 class="titulo">Learning</h1>
  </header>
  <section class="quadro">
    <div class="quadrado">
      <h2>Coloque a frase na ordem correta</h2>
      <div class="linha-drop" id="linha"></div>
      <div class="botoesPalavras">
        <div class="palavra" draggable="true">student</div>
        <div class="palavra" draggable="true">I</div>
        <div class="palavra" draggable="true">a</div>
        <div class="palavra" draggable="true">am</div>
      </div>
      <a href="fase4.php" class="btn sim">Próximo</a>
    </div>
  </section>

  <script>
    // ✅ Passar o nome do usuário do PHP para o JavaScript
    const nomeAluno = "<?php echo $nomeAluno; ?>";
    
    const palavras = document.querySelectorAll(".palavra");
    const linha = document.querySelector(".linha-drop");
    const containerBotoes = document.querySelector(".botoesPalavras");
    const btnProximo = document.querySelector(".btn.sim");
    const quadrado = document.querySelector(".quadrado");

    let verificado = false;
    let acertou = false;

    palavras.forEach(palavra => {
      palavra.addEventListener("dragstart", () => {
        if (verificado) return;
        palavra.classList.add("arrastando");
      });
      palavra.addEventListener("dragend", () => {
        palavra.classList.remove("arrastando");
      });
    });

    document.querySelectorAll(".linha-drop, .botoesPalavras").forEach(area => {
      area.addEventListener("dragover", e => {
        if (verificado) return;
        e.preventDefault();
        area.classList.add("over");
      });
      area.addEventListener("dragleave", () => {
        area.classList.remove("over");
      });
      area.addEventListener("drop", e => {
        if (verificado) return;
        e.preventDefault();
        area.classList.remove("over");
        const palavra = document.querySelector(".arrastando");
        area.appendChild(palavra);
      });
    });

    btnProximo.addEventListener("click", e => {
      e.preventDefault();

      if (verificado) {
        window.location.href = "fase4.php";
        return;
      }

      const palavrasNaLinha = Array.from(linha.children).map(p => p.textContent.trim());
      const fraseCerta = ["I", "am", "a", "student"];

      if (palavrasNaLinha.length < fraseCerta.length) {
        alert("Coloque todas as palavras na linha antes de confirmar!");
        return;
      }

      verificado = true;

      const msgExistente = document.querySelector(".mensagem-feedback");
      if (msgExistente) msgExistente.remove();

      const msg = document.createElement("p");
      msg.classList.add("mensagem-feedback");
      msg.style.marginTop = "20px";
      msg.style.fontSize = "22px";
      msg.style.fontWeight = "600";

      if (JSON.stringify(palavrasNaLinha) === JSON.stringify(fraseCerta)) {
        quadrado.style.border = "solid 8px #2e7d32";
        msg.innerHTML = "Parabéns! Você acertou.";
        msg.style.color = "#2e7d32";
        acertou = true;

        let acertos = parseInt(localStorage.getItem("acertos")) || 0;
        localStorage.setItem("acertos", acertos + 1);
        salvarResultado(1, 1);

      } else {
        quadrado.style.border = "solid 8px #b71c1c";
        msg.innerHTML = "Você errou! A frase correta é: <strong>I am a student!</strong>";
        msg.style.color = "#b71c1c";
        salvarResultado(1, 0);
      }

      linha.insertAdjacentElement("afterend", msg);
      palavras.forEach(p => p.setAttribute("draggable", "false"));
      btnProximo.textContent = "Avançar ➜";
    });

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