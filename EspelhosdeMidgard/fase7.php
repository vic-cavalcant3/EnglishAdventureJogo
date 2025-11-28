<?php
// Iniciar sessão e verificar login
require_once '../mapa/config.php';
verificarLogin();

// Pegar nome do usuário da sessão
$nomeAluno = $_SESSION['nome'] ?? $_SESSION['usuario_nome'] ?? 'Visitante';
$usuario_id = $_SESSION['id'] ?? $_SESSION['usuario_id'] ?? 0;

// ⭐ DEFINIR: Esta é a FASE 7 do Jogo 4 (Espelhos de Midgard)
$numero_fase = 7;
$jogo_numero = 4;

// ⭐ DEFINIR TIPO DA QUESTÃO
$tipo_gramatica = 'interrogativa';
$tipo_habilidade = 'reading'; // Tradução é reading
$nome_atividade = 'jogo4_fase7';

// ✅ BUSCAR XP ATUAL DESTA FASE DO JOGO 4
$xp_atual_fase = obterXPFase3($pdo, $usuario_id, $numero_fase);
$xp_total_jogo4 = obterXPTotal3($pdo, $usuario_id);

// Debug (opcional - remova depois)
error_log("🎮 Jogo 4 - Fase $numero_fase | XP Fase: $xp_atual_fase | XP Total: $xp_total_jogo4");
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
            background: #388e3c; 
        }
        .option-btn.selected-wrong {
            background: #ac2a2a; 
        }
        .option-btn.show-correct {
            background: #388e3c; 
            border: 3px solid #388e3c; 
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

        .vocabulario-btn img, .pocao-btn img {
            width: 36px; height: 38px;
        }
        .vocabulario-btn img { margin-left: 60%; }
        .pocao-btn img { margin-right: 36%; }

        /* overlay e modal */
        #dictionaryOverlay, #pocaoOverlay { 
            position: fixed; top:0; left:0; width:100vw; height:100vh; 
            background: rgba(0,0,0,0.45); opacity: 0; pointer-events: none; 
            transition: 0.3s ease; z-index: 900; 
        }
        #dictionaryOverlay.show, #pocaoOverlay.show { 
            opacity: 1; pointer-events: auto; 
        }

        #dictionaryModal {
            position: fixed; top:50%; left:50%;
            width: 90%; height: 90%;
            transform: translate(-50%, -50%) scale(0.85);
            opacity: 0; pointer-events: none;
            background: transparent; border: none;
            transition: 0.35s ease; z-index: 1000;
        }
        #pocaoModal {
            position: fixed; top:50%; left:50%;
            width: 100%; height: 100%;
            transform: translate(-50%, -50%) scale(0.85);
            opacity: 0; pointer-events: none;
            background: transparent; border: none;
            transition: 0.35s ease; z-index: 1000;
            overflow-y: hidden;
        }
        #dictionaryModal.show, #pocaoModal.show { 
            transform: translate(-50%, -50%) scale(1); 
            opacity: 1; pointer-events: auto; 
        }
    </style>
</head>

<body>
    <header class="topo">
        <h1 class="titulo">Espelhos de Midgard</h1>
        <a href="../mapa/mapa.php" class="sair"><img src="imgs/sair.png" alt="Sair" /></a>
    </header>

    <div class="main-content">
        <div class="question-container" id="questionContainer">
            <p class="instruction">Traduza para o inglês</p>
            <p class="question-phrase" id="questionPhrase">Você é forte?</p>

            <div class="options">
                <button class="option-btn correct" data-value="A">A) Are you strong?</button>
                <button class="option-btn" data-value="B">B) Is you strong?</button>
                <button class="option-btn" data-value="C">C) Am you strong?</button>
            </div>

            <p id="feedback"></p>
        </div>
        
        <button id="nextBtn" class="send-btn" onclick="avancar()">Avançar</button>
    </div>

    <div class="xp-container">
        <div class="xp-info">
            <span>XP: <span id="xp-current">0/40</span></span>
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
    <iframe id="dictionaryModal" src="dicionario.html"></iframe>

    <div class="pocao">
        <span class="help">Precisa de ajuda?</span>
        <button class="pocao-btn" id="openPocaoBtn">
            <img src="imgs/poção.png" alt="Poção" /> 
            <span>POÇÃO</span>
        </button>
    </div>
    <div id="pocaoOverlay"></div>
    <iframe id="pocaoModal" src="poção/am.html"></iframe>

    <script>
// ============================================
// CONFIGURAÇÕES - JOGO 4 (Espelhos de Midgard)
// ============================================
const NOME_ALUNO = "<?php echo $nomeAluno; ?>";
const NUMERO_FASE = <?php echo $numero_fase; ?>;
const JOGO_NUMERO = <?php echo $jogo_numero; ?>;
const NOME_ATIVIDADE = "<?php echo $nome_atividade; ?>";
const TIPO_GRAMATICA = "<?php echo $tipo_gramatica; ?>";
const TIPO_HABILIDADE = "<?php echo $tipo_habilidade; ?>";
const XP_ATUAL_FASE = <?php echo $xp_atual_fase; ?>;
const XP_TOTAL_ACUMULADO = <?php echo $xp_total_jogo4; ?>;
const XP_MAXIMO_TOTAL = 40;

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
const options = document.querySelectorAll(".option-btn");
const xpFill = document.getElementById("xp-fill");
const dragon = document.getElementById("dragon");
const xpCurrent = document.getElementById("xp-current");
const xpGained = document.getElementById("xp-gained");

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
    
    xpFaseAtual += xpChange;
    if (xpFaseAtual < 0) xpFaseAtual = 0;
    if (xpFaseAtual > 40) xpFaseAtual = 40;
    
    xpTotalAcumulado += xpChange;
    if (xpTotalAcumulado < 0) xpTotalAcumulado = 0;
    if (xpTotalAcumulado > XP_MAXIMO_TOTAL) xpTotalAcumulado = XP_MAXIMO_TOTAL;
    
    xpGanhoNaRodadaAtual = xpChange;
    
    console.log(`📊 XP - Fase: ${xpFaseAtual}/40 | Total: ${xpTotalAcumulado}/${XP_MAXIMO_TOTAL}`);
    
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
// SALVAR XP NO BANCO (JOGO 4)
// ============================================
function salvarXPJogo4() {
    if (xpGanhoNaRodadaAtual === 0) {
        console.log('⚠️ Nenhum XP para salvar');
        return;
    }

    const formData = new FormData();
    formData.append('nomeAluno', NOME_ALUNO);
    formData.append('jogo', JOGO_NUMERO);
    formData.append('fase', NUMERO_FASE);
    formData.append('xp', xpGanhoNaRodadaAtual);

    console.log('📤 Salvando XP Jogo 4:', {
        aluno: NOME_ALUNO,
        jogo: JOGO_NUMERO,
        fase: NUMERO_FASE,
        xp_mudanca: xpGanhoNaRodadaAtual
    });

    fetch('../mapa/salvar_xp3.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log('📥 Resposta Jogo 4:', data);
        
        if (data.sucesso) {
            console.log(`✅ Salvo! XP Fase ${NUMERO_FASE}: ${data.xp_fase}/40 | XP Total: ${data.xp_total} | Estrelas: ${data.estrelas}`);
        } else {
            console.error('❌ Erro:', data.mensagem);
        }
    })
    .catch(error => console.error('❌ Erro na requisição:', error));
    
    xpGanhoNaRodadaAtual = 0;
}

// ============================================
// LÓGICA DO JOGO - QUESTÕES MÚLTIPLA ESCOLHA
// ============================================
function disableOptions() {
    options.forEach(btn => btn.disabled = true);
}

function processAnswer(selectedBtn) {
    if (jaRespondeu || questaoJaFoiPontuada) {
        console.log('⚠️ Questão já foi respondida e pontuada anteriormente');
        return;
    }

    jaRespondeu = true;
    questaoJaFoiPontuada = true;

    disableOptions();

    const isCorrect = selectedBtn.classList.contains("correct");

    if (isCorrect) {
        selectedBtn.classList.add("selected-correct");
        feedback.textContent = "✔ Resposta correta!";
        feedback.style.color = "#388e3c";
        giveXP(true);
    } else {
        selectedBtn.classList.add("selected-wrong");
        feedback.textContent = "✖ Resposta errada!";
        feedback.style.color = "#b71c1c";
        giveXP(false);

        // mostra qual é a certa
        document.querySelector(".option-btn.correct").classList.add("show-correct");
    }

    questionContainer.classList.add("answered");
    nextBtn.style.display = "block";

    console.log('💾 Salvando no banco...');
    salvarProgressoDetalhado(isCorrect);
    salvarXPJogo4();
}

function avancar() {
    const proximaFase = NUMERO_FASE + 1;
    if (proximaFase <= 10) {
        window.location.href = 'fase' + proximaFase + '.php';
    } else {
        window.location.href = '../mapa/mapa.php';
    }
}

// ============================================
// EVENT LISTENERS
// ============================================
options.forEach(btn => {
    btn.addEventListener("click", () => {
        if (btn.disabled) return;
        processAnswer(btn);
    });
});

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

const pocaoOverlay = document.getElementById("pocaoOverlay");
const pocaoModal = document.getElementById("pocaoModal");
const openPocaoBtn = document.getElementById("openPocaoBtn");

function openPocao() { 
    pocaoOverlay.classList.add("show"); 
    pocaoModal.classList.add("show"); 
    try{ pocaoModal.focus(); } catch(e){} 
}
function closePocao() { 
    pocaoOverlay.classList.remove("show"); 
    pocaoModal.classList.remove("show"); 
}
window.closePocao = closePocao;

openPocaoBtn.addEventListener("click", e => { 
    e.stopPropagation(); 
    openPocao(); 
});
pocaoOverlay.addEventListener("click", e => { 
    if(e.target === pocaoOverlay) closePocao(); 
});
document.addEventListener("keydown", e => { 
    if(e.key === "Escape") closePocao(); 
});
    </script>

</body>
</html>