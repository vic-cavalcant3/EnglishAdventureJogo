<?php
session_start();

// Configuração do banco de dados
$host = 'localhost';
$dbname = 'englishadventure';
$username = 'root';
$password = '';

$erro = '';
$sucesso = '';
$nome = 'Aluno'; // Define um valor padrão para evitar erro de variável indefinida

// Impedir acesso sem login
if (!isset($_SESSION['id'])) {
    header('Location: ../index.php');
    exit();
}

$usuarioId = $_SESSION['id'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Busca nome e email do usuário logado
    $stmt = $pdo->prepare("SELECT nome, email FROM usuarios WHERE id = ?");
    $stmt->execute([$usuarioId]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Se encontrou o usuário, atualiza o nome
    if ($usuario) {
        $nome = $usuario['nome'];
    }
} catch (PDOException $e) {
    die("Erro ao conectar: " . $e->getMessage());
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ../index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tela Inicial do Jogo</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.general_ci.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">

    <style>
        /* Reset Básico e Configuração da Fonte */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body, html {
            height: 100%;
            width: 100%;
            overflow: hidden; 
            /* Aplica a fonte Poppins ao corpo inteiro */
            font-family: 'Poppins', sans-serif; 
        }

        /* Container principal da tela, adaptado para o background do segundo exemplo */
        .tela-inicial {
            height: 100%;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center; 
            position: relative;
            
            /* Configuração da Imagem de Fundo (mantendo a referência do seu código) */
            background-image: url(../src/imgs/montanhas.png); /* **VERIFIQUE O CAMINHO DA SUA IMAGEM!** */
            background-size: cover; 
            background-position: center bottom; 
            background-repeat: no-repeat;
            background-attachment: fixed; 
        }
        
        /* Botão de Voltar (Seta) */
        .btn-voltar {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 30px;
            color: #27778b; /* Cor azul-escura */
            text-decoration: none;
            padding: 5px 10px;
        }

        /* Título "Olá, aluno!" */
        header {
            position: absolute;
            top: 25vh; /* Posição alta para o título */
            text-align: center;
        }

        h1 {
            font-size: 36px;
            color: #562617; 
            font-weight: 700; /* Poppins Bold para o título */
            letter-spacing: 1px;
        }

        /* Container dos botões */
        .opcoes-jogo {
            display: flex;
            flex-direction: column;
            gap: 15px; 
            width: 300px; 
            max-width: 90%; 
            position: absolute;
            top: 45vh; /* Posição dos botões */
        }
        
        /* Estilo Base dos Botões */
        .btn-jogo {
            padding: 15px 25px; 
            border: none;
            border-radius: 10px; 
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s;
            font-weight: 700; /* Poppins Bold */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3); 
            width: 100%; 
            letter-spacing: 0.5px; 
        }

        /* Estilo do Botão "Jogar online" (Claro) */
        .btn-jogo.online {
            background-color: #eeddab; /* Cor da imagem original (Bege/Amarelo) */
            color: #3b2f29; /* Marrom Escuro */
            border: 2px solid #562617; 
            box-shadow: 0 2px 2px rgba(0, 0, 0, 0.2); 
            font-weight: 600; 
            font-family: "Poppins";
        }

        /* Estilo do Botão "Jogar com tabuleiro" (Escuro) */
        .btn-jogo.tabuleiro {
            font-family: "Poppins";
            background-color: #a3795a; /* Cor da imagem original (Marrom Médio) */
            color: #fff6e7; /* Branco Suave */
            border: 2px solid #382416; 
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2); 
            font-weight: 600; 
        }

        /* Efeitos de Hover */
        .btn-jogo:hover {
            opacity: 1; 
            transform: translateY(-2px); 
            box-shadow: 0 8px 10px rgba(0, 0, 0, 0.3);
        }

        /* Área do rodapé com informações do usuário */
        .user-info {
            position: absolute;
            bottom: 30px;
            text-align: center;
            width: 100%;
        }

        .user-info p {
            color: #fff;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .btn-logout {
            text-decoration: underline;
            background: transparent;
            border: transparent;
            color: #fff;
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .btn-logout:hover {
            
            color: #bdb19dff;
        }

        /* Mensagens de erro/sucesso */
        .mensagem {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            text-align: center;
            font-weight: 600;
            position: absolute;
            top: 35vh;
            width: 80%;
            max-width: 400px;
        }

        .erro {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }

        .sucesso {
            background-color: #e8f5e8;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }

        /* Responsividade */
        @media (max-width: 400px) {
            .opcoes-jogo {
                width: 90%;
            }
            
            .user-info {
                bottom: 20px;
            }
        }
    </style>
</head>
<body>
    
    <audio id="som-clique" src="../src/sons/botao.mp3" preload="auto"></audio>
    
    <div class="tela-inicial">
   
        
        <header>
            <h1>Olá, <?php echo htmlspecialchars($nome); ?>!</h1>
        </header>

        <?php if ($erro): ?>
            <div class="mensagem erro"><?php echo $erro; ?></div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="mensagem sucesso"><?php echo $sucesso; ?></div>
        <?php endif; ?>

        <div class="opcoes-jogo">
            <a href="../mapa/fases.php" >
            <button class="btn-jogo online" id="btnOnline">Jogar online</button>
            </a>
            
            <button class="btn-jogo tabuleiro" id="btnTabuleiro">Jogar com tabuleiro</button>
        </div>

        <div class="user-info">
            <button class="btn-logout" onclick="sair()">Sair da Conta</button>
        </div>
    </div>

    <script>
        // 1. Pega o elemento de áudio
        const somClique = document.getElementById('som-clique');

        // 2. Define a função que toca o som
        function tocarSom() {
            // Reinicia o áudio para que ele possa ser tocado repetidamente rapidamente
            somClique.currentTime = 0; 
            somClique.play().catch(e => {
                console.warn("Erro ao tentar tocar o som (verifique o caminho SRC e as restrições do navegador):", e);
            });
        }

        // 3. Pega os botões que queremos sonorizar
        const botoes = document.querySelectorAll('.btn-jogo');

        // 4. Adiciona o evento de clique para cada botão
        botoes.forEach(botao => {
            if (botao) { 
                botao.addEventListener('click', tocarSom);
            }
        });

        // Função para sair da conta
        function sair() {
            if (confirm('Tem certeza que deseja sair da sua conta?')) {
                window.location.href = '?logout=true';
            }
        }
    </script>
</body>
</html>