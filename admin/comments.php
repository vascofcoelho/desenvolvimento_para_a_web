<?php
// admin/comments.php - listagem e gestão de comentários
session_start();
require_once __DIR__ . '/../db.php';

if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=admin/comments.php');
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

if ($role !== 'admin' && $role !== 'moderator') { echo 'Acesso negado.'; exit; }

$comments = [];
$sql = 'SELECT c.id_comentario, c.comentario, DATE_FORMAT(c.data, "%d/%m/%Y %H:%i") as data, u.username, a.titulo, a.URL_slug FROM Comentarios c LEFT JOIN Users u ON c.id_user = u.id_user LEFT JOIN Artigos a ON c.id_artigo = a.id_artigo ORDER BY c.data DESC';
if ($res = $conn->query($sql)) {
    while ($row = $res->fetch_assoc()) $comments[] = $row;
    $res->free();
}

function e($s){ return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Backoffice - Comentários</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php require_once __DIR__ . '/../partials/navbar.php'; ?>
<main class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestão de Comentários</h2>
        <?php if (!empty($role) && $role === 'moderator'): ?>
            <a class="btn btn-secondary" href="../index.php">Voltar aos Artigos</a>
        <?php else: ?>
            <a class="btn btn-secondary" href="articles.php">Voltar aos Artigos</a>
        <?php endif; ?>
    </div>

    <?php if (count($comments) === 0): ?>
        <p>Nenhum comentário encontrado.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Utilizador</th>
                        <th>Comentário</th>
                        <th>Artigo</th>
                        <th>Data</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($comments as $c): ?>
                    <tr>
                        <td><?php echo e($c['id_comentario']); ?></td>
                        <td><?php echo e($c['username'] ?: 'Utilizador'); ?></td>
                        <td style="max-width:400px;"><?php echo nl2br(e($c['comentario'])); ?></td>
                        <td><?php if (!empty($c['URL_slug'])): ?><a href="../artigo.php?slug=<?php echo urlencode($c['URL_slug']); ?>" target="_blank"><?php echo e($c['titulo']); ?></a><?php else: ?>-<?php endif; ?></td>
                        <td><?php echo e($c['data']); ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-danger" href="delete_comment.php?id=<?php echo e($c['id_comentario']); ?>" onclick="return confirm('Apagar comentário?');">Apagar</a>
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
