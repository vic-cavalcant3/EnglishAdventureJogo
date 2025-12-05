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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
         <link rel="icon" type="image/png" href="../src/logo.png">
    <title>English Adventure </title>

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

        /* HEADER MODERNIZADO */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 24px;
            color: #fff;
            height: 60px;
            width: 100%;
            z-index: 1000;
            position: relative;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
           
        }

        .page-title {
            font-size: 1.8em;
            font-weight: 600;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            color: #FFFFFF;
            letter-spacing: 0.5px;
        }

        .left-icon-container {
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .left-icon-container:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .left-icon-container img {
            width: 20px;
            height: 20px;
            filter: brightness(0) invert(1);
        }

        .user-profile-container {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 25px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
        }

        .user-profile-container:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #7C604F;
            font-size: 1em;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .user-name {
            font-size: 0.95em;
            font-weight: 500;
            color: #FFFFFF;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
            max-width: 120px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Alternativa mais minimalista */
        .user-profile-minimal {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;

            border-radius: 20px;
            backdrop-filter: blur(8px);
            
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
        }

        .user-profile-minimal:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .user-avatar-minimal {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #7C604F;
            font-size: 0.9em;
            border: 2px solid rgba(255, 255, 255, 0.4);
        }

        .user-name-minimal {
            font-size: 0.9em;
            font-weight: 500;
            color: #FFFFFF;
        }

        /* Alternativa com ícone */
        .user-profile-icon {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
        }

        .user-profile-icon:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-1px);
        }

        .user-icon {
            width: 20px;
            height: 20px;
            filter: brightness(0) invert(1);
            opacity: 0.9;
        }

        .user-name-icon {
            font-size: 0.9em;
            font-weight: 500;
            color: #FFFFFF;
        }

        /* Resto do CSS permanece igual */
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
            border: 1px solid rgba(0, 0, 0, 1);
        }

        .phase-marker:hover:not(.locked) {
            transform: scale(1.1);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.4);
        }

        .phase-marker.locked {
            background-image: url(../src/imgs/cadeadofase01.png);
            z-index: 50px;
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

        .stars-container .star:nth-child(1) {
            transform: translateY(6px);
        }

        .stars-container .star:nth-child(2) {
            transform: translateY(0);
        }

        .stars-container .star:nth-child(3) {
            transform: translateY(6px);
        }

        .stars-container .star:only-child {
            transform: translateY(2px);
        }

        .star {
            width: 35px;
            height: 35px;
            margin: 0 -4px;
            position: relative;
            bottom: 20px;
            font-size: 14px;
            color: #FFD700;
            text-shadow: 0 0 3px rgba(0, 0, 0, 0.8);
        }

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
            .top-bar {
                padding: 10px 16px;
                height: 60px;
            }

            .page-title {
                font-size: 1.4em;
            }

            .user-name {
                max-width: 80px;
                font-size: 0.85em;
            }

            .user-avatar {
                width: 30px;
                height: 30px;
                font-size: 0.85em;
            }

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
            
         <a href="" class="user-profile-minimal">
                <div class="user-avatar-minimal">
                    <?php echo strtoupper(substr($usuario_nome, 0, 1)); ?>
                </div>
                <span class="user-name-minimal"><?php echo htmlspecialchars($usuario_nome); ?></span>
            </a>  
            


        </header>



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