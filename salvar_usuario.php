<?php
header('Content-Type: application/json');
include 'db.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Dados não recebidos']);
    exit;
}

$nome = isset($data['nome']) ? trim($data['nome']) : '';
$email = isset($data['email']) ? trim($data['email']) : '';
$senha = isset($data['senha']) ? $data['senha'] : '';
$nascimento = isset($data['nascimento']) ? trim($data['nascimento']) : '';
$ocupacao = isset($data['ocupacao']) ? trim($data['ocupacao']) : '';

if (!$nome || !$email || !$senha || !$nascimento) {
    echo json_encode(['success' => false, 'error' => 'Campos obrigatórios faltando']);
    exit;
}

$idade = 0;
$senhaHash = $senha;

$stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, data_nascimento, idade, ocupacao) VALUES (?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => $conn->error]);
    exit;
}

$stmt->bind_param('ssssis', $nome, $email, $senhaHash, $nascimento, $idade, $ocupacao);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    $error = $stmt->error;
    if (strpos($error, 'Duplicate entry') !== false) {
        $error = 'Email já cadastrado.';
    }
    echo json_encode(['success' => false, 'error' => $error]);
}

$conn->close();
?>