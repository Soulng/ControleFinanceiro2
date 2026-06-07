<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
include 'db.php';

// FUNÇÃO DE COMPATIBILIDADE PARA PHP ANTIGO 
if (!function_exists('password_verify')) {
    function password_verify($password, $hash) {
        return crypt($password, $hash) === $hash;
    }
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Dados não recebidos']);
    exit;
}

$email = isset($data['email']) ? trim($data['email']) : '';
$senha = isset($data['senha']) ? $data['senha'] : '';

if (!$email || !$senha) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Email e senha são obrigatórios']);
    exit;
}

$stmt = $conn->prepare("SELECT id, nome, senha FROM usuarios WHERE email = ?");
if (!$stmt) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => $conn->error]);
    exit;
}

$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Email não encontrado']);
    exit;
}

$row = $result->fetch_assoc();

$senhaValida = false;
if (password_verify($senha, $row['senha'])) {
    $senhaValida = true;
} elseif ($senha === $row['senha']) {
    $senhaValida = true;
} elseif (md5($senha) === $row['senha']) {
    $senhaValida = true;
}

if (!$senhaValida) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Senha incorreta']);
    exit;
}

$_SESSION['user_id']   = (int) $row['id'];
$_SESSION['user_name'] = $row['nome'];

$conn->close();

ob_clean();
echo json_encode([
    'success'   => true,
    'user_id'   => $row['id'],
    'user_name' => $row['nome']
]);
