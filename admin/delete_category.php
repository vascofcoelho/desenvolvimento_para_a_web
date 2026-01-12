<?php
// admin/delete_category.php
session_start();
require_once __DIR__ . '/../db.php';

if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=admin/categories.php');
    exit;
}

$conn = get_db();
$uid = (int)($_SESSION['user_id'] ?? 0);
$stmt = $conn->prepare('SELECT role FROM users WHERE id_user = ? LIMIT 1');
$stmt->bind_param('i', $uid);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
$stmt->close();
$role = $r['role'] ?? 'user';
if ($role !== 'admin') { echo 'Acesso negado.'; exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: categories.php'); exit; }

// Apagar categoria (artigos com essa categoria ficarão com id_categoria = NULL por FK)
$del = $conn->prepare('DELETE FROM categorias WHERE id_categoria = ?');
$del->bind_param('i', $id);
$del->execute();
$del->close();

header('Location: categories.php');
exit;
