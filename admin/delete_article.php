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
$stmt = $conn->prepare('SELECT role, username FROM users WHERE id_user = ? LIMIT 1');
$stmt->bind_param('i', $uid);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
$stmt->close();
$role = $r['role'] ?? 'user';
$username = $r['username'] ?? '';


$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: articles.php'); exit; }

// Opcional: obter imagem (adiar remoção até confirmação da BD)
$stmt = $conn->prepare('SELECT foto FROM artigos WHERE id_artigo = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$fotoRow = $res->fetch_assoc();
$stmt->close();
$foto_path = ($fotoRow && !empty($fotoRow['foto'])) ? $fotoRow['foto'] : '';

// If author, ensure they own the article
$allow = false;
if ($role === 'admin') $allow = true;
elseif ($role === 'author') {
    $stmt = $conn->prepare('SELECT autor FROM artigos WHERE id_artigo = ? LIMIT 1');
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

// Delete dependent rows in a transaction: comentarios, likes, then artigo
$conn->begin_transaction();
$ok = true;
// delete comentarios
$d1 = $conn->prepare('DELETE FROM comentarios WHERE id_artigo = ?');
if ($d1) {
    $d1->bind_param('i', $id);
    if (!$d1->execute()) $ok = false;
    $d1->close();
} else {
    $ok = false;
}
// delete likes
$d2 = $conn->prepare('DELETE FROM likes WHERE id_artigo = ?');
if ($d2) {
    $d2->bind_param('i', $id);
    if (!$d2->execute()) $ok = false;
    $d2->close();
} else {
    $ok = false;
}
// delete artigo
$d3 = $conn->prepare('DELETE FROM artigos WHERE id_artigo = ?');
if ($d3) {
    $d3->bind_param('i', $id);
    if (!$d3->execute()) $ok = false;
    $d3->close();
} else {
    $ok = false;
}

if ($ok) {
    $conn->commit();
    // remove image file if present
    if (!empty($foto_path)) {
        $path = __DIR__ . '/../' . $foto_path;
        if (file_exists($path)) @unlink($path);
    }
    header('Location: articles.php');
    exit;
} else {
    $conn->rollback();
    echo 'Erro ao eliminar artigo. Tente novamente.';
    exit;
}
