<?php
session_start();
require_once __DIR__ . '/../db.php';

if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=admin/category_edit.php');
    exit;
}

$conn = get_db();
$uid = (int)($_SESSION['user_id'] ?? 0);
$stmt = $conn->prepare('SELECT role FROM Users WHERE id_user = ? LIMIT 1');
$stmt->bind_param('i', $uid);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
$stmt->close();
$role = $r['role'] ?? 'user';
if ($role !== 'admin') { echo 'Acesso negado.'; exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$cat = ['categoria' => ''];
if ($id > 0) {
    $stmt = $conn->prepare('SELECT id_categoria, categoria FROM Categorias WHERE id_categoria = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($r = $res->fetch_assoc()) $cat = $r;
    $stmt->close();
}

function e($s){ return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $id ? 'Editar' : 'Criar'; ?> Categoria</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php require_once __DIR__ . '/../partials/navbar.php'; ?>
<main class="container my-5">
    <h3><?php echo $id ? 'Editar' : 'Criar'; ?> Categoria</h3>
    <form method="post" action="save_category.php">
        <input type="hidden" name="id_categoria" value="<?php echo e($id); ?>">
        <div class="mb-3">
            <label class="form-label">Categoria</label>
            <input name="categoria" class="form-control" required value="<?php echo e($cat['categoria']); ?>">
        </div>
        <button class="btn btn-success">Guardar</button>
        <a class="btn btn-outline-success" href="categories.php">Voltar</a>
    </form>
</main>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
