<?php
session_start();

// Configuração do banco de dados
$host = 'localhost';
$dbname = 'englishadventure';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// Verificar se o usuário está logado
if (!isset($_SESSION['id'])) {
    die("Você precisa estar logado para acessar esta página. <a href='../paginicial/conta/login.php'>Fazer login</a>");
}

$usuario_id = $_SESSION['id'];
$pagina_atual = 1; // Esta é a página 1 de 20

// Buscar dados do usuário na tabela usuarios e jogo (usando JOIN)
$stmt = $pdo->prepare("
    SELECT u.nome, j.xp, j.pagina_atual 
    FROM usuarios u
    LEFT JOIN jogo j ON u.id = j.id
    WHERE u.id = ?
");
$stmt->execute([$usuario_id]);
$aluno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$aluno) {
    die("Usuário não encontrado");
}

// Se não existir registro na tabela jogo, criar um
if ($aluno['xp'] === null) {
    $stmt = $pdo->prepare("INSERT INTO jogo (id, nome, xp, pagina_atual) VALUES (?, ?, 0, 1)");
    $stmt->execute([$usuario_id, $aluno['nome']]);
    $xp_atual = 0;
} else {
    $xp_atual = $aluno['xp'];
}

$nome_aluno = $aluno['nome'];

// Processar resposta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resposta'])) {
    $resposta = $_POST['resposta'];
    $resposta_correta = 'am'; // Resposta correta: B) am
    
    if ($resposta === $resposta_correta) {
        // Resposta correta - adiciona 10 XP
        $novo_xp = min($xp_atual + 10, 200); // Máximo 200 XP
        
        $stmt = $pdo->prepare("UPDATE jogo SET xp = ?, pagina_atual = 2 WHERE id = ?");
        $stmt->execute([$novo_xp, $usuario_id]);
        
        $_SESSION['mensagem'] = "Correto! +10 XP";
        $_SESSION['acertou'] = true;
    } else {
        // Resposta errada - XP permanece o mesmo
        $_SESSION['mensagem'] = "Resposta incorreta. Continue tentando!";
        $_SESSION['acertou'] = false;
    }
    
    // Redirecionar para a próxima página (página 2)
    header('Location: page2.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>As runas da identidade</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow: hidden;
            width: 100vw;
            height: 100vh;
            position: relative;
        }

        /* Background com gradiente */
        .background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(180deg, #8B4867 0%, #C96B6B 35%, #E89B6F 60%, #F4C88A 85%, #FFE4B3 100%);
            z-index: -1;
        }

        /* Montanhas decorativas */
        .mountains {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 40%;
            z-index: 0;
        }

        .mountain {
            position: absolute;
            bottom: 0;
            width: 0;
            height: 0;
            border-style: solid;
        }

        .mountain-1 {
            left: 5%;
            border-width: 0 120px 250px 120px;
            border-color: transparent transparent #7A6B8E transparent;
        }

        .mountain-2 {
            left: 15%;
            border-width: 0 80px 180px 80px;
            border-color: transparent transparent #8B7A9E transparent;
        }

        .mountain-3 {
            right: 30%;
            border-width: 0 100px 200px 100px;
            border-color: transparent transparent #D4916B transparent;
        }

        .mountain-4 {
            right: 15%;
            border-width: 0 90px 220px 90px;
            border-color: transparent transparent #A8899B transparent;
        }

        .mountain-5 {
            right: 5%;
            border-width: 0 110px 240px 110px;
            border-color: transparent transparent #8B7A9E transparent;
        }

        /* Nuvens decorativas */
        .clouds {
            position: absolute;
            top: 15%;
            width: 100%;
            z-index: 1;
        }

        .cloud {
            position: absolute;
            background: rgba(255, 255, 255, 0.6);
            border-radius: 100px;
        }

        .cloud-1 {
            left: 2%;
            width: 120px;
            height: 50px;
        }

        .cloud-2 {
            right: 10%;
            width: 100px;
            height: 40px;
        }

        .cloud-3 {
            right: 25%;
            width: 80px;
            height: 35px;
        }

        /* Header */
        .header {
            position: absolute;
            top: 20px;
            left: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 100;
        }

        .avatar-container {
            background: linear-gradient(135deg, #D4916B 0%, #C67B5C 100%);
            padding: 3px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding-right: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .avatar {
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .avatar-name {
            color: white;
            font-weight: 600;
            font-size: 16px;
        }

        .title {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            color: white;
            font-size: 28px;
            font-weight: 600;
            z-index: 100;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .copy-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.5);
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 100;
        }

        /* Card central */
        .game-card {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(235, 215, 205, 0.95);
            border-radius: 30px;
            padding: 50px 60px;
            width: 550px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            z-index: 50;
        }

        .question-text {
            text-align: center;
            margin-bottom: 35px;
        }

        .question-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }

        .question-main {
            font-size: 32px;
            font-weight: 600;
            color: #2c2c2c;
        }

        .question-main .blank {
            border-bottom: 3px solid #2c2c2c;
            padding: 0 15px;
            display: inline-block;
            min-width: 100px;
        }

        /* Botões de resposta */
        .answers {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .answer-btn {
            width: 100%;
            padding: 18px 30px;
            border: none;
            border-radius: 50px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            color: white;
            text-align: left;
        }

        .answer-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }

        .answer-btn:active {
            transform: translateY(0);
        }

        .answer-a {
            background: linear-gradient(135deg, #D4916B 0%, #C67B5C 100%);
        }

        .answer-b {
            background: linear-gradient(135deg, #6B9D6E 0%, #5A8A5D 100%);
        }

        .answer-c {
            background: linear-gradient(135deg, #B88B6F 0%, #A67A5E 100%);
        }

        /* Viking mascote */
        .viking-mascot {
            position: absolute;
            bottom: 80px;
            left: 40px;
            width: 80px;
            height: 80px;
            z-index: 10;
        }

        /* Botões inferiores */
        .bottom-left {
            position: absolute;
            bottom: 30px;
            left: 30px;
            z-index: 100;
        }

        .dictionary-btn {
            background: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 50px;
            padding: 12px 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            font-size: 16px;
            font-weight: 600;
            color: #8B6B4A;
        }

        .bottom-right {
            position: absolute;
            bottom: 30px;
            right: 30px;
            z-index: 100;
        }

        .help-btn {
            background: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 50%;
            width: 70px;
            height: 70px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .help-text {
            font-size: 11px;
            color: #666;
        }

        .xp-display {
            position: absolute;
            top: 80px;
            right: 20px;
            background: rgba(255, 255, 255, 0.9);
            padding: 10px 20px;
            border-radius: 20px;
            font-weight: 600;
            color: #8B6B4A;
            z-index: 100;
        }
    </style>
</head>
<body>
    <div class="background"></div>
    
    <!-- Montanhas -->
    <div class="mountains">
        <div class="mountain mountain-1"></div>
        <div class="mountain mountain-2"></div>
        <div class="mountain mountain-3"></div>
        <div class="mountain mountain-4"></div>
        <div class="mountain mountain-5"></div>
    </div>

    <!-- Nuvens -->
    <div class="clouds">
        <div class="cloud cloud-1"></div>
        <div class="cloud cloud-2"></div>
        <div class="cloud cloud-3"></div>
    </div>

    <!-- Header -->
    <div class="header">
        <div class="avatar-container">
            <div class="avatar"></div>
            <span class="avatar-name"><?php echo htmlspecialchars($nome_aluno); ?></span>
        </div>
    </div>

    <div class="title">As runas da identidade</div>

    <div class="xp-display">XP: <?php echo $xp_atual; ?> / 200</div>

    <button class="copy-btn">📋</button>

    <!-- Viking mascote -->
    <div class="viking-mascot">
        <svg viewBox="0 0 100 100" style="width: 100%; height: 100%;">
            <circle cx="50" cy="50" r="45" fill="#D4916B"/>
            <circle cx="35" cy="45" r="8" fill="#2c2c2c"/>
            <circle cx="65" cy="45" r="8" fill="#2c2c2c"/>
            <path d="M 30 60 Q 50 70 70 60" stroke="#2c2c2c" stroke-width="3" fill="none"/>
            <polygon points="20,20 30,35 25,40" fill="#8B6B4A"/>
            <polygon points="80,20 70,35 75,40" fill="#8B6B4A"/>
        </svg>
    </div>

    <!-- Card do jogo -->
    <form method="POST" action="">
        <div class="game-card">
            <div class="question-text">
                <div class="question-label">Responda</div>
                <div class="question-main">
                    I <span class="blank"></span> a Viking.
                </div>
            </div>

            <div class="answers">
                <button type="submit" name="resposta" value="are" class="answer-btn answer-a">
                    A) are
                </button>
                <button type="submit" name="resposta" value="am" class="answer-btn answer-b">
                    B) am
                </button>
                <button type="submit" name="resposta" value="be" class="answer-btn answer-c">
                    C) be
                </button>
            </div>
        </div>
    </form>

    <!-- Botões inferiores -->
    <div class="bottom-left">
        <button class="dictionary-btn" onclick="window.open('dictionary.php', '_blank')">
            📖 Dicionário
        </button>
    </div>

    <div class="bottom-right">
        <button class="help-btn" onclick="alert('Escolha o verbo correto para completar a frase: I ___ a Viking.')">
            💗
            <span class="help-text">POÇÃO DE HELP</span>
        </button>
    </div>
</body>
</html>