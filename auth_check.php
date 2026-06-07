<?php
/**
 * auth_check.php
 * Inclua este arquivo no topo de qualquer PHP que precise de autenticação.
 * Garante que session_start() foi chamado e que o usuário está logado.
 * Se não estiver logado, responde JSON de erro e encerra.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Não autenticado', 'redirect' => 'index.html']);
    exit;
}

// Disponibiliza o ID do usuário autenticado como constante
$CURRENT_USER_ID = (int) $_SESSION['user_id'];
