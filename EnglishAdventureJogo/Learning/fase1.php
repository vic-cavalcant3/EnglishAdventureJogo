<?php 
require_once("conexao.php"); 
session_start();


// Buscar dados do usuário no banco de dados
$usuario_id = $_SESSION['usuario_id'];
$sql = "SELECT nome FROM usuarios WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Se não encontrar o usuário, redireciona para login

$nome_usuario = $usuario['nome'];

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Irish+Grover&family=Itim&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="icon" type="image/png" href="img/logo.png">
  <title>English Adventure</title>

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', cursive;
    }

    html, body {
      overflow: hidden; /* Desativa a rolagem */
      width: 100%;
      height: 100%;
    }

    .fundo {
      width: 100%;
      height: 100vh;
      object-fit: cover;
      position: absolute;
      top: 0;
      left: 0;
      z-index: 0;
    }

     .user-profile-button {
            background-color: #7C604F; 
            color: white;
            font-size: 0.9em;
            padding: 8px 15px;
            border-radius: 30px;
            font-weight: 700;
            text-decoration: none;
            transition: opacity 0.2s;
            line-height: 1.2;
            text-align: center;
            margin-top: 20px;
          
            cursor: pointer;
        }

         .role-bottom {
            font-size: 1em;
            font-weight: 700;
        }

    .quadro {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 1;
    }

    .quadrado {
      background-color: rgba(255, 240, 210, 0.9); 
      width: 90%;
      max-width: 900px;
      padding: 80px;
      border-radius: 20px;
      text-align: center;
      font-size: 25px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }
    .quadrado p{
      font-size: 22px;
    }

    a {
      font-size: 18px;
      display: inline-block;
      padding-top: 30px;
      color: #865f3b;
    }

    a:hover {
      color: #b48962;
      transform: scale(0.9);
    }

    .boneca {
      position: absolute;
      bottom: 100px;
      left: 250px;
      width: 180px;
      z-index: 2;
    }

      /* ======== TOPO ======== */
      .topo {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 70px;
        display: flex;
        justify-content: space-between;
        align-items: center; /* ✅ Alinha tudo na mesma linha */
        padding: 0 40px;
        background: transparent;
        color: white;
        font-family: 'Irish Grover', cursive;
        z-index: 5;
      }


      .sair img {
        margin-bottom: 10px;
        width: 25px;
        height: 25px;
        display: block;
        filter: none; /* mantém cor original */
        transition: transform 0.2s;
      }

      .sair img:hover {
        transform: scale(1.1);
      }

      .titulo {
        margin-right: 43%;
        font-size: 38px;
        color: #ffffffd8;
        text-align: center;
        font-family: 'Irish Grover';
        letter-spacing: 2px;
        margin-top: 25px;
      }

      .perfil {
        display: flex;
        align-items: center;
        background: #865f3b;
        padding: 5px 15px;
        border-radius: 30px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        margin-top: 25px;
      }

      .perfil img {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        margin-right: 10px;
      }

      .perfil span {
        color: #fff;
        font-weight: 500;
        font-size: 14px;
      }
  </style>
</head>

<body>



  <img class="fundo" src="img/fundo2.png" alt="">
  <img class="boneca" src="img/girl (1).png" alt="Boneca Viking">

  <header class="topo">
    
    <a href="../mapa/fases.php" class="sair">
      <img src="img/voltar.png" alt="Sair">
    </a>

    <h1 class="titulo">Learning</h1>

     
  </header>

  <section class="quadro">
    <div class="quadrado">
      <p>Olá <?php echo htmlspecialchars($nome_usuario); ?>, que bom que você está aqui para nos ajudar! 
        Loki realmente fez uma bagunça por aqui e precisamos tentar entender
        os 5 cristais do Ser, ou melhor, do To be.</p>
      <a href="fase2.php" class="link-transicao">Próximo</a>
    </div>
  </section>

</body>
</html>