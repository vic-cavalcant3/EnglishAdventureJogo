<?php
// Iniciar sessão e verificar login
require_once '../mapa/config.php';
verificarLogin();

// Pegar nome do usuário da sessão
$nomeAluno = $_SESSION['nome'] ?? $_SESSION['usuario_nome'] ?? 'Visitante';
$usuario_id = $_SESSION['id'] ?? $_SESSION['usuario_id'] ?? 0;

// Definir qual fase é esta
$numero_fase = 2; // FASE 1

// ⭐ DEFINIR TIPO DA QUESTÃO
$tipo_gramatica = 'afirmativa'; // 'afirmativa', 'interrogativa' ou 'negativa'
$tipo_habilidade = 'choice'; // 'speaking', 'reading', 'listening' ou 'writing'
$nome_atividade = 'jogo2_fase2'; // Identificador único


// Buscar XP atual desta fase do banco
$xp_atual_fase = obterXPFase($pdo, $usuario_id, $numero_fase);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://fonts.googleapis.com/css2?family=Irish+Grover&family=Itim&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="icon" type="image/png" href="imgs/logo.png">
  <title>English Adventure</title>


    <style>
        * {
            margin: 0; padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }
        body {
            background-image: url("imgs/fundo.png");
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
            height: 100vh; width: 100vw;
            overflow: hidden;
            display: block; 
        }
        .topo {
            position: absolute; top: 0; left: 0;
            width: 100%; height: 70px;
            display: flex; justify-content: center; align-items: center;
            padding: 0 40px; background: transparent;
            font-family: "Irish Grover", sans-serif;
            color: white; z-index: 10;
        }
        .titulo {
            position: absolute; left: 50%;
            transform: translateX(-50%);
            font-size: 28px; color: #ffffffd8;
            letter-spacing: 2px;
            font-family: "Irish Grover", sans-serif;
        }
        .sair {
            position: absolute; right: 40px;
            display: flex; align-items: center; justify-content: center;
            text-decoration: none;
        }
        .sair img {
            width: 28px; height: 28px;
            transition: 0.2s;
        }
        .sair img:hover { transform: scale(1.1); }

        .main-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 5;
            position: absolute; 
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%); 
        }

        .question-container {
            width: 650px;
            background: rgba(255,255,255,0.70);
            padding: 40px;
            border-radius: 25px;
            backdrop-filter: blur(6px);
            box-shadow: 0 4px 25px rgba(0,0,0,0.25);
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 5;
            transition: padding 0.3s ease;
        }
        
        .question-container.answered {
            padding-top: 25px; 
            padding-bottom: 25px; 
        }

        .instruction {
            font-size: 18px;
            color: #444;
            margin-bottom: 10px;
        }

        .question-phrase {
            font-size: 28px;
            font-weight: 600;
            color: #222;
            margin-bottom: 35px;
        }

        .options {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .option-btn {
            width: 100%;
            padding: 18px;
            border: none;
            border-radius: 40px;
            background: #B9794C;
            color: white;
            font-size: 18px;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .option-btn:hover {
            transform: scale(1.03);
            background: #94613c;
        }

        .option-btn.selected-correct {
            background: #4caf50; 
        }

        .option-btn.selected-correct:hover {
            background: #388e3c; 
            transform: scale(1.03);
        }

        .option-btn.selected-wrong {
            background: #f44336; 
        }

        .option-btn.selected-wrong:hover {
            background: #d32f2f; 
            transform: scale(1.03);
        }

        .option-btn.show-correct {
            background: #81c784; 
            border: 3px solid #388e3c; 
        }

        .option-btn.show-correct:hover {
            background: #81c784; 
        }

        .correct {
            background: #B9794C;
        }
        .correct:hover {
            background: #94613c;
        }
        
        #next-phase-btn-outside {
            width: 280px; 
            background: #B9794C; 
            padding: 10px; 
            border-radius: 40px; 
            margin-top: 25px; 
            display: none; 
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s ease;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        #next-phase-btn-outside:hover {
            background: #a36b41; 
            transform: scale(1.03); 
        }

        #feedback {
            margin-top: 15px;
            font-size: 20px; font-weight: bold;
        }

        .bottom-left {
            position: absolute; left: 0; bottom: 60px;
            display: flex; align-items: center; gap: 10px;
        }

        .pocao {
            position: absolute; bottom: 60px; right: 0;
            display: flex; flex-direction: column;
            align-items: flex-end; gap: 5px;
        }

        .vocabulario-btn, .pocao-btn {
            display: flex; align-items: center; justify-content: center;
            gap: 8px;
            background: rgba(255,255,255,0.95);
            border: none;
            cursor: pointer;
            box-shadow: 0 3px 6px rgba(0,0,0,0.3);
            transition: 0.2s; font-size: 16px; font-weight: 600;
            padding: 20px 50px;
        }
        .vocabulario-btn {
            border-radius: 0 39px 39px 0; padding: 20px 80px;
        }
        .pocao-btn {
            border-radius: 39px 0 0 39px;
        }
        .vocabulario-btn:hover, .pocao-btn:hover { transform: scale(1.05); }

        .help {
            color: white; font-size: 14px; font-weight: 500; margin-right: 30px;
        }

        /* XP BAR */
        .xp-container {
            position: absolute; left: 20px; bottom: 150px;
            width: 200px; height: 60px; z-index: 10;
        }
        
        .xp-info {
            position: relative;
            top: 20px;
            width: 90px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-left: 5px;
            margin-top: 5px;
            color: white;
            font-size: 12px;
            font-weight: 200;
            opacity: 0.9;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
            z-index: 10;
        }
        
        .xp-bar {
            width: 100%; 
            height: 20px;
            background: rgba(244, 227, 199, 0.4);
            border-radius: 10px; 
            overflow: hidden;
            border: 2px solid rgba(74, 43, 23, 0.3);
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            position: relative;
        }
        
        .xp-fill {
            height: 100%; 
            width: 0%;
            background: linear-gradient(90deg, #8B4513 0%, #D2691E 50%, #CD853F 100%);
            transition: width 0.8s cubic-bezier(0.4, 0.0, 0.2, 1);
            box-shadow: inset 0 2px 4px rgba(255,255,255,0.3);
            position: relative;
        }
        
        .xp-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 50%;
            background: linear-gradient(180deg, rgba(255,255,255,0.3) 0%, transparent 100%);
            border-radius: 10px 10px 0 0;
        }
        
            .dragon {
        
            margin-left: 10px;
            position: relative; 
            width: 40px;
            top: -62px;   
            left: 60px;
            transition: left 0.8s cubic-bezier(0.4, 0.0, 0.2, 1);
            z-index: 11;
        }
        
        
        
        /* Animação de pulso quando ganha XP */
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        /* .dragon.gaining-xp {
            animation: pulse 0.2s ease;
        } */
        
        .vocabulario-btn img { margin-left: 60%; }
        .pocao-btn img { margin-right: 36%; }
    </style>
</head>

<body>

    <header class="topo">
        <h1 class="titulo">As runas da identidade</h1>
        <a href="../mapa/mapa.php" class="sair"><img src="imgs/sair.png" alt="Sair" /></a>
    </header>

    <div class="main-content">
        <div class="question-container">
            <p class="instruction">Complete a frase corretamente</p>
          <p class="question-phrase" id="questionPhrase">He brave</p>

            <div class="options">

                <button class="option-btn correct">A) Is</button>
                <button class="option-btn">B) Am</button>
                <button class="option-btn">C) Are</button>

            </div>

            <p id="feedback"></p>
        </div>
        
        <button id="next-phase-btn-outside" onclick="avancar()" >Avançar</button>
    </div>

    <div class="xp-container">
        <div class="xp-info">
            <span>XP: <span id="xp-current">0/100</span></span>
            <span id="xp-gained" style="color: #90EE90; display: none;">+1</span>
        </div>
        <div class="xp-bar">
            <div class="xp-fill" id="xp-fill"></div>
        </div>
        <img src="imgs/dragao.png" class="dragon" id="dragon" />
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

    <script>

        function avancar() {
            window.location.href = 'fase3.php';
        }
// ============================================
// CONFIGURAÇÕES - FASE 2
// ============================================

const NOME_ALUNO = "<?php echo $nomeAluno; ?>";
const NUMERO_FASE = <?php echo $numero_fase; ?>;
const NOME_ATIVIDADE = "<?php echo $nome_atividade; ?>";
const TIPO_GRAMATICA = "<?php echo $tipo_gramatica; ?>"; // ⭐ NOVO
const TIPO_HABILIDADE = "<?php echo $tipo_habilidade; ?>"; // ⭐ NOVO
const XP_ATUAL_FASE = <?php echo $xp_atual_fase; ?>;
const XP_TOTAL_ACUMULADO = <?php echo obterXPTotal($pdo, $usuario_id); ?>;
const XP_MAXIMO_TOTAL = 10;

const phasePoints = { 
    correct: 1,
    wrong: -1
};

// ============================================
// ELEMENTOS DOM
// ============================================
const questionContainer = document.querySelector(".question-container"); 
const feedback = document.getElementById("feedback");
const options = document.querySelectorAll(".option-btn");
const nextBtn = document.getElementById("next-phase-btn-outside");
const questionPhrase = document.getElementById("questionPhrase");
const xpFill = document.getElementById("xp-fill");
const dragon = document.getElementById("dragon");
const xpCurrent = document.getElementById("xp-current");
const xpGained = document.getElementById("xp-gained");

// ============================================
// VARIÁVEIS DE XP
// ============================================
let xpFaseAtual = XP_ATUAL_FASE;
let xpTotalAcumulado = XP_TOTAL_ACUMULADO;
let xpGanhoNaRodadaAtual = 0;
let jaRespondeu = false;

updateXPBar();

console.log('🎮 Fase 2 iniciada:', {
    xpFaseAtual: xpFaseAtual,
    xpTotalAcumulado: xpTotalAcumulado
});

// ============================================
// SALVAR PROGRESSO DETALHADO (⭐ NOVO)
// ============================================
function salvarProgressoDetalhado(acertou) {
    const formData = new FormData();
    formData.append('usuario_id', <?php echo $usuario_id; ?>);
    formData.append('fase', NUMERO_FASE);
    formData.append('atividade', NOME_ATIVIDADE);
    formData.append('tipo_gramatica', TIPO_GRAMATICA);
    formData.append('tipo_habilidade', TIPO_HABILIDADE);
    formData.append('acertou', acertou ? 1 : 0);

    console.log('📤 Salvando progresso detalhado:', {
        fase: NUMERO_FASE,
        atividade: NOME_ATIVIDADE,
        tipo_gramatica: TIPO_GRAMATICA,
        tipo_habilidade: TIPO_HABILIDADE,
        acertou: acertou
    });

    fetch('../mapa/salvar_progresso_detalhado.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log('📥 Resposta progresso:', data);
        
        if (data.sucesso) {
            console.log(`✅ Progresso salvo com sucesso!`);
        } else {
            console.error('❌ Erro:', data.mensagem);
        }
    })
    .catch(error => console.error('❌ Erro na requisição:', error));
}


// ============================================
// FUNÇÕES DE XP
// ============================================
function updateXPBar() {
    let percent = Math.min((xpTotalAcumulado / XP_MAXIMO_TOTAL) * 100, 100);
    xpFill.style.width = percent + "%";
    dragon.style.left = `calc(${percent}% - 27px)`;
    xpCurrent.textContent = `${xpTotalAcumulado}/${XP_MAXIMO_TOTAL}`;
    
    console.log(`📊 Barra - Total: ${xpTotalAcumulado}/${XP_MAXIMO_TOTAL}`);
}

function animateXPGain(amount) {
    if (amount !== 0) {
        xpGained.textContent = (amount > 0 ? '+' : '') + amount;
        xpGained.style.color = amount > 0 ? '#90EE90' : '#FF6B6B';
        xpGained.style.display = 'inline';
    }
    
    dragon.classList.add('gaining-xp');
    
    setTimeout(() => {
        xpGained.style.display = 'none';
        dragon.classList.remove('gaining-xp');
    }, 1500);
}

function giveXP(isCorrect) {
    const xpChange = isCorrect ? phasePoints.correct : phasePoints.wrong;
    
    console.log('⭐ giveXP:', isCorrect ? 'CORRETO ✔' : 'ERRADO ✖');
    
    xpFaseAtual += xpChange;
    if (xpFaseAtual < 0) xpFaseAtual = 0;
    if (xpFaseAtual > 10) xpFaseAtual = 10;
    
    xpTotalAcumulado += xpChange;
    if (xpTotalAcumulado < 0) xpTotalAcumulado = 0;
    if (xpTotalAcumulado > XP_MAXIMO_TOTAL) xpTotalAcumulado = XP_MAXIMO_TOTAL;
    
    xpGanhoNaRodadaAtual = xpChange;
    
    console.log(`📊 XP - Fase: ${xpFaseAtual}/10 | Total: ${xpTotalAcumulado}/${XP_MAXIMO_TOTAL}`);
    
    updateXPBar();
    animateXPGain(xpChange);
}

function salvarXPNoBanco() {
    if (xpGanhoNaRodadaAtual === 0) return;

    const formData = new FormData();
    formData.append('nomeAluno', NOME_ALUNO);
    formData.append('fase', NUMERO_FASE);
    formData.append('xp', xpGanhoNaRodadaAtual);

    fetch('../mapa/salvar_xp.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            console.log(`✅ Salvo! Fase ${NUMERO_FASE}: ${data.xp_fase}/10 | Total: ${data.xp_total}`);
        }
    })
    .catch(error => console.error('❌ Erro:', error));
    
    xpGanhoNaRodadaAtual = 0;
}

function calcularEstrelas() {
    let estrelas = 0;
    if (xpFaseAtual >= 8) estrelas = 3;
    else if (xpFaseAtual >= 5) estrelas = 2;
    else if (xpFaseAtual >= 2) estrelas = 1;
    
    console.log(`⭐ Estrelas: ${estrelas} (XP fase: ${xpFaseAtual}/10)`);
    return estrelas;
}

function salvarEstrelasFase(estrelas) {
    const formData = new FormData();
    formData.append('nomeAluno', NOME_ALUNO);
    formData.append('fase', NUMERO_FASE);
    formData.append('estrelas', estrelas);

    fetch('../mapa/salvar_estrelas_fase.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .catch(error => console.error('❌ Erro:', error));
}

// ============================================
// LÓGICA DA QUESTÃO
// ============================================
options.forEach(btn => {
    btn.addEventListener("click", () => {
        if (jaRespondeu) return;
        jaRespondeu = true;

        const isCorrect = btn.classList.contains("correct");

        const chosenWord = btn.textContent.trim().replace(/^.\)\s*/, "");
        questionPhrase.innerHTML = `He <span id="chosenWord">${chosenWord}</span> brave`;

        const chosenSpan = document.getElementById("chosenWord");
        chosenSpan.style.fontWeight = "700";

        if (isCorrect) {
            chosenSpan.style.color = "green";
            feedback.textContent = "✔ Resposta correta!";
            feedback.style.color = "#388e3c";
            btn.classList.add("selected-correct");
            giveXP(true);
        } else {
            chosenSpan.style.color = "red";
            feedback.textContent = "✖ Resposta errada!";
            feedback.style.color = "#b71c1c";
            btn.classList.add("selected-wrong");
            giveXP(false);
            document.querySelector(".option-btn.correct")?.classList.add("show-correct");
        }

        // ⭐ SALVAR TUDO NA ORDEM CORRETA
        salvarProgressoDetalhado(isCorrect);  // ✅ ADICIONAR ESTA LINHA
        salvarXPNoBanco();
        salvarEstrelasFase(calcularEstrelas());

        questionContainer.classList.add("answered");
        nextBtn.style.display = "block";
        options.forEach(b => b.disabled = true);
    });
});

function avancar() {
    window.location.href = 'fase3.php';
}
    </script>

</body> 
</html>