<?php
session_start();
require_once __DIR__ . '/../db.php';

if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=admin/index.php');
    exit;
}

$conn = get_db();
$uid = (int)($_SESSION['user_id'] ?? 0);
$stmt = $conn->prepare('SELECT role, username FROM Users WHERE id_user = ? LIMIT 1');
$stmt->bind_param('i', $uid);
$stmt->execute();
$res = $stmt->get_result();
$role = 'user'; $username = '';
if ($r = $res->fetch_assoc()) { $role = $r['role'] ?? 'user'; $username = $r['username']; }
$stmt->close();

if ($role !== 'admin') { echo 'Acesso negado. Só administradores podem aceder ao dashboard.'; exit; }

?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php require_once __DIR__ . '/../partials/navbar.php'; ?>
<main class="container my-5">
    <h2>Dashboard Admin</h2>
    <p class="text-muted">Bem-vindo, <?php echo htmlspecialchars($username); ?>.</p>

    <div class="row g-3">
        <div class="col-12 col-md-6 col-lg-3">
            <a class="btn btn-success w-100 py-3" href="articles.php">Gestão de Artigos</a>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <a class="btn btn-success w-100 py-3" href="comments.php">Gestão de Comentários</a>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <a class="btn btn-success w-100 py-3" href="categories.php">Gestão de Categorias</a>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <a class="btn btn-success w-100 py-3" href="users.php">Gestão de Utilizadores</a>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <a class="btn btn-success w-100 py-3" href="../estatisticas.php">Estatísticas</a>
        </div>
    </div>

</main>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
