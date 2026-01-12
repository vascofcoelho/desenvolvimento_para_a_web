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
 $stmt = $conn->prepare('SELECT role, username FROM users WHERE id_user = ? LIMIT 1');
 $stmt->bind_param('i', $uid);
 $stmt->execute();
 $r = $stmt->get_result()->fetch_assoc();
 $stmt->close();
 $role = $r['role'] ?? 'user';
 $username = $r['username'] ?? '';
 if ($role !== 'admin' && $role !== 'moderator' && $role !== 'author') { echo 'Acesso negado.'; exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: comments.php'); exit; }

// If author, ensure the comment belongs to an article authored by them
if ($role === 'author') {
    $s = $conn->prepare('SELECT a.autor FROM comentarios c LEFT JOIN artigos a ON c.id_artigo = a.id_artigo WHERE c.id_comentario = ? LIMIT 1');
    $s->bind_param('i', $id);
    $s->execute();
    $row = $s->get_result()->fetch_assoc();
    $s->close();
    if (!$row) { echo 'Comentário não encontrado.'; exit; }
    $art_autor = $row['autor'];
    $allow = false;
    if (is_numeric($art_autor)) {
        if ((int)$art_autor === $uid) $allow = true;
    } else {
        if ($art_autor === $username) $allow = true;
    }
    if (!$allow) { echo 'Acesso negado.'; exit; }
}

$del = $conn->prepare('DELETE FROM comentarios WHERE id_comentario = ?');
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
