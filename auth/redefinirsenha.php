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
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erro = "Por favor, insira um email válido.";
        } else {
            try {
                $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Verificar se o email existe
                $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
                $stmt->execute([$email]);
                
                if ($stmt->fetch()) {
                    $_SESSION['email_redefinir'] = $email;
                    $_SESSION['tempo_verificacao'] = time();
                    $email_verificado = true;
                } else {
                    $erro = "Se este email estiver cadastrado, você poderá redefinir a senha.";
                }
            } catch(PDOException $e) {
                $erro = "Erro ao conectar ao banco de dados.";
                error_log($e->getMessage());
            }
        }
    }
    
    // Etapa 2: Redefinir senha
    if (isset($_POST['redefinir_senha'])) {
        $nova_senha = $_POST['nova-senha'] ?? '';
        $confirma_senha = $_POST['confirma-senha'] ?? '';
        $email = $_SESSION['email_redefinir'] ?? '';
        
        // Verificar se a sessão não expirou (15 minutos)
        if (!isset($_SESSION['tempo_verificacao']) || (time() - $_SESSION['tempo_verificacao']) > 900) {
            $erro = "Sessão expirada. Por favor, verifique seu email novamente.";
            unset($_SESSION['email_redefinir']);
            unset($_SESSION['tempo_verificacao']);
            $email_verificado = false;
        } elseif (empty($email)) {
            $erro = "Erro na verificação. Por favor, comece novamente.";
            $email_verificado = false;
        } elseif (empty($nova_senha) || empty($confirma_senha)) {
            $erro = "Por favor, preencha todos os campos.";
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
                unset($_SESSION['tempo_verificacao']);
                
                // Redirecionar para login com mensagem de sucesso
                $_SESSION['mensagem_sucesso'] = "Senha redefinida com sucesso! Faça login com sua nova senha.";
                header('Location: login.php');
                exit();
            } catch(PDOException $e) {
                $erro = "Erro ao redefinir senha. Tente novamente.";
                error_log($e->getMessage());
                $email_verificado = true;
            }
        }
    }
}

// Verificar se já tem email na sessão e se não expirou
if (isset($_SESSION['email_redefinir']) && isset($_SESSION['tempo_verificacao'])) {
    if ((time() - $_SESSION['tempo_verificacao']) <= 900) {
        $email_verificado = true;
    } else {
        unset($_SESSION['email_redefinir']);
        unset($_SESSION['tempo_verificacao']);
        $erro = "Sessão expirada. Por favor, verifique seu email novamente.";
    }
}

// Botão para cancelar e voltar
if (isset($_GET['cancelar'])) {
    unset($_SESSION['email_redefinir']);
    unset($_SESSION['tempo_verificacao']);
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
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
            background-image: url(../src/imgs/barcoNOrio.png);
            background-repeat: no-repeat;
            background-position: center bottom;
            background-size: cover; 
        }

        .titulo-principal {
            position: absolute;
            top: 80px; 
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
            padding: 40px 50px; 
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px; 
            text-align: center;
            position: relative; 
            z-index: 10;
            margin-top: 60px; 
            height: auto;
            min-height: 300px;
        }

        .subtitulo {
            font-size: 16px;
            color: var(--cor-texto-principal);
            opacity: 0.8; 
            margin-bottom: 25px;
            line-height: 1.5;
            font-weight: 500; 
        }

        .mensagem-erro {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #f5c6cb;
            font-size: 13px;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center; 
            width: 100%;
        }

        label {
            font-size: 13px;
            color: var(--cor-texto-principal);
            margin-bottom: 6px;
            font-weight: 600; 
            width: 100%;
            max-width: 400px;
            text-align: left; 
        }

        input[type="email"],
        input[type="password"] {
            padding: 10px 12px;
            margin-bottom: 15px;
            border: 1px solid var(--cor-borda-input);
            border-radius: 8px; 
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s, box-shadow 0.3s;
            font-weight: 400; 
            width: 100%;
            max-width: 400px; 
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: var(--cor-botao);
            box-shadow: 0 0 5px rgba(255, 99, 71, 0.5); 
        }

        .btn-redefinir {
            background-color: var(--cor-botao);
            color: var(--cor-texto-principal); 
            padding: 10px 15px;
            border: 1px solid var(--cor-borda); 
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600; 
            margin-top: 2px;
            transition: background-color 0.3s, border-color 0.3s, transform 0.2s;
            width: 100%;
            max-width: 350px; 
            margin-bottom: 0;
        }

        .btn-redefinir:focus {
            outline: none;
            border: 2px solid var(--cor-borda-botao-foco);
        }

        .btn-redefinir:hover {
            background-color: #e5533c; 
            transform: translateY(-1px); 
        }

        .btn-cancelar {
            background-color: transparent;
            color: var(--cor-texto-principal);
            padding: 8px;
            border: none;
            cursor: pointer;
            font-size: 13px;
            margin-top: 10px;
            text-decoration: underline;
        }

        .btn-cancelar:hover {
            opacity: 0.7;
        }

        @media (max-width: 768px) {
            .card-redefinir {
                max-width: 90%; 
                padding: 30px;
                margin-top: 70px;
            }
            .titulo-principal {
                top: 50px;
            }
            .titulo-principal h1 {
                font-size: 28px;
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
        <img src="../src/imgs/botaoVoltar.png" alt="Voltar" class="icone-voltar-img">
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
                <input type="email" id="email" name="email" required 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                <button type="submit" name="verificar_email" class="btn-redefinir">Verificar Email</button>
            </form>
            
            <a href="login.php" class="btn-cancelar">Voltar para o login</a>
            
        <?php else: ?>
            <!-- Etapa 2: Criar Nova Senha -->
            <p class="subtitulo">Tudo bem esquecer a senha às vezes, ainda bem que é possível criar outra!</p>
            
            <?php if ($erro): ?>
                <div class="mensagem-erro"><?php echo htmlspecialchars($erro); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <label for="nova-senha">Crie uma senha nova</label>
                <input type="password" id="nova-senha" name="nova-senha" required>

                <label for="confirma-senha">Confirme a senha</label>
                <input type="password" id="confirma-senha" name="confirma-senha" required>

                <button type="submit" name="redefinir_senha" class="btn-redefinir">Redefinir</button>
            </form>
            
            <a href="?cancelar=1" class="btn-cancelar">Cancelar</a>
        <?php endif; ?>
    </div>
</body>
</html>