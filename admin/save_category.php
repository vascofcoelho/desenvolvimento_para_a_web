<?php
// admin/save_category.php - handler para criar/editar categoria
session_start();
require_once __DIR__ . '/../db.php';

if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=admin/categories.php');
    exit;
}

$conn = get_db();
$uid = (int)($_SESSION['user_id'] ?? 0);
$stmt = $conn->prepare('SELECT role FROM Users WHERE id_user = ? LIMIT 1');
$stmt->bind_param('i', $uid);
$stmt->execute();
$res = $stmt->get_result();
$role = 'user';
if ($r = $res->fetch_assoc()) $role = $r['role'] ?? 'user';
$stmt->close();

if ($role !== 'admin') { echo 'Acesso negado.'; exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: categories.php'); exit; }

$id = isset($_POST['id_categoria']) ? (int)$_POST['id_categoria'] : 0;
$categoria = trim($_POST['categoria'] ?? '');
if ($categoria === '') { echo 'Nome da categoria é obrigatório.'; exit; }

// Verificar unicidade
$q = $conn->prepare('SELECT id_categoria FROM Categorias WHERE categoria = ?' . ($id>0 ? ' AND id_categoria != ?' : '') . ' LIMIT 1');
if ($id>0) $q->bind_param('si', $categoria, $id); else $q->bind_param('s', $categoria);
$q->execute();
$exists = $q->get_result()->fetch_assoc();
$q->close();
if ($exists) { echo 'Já existe uma categoria com esse nome.'; exit; }

if ($id > 0) {
    $upd = $conn->prepare('UPDATE Categorias SET categoria = ? WHERE id_categoria = ?');
    $upd->bind_param('si', $categoria, $id);
    $ok = $upd->execute();
    $upd->close();
} else {
    $ins = $conn->prepare('INSERT INTO Categorias (categoria) VALUES (?)');
    $ins->bind_param('s', $categoria);
    $ok = $ins->execute();
    $ins->close();
}

if (!$ok) { echo 'Erro ao gravar categoria.'; exit; }

header('Location: categories.php');
exit;
