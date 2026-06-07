<?php
header('Content-Type: application/json');

include 'auth_check.php'; // valida sessão e define $CURRENT_USER_ID
include 'db.php';

$stmt = $conn->prepare(
    "SELECT codigo, data_reg, descricao, categoria, tipo, valor
     FROM transacoes
     WHERE usuario_id = ?
     ORDER BY data_reg DESC"
);

if (!$stmt) {
    echo json_encode(['success' => false, 'error' => $conn->error]);
    exit;
}

$stmt->bind_param('i', $CURRENT_USER_ID);
$stmt->execute();
$resultado = $stmt->get_result();

$transacoes = [];

while ($row = $resultado->fetch_assoc()) {
    $tipoRaw = strtolower(trim($row['tipo']));

    if ($tipoRaw === 'renda' || $tipoRaw === 'option2' || $tipoRaw === 'receita') {
        $tipoNorm = 'receita';
    } else {
        $tipoNorm = 'despesa';
    }

    $transacoes[] = [
        'codigo'    => $row['codigo'],
        'data'      => $row['data_reg'],
        'descricao' => $row['descricao'],
        'categoria' => $row['categoria'],
        'tipo'      => $tipoNorm,
        'valor'     => (float) $row['valor'],
    ];
}

echo json_encode(['success' => true, 'transacoes' => $transacoes]);
exit;
