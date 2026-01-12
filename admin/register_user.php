<?php
session_start();
require_once __DIR__ . '/../db.php';

if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=admin/register_user.php');
    exit;
}

$conn = get_db();
$uid = (int)($_SESSION['user_id'] ?? 0);
$stmt = $conn->prepare('SELECT role FROM users WHERE id_user = ? LIMIT 1');
$stmt->bind_param('i', $uid);
$stmt->execute();
$res = $stmt->get_result();
$role = 'user';
if ($r = $res->fetch_assoc()) $role = $r['role'] ?? 'user';
$stmt->close();

if ($role !== 'admin') { echo 'Acesso negado.'; exit; }

 $editing = false;
 $user = ['id_user'=>0,'username'=>'','email'=>'','first_name'=>'','last_name'=>'','role'=>'user'];
if (!empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare('SELECT id_user, username, email, first_name, last_name, role FROM users WHERE id_user = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($r = $res->fetch_assoc()) { $user = $r; $editing = true; }
    $stmt->close();
}

function e($s){ return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $editing ? 'Editar' : 'Criar'; ?> Utilizador</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php require_once __DIR__ . '/../partials/navbar.php'; ?>
<main class="container my-5">
    <h2><?php echo $editing ? 'Editar' : 'Criar'; ?> Utilizador</h2>
    <form method="post" action="save_user.php">
        <input type="hidden" name="id_user" value="<?php echo e($user['id_user']); ?>">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input class="form-control" name="username" value="<?php echo e($user['username']); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" value="<?php echo e($user['email']); ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Nome</label>
            <div class="d-flex gap-2">
                <input class="form-control" name="first_name" placeholder="Primeiro nome" value="<?php echo e($user['first_name']); ?>">
                <input class="form-control" name="last_name" placeholder="Sobrenome" value="<?php echo e($user['last_name']); ?>">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Password <?php if ($editing) echo '(deixe em branco para manter)'; ?></label>
            <input type="password" class="form-control" name="password">
        </div>
        <div class="mb-3">
            <label class="form-label">Role</label>
            <?php if ($editing && (int)($user['id_user'] ?? 0) === $uid): ?>
                <!-- Prevent user from changing their own role -->
                <div class="form-control"><?php echo htmlspecialchars($user['role'] ?? 'user'); ?> <small class="text-muted">(Não pode alterar a sua própria role)</small></div>
                <input type="hidden" name="role" value="<?php echo htmlspecialchars($user['role'] ?? 'user'); ?>">
            <?php else: ?>
                <select name="role" class="form-select">
                    <option value="user" <?php if(($user['role'] ?? '') === 'user') echo 'selected'; ?>>User</option>
                    <option value="author" <?php if(($user['role'] ?? '') === 'author') echo 'selected'; ?>>Author</option>
                    <option value="moderator" <?php if(($user['role'] ?? '') === 'moderator') echo 'selected'; ?>>Moderator</option>
                    <option value="admin" <?php if(($user['role'] ?? '') === 'admin') echo 'selected'; ?>>Admin</option>
                </select>
            <?php endif; ?>
        </div>
        <div>
            <button class="btn btn-success" type="submit">Salvar</button>
            <a class="btn btn-outline-success" href="users.php">Cancelar</a>
        </div>
    </form>
</main>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
