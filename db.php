<?php
// db.php
// Ligação simples à base de dados usando mysqli (Laragon defaults)

function get_db()
{
    // Ajuste estas credenciais se necessário
    $db_host = '127.0.0.1';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'rabbithead';
    $db_port = 3306;

    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
    if ($conn->connect_errno) {
        http_response_code(500);
        echo 'Erro de ligação à base de dados: ' . htmlspecialchars($conn->connect_error);
        exit;
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

?>
