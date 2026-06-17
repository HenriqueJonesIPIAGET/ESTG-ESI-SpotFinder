<?php
session_start();
require_once 'conexao.php';

header('Content-Type: application/json');

// 1. Proteger: só utilizadores logados podem comprar
if (!isset($_SESSION['utilizador_id'])) {
    echo json_encode(['status' => 'nao_autorizado']);
    exit;
}

$utilizador_id = $_SESSION['utilizador_id'];
$evento_id = $_POST['evento_id'] ?? 0;

if ($evento_id) {
    // 2. Ir à base de dados saber o preço atual deste evento
    $stmt = $pdo->prepare("SELECT preco FROM eventos WHERE id = ?");
    $stmt->execute([$evento_id]);
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($evento) {
        // 3. Simular a compra: Insere 1 bilhete na tabela compras com o valor do evento
        $ins = $pdo->prepare("INSERT INTO compras (utilizador_id, evento_id, quantidade, valor_total) VALUES (?, ?, 1, ?)");
        $ins->execute([$utilizador_id, $evento_id, $evento['preco']]);

        echo json_encode(['status' => 'sucesso']);
    } else {
        echo json_encode(['status' => 'erro']);
    }
} else {
    echo json_encode(['status' => 'erro']);
}
?>