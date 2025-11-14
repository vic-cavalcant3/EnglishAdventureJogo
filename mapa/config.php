    <?php
    // Iniciar sessão logo no início
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Configuração do banco de dados
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'englishadventure');
    define('DB_USER', 'root');
    define('DB_PASS', '');

    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
    } catch (PDOException $e) {
        die("Erro na conexão: " . $e->getMessage());
    }

    // Função para verificar se usuário está logado
    function verificarLogin() {
        // Verificar se existe 'id' ou 'usuario_id' na sessão
        if (!isset($_SESSION['id']) && !isset($_SESSION['usuario_id'])) {
            header('Location: ../auth/login.php');
            exit;
        }
        
        // Padronizar: se existe 'id' mas não existe 'usuario_id', criar 'usuario_id'
        if (isset($_SESSION['id']) && !isset($_SESSION['usuario_id'])) {
            $_SESSION['usuario_id'] = $_SESSION['id'];
        }
        
        // Padronizar: se existe 'nome' mas não existe 'usuario_nome', criar 'usuario_nome'
        if (isset($_SESSION['nome']) && !isset($_SESSION['usuario_nome'])) {
            $_SESSION['usuario_nome'] = $_SESSION['nome'];
        }
    }

    // Função para obter progresso do usuário
    function obterProgressoUsuario($pdo, $usuario_id) {
        $stmt = $pdo->prepare("
            SELECT 
                atividade,
                COUNT(*) as estrelas,
                MAX(dataRegistro) as ultima_atividade
            FROM estrelas 
            WHERE nomeAluno = (SELECT nome FROM usuarios WHERE id = ?)
            GROUP BY atividade
            ORDER BY atividade
        ");
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll();
    }

    /**
     * Verifica se uma fase está desbloqueada
     * Fase 1: Sempre desbloqueada
     * Fase 2+: Só desbloqueia se a fase anterior tiver pelo menos 1 estrela
     */
    function faseDesbloqueada($pdo, $usuario_id, $numero_fase) {
        // Fase 1 sempre está desbloqueada
        if ($numero_fase == 1) {
            return true;
        }
        
        // Para outras fases, verifica se a fase anterior foi completada
        $fase_anterior = $numero_fase - 1;
        
        // Busca o nome do usuário
        $stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
        $stmt->execute([$usuario_id]);
        $usuario = $stmt->fetch();
        
        if (!$usuario) {
            return false;
        }
        
        // Verifica se tem pelo menos 1 estrela na fase anterior
        $padraoFase = "fase" . $fase_anterior . "_%";
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total_estrelas
            FROM estrelas 
            WHERE nomeAluno = ?
            AND atividade LIKE ?
            AND acertou = 1
        ");
        $stmt->execute([$usuario['nome'], $padraoFase]);
        $resultado = $stmt->fetch();
        
        // Fase desbloqueia se tiver pelo menos 1 estrela na fase anterior
        return ($resultado['total_estrelas'] >= 1);
    }

    /**
     * Conta quantas estrelas o usuário tem em uma fase específica
     * Conta atividades únicas completadas (acertou = 1)
     */
    function contarEstrelasFase($pdo, $usuario_id, $numero_fase) {
        // Busca o nome do usuário
        $stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
        $stmt->execute([$usuario_id]);
        $usuario = $stmt->fetch();
        
        if (!$usuario) {
            return 0;
        }
        
        // Padrão para buscar atividades da fase específica
        $padraoFase = "fase" . $numero_fase . "_%";
        
        // ✅ CORREÇÃO: Conta atividades ÚNICAS com acertou = 1
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total_estrelas
            FROM estrelas 
            WHERE nomeAluno = ?
            AND atividade LIKE ?
            AND acertou = 1
        ");
        $stmt->execute([$usuario['nome'], $padraoFase]);
        $resultado = $stmt->fetch();
        
        return $resultado['total_estrelas'] ?? 0;
    }

    /**
     * Registra uma estrela quando o usuário completa uma atividade
     * @param int $usuario_id - ID do usuário
     * @param string $atividade - Nome da atividade (ex: "fase1_atividade1")
     * @param int $acertou - 1 se acertou, 0 se errou
     */
    function registrarEstrela($pdo, $usuario_id, $atividade, $acertou = 1) {
        // Busca o nome do usuário
        $stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
        $stmt->execute([$usuario_id]);
        $usuario = $stmt->fetch();
        
        if (!$usuario) {
            return false;
        }
        
        // Verifica se já existe registro para esta atividade
        $stmt = $pdo->prepare("
            SELECT id FROM estrelas 
            WHERE nomeAluno = ? 
            AND atividade = ?
        ");
        $stmt->execute([$usuario['nome'], $atividade]);
        $existe = $stmt->fetch();
        
        if ($existe) {
            // Atualiza registro existente
            $stmt = $pdo->prepare("
                UPDATE estrelas 
                SET acertou = ?, dataRegistro = NOW()
                WHERE nomeAluno = ? AND atividade = ?
            ");
            return $stmt->execute([$acertou, $usuario['nome'], $atividade]);
        } else {
            // Insere novo registro
            $stmt = $pdo->prepare("
                INSERT INTO estrelas (nomeAluno, atividade, acertou, dataRegistro)
                VALUES (?, ?, ?, NOW())
            ");
            return $stmt->execute([$usuario['nome'], $atividade, $acertou]);
        }
    }

    /**
     * Verifica se o usuário já completou uma atividade específica
     */
    function atividadeCompleta($pdo, $usuario_id, $atividade) {
        $stmt = $pdo->prepare("
            SELECT acertou FROM estrelas 
            WHERE nomeAluno = (SELECT nome FROM usuarios WHERE id = ?)
            AND atividade = ?
        ");
        $stmt->execute([$usuario_id, $atividade]);
        $resultado = $stmt->fetch();
        
        return $resultado && $resultado['acertou'] == 1;
    }

    /**
     * Obtém todas as estrelas de um usuário
     */
    function obterTodasEstrelas($pdo, $usuario_id) {
        $stmt = $pdo->prepare("
            SELECT atividade, acertou, dataRegistro
            FROM estrelas 
            WHERE nomeAluno = (SELECT nome FROM usuarios WHERE id = ?)
            ORDER BY dataRegistro DESC
        ");
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll();
    }


    /**
 * Atualiza o total de estrelas do usuário na tabela jogo
 * Conta apenas estrelas das 3 primeiras fases (fase1, fase2, fase3)
 * @param PDO $pdo - Conexão com banco de dados
 * @param int $usuario_id - ID do usuário
 * @return bool - True se atualizou com sucesso
 */
function atualizarEstrelasJogo($pdo, $usuario_id) {
    // Busca o nome do usuário
    $stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        return false;
    }
    
    // Conta o total de atividades DISTINTAS onde acertou = 1 nas fases 1, 2 e 3
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT atividade) as total_estrelas
        FROM estrelas 
        WHERE nomeAluno = ?
        AND (
            atividade LIKE 'fase1_%' OR 
            atividade LIKE 'fase2_%' OR 
            atividade LIKE 'fase3_%'
        )
        AND acertou = 1
    ");
    $stmt->execute([$usuario['nome']]);
    $resultado = $stmt->fetch();
    
    $total_estrelas = $resultado['total_estrelas'] ?? 0;
    
    echo "🔸 DEBUG: Usuario={$usuario['nome']}, Total Estrelas={$total_estrelas}\n";
    
    // Atualiza a tabela jogo
    $stmt = $pdo->prepare("
        UPDATE jogo 
        SET estrelas = ?
        WHERE nome = ?
    ");
    
    $sucesso = $stmt->execute([$total_estrelas, $usuario['nome']]);
    
    if ($sucesso) {
        echo "✅ Tabela jogo atualizada: {$total_estrelas} estrelas\n";
    }
    
    return $sucesso;
}
    /**
     * Registra uma estrela E atualiza a tabela jogo automaticamente
     * Use esta função ao invés de registrarEstrela() para salvar em ambas as tabelas
     */
    function registrarEstrelaCompleta($pdo, $usuario_id, $atividade, $acertou = 1) {
        // Primeiro registra a estrela na tabela estrelas
        $sucesso = registrarEstrela($pdo, $usuario_id, $atividade, $acertou);
        
        if ($sucesso) {
            // Depois atualiza o total na tabela jogo
            atualizarEstrelasJogo($pdo, $usuario_id);
        }
        
        return $sucesso;
    }
    ?>