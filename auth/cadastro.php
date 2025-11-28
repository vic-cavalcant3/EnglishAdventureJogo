<?php
session_start();

// Configuração do banco de dados
$host = 'localhost';
$dbname = 'englishadventure';
$username = 'root';
$password = '';

$erro = '';
$sucesso = '';

// Processar cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    
    // Validações
    if (empty($nome) || empty($email) || empty($senha)) {
        $erro = "Por favor, preencha todos os campos.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Por favor, insira um email válido.";
    } elseif (strlen($senha) < 6) {
        $erro = "A senha deve ter pelo menos 6 caracteres.";
    } else {
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Verificar se o email já existe
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $usuario_existente = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario_existente) {
                $erro = "Este email já está cadastrado. Por favor, use outro email ou faça login.";
            } else {
                // Hash da senha (limitado a 20 caracteres conforme seu banco)
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                
                // Inserir novo usuário (sem telefone, pois não é obrigatório)
                $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
                $stmt->execute([$nome, $email, $senha_hash]);
                
                $_SESSION['mensagem_sucesso'] = "Cadastro realizado com sucesso! Faça login para continuar.";
                header('Location: login.php');
                exit();
            }
        } catch(PDOException $e) {
            $erro = "Erro ao conectar ao banco de dados: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../src/imgs/logo.png">
    <title>English Adventure</title>

    <style>
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }

        body {
            animation: fadeIn 0.5s ease-in-out;
        }

        .fade-out {
            animation: fadeOut 0.5s ease-in-out forwards;
        }

        :root {
            --cor-principal: #333;
            --cor-secundaria: #60403c;
            --cor-fundo-card: rgba(255, 255, 255, 0.3);
            --cor-texto-principal: #562617;
            --cor-texto-titulos: #562617; 
            --cor-criar-borda: #562617;
            --cor-botao-criar: #F78070;
            --cor-input-borda: #ccc;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-image: url('../src/imgs/barcoNOrio.png'); 
            background-repeat: no-repeat;
            background-position: center bottom;
            background-size: cover;
            position: relative;
            overflow: hidden;
        }

        .mensagem-erro {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
            text-align: center;
        }

        .mensagem-sucesso {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
            text-align: center;
        }

        .botao-voltar-personalizado {
            display: flex;
            flex-direction: column; 
            align-items: center; 
            gap: 5px; 
            position: absolute;
            top: 10px;
            left: 10px;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-size: 1em;
            font-weight: bold;
            transition: background-color 0.3s, opacity 0.3s;
            line-height: 1; 
        }

        .icone-voltar-img {
            width: 20px; 
            height: 20px;
            left: 1px;
        }

        .botao-voltar-personalizado:hover {
            opacity: 0.9;
        }

        .main-container {
            width: 100%;
            height: 100vh;
            display: flex;
            flex-direction: column; 
            justify-content: center; 
            align-items: flex-end; 
            padding-right: 10%; 
            box-sizing: border-box;
        }

        .card-title {
            font-size: 2.5em;
            color: var(--cor-texto-principal);
            font-weight: 700;
            margin-bottom: 10px; 
            text-align: left;
            width: 450px; 
            max-width: 90%; 
            box-sizing: border-box;
        }
        
        .signup-card {
            width: 450px;
            max-width: 90%;
            padding: 40px;
            border-radius: 15px;
            background: var(--cor-fundo-card);
            backdrop-filter: blur(5px); 
            -webkit-backdrop-filter: blur(5px); 
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .input-group {
            margin-bottom: 25px;
            position: relative;
        }

        .input-group label {
            display: block;
            font-size: 1em;
            color: var(--cor-texto-titulos);
            margin-bottom: 8px;
            font-weight: 600;
        }

        .input-group input {
            width: 100%;
            padding: 12px;
            font-size: 1em;
            border: 1px solid var(--cor-input-borda);
            border-radius: 5px;
            box-sizing: border-box;
            outline: none;
            background-color: white; 
            padding-right: 45px;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 55px;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
        }
        
        .password-toggle img {
            width: 20px;
            height: 20px;
            opacity: 0.7;
            transition: opacity 0.3s;
        }
        
        .password-toggle:hover img {
            opacity: 1;
        }

        .btn-criar {
            width: 60%;
            padding: 15px;
            margin-top: 15px;
            margin-left: 20%;
            font-size: 1.2em;
            font-weight: bold;
            color: var(--cor-texto-titulos);
            background-color: var(--cor-botao-criar);
            border: 2px solid var(--cor-criar-borda);
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s, box-shadow 0.3s;
            box-shadow: 0 1px 0px rgba(172, 31, 31, 0.141);
        }

        .btn-criar:hover {
            background-color: #E66F60;
            box-shadow: 0 4px 0px rgba(172, 31, 31, 0.3);
        }
        
        .login-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: var(--cor-texto-principal);
            font-size: 0.9em;
            font-weight: 500;
            transition: color 0.3s;
            text-decoration: none;
        }

        .login-link:hover {
            color: var(--cor-secundaria);
        }

        @media (max-width: 900px) {
            .main-container {
                justify-content: flex-start; 
                align-items: center;
                padding: 50px 0; 
                padding-right: 0; 
            }
            .signup-card, .card-title {
                width: 90%;
                max-width: 450px;
                margin-left: auto;
                margin-right: auto;
            }
        }
    </style>
</head>
<body>
    
    <a href="../index.php" class="botao-voltar-personalizado">
        <img src="../src/imgs/botaoVoltar.png" alt="Voltar" class="icone-voltar-img">
    </a>

    <div class="main-container">
        <h2 class="card-title">Crie uma conta</h2>

        <div class="signup-card">
            <?php if ($erro): ?>
                <div class="mensagem-erro"><?php echo htmlspecialchars($erro); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['mensagem_sucesso'])): ?>
                <div class="mensagem-sucesso"><?php echo htmlspecialchars($_SESSION['mensagem_sucesso']); unset($_SESSION['mensagem_sucesso']); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="input-group">
                    <label for="nome">Seu nome</label>
                    <input type="text" id="nome" name="nome" placeholder="" required value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>">
                </div>

                <div class="input-group">
                    <label for="email">Digite o email</label>
                    <input type="email" id="email" name="email" placeholder="" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="input-group">
                    <label for="senha">Crie uma senha</label>
                    <input type="password" id="senha" name="senha" placeholder="" required minlength="6">
                    <button type="button" class="password-toggle" onclick="togglePassword('senha')">
                        <img src="../src/imgs/fluent_eye-off-16-regular.png" alt="Mostrar senha" id="senha-icon">
                    </button>
                </div>
                
                <button type="submit" class="btn-criar">Criar</button>
            </form>

            <a href="login.php" class="login-link">Fazer login</a>
        </div>
    </div>

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(inputId + '-icon');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.src = '../src/imgs/iconoir_eye.png';
                icon.alt = 'Ocultar senha';
            } else {
                input.type = 'password';
                icon.src = '../src/imgs/fluent_eye-off-16-regular.png';
                icon.alt = 'Mostrar senha';
            }
        }
    </script>
</body>
</html>