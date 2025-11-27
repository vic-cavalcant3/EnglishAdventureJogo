<?php
// Iniciar sessão e verificar login
require_once '../mapa/config.php';
verificarLogin();

// Pegar nome do usuário da sessão
$nomeAluno = $_SESSION['nome'] ?? $_SESSION['usuario_nome'] ?? 'Visitante';
$usuario_id = $_SESSION['id'] ?? $_SESSION['usuario_id'] ?? 0;

// Definir qual fase é esta (ALTERE AQUI conforme a fase)
$numero_fase = 4; // MUDE PARA O NÚMERO DA FASE ATUAL

$tipo_gramatica = 'afirmativa'; // 'afirmativa', 'interrogativa' ou 'negativa'
$tipo_habilidade = 'writing'; // 'speaking', 'reading', 'listening' ou 'writing'
$nome_atividade = 'jogo2_fase4'; // Identificador único

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
            width: 600px; 
            padding: 30px;
            border-radius: 25px;
            background: rgba(255,255,255,0.65);
            backdrop-filter: blur(6px);
            box-shadow: 0 4px 25px rgba(0,0,0,0.2);
            text-align: center;
            display: flex; flex-direction: column;
            justify-content: center; align-items: center;
            z-index: 5;
        }
        .instruction {
            font-size: 18px; margin-bottom: 5px; color: #555;
        }
        .question-phrase {
            font-size: 30px; font-weight: 600;
            margin-bottom: 30px;
            color: #333;
        }

        .input-wrapper {
            position: relative; width: 80%; max-width: 400px;
            margin-top: 20px;
        }
        
        .text-input {
            width: 100%; 
            height:85%;
            padding: 15px 20px; padding-right: 60px;
            border: 2px solid #a3a3a3;
            border-radius: 30px; font-size: 18px;
            outline: none; transition: 0.3s ease;
        }
        .text-input:focus {
            border-color: #865f3b;
            box-shadow: 0 0 0 3px rgba(134,95,59,0.3);
        }
        .keyboard-icon {
            position: absolute; right: 20px; top: 50%;
            transform: translateY(-50%);
            width: 28px; height: 28px; cursor: pointer;
            filter: brightness(0.6); transition: 0.2s;
        }
        .keyboard-icon:hover { filter: brightness(0.4); }

        .vocabulario-btn img, .pocao-btn img {
            width: 36px; height: 38px;
        }
        .vocabulario-btn img { margin-left: 60%; }
        .pocao-btn img { margin-right: 36%; }

        #feedback {
            margin-top: 15px;
            font-size: 18px; font-weight: bold;
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

        .overlay {
            position: fixed; top: 0; left: 0;
            width: 100vw; height: 100vh;
            background: rgba(0,0,0,0.816);
            display: none; justify-content: center; align-items: center;
            z-index: 1000;
        }
        .overlay.show { display: flex; }
        .overlay-content { max-width: 90%; max-height: 90%; animation: overlayAppear 0.3s ease-out; }
        .overlay-content img { max-width: 100%; max-height: 90vh; object-fit: contain; }

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
            position: relative; 
            width: 40px;
            top: -62px;   
            left: 50px;
            transition: left 0.8s cubic-bezier(0.4, 0.0, 0.2, 1);
            z-index: 11;
        }
        
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .dragon.gaining-xp {
            animation: pulse 0.2s ease;
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
            background: #B9794C; 
            color: white;
            transition: 0.2s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .send-btn:hover {
            transform: scale(1.03); 
            background: #a36b41; 
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

        @keyframes overlayAppear {
            from { opacity: 0; transform: scale(0.8); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>
</head>

<body>
    <header class="topo">
        <h1 class="titulo">As runas da identidade</h1>
        <a href="../mapa/mapa.php" class="sair"><img src="imgs/sair.png" alt="Sair" /></a>
    </header>

    <div class="main-content">
        <div class="question-container">
            <p class="instruction">Reescreva na afirmativa</p>
            <p class="question-phrase" id="questionPhrase">She is not my teacher.</p>

            <div class="input-wrapper">
                <input type="text" class="text-input" id="answerInput" placeholder="Write" />
                <img src="imgs/teclado.png" alt="Teclado" class="keyboard-icon" />
            </div>

            <button id="sendBtn" class="send-btn">Enviar</button>
            <p id="feedback"></p>
        </div>
        
        <button id="next-phase-btn-outside" onclick="avancar()">Avançar</button>
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

    <div class="overlay" id="overlay">
        <div class="overlay-content">
            <img src="imgs/cartas/inicio.png" alt="Troll" />
        </div>
        <button onclick="document.getElementById('overlay').classList.remove('show')">Fechar</button>
    </div>

    <script>
// ============================================
// CONFIGURAÇÕES - FASE X (ALTERE CONFORME A FASE)
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
    correct: 1,  // +1 XP por resposta certa
    wrong: -1    // -1 XP por resposta errada
};

// ============================================
// ELEMENTOS DOM
// ============================================
const answerInput = document.getElementById("answerInput");
const feedback = document.getElementById("feedback");
const sendBtn = document.getElementById("sendBtn");
const nextBtn = document.getElementById("next-phase-btn-outside");
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

// Atualizar barra inicial
updateXPBar();

console.log('🎮 Fase iniciada:', {
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
// FUNÇÕES DE XP (IGUAL ÀS OUTRAS FASES)
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
    
    // Atualizar XP da fase atual
    xpFaseAtual += xpChange;
    if (xpFaseAtual < 0) xpFaseAtual = 0;
    if (xpFaseAtual > 10) xpFaseAtual = 10;
    
    // Atualizar XP total
    xpTotalAcumulado += xpChange;
    if (xpTotalAcumulado < 0) xpTotalAcumulado = 0;
    if (xpTotalAcumulado > XP_MAXIMO_TOTAL) xpTotalAcumulado = XP_MAXIMO_TOTAL;
    
    xpGanhoNaRodadaAtual = xpChange;
    
    console.log(`📊 XP - Fase: ${xpFaseAtual}/10 | Total: ${xpTotalAcumulado}/${XP_MAXIMO_TOTAL}`);
    
    updateXPBar();
    animateXPGain(xpChange);
}

// ============================================
// SALVAR XP NO BANCO
// ============================================
function salvarXPNoBanco() {
    if (xpGanhoNaRodadaAtual === 0) {
        console.log('⚠️ Nenhum XP para salvar');
        return;
    }

    const formData = new FormData();
    formData.append('nomeAluno', NOME_ALUNO);
    formData.append('fase', NUMERO_FASE);
    formData.append('xp', xpGanhoNaRodadaAtual);

    console.log('📤 Salvando XP:', {
        aluno: NOME_ALUNO,
        fase: NUMERO_FASE,
        xp_mudanca: xpGanhoNaRodadaAtual
    });

    fetch('../mapa/salvar_xp.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log('📥 Resposta:', data);
        
        if (data.sucesso) {
            console.log(`✅ Salvo! XP Fase ${NUMERO_FASE}: ${data.xp_fase}/10 | XP Total: ${data.xp_total}`);
        } else {
            console.error('❌ Erro:', data.mensagem);
        }
    })
    .catch(error => console.error('❌ Erro na requisição:', error));
    
    xpGanhoNaRodadaAtual = 0;
}

// ============================================
// SALVAR ESTRELAS
// ============================================
function calcularEstrelas() {
    let estrelas = 0;
    
    if (xpFaseAtual >= 8) {
        estrelas = 3;
    } else if (xpFaseAtual >= 5) {
        estrelas = 2;
    } else if (xpFaseAtual >= 2) {
        estrelas = 1;
    }
    
    console.log(`⭐ Estrelas: ${estrelas} (XP fase: ${xpFaseAtual}/10)`);
    return estrelas;
}

function salvarEstrelasFase(estrelas) {
    const formData = new FormData();
    formData.append('nomeAluno', NOME_ALUNO);
    formData.append('fase', NUMERO_FASE);
    formData.append('estrelas', estrelas);

    console.log('⭐ Salvando estrelas:', estrelas);

    fetch('../mapa/salvar_estrelas_fase.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => console.log('⭐ Estrelas salvas:', data))
    .catch(error => console.error('❌ Erro:', error));
}

// ============================================
// LÓGICA DA QUESTÃO
// ============================================
function processAnswer() {
    if (jaRespondeu) return;

    const userAnswer = answerInput.value.trim().toLowerCase();
    
    // RESPOSTAS ACEITAS (ALTERE CONFORME SUA FASE)
    const acceptedAnswers = [
        "she is my teacher.",
        "she is my teacher",
    ].map(ans => ans.toLowerCase());

    let isCorrect = acceptedAnswers.includes(userAnswer);
    jaRespondeu = true;

    // Esconder botão enviar
    sendBtn.style.display = 'none';
    
    if (isCorrect) {
        feedback.textContent = "✔ Resposta correta!";
        feedback.style.color = "#2e7d32";
        giveXP(true);
    } else {
        const correctAnswer = "She is my teacher.";
        feedback.textContent = `✖ Resposta errada! O correto é: ${correctAnswer}`;
        feedback.style.color = "#b71c1c";
        giveXP(false);
    }

    // Desabilitar input
    answerInput.disabled = true;
    document.querySelector(".keyboard-icon").style.pointerEvents = "none";
    
    // Mostrar botão avançar
    nextBtn.style.display = 'block';

            // ⭐ SALVAR TUDO NA ORDEM CORRETA
        salvarProgressoDetalhado(isCorrect);  // ✅ ADICIONAR ESTA LINHA
        salvarXPNoBanco();
        salvarEstrelasFase(calcularEstrelas());

    // ⭐ SALVAR NO BANCO
    salvarXPNoBanco();
    const estrelas = calcularEstrelas();
    salvarEstrelasFase(estrelas);
}

function avancar() {
    // ALTERE PARA A PRÓXIMA FASE
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
answerInput.addEventListener("keypress", function (event) {
    if (event.key === "Enter") {
        event.preventDefault();
        processAnswer();
    }
});

sendBtn.addEventListener("click", processAnswer);

document.querySelector(".vocabulario-btn").addEventListener("click", () => {
    document.getElementById("overlay").classList.add("show");
});

document.querySelector(".pocao-btn").addEventListener("click", () => {
    alert("Poção de ajuda ativada!");
});
    </script>
</body>
</html>