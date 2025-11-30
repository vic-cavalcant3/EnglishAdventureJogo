<?php
// Iniciar sessão e verificar login
require_once '../mapa/config.php';
verificarLogin();

// Pegar nome do usuário da sessão
$nomeAluno = $_SESSION['nome'] ?? $_SESSION['usuario_nome'] ?? 'Visitante';
$usuario_id = $_SESSION['id'] ?? $_SESSION['usuario_id'] ?? 0;

// Definir qual fase é esta
$numero_fase = 9; // Fase 7 do Jogo 2
$jogo_numero = 3; // Jogo 2 (Floresta Escura)

$tipo_gramatica = 'negativa';
$tipo_habilidade = 'speaking';
$nome_atividade = 'jogo3_fase9';

// Buscar XP atual desta fase do banco
$xp_atual_fase = obterXPFase2($pdo, $usuario_id, $numero_fase);
$xp_total_jogo2 = obterXPTotal2($pdo, $usuario_id);
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
            margin: 0; padding: 0; box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }
        body {
            background-image: url("imgs/floresta.png");
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
            height: 100vh; width: 100vw;
            overflow: hidden; display: block;
        }
        /* ---------------- TOPO ---------------- */
        .topo {
            position: absolute; top: 0; left: 0;
            width: 100%; height: 70px;
            display: flex; justify-content: center; align-items: center;
            padding: 0 40px; background: transparent;
            color: white; z-index: 10;
            font-family: "Irish Grover", sans-serif;
        }
        .titulo {
            position: absolute; left: 50%; transform: translateX(-50%);
            font-size: 28px; color: #ffffffd8;
            letter-spacing: 2px; font-family: "Irish Grover", sans-serif;
        }
        .sair {
            position: absolute; right: 40px;
            display: flex; align-items: center; justify-content: center;
            text-decoration: none;
        }
        .sair img {
            width: 28px; height: 28px; transition: 0.2s;
        }
        .sair img:hover { transform: scale(1.1); }
        /* ---------------- CONTEÚDO PRINCIPAL ---------------- */
        .main-content {
            display: flex; flex-direction: column; align-items: center;
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%); z-index: 5;
        }
        .question-container {
            width: 650px; background: rgba(255, 255, 255, 0.70);
            padding: 40px; border-radius: 25px;
            backdrop-filter: blur(6px); box-shadow: 0 4px 25px rgba(0,0,0,0.25);
            text-align: center; display: flex; flex-direction: column;
            align-items: center; z-index: 5; transition: padding 0.3s ease;
        }
        .question-container.answered {
            padding-top: 25px; padding-bottom: 25px;
        }
        /* ---------------- FRASE ---------------- */
        .instruction {
            font-size: 18px; color: #444; margin-bottom: 30px;
        }
        .pronunciation-phrase-box {
            width: 400px; background: rgba(255,255,255,0.8);
            border: 2px solid #aaa; border-radius: 40px;
            padding: 12px 25px; margin-bottom: 15px;
            display: inline-flex; justify-content: center; align-items: center;
        }
        .pronunciation-phrase {
            font-size: 18px; font-weight: 500; color: #222;
        }
        /* ---------------- RÓTULO + VELOCIDADE ---------------- */
        .audio-control-container {
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 30px;
        }
        #pronunciation-label {
            font-size: 14px; color: #555;
            cursor: pointer; text-decoration: underline;
            margin-right: 10px;
        }
        .speed-btn {
            background: #f0f0f0; border: 1px solid #ccc;
            color: #444; font-size: 12px; font-weight: 600;
            padding: 3px 8px; margin-left: 5px;
            border-radius: 5px; cursor: pointer;
            transition: 0.1s;
            display: none;
        }
        .speed-btn:hover { background: #e0e0e0; }
        .speed-btn.active {
            background: #3b2a20; color: white; border-color: #3b2a20;
        }
        /* ---------------- MICROFONE ---------------- */
        .mic-btn {
            width: 90px; height: 90px; border-radius: 50%;
            background: #ffffff; border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            cursor: pointer; transition: 0.2s;
            display: flex; justify-content: center; align-items: center;
        }
        .mic-icon {
            width: 45px; height: 45px; fill: #3b2a20;
        }
        .mic-recording {
            background: #f44336; animation: pulse 1.5s infinite;
        }
        .mic-recording .mic-icon { fill: white; }
        @keyframes pulse {
            0%   { box-shadow: 0 0 0 0 rgba(244,67,54,0.4); }
            70%  { box-shadow: 0 0 0 20px rgba(244,67,54,0); }
            100% { box-shadow: 0 0 0 0 rgba(244,67,54,0); }
        }
        #feedback {
            margin-top: 40px; font-size: 20px; font-weight: bold;
        }
        /* ---------------- BOTÃO AVANÇAR ---------------- */
        #next-phase-btn-outside {
                     width: 280px;
            background: #3b2a20;
            padding: 10px;
            border-radius: 40px;
            margin-top: 25px;
            display: none;
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s ease;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        #next-phase-btn-outside:hover {
                               background: #2c1e15;
            transform: scale(1.03);
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
        .dragon.gaining-xp {
            animation: pulse 0.2s ease;
        }
        /* ---------------- BOTÕES INFERIORES ---------------- */
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
        .vocabulario-btn img { margin-left: 60%; }
        .pocao-btn img { margin-right: 36%; }
    </style>
</head>
<body>
    <header class="topo">
        <h1 class="titulo">A floresta sombria</h1>
        <a href="../mapa/mapa.php" class="sair"><img src="imgs/sair.png" alt="Sair" /></a>
    </header>
    <div class="main-content">
        <div class="question-container" id="questionContainer">
            <p class="instruction">Pronuncie</p>
            <div class="pronunciation-phrase-box">
                <span class="pronunciation-phrase" id="pronunciationText">I'm not lost in the forest.</span>
            </div>
            <div class="audio-control-container">
                <p id="pronunciation-label">Escutar pronúncia</p>
                <button class="speed-btn active" data-speed="1.0">1.0x</button>
                <button class="speed-btn" data-speed="0.5">0.5x</button>
            </div>
            <button class="mic-btn" id="micBtn">
                <img src="imgs/microfone.png" class="mic-icon" alt="">
            </button>
            <p id="feedback"></p>
        </div>
        <button id="next-phase-btn-outside" onclick="avancar()">Avançar</button>
    </div>
    <!-- XP -->
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
    <!-- DICIONÁRIO -->
    <div class="bottom-left">
        <button class="vocabulario-btn">
            <span>Dicionário</span>
            <img src="imgs/dicionario.png" alt="Vocabulário" />
        </button>
    </div>
    <!-- POÇÃO -->
    <div class="pocao">
        <span class="help">Precisa de ajuda?</span>
        <button class="pocao-btn">
            <img src="imgs/poção.png" alt="Poção" />
            <span class="btn-text">POÇÃO</span>
        </button>
    </div>
    <script>
// ============================================
// CONFIGURAÇÕES - FASE 1
// ============================================
const NOME_ALUNO = "<?php echo $nomeAluno; ?>";
const NUMERO_FASE = <?php echo $numero_fase; ?>;
const JOGO_NUMERO = <?php echo $jogo_numero; ?>;
const NOME_ATIVIDADE = "<?php echo $nome_atividade; ?>";
const TIPO_GRAMATICA = "<?php echo $tipo_gramatica; ?>";
const TIPO_HABILIDADE = "<?php echo $tipo_habilidade; ?>";
const XP_ATUAL_FASE = <?php echo $xp_atual_fase; ?>;
const XP_TOTAL_ACUMULADO = <?php echo $xp_total_jogo2; ?>;
const XP_MAXIMO_TOTAL = 40;

const phasePoints = { 
    correct: 4,
    wrong: -4
};

// ============================================
// ELEMENTOS DOM
// ============================================
const questionContainer = document.getElementById("questionContainer");
const feedback = document.getElementById("feedback");
const nextBtn = document.getElementById("next-phase-btn-outside");
const micBtn = document.getElementById("micBtn");
const pronunciationLabel = document.getElementById("pronunciation-label");
const speedBtns = document.querySelectorAll(".speed-btn");
const pronunciationText = document.getElementById("pronunciationText");
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

console.log('🎮 Fase 1 iniciada:', {
    xpFaseAtual: xpFaseAtual,
    xpTotalAcumulado: xpTotalAcumulado
});

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
    fetch('../mapa/salvar_xp2.php', {
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
    .catch(error => console.error('❌ Erro:', error));
}

// ============================================
// RECONHECIMENTO DE VOZ
// ============================================
let audio = new Audio();
audio.src = "sound/lostintheforest.wav";
let isRecording = false;
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

let speedVisible = false;

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

function normalize(text) {
    return text
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/[^\w\s]/gi, "")
        .trim();
}

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

function isSpeechCorrect(spokenText) {
    const correct = normalize(pronunciationText.textContent);
    const spoken = normalize(spokenText);
    const distance = levenshtein(spoken, correct);
    const similarity = 1 - distance / Math.max(spoken.length, correct.length);
    console.log("Similaridade:", similarity);
    return similarity >= 0.78;
}

micBtn.addEventListener("click", () => {
    if (!recognition) {
        alert("Reconhecimento de voz não suportado.");
        return;
    }
    if (isRecording) {
        recognition.stop();
        return;
    }
    if (!audio.paused) {
        audio.pause();
        audio.currentTime = 0;
    }
    isRecording = true;
    micBtn.classList.add("mic-recording");
    feedback.textContent = "";
    pronunciationLabel.textContent = "Gravando... Fale agora!";
    recognition.start();
});

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
            giveXP(true);
            salvarProgressoDetalhado(true);
            salvarXPNoBanco();
            salvarEstrelasFase(calcularEstrelas());
            nextBtn.style.display = "block";
            micBtn.style.pointerEvents = "none";
            pronunciationLabel.style.pointerEvents = "none";
            speedBtns.forEach(btn => btn.style.pointerEvents = "none");
        } else {
            feedback.textContent = "✖ Não foi desta vez. Tente novamente!";
            feedback.style.color = "#b71c1c";
            giveXP(false);
            salvarProgressoDetalhado(false);
            salvarXPNoBanco();


                             // ⭐ BLOQUEIA NOVAS TENTATIVAS
            micBtn.style.pointerEvents = "none";
            pronunciationLabel.style.pointerEvents = "none";
            speedBtns.forEach(btn => btn.style.pointerEvents = "none");
            
            // ⭐ MOSTRA BOTÃO AVANÇAR MESMO QUANDO ERRA
            nextBtn.style.display = "block";
            console.log("❌ Pronúncia incorreta - avançando para próxima fase");
        }


        pronunciationLabel.textContent = "Escutar pronúncia";
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
        window.location.href = '../mapa/mapa.php';
    }
}

    </script>
</body>
</html>