<?php
// Iniciar sessão e verificar login
require_once '../mapa/config.php';
verificarLogin();

// Pegar nome do usuário da sessão
$nomeAluno = $_SESSION['nome'] ?? $_SESSION['usuario_nome'] ?? 'Visitante';
$usuario_id = $_SESSION['id'] ?? $_SESSION['usuario_id'] ?? 0;

// Definir qual fase é esta (ALTERE AQUI conforme a fase)
$numero_fase = 6; // MUDE PARA O NÚMERO DA FASE ATUAL

$tipo_gramatica = 'afirmativa'; // 'afirmativa', 'interrogativa' ou 'negativa'
$tipo_habilidade = 'listening'; // 'speaking', 'reading', 'listening' ou 'writing'
$nome_atividade = 'jogo2_fase6'; // Identificador único

// ⭐⭐ ADICIONAR AQUI: Verificar permissão para jogar


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
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif
        }

        body {
            background-image: url("imgs/fundo.png");
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
            display: block
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
            font-family: "Irish Grover", sans-serif;
            color: #fff;
            z-index: 10
        }

        .titulo {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            font-size: 28px;
            color: #ffffffd8;
            letter-spacing: 2px;
            font-family: "Irish Grover", sans-serif
        }

        .sair {
            position: absolute;
            right: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none
        }

        .sair img {
            width: 28px;
            height: 28px;
            transition: .2s
        }

        .sair img:hover {
            transform: scale(1.1)
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
        
        

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }
        }

        .dragon.gaining-xp {
            animation: pulse 0.2s ease;
        }

        /* ALTERAÇÃO AQUI: Adiciona transição para 'top' e 'transform' */
        .main-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 5;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            transition: top .3s ease, transform .3s ease;
        }

        /* NOVO: Classe para mover para baixo */
        .main-content.answered-down {
            top: 53%;
        }

        .question-container {
            width: 650px;
            background: rgba(255, 255, 255, .7);
            padding: 30px 40px;
            border-radius: 25px;
            backdrop-filter: blur(6px);
            box-shadow: 0 4px 25px rgba(0, 0, 0, .25);
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 5;
            transition: padding .3s ease
        }

        .question-container.answered {
            padding: 15px 40px
        }

        .instruction {
            font-size: 18px;
            color: #444;
            margin-bottom: 10px
        }

        .question-phrase {
            font-size: 28px;
            font-weight: 600;
            color: #222;
            margin-bottom: 35px
        }

        .options {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 20px
        }

        .option-btn {
            width: 100%;
            padding: 18px;
            border: none;
            border-radius: 40px;
            background: #B9794C;
            color: #fff;
            font-size: 18px;
            cursor: pointer;
            transition: .2s ease
        }

        .option-btn:hover {
            transform: scale(1.03);
            background: #94613c
        }

        .option-btn.selected-correct {
            background: #4caf50
        }

        .option-btn.selected-wrong {
            background: #f44336
        }

        .option-btn.show-correct {
            background: #81c784;
            border: 3px solid #388e3c
        }

        #next-phase-btn-outside {
            width: 280px;
            background: #B9794C;
            padding: 10px;
            border-radius: 40px;
            margin-top: 25px;
            display: none;
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: .2s ease;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, .2)
        }

        #next-phase-btn-outside:hover {
            background: #a36b41;
            transform: scale(1.03)
        }

        #feedback {
            margin-top: 15px;
            font-size: 20px;
            font-weight: 700
        }

        .bottom-left {
            position: absolute;
            left: 0;
            bottom: 60px;
            display: flex;
            align-items: center;
            gap: 10px
        }

        .pocao {
            position: absolute;
            bottom: 60px;
            right: 0;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 5px
        }

        .vocabulario-btn,
        .pocao-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: rgba(255, 255, 255, .95);
            border: none;
            cursor: pointer;
            box-shadow: 0 3px 6px rgba(0, 0, 0, .3);
            transition: .2s;
            font-size: 16px;
            font-weight: 600;
            padding: 20px 50px
        }

        .vocabulario-btn {
            border-radius: 0 39px 39px 0;
            padding: 20px 80px
        }

        .pocao-btn {
            border-radius: 39px 0 0 39px
        }

        .vocabulario-btn:hover,
        .pocao-btn:hover {
            transform: scale(1.05)
        }

        .help {
            color: white;
            font-size: 14px;
            font-weight: 500;
            margin-right: 30px
        }

        .audio-box {
            width: 100%;
            border-radius: 20px;
            padding: 18px 25px;
            margin-bottom: 18px;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative
        }

        .audio-center {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px
        }

        .play-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: none;
            background: #3b2a20;
            color: #fff;
            font-size: 24px;
            cursor: pointer;
            transition: .2s
        }

        .play-btn:hover {
            transform: scale(1.08);
            background: #2c1e15
        }

        .speed-label {
            font-size: 13px;
            font-weight: 600;
            color: #3b2a20
        }

        .speed-box {
            position: absolute;
            display: flex;
            gap: 10px;
            top: 25px
        }

        .speed-box.left {
            left: 150px
        }

        .speed-box.right {
            right: 150px
        }

        .speed-btn {
            padding: 6px 14px;
            border-radius: 12px;
            border: 2px solid #a67a57;
            background: #d6c1af;
            color: #3b2a20;
            cursor: pointer;
            transition: .2s;
            font-size: 15px;
            font-weight: 600
        }

        .speed-btn:hover {
            transform: scale(1.06);
            background: #c7b09b
        }

        .speed-active {
            background: #a67a57 !important;
            color: #fff !important;
            border-color: #3b2a20 !important
        }

        .vocabulario-btn img {
            margin-left: 60%
        }

        .pocao-btn img {
            margin-right: 36%
        }

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
        <h1 class="titulo">As runas da identidade </h1>
       <button id="sairBtn" class="sair">
   <img src="imgs/sair.png" alt="Sair"/>
</button>

    </header>

    <div class="main-content">
        <div class="question-container">
            <p class="instruction">Escolha o que você ouviu</p>

            <div class="audio-box">
                <div class="speed-box left"><button class="speed-btn" data-speed="0.5">0.5x</button></div>
                <div class="audio-center"><button class="play-btn" id="playBtn">▶</button><span class="speed-label"
                        id="currentSpeedLabel">1x</span></div>
                <div class="speed-box right"><button class="speed-btn" data-speed="1.5">1.5x</button></div>
            </div>

            <div class="options">
                <button class="option-btn">A) It is sunny.</button>
                <button class="option-btn correct">B) It is cold.</button>
                <button class="option-btn ">C) It are cold.</button>
            </div>

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

        // ⭐ Sons de feedback
        const somAcerto = document.getElementById("somAcerto");
        const somErro = document.getElementById("somErro");

        const phasePoints = {
            correct: 1,  // +1 XP por resposta certa
            wrong: -1    // -1 XP por resposta errada
        };

        // ============================================
        // ELEMENTOS DOM
        // ============================================
        const playBtn = document.getElementById("playBtn");
        const speedBtns = document.querySelectorAll(".speed-btn");
        const currentSpeedLabel = document.getElementById("currentSpeedLabel");
        const options = document.querySelectorAll(".option-btn");
        const feedback = document.getElementById("feedback");
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
            // ⭐ MUDANÇA: Envia o XP FINAL da fase, não a mudança
            const formData = new FormData();
            formData.append('nomeAluno', NOME_ALUNO);
            formData.append('fase', NUMERO_FASE);
            formData.append('xp', xpFaseAtual); // ✅ Envia o XP total da fase (0-10)

            console.log('📤 Salvando XP:', {
                aluno: NOME_ALUNO,
                fase: NUMERO_FASE,
                xp_final_fase: xpFaseAtual // ✅ Sempre entre 0-10
            });

            fetch('../mapa/salvar_xp.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('📥 Resposta XP:', data);
                
                if (data.sucesso) {
                    console.log(`✅ Salvo! XP Fase ${NUMERO_FASE}: ${data.xp_fase}/10 | XP Total: ${data.xp_total}`);
                } else {
                    console.error('❌ Erro:', data.mensagem);
                }
            })
            .catch(error => console.error('❌ Erro na requisição:', error));
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
        // SISTEMA DE ÁUDIO
        // ============================================
        let audio = new Audio();
        // ALTERE O CAMINHO DO ÁUDIO CONFORME SUA FASE
        audio.src = "sound/itiscold.mpeg";
        audio.playbackRate = 1;

        function updateSpeed(speed) {
            audio.playbackRate = speed;
            currentSpeedLabel.textContent = `${speed}x`;
        }
        updateSpeed(1);

        playBtn.addEventListener("click", () => {
            if (!audio.src) return;
            if (audio.paused) {
                audio.play();
                playBtn.textContent = "⏸";
            } else {
                audio.pause();
                playBtn.textContent = "▶";
            }
        });

        audio.addEventListener("ended", () => playBtn.textContent = "▶");

        speedBtns.forEach(btn => {
            btn.addEventListener("click", () => {
                speedBtns.forEach(b => b.classList.remove("speed-active"));
                const newSpeed = parseFloat(btn.dataset.speed);
                if (newSpeed !== 1) btn.classList.add("speed-active");
                updateSpeed(newSpeed);
                audio.currentTime = 0;
                if (!audio.paused) audio.play();
            });
        });

        // ============================================
        // LÓGICA DA QUESTÃO
        // ============================================
        function disableOptions() {
            options.forEach(b => b.disabled = true);
        }

        options.forEach(btn => {
            btn.addEventListener("click", () => {
                if (jaRespondeu) return;
                jaRespondeu = true;

                const isCorrect = btn.classList.contains("correct");

                disableOptions();

                if (isCorrect) {
                    btn.classList.add("selected-correct");
                    feedback.textContent = "✔ Resposta correta!";
                    feedback.style.color = "#388e3c";
                    somAcerto.play(); // ⭐ Toca som de acerto
                    giveXP(true);
                } else {
                    btn.classList.add("selected-wrong");
                    feedback.textContent = "✖ Resposta errada!";
                    feedback.style.color = "#b71c1c";
                    somErro.play(); // ⭐ Toca som de erro
                    giveXP(false);
                    document.querySelector(".option-btn.correct").classList.add("show-correct");
                }

                document.querySelector('.question-container').classList.add('answered');
                document.querySelector('.main-content').classList.add('answered-down');
                nextBtn.style.display = 'block';

                // ⭐ SALVAR TUDO NA ORDEM CORRETA
                salvarProgressoDetalhado(isCorrect);
                salvarXPNoBanco();
                salvarEstrelasFase(calcularEstrelas());
            });
        });

        function avancar() {
            // ALTERE PARA A PRÓXIMA FASE
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