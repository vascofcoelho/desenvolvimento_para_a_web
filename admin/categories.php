<?php
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

$cats = [];
$sql = 'SELECT c.id_categoria, c.categoria, COUNT(a.id_artigo) as posts_count 
        FROM Categorias c 
        LEFT JOIN Artigos a ON c.id_categoria = a.id_categoria 
        GROUP BY c.id_categoria, c.categoria 
        ORDER BY c.categoria ASC';
if ($res = $conn->query($sql)) { while ($c = $res->fetch_assoc()) $cats[] = $c; $res->free(); }

function e($s){ return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Backoffice - Categorias</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php require_once __DIR__ . '/../partials/navbar.php'; ?>
<main class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestão de Categorias</h2>
        <a class="btn btn-success" href="category_edit.php">Criar Categoria</a>
    </div>

    <?php if (count($cats) === 0): ?>
        <p>Nenhuma categoria encontrada.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Categoria</th>
                        <th class="text-center">Posts</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($cats as $c): ?>
                    <tr>
                        <td><?php echo e($c['id_categoria']); ?></td>
                        <td><?php echo e($c['categoria']); ?></td>
                        <td class="text-center"><?php echo (int)$c['posts_count']; ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-success" href="category_edit.php?id=<?php echo e($c['id_categoria']); ?>">Editar</a>
                            <a class="btn btn-sm btn-danger" href="delete_category.php?id=<?php echo e($c['id_categoria']); ?>" onclick="return confirm('Apagar esta categoria? Os artigos vão ficar sem categoria.');">Apagar</a>
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
