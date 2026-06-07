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

$stmt = $conn->prepare(
    "INSERT INTO metas (nome_meta, valor_total, valor_guardado, descricao, imagem_url, usuario_id)
     VALUES (?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    echo json_encode(['success' => false, 'error' => $conn->error]);
    exit;
}

$stmt->bind_param(
    "sddssi",
    $data['nome'],
    $data['valorTotal'],
    $data['valorAtual'],
    $data['descricao'],
    $data['iconeURL'],
    $CURRENT_USER_ID
);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}
?>
