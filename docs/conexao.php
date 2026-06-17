<?php
$host = 'localhost';
$dbname = 'db_spot';
$user = 'root'; // Utilizador padrão do XAMPP
$pass = '';     // Password padrão do XAMPP (vazia)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    // Configurar o PDO para lançar exceções em caso de erro
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao ligar à base de dados: " . $e->getMessage());
}
?>






