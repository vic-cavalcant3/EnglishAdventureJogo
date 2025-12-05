<?php
// Iniciar sessão e verificar login
require_once '../mapa/config.php';
verificarLogin();

// Pegar nome do usuário da sessão
$nomeAluno = $_SESSION['nome'] ?? $_SESSION['usuario_nome'] ?? 'Visitante';
$usuario_id = $_SESSION['id'] ?? $_SESSION['usuario_id'] ?? 0;

// ⭐ DEFINIR: Esta é a FASE 3 do Jogo 4 (Espelhos de Midgard)
$numero_fase = 3;
$jogo_numero = 4;

// ⭐ DEFINIR TIPO DA QUESTÃO
$tipo_gramatica = 'afirmativa';
$tipo_habilidade = 'writing'; // Tradução PT->EN é considerado writing
$nome_atividade = 'jogo4_fase3';

// ✅ BUSCAR XP ATUAL DESTA FASE DO JOGO 4
$xp_atual_fase = obterXPFase3($pdo, $usuario_id, $numero_fase); // Use obterXPFase2
$xp_total_jogo3 = obterXPTotal3($pdo, $usuario_id); // Use obterXPTotal2

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="icon" type="image/png" href="imgs/logo.png">
    <title>English Adventure </title>

    <link href="https://fonts.googleapis.com/css2?family=Irish+Grover&family=Itim&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
 

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
            background: #3b2a20;
            color: white;
            font-size: 18px;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .option-btn:hover {
            transform: scale(1.03);
            background: #2c1e15;
        }

        .option-btn.selected-correct {
            background: #4caf50; 
        }
        .option-btn.selected-wrong {
            background: #f44336; 
        }
        .option-btn.show-correct {
            background: #81c784; 
            border: 3px solid #388e3c; 
        }

        #next-phase-btn-outside {
            width: 280px; 
            background: #3b2a20; 
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
            background: #2c1e15; 
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
        <div class="question-container">
            <p class="instruction">Traduza para o inglês</p>
            <p class="question-phrase" id="questionPhrase">O dragão está forte agora?</p>

            <div class="options">
                <button class="option-btn" data-is-correct="false">A) Are the dragon strong now?</button>
                <button class="option-btn" data-is-correct="false">B) Am the dragon strong now?</button>
                <button class="option-btn" data-is-correct="true">C) Is the dragon strong now?</button>
            </div>

            <p id="feedback"></p>
        </div>
        
        <button id="next-phase-btn-outside" onclick="avancar()">Avançar</button>
    </div>

    <div class="xp-container">
        <div class="xp-info">
            <span>XP: <span id="xp-current">0/50</span></span>
            <span id="xp-gained" style="color: #90EE90; display: none;">+5</span>
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
const feedback = document.getElementById("feedback");
const options = document.querySelectorAll(".option-btn");
const nextBtn = document.getElementById("next-phase-btn-outside");
const questionContainer = document.querySelector(".question-container");
const correctAnswer = document.querySelector('[data-is-correct="true"]');
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
    
    // ✅ CORREÇÃO: Jogo 3 tem 50 XP máximo
    xpFaseAtual += xpChange;
    if (xpFaseAtual < 0) xpFaseAtual = 0;
    if (xpFaseAtual > 50) xpFaseAtual = 50; // MÁXIMO 50 XP POR FASE
    
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
// LÓGICA DE RESPOSTA
// ============================================
function disableOptions() {
    options.forEach(btn => {
        btn.disabled = true;
        btn.style.pointerEvents = 'none';
        btn.style.opacity = '0.7';
    });
}

options.forEach(btn => {
    btn.addEventListener("click", () => {
        if (jaRespondeu || questaoJaFoiPontuada) {
            console.log('⚠️ Questão já foi respondida e pontuada anteriormente');
            return;
        }

        jaRespondeu = true;
        questaoJaFoiPontuada = true;

        disableOptions();
        
        const isCorrect = btn.getAttribute("data-is-correct") === "true";

        if (isCorrect) {
            feedback.textContent = `✔ Resposta correta!`;
            feedback.style.color = "#388e3c";
            btn.classList.add("selected-correct");
             somAcerto.play(); // ⭐ Toca som de acerto
            giveXP(true);
        } else {
            feedback.textContent = `✖ Resposta errada! `;
            feedback.style.color = "#ac2a2a";
            btn.classList.add("selected-wrong");
             somErro.play(); // ⭐ Toca som de acerto
            giveXP(false);
            correctAnswer.classList.add("show-correct");
        }

        console.log('💾 Salvando no banco...');
        salvarProgressoDetalhado(isCorrect);
        salvarXPJogo3();

        questionContainer.classList.add("answered");
        nextBtn.style.display = "block";
    });
});

function avancar() {
    const proximaFase = NUMERO_FASE + 1;
    if (proximaFase <= 10) {
        window.location.href = 'fase' + proximaFase + '.php';
    } else {
        window.location.href = '../mapa/fases.php';
    }
}

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