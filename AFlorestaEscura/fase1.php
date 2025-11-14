<?php
session_start();

// Configuração do banco de dados
$host = 'localhost';
$dbname = 'englishadventure';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    
  <title>English Adventure</title>

    <link href="https://fonts.googleapis.com/css2?family=Irish+Grover&family=Itim&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="icon" type="image/png" href="imgs/logo.png">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background-image: url("imgs/floresta.png");
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
            height: 100vh;
            width: 100vw;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        .topo {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 70px;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 0 40px;
            background: transparent;
            font-family: 'Irish Grover', sans-serif;
            color: white;
            z-index: 10;
        }

        .titulo {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            font-size: 28px;
            color: #ffffffd8;
            letter-spacing: 2px;
            font-family: 'Irish Grover', sans-serif;
            font-weight: 500;
        }

        .sair {
            position: absolute;
            right: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .sair img {
            width: 28px;
            height: 28px;
            transition: 0.2s;
        }

        .sair img:hover {
            transform: scale(1.1);
        }

        .quiz-container {
            width: 600px;
            height: 400px;
            background: rgba(255, 255, 255, 0.65);
            padding: 30px;
            border-radius: 25px;
            backdrop-filter: blur(6px);
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.2);
            text-align: center;
            margin-left: 48%;
            z-index: 5;
        }

        .title {
            font-size: 25px;
            margin-bottom: 20px;
            padding: 10px;
        }

        .play-btn {
            background: white;
            border: none;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            font-size: 26px;
            cursor: pointer;
            margin-bottom: 25px;
            transition: 0.2s;
        }

        .play-btn:hover {
            transform: scale(1.08);
            background: #f0f0f0;
        }

        .options {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .option {
            border: none;
            width: 500px;
            align-self: center;
            padding: 14px;
            border-radius: 30px;
            background: #3e2d22;
            color: white;
            cursor: pointer;
            font-size: 15px;
            transition: 0.2s;
        }

        .option:hover {
            transform: scale(1.03);
        }

        .correct {
            background: #2e7d32 !important;
        }

        .wrong {
            background: #9c2121 !important;
        }

        /* ÍCONES DEFINIDOS PARA 54px - LINHA DUPLICADA REMOVIDA */
        .vocabulario-btn img,
        .pocao-btn img {
            width: 30px;
            height: 36px;
        }

        .vocabulario-btn img {
            margin-left: 70%;
        }

        .pocao-btn img {
            margin-right: 60%;
        }

        #feedback {
            margin-top: 15px;
            font-size: 18px;
            font-weight: bold;
        }

        .bottom-left {
            position: absolute;
            left: 0px;
            bottom: 60px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .pocao {
            position: absolute;
            bottom: 60px;
            right: 0px;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 5px;
        }

        .vocabulario-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background-color: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 0 39px 39px 0;
            padding: 20px 80px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.3);
            transition: 0.2s;
            font-size: 16px;
        }

        .pocao-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background-color: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 39px 0 0 39px;
            padding: 20px 50px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.3);
            transition: 0.2s;
            font-size: 16px;
        }

        .vocabulario-btn:hover,
        .pocao-btn:hover {
            transform: scale(1.05);
        }

        .help {
            color: white;
            font-size: 14px;
            font-weight: 500;
            margin-right: 30px;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.816);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .overlay.show {
            display: flex;
        }

        .overlay-content {
            max-width: 90%;
            max-height: 90%;
            animation: overlayAppear 0.3s ease-out;
        }

        .overlay-content img {
            max-width: 100%;
            max-height: 90vh;
            object-fit: contain;
        }

        .disabled {
            pointer-events: none;
            opacity: 1;
        }

        .option.disabled:hover {
            transform: none;
            background: inherit;
        }

        .xp-container {
            position: absolute;
            left: 20px;
            bottom: 140px;
            width: 200px;
            height: 40px;
            z-index: 10;
        }

        .title1 {
            font-size: 20px;
            padding: 10px;
        }

        .xp-bar {
            width: 100%;
            height: 18px;
            background: #f4e3c7;
            border-radius: 10px;
            overflow: hidden;
        }

        .xp-fill {
            height: 100%;
            width: 0%;
            background: #4a2b17;
            transition: width 0.4s ease;
        }

        .dragon {
            position: absolute;
            width: 50px;
            top: -40px;
            left: 0;
            padding-left: 10px;
            transition: left 0.4s ease;
        }

        @keyframes overlayAppear {
            from {
                opacity: 0;
                transform: scale(0.8);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }
    </style>
</head>

<body>
    <header class="topo">
        <h1 class="titulo">A floresta escura de Huldra</h1>
        <a href="#" class="sair">
            <img src="imgs/sair.png" alt="Sair">
        </a>
    </header>
    <div class="quiz-container">
        <p class="title1">Responda</p>
        <p class="title">Are you my best friend?</p>
        <div class="options">
            <button class="option" data-answer="wrong">A) He is</button>
            <button class="option" data-answer="wrong">B) I am</button>
            <button class="option" data-answer="correct">C) You are</button>
        </div>
        <p id="feedback"></p>
    </div>
    <div class="xp-container">
        <img src="imgs/dragao.png" class="dragon" id="dragon">
        <div class="xp-bar">
            <div class="xp-fill" id="xp-fill"></div>
        </div>
    </div>
    <div class="bottom-left">
        <button class="vocabulario-btn">
            <span>Dicionário</span>
            <img src="imgs/dicionario.png" alt="Vocabulário" />
        </button>
    </div>
    <div class="pocao">
        <span class="help">Precisa de ajuda?</span>
        <button class="pocao-btn">
            <img src="imgs/poção.png" alt="Poção" />
            <span class="btn-text">POÇÃO</span>
        </button>
    </div>
    <div class="overlay" id="overlay">
        <div class="overlay-content">
            <img src="images/cartas/inicio.png" alt="Troll" />
        </div>
        <button onclick="document.getElementById('overlay').classList.remove('show')">Fechar</button>
    </div>
    <script>
        const options = document.querySelectorAll(".option");
        const feedback = document.getElementById("feedback");
        const phasePoints = { 1: { correct: 1, wrong: -1 }, 2: { correct: 4, wrong: -4 }, 3: { correct: 5, wrong: -5 } };
        let xp = parseInt(localStorage.getItem("xp")) || 0;
        const maxXP = 100;
        updateXPBar();
        function updateXPBar() {
            const fill = document.getElementById("xp-fill");
            const dragon = document.getElementById("dragon");
            let percent = Math.min((xp / maxXP) * 100, 100);
            fill.style.width = percent + "%";
            dragon.style.left = `calc(${percent}% - 20px)`;
        }
        function giveXP(phase, isCorrect) {
            const points = phasePoints[phase];
            if (isCorrect) { xp += points.correct; } else { xp += points.wrong; }
            if (xp < 0) xp = 0;
            localStorage.setItem("xp", xp);
            updateXPBar();
        }
        options.forEach(btn => {
            btn.addEventListener("click", () => {
                if (feedback.textContent !== "") return;
                options.forEach(o => { o.classList.add("disabled"); });
                const isCorrect = btn.dataset.answer === "correct";
                if (isCorrect) {
                    btn.classList.add("correct");
                    feedback.textContent = "✔ Resposta correta!";
                    feedback.style.color = "#2e7d32";
                    giveXP(1, true);
                } else {
                    btn.classList.add("wrong");
                    const correctOption = document.querySelector('.option[data-answer="correct"]');
                    correctOption.classList.add("correct");
                    feedback.textContent = "✖ Resposta errada!";
                    feedback.style.color = "#b71c1c";
                    giveXP(1, false);
                }
                setTimeout(() => {
                    window.location.href = "parte3.php";
                }, 3000);
            });
        });
    </script>
</body>

</html>