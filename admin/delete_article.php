<?php
// admin/delete_article.php
session_start();
require_once __DIR__ . '/../db.php';

if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=admin/articles.php');
    exit;
}

$conn = get_db();
$uid = (int)($_SESSION['user_id'] ?? 0);
// check role
$stmt = $conn->prepare('SELECT role, username FROM Users WHERE id_user = ? LIMIT 1');
$stmt->bind_param('i', $uid);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
$stmt->close();
$role = $r['role'] ?? 'user';
$username = $r['username'] ?? '';


$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: articles.php'); exit; }

// Opcional: obter imagem e remover ficheiro
$stmt = $conn->prepare('SELECT foto FROM Artigos WHERE id_artigo = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();
if ($row && !empty($row['foto'])) {
    $path = __DIR__ . '/../' . $row['foto'];
    if (file_exists($path)) @unlink($path);
}

// If author, ensure they own the article
$allow = false;
if ($role === 'admin') $allow = true;
elseif ($role === 'author') {
    $stmt = $conn->prepare('SELECT autor FROM Artigos WHERE id_artigo = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    if ($row) {
        // autor may be stored as id or legacy username
        if (is_numeric($row['autor'])) {
            if ((int)$row['autor'] === $uid) $allow = true;
        } else {
            if ($row['autor'] === $username) $allow = true;
        }
    }
}

if (!$allow) { echo 'Acesso negado.'; exit; }

$del = $conn->prepare('DELETE FROM Artigos WHERE id_artigo = ?');
$del->bind_param('i', $id);
$del->execute();
$del->close();

header('Location: articles.php');
exit;
