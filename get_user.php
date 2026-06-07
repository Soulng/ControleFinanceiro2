<?php
header('Content-Type: application/json');

include 'auth_check.php'; // valida sessão e define $CURRENT_USER_ID
include 'db.php';

// REMOVIDO: o bypass por $_GET['id'] que permitia ver dados de qualquer usuário.
// Agora o ID vem EXCLUSIVAMENTE da sessão PHP, via auth_check.php.

$stmt = $conn->prepare(
    "SELECT nome, email, data_nascimento, idade, ocupacao
     FROM usuarios
     WHERE id = ?"
);

if (!$stmt) {
    echo json_encode(['success' => false, 'error' => $conn->error]);
    exit;
}

$stmt->bind_param('i', $CURRENT_USER_ID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Usuário não encontrado']);
    exit;
}

$user = $result->fetch_assoc();

echo json_encode(['success' => true, 'user' => $user]);

$conn->close();
?>
