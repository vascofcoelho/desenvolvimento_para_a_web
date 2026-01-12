<?php
session_start();
require_once __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: users.php'); exit; }

if (empty($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }
$conn = get_db();
$uid = (int)($_SESSION['user_id'] ?? 0);
// check role of current user
$stmt = $conn->prepare('SELECT role FROM users WHERE id_user = ? LIMIT 1');
$stmt->bind_param('i', $uid);
$stmt->execute();
$res = $stmt->get_result();
$role = 'user';
if ($r = $res->fetch_assoc()) $role = $r['role'] ?? 'user';
$stmt->close();
if ($role !== 'admin') { echo 'Acesso negado.'; exit; }

$id = (int)($_POST['id_user'] ?? 0);
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$first = trim($_POST['first_name'] ?? '');
$last = trim($_POST['last_name'] ?? '');
$role_new = trim($_POST['role'] ?? 'user');
$password = $_POST['password'] ?? '';

// Prevent admins from changing their own role
if ($id > 0 && $id === $uid) {
    // fetch current role and force it
    $s = $conn->prepare('SELECT role FROM users WHERE id_user = ? LIMIT 1');
    $s->bind_param('i', $uid);
    $s->execute();
    $rr = $s->get_result()->fetch_assoc();
    $s->close();
    if ($rr && !empty($rr['role'])) {
        $role_new = $rr['role'];
    }
}

if ($id > 0) {
    // editar
    if ($password !== '') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('UPDATE users SET username = ?, email = ?, first_name = ?, last_name = ?, role = ?, password = ? WHERE id_user = ?');
        $stmt->bind_param('ssssssi', $username, $email, $first, $last, $role_new, $hash, $id);
    } else {
        $stmt = $conn->prepare('UPDATE users SET username = ?, email = ?, first_name = ?, last_name = ?, role = ? WHERE id_user = ?');
        $stmt->bind_param('sssssi', $username, $email, $first, $last, $role_new, $id);
    }
    $stmt->execute();
    $stmt->close();
} else {
    // criar
    if ($password === '') { $password = bin2hex(random_bytes(6)); }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $role_new = !empty($role_new) ? $role_new : 'user';
    $stmt = $conn->prepare('INSERT INTO users (username, email, first_name, last_name, password, role) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('ssssss', $username, $email, $first, $last, $hash, $role_new);
    $stmt->execute();
    $stmt->close();
}

header('Location: users.php'); exit;
