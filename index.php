<?php
session_start();




?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
      <link rel="icon" type="image/png" href="src/imgs/logo.png">
  <title>English Adventure</title>
    <style>
        /* (Seu CSS completo, que você já tem) */
        /* ... */
        
        /* NOVO: Estilo para remover a decoração de link dos botões */
        .button-group a {
            text-decoration: none; /* Remove o sublinhado do link */
            display: block; /* Garante que o link ocupe toda a largura */
        }
        @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes fadeOut {
    from { opacity: 1; }
    to { opacity: 0; }
}

/* Aplica o fade-in ao carregar a página */
body {
    animation: fadeIn 0.5s ease-in-out; /* 0.5s é o tempo da transição */
}

/* Classe que será adicionada ao clicar para iniciar o fade-out */
.fade-out {
    animation: fadeOut 0.5s ease-in-out forwards;
}
        /* (Resto do seu CSS...) */
        body {
            /* ... (Seu background e layout) ... */
            font-family: 'Poppins'; 
            background-image: url(src/imgs/montanhas.png);
            background-size: cover; 
            /* ... (Resto do body CSS) ... */
            min-height: 100vh;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center; 
            flex-direction: column;
            background-color: #333; 
        }

        /* ------------------------------------------- */
        /* REMOVIDO: As classes de peso da fonte (poppins-thin, etc.) */
        /* ------------------------------------------- */
        
        .logo {
            /* Ajustei a margem para 80px */
            margin-bottom: 80px; 
            margin-right: 80px;
            /* Adicionei a largura fixa da logo para melhor controle em telas grandes */
            width: 300px; 
            max-width: 80%;
        }

        .logo img {
            width: 120%; 
            height: auto; 
            display: block;
            margin: 0 auto; 
        }

        /* Agrupamento dos botões */
        .button-group {
            display: flex;
            flex-direction: column; 
            gap: 15px; 
            width: 300px; 
            max-width: 90%; 
            margin: 0 auto; 
        }
        
        /* Estilo Base dos Botões */
        .btn {
            padding: 15px 25px; 
            border: none;
            border-radius: 10px; 
            font-size: 1.1em;
            font-weight: 500; 
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s;
            font-weight: 700; 
            color: #333; 
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3); 
            width: 100%; 
            letter-spacing: 0.5px; 
            /* Importante: O display block faria o link funcionar melhor, 
               mas como o botão já é block, não precisamos mudar aqui. */
        }
        
        /* Estilo do Botão Login (Claro) */
        .login-btn {
            background-color: #EEDDAB; 
            color: #562617 ;
            border: 2px solid #562617; 
            box-shadow: 0 2px 2px rgba(0, 0, 0, 0.2); 
            font-weight: 600;
        }

        /* Estilo do Botão Cadastrar (Marrom Escuro) */
        .register-btn {
            background-color: #A3795A; 
            color: #fff6e7; 
            border: 2px solid #382416; 
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2); 
            font-weight: 600;
        }

        /* Efeitos de Hover */
        .btn:hover {
            opacity: 1; 
            transform: translateY(-2px); 
            box-shadow: 0 8px 10px rgba(0, 0, 0, 0.3);
        }

        /* Responsividade básica para logo e botões */
        @media (max-width: 400px) {
            .logo {
                width: 250px;
                margin-bottom: 50px;
            }
            .button-group {
                width: 90%;
            }
        }
    </style>


</head>
<body>

    <audio id="som-clique" src="src/sound/botao.mp3" preload="auto"></audio>

    <div class="logo">
        <img src="src/imgs/logo (2).png" alt="English Adventure Logo">
    </div>
    
    <div class="botoes">
        <div class="button-group">
            
            <a href="auth/login.php" class="btn-link-login">
                <button class="btn login-btn">Login</button>
            </a>
            
            <a href="auth/cadastro.php" class="btn-link-register">
                <button class="btn register-btn">Cadastrar</button>
            </a>
            
        </div>
    </div>

    <script>
        // 1. Pega o elemento de áudio
        const somClique = document.getElementById('som-clique');

        // 2. Define a função que toca o som
        function tocarSom() {
            somClique.currentTime = 0; 
            somClique.play().catch(e => {
                console.warn("Erro ao tentar tocar o som (verifique o caminho SRC e as restrições do navegador):", e);
            });
        }

        // 3. Pega os LINKS (agora que envolvem os botões)
        // OBS: Mudamos o seletor de '.btn' para '.button-group a' para pegar os links
        const linksDosBotoes = document.querySelectorAll('.button-group a');

        // 4. Adiciona o evento de clique para cada link
        linksDosBotoes.forEach(link => {
            if (link) { 
                link.addEventListener('click', tocarSom);
                
                // Opcional: Para evitar que o som toque duas vezes
                // e garantir que a página seja carregada depois do som, 
                // você pode adicionar um pequeno atraso e usar event.preventDefault(),
                // mas para simplicidade, a solução acima (tocar e seguir) é a mais comum.
            }
        });
    </script>
</body>
</html>