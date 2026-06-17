<?php
session_start();
require_once 'conexao.php';

header('Content-Type: application/json');

// Verificar se o utilizador tem sessão iniciada
if (!isset($_SESSION['utilizador_id'])) {
    echo json_encode(['status' => 'nao_autorizado']);
    exit;
}

$utilizador_id = $_SESSION['utilizador_id'];
$evento_id = $_POST['evento_id'] ?? 0;

if ($evento_id) {
    // Verificar se este evento já está nos favoritos deste utilizador
    $stmt = $pdo->prepare("SELECT id FROM favoritos WHERE utilizador_id = ? AND evento_id = ?");
    $stmt->execute([$utilizador_id, $evento_id]);

    if ($stmt->rowCount() > 0) {
        // Se já está, remove (Un-favorite)
        $del = $pdo->prepare("DELETE FROM favoritos WHERE utilizador_id = ? AND evento_id = ?");
        $del->execute([$utilizador_id, $evento_id]);
        echo json_encode(['status' => 'sucesso', 'acao' => 'removido']);
    } else {
        // Se não está, adiciona (Favorite)
        $ins = $pdo->prepare("INSERT INTO favoritos (utilizador_id, evento_id) VALUES (?, ?)");
        $ins->execute([$utilizador_id, $evento_id]);
        echo json_encode(['status' => 'sucesso', 'acao' => 'adicionado']);
    }
} else {
    echo json_encode(['status' => 'erro']);
}
?>