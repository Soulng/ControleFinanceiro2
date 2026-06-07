<?php
/**
 * logout.php
 * Destrói a sessão PHP e responde JSON de confirmação.
 * O JavaScript chama este endpoint e só então redireciona.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Apaga todas as variáveis de sessão
$_SESSION = [];

// Destrói o cookie de sessão no navegador
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destrói a sessão no servidor
session_destroy();

header('Content-Type: application/json');
echo json_encode(['success' => true]);
exit;
?>
