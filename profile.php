<?php
session_start();
require_once __DIR__ . '/db.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php?redirect=profile.php'); exit;
}

$conn = get_db();
$uid = (int)$_SESSION['user_id'];

$hasAvatar = false;
$colStmt = $conn->prepare("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'avatar'");
$colStmt->execute();
$colRes = $colStmt->get_result();
if ($colRes && ($cr = $colRes->fetch_assoc())) { $hasAvatar = ((int)$cr['cnt'] > 0); }
$colStmt->close();

$hasBiografia = false;
$colStmt2 = $conn->prepare("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'biografia'");
$colStmt2->execute();
$colRes2 = $colStmt2->get_result();
if ($colRes2 && ($cr2 = $colRes2->fetch_assoc())) { $hasBiografia = ((int)$cr2['cnt'] > 0); }
$colStmt2->close();

// Se não existe, criar o campo biografia
if (!$hasBiografia) {
    try {
        $conn->query("ALTER TABLE users ADD COLUMN biografia TEXT DEFAULT NULL");
        $hasBiografia = true;
    } catch (Exception $e) {
        // Ignore if already exists or error
    }
}

// Buscar role do utilizador
$role = 'user';
$selectFields = 'id_user, username, email, first_name, last_name';
if ($hasAvatar) $selectFields .= ', avatar';
if ($hasBiografia) $selectFields .= ', biografia, role';

$stmt = $conn->prepare('SELECT ' . $selectFields . ' FROM users WHERE id_user = ? LIMIT 1');
$stmt->bind_param('i', $uid);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc() ?: null;
$stmt->close();

if ($user) {
    $role = $user['role'] ?? 'user';
}

if (!$user) { echo 'Utilizador não encontrado.'; exit; }

function e($s){ return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

$msg = '';
if (!empty($_GET['success'])) $msg = 'Perfil atualizado com sucesso.';
if (!empty($_GET['error'])) $msg = $_GET['error'];

?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Meu Perfil</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php require_once __DIR__ . '/partials/navbar.php'; ?>
<main class="container my-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6">
            <h3>Editar Perfil</h3>
            <?php if ($msg): ?><div class="alert <?php echo isset($_GET['success']) ? 'alert-success' : 'alert-danger'; ?>"><?php echo e($msg); ?></div><?php endif; ?>
            <form method="post" action="save_profile.php" enctype="multipart/form-data">
                <input type="hidden" name="id_user" value="<?php echo e($user['id_user']); ?>">
                <div class="mb-3">
                    <label class="form-label">Nome de Utilizador</label>
                    <input name="username" class="form-control" required value="<?php echo e($user['username']); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input name="email" type="email" class="form-control" value="<?php echo e($user['email']); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Nome</label>
                    <input name="first_name" class="form-control" value="<?php echo e($user['first_name']); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Sobrenome</label>
                    <input name="last_name" class="form-control" value="<?php echo e($user['last_name']); ?>">
                </div>
                <?php if (!empty($user['avatar'])): ?>
                    <div class="mb-3">
                        <label class="form-label">Foto atual</label>
                        <div><img src="<?php echo e($user['avatar']); ?>" alt="Avatar" style="max-width:150px;border-radius:6px;"></div>
                    </div>
                <?php endif; ?>
                <div class="mb-3">
                    <label class="form-label">Foto de Perfil</label>
                    <input name="avatar" type="file" class="form-control" accept="image/*">
                </div>
                <?php if ($role === 'author'): ?>
                    <hr>
                    <h5 class="mb-3">Biografia do Autor</h5>
                    <div class="mb-3">
                        <label class="form-label">Biografia</label>
                        <textarea name="biografia" class="form-control" rows="5" placeholder="Escreva uma pequena biografia sobre si..."><?php echo e($user['biografia'] ?? ''); ?></textarea>
                        <small class="text-muted">Esta biografia será visível no seu perfil público.</small>
                    </div>
                <?php endif; ?>
                <hr>
                <p class="text-muted small">Deixe as palavras-passe em branco para manter a atual.</p>
                <div class="mb-3">
                    <label class="form-label">Nova Palavra-passe</label>
                    <input name="password" type="password" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirme Nova Palavra-passe</label>
                    <input name="password_confirm" type="password" class="form-control">
                </div>
                <div>
                    <button class="btn btn-success">Salvar</button>
                    <a class="btn btn-outline-success" href="index.php">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</main>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
