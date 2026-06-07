<?php
include 'db.php';

$result = $conn->query("DESCRIBE metas");

if ($result->num_rows > 0) {
    echo "Estrutura da tabela 'metas':\n";
    while($row = $result->fetch_assoc()) {
        echo $row["Field"] . " - " . $row["Type"] . "\n";
    }
} else {
    echo "Tabela não encontrada.";
}

$conn->close();
?>