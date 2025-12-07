<?php
// register_submit.php - handler para registo público
require_once __DIR__ . '/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php'); exit;
}

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';
$first = trim($_POST['first_name'] ?? '');
$last = trim($_POST['last_name'] ?? '');

if ($username === '' || $password === '') {
    header('Location: register.php?error=' . urlencode('Preencha o nome de utilizador e a palavra-passe.'));
    exit;
}
if ($password !== $password_confirm) {
    header('Location: register.php?error=' . urlencode('As palavras-passe não coincidem.'));
    exit;
}

$conn = get_db();
// Verificar existência de username
$stmt = $conn->prepare('SELECT id_user FROM Users WHERE username = ? LIMIT 1');
$stmt->bind_param('s', $username);
$stmt->execute();
$res = $stmt->get_result();
if ($res && $res->fetch_assoc()) {
    $stmt->close();
    header('Location: register.php?error=' . urlencode('Nome de utilizador já existe.'));
    exit;
}
$stmt->close();

$hash = password_hash($password, PASSWORD_DEFAULT);
$salt = '';
$role = 'user';
// Ensure role column exists
$colStmt = $conn->prepare("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Users' AND COLUMN_NAME = 'role'");
$colStmt->execute();
$colRes = $colStmt->get_result();
$hasRole = false;
if ($colRes && ($cr = $colRes->fetch_assoc())) { $hasRole = ((int)$cr['cnt'] > 0); }
$colStmt->close();
if (!$hasRole) {
    // best-effort add column
    $conn->query("ALTER TABLE Users ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'user'");
    $hasRole = true;
}

if ($hasRole) {
    $stmt = $conn->prepare('INSERT INTO Users (username, email, password, salt, first_name, last_name, role) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('sssssss', $username, $email, $hash, $salt, $first, $last, $role);
} else {
    // fallback if cannot add role column
    $stmt = $conn->prepare('INSERT INTO Users (username, email, password, salt, first_name, last_name) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('ssssss', $username, $email, $hash, $salt, $first, $last);
}
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    header('Location: login.php?registered=1'); exit;
} else {
    header('Location: register.php?error=' . urlencode('Erro ao criar a conta.')); exit;
}
