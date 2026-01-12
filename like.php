<?php
header('Content-Type: application/json; charset=utf-8');
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Autenticação necessária']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data) || empty($data['id_artigo'])) {
    echo json_encode(['success' => false, 'error' => 'Pedido inválido']);
    exit;
}

$id_artigo = (int)$data['id_artigo'];
if ($id_artigo <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID de artigo inválido']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$conn = get_db();

// Verifica se já existe
$stmt = $conn->prepare('SELECT id_like FROM likes WHERE id_artigo = ? AND id_user = ? LIMIT 1');
$stmt->bind_param('ii', $id_artigo, $user_id);
$stmt->execute();
$res = $stmt->get_result();
$exists = (bool) $res->fetch_assoc();
$stmt->close();

$ok = true;
$liked = false;
if ($exists) {
    $del = $conn->prepare('DELETE FROM likes WHERE id_artigo = ? AND id_user = ?');
    $del->bind_param('ii', $id_artigo, $user_id);
    $ok = $del->execute();
    $del->close();
    $liked = false;
} else {
    $ins = $conn->prepare('INSERT INTO likes (id_user, id_artigo) VALUES (?, ?)');
    $ins->bind_param('ii', $user_id, $id_artigo);
    $ok = $ins->execute();
    $ins->close();
    $liked = true;
}

if (!$ok) {
    echo json_encode(['success' => false, 'error' => 'Erro no servidor ao processar like']);
    exit;
}

$cstmt = $conn->prepare('SELECT COUNT(*) AS cnt FROM likes WHERE id_artigo = ?');
$cstmt->bind_param('i', $id_artigo);
$cstmt->execute();
$cres = $cstmt->get_result();
$count = (int) ($cres->fetch_assoc()['cnt'] ?? 0);
$cstmt->close();
$conn->close();

echo json_encode(['success' => true, 'liked' => $liked, 'count' => $count]);
exit;
