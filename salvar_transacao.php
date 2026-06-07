<?php
header('Content-Type: application/json');

include 'auth_check.php'; // valida sessão e define $CURRENT_USER_ID
include 'db.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Dados não recebidos pelo PHP']);
    exit;
}

// Vincula a transação ao usuário autenticado via sessão — nunca confia em dados do cliente
$stmt = $conn->prepare(
    "INSERT INTO transacoes (codigo, data_reg, descricao, categoria, tipo, valor, usuario_id)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    echo json_encode(['success' => false, 'error' => $conn->error]);
    exit;
}

$stmt->bind_param(
    "issssdi",
    $data['codigo'],
    $data['data'],
    $data['descricao'],
    $data['categoria'],
    $data['tipo'],
    $data['valor'],
    $CURRENT_USER_ID
);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}
?>
