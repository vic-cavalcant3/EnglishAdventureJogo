<?php
// Arquivo para testar se a sessão está funcionando
session_start();

echo "<h2>Teste de Sessão</h2>";
echo "<h3>Dados da Sessão:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>Status:</h3>";
if (isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id'])) {
    echo "✅ Usuário está logado!<br>";
    echo "ID: " . $_SESSION['usuario_id'] . "<br>";
    echo "Nome: " . ($_SESSION['usuario_nome'] ?? 'Não definido') . "<br>";
} else {
    echo "❌ Usuário NÃO está logado!<br>";
    echo "A sessão não contém usuario_id<br>";
}

echo "<br><a href='fases.php'>Ir para Fases</a>";
?>