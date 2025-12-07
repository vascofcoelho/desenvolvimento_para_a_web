<?php
// admin/users.php - listagem e gestão de utilizadores
session_start();
require_once __DIR__ . '/../db.php';

if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=admin/users.php');
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

if ($role !== 'admin') {
    echo 'Acesso negado.'; exit;
}

$users = [];
$sql = 'SELECT id_user, username, email, first_name, last_name, role FROM Users ORDER BY username';
if ($res = $conn->query($sql)) { while ($u = $res->fetch_assoc()) $users[] = $u; $res->free(); }

function e($s){ return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Backoffice - Utilizadores</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php require_once __DIR__ . '/../partials/navbar.php'; ?>
<main class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestão de Utilizadores</h2>
        <a class="btn btn-success" href="register_user.php">Criar Novo Utilizador</a>
    </div>

    <?php if (count($users) === 0): ?>
        <p>Nenhum utilizador encontrado.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Nome</th>
                        <th>Role</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?php echo e($u['id_user']); ?></td>
                        <td><?php echo e($u['username']); ?></td>
                        <td><?php echo e($u['email']); ?></td>
                        <td><?php echo e(trim(($u['first_name'] . ' ' . $u['last_name']))); ?></td>
                        <td><?php echo htmlspecialchars($u['role'] ?? 'user'); ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-primary" href="register_user.php?id=<?php echo e($u['id_user']); ?>">Editar</a>
                            <a class="btn btn-sm btn-danger" href="delete_user.php?id=<?php echo e($u['id_user']); ?>" onclick="return confirm('Apagar este utilizador?');">Apagar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
