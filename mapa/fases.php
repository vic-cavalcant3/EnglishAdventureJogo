<?php
require_once 'config.php';

verificarLogin();

$usuario_id = $_SESSION['usuario_id'];
$usuario_nome = $_SESSION['usuario_nome'] ?? $_SESSION['nome'] ?? 'Jogador';

// DEBUG: Verificar tabelas
error_log("🔍 Verificando tabelas para usuário: $usuario_id ($usuario_nome)");

// Verificar se a tabela fase_estrelas existe e tem dados
try {
    $table_check = $pdo->query("SHOW TABLES LIKE 'fase_estrelas'")->fetch();
    if ($table_check) {
        error_log("✅ Tabela fase_estrelas existe");
        
        // Verificar estrelas deste usuário
        $stmt = $pdo->prepare("SELECT fase, estrelas FROM fase_estrelas WHERE usuario_id = ?");
        $stmt->execute([$usuario_id]);
        $estrelas_usuario = $stmt->fetchAll();
        
        error_log("📊 Estrelas do usuário:");
        foreach ($estrelas_usuario as $estrela) {
            error_log("   Fase {$estrela['fase']}: {$estrela['estrelas']} estrelas");
        }
    } else {
        error_log("❌ Tabela fase_estrelas NÃO existe");
    }
} catch (Exception $e) {
    error_log("❌ Erro ao verificar tabelas: " . $e->getMessage());
}


$fases = [];
for ($i = 1; $i <= 4; $i++) {
    $desbloqueada = faseDesbloqueada($pdo, $usuario_id, $i);
    
    $fases[$i] = [
        'desbloqueada' => $desbloqueada,
        'estrelas' => obterEstrelasPorXP($pdo, $usuario_id, $i, $desbloqueada) // ⭐ PASSA info de desbloqueio
    ];
    
    error_log("🎮 Fase $i - Desbloqueada: " . ($fases[$i]['desbloqueada'] ? 'SIM' : 'NÃO') . ", Estrelas: " . $fases[$i]['estrelas']);
}
$paths = [
    1 => '../Learning/fase1.php',
    2 => '../AsRunasDeIdentidade/fase1.php',
    3 => '../AFlorestaEscura/fase1.php',
    4 => '../EspelhosDeMidgard/fase1.php'
];

$coresFases = [
    1 => '#194D6F',
    2 => '#682B10', 
    3 => '#8B2B28',
    4 => '#1D5529'
];
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
      <link rel="icon" type="image/png" href="../src/imgs/logo.png">
  <title>English Adventure</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        html,
        body {
            height: 100%;
            overflow: hidden;
        }

        .page-container {
            display: flex;
            flex-direction: column;
            width: 100%;
            height: 100%;
            position: relative;
            background-image: url('../src/imgs/fases.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: #000;
            color: #fff;
            height: 60px;
            width: 100%;
            z-index: 1000;
            position: relative;
        }

        .page-title {
            font-size: 1.8em;
            font-weight: 400;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
        }

        .left-icon-container {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .left-icon-container img {
            width: 20px;
            height: 20px;
        }

        .user-profile-button {
            background-color: #7C604F;
            color: #fff;
            font-size: 0.9em;
            padding: 8px 15px;
            border-radius: 30px;
            font-weight: 700;
            text-decoration: none;
            transition: opacity 0.2s;
            line-height: 1.2;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            cursor: pointer;
        }

        .content-area {
            flex-grow: 1;
            position: relative;
            width: 100%;
        }

        .map-frame-reference {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 65vh;
            height: 65vh;
            max-width: 700px;
            max-height: 700px;
        }

        .phase-marker {
            width: 86px;
            height: 86px;
            position: absolute;
            border-radius: 50%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            z-index: 20;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1 px solid rgba(0, 0, 0, 1);
        }

        .phase-marker:hover:not(.locked) {
            transform: scale(1.1);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.4);
        }

        .phase-marker.locked {
            background-image: url(../src/imgs/cadeadofase01.png);
            z-index: 50px;
            /* opacity: 0.6; */
            cursor: not-allowed;
            filter: grayscale(50%);
        }




        .phase-number {

            color: #194D6F;
            font-size: 2.5em;


            font-weight: 600;

        }

        .stars-container {
            position: absolute;
            bottom: 55px;
            left: 52%;
            transform: translateX(-50%);
            display: flex;

        }

        /* Estrela 1 – levemente mais baixa */
        .stars-container .star:nth-child(1) {
            transform: translateY(6px);
        }

        /* Estrela 2 – mais alta (topo da curva) */
        .stars-container .star:nth-child(2) {
            transform: translateY(0);
        }

        /* Estrela 3 – igual à primeira */
        .stars-container .star:nth-child(3) {
            transform: translateY(6px);
        }

        /* Se só tiver 1 estrelinha, ela fica centralizada e normal */
        .stars-container .star:only-child {
            transform: translateY(2px);
        }

        .star {
            width: 35px;
            height: 35px;
            margin: 0 -4px;
            /* 👉 aproxima! pode ajustar pra -4 ou -5 se quiser mais junto */
            position: relative;
            bottom: 20px;
            font-size: 14px;
            color: #FFD700;
            text-shadow: 0 0 3px rgba(0, 0, 0, 0.8);
        }

        /* Posições das fases no mapa */
        .marker-1 {
            border: #000 solid 1px;
            bottom: 55%;
            right: 8.5%;
            background-color: #6EACE7;
        }

        .marker-2 {
            color: #682B10;
            border: #000 solid 1px;
            bottom: 37%;
            left: 44.8%;
            background-color: #C6521E;

        }

        .marker-3 {
            border: #000 solid 1px;
            bottom: 7%;
            left: 46.2%;
            background-color: #FD716C;
        }

        .marker-4 {
            border: #000 solid 1px;
            bottom: 7.9%;
            left: 5.7%;
            background-color: #49A55D;
        }

        /* Fundo cinza arredondado igual à imagem */
        .lock-overlay {
            position: absolute;
            top: 20%;
            left: 10%;
            width: 80%;
            height: 60px;

            display: flex;
            align-items: center;
            justify-content: center;

        }

        /* Ícone branco centralizado */
        .lock-icon img {
            width: 80px;
            height: 80px;

        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;

            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background-image: url('../src/imgs/pergaminho.png');
            background-size: cover;
            background-position: center;
            padding: 60px 80px;
            border-radius: 20px;
            text-align: center;
            max-width: 500px;
            position: relative;
        }

        .modal-content h2 {
            font-size: 2em;
            color: #3d2817;
            margin-bottom: 20px;
        }

        .modal-content p {
            font-size: 1.1em;
            color: #3d2817;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .modal-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
        }

        .modal-btn {
            padding: 12px 30px;
            border: none;
            border-radius: 25px;
            font-size: 1em;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s;
            text-decoration: none;
            display: inline-block;
        }

        .modal-btn:hover {
            transform: scale(1.05);
        }

        .btn-sim {
            background-color: #4a3326;
            color: white;
        }

        .btn-retomar {
            background-color: transparent;
            color: #4a3326;
            border: 2px solid #4a3326;
        }

        .phase-marker.locked {
            background-color: rgba(120, 120, 120, 0.6) !important;
            /* cinza translúcido bonitão */
            border-color: #3a3a3a !important;
        }


        @media (max-width: 1024px) {
            .map-frame-reference {
                width: 80vw;
                height: 80vw;
            }

            .phase-marker {
                width: 90px;
                height: 90px;
            }

            .phase-number {
                font-size: 2em;
            }
        }

        @media (max-width: 600px) {
            .map-frame-reference {
                width: 90vw;
                height: 90vw;
            }

            .phase-marker {
                width: 70px;
                height: 70px;
            }

            .phase-number {
                font-size: 1.5em;
            }

            .top-bar {
                padding: 10px 15px;
            }

            .page-title {
                font-size: 1.4em;
            }

            .user-profile-button {
                font-size: 0.8em;
                padding: 6px 10px;
            }
        }
    </style>
</head>

<body>
    <div class="page-container">

        <header class="top-bar">
            <a href="../home/inicialcomconta.php" class="left-icon-container">
                <img src="../src/imgs/botaoVoltar.png" alt="Voltar">
            </a>

            <h1 class="page-title">Comece a jogar</h1>

            <a href="" class="user-profile-button">
                <span>
                    <?php echo htmlspecialchars($usuario_nome); ?>
                </span>
            </a>
        </header>

<!-- DEBUG VISUAL -->
<div style="position: fixed; top: 70px; left: 10px; background: rgba(0,0,0,0.8); color: white; padding: 10px; z-index: 10000; border-radius: 5px;">
    <strong>DEBUG - Usuário: <?php echo $usuario_nome; ?> (ID: <?php echo $usuario_id; ?>)</strong><br>
    
    <?php 
    $xp_fase2 = obterXPFase($pdo, $usuario_id, 2);
    $xp_total = obterXPTotal($pdo, $usuario_id);
    $estrelas_fase2 = obterEstrelasPorXP($pdo, $usuario_id, 2, true);
    ?>
    
    <strong>XP NO BANCO:</strong><br>
    - Fase 2 individual: <?php echo $xp_fase2; ?> XP<br>
    - <strong>XP TOTAL: <?php echo $xp_total; ?> XP</strong> ⭐<br>
    
    <strong>ESTRELAS CALCULADAS:</strong><br>
    - Usando XP TOTAL (<?php echo $xp_total; ?>) → <?php echo $estrelas_fase2; ?> estrelas<br>
    
    <strong>LÓGICA:</strong><br>
    - 0-1 XP TOTAL = 0 estrelas<br>
    - 2-4 XP TOTAL = 1 estrela ⭐<br>
    - 5-7 XP TOTAL = 2 estrelas ⭐⭐<br>
    - 8-10 XP TOTAL = 3 estrelas ⭐⭐⭐<br>
    
    <div style="color: lightgreen; margin-top: 5px;">
        ✅ <strong>RESULTADO: Com 10 XP TOTAL → 3 ESTRELAS!</strong>
    </div>
</div>

        <main class="content-area">
            <div class="map-frame-reference">
                <?php for ($i = 1; $i <= 4; $i++): ?>
                <a href="<?php echo $fases[$i]['desbloqueada'] ? $paths[$i] : 'javascript:void(0)'; ?>"
                    class="phase-marker marker-<?php echo $i; ?> <?php echo !$fases[$i]['desbloqueada'] ? 'locked' : ''; ?>"
                    <?php if (!$fases[$i]['desbloqueada']): ?>
                    onclick="event.preventDefault(); alert('Complete a fase anterior para desbloquear esta fase!');"
                    <?php endif; ?>>


                    <?php if ($fases[$i]['estrelas'] > 0): ?>
                    <div class="stars-container">
                        <?php for ($s = 0; $s < min($fases[$i]['estrelas'], 3); $s++): ?>
                        <img src="../src/imgs/star.png" class="star" alt="">
                        <?php endfor; ?>
                    </div>
                    <?php endif; ?>

                    <span class="phase-number" style="color: <?php echo $coresFases[$i]; ?>;">
                        <?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>
                    </span>

                    <?php if (!$fases[$i]['desbloqueada']): ?>
                    <div class="lock-overlay">
                        <div class="lock-icon">
                            <img src="../src/imgs/cadeadofase01.png" alt="">
                        </div>
                    </div>
                    <?php endif; ?>
                </a>
                <?php endfor; ?>
            </div>
        </main>

    </div>

    <div class="modal-overlay" id="modalSaida">
        <div class="modal-content">
            <h2>Atenção!!</h2>
            <p>Ao abandonar a partida, você não receberá a recompensa final. Você tem certeza que deseja parar de jogar?
            </p>
            <div class="modal-buttons">
                <button class="modal-btn btn-sim" onclick="sairJogo()">SIM</button>
                <button class="modal-btn btn-retomar" onclick="fecharModal()">DESEJO RETOMAR AO JOGO</button>
            </div>
        </div>
    </div>

    <script>
        function fecharModal() {
            document.getElementById('modalSaida').classList.remove('active');
        }

        function sairJogo() {
            window.location.href = '../paginicial/menu.php';
        }

        window.addEventListener('beforeunload', function (e) {
            const estaNaFase = window.location.pathname.includes('fase') &&
                !window.location.pathname.includes('fases.php');

            if (estaNaFase) {
                e.preventDefault();
                e.returnValue = 'Você tem certeza que deseja sair? Seu progresso pode ser perdido.';
                return e.returnValue;
            }
        });
    </script>
</body>

</html>