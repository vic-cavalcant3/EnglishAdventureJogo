<?php
// Iniciar sessão e verificar login
require_once '../mapa/config.php';
verificarLogin();

// Pegar nome do usuário da sessão
$nomeAluno = $_SESSION['nome'] ?? $_SESSION['usuario_nome'] ?? 'Visitante';
$usuario_id = $_SESSION['id'] ?? $_SESSION['usuario_id'] ?? 0;

// ⭐ DEFINIR: Esta é a FASE 1 do Jogo 2 (Floresta Escura)
$numero_fase = 1; // Fase 1 do Jogo 2
$jogo_numero = 2; // Jogo 2 (Floresta Escura)

// ⭐ DEFINIR TIPO DA QUESTÃO
$tipo_gramatica = 'interrogativa'; // 'afirmativa', 'interrogativa' ou 'negativa'
$tipo_habilidade = 'choice'; // 'speaking', 'reading', 'listening' ou 'writing'
$nome_atividade = 'jogo2_fase1'; // Identificador único

// ✅ BUSCAR XP ATUAL DESTA FASE DO JOGO 2
$xp_atual_fase = obterXPFase2($pdo, $usuario_id, $numero_fase);
$xp_total_jogo2 = obterXPTotal2($pdo, $usuario_id);

// Debug (opcional - remova depois)
error_log("🎮 Jogo 2 - Fase $numero_fase | XP Fase: $xp_atual_fase | XP Total: $xp_total_jogo2");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>As runas da identidade</title>
    <link href="https://fonts.googleapis.com/css2?family=Irish+Grover&family=Itim&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="icon" type="image/png" href="imgs/dragao.png" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif
        }

        body {
            background-image: url("imgs/espelho.png");
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
            background: #388e3c
        }

        .option-btn.selected-wrong {
            background: #ac2a2a
        }

        .option-btn.show-correct {
            background: #388e3c;
            border: 3px solid #388e3c
        }

        #next-phase-btn-outside {
            width: 280px;
            background: #4a2b17;
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
            background: #7a4c2a;
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

        .xp-container {
            position: absolute;
            left: 20px;
            bottom: 140px;
            width: 200px;
            height: 40px;
            z-index: 10
        }

        .xp-bar {
            width: 100%;
            height: 18px;
            background: #f4e3c7;
            border-radius: 10px;
            overflow: hidden
        }

        .xp-fill {
            height: 100%;
            width: 0;
            background: #4a2b17;
            transition: width .4s ease
        }

        .dragon {
            position: absolute;
            width: 50px;
            top: -40px;
            left: 0;
            padding-left: 10px;
            transition: left .4s ease
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
            background: #3b2a20
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
            padding: 8px 8px;
            border-radius: 80px;
            border: 2px solid #6a452d;
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
            background: #6a452d !important;
            color: #fff !important;
            border-color: #6a452d !important
        }

        .vocabulario-btn img {
            margin-left: 60%
        }

        .pocao-btn img {
            margin-right: 36%
        }

        /* botões dicionário e poção */
        .bottom-left {
            position: absolute;
            left: 0;
            bottom: 60px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .pocao {
            position: absolute;
            bottom: 60px;
            right: 0;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 5px;
        }

        .vocabulario-btn,
        .pocao-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.95);
            border: none;
            cursor: pointer;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.3);
            transition: 0.2s;
            font-size: 16px;
            font-weight: 600;
            padding: 20px 50px;
        }

        .vocabulario-btn {
            border-radius: 0 39px 39px 0;
            padding: 20px 80px;
        }

        .pocao-btn {
            border-radius: 39px 0 0 39px;
        }

        .vocabulario-btn:hover,
        .pocao-btn:hover {
            transform: scale(1.05);
        }

        .vocabulario-btn img {
            margin-left: 60%;
        }

        .pocao-btn img {
            margin-right: 36%;
        }

        /* overlay e modal igual ao dicionário */
        #dictionaryOverlay,
        #pocaoOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.45);
            opacity: 0;
            pointer-events: none;
            transition: 0.3s ease;
            z-index: 900;
        }

        #dictionaryOverlay.show,
        #pocaoOverlay.show {
            opacity: 1;
            pointer-events: auto;
        }

        #dictionaryModal {
            position: fixed;
            top: 50%;
            left: 50%;
            width: 90%;
            height: 90%;
            transform: translate(-50%, -50%) scale(0.85);
            opacity: 0;
            pointer-events: none;
            background: transparent;
            border: none;
            transition: 0.35s ease;
            z-index: 1000;
        }

        #pocaoModal {
            position: fixed;
            top: 50%;
            left: 50%;
            width: 100%;
            height: 100%;
            transform: translate(-50%, -50%) scale(0.85);
            opacity: 0;
            pointer-events: none;
            background: transparent;
            border: none;
            transition: 0.35s ease;
            z-index: 1000;
            overflow-y: hidden;
        }

        #dictionaryModal.show,
        #pocaoModal.show {
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
            pointer-events: auto;
        }

        /* Estilo para animação de ganho de XP */
        .dragon.gaining-xp {
            animation: xpBounce 0.5s ease;
        }

        @keyframes xpBounce {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        .xp-info {
            position: absolute;
            left: 20px;
            bottom: 180px;
            color: #4a2b17;
            font-size: 14px;
            font-weight: 600;
        }

        .xp-gain {
            display: none;
            font-weight: 700;
            margin-left: 5px;
        }
    </style>
</head>

<body>
    <header class="topo">
        <h1 class="titulo">Espelhos de Midgard</h1>
        <a href="../mapa/mapa.php" class="sair"><img src="imgs/sair.png" alt="Sair" /></a>
    </header>

    <div class="main-content">
        <div class="question-container">
            <p class="instruction">Escolha o que você ouviu</p>

            <div class="audio-box">
                <div class="speed-box left"><button class="speed-btn" data-speed="0.5">0.5x</button></div>
                <div class="audio-center">
                    <button class="play-btn" id="playBtn">▶</button>
                    <span class="speed-label" id="currentSpeedLabel">1x</span>
                </div>
                <div class="speed-box right"><button class="speed-btn" data-speed="1.5">1.5x</button></div>
            </div>

            <div class="options">
                <button class="option-btn">A) Is he your friend.</button>
                <button class="option-btn correct">B) Am I your friend</button>
                <button class="option-btn">C) Are I your friend.</button>
            </div>

            <p id="feedback"></p>
        </div>

        <button id="next-phase-btn-outside" onclick="avancar()">Avançar</button>
    </div>

    <div class="xp-info">
        <span id="xp-current">0/4</span>
        <span id="xp-gained" class="xp-gain"></span>
    </div>

    <div class="xp-container">
        <img src="imgs/dragao.png" class="dragon" id="dragon" />
        <div class="xp-bar">
            <div class="xp-fill" id="xp-fill"></div>
        </div>
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
        <button class="pocao-btn" id="openPocaoBtn">
            <img src="imgs/poção.png" alt="Poção" />
            <span>Poção</span>
        </button>
    </div>
    <div id="pocaoOverlay"></div>
    <iframe id="pocaoModal" src="poção/am.html"></iframe>

    <script>
        // ============================================
        // CONFIGURAÇÕES - FASE 1 - JOGO 2
        // ============================================
        const NOME_ALUNO = "<?php echo $nomeAluno; ?>";
        const USUARIO_ID = <?php echo $usuario_id; ?>;
        const NUMERO_FASE = <?php echo $numero_fase; ?>;
        const NOME_ATIVIDADE = "<?php echo $nome_atividade; ?>";
        const TIPO_GRAMATICA = "<?php echo $tipo_gramatica; ?>";
        const TIPO_HABILIDADE = "<?php echo $tipo_habilidade; ?>";
        const XP_ATUAL_FASE = <?php echo $xp_atual_fase; ?>;
        const XP_TOTAL_ACUMULADO = <?php echo $xp_total_jogo2; ?>;
        const XP_MAXIMO_TOTAL = 40; // Jogo 2 tem 40 XP máximo

        const phasePoints = { 
            correct: 1,  // +1 XP por acerto no Jogo 2
            wrong: -1    // -1 XP por erro no Jogo 2
        };

        // ============================================
        // ELEMENTOS DOM
        // ============================================
        const questionContainer = document.querySelector(".question-container"); 
        const feedback = document.getElementById("feedback");
        const options = document.querySelectorAll(".option-btn");
        const nextBtn = document.getElementById("next-phase-btn-outside");
        const xpFill = document.getElementById("xp-fill");
        const dragon = document.getElementById("dragon");
        const xpCurrent = document.getElementById("xp-current");
        const xpGained = document.getElementById("xp-gained");
        const playBtn = document.getElementById("playBtn");
        const speedBtns = document.querySelectorAll(".speed-btn");
        const currentSpeedLabel = document.getElementById("currentSpeedLabel");

        // ============================================
        // VARIÁVEIS DE XP
        // ============================================
        let xpFaseAtual = XP_ATUAL_FASE;
        let xpTotalAcumulado = XP_TOTAL_ACUMULADO;
        let xpGanhoNaRodadaAtual = 0;
        let jaRespondeu = false;

        // ============================================
        // ÁUDIO
        // ============================================
        let audio = new Audio(); 
        audio.src = "../som/amIyourfriend.mp3"; 
        audio.playbackRate = 1;

        function updateSpeed(s) { 
            audio.playbackRate = s; 
            currentSpeedLabel.textContent = `${s}x`; 
        }

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
                const ns = parseFloat(btn.dataset.speed);
                if (ns !== 1) btn.classList.add("speed-active");
                updateSpeed(ns);
                audio.currentTime = 0;
                if (!audio.paused) audio.play();
            });
        });

        updateSpeed(1);

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
            if (xpFaseAtual > 4) xpFaseAtual = 4; // Jogo 2: 4 XP máximo por fase
            
            xpTotalAcumulado += xpChange;
            if (xpTotalAcumulado < 0) xpTotalAcumulado = 0;
            if (xpTotalAcumulado > XP_MAXIMO_TOTAL) xpTotalAcumulado = XP_MAXIMO_TOTAL;
            
            xpGanhoNaRodadaAtual = xpChange;
            
            console.log(`📊 XP - Fase: ${xpFaseAtual}/4 | Total: ${xpTotalAcumulado}/${XP_MAXIMO_TOTAL}`);
            
            updateXPBar();
            animateXPGain(xpChange);
        }

        // ============================================
        // SALVAR PROGRESSO DETALHADO
        // ============================================
        function salvarProgressoDetalhado(acertou) {
            const formData = new FormData();
            formData.append('usuario_id', USUARIO_ID);
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
        // SALVAR XP NO BANCO (JOGO 2)
        // ============================================
        function salvarXPNoBanco() {
            if (xpGanhoNaRodadaAtual === 0) {
                console.log('⚠️ Nenhum XP para salvar');
                return;
            }

            const formData = new FormData();
            formData.append('usuario_id', USUARIO_ID);
            formData.append('fase', NUMERO_FASE);
            formData.append('xp', xpGanhoNaRodadaAtual);
            formData.append('jogo', 2); // Especificar que é Jogo 2

            console.log('📤 Salvando XP Jogo 2:', {
                usuario_id: USUARIO_ID,
                fase: NUMERO_FASE,
                xp_mudanca: xpGanhoNaRodadaAtual
            });

            fetch('../mapa/salvar_xp_jogo2.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('📥 Resposta XP Jogo 2:', data);
                
                if (data.sucesso) {
                    console.log(`✅ Salvo! XP Fase ${NUMERO_FASE}: ${data.xp_fase}/4 | XP Total: ${data.xp_total}/40`);
                } else {
                    console.error('❌ Erro:', data.mensagem);
                }
            })
            .catch(error => console.error('❌ Erro na requisição:', error));
            
            xpGanhoNaRodadaAtual = 0;
        }

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

                if (isCorrect) {
                    btn.classList.add("selected-correct");
                    feedback.textContent = "✔ Resposta correta!";
                    feedback.style.color = "#388e3c";
                    giveXP(true);
                } else {
                    btn.classList.add("selected-wrong");
                    feedback.textContent = "✖ Resposta errada!";
                    feedback.style.color = "#b71c1c";
                    giveXP(false);
                    document.querySelector(".option-btn.correct").classList.add("show-correct");
                }

                // ⭐ SALVAR TUDO
                salvarProgressoDetalhado(isCorrect);
                salvarXPNoBanco();

                questionContainer.classList.add("answered");
                document.querySelector('.main-content').classList.add('answered-down');
                nextBtn.style.display = "block";
                disableOptions();
            });
        });

        function avancar() {
            window.location.href = 'F3P2.html';
        }

        // ============================================
        // DICIONÁRIO E POÇÃO
        // ============================================
        const dictionaryOverlay = document.getElementById("dictionaryOverlay");
        const dictionaryModal = document.getElementById("dictionaryModal");
        const openDictionaryBtn = document.getElementById("openDictionaryBtn");
        const pocaoOverlay = document.getElementById("pocaoOverlay");
        const pocaoModal = document.getElementById("pocaoModal");
        const openPocaoBtn = document.getElementById("openPocaoBtn");

        function openDictionary() { 
            dictionaryOverlay.classList.add("show"); 
            dictionaryModal.classList.add("show"); 
            try { dictionaryModal.focus(); } catch (e) { } 
        }

        function closeDictionary() { 
            dictionaryOverlay.classList.remove("show"); 
            dictionaryModal.classList.remove("show"); 
        }

        function openPocao() { 
            pocaoOverlay.classList.add("show"); 
            pocaoModal.classList.add("show"); 
            try { pocaoModal.focus(); } catch (e) { } 
        }

        function closePocao() { 
            pocaoOverlay.classList.remove("show"); 
            pocaoModal.classList.remove("show"); 
        }

        window.closeDictionary = closeDictionary;
        window.closePocao = closePocao;

        openDictionaryBtn.addEventListener("click", e => { e.stopPropagation(); openDictionary(); });
        dictionaryOverlay.addEventListener("click", e => { if (e.target === dictionaryOverlay) closeDictionary(); });
        
        openPocaoBtn.addEventListener("click", e => { e.stopPropagation(); openPocao(); });
        pocaoOverlay.addEventListener("click", e => { if (e.target === pocaoOverlay) closePocao(); });
        
        document.addEventListener("keydown", e => { 
            if (e.key === "Escape") {
                closeDictionary(); 
                closePocao();
            } 
        });

        // ============================================
        // INICIALIZAÇÃO
        // ============================================
        updateXPBar();

        console.log('🎮 Fase Jogo 2 iniciada:', {
            fase: NUMERO_FASE,
            atividade: NOME_ATIVIDADE,
            tipo_gramatica: TIPO_GRAMATICA,
            tipo_habilidade: TIPO_HABILIDADE,
            xpFaseAtual: xpFaseAtual,
            xpTotalAcumulado: xpTotalAcumulado
        });
    </script>
</body>
</html>