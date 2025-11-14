<?php
session_start();

// Configuração do banco de dados
$host = 'localhost';
$dbname = 'englishadventure';
$username = 'root';
$password = '';

$erro = '';
$sucesso = '';
$email_verificado = false;

// Processar redefinição de senha
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Etapa 1: Verificar email
    if (isset($_POST['verificar_email'])) {
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email)) {
            $erro = "Por favor, insira seu email.";
        } else {
            try {
                $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Verificar se o email existe
                $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
                $stmt->execute([$email]);
                
                if ($stmt->fetch()) {
                    $_SESSION['email_redefinir'] = $email;
                    $email_verificado = true;
                } else {
                    $erro = "Email não encontrado no sistema.";
                }
            } catch(PDOException $e) {
                $erro = "Erro ao conectar ao banco de dados.";
            }
        }
    }
    
    // Etapa 2: Redefinir senha
    if (isset($_POST['redefinir_senha'])) {
        $nova_senha = $_POST['nova-senha'] ?? '';
        $confirma_senha = $_POST['confirma-senha'] ?? '';
        $email = $_SESSION['email_redefinir'] ?? '';
        
        if (empty($nova_senha) || empty($confirma_senha)) {
            $erro = "Por favor, preencha todos os campos.";
            $email_verificado = true;
        } elseif (strlen($nova_senha) < 6) {
            $erro = "A senha deve ter pelo menos 6 caracteres.";
            $email_verificado = true;
        } elseif ($nova_senha !== $confirma_senha) {
            $erro = "As senhas não coincidem.";
            $email_verificado = true;
        } else {
            try {
                $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Atualizar senha
                $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE email = ?");
                $stmt->execute([$senha_hash, $email]);
                
                // Limpar sessão
                unset($_SESSION['email_redefinir']);
                
                // Redirecionar para login com mensagem de sucesso
                $_SESSION['mensagem_sucesso'] = "Senha redefinida com sucesso! Faça login com sua nova senha.";
                header('Location: login.php');
                exit();
            } catch(PDOException $e) {
                $erro = "Erro ao redefinir senha. Tente novamente.";
                $email_verificado = true;
            }
        }
    }
}

// Verificar se já tem email na sessão
if (isset($_SESSION['email_redefinir'])) {
    $email_verificado = true;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha - English Adventure</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    
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
            --cor-principal: #562617; 
            --cor-texto-principal: #562617;
            --cor-fundo-card: rgba(255, 255, 255, 0.5); 
            --cor-borda-input: #cccccc;
            --cor-borda: #382416; 
            --cor-botao: #FD716C;
            --cor-borda-botao-foco: #007bff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif; 
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            overflow: hidden; 
            position: relative;
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
            z-index: 30;
        }

        .icone-voltar-img {
            width: 20px; 
            height: 20px;
            left: 1px;
        }

        .botao-voltar-personalizado:hover {
            opacity: 0.9;
        }

        .container-fundo {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url(../../imgs/barcoNOrio.png);
            background-repeat: no-repeat;
            background-position: center bottom;
            background-size: cover; 
        }

        .titulo-principal {
            position: absolute;
            top: 100px; 
            text-align: center;
            z-index: 20; 
        }

        .titulo-principal h1 {
            font-size: 36px;
            color: var(--cor-principal);
            font-weight: 700; 
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1); 
        }

        .card-redefinir {
            background-color: var(--cor-fundo-card);
            padding: 50px 70px; 
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 750px; 
            text-align: center;
            position: relative; 
            z-index: 10;
            margin-top: 100px; 
        }

        .subtitulo {
            font-size: 16px;
            color: var(--cor-texto-principal);
            opacity: 0.8; 
            margin-bottom: 30px;
            line-height: 1.5;
            font-weight: 400; 
        }

        .mensagem-erro {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center; 
        }

        label {
            font-size: 14px;
            color: var(--cor-texto-principal);
            margin-bottom: 8px;
            font-weight: 700; 
            width: 100%;
            max-width: 500px;
            text-align: left; 
        }

        input[type="email"],
        input[type="password"] {
            padding: 12px 15px;
            margin-bottom: 20px;
            border: 1px solid var(--cor-borda-input);
            border-radius: 10px; 
            font-size: 16px;
            outline: none;
            transition: border-color 0.3s, box-shadow 0.3s;
            font-weight: 400; 
            width: 100%;
            max-width: 500px; 
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: var(--cor-botao);
            box-shadow: 0 0 5px rgba(255, 99, 71, 0.5); 
        }

        .btn-redefinir {
            background-color: var(--cor-botao);
            color: var(--cor-texto-principal); 
            padding: 12px 20px;
            border: 1px solid var(--cor-borda); 
            border-radius: 10px;
            cursor: pointer;
            font-size: 18px;
            font-weight: 700; 
            margin-top: 10px;
            transition: background-color 0.3s, border-color 0.3s, transform 0.2s;
            width: 100%;
            max-width: 400px; 
        }

        .btn-redefinir:focus {
            outline: none;
            border: 3px solid var(--cor-borda-botao-foco);
        }

        .btn-redefinir:hover {
            background-color: #e5533c; 
            transform: translateY(-1px); 
        }

        @media (max-width: 768px) {
            .card-redefinir {
                max-width: 90%; 
                padding: 30px;
                margin-top: 80px;
            }
            .titulo-principal {
                top: 50px;
            }
            .titulo-principal h1 {
                font-size: 30px;
            }
            .botao-voltar-personalizado {
                top: 10px;
                left: 10px;
            }
        }
    </style>
</head>
<body>
    
    <a href="login.php" class="botao-voltar-personalizado">
        <img src="../../imgs/botaoVoltar.png" alt="Voltar" class="icone-voltar-img">
    </a>
    
    <div class="container-fundo"></div>

    <div class="titulo-principal">
        <h1>Redefina a senha</h1>
    </div>

    <div class="card-redefinir">
        <?php if (!$email_verificado): ?>
            <!-- Etapa 1: Verificar Email -->
            <p class="subtitulo">Digite seu email para redefinir a senha</p>
            
            <?php if ($erro): ?>
                <div class="mensagem-erro"><?php echo htmlspecialchars($erro); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <label for="email">Email cadastrado</label>
                <input type="email" id="email" name="email" required>
                <button type="submit" name="verificar_email" class="btn-redefinir">Verificar Email</button>
            </form>
        <?php else: ?>
            <!-- Etapa 2: Criar Nova Senha -->
            <p class="subtitulo">Tudo bem esquecer a senha às vezes, ainda bem que é possível criar outra!</p>
            
            <?php if ($erro): ?>
                <div class="mensagem-erro"><?php echo htmlspecialchars($erro); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <label for="nova-senha">Crie uma senha nova</label>
                <input type="password" id="nova-senha" name="nova-senha" required minlength="6">

                <label for="confirma-senha">Confirme a senha</label>
                <input type="password" id="confirma-senha" name="confirma-senha" required minlength="6">

                <button type="submit" name="redefinir_senha" class="btn-redefinir">Redefinir</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>