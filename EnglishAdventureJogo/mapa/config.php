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
// ⭐ SUBSTITUA A FUNÇÃO registrarXPFase() NO config.php POR ESTA VERSÃO CORRIGIDA:

function registrarXPFase($pdo, $usuario_id, $fase, $xp_final) {
    if ($fase < 1 || $fase > 10) {
        error_log("❌ Fase inválida: $fase");
        return false;
    }
    
    $stmt_nome = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
    $stmt_nome->execute([$usuario_id]);
    $usuario = $stmt_nome->fetch();
    
    if (!$usuario) {
        error_log("❌ Usuário não encontrado: ID $usuario_id");
        return false;
    }
    
    $nomeAluno = $usuario['nome'];
    $xp_maximo_por_fase = 10;

    try {
        // 1️⃣ CRIAR LINHA SE NÃO EXISTIR
        $colunas = "usuario_id, nomeAluno";
        $valores = "?, ?";
        $params_init = [$usuario_id, $nomeAluno];
        
        for ($i = 1; $i <= 10; $i++) {
            $colunas .= ", fase" . $i . "_xp";
            $valores .= ", 0";
        }
        $colunas .= ", total_xp";
        $valores .= ", 0";
        
        $stmt_init = $pdo->prepare("
            INSERT INTO xp_jogo1 ($colunas, dataRegistro)
            VALUES ($valores, NOW())
            ON DUPLICATE KEY UPDATE usuario_id = usuario_id
        ");
        $stmt_init->execute($params_init);
        
        // 2️⃣ OBTER XP ATUAL DA FASE
        $xp_fase_atual = obterXPFase($pdo, $usuario_id, $fase);
        
        // 3️⃣ GARANTIR QUE O XP FINAL ESTÁ NO LIMITE (0-10)
        $novo_xp_fase = max(0, min($xp_maximo_por_fase, $xp_final));
        
        // 4️⃣ SÓ ATUALIZAR SE O NOVO XP FOR DIFERENTE
        if ($novo_xp_fase != $xp_fase_atual) {
            $coluna_fase = "fase" . $fase . "_xp";
            $stmt_update = $pdo->prepare("
                UPDATE xp_jogo1 
                SET $coluna_fase = ?,
                    dataRegistro = NOW()
                WHERE usuario_id = ?
            ");
            $stmt_update->execute([$novo_xp_fase, $usuario_id]);
            
            // 5️⃣ RECALCULAR O TOTAL (SOMA DE TODAS AS 10 FASES)
            $stmt_total = $pdo->prepare("
                UPDATE xp_jogo1 
                SET total_xp = (
                    COALESCE(fase1_xp, 0) + 
                    COALESCE(fase2_xp, 0) + 
                    COALESCE(fase3_xp, 0) + 
                    COALESCE(fase4_xp, 0) + 
                    COALESCE(fase5_xp, 0) + 
                    COALESCE(fase6_xp, 0) + 
                    COALESCE(fase7_xp, 0) + 
                    COALESCE(fase8_xp, 0) + 
                    COALESCE(fase9_xp, 0) + 
                    COALESCE(fase10_xp, 0)
                )
                WHERE usuario_id = ?
            ");
            $stmt_total->execute([$usuario_id]);
        }
        
        // 6️⃣ OBTER VALORES FINAIS PARA LOG
        $xp_total_novo = obterXPTotal($pdo, $usuario_id);
        
        // Log detalhado
        error_log("✅ XP registrado - Usuário: $nomeAluno | Fase: $fase");
        error_log("   XP Recebido: $xp_final");
        error_log("   XP Fase: $xp_fase_atual → $novo_xp_fase");
        error_log("   XP Total: $xp_total_novo");
        
        return true;
        
    } catch (PDOException $e) {
        error_log("❌ Erro ao registrar XP: " . $e->getMessage());
        return false;
    }
}

// ⭐ FUNÇÃO AUXILIAR PARA RECALCULAR TOTAL_XP (CASO PRECISE CORRIGIR)
function recalcularTotalXP($pdo, $usuario_id) {
    try {
        $stmt = $pdo->prepare("
            UPDATE xp_jogo1 
            SET total_xp = (
                COALESCE(fase1_xp, 0) + 
                COALESCE(fase2_xp, 0) + 
                COALESCE(fase3_xp, 0) + 
                COALESCE(fase4_xp, 0) + 
                COALESCE(fase5_xp, 0) + 
                COALESCE(fase6_xp, 0) + 
                COALESCE(fase7_xp, 0) + 
                COALESCE(fase8_xp, 0) + 
                COALESCE(fase9_xp, 0) + 
                COALESCE(fase10_xp, 0)
            )
            WHERE usuario_id = ?
        ");
        $stmt->execute([$usuario_id]);
        
        $xp_total = obterXPTotal($pdo, $usuario_id);
        error_log("✅ Total XP recalculado: $xp_total");
        
        return true;
    } catch (PDOException $e) {
        error_log("❌ Erro ao recalcular total XP: " . $e->getMessage());
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
    
    // Se fase anterior é 2, verifica se tem pelo menos 1 ESTRELA
    if ($fase_anterior == 2) {
        $estrelas_fase2 = obterEstrelasPorXP($pdo, $usuario_id, 2, true);
        return ($estrelas_fase2 >= 1); // ⭐ 1+ estrela na Fase 2 desbloqueia Fase 3
    }
    
    // ⭐ CORREÇÃO: Se fase anterior é 3, verifica se tem pelo menos 1 ESTRELA na Fase 3
    if ($fase_anterior == 3) {
        $estrelas_fase3 = obterEstrelasPorXP($pdo, $usuario_id, 3, true);
        return ($estrelas_fase3 >= 1); // ⭐ 1+ estrela na Fase 3 desbloqueia Fase 4
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
    
    // Fase 3: Usa XP TOTAL do Jogo 2
    if ($numero_fase == 3) {
        $xp_total_jogo2 = obterXPTotalJogo2($pdo, $usuario_id);
        
        if ($xp_total_jogo2 >= 32) return 3;
        elseif ($xp_total_jogo2 >= 20) return 2;
        elseif ($xp_total_jogo2 >= 8) return 1;
        return 0;
    }
    
    // ⭐ CORREÇÃO: Fase 4: Usa XP TOTAL do Jogo 3 (Espelhos de Midgard)
    if ($numero_fase == 4) {
        $xp_total_jogo3 = obterXPTotalJogo3($pdo, $usuario_id);
        
        // 50 XP máximo = 100%
        if ($xp_total_jogo3 >= 40) return 3;      // 80%+ = 40-50 XP = 3 estrelas
        elseif ($xp_total_jogo3 >= 25) return 2;  // 50%+ = 25-39 XP = 2 estrelas  
        elseif ($xp_total_jogo3 >= 10) return 1;  // 20%+ = 10-24 XP = 1 estrela
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
    $xp_maximo_por_fase = 40;
    $xp_maximo_total = 40;

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

// ============================================
// FUNÇÕES PARA JOGO 4 (Espelhos de Midgard)
// ============================================

// ============================================
// FUNÇÕES PARA JOGO 3 (Espelhos de Midgard) - usa xp_jogo3
// ============================================

/**
 * Registra XP para o Jogo 3 (Espelhos de Midgard) - 50 XP máximo
 */
function registrarXPJogo3($pdo, $usuario_id, $fase, $xp_ganho) {
    if ($fase < 1 || $fase > 10) {
        error_log("❌ Fase inválida para Jogo 3: $fase");
        return false;
    }
    
    $stmt_nome = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
    $stmt_nome->execute([$usuario_id]);
    $usuario = $stmt_nome->fetch();
    
    if (!$usuario) {
        error_log("❌ Usuário não encontrado: ID $usuario_id");
        return false;
    }
    
    $nomeAluno = $usuario['nome'];
    $xp_maximo_por_fase = 50; // ⭐ MUDADO: 50 XP por fase
    $xp_maximo_total = 50;    // ⭐ MUDADO: 50 XP total máximo

    try {
        // Verificar se já existe linha
        $stmt_check = $pdo->prepare("SELECT id FROM xp_jogo3 WHERE usuario_id = ?");
        $stmt_check->execute([$usuario_id]);
        $existe = $stmt_check->fetch();
        
        if (!$existe) {
            // Criar linha inicial com 8 fases
            $colunas = "usuario_id, nomeAluno";
            $valores = "?, ?";
            $params_init = [$usuario_id, $nomeAluno];
            
            for ($i = 1; $i <= 8; $i++) {
                $colunas .= ", fase" . $i . "_xp";
                $valores .= ", 0";
            }
            $colunas .= ", total_xp";
            $valores .= ", 0";
            
            $stmt_init = $pdo->prepare("
                INSERT INTO xp_jogo3 ($colunas, dataRegistro)
                VALUES ($valores, NOW())
            ");
            $stmt_init->execute($params_init);
            
            error_log("✅ Linha criada para Jogo 3 - usuário $nomeAluno (ID: $usuario_id)");
        }
        
        // Obter XP atual
        $xp_fase_atual = obterXPFaseJogo3($pdo, $usuario_id, $fase);
        $xp_total_atual = obterXPTotalJogo3($pdo, $usuario_id);
        
        // Calcular novos valores com limite
        $novo_xp_fase = min($xp_maximo_por_fase, max(0, $xp_fase_atual + $xp_ganho));
        $novo_xp_total = min($xp_maximo_total, max(0, $xp_total_atual + $xp_ganho));
        
        // Atualizar XP
        $coluna_fase = "fase" . $fase . "_xp";
        $stmt_update = $pdo->prepare("
            UPDATE xp_jogo3 
            SET $coluna_fase = ?,
                total_xp = ?,
                dataRegistro = NOW()
            WHERE usuario_id = ?
        ");
        
        $stmt_update->execute([$novo_xp_fase, $novo_xp_total, $usuario_id]);
        
        error_log("✅ XP Jogo 3 - Usuário: $nomeAluno | Fase: $fase");
        error_log("   XP Ganho: $xp_ganho");
        error_log("   XP Fase: $xp_fase_atual → $novo_xp_fase / $xp_maximo_por_fase");
        error_log("   XP Total: $xp_total_atual → $novo_xp_total / $xp_maximo_total");
        
        return true;
        
    } catch (PDOException $e) {
        error_log("❌ Erro ao registrar XP Jogo 3: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtém XP total do Jogo 3
 */
function obterXPTotalJogo3($pdo, $usuario_id) {
    try {
        $stmt = $pdo->prepare("SELECT total_xp FROM xp_jogo3 WHERE usuario_id = ?");
        $stmt->execute([$usuario_id]);
        $result = $stmt->fetch();
        return $result ? intval($result['total_xp']) : 0;
    } catch (PDOException $e) {
        error_log("Erro ao obter XP total Jogo 3: " . $e->getMessage());
        return 0;
    }
}

/**
 * Obtém XP de uma fase específica do Jogo 3
 */
function obterXPFaseJogo3($pdo, $usuario_id, $fase) {
    try {
        $coluna_fase = "fase" . $fase . "_xp";
        $stmt = $pdo->prepare("SELECT $coluna_fase FROM xp_jogo3 WHERE usuario_id = ?");
        $stmt->execute([$usuario_id]);
        $result = $stmt->fetch();
        return $result ? intval($result[$coluna_fase]) : 0;
    } catch (PDOException $e) {
        error_log("Erro ao obter XP da fase Jogo 3: " . $e->getMessage());
        return 0;
    }
}

/**
 * Registra XP completo do Jogo 3
 */
function registrarXPCompleto3($pdo, $usuario_id, $fase, $xp) {
    return registrarXPJogo3($pdo, $usuario_id, $fase, $xp);
}

// ⭐ ALIAS para compatibilidade
function obterXPTotal3($pdo, $usuario_id) {
    return obterXPTotalJogo3($pdo, $usuario_id);
}

function obterXPFase3($pdo, $usuario_id, $fase) {
    return obterXPFaseJogo3($pdo, $usuario_id, $fase);
}


// ============================================
// SISTEMA DE CONTROLE DE REPLAY COM CRISTAL
// ============================================

/**
 * Verifica se o usuário pode jogar uma fase
 * Mostra overlay se já jogou e não tem cristal
 * 
 * USO: verificarPermissaoFase($pdo, 1, 3); // Jogo 1, Fase 3
 * 
 * @param PDO $pdo
 * @param int $jogo (1=Learning, 2=Jogo2, 3=Jogo3, 4=Jogo4)
 * @param int $fase (1-10)
 */
function verificarPermissaoFase($pdo, $jogo, $fase) {
    // Garantir que temos o ID do usuário
    $usuario_id = $_SESSION['usuario_id'] ?? $_SESSION['id'] ?? null;
    
    if (!$usuario_id) {
        header('Location: ../auth/login.php');
        exit;
    }
    
    // ⭐ JOGO 4: Sempre pode jogar (é onde conquista o cristal)
    if ($jogo == 4) {
        registrarFaseJogada($pdo, $usuario_id, $jogo, $fase);
        return true;
    }
    
    // ⭐ Verificar se tem cristal de replay
    $tem_cristal = verificarCristal($pdo, $usuario_id);
    
    // ⭐ Verificar se já jogou esta fase
    $ja_jogou = verificarFaseJogada($pdo, $usuario_id, $jogo, $fase);
    
    // ✅ REGRA: Se TEM CRISTAL, pode jogar sempre
    if ($tem_cristal) {
        registrarFaseJogada($pdo, $usuario_id, $jogo, $fase);
        return true;
    }
    
    // ✅ REGRA: Se NÃO TEM CRISTAL e JÁ JOGOU, mostra overlay
    if ($ja_jogou) {
        $_SESSION['mostrar_overlay_bloqueio'] = true;
        $_SESSION['fase_bloqueada'] = $fase;
        $_SESSION['jogo_bloqueado'] = $jogo;
        return false; // Não redireciona, apenas sinaliza que está bloqueado
    }
    
    // ✅ PRIMEIRA VEZ jogando: pode jogar
    registrarFaseJogada($pdo, $usuario_id, $jogo, $fase);
    return true;
}

/**
 * Verifica se usuário tem o cristal de replay
 */
function verificarCristal($pdo, $usuario_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT id FROM cristais_conquistados 
            WHERE usuario_id = ? AND cristal_tipo = 'cristal_replay'
        ");
        $stmt->execute([$usuario_id]);
        return $stmt->fetch() !== false;
    } catch (PDOException $e) {
        error_log("❌ Erro ao verificar cristal: " . $e->getMessage());
        return false;
    }
}

/**
 * Concede o cristal de replay ao usuário (chamado ao completar Jogo 4)
 */
function concederCristal($pdo, $usuario_id) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO cristais_conquistados (usuario_id, cristal_tipo)
            VALUES (?, 'cristal_replay')
            ON DUPLICATE KEY UPDATE data_conquista = NOW()
        ");
        $stmt->execute([$usuario_id]);
        
        error_log("✅ Cristal de replay concedido para usuário ID: $usuario_id");
        return true;
    } catch (PDOException $e) {
        error_log("❌ Erro ao conceder cristal: " . $e->getMessage());
        return false;
    }
}

/**
 * Verifica se uma fase já foi jogada
 */
function verificarFaseJogada($pdo, $usuario_id, $jogo, $fase) {
    try {
        $stmt = $pdo->prepare("
            SELECT id FROM fases_jogadas 
            WHERE usuario_id = ? AND jogo = ? AND fase = ?
        ");
        $stmt->execute([$usuario_id, $jogo, $fase]);
        return $stmt->fetch() !== false;
    } catch (PDOException $e) {
        error_log("❌ Erro ao verificar fase jogada: " . $e->getMessage());
        return false;
    }
}

/**
 * Registra que o usuário jogou uma fase
 */
function registrarFaseJogada($pdo, $usuario_id, $jogo, $fase) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO fases_jogadas (usuario_id, jogo, fase, primeira_vez)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE ultima_jogada = NOW()
        ");
        $stmt->execute([$usuario_id, $jogo, $fase]);
        return true;
    } catch (PDOException $e) {
        error_log("❌ Erro ao registrar fase jogada: " . $e->getMessage());
        return false;
    }
}

/**
 * ⭐ NOVO: Verifica se a próxima fase deve ser desbloqueada
 * Regra: Basta ter TENTADO a fase anterior (não precisa de XP)
 */
function faseDesbloqueadaPorTentativa($pdo, $usuario_id, $jogo, $fase) {
    // Fase 1 sempre desbloqueada
    if ($fase == 1) {
        return true;
    }
    
    // Verifica se TENTOU a fase anterior
    $fase_anterior = $fase - 1;
    return verificarFaseJogada($pdo, $usuario_id, $jogo, $fase_anterior);
}

/**
 * Obtém estatísticas de replay do usuário
 */
function obterEstatisticasReplay($pdo, $usuario_id) {
    try {
        $tem_cristal = verificarCristal($pdo, $usuario_id);
        
        $stmt = $pdo->prepare("
            SELECT jogo, COUNT(DISTINCT fase) as fases_jogadas
            FROM fases_jogadas
            WHERE usuario_id = ?
            GROUP BY jogo
        ");
        $stmt->execute([$usuario_id]);
        $jogos = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
        
        return [
            'tem_cristal' => $tem_cristal,
            'fases_por_jogo' => [
                1 => isset($jogos[1]) ? $jogos[1][0]['fases_jogadas'] : 0,
                2 => isset($jogos[2]) ? $jogos[2][0]['fases_jogadas'] : 0,
                3 => isset($jogos[3]) ? $jogos[3][0]['fases_jogadas'] : 0,
                4 => isset($jogos[4]) ? $jogos[4][0]['fases_jogadas'] : 0
            ]
        ];
    } catch (PDOException $e) {
        error_log("❌ Erro ao obter estatísticas: " . $e->getMessage());
        return null;
    }
}
function gerarOverlayBloqueio($jogo, $fase) {
    $nomes_jogos = [
        1 => 'Learning',
        2 => 'As Runas de Identidade', 
        3 => 'A Floresta Escura'
    ];
    
    $nome_jogo = $nomes_jogos[$jogo] ?? "Jogo $jogo";
    
    // Verificar se é a última fase do jogo atual
    $ultima_fase_por_jogo = [
        1 => 5,  // Learning tem 5 fases
        2 => 10, // Castelo das Frases tem 10 fases
        3 => 10  // Espelhos de Midgard tem 10 fases
    ];
    
    $eh_ultima_fase = ($fase == ($ultima_fase_por_jogo[$jogo] ?? 10));
    
    return '
    <div id="overlayBloqueio" style="
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.85);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 99999;
        animation: fadeIn 0.3s ease;
       font-family: "Poppins", cursive;
    ">
        <div style="
            background-image: url(\'../src/imgs/pergaminho.png\');
            background-size: cover;
            background-position: center;
            padding: 60px 80px;
            border-radius: 20px;
            text-align: center;
            max-width: 600px;
            color: #3d2817;
            animation: slideUp 0.5s ease;
            position: relative;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        ">
            <div style="font-size: 80px; margin-bottom: 20px;">🔒</div>
            
            <h2 style="
                font-size: 32px;
                margin-bottom: 20px;
                 font-family: "Poppins", cursive;
                color: #865f3b;
                text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
            ">
                Fase Já Completada!
            </h2>
            
             <p style="
                    font-family: "Poppins", cursive;
                font-size: 17px;
               
                margin-bottom: 10px;
                color: #3d2817;
                font-weight: 500;
            ">
                Você já jogou <strong style="color: #3d2817;"> ' . $nome_jogo . ' </strong> !<br>
                <br>
                Para jogar novamente, complete o <br> <strong style="color: #3d2817;">Jogo 4</strong> e conquiste o <strong style="color: #3d2817;"> Cristal da Identidade </strong> <br>


            </p>
            
            <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                <button onclick="voltarMapa()" style="
                margin-top:20px;
                    background-color: #3d2817;
                     width: 40%;
                    height: 40px;
                    color: white;
                    border: none;
                    padding: 5px 5px;
                    border-radius: 25px;
                    font-size: 12px;
                    font-weight: 200;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
                       font-family: "Poppins", cursive;
                ">
                     Voltar ao Mapa
                </button>
                
                
            </div>
        </div>
    </div>
    
    <style>
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideUp {
            from { 
                transform: translateY(50px);
                opacity: 0;
            }
            to { 
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        #overlayBloqueio button:hover {
              transform: scale(1.03);
            background: #2c1e15;
        }
        
        #overlayBloqueio button:active {
            transform: translateY(-1px) scale(1.02);
        }
    </style>
    
    <script>
        function voltarMapa() {
            window.location.href = "../mapa/fases.php";
        }
        
        ' . (!$eh_ultima_fase ? '
        function irProximaFase() {
            // Calcula próxima fase
            const faseAtual = ' . $fase . ';
            const proximaFase = faseAtual + 1;
            
            // Redireciona para próxima fase se existir
            if (proximaFase <= ' . ($ultima_fase_por_jogo[$jogo] ?? 10) . ') {
                // Adapte conforme a estrutura dos seus arquivos
                const jogoAtual = ' . $jogo . ';
                let basePath = "";
                
                switch(jogoAtual) {
                    case 1:
                        basePath = "../Learning/";
                        break;
                    case 2:
                        basePath = "../AsRunasDeIdentidade/";
                        break;
                    case 3:
                        basePath = "../AFlorestaEscura/";
                        break;
                    default:
                        basePath = "../";
                }
                
                window.location.href = basePath + "fase" + proximaFase + ".php";
            } else {
                voltarMapa();
            }
        }
        ' : '') . '
        
        // Fechar com ESC
        document.addEventListener("keydown", function(e) {
            if (e.key === "Escape") {
                voltarMapa();
            }
        });
    </script>
    ';
}




?>