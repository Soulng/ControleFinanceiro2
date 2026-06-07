<?php
header('Content-Type: application/json');

include 'auth_check.php'; // valida sessão e define $CURRENT_USER_ID
include 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['codigo'])) {
    echo json_encode(['success' => false, 'error' => 'Código não enviado']);
    exit;
}

$codigo = $data['codigo'];

// O WHERE usuario_id = ? garante que um usuário não consegue deletar
// transações de outro usuário, mesmo que envie o código correto
$stmt = $conn->prepare("DELETE FROM transacoes WHERE codigo = ? AND usuario_id = ?");

if (!$stmt) {
    echo json_encode(['success' => false, 'error' => $conn->error]);
    exit;
}

$stmt->bind_param("si", $codigo, $CURRENT_USER_ID);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Registro não encontrado ou sem permissão']);
    }
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

exit;
