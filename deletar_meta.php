<?php
header('Content-Type: application/json');

include 'auth_check.php'; // valida sessão e define $CURRENT_USER_ID
include 'db.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['id'])) {
    echo json_encode(['success' => false, 'error' => 'ID não fornecido']);
    exit;
}

// O WHERE usuario_id = ? impede deleção de metas de outros usuários
$stmt = $conn->prepare("DELETE FROM metas WHERE id = ? AND usuario_id = ?");

if (!$stmt) {
    echo json_encode(['success' => false, 'error' => $conn->error]);
    exit;
}

$stmt->bind_param("ii", $data['id'], $CURRENT_USER_ID);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Meta não encontrada ou sem permissão']);
    }
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}
?>
