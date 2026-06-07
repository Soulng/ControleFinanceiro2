<?php
$host = "127.0.0.1";
$user = "root";
$pass = "usbw"; 
$db   = "p12semteste_paginicial";

$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset("utf8");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Erro na conexão: ' . $conn->connect_error]);
    exit;
}
