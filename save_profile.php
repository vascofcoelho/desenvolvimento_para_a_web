<?php
session_start();
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: profile.php'); exit; }
if (empty($_SESSION['user_id'])) { header('Location: login.php?redirect=profile.php'); exit; }

$conn = get_db();
$uid = (int)$_SESSION['user_id'];

$id = (int)($_POST['id_user'] ?? 0);
if ($id !== $uid) { header('Location: profile.php?error=' . urlencode('Acesso negado.')); exit; }

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$first = trim($_POST['first_name'] ?? '');
$last = trim($_POST['last_name'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

if ($username === '') { header('Location: profile.php?error=' . urlencode('Nome de utilizador não pode ficar vazio.')); exit; }
if ($password !== '' && $password !== $password_confirm) { header('Location: profile.php?error=' . urlencode('As palavras-passe não coincidem.')); exit; }

// Verificar se username está em uso por outro user
$stmt = $conn->prepare('SELECT id_user FROM Users WHERE username = ? AND id_user != ? LIMIT 1');
$stmt->bind_param('si', $username, $uid);
$stmt->execute();
$res = $stmt->get_result();
if ($res && $res->fetch_assoc()) { $stmt->close(); header('Location: profile.php?error=' . urlencode('Nome de utilizador já em uso.')); exit; }
$stmt->close();

// Handle avatar upload if present
$avatarFilename = null;
if (!empty($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['avatar'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $maxSize = 2 * 1024 * 1024; // 2MB
        if ($file['size'] > $maxSize) {
            header('Location: profile.php?error=' . urlencode('Ficheiro demasiado grande (máx 2MB).')); exit;
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
        if (!isset($allowed[$mime])) {
            header('Location: profile.php?error=' . urlencode('Tipo de ficheiro inválido.')); exit;
        }
        $ext = $allowed[$mime];
        $avatarsDir = __DIR__ . '/imgs/avatars';
        if (!is_dir($avatarsDir)) mkdir($avatarsDir, 0755, true);
        $avatarFilename = 'avatar_user_' . $uid . '_' . time() . '.' . $ext;
        $dest = $avatarsDir . '/' . $avatarFilename;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            header('Location: profile.php?error=' . urlencode('Erro ao guardar o ficheiro.')); exit;
        }
        // web path to saved avatar
        $avatarWebPath = 'imgs/avatars/' . $avatarFilename;
    } else {
        header('Location: profile.php?error=' . urlencode('Erro no upload do ficheiro.')); exit;
    }
}

// Ensure avatar column exists; add if missing
$colStmt = $conn->prepare("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Users' AND COLUMN_NAME = 'avatar'");
$colStmt->execute();
$colRes = $colStmt->get_result();
$hasAvatarCol = false;
if ($colRes && ($cr = $colRes->fetch_assoc())) { $hasAvatarCol = ((int)$cr['cnt'] > 0); }
$colStmt->close();
if (!$hasAvatarCol && $avatarFilename !== null) {
    // try to add column (best-effort)
    $conn->query("ALTER TABLE Users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL");
    $hasAvatarCol = true;
}

if ($password !== '') {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $salt = '';
    if ($hasAvatarCol && isset($avatarWebPath)) {
        $stmt = $conn->prepare('UPDATE Users SET username = ?, email = ?, first_name = ?, last_name = ?, password = ?, salt = ?, avatar = ? WHERE id_user = ?');
        $stmt->bind_param('sssssssi', $username, $email, $first, $last, $hash, $salt, $avatarWebPath, $uid);
    } else {
        $stmt = $conn->prepare('UPDATE Users SET username = ?, email = ?, first_name = ?, last_name = ?, password = ?, salt = ? WHERE id_user = ?');
        $stmt->bind_param('ssssssi', $username, $email, $first, $last, $hash, $salt, $uid);
    }
} else {
    if ($hasAvatarCol && isset($avatarWebPath)) {
        $stmt = $conn->prepare('UPDATE Users SET username = ?, email = ?, first_name = ?, last_name = ?, avatar = ? WHERE id_user = ?');
        $stmt->bind_param('sssssi', $username, $email, $first, $last, $avatarWebPath, $uid);
    } else {
        $stmt = $conn->prepare('UPDATE Users SET username = ?, email = ?, first_name = ?, last_name = ? WHERE id_user = ?');
        $stmt->bind_param('ssssi', $username, $email, $first, $last, $uid);
    }
}

$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    // Atualizar sessão com novo username
    $_SESSION['username'] = $username;
    // Atualizar avatar em sessão se foi enviado
    if ($hasAvatarCol && isset($avatarWebPath)) {
        // store raw path (e.g. 'imgs/avatars/...') in session; navbar will prefix base when rendering
        $_SESSION['avatar'] = $avatarWebPath;
    }
    header('Location: profile.php?success=1'); exit;
} else {
    header('Location: profile.php?error=' . urlencode('Erro ao salvar perfil.')); exit;
}
