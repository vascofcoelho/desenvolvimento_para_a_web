<?php
// submit_comment.php
// Recebe POST de comentário, exige utilizador autenticado em sessão

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/db.php';

// Verifica autenticação
if (empty($_SESSION['user_id'])) {
    // Não autenticado -> redireciona para página de login
    header('Location: login.html');
    exit;
}

$user_id = (int) $_SESSION['user_id'];

// Validação básica
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Método não permitido.';
    exit;
}

$id_artigo = isset($_POST['id_artigo']) ? (int) $_POST['id_artigo'] : 0;
$comentario = isset($_POST['comentario']) ? trim($_POST['comentario']) : '';

if ($id_artigo <= 0 || $comentario === '') {
    http_response_code(400);
    echo 'Dados inválidos.';
    exit;
}

if (mb_strlen($comentario) > 2000) {
    http_response_code(400);
    echo 'Comentário demasiado longo.';
    exit;
}

$conn = get_db();

// Inserir comentário
$sql = "INSERT INTO comentarios (id_user, comentario, id_artigo) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo 'Erro na BD.';
    exit;
}
$stmt->bind_param('isi', $user_id, $comentario, $id_artigo);
$ok = $stmt->execute();
$stmt->close();

if (!$ok) {
    http_response_code(500);
    echo 'Erro ao gravar comentário.';
    exit;
}

// Obter slug para redirecionar de volta ao artigo
$slug = '';
$sql = 'SELECT URL_slug FROM artigos WHERE id_artigo = ? LIMIT 1';
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param('i', $id_artigo);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) $slug = $row['URL_slug'];
    $stmt->close();
}

$conn->close();

// Redirecionar para a secção de comentários do artigo
if ($slug !== '') {
    header('Location: artigo.php?slug=' . urlencode($slug) . '#comentarios');
    exit;
} else {
    // Se não houver slug (incomum), apenas voltar à página anterior
    header('Location: index.php');
    exit;
}

?>
