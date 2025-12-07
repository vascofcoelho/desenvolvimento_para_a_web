<?php
// admin/delete_user.php - apagar utilizador (apenas administradores)
session_start();
require_once __DIR__ . '/../db.php';

if (empty($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }
$conn = get_db();
$uid = (int)($_SESSION['user_id'] ?? 0);
// require admin role
$stmt = $conn->prepare('SELECT role FROM Users WHERE id_user = ? LIMIT 1');
$stmt->bind_param('i', $uid);
$stmt->execute();
$res = $stmt->get_result();
if ($r = $res->fetch_assoc()) {
	$role = $r['role'] ?? 'user';
} else {
	$role = 'user';
}
$stmt->close();
if ($role !== 'admin') { echo 'Acesso negado.'; exit; }

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: users.php'); exit; }

// Não permitir que um administrador apague a si próprio
if ($id === $uid) { echo 'Não pode apagar o seu próprio utilizador.'; exit; }

$stmt = $conn->prepare('DELETE FROM Users WHERE id_user = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();

header('Location: users.php'); exit;
