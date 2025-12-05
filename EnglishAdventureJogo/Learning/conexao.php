<?php
$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "englishadventure";

try {
  $pdo = new PDO("mysql:host=$servidor;dbname=$banco;charset=utf8", $usuario, $senha);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  // mostra erro em caso de problema com a conexão
  die("Erro ao conectar ao banco: " . $e->getMessage());
}
?>
