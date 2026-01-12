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
$biografia = trim($_POST['biografia'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

if ($username === '') { header('Location: profile.php?error=' . urlencode('Nome de utilizador não pode ficar vazio.')); exit; }
if ($password !== '' && $password !== $password_confirm) { header('Location: profile.php?error=' . urlencode('As palavras-passe não coincidem.')); exit; }

// Verificar se username está em uso por outro user
$stmt = $conn->prepare('SELECT id_user FROM users WHERE username = ? AND id_user != ? LIMIT 1');
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
$colStmt = $conn->prepare("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'avatar'");
$colStmt->execute();
$colRes = $colStmt->get_result();
$hasAvatarCol = false;
if ($colRes && ($cr = $colRes->fetch_assoc())) { $hasAvatarCol = ((int)$cr['cnt'] > 0); }
$colStmt->close();
if (!$hasAvatarCol && $avatarFilename !== null) {
    // try to add column (best-effort)
    $conn->query("ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL");
    $hasAvatarCol = true;
}

// Ensure biografia column exists; add if missing
$colStmt2 = $conn->prepare("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'biografia'");
$colStmt2->execute();
$colRes2 = $colStmt2->get_result();
$hasBiografiaCol = false;
if ($colRes2 && ($cr2 = $colRes2->fetch_assoc())) { $hasBiografiaCol = ((int)$cr2['cnt'] > 0); }
$colStmt2->close();
if (!$hasBiografiaCol) {
    // try to add column (best-effort)
    try {
        $conn->query("ALTER TABLE users ADD COLUMN biografia TEXT DEFAULT NULL");
        $hasBiografiaCol = true;
    } catch (Exception $e) {
        // Ignore if already exists or error
    }
}

// Build UPDATE query dynamically based on what fields are available
$updateFields = ['username', 'email', 'first_name', 'last_name'];
$updateValues = [$username, $email, $first, $last];
$types = 'ssss';

if ($password !== '') {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $salt = '';
    $updateFields[] = 'password';
    $updateFields[] = 'salt';
    $updateValues[] = $hash;
    $updateValues[] = $salt;
    $types .= 'ss';
}

if ($hasAvatarCol && isset($avatarWebPath)) {
    $updateFields[] = 'avatar';
    $updateValues[] = $avatarWebPath;
    $types .= 's';
}

if ($hasBiografiaCol) {
    $updateFields[] = 'biografia';
    $updateValues[] = $biografia;
    $types .= 's';
}

$setClause = implode(' = ?, ', $updateFields) . ' = ?';
$sql = "UPDATE users SET $setClause WHERE id_user = ?";
$updateValues[] = $uid;
$types .= 'i';
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$updateValues);

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
