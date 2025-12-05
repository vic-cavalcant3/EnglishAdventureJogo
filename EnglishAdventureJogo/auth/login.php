<?php
session_start();
// Configuração do banco de dados
$host = 'localhost';
$dbname = 'englishadventure';
$username = 'root';
$password = '';

$erro = '';
$sucesso = '';

// Processar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    
    if (empty($email) || empty($senha)) {
        $erro = "Por favor, preencha todos os campos.";
    } else {
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Buscar usuário por email - CORRIGIDO: sehha em vez de senha
            $stmt = $pdo->prepare("SELECT id, nome, senha FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario) {
                // Verificar senha
                if (password_verify($senha, $usuario['senha'])) {
                    // Login bem-sucedido
                    $_SESSION['id'] = $usuario['id'];
                    $_SESSION['nome'] = $usuario['nome'];
                    $_SESSION['email'] = $email;
                    
                    header('Location: ../home/inicialcomconta.php');
                    exit();
                } else {
                    $erro = "Email ou senha incorretos.";
                }
            } else {
                $erro = "Email ou senha incorretos.";
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
        :root {
            --cor-principal: #562617;
            --cor-secundaria: #60403c;
            --cor-fundo-card: rgba(255, 255, 255, 0.3);
            --cor-texto-principal: #562617;
            --cor-texto-titulos: #562617;
            --cor-criar-borda: #562617;
            --cor-botao-criar: #F78070;
            --cor-input-borda: #ccc;
        }

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

        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif; 
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-image: url(../src/imgs/barcoNOrio.png);
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center bottom;
            position: relative;
            overflow: hidden;
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
            color: var(--cor-texto-titulos);
            font-weight: 700;
            margin-bottom: 10px; 
            margin-right: -39px; 
            width: 450px; 
            max-width: 90%; 
            box-sizing: border-box;
        }

        .greeting-description {
            font-size: 1.1em;
            color: var(--cor-texto-principal);
            margin-bottom: 30px; 
            font-weight: 700;
            max-width: 500px; 
            width: 480px; 
            box-sizing: border-box;
            align-self: flex-end; 
            text-align: left;
            margin-right: 30px; 
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
            text-align: left;
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
        
        .forgot-password-link {
            display: block;
            text-align:left;
            margin-top: -15px; 
            margin-bottom: 25px;
            color: #562617; 
            font-size: 0.9em;
            font-weight: 500;
            transition: color 1.0s;
        }
        
        .forgot-password-link:hover {
            text-decoration: underline;
            color:#783838 ;
        }

        .btn-enter { 
            width: 60%;
            padding: 15px;
            margin-top: 20px;
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
        
        .btn-enter:hover {
            background-color: #E66F60;
            box-shadow: 0 4px 0px rgba(172, 31, 31, 0.3);
        }
        
        .signup-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color:#562617;
            font-size: 0.9em;
            font-weight: 500;
            transition: color 1.0s;
        }

        .signup-link:hover {
            color:#783838
        }

        @media (max-width: 900px) {
            .main-container {
                justify-content: flex-start; 
                align-items: center;
                padding: 50px 0; 
            }
            .signup-card, .card-title, .greeting-description {
                width: 90%;
                max-width: 450px;
                text-align: center; 
                margin-left: auto;
                margin-right: auto;
            }
            .forgot-password-link {
                text-align: center; 
            }
        }
    </style>
</head>
<body>
    
    <a href="../index.php" class="botao-voltar-personalizado">
        <img src="../src/imgs/botaoVoltar.png" alt="Voltar" class="icone-voltar-img">
    </a>

    <div class="main-container">
        
        <h2 class="card-title">Olá de novo!</h2>
        <p class="greeting-description">Coloque seus dados para começarmos a aventura juntos!</p>

        <div class="signup-card">
            <?php if ($erro): ?>
                <div class="mensagem-erro"><?php echo htmlspecialchars($erro); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['mensagem_sucesso'])): ?>
                <div class="mensagem-sucesso"><?php echo htmlspecialchars($_SESSION['mensagem_sucesso']); unset($_SESSION['mensagem_sucesso']); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="input-group">
                    <label for="email">Digite o email</label>
                    <input type="email" id="email" name="email" placeholder="" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="input-group">
                    <label for="senha">Digite a senha</label>
                    <input type="password" id="senha" name="senha" placeholder="" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('senha')">
                        <img src="../src/imgs/fluent_eye-off-16-regular.png" alt="Mostrar senha" id="senha-icon">
                    </button>
                </div>
                
                <a href="redefinirsenha.php" class="forgot-password-link">Esqueci a senha</a>
                
                <button type="submit" class="btn-enter">Entrar</button>
            </form>

            <a href="cadastro.php" class="signup-link">Cadastrar</a>
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