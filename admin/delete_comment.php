<?php
// admin/delete_comment.php
session_start();
require_once __DIR__ . '/../db.php';

if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=admin/comments.php');
    exit;
}

$conn = get_db();
$uid = (int)($_SESSION['user_id'] ?? 0);
$stmt = $conn->prepare('SELECT role FROM Users WHERE id_user = ? LIMIT 1');
$stmt->bind_param('i', $uid);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
$stmt->close();
$role = $r['role'] ?? 'user';
if ($role !== 'admin' && $role !== 'moderator') { echo 'Acesso negado.'; exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: comments.php'); exit; }

$del = $conn->prepare('DELETE FROM Comentarios WHERE id_comentario = ?');
$del->bind_param('i', $id);
$del->execute();
$del->close();

// Redireciona de volta ao admin/comments.php
    // Redireciona para onde for pedido (ex: voltar ao artigo) ou para a listagem
    $redirect = 'comments.php';
    if (!empty($_GET['redirect'])) {
        // Segurança: permitir apenas redirecionamentos internos simples
        $r = $_GET['redirect'];
        if (strpos($r, '/') === false && strpos($r, 'http') === false) {
            $redirect = $r;
        }
    }
    header('Location: ' . $redirect);
    exit;
