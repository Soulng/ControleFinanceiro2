<?php
include 'db.php';

$sql = "ALTER TABLE usuarios MODIFY data_nascimento VARCHAR(20)";

if ($conn->query($sql) === TRUE) {
    echo "Tabela alterada com sucesso!";
} else {
    echo "Erro ao alterar tabela: " . $conn->error;
}

$conn->close();
?>