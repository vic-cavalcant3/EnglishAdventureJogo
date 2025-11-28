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



// ============================================
// FUNÇÕES DE PROGRESSO DETALHADO (NOVO)
// ============================================

/**
 * Registra progresso detalhado de uma questão
 * 
 * @param PDO $pdo
 * @param int $usuario_id
 * @param int $fase Número da fase (1-10)
 * @param string $atividade Nome da atividade (ex: "fase1_atividade1")
 * @param string $tipo_gramatica 'afirmativa', 'interrogativa' ou 'negativa'
 * @param string $tipo_habilidade 'speaking', 'reading', 'listening' ou 'writing'
 * @param bool $acertou Se acertou a questão
 */
function registrarProgressoDetalhado($pdo, $usuario_id, $fase, $atividade, $tipo_gramatica, $tipo_habilidade, $acertou = true) {
    try {
        // Buscar nome do usuário
        $stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
        $stmt->execute([$usuario_id]);
        $usuario = $stmt->fetch();
        
        if (!$usuario) {
            error_log("❌ Usuário não encontrado: ID $usuario_id");
            return false;
        }
        
        $nomeAluno = $usuario['nome'];
        
        // Verificar se já existe registro
        $stmt = $pdo->prepare("
            SELECT id, tentativas, acertou 
            FROM progresso_detalhado 
            WHERE usuario_id = ? AND atividade = ?
        ");
        $stmt->execute([$usuario_id, $atividade]);
        $registro_existente = $stmt->fetch();
        
        if ($registro_existente) {
            // Atualizar registro existente
            $stmt = $pdo->prepare("
                UPDATE progresso_detalhado 
                SET acertou = ?,
                    tentativas = tentativas + 1,
                    dataUltimaAtualizacao = NOW()
                WHERE usuario_id = ? AND atividade = ?
            ");
            $stmt->execute([$acertou ? 1 : 0, $usuario_id, $atividade]);
        } else {
            // Criar novo registro
            $stmt = $pdo->prepare("
                INSERT INTO progresso_detalhado 
                (usuario_id, nomeAluno, fase, atividade, tipo_gramatica, tipo_habilidade, acertou, tentativas)
                VALUES (?, ?, ?, ?, ?, ?, ?, 1)
            ");
            $stmt->execute([
                $usuario_id, 
                $nomeAluno, 
                $fase, 
                $atividade, 
                $tipo_gramatica, 
                $tipo_habilidade, 
                $acertou ? 1 : 0
            ]);
        }
        
        // Atualizar resumo
        atualizarResumoProgresso($pdo, $usuario_id);
        
        error_log("✅ Progresso registrado - Usuário: $nomeAluno | Fase: $fase | Tipo: $tipo_gramatica/$tipo_habilidade | Acertou: " . ($acertou ? "Sim" : "Não"));
        
        return true;
        
    } catch (PDOException $e) {
        error_log("❌ Erro ao registrar progresso: " . $e->getMessage());
        return false;
    }
}

/**
 * Atualiza o resumo de progresso do usuário
 */
/**
 * Atualiza o resumo de progresso do usuário
 */
function atualizarResumoProgresso($pdo, $usuario_id) {
    try {
        // Buscar nome do usuário
        $stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
        $stmt->execute([$usuario_id]);
        $usuario = $stmt->fetch();
        
        if (!$usuario) return false;
        
        $nomeAluno = $usuario['nome'];
        
        // Contar por tipo de gramática
        $stmt = $pdo->prepare("
            SELECT 
                tipo_gramatica,
                COUNT(*) as total,
                SUM(acertou) as acertos
            FROM progresso_detalhado
            WHERE usuario_id = ?
            GROUP BY tipo_gramatica
        ");
        $stmt->execute([$usuario_id]);
        $gramatica = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_UNIQUE);
        
        // Contar por tipo de habilidade
        $stmt = $pdo->prepare("
            SELECT 
                tipo_habilidade,
                COUNT(*) as total,
                SUM(acertou) as acertos
            FROM progresso_detalhado
            WHERE usuario_id = ?
            GROUP BY tipo_habilidade
        ");
        $stmt->execute([$usuario_id]);
        $habilidades = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_UNIQUE);
        
        // Criar ou atualizar resumo (COM CHOICE!)
        $stmt = $pdo->prepare("
            INSERT INTO resumo_progresso 
            (usuario_id, nomeAluno, 
             afirmativa_total, afirmativa_acertos,
             interrogativa_total, interrogativa_acertos,
             negativa_total, negativa_acertos,
             speaking_total, speaking_acertos,
             reading_total, reading_acertos,
             listening_total, listening_acertos,
             writing_total, writing_acertos,
             choice_total, choice_acertos)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                afirmativa_total = VALUES(afirmativa_total),
                afirmativa_acertos = VALUES(afirmativa_acertos),
                interrogativa_total = VALUES(interrogativa_total),
                interrogativa_acertos = VALUES(interrogativa_acertos),
                negativa_total = VALUES(negativa_total),
                negativa_acertos = VALUES(negativa_acertos),
                speaking_total = VALUES(speaking_total),
                speaking_acertos = VALUES(speaking_acertos),
                reading_total = VALUES(reading_total),
                reading_acertos = VALUES(reading_acertos),
                listening_total = VALUES(listening_total),
                listening_acertos = VALUES(listening_acertos),
                writing_total = VALUES(writing_total),
                writing_acertos = VALUES(writing_acertos),
                choice_total = VALUES(choice_total),
                choice_acertos = VALUES(choice_acertos)
        ");
        
        $stmt->execute([
            $usuario_id,
            $nomeAluno,
            $gramatica['afirmativa']['total'] ?? 0,
            $gramatica['afirmativa']['acertos'] ?? 0,
            $gramatica['interrogativa']['total'] ?? 0,
            $gramatica['interrogativa']['acertos'] ?? 0,
            $gramatica['negativa']['total'] ?? 0,
            $gramatica['negativa']['acertos'] ?? 0,
            $habilidades['speaking']['total'] ?? 0,
            $habilidades['speaking']['acertos'] ?? 0,
            $habilidades['reading']['total'] ?? 0,
            $habilidades['reading']['acertos'] ?? 0,
            $habilidades['listening']['total'] ?? 0,
            $habilidades['listening']['acertos'] ?? 0,
            $habilidades['writing']['total'] ?? 0,
            $habilidades['writing']['acertos'] ?? 0,
            $habilidades['choice']['total'] ?? 0,      // ⭐ ADICIONADO
            $habilidades['choice']['acertos'] ?? 0     // ⭐ ADICIONADO
        ]);
        
        return true;
        
    } catch (PDOException $e) {
        error_log("❌ Erro ao atualizar resumo: " . $e->getMessage());
        return false;
    }
}
/**
 * Obtém o resumo de progresso do usuário (para o mobile)
 */
/**
 * Obtém o resumo de progresso do usuário (para o mobile)
 */
function obterResumoProgresso($pdo, $usuario_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM resumo_progresso WHERE usuario_id = ?");
        $stmt->execute([$usuario_id]);
        $resumo = $stmt->fetch();
        
        if (!$resumo) {
            // Criar resumo inicial se não existir
            atualizarResumoProgresso($pdo, $usuario_id);
            $stmt->execute([$usuario_id]);
            $resumo = $stmt->fetch();
        }
        
        return $resumo ?: [
            'afirmativa_total' => 0, 'afirmativa_acertos' => 0,
            'interrogativa_total' => 0, 'interrogativa_acertos' => 0,
            'negativa_total' => 0, 'negativa_acertos' => 0,
            'speaking_total' => 0, 'speaking_acertos' => 0,
            'reading_total' => 0, 'reading_acertos' => 0,
            'listening_total' => 0, 'listening_acertos' => 0,
            'writing_total' => 0, 'writing_acertos' => 0,
            'choice_total' => 0, 'choice_acertos' => 0  // ⭐ ADICIONADO
        ];
        
    } catch (PDOException $e) {
        error_log("❌ Erro ao obter resumo: " . $e->getMessage());
        return null;
    }
}
/**
 * Obtém progresso detalhado por fase
 */
function obterProgressoPorFase($pdo, $usuario_id, $fase) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                atividade, 
                tipo_gramatica, 
                tipo_habilidade, 
                acertou, 
                tentativas,
                dataRegistro
            FROM progresso_detalhado
            WHERE usuario_id = ? AND fase = ?
            ORDER BY dataRegistro ASC
        ");
        $stmt->execute([$usuario_id, $fase]);
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("❌ Erro ao obter progresso da fase: " . $e->getMessage());
        return [];
    }
}


// Função para verificar se usuário está logado
function verificarLogin() {
    if (!isset($_SESSION['id']) && !isset($_SESSION['usuario_id'])) {
        header('Location: ../auth/login.php');
        exit;
    }
    
    if (isset($_SESSION['id']) && !isset($_SESSION['usuario_id'])) {
        $_SESSION['usuario_id'] = $_SESSION['id'];
    }
    
    if (isset($_SESSION['nome']) && !isset($_SESSION['usuario_nome'])) {
        $_SESSION['usuario_nome'] = $_SESSION['nome'];
    }
}

// ============================================
// FUNÇÕES DE XP - 10 FASES (SISTEMA CORRIGIDO)
// ============================================

/**
 * Registra XP de forma acumulativa (UMA LINHA por usuário)
 */
function registrarXPFase($pdo, $usuario_id, $fase, $xp_ganho) {
    // Validar fase (1-10)
    if ($fase < 1 || $fase > 10) {
        error_log("❌ Fase inválida: $fase");
        return false;
    }
    
    // Buscar nome do usuário
    $stmt_nome = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
    $stmt_nome->execute([$usuario_id]);
    $usuario = $stmt_nome->fetch();
    
    if (!$usuario) {
        error_log("❌ Usuário não encontrado: ID $usuario_id");
        return false;
    }
    
    $nomeAluno = $usuario['nome'];
    $xp_maximo_por_fase = 10;
    $xp_maximo_total = 100;

    try {
        // PRIMEIRO: Criar linha se não existir
        $colunas = "usuario_id, nomeAluno";
        $valores = "?, ?";
        $params_init = [$usuario_id, $nomeAluno];
        
        // Adicionar colunas para todas as 10 fases
        for ($i = 1; $i <= 10; $i++) {
            $colunas .= ", fase" . $i . "_xp";
            $valores .= ", 0";
        }
        $colunas .= ", total_xp";
        $valores .= ", 0";
        
        $stmt_init = $pdo->prepare("
            INSERT INTO xp_jogo1 ($colunas, dataRegistro)
            VALUES ($valores, NOW())
            ON DUPLICATE KEY UPDATE dataRegistro = NOW()
        ");
        $stmt_init->execute($params_init);
        
        // SEGUNDO: Atualizar XP da fase específica
        $coluna_fase = "fase" . $fase . "_xp";
        $stmt_update = $pdo->prepare("
            UPDATE xp_jogo1 
            SET $coluna_fase = GREATEST(0, LEAST(?, $coluna_fase + ?)),
                total_xp = GREATEST(0, LEAST(?, total_xp + ?)),
                dataRegistro = NOW()
            WHERE usuario_id = ?
        ");
        
        $stmt_update->execute([$xp_maximo_por_fase, $xp_ganho, $xp_maximo_total, $xp_ganho, $usuario_id]);
        
        // Log de sucesso
        $xp_atual = obterXPFase($pdo, $usuario_id, $fase);
        $xp_total = obterXPTotal($pdo, $usuario_id);
        error_log("✅ XP registrado - Usuário: $nomeAluno | Fase: $fase | Ganho: $xp_ganho | Fase: $xp_atual/10 | Total: $xp_total/100");
        
        return true;
        
    } catch (PDOException $e) {
        error_log("❌ Erro ao registrar XP: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtém o XP total do usuário (soma de todas as fases)
 */
function obterXPTotal($pdo, $usuario_id) {
    try {
        $stmt = $pdo->prepare("SELECT total_xp FROM xp_jogo1 WHERE usuario_id = ?");
        $stmt->execute([$usuario_id]);
        $result = $stmt->fetch();
        return $result ? intval($result['total_xp']) : 0;
    } catch (PDOException $e) {
        error_log("Erro ao obter XP total: " . $e->getMessage());
        return 0;
    }
}

/**
 * Obtém o XP de uma fase específica
 */
function obterXPFase($pdo, $usuario_id, $fase) {
    try {
        $coluna_fase = "fase" . $fase . "_xp";
        $stmt = $pdo->prepare("SELECT $coluna_fase FROM xp_jogo1 WHERE usuario_id = ?");
        $stmt->execute([$usuario_id]);
        $result = $stmt->fetch();
        return $result ? intval($result[$coluna_fase]) : 0;
    } catch (PDOException $e) {
        error_log("Erro ao obter XP da fase: " . $e->getMessage());
        return 0;
    }
}

/**
 * Obtém o XP inicial para uma fase (soma das fases anteriores)
 */
function obterXPInicialFase($pdo, $usuario_id, $fase) {
    if ($fase <= 1) {
        return 0;
    }
    
    try {
        $sql = "SELECT (";
        for ($i = 1; $i < $fase; $i++) {
            if ($i > 1) $sql .= " + ";
            $sql .= "COALESCE(fase" . $i . "_xp, 0)";
        }
        $sql .= ") as xp_anterior FROM xp_jogo1 WHERE usuario_id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        $result = $stmt->fetch();
        return $result ? intval($result['xp_anterior']) : 0;
    } catch (PDOException $e) {
        error_log("Erro ao obter XP inicial da fase: " . $e->getMessage());
        return 0;
    }
}

/**
 * Registra XP E atualiza a tabela jogo automaticamente
 */
function registrarXPCompleto($pdo, $usuario_id, $fase, $xp) {
    // Primeiro registra o XP na tabela xp_jogo1
    $sucesso = registrarXPFase($pdo, $usuario_id, $fase, $xp);
    
    if ($sucesso) {
        // Depois atualiza o total na tabela jogo
        atualizarXPJogo($pdo, $usuario_id);
    }
    
    return $sucesso;
}

/**
 * Atualiza o XP total na tabela jogo
 */
function atualizarXPJogo($pdo, $usuario_id) {
    $stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        return false;
    }
    
    $xp_total = obterXPTotal($pdo, $usuario_id);
    
    $stmt = $pdo->prepare("UPDATE jogo SET xp_total = ? WHERE nome = ?");
    return $stmt->execute([$xp_total, $usuario['nome']]);
}

/**
 * Obtém progresso de todas as fases
 */
function obterProgressoFases($pdo, $usuario_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                fase1_xp, fase2_xp, fase3_xp, fase4_xp, fase5_xp,
                fase6_xp, fase7_xp, fase8_xp, fase9_xp, fase10_xp,
                total_xp
            FROM xp_jogo1 
            WHERE usuario_id = ?
        ");
        $stmt->execute([$usuario_id]);
        $result = $stmt->fetch();
        
        if (!$result) {
            return array_fill(1, 10, ['xp_obtido' => 0, 'xp_total_fase' => 10]);
        }
        
        $progresso = [];
        for ($i = 1; $i <= 10; $i++) {
            $progresso[$i] = [
                'xp_obtido' => $result["fase{$i}_xp"] ?? 0,
                'xp_total_fase' => 10
            ];
        }
        
        return $progresso;
    } catch (PDOException $e) {
        error_log("Erro ao obter progresso das fases: " . $e->getMessage());
        return array_fill(1, 10, ['xp_obtido' => 0, 'xp_total_fase' => 10]);
    }
}

/**
 * Obtém o XP total acumulado do usuário (soma das 10 fases)
 */
function obterXPTotalAcumulado($pdo, $usuario_id) {
    return obterXPTotal($pdo, $usuario_id); // Já retorna a soma de todas as fases
}

// ============================================
// FUNÇÕES DE ESTRELAS (MANTIDAS)
// ============================================

function obterProgressoUsuario($pdo, $usuario_id) {
    $stmt = $pdo->prepare("
        SELECT atividade, COUNT(*) as estrelas, MAX(dataRegistro) as ultima_atividade
        FROM estrelas 
        WHERE nomeAluno = (SELECT nome FROM usuarios WHERE id = ?)
        GROUP BY atividade
        ORDER BY atividade
    ");
    $stmt->execute([$usuario_id]);
    return $stmt->fetchAll();
}

function faseDesbloqueada($pdo, $usuario_id, $numero_fase) {
    if ($numero_fase == 1) return true;
    
    $fase_anterior = $numero_fase - 1;
    
    // Se fase anterior é 1, verifica se tem pelo menos 1 estrela
    if ($fase_anterior == 1) {
        $estrelas_fase1 = contarEstrelasFase($pdo, $usuario_id, 1);
        return ($estrelas_fase1 >= 1);
    }
    
    // ⭐ MUDANÇA CRÍTICA: Se fase anterior é 2, verifica se tem pelo menos 1 ESTRELA
    if ($fase_anterior == 2) {
        $estrelas_fase2 = obterEstrelasPorXP($pdo, $usuario_id, 2, true);
        return ($estrelas_fase2 >= 1); // ⭐ 1+ estrela na Fase 2 desbloqueia Fase 3
    }
    
    // Se fase anterior é 3 (Jogo 2 - Fase 1), verifica se completou
    if ($fase_anterior == 3) {
        $xp_fase1_jogo2 = obterXPFaseJogo2($pdo, $usuario_id, 1);
        return ($xp_fase1_jogo2 >= 2); // Precisa de 2+ XP no Jogo 2
    }
    
    return false;
}

function contarEstrelasFase($pdo, $usuario_id, $numero_fase) {
    $stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) return 0;
    
    $padraoFase = "fase" . $numero_fase . "_%";
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_estrelas
        FROM estrelas 
        WHERE nomeAluno = ? AND atividade LIKE ? AND acertou = 1
    ");
    $stmt->execute([$usuario['nome'], $padraoFase]);
    $resultado = $stmt->fetch();
    
    return $resultado['total_estrelas'] ?? 0;
}

function registrarEstrela($pdo, $usuario_id, $atividade, $acertou = 1) {
    $stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) return false;
    
    $stmt = $pdo->prepare("SELECT id FROM estrelas WHERE nomeAluno = ? AND atividade = ?");
    $stmt->execute([$usuario['nome'], $atividade]);
    $existe = $stmt->fetch();
    
    if ($existe) {
        $stmt = $pdo->prepare("
            UPDATE estrelas SET acertou = ?, dataRegistro = NOW()
            WHERE nomeAluno = ? AND atividade = ?
        ");
        return $stmt->execute([$acertou, $usuario['nome'], $atividade]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO estrelas (nomeAluno, atividade, acertou, dataRegistro)
            VALUES (?, ?, ?, NOW())
        ");
        return $stmt->execute([$usuario['nome'], $atividade, $acertou]);
    }
}

function atividadeCompleta($pdo, $usuario_id, $atividade) {
    $stmt = $pdo->prepare("
        SELECT acertou FROM estrelas 
        WHERE nomeAluno = (SELECT nome FROM usuarios WHERE id = ?) AND atividade = ?
    ");
    $stmt->execute([$usuario_id, $atividade]);
    $resultado = $stmt->fetch();
    
    return $resultado && $resultado['acertou'] == 1;
}

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

function atualizarEstrelasJogo($pdo, $usuario_id) {
    $stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) return false;
    
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT atividade) as total_estrelas
        FROM estrelas 
        WHERE nomeAluno = ? AND (
            atividade LIKE 'fase1_%' OR 
            atividade LIKE 'fase2_%' OR 
            atividade LIKE 'fase3_%'
        ) AND acertou = 1
    ");
    $stmt->execute([$usuario['nome']]);
    $resultado = $stmt->fetch();
    
    $total_estrelas = $resultado['total_estrelas'] ?? 0;
    
    $stmt = $pdo->prepare("UPDATE jogo SET estrelas = ? WHERE nome = ?");
    return $stmt->execute([$total_estrelas, $usuario['nome']]);
}

function registrarEstrelaCompleta($pdo, $usuario_id, $atividade, $acertou = 1) {
    $sucesso = registrarEstrela($pdo, $usuario_id, $atividade, $acertou);
    
    if ($sucesso) {
        atualizarEstrelasJogo($pdo, $usuario_id);
    }
    
    return $sucesso;
}

function obterEstrelasPorXP($pdo, $usuario_id, $numero_fase, $fase_desbloqueada = false) {
    if (!$fase_desbloqueada) return 0;
    
    // Fase 1 usa sistema de estrelas tradicional
    if ($numero_fase == 1) {
        return contarEstrelasFase($pdo, $usuario_id, $numero_fase);
    }
    
    // Fase 2: Usa XP TOTAL do Jogo 1
    if ($numero_fase == 2) {
        $xp_total = obterXPTotal($pdo, $usuario_id);
        
        if ($xp_total >= 8) return 3;
        elseif ($xp_total >= 5) return 2;
        elseif ($xp_total >= 2) return 1;
        return 0;
    }
    
    // ⭐ NOVO: Fase 3: Usa XP TOTAL do Jogo 2
    if ($numero_fase == 3) {
        $xp_total_jogo2 = obterXPTotalJogo2($pdo, $usuario_id);
        
        if ($xp_total_jogo2 >= 8) return 3;
        elseif ($xp_total_jogo2 >= 5) return 2;
        elseif ($xp_total_jogo2 >= 2) return 1;
        return 0;
    }
    
    // Fase 4: Ainda não implementada
    if ($numero_fase == 4) {
        return 0;
    }
    
    return 0;
}

// ============================================
// FUNÇÕES BASE PARA JOGO 2 (adicione ANTES das funções adicionais)
// ============================================

/**
 * Registra XP para o Jogo 2
 */
/**
 * Registra XP para o Jogo 2
 */
function registrarXPJogo2($pdo, $usuario_id, $fase, $xp_ganho) {
    // Validar fase (1-10)
    if ($fase < 1 || $fase > 10) {
        error_log("❌ Fase inválida para Jogo 2: $fase");
        return false;
    }
    
    // Buscar nome do usuário
    $stmt_nome = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
    $stmt_nome->execute([$usuario_id]);
    $usuario = $stmt_nome->fetch();
    
    if (!$usuario) {
        error_log("❌ Usuário não encontrado: ID $usuario_id");
        return false;
    }
    
    $nomeAluno = $usuario['nome'];
    $xp_maximo_por_fase = 10;
    $xp_maximo_total = 100;

    try {
        // ⭐ VERIFICAR SE JÁ EXISTE LINHA PARA ESTE USUÁRIO
        $stmt_check = $pdo->prepare("SELECT id FROM xp_jogo2 WHERE usuario_id = ?");
        $stmt_check->execute([$usuario_id]);
        $existe = $stmt_check->fetch();
        
        if (!$existe) {
            // ✅ CRIAR LINHA INICIAL (apenas se não existir)
            $colunas = "usuario_id, nomeAluno";
            $valores = "?, ?";
            $params_init = [$usuario_id, $nomeAluno];
            
            // Adicionar colunas para todas as 10 fases
            for ($i = 1; $i <= 10; $i++) {
                $colunas .= ", fase" . $i . "_xp";
                $valores .= ", 0";
            }
            $colunas .= ", total_xp";
            $valores .= ", 0";
            
            $stmt_init = $pdo->prepare("
                INSERT INTO xp_jogo2 ($colunas, dataRegistro)
                VALUES ($valores, NOW())
            ");
            $stmt_init->execute($params_init);
            
            error_log("✅ Linha criada para usuário $nomeAluno (ID: $usuario_id)");
        }
        
        // ⭐ ATUALIZAR XP DA FASE ESPECÍFICA
        $coluna_fase = "fase" . $fase . "_xp";
        $stmt_update = $pdo->prepare("
            UPDATE xp_jogo2 
            SET $coluna_fase = GREATEST(0, LEAST(?, $coluna_fase + ?)),
                total_xp = GREATEST(0, LEAST(?, total_xp + ?)),
                dataRegistro = NOW()
            WHERE usuario_id = ?
        ");
        
        $stmt_update->execute([
            $xp_maximo_por_fase, 
            $xp_ganho, 
            $xp_maximo_total, 
            $xp_ganho, 
            $usuario_id
        ]);
        
        // Log de sucesso
        $xp_atual = obterXPFaseJogo2($pdo, $usuario_id, $fase);
        $xp_total = obterXPTotalJogo2($pdo, $usuario_id);
        error_log("✅ XP Jogo 2 - Usuário: $nomeAluno | Fase: $fase | Ganho: $xp_ganho | Fase: $xp_atual/10 | Total: $xp_total/100");
        
        return true;
        
    } catch (PDOException $e) {
        error_log("❌ Erro ao registrar XP Jogo 2: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtém XP total do Jogo 2
 */
function obterXPTotalJogo2($pdo, $usuario_id) {
    try {
        $stmt = $pdo->prepare("SELECT total_xp FROM xp_jogo2 WHERE usuario_id = ?");
        $stmt->execute([$usuario_id]);
        $result = $stmt->fetch();
        return $result ? intval($result['total_xp']) : 0;
    } catch (PDOException $e) {
        error_log("Erro ao obter XP total Jogo 2: " . $e->getMessage());
        return 0;
    }
}

/**
 * Obtém XP de uma fase específica do Jogo 2
 */
function obterXPFaseJogo2($pdo, $usuario_id, $fase) {
    try {
        $coluna_fase = "fase" . $fase . "_xp";
        $stmt = $pdo->prepare("SELECT $coluna_fase FROM xp_jogo2 WHERE usuario_id = ?");
        $stmt->execute([$usuario_id]);
        $result = $stmt->fetch();
        return $result ? intval($result[$coluna_fase]) : 0;
    } catch (PDOException $e) {
        error_log("Erro ao obter XP da fase Jogo 2: " . $e->getMessage());
        return 0;
    }
}

/**
 * Obtém estrelas para o Jogo 2 (Fase 3)
 */
function obterEstrelasJogo2($pdo, $usuario_id, $fase_desbloqueada = false) {
    if (!$fase_desbloqueada) return 0;
    
    $xp_total = obterXPTotalJogo2($pdo, $usuario_id);
    
    // mesma lógica: usa XP TOTAL para calcular estrelas
    if ($xp_total >= 8) return 3;
    elseif ($xp_total >= 5) return 2;
    elseif ($xp_total >= 2) return 1;
    
    return 0;
}

// ============================================
// FUNÇÕES ADICIONAIS PARA JOGO 2
// ============================================

/**
 * Registra XP completo do Jogo 2 (registra XP e atualiza tabela jogo)
 */
function registrarXPCompleto2($pdo, $usuario_id, $fase, $xp) {
    // Primeiro registra o XP na tabela xp_jogo2
    $sucesso = registrarXPJogo2($pdo, $usuario_id, $fase, $xp);
    
    if ($sucesso) {
        // Depois atualiza o total na tabela jogo (se necessário)
        atualizarXPJogo2($pdo, $usuario_id);
    }
    
    return $sucesso;
}

/**
 * Atualiza o XP total do Jogo 2 na tabela jogo
 */
function atualizarXPJogo2($pdo, $usuario_id) {
    $stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        return false;
    }
    
    $xp_total = obterXPTotalJogo2($pdo, $usuario_id);
    
    // Você pode criar uma coluna xp_jogo2 na tabela jogo se quiser rastrear separadamente
    // Por enquanto, vamos apenas retornar true
    return true;
}

/**
 * Obtém XP total do Jogo 2 (alias para compatibilidade)
 */
function obterXPTotal2($pdo, $usuario_id) {
    return obterXPTotalJogo2($pdo, $usuario_id);
}

/**
 * Obtém XP de uma fase específica do Jogo 2 (alias para compatibilidade)
 */
function obterXPFase2($pdo, $usuario_id, $fase) {
    return obterXPFaseJogo2($pdo, $usuario_id, $fase);
}

/**
 * Obtém o XP inicial para uma fase do Jogo 2 (soma das fases anteriores)
 */
function obterXPInicialFase2($pdo, $usuario_id, $fase) {
    if ($fase <= 1) {
        return 0;
    }
    
    try {
        $sql = "SELECT (";
        for ($i = 1; $i < $fase; $i++) {
            if ($i > 1) $sql .= " + ";
            $sql .= "COALESCE(fase" . $i . "_xp, 0)";
        }
        $sql .= ") as xp_anterior FROM xp_jogo2 WHERE usuario_id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        $result = $stmt->fetch();
        return $result ? intval($result['xp_anterior']) : 0;
    } catch (PDOException $e) {
        error_log("Erro ao obter XP inicial da fase Jogo 2: " . $e->getMessage());
        return 0;
    }
}

/**
 * Obtém progresso de todas as fases do Jogo 2
 */
function obterProgressoFases2($pdo, $usuario_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                fase1_xp, fase2_xp, fase3_xp, fase4_xp, fase5_xp,
                fase6_xp, fase7_xp, fase8_xp, fase9_xp, fase10_xp,
                total_xp
            FROM xp_jogo2 
            WHERE usuario_id = ?
        ");
        $stmt->execute([$usuario_id]);
        $result = $stmt->fetch();
        
        if (!$result) {
            return array_fill(1, 10, ['xp_obtido' => 0, 'xp_total_fase' => 10]);
        }
        
        $progresso = [];
        for ($i = 1; $i <= 10; $i++) {
            $progresso[$i] = [
                'xp_obtido' => $result["fase{$i}_xp"] ?? 0,
                'xp_total_fase' => 10
            ];
        }
        
        return $progresso;
    } catch (PDOException $e) {
        error_log("Erro ao obter progresso das fases Jogo 2: " . $e->getMessage());
        return array_fill(1, 10, ['xp_obtido' => 0, 'xp_total_fase' => 10]);
    }
}

?>