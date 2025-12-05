<?php
// Iniciar sessão e verificar login
require_once '../mapa/config.php';
verificarLogin();

// Pegar nome do usuário da sessão
$nomeAluno = $_SESSION['nome'] ?? $_SESSION['usuario_nome'] ?? 'Visitante';
$usuario_id = $_SESSION['id'] ?? $_SESSION['usuario_id'] ?? 0;

// ⭐ DEFINIR: Esta é a FASE 8 do Jogo 4 (Espelhos de Midgard)
$numero_fase = 8;
$jogo_numero = 4;

// ⭐ DEFINIR TIPO DA QUESTÃO
$tipo_gramatica = 'interrogativa'; // Tipo de gramática: interrogative
$tipo_habilidade = 'speaking'; // Pronúncia é speaking
$nome_atividade = 'jogo4_fase8';

// ✅ BUSCAR XP ATUAL DESTA FASE DO JOGO 4
$xp_atual_fase = obterXPFase3($pdo, $usuario_id, $numero_fase); // Use obterXPFase2
$xp_total_jogo3 = obterXPTotal3($pdo, $usuario_id); // Use obterXPTotal2

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />


    <link href="https://fonts.googleapis.com/css2?family=Irish+Grover&family=Itim&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="icon" type="image/png" href="imgs/logo.png">
    <title>English Adventure </title>

    <style>
        * {
            margin: 0; padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }
        body {
            background-image: url("imgs/espelho.png");
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
            height: 100vh; width: 100vw;
            display: flex; justify-content: center; align-items: center;
            overflow: hidden;
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
            margin-bottom: 5%;
        }

        .question-container {
            width: 650px; 
            background: rgba(255,255,255,0.70);
            padding: 40px;
            border-radius: 25px;
            backdrop-filter: blur(6px);
            box-shadow: 0 4px 25px rgba(0,0,0,0.25);
            text-align: center;
            display: flex; flex-direction: column;
            justify-content: center; align-items: center;
            z-index: 5;
            transition: padding 0.3s ease;
        }
        .question-container.answered {
            padding-top: 25px; padding-bottom: 25px;
        }

        .instruction {
            font-size: 18px; color: #444; margin-bottom: 30px;
        }

        .pronunciation-phrase-box {
            width: 400px; 
            background: rgba(255,255,255,0.8);
            border: 2px solid #aaa; 
            border-radius: 40px;
            padding: 12px 25px; 
            margin-bottom: 15px;
            display: inline-flex; 
            justify-content: center; 
            align-items: center;
        }

        .pronunciation-phrase {
            font-size: 18px; 
            font-weight: 500; 
            color: #222;
        }

        .audio-control-container {
            display: flex; 
            align-items: center; 
            justify-content: center;
            margin-bottom: 30px;
        }

        #pronunciation-label {
            font-size: 14px; 
            color: #555;
            cursor: pointer; 
            text-decoration: underline;
            margin-right: 10px;
        }

        .speed-btn {
            background: #f0f0f0; 
            border: 1px solid #ccc;
            color: #444; 
            font-size: 12px; 
            font-weight: 600;
            padding: 8px 6px; 
            margin-left: 5px;
            border-radius: 50px; 
            cursor: pointer;
            transition: 0.1s;
            display: none;
        }

        .speed-btn:hover { background: #e0e0e0; }
        .speed-btn.active {
            background: #3b2a20; 
            color: white; 
            border-color: #3b2a20;
            padding: 8px 8px; 
            border-radius: 50px;
        }

        .mic-btn {
            width: 90px; 
            height: 90px; 
            border-radius: 50%;
            background: #ffffff; 
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            cursor: pointer; 
            transition: 0.2s;
            display: flex; 
            justify-content: center; 
            align-items: center;
        }

        .mic-icon {
            width: 35px; 
            height: 35px; 
            fill: #3b2a20;
        }

        .mic-recording {
            background: #f44336; 
            animation: pulse 1.5s infinite;
        }

        .mic-recording .mic-icon { fill: white; }

        @keyframes pulse {
            0%   { box-shadow: 0 0 0 0 rgba(244,67,54,0.4); }
            70%  { box-shadow: 0 0 0 20px rgba(244,67,54,0); }
            100% { box-shadow: 0 0 0 0 rgba(244,67,54,0); }
        }

        #feedback {
            margin-top: 40px; 
            font-size: 20px; 
            font-weight: bold;
        }

        .send-btn {
            margin-top: 25px;
            padding: 10px 40px;
            font-size: 16px; 
            font-weight: 600;
            border: none;
            width: 280px; 
            border-radius: 40px;
            cursor: pointer;
            background: #3b2a20; 
            color: white;
            transition: 0.2s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            display: none;
        }

        .send-btn:hover {
            transform: scale(1.03); 
            background: #2c1e15; 
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
            padding: 25px 50px;
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
            margin-left: 18px;
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
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .gaining-xp {
            animation: pulse 0.6s ease-in-out;
        }

       .vocabulario-btn img { margin-left: 60%; }
        .pocao-btn img { margin-right: 36%; }
     .bottom-left { position: absolute; left: 0; bottom: 60px; display: flex; align-items: center; gap: 10px; }
.pocao { position: absolute; bottom: 60px; right: 0; display: flex; flex-direction: column; align-items: flex-end; gap: 5px; }
.vocabulario-btn, .pocao-btn { display: flex; align-items: center; justify-content: center; gap: 8px; background: rgba(255,255,255,0.95); border: none; cursor: pointer; box-shadow: 0 3px 6px rgba(0,0,0,0.3); transition: 0.2s; font-size: 16px; font-weight: 600; padding: 20px 50px; }
.vocabulario-btn { border-radius: 0 39px 39px 0; padding: 20px 80px; }
.pocao-btn { border-radius: 39px 0 0 39px; }
.vocabulario-btn:hover, .pocao-btn:hover { transform: scale(1.05); }
.vocabulario-btn img { margin-left: 60%; }
.pocao-btn img { margin-right: 36%; }

        .sair {
            position: absolute; right: 40px;
            display: flex; align-items: center; justify-content: center;
            text-decoration: none;
            background: transparent !important; 
    border: none !important;
        }
        .sair img {
            width: 28px; height: 28px;
            transition: 0.2s;
        }
        .sair img:hover { transform: scale(1.1); }
/* === OVERLAYS DO DICIONÁRIO E POÇÃO === */
#dictionaryOverlay, 
#pocaoOverlay {
    position: fixed; top:0; left:0;
    width:100vw; height:100vh;
    background: rgba(0,0,0,0.45);
    opacity: 0; pointer-events: none;
    transition: 0.3s ease; 
    z-index: 900;
}

#dictionaryOverlay.show,
#pocaoOverlay.show {
    opacity: 1;
    pointer-events: auto;
}

/* === MODAIS DO DICIONÁRIO E POÇÃO === */
#dictionaryModal,
#pocaoModal {
    position: fixed;
    top:50%; left:50%;
    width: 100%; height: 100%;
    transform: translate(-50%, -50%) scale(0.85);
    opacity: 0; pointer-events: none;
    background: transparent;
    border: none;
    transition: 0.35s ease;
    z-index: 1000;
}

#dictionaryModal.show,
#pocaoModal.show {
    transform: translate(-50%, -50%) scale(1);
    opacity: 1;
    pointer-events: auto;
}

/* === OVERLAY/MODAL DO SAIR (TOTALMENTE SEPARADO) === */
#sairOverlay2 {
    position: fixed; top: 0; left: 0;
    width: 100vw; height: 100vh;
    background: rgba(0,0,0,0.55);
    opacity: 0; pointer-events: none;
    transition: 0.3s ease;
    z-index: 1200;
}

#sairOverlay2.show {
    opacity: 1;
    pointer-events: auto;
}

#sairModal2 {
    position: fixed;
    top: 50%; left: 50%;
    width: 100%; height: 100%;
    transform: translate(-50%, -50%) scale(0.85);
    opacity: 0; pointer-events: none;
    border: none;
    background: transparent;
    transition: 0.35s ease;
    z-index: 1300;
}

#sairModal2.show {
    opacity: 1;
    transform: translate(-50%, -50%) scale(1);
    pointer-events: auto;
}

        .vocabulario-btn img { margin-left: 60%; }
        .pocao-btn img { margin-right: 36%; }
    </style>
</head>

<body>
  <header class="topo">
        <h1 class="titulo">Espelhos de Midgard </h1>
       <button id="sairBtn" class="sair">
   <img src="imgs/sair.png" alt="Sair"/>
</button>
</header>
    
    <div class="main-content">
        <div class="question-container" id="questionContainer">
            <p class="instruction">Pronuncie</p>

            <div class="pronunciation-phrase-box">
                <span class="pronunciation-phrase" id="pronunciationText">Is the warrior ready?</span>
            </div>

            <div class="audio-control-container">
                <p id="pronunciation-label">Escutar pronúncia</p>

                <button class="speed-btn active" data-speed="1.0">1.0x</button>
                <button class="speed-btn" data-speed="0.5">0.5x</button>
            </div>

            <button class="mic-btn" id="micBtn">
                <img class="mic-icon" src="imgs/microfone.png" alt="Microfone">
            </button>

            <p id="feedback"></p>
        </div>

        <button id="nextBtn" class="send-btn" onclick="avancar()">Avançar</button>
    </div>

    <div class="xp-container">
        <div class="xp-info">
            <span>XP: <span id="xp-current">0/50</span></span>
            <span id="xp-gained" style="color: #90EE90; display: none;">+10</span>
        </div>
        <div class="xp-bar">
            <div class="xp-fill" id="xp-fill"></div>
        </div>
        <img src="imgs/dragao.png" class="dragon" id="dragon" />
    </div>

   <div class="bottom-left">
    <button class="vocabulario-btn" id="openDictionaryBtn">
        <span>Dicionário</span>
        <img src="imgs/dicionario.png" alt="Vocabulário" /> 
    </button>
</div>
<div id="dictionaryOverlay"></div>
<iframe id="dictionaryModal" src="../atributos/dicionario.html"></iframe>

<div class="pocao">
    <button class="pocao-btn" id="openPocaoBtn">
       <img src="imgs/poção.png" alt="Poção" /> 
        <span>Poção</span>
        
    </button>
</div>
<div id="pocaoOverlay"></div>
<iframe id="pocaoModal" src="../atributos/poção/am.html"></iframe>

<!-- NOVO OVERLAY/MODAL 100% SEPARADO DO SAIR -->

<div id="sairOverlay2"></div>
<iframe id="sairModal2" src=""></iframe>
    <!-- Áudios de feedback -->
    <audio id="somAcerto" src="sound/acerto.mp4" preload="auto"></audio>
    <audio id="somErro" src="sound/erro.mp4" preload="auto"></audio>

    <script>
// ============================================
// CONFIGURAÇÕES - JOGO 4 (Espelhos de Midgard)
// ============================================
const NOME_ALUNO = "<?php echo $nomeAluno; ?>";
const NUMERO_FASE = <?php echo $numero_fase; ?>;
const JOGO_NUMERO = <?php echo $jogo_numero; ?>; // 4
const NOME_ATIVIDADE = "<?php echo $nome_atividade; ?>";
const TIPO_GRAMATICA = "<?php echo $tipo_gramatica; ?>";
const TIPO_HABILIDADE = "<?php echo $tipo_habilidade; ?>";
const XP_ATUAL_FASE = <?php echo $xp_atual_fase; ?>;
const XP_TOTAL_ACUMULADO = <?php echo $xp_total_jogo3; ?>;
const XP_MAXIMO_TOTAL = 50; // 8 fases × 40 XP cada = 320 XP total

const phasePoints = { 
    correct: 5,
    wrong: -5
};

// ============================================
// ELEMENTOS DOM
// ============================================
const questionContainer = document.getElementById("questionContainer");
const feedback = document.getElementById("feedback");
const nextBtn = document.getElementById("nextBtn");
const micBtn = document.getElementById("micBtn");
const pronunciationLabel = document.getElementById("pronunciation-label");
const speedBtns = document.querySelectorAll(".speed-btn");
const pronunciationText = document.getElementById("pronunciationText");
const xpFill = document.getElementById("xp-fill");
const dragon = document.getElementById("dragon");
const xpCurrent = document.getElementById("xp-current");
const xpGained = document.getElementById("xp-gained");

const somAcerto = document.getElementById("somAcerto");
const somErro = document.getElementById("somErro");


// ============================================
// VARIÁVEIS DE CONTROLE
// ============================================
let xpFaseAtual = XP_ATUAL_FASE;
let xpTotalAcumulado = XP_TOTAL_ACUMULADO;
let xpGanhoNaRodadaAtual = 0;
let jaRespondeu = false;
let questaoJaFoiPontuada = false;
let isRecording = false;
let speedVisible = false;

let audio = new Audio();
audio.src = "sound/isthewarriorready.mp3";

updateXPBar();

console.log('🎮 Jogo 4 - Fase iniciada:', {
    jogo: JOGO_NUMERO,
    fase: NUMERO_FASE,
    atividade: NOME_ATIVIDADE,
    tipo_gramatica: TIPO_GRAMATICA,
    tipo_habilidade: TIPO_HABILIDADE,
    xpFaseAtual: xpFaseAtual,
    xpTotalAcumulado: xpTotalAcumulado
});

// ============================================
// FUNÇÕES DE XP
// ============================================
function updateXPBar() {
    let percent = Math.min((xpTotalAcumulado / XP_MAXIMO_TOTAL) * 100, 100);
    xpFill.style.width = percent + "%";
    dragon.style.left = `calc(${percent}% - 27px)`;
    xpCurrent.textContent = `${xpTotalAcumulado}/${XP_MAXIMO_TOTAL}`;
    
    console.log(`📊 Barra atualizada - Total: ${xpTotalAcumulado}/${XP_MAXIMO_TOTAL} (${percent.toFixed(1)}%)`);
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
    
    console.log('⭐ giveXP:', isCorrect ? 'CORRETO ✔' : 'ERRADO ✖', '| Mudança:', xpChange);
    
    xpFaseAtual += xpChange;
    if (xpFaseAtual < 0) xpFaseAtual = 0;
    if (xpFaseAtual > 50) xpFaseAtual = 50;
    
    xpTotalAcumulado += xpChange;
    if (xpTotalAcumulado < 0) xpTotalAcumulado = 0;
    if (xpTotalAcumulado > XP_MAXIMO_TOTAL) xpTotalAcumulado = XP_MAXIMO_TOTAL;
    
    xpGanhoNaRodadaAtual = xpChange;
    
    console.log(`📊 XP - Fase: ${xpFaseAtual}/50 | Total: ${xpTotalAcumulado}/${XP_MAXIMO_TOTAL}`);
    
    updateXPBar();
    animateXPGain(xpChange);
}

// ============================================
// SALVAR PROGRESSO DETALHADO
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
// SALVAR XP NO BANCO (JOGO 3) - CORRIGIDO
// ============================================
function salvarXPJogo3() {
    if (xpGanhoNaRodadaAtual === 0) {
        console.log('⚠️ Nenhum XP para salvar');
        return;
    }

    const formData = new FormData();
    formData.append('nomeAluno', NOME_ALUNO);
    formData.append('fase', NUMERO_FASE);
    formData.append('xp', xpGanhoNaRodadaAtual);

    console.log('📤 Salvando XP Jogo 3:', {
        aluno: NOME_ALUNO,
        fase: NUMERO_FASE,
        xp_mudanca: xpGanhoNaRodadaAtual
    });

    // ✅ CORREÇÃO: Usar salvar_xp3.php (arquivo correto para Jogo 3)
    fetch('../mapa/salvar_xp3.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log('📥 Resposta Jogo 3:', data);
        
        if (data.sucesso) {
            console.log(`✅ Salvo! XP Fase ${NUMERO_FASE}: ${data.xp_fase}/50 | XP Total: ${data.xp_total} | Estrelas: ${data.estrelas}`);
        } else {
            console.error('❌ Erro:', data.mensagem);
        }
    })
    .catch(error => console.error('❌ Erro na requisição:', error));
    
    xpGanhoNaRodadaAtual = 0;
}

// ============================================
// RECONHECIMENTO DE VOZ
// ============================================
let recognition;

try {
    recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
} catch (e) {
    alert("Seu navegador não suporta reconhecimento de voz.");
}

if (recognition) {
    recognition.lang = "en-US";
    recognition.interimResults = false;
    recognition.maxAlternatives = 1;
}

// Normalização de texto
function normalize(text) {
    return text
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/[^\w\s]/gi, "")
        .trim();
}

// Distância de Levenshtein
function levenshtein(a, b) {
    const matrix = [];
    if (!a.length) return b.length;
    if (!b.length) return a.length;

    for (let i = 0; i <= b.length; i++) matrix[i] = [i];
    for (let j = 0; j <= a.length; j++) matrix[0][j] = j;

    for (let i = 1; i <= b.length; i++) {
        for (let j = 1; j <= a.length; j++) {
            if (b.charAt(i - 1) === a.charAt(j - 1)) {
                matrix[i][j] = matrix[i - 1][j - 1];
            } else {
                matrix[i][j] = Math.min(
                    matrix[i - 1][j - 1] + 1,
                    matrix[i][j - 1] + 1,
                    matrix[i - 1][j] + 1
                );
            }
        }
    }
    return matrix[b.length][a.length];
}

// Determina se a fala bate com a frase
function isSpeechCorrect(spokenText) {
    const correct = normalize(pronunciationText.textContent);
    const spoken = normalize(spokenText);

    const distance = levenshtein(spoken, correct);
    const similarity = 1 - distance / Math.max(spoken.length, correct.length);

    console.log("Falado:", spoken);
    console.log("Correto:", correct);
    console.log("Similaridade:", similarity);

    return similarity >= 0.78; // bom para pronunciantes brasileiros
}

// ============================================
// CONTROLES DE ÁUDIO
// ============================================
function showSpeedButtons() {
    if (!speedVisible) {
        speedBtns.forEach(btn => btn.style.display = "inline-block");
        speedVisible = true;
    }
}

function playAudio() {
    if (isRecording) return;

    showSpeedButtons();

    audio.currentTime = 0;
    audio.play();
}

// ============================================
// MICROFONE E GRAVAÇÃO
// ============================================
function processSpeech() {
    if (jaRespondeu || questaoJaFoiPontuada) {
        console.log('⚠️ Questão já foi respondida e pontuada anteriormente');
        return;
    }

    jaRespondeu = true;
    questaoJaFoiPontuada = true;

    if (!recognition) {
        alert("Reconhecimento de voz não suportado.");
        return;
    }

    if (isRecording) {
        recognition.stop();
        return;
    }

    // interrompe áudio se estiver tocando
    if (!audio.paused) {
        audio.pause();
        audio.currentTime = 0;
    }

    isRecording = true;

    micBtn.classList.add("mic-recording");
    feedback.textContent = "";
    pronunciationLabel.textContent = "Gravando... Fale agora!";

    recognition.start();
}

// ============================================
// EVENT LISTENERS
// ============================================
pronunciationLabel.addEventListener("click", playAudio);

speedBtns.forEach(btn => {
    btn.addEventListener("click", e => {
        if (isRecording) return;

        const speed = parseFloat(e.target.dataset.speed);
        audio.playbackRate = speed;

        speedBtns.forEach(b => b.classList.remove("active"));
        e.target.classList.add("active");

        playAudio();
    });
});

micBtn.addEventListener("click", processSpeech);

if (recognition) {
    recognition.addEventListener("result", event => {
        const spoken = event.results[0][0].transcript;

        isRecording = false;
        micBtn.classList.remove("mic-recording");

        const correct = isSpeechCorrect(spoken);

        questionContainer.classList.add("answered");

        if (correct) {
            feedback.textContent = "✔ Pronúncia correta!";
            feedback.style.color = "#388e3c";
                   somAcerto.play(); // ⭐ Toca som de acerto
            giveXP(true);
        } else {
            feedback.textContent = "✖ Não foi desta vez. Tente novamente!";
            feedback.style.color = "#b71c1c";
                   somErro.play(); // ⭐ Toca som de acerto
            giveXP(false);
        }

        // ⭐ BOTÃO AVANÇAR SEMPRE APARECE (UMA TENTATIVA)
        nextBtn.style.display = "block";

        // Desabilitar interações
        micBtn.style.pointerEvents = "none";
        pronunciationLabel.style.pointerEvents = "none";
        speedBtns.forEach(btn => btn.style.pointerEvents = "none");

        pronunciationLabel.textContent = "Escutar pronúncia";

        console.log('💾 Salvando no banco...');
        salvarProgressoDetalhado(correct);
        salvarXPJogo3();
    });

    recognition.addEventListener("end", () => {
        if (isRecording) {
            recognition.start();
        }
    });
}

function avancar() {
    const proximaFase = NUMERO_FASE + 1;
    if (proximaFase <= 10) {
        window.location.href = 'fase' + proximaFase + '.php';
    } else {
        window.location.href = '../mapa/fases.php';
    }
}

// ============================================
// MODAIS (Dicionário e Poção)
// ============================================
const dictionaryOverlay = document.getElementById("dictionaryOverlay");
const dictionaryModal = document.getElementById("dictionaryModal");
const openDictionaryBtn = document.getElementById("openDictionaryBtn");

function openDictionary() { 
    dictionaryOverlay.classList.add("show"); 
    dictionaryModal.classList.add("show"); 
    try { dictionaryModal.focus(); } catch(e){} 
}
function closeDictionary() { 
    dictionaryOverlay.classList.remove("show"); 
    dictionaryModal.classList.remove("show"); 
}
window.closeDictionary = closeDictionary;

openDictionaryBtn.addEventListener("click", e => { 
    e.stopPropagation(); 
    openDictionary(); 
});
dictionaryOverlay.addEventListener("click", e => { 
    if(e.target === dictionaryOverlay) closeDictionary(); 
});
document.addEventListener("keydown", e => { 
    if(e.key === "Escape") closeDictionary(); 
});
 // === DICIONÁRIO ===
const dictionaryOverlay = document.getElementById("dictionaryOverlay");
const dictionaryModal = document.getElementById("dictionaryModal");
const openDictionaryBtn = document.getElementById("openDictionaryBtn");

function openDictionary() { dictionaryOverlay.classList.add("show"); dictionaryModal.classList.add("show"); }
function closeDictionary() { dictionaryOverlay.classList.remove("show"); dictionaryModal.classList.remove("show"); }
window.closeDictionary = closeDictionary;

openDictionaryBtn.addEventListener("click", e => { e.stopPropagation(); openDictionary(); });
dictionaryOverlay.addEventListener("click", e => { if(e.target === dictionaryOverlay) closeDictionary(); });
document.addEventListener("keydown", e => { if(e.key === "Escape") closeDictionary(); });


// === POÇÃO ===
const pocaoOverlay = document.getElementById("pocaoOverlay");
const pocaoModal = document.getElementById("pocaoModal");
const openPocaoBtn = document.getElementById("openPocaoBtn");

function openPocao() { pocaoOverlay.classList.add("show"); pocaoModal.classList.add("show"); }
function closePocao() { pocaoOverlay.classList.remove("show"); pocaoModal.classList.remove("show"); }
window.closePocao = closePocao;

openPocaoBtn.addEventListener("click", e => { e.stopPropagation(); openPocao(); });
pocaoOverlay.addEventListener("click", e => { if(e.target === pocaoOverlay) closePocao(); });
document.addEventListener("keydown", e => { if(e.key === "Escape") closePocao(); });

// === BOTÃO SAIR - ABRIR MODAL ===
const sairBtn = document.getElementById("sairBtn");
const sairOverlay = document.getElementById("sairOverlay2");
const sairModal = document.getElementById("sairModal2");

sairBtn.addEventListener("click", () => {
  sairOverlay.classList.add("show");
  sairModal.classList.add("show");
  sairModal.src = "../atributos/sair.html"; // o arquivo do modal
});

// === FECHAR QUANDO CLICAR FORA ===
sairOverlay.addEventListener("click", () => {
  fecharTelaSair();
});

// === FUNÇÃO PARA FECHAR O MODAL ===
function fecharTelaSair() {
  sairOverlay.classList.remove("show");
  sairModal.classList.remove("show");
}

// === RECEBER EVENTO DO IFRAME (RETOMAR) ===
window.addEventListener("message", (event) => {
  if (event.data?.action === "retomarJogo") {
    fecharTelaSair();
  }
});

    </script>
</body>
</html>