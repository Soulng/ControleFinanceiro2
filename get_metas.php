<?php
header('Content-Type: application/json');

include 'auth_check.php'; // valida sessão e define $CURRENT_USER_ID
include 'db.php';

$stmt = $conn->prepare(
    "SELECT id, nome_meta, valor_total, valor_guardado, descricao, imagem_url
     FROM metas
     WHERE usuario_id = ?
     ORDER BY id DESC"
);

if (!$stmt) {
    echo json_encode(['success' => false, 'error' => $conn->error]);
    exit;
}

$stmt->bind_param('i', $CURRENT_USER_ID);
$stmt->execute();
$resultado = $stmt->get_result();

$metas = [];

while ($row = $resultado->fetch_assoc()) {
    $progresso = 0;
    if ((float)$row['valor_total'] > 0) {
        $progresso = min(((float)$row['valor_guardado'] / (float)$row['valor_total']) * 100, 100);
    }

    $metas[] = [
        'id'         => $row['id'],
        'nome'       => $row['nome_meta'],
        'valorTotal' => (float) $row['valor_total'],
        'valorAtual' => (float) $row['valor_guardado'],
        'descricao'  => $row['descricao'],
        'iconeURL'   => $row['imagem_url'],
        'progresso'  => $progresso
    ];
}

echo json_encode(['success' => true, 'metas' => $metas]);
exit;
