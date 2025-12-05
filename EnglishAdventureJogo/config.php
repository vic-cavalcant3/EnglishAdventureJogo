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
// FUNÇÕES DE PROGRESSO DETALHADO
// ============================================

/**
 * Registra progresso detalhado de uma questão
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
        
        // Criar ou atualizar resumo
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
            $habilidades['choice']['total'] ?? 0,
            $habilidades['choice']['acertos'] ?? 0
        ]);
        
        return true;
        
    } catch (PDOException $e) {
        error_log("❌ Erro ao atualizar resumo: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtém o resumo de progresso do usuário
 */
function obterResumoProgresso($pdo, $usuario_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM resumo_progresso WHERE usuario_id = ?");
        $stmt->execute([$usuario_id]);
        $resumo = $stmt->fetch();
        
        if (!$resumo) {
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
            'choice_total' => 0, 'choice_acertos' => 0
        ];
        
    } catch (PDOException $e) {
        error_log("❌ Erro ao obter resumo: " . $e->getMessage());
        return null;
    }
}

// ============================================
// FUNÇÕES DE XP - JOGO 1
// ============================================

/**
 * Registra XP para o Jogo 1
 */
function registrarXPJogo1($pdo, $usuario_id, $fase, $xp_ganho) {
    if ($fase < 1 || $fase > 10) {
        error_log("❌ Fase inválida Jogo 1: $fase");
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
    $xp_maximo_total = 100;

    try {
        // Criar linha se não existir
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
            ON DUPLICATE KEY UPDATE dataRegistro = NOW()
        ");
        $stmt_init->execute($params_init);
        
        // Atualizar XP da fase
        $coluna_fase = "fase" . $fase . "_xp";
        $stmt_update = $pdo->prepare("
            UPDATE xp_jogo1 
            SET $coluna_fase = GREATEST(0, LEAST(?, $coluna_fase + ?)),
                total_xp = GREATEST(0, LEAST(?, total_xp + ?)),
                dataRegistro = NOW()
            WHERE usuario_id = ?
        ");
        
        $stmt_update->execute([$xp_maximo_por_fase, $xp_ganho, $xp_maximo_total, $xp_ganho, $usuario_id]);
        
        // Atualizar XP total automaticamente
        atualizarXPTotalJogo($pdo, $usuario_id);
        
        error_log("✅ XP Jogo 1 - Usuário: $nomeAluno | Fase: $fase | Ganho: $xp_ganho");
        
        return true;
        
    } catch (PDOException $e) {
        error_log("❌ Erro ao registrar XP Jogo 1: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtém XP total do Jogo 1
 */
function obterXPTotalJogo1($pdo, $usuario_id) {
    try {
        $stmt = $pdo->prepare("SELECT total_xp FROM xp_jogo1 WHERE usuario_id = ?");
        $stmt->execute([$usuario_id]);
        $result = $stmt->fetch();
        return $result ? intval($result['total_xp']) : 0;
    } catch (PDOException $e) {
        error_log("Erro ao obter XP total Jogo 1: " . $e->getMessage());
        return 0;
    }
}

/**
 * Obtém XP de uma fase específica do Jogo 1
 */
function obterXPFaseJogo1($pdo, $usuario_id, $fase) {
    try {
        $coluna_fase = "fase" . $fase . "_xp";
        $stmt = $pdo->prepare("SELECT $coluna_fase FROM xp_jogo1 WHERE usuario_id = ?");
        $stmt->execute([$usuario_id]);
        $result = $stmt->fetch();
        return $result ? intval($result[$coluna_fase]) : 0;
    } catch (PDOException $e) {
        error_log("Erro ao obter XP da fase Jogo 1: " . $e->getMessage());
        return 0;
    }
}

// ============================================
// FUNÇÕES DE XP - JOGO 2
// ============================================

/**
 * Registra XP para o Jogo 2
 */
function registrarXPJogo2($pdo, $usuario_id, $fase, $xp_ganho) {
    if ($fase < 1 || $fase > 10) {
        error_log("❌ Fase inválida Jogo 2: $fase");
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
    $xp_maximo_por_fase = 4;
    $xp_maximo_total = 40;

    try {
        // Verificar se existe linha
        $stmt_check = $pdo->prepare("SELECT id FROM xp_jogo2 WHERE usuario_id = ?");
        $stmt_check->execute([$usuario_id]);
        $existe = $stmt_check->fetch();
        
        if (!$existe) {
            // Criar linha inicial
            $colunas = "usuario_id, nomeAluno";
            $valores = "?, ?";
            $params_init = [$usuario_id, $nomeAluno];
            
            for ($i = 1; $i <= 10; $i++) {
                $colunas .= ", fase" . $i . "_xp";
                $valores .= ", 0";
            }
            $colunas .= ", total_xp";
            $valores .= ", 0";
            
            $stmt_init = $pdo->prepare("INSERT INTO xp_jogo2 ($colunas, dataRegistro) VALUES ($valores, NOW())");
            $stmt_init->execute($params_init);
        }
        
        // Atualizar XP
        $coluna_fase = "fase" . $fase . "_xp";
        $stmt_update = $pdo->prepare("
            UPDATE xp_jogo2 
            SET $coluna_fase = GREATEST(0, LEAST(?, $coluna_fase + ?)),
                total_xp = GREATEST(0, LEAST(?, total_xp + ?)),
                dataRegistro = NOW()
            WHERE usuario_id = ?
        ");
        
        $stmt_update->execute([$xp_maximo_por_fase, $xp_ganho, $xp_maximo_total, $xp_ganho, $usuario_id]);
        
        // Atualizar XP total automaticamente
        atualizarXPTotalJogo($pdo, $usuario_id);
        
        error_log("✅ XP Jogo 2 - Usuário: $nomeAluno | Fase: $fase | Ganho: $xp_ganho");
        
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

// ============================================
// FUNÇÕES DE XP - JOGO 3
// ============================================

/**
 * Registra XP para o Jogo 3
 */
function registrarXPJogo3($pdo, $usuario_id, $fase, $acertou = true) {
    if ($fase < 1 || $fase > 8) {
        error_log("❌ Fase inválida Jogo 3: $fase");
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
    $xp_ganho = $acertou ? 5 : -5;
    $xp_maximo_por_fase = 40;
    $xp_maximo_total = 320;

    try {
        // Verificar se existe linha
        $stmt_check = $pdo->prepare("SELECT id FROM xp_jogo3 WHERE usuario_id = ?");
        $stmt_check->execute([$usuario_id]);
        $existe = $stmt_check->fetch();
        
        if (!$existe) {
            // Criar linha inicial
            $colunas = "usuario_id, nomeAluno";
            $valores = "?, ?";
            $params_init = [$usuario_id, $nomeAluno];
            
            for ($i = 1; $i <= 8; $i++) {
                $colunas .= ", fase" . $i . "_xp";
                $valores .= ", 0";
            }
            $colunas .= ", total_xp";
            $valores .= ", 0";
            
            $stmt_init = $pdo->prepare("INSERT INTO xp_jogo3 ($colunas, dataRegistro) VALUES ($valores, NOW())");
            $stmt_init->execute($params_init);
        }
        
        // Atualizar XP
        $coluna_fase = "fase" . $fase . "_xp";
        $stmt_update = $pdo->prepare("
            UPDATE xp_jogo3 
            SET $coluna_fase = GREATEST(0, LEAST(?, $coluna_fase + ?)),
                total_xp = GREATEST(0, LEAST(?, total_xp + ?)),
                dataRegistro = NOW()
            WHERE usuario_id = ?
        ");
        
        $stmt_update->execute([$xp_maximo_por_fase, $xp_ganho, $xp_maximo_total, $xp_ganho, $usuario_id]);
        
        // Atualizar XP total automaticamente
        atualizarXPTotalJogo($pdo, $usuario_id);
        
        $status = $acertou ? "ACERTOU" : "ERROU";
        error_log("✅ XP Jogo 3 - $status | Usuário: $nomeAluno | Fase: $fase | XP: $xp_ganho");
        
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

// ============================================
// FUNÇÕES DE XP TOTAL CONSOLIDADO
// ============================================

/**
 * Atualiza o XP TOTAL na tabela jogo (soma dos 3 jogos)
 */
function atualizarXPTotalJogo($pdo, $usuario_id) {
    try {
        // Buscar nome do usuário
        $stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
        $stmt->execute([$usuario_id]);
        $usuario = $stmt->fetch();
        
        if (!$usuario) {
            error_log("❌ Usuário não encontrado para atualizar XP total: ID $usuario_id");
            return false;
        }
        
        $nomeAluno = $usuario['nome'];
        
        // Buscar XP de cada jogo
        $xp_jogo1 = obterXPTotalJogo1($pdo, $usuario_id);
        $xp_jogo2 = obterXPTotalJogo2($pdo, $usuario_id);
        $xp_jogo3 = obterXPTotalJogo3($pdo, $usuario_id);
        
        // Calcular XP TOTAL
        $xp_total = $xp_jogo1 + $xp_jogo2 + $xp_jogo3;
        
        // Verificar se usuário existe na tabela jogo
        $stmt_check = $pdo->prepare("SELECT nome FROM jogo WHERE nome = ?");
        $stmt_check->execute([$nomeAluno]);
        $existe_jogo = $stmt_check->fetch();
        
        if (!$existe_jogo) {
            // Criar registro se não existir
            $stmt_insert = $pdo->prepare("
                INSERT INTO jogo (nome, xp_jogo1, xp_jogo2, xp_jogo3, xp_total, dataRegistro)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt_insert->execute([$nomeAluno, $xp_jogo1, $xp_jogo2, $xp_jogo3, $xp_total]);
        } else {
            // Atualizar registro existente
            $stmt_update = $pdo->prepare("
                UPDATE jogo 
                SET xp_jogo1 = ?,
                    xp_jogo2 = ?,
                    xp_jogo3 = ?,
                    xp_total = ?,
                    dataRegistro = NOW()
                WHERE nome = ?
            ");
            $stmt_update->execute([$xp_jogo1, $xp_jogo2, $xp_jogo3, $xp_total, $nomeAluno]);
        }
        
        error_log("✅ XP Total atualizado - $nomeAluno | J1: $xp_jogo1 | J2: $xp_jogo2 | J3: $xp_jogo3 | TOTAL: $xp_total");
        
        return true;
        
    } catch (PDOException $e) {
        error_log("❌ Erro ao atualizar XP total: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtém o XP TOTAL consolidado (para mobile)
 */
function obterXPTotalConsolidado($pdo, $usuario_id) {
    try {
        // Buscar nome do usuário
        $stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
        $stmt->execute([$usuario_id]);
        $usuario = $stmt->fetch();
        
        if (!$usuario) {
            return [
                'xp_jogo1' => 0,
                'xp_jogo2' => 0, 
                'xp_jogo3' => 0,
                'xp_total' => 0,
                'xp_maximo_total' => 460
            ];
        }
        
        $nomeAluno = $usuario['nome'];
        
        // Buscar da tabela jogo
        $stmt = $pdo->prepare("
            SELECT 
                COALESCE(xp_jogo1, 0) as xp_jogo1,
                COALESCE(xp_jogo2, 0) as xp_jogo2,
                COALESCE(xp_jogo3, 0) as xp_jogo3,
                COALESCE(xp_total, 0) as xp_total
            FROM jogo 
            WHERE nome = ?
        ");
        $stmt->execute([$nomeAluno]);
        $result = $stmt->fetch();
        
        if ($result) {
            return [
                'xp_jogo1' => intval($result['xp_jogo1']),
                'xp_jogo2' => intval($result['xp_jogo2']),
                'xp_jogo3' => intval($result['xp_jogo3']),
                'xp_total' => intval($result['xp_total']),
                'xp_maximo_total' => 460
            ];
        } else {
            // Se não existir, calcular na hora
            $xp_jogo1 = obterXPTotalJogo1($pdo, $usuario_id);
            $xp_jogo2 = obterXPTotalJogo2($pdo, $usuario_id);
            $xp_jogo3 = obterXPTotalJogo3($pdo, $usuario_id);
            $xp_total = $xp_jogo1 + $xp_jogo2 + $xp_jogo3;
            
            return [
                'xp_jogo1' => $xp_jogo1,
                'xp_jogo2' => $xp_jogo2,
                'xp_jogo3' => $xp_jogo3,
                'xp_total' => $xp_total,
                'xp_maximo_total' => 460
            ];
        }
        
    } catch (PDOException $e) {
        error_log("❌ Erro ao obter XP consolidado: " . $e->getMessage());
        return [
            'xp_jogo1' => 0,
            'xp_jogo2' => 0,
            'xp_jogo3' => 0,
            'xp_total' => 0,
            'xp_maximo_total' => 460
        ];
    }
}

// ============================================
// FUNÇÕES DE COMPATIBILIDADE (para páginas existentes)
// ============================================

// Para páginas que usam as funções antigas
function obterXPTotal($pdo, $usuario_id) {
    return obterXPTotalJogo1($pdo, $usuario_id);
}

function obterXPFase($pdo, $usuario_id, $fase) {
    return obterXPFaseJogo1($pdo, $usuario_id, $fase);
}

function registrarXPFase($pdo, $usuario_id, $fase, $xp_ganho) {
    return registrarXPJogo1($pdo, $usuario_id, $fase, $xp_ganho);
}

// Para Jogo 2
function obterXPTotal2($pdo, $usuario_id) {
    return obterXPTotalJogo2($pdo, $usuario_id);
}

function obterXPFase2($pdo, $usuario_id, $fase) {
    return obterXPFaseJogo2($pdo, $usuario_id, $fase);
}

// Para Jogo 3  
function obterXPTotal3($pdo, $usuario_id) {
    return obterXPTotalJogo3($pdo, $usuario_id);
}

function obterXPFase3($pdo, $usuario_id, $fase) {
    return obterXPFaseJogo3($pdo, $usuario_id, $fase);
}

// ============================================
// FUNÇÕES DE VERIFICAÇÃO DE LOGIN
// ============================================

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
// FUNÇÕES DE ESTRELAS (COMPLETAS)
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
    
    // Para Fase 2, verifica Fase 1
    if ($fase_anterior == 1) {
        $estrelas_fase1 = obterEstrelasPorXP($pdo, $usuario_id, 1, true);
        return ($estrelas_fase1 >= 1); // Precisa de 1+ estrela na Fase 1
    }
    
    // Para Fase 3, verifica Fase 2  
    if ($fase_anterior == 2) {
        $estrelas_fase2 = obterEstrelasPorXP($pdo, $usuario_id, 2, true);
        return ($estrelas_fase2 >= 1); // Precisa de 1+ estrela na Fase 2
    }
    
    // Para Fase 4, verifica Fase 3
    if ($fase_anterior == 3) {
        $estrelas_fase3 = obterEstrelasPorXP($pdo, $usuario_id, 3, true);
        return ($estrelas_fase3 >= 1); // Precisa de 1+ estrela na Fase 3
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

/**
 * Obtém XP inicial para uma fase do Jogo 1 (soma das fases anteriores)
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
        error_log("Erro ao obter XP inicial da fase Jogo 1: " . $e->getMessage());
        return 0;
    }
}

/**
 * Registra XP completo do Jogo 1 (compatibilidade)
 */
function registrarXPCompleto($pdo, $usuario_id, $fase, $xp) {
    return registrarXPJogo1($pdo, $usuario_id, $fase, $xp);
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

/**
 * Obtém estrelas baseado no XP (sistema atualizado)
 */
function obterEstrelasPorXP($pdo, $usuario_id, $numero_fase, $fase_desbloqueada = false) {
    if (!$fase_desbloqueada) return 0;
    
    // Fase 1 usa sistema de estrelas tradicional
    if ($numero_fase == 1) {
        return contarEstrelasFase($pdo, $usuario_id, $numero_fase);
    }
    
    // Fase 2: Usa XP TOTAL do Jogo 1
    if ($numero_fase == 2) {
        $xp_total = obterXPTotalJogo1($pdo, $usuario_id);
        
        if ($xp_total >= 8) return 3;
        elseif ($xp_total >= 5) return 2;
        elseif ($xp_total >= 2) return 1;
        return 0;
    }
    
    // Fase 3: Usa XP TOTAL do Jogo 2
    if ($numero_fase == 3) {
        $xp_total_jogo2 = obterXPTotalJogo2($pdo, $usuario_id);
        
        if ($xp_total_jogo2 >= 32) return 3;      // 80% ou mais (32-40 XP)
        elseif ($xp_total_jogo2 >= 20) return 2;  // 50% ou mais (20-31 XP)  
        elseif ($xp_total_jogo2 >= 8) return 1;   // 20% ou mais (8-19 XP)
        return 0;                                 // Menos de 8 XP
    }
    
    // Fase 4: Usa XP TOTAL do Jogo 3
    if ($numero_fase == 4) {
        $xp_total_jogo3 = obterXPTotalJogo3($pdo, $usuario_id);
        
        if ($xp_total_jogo3 >= 256) return 3;     // 80% ou mais (256-320 XP)
        elseif ($xp_total_jogo3 >= 160) return 2; // 50% ou mais (160-255 XP)  
        elseif ($xp_total_jogo3 >= 64) return 1;  // 20% ou mais (64-159 XP)
        return 0;                                 // Menos de 64 XP
    }
    
    return 0;



    
}

// ============================================
// FUNÇÕES PARA JOGO 2 - XP E ESTRELAS
// ============================================

/**
 * Registra XP completo do Jogo 2 (compatibilidade)
 */
function registrarXPCompleto2($pdo, $usuario_id, $fase, $xp) {
    return registrarXPJogo2($pdo, $usuario_id, $fase, $xp);
}

/**
 * Obtém XP inicial para uma fase do Jogo 2 (soma das fases anteriores)
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
            return array_fill(1, 10, ['xp_obtido' => 0, 'xp_total_fase' => 4]);
        }
        
        $progresso = [];
        for ($i = 1; $i <= 10; $i++) {
            $progresso[$i] = [
                'xp_obtido' => $result["fase{$i}_xp"] ?? 0,
                'xp_total_fase' => 4  // 4 XP por fase
            ];
        }
        
        return $progresso;
    } catch (PDOException $e) {
        error_log("Erro ao obter progresso das fases Jogo 2: " . $e->getMessage());
        return array_fill(1, 10, ['xp_obtido' => 0, 'xp_total_fase' => 4]);
    }
}

// ============================================
// FUNÇÕES PARA JOGO 3 - XP E ESTRELAS
// ============================================

/**
 * Registra XP completo do Jogo 3 (compatibilidade)
 */
function registrarXPCompleto3($pdo, $usuario_id, $fase, $xp) {
    // Para Jogo 3, convertemos o XP em acerto/erro
    $acertou = ($xp > 0);
    return registrarXPJogo3($pdo, $usuario_id, $fase, $acertou);
}

/**
 * Obtém XP inicial para uma fase do Jogo 3 (soma das fases anteriores)
 */
function obterXPInicialFase3($pdo, $usuario_id, $fase) {
    if ($fase <= 1) {
        return 0;
    }
    
    try {
        $sql = "SELECT (";
        for ($i = 1; $i < $fase; $i++) {
            if ($i > 1) $sql .= " + ";
            $sql .= "COALESCE(fase" . $i . "_xp, 0)";
        }
        $sql .= ") as xp_anterior FROM xp_jogo3 WHERE usuario_id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        $result = $stmt->fetch();
        return $result ? intval($result['xp_anterior']) : 0;
    } catch (PDOException $e) {
        error_log("Erro ao obter XP inicial da fase Jogo 3: " . $e->getMessage());
        return 0;
    }
}

/**
 * Obtém progresso de todas as fases do Jogo 3
 */
function obterProgressoFases3($pdo, $usuario_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                fase1_xp, fase2_xp, fase3_xp, fase4_xp, fase5_xp,
                fase6_xp, fase7_xp, fase8_xp, total_xp
            FROM xp_jogo3 
            WHERE usuario_id = ?
        ");
        $stmt->execute([$usuario_id]);
        $result = $stmt->fetch();
        
        if (!$result) {
            return array_fill(1, 8, ['xp_obtido' => 0, 'xp_total_fase' => 40]);
        }
        
        $progresso = [];
        for ($i = 1; $i <= 8; $i++) {
            $progresso[$i] = [
                'xp_obtido' => $result["fase{$i}_xp"] ?? 0,
                'xp_total_fase' => 40  // 40 XP por fase
            ];
        }
        
        return $progresso;
    } catch (PDOException $e) {
        error_log("Erro ao obter progresso das fases Jogo 3: " . $e->getMessage());
        return array_fill(1, 8, ['xp_obtido' => 0, 'xp_total_fase' => 40]);
    }
}

// ============================================
// FUNÇÕES DE ESTRELAS PARA JOGO 2
// ============================================

/**
 * Calcula estrelas baseado no XP (para Jogo 2)
 */
function calcularEstrelasPorXP($xp_fase) {
    if ($xp_fase >= 3) return 3;      // 3-4 XP = 3 estrelas
    if ($xp_fase >= 2) return 2;      // 2 XP = 2 estrelas
    if ($xp_fase >= 1) return 1;      // 1 XP = 1 estrela
    return 0;                         // 0 XP = sem estrelas
}

/**
 * Salva estrelas na tabela fase_estrelas2
 */
function salvarEstrelasFase2($pdo, $usuario_id, $fase, $estrelas) {
    try {
        // Verificar se já existe registro
        $stmt = $pdo->prepare("
            SELECT id FROM fase_estrelas2 
            WHERE usuario_id = ? AND fase = ?
        ");
        $stmt->execute([$usuario_id, $fase]);
        $existe = $stmt->fetch();
        
        if ($existe) {
            // Atualizar apenas se as novas estrelas forem MAIORES
            $stmt = $pdo->prepare("
                UPDATE fase_estrelas2 
                SET estrelas = GREATEST(estrelas, ?)
                WHERE usuario_id = ? AND fase = ?
            ");
            $stmt->execute([$estrelas, $usuario_id, $fase]);
            error_log("⭐ Estrelas atualizadas (JOGO 2) - Fase $fase: $estrelas estrelas");
        } else {
            // Criar novo registro
            $stmt = $pdo->prepare("
                INSERT INTO fase_estrelas2 (usuario_id, fase, estrelas)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$usuario_id, $fase, $estrelas]);
            error_log("⭐ Estrelas criadas (JOGO 2) - Fase $fase: $estrelas estrelas");
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("❌ Erro ao salvar estrelas (JOGO 2): " . $e->getMessage());
        return false;
    }
}

/**
 * Obtém estrelas de uma fase específica do Jogo 2
 */
function obterEstrelasFase2($pdo, $usuario_id, $fase) {
    try {
        $stmt = $pdo->prepare("
            SELECT estrelas FROM fase_estrelas2 
            WHERE usuario_id = ? AND fase = ?
        ");
        $stmt->execute([$usuario_id, $fase]);
        $result = $stmt->fetch();
        return $result ? intval($result['estrelas']) : 0;
    } catch (PDOException $e) {
        error_log("Erro ao obter estrelas da fase Jogo 2: " . $e->getMessage());
        return 0;
    }
}

/**
 * Obtém todas as estrelas do Jogo 2
 */
function obterTodasEstrelasJogo2($pdo, $usuario_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT fase, estrelas FROM fase_estrelas2 
            WHERE usuario_id = ? 
            ORDER BY fase ASC
        ");
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Erro ao obter estrelas do Jogo 2: " . $e->getMessage());
        return [];
    }
}

// ============================================
// FUNÇÕES DE ESTRELAS PARA JOGO 3
// ============================================

/**
 * Salva estrelas na tabela fase_estrelas3
 */
function salvarEstrelasFase3($pdo, $usuario_id, $fase, $estrelas) {
    try {
        // Verificar se já existe registro
        $stmt = $pdo->prepare("
            SELECT id FROM fase_estrelas3 
            WHERE usuario_id = ? AND fase = ?
        ");
        $stmt->execute([$usuario_id, $fase]);
        $existe = $stmt->fetch();
        
        if ($existe) {
            // Atualizar apenas se as novas estrelas forem MAIORES
            $stmt = $pdo->prepare("
                UPDATE fase_estrelas3 
                SET estrelas = GREATEST(estrelas, ?)
                WHERE usuario_id = ? AND fase = ?
            ");
            $stmt->execute([$estrelas, $usuario_id, $fase]);
            error_log("⭐ Estrelas atualizadas (JOGO 3) - Fase $fase: $estrelas estrelas");
        } else {
            // Criar novo registro
            $stmt = $pdo->prepare("
                INSERT INTO fase_estrelas3 (usuario_id, fase, estrelas)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$usuario_id, $fase, $estrelas]);
            error_log("⭐ Estrelas criadas (JOGO 3) - Fase $fase: $estrelas estrelas");
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("❌ Erro ao salvar estrelas (JOGO 3): " . $e->getMessage());
        return false;
    }
}

/**
 * Obtém estrelas de uma fase específica do Jogo 3
 */
function obterEstrelasFase3($pdo, $usuario_id, $fase) {
    try {
        $stmt = $pdo->prepare("
            SELECT estrelas FROM fase_estrelas3 
            WHERE usuario_id = ? AND fase = ?
        ");
        $stmt->execute([$usuario_id, $fase]);
        $result = $stmt->fetch();
        return $result ? intval($result['estrelas']) : 0;
    } catch (PDOException $e) {
        error_log("Erro ao obter estrelas da fase Jogo 3: " . $e->getMessage());
        return 0;
    }
}

/**
 * Obtém todas as estrelas do Jogo 3
 */
function obterTodasEstrelasJogo3($pdo, $usuario_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT fase, estrelas FROM fase_estrelas3 
            WHERE usuario_id = ? 
            ORDER BY fase ASC
        ");
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Erro ao obter estrelas do Jogo 3: " . $e->getMessage());
        return [];
    }
}

// ============================================
// FUNÇÕES ADICIONAIS PARA PROGRESSO MOBILE
// ============================================

/**
 * Obtém progresso detalhado para mobile
 */
function obterProgressoMobile($pdo, $usuario_id) {
    $xp_consolidado = obterXPTotalConsolidado($pdo, $usuario_id);
    $resumo_progresso = obterResumoProgresso($pdo, $usuario_id);
    
    return [
        'xp' => $xp_consolidado,
        'progresso' => $resumo_progresso,
        'estrelas_jogo1' => obterEstrelasPorXP($pdo, $usuario_id, 2, true), // Estrelas do Jogo 1
        'estrelas_jogo2' => obterEstrelasJogo2($pdo, $usuario_id, true),    // Estrelas do Jogo 2  
        'estrelas_jogo3' => obterEstrelasJogo3($pdo, $usuario_id, true),    // Estrelas do Jogo 3
        'ultima_atualizacao' => date('Y-m-d H:i:s')
    ];
}

/**
 * Obtém progresso por fase para mobile
 */
function obterProgressoFaseMobile($pdo, $usuario_id, $fase) {
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
        $progresso_detalhado = $stmt->fetchAll();
        
        // Calcular estatísticas da fase
        $total_atividades = count($progresso_detalhado);
        $acertos = array_sum(array_column($progresso_detalhado, 'acertou'));
        $percentual_acertos = $total_atividades > 0 ? ($acertos / $total_atividades) * 100 : 0;
        
        return [
            'fase' => $fase,
            'total_atividades' => $total_atividades,
            'acertos' => $acertos,
            'percentual_acertos' => round($percentual_acertos, 1),
            'atividades' => $progresso_detalhado
        ];
        
    } catch (PDOException $e) {
        error_log("❌ Erro ao obter progresso da fase: " . $e->getMessage());
        return [
            'fase' => $fase,
            'total_atividades' => 0,
            'acertos' => 0,
            'percentual_acertos' => 0,
            'atividades' => []
        ];
    }
}

// ============================================
// FUNÇÕES DE ESTRELAS (mantidas para compatibilidade)
// ============================================

function obterEstrelasJogo2($pdo, $usuario_id, $fase_desbloqueada = false) {
    if (!$fase_desbloqueada) return 0;
    
    $xp_total = obterXPTotalJogo2($pdo, $usuario_id);
    
    if ($xp_total >= 32) return 3;
    elseif ($xp_total >= 20) return 2;  
    elseif ($xp_total >= 8) return 1;
    
    return 0;
}

function obterEstrelasJogo3($pdo, $usuario_id, $fase_desbloqueada = false) {
    if (!$fase_desbloqueada) return 0;
    
    $xp_total = obterXPTotalJogo3($pdo, $usuario_id);
    
    if ($xp_total >= 256) return 3;
    elseif ($xp_total >= 160) return 2;  
    elseif ($xp_total >= 64) return 1;
    
    return 0;
}

?>