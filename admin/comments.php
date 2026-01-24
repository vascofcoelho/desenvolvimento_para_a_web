<?php
session_start();
require_once __DIR__ . '/../db.php';

if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=admin/comments.php');
    exit;
}

$conn = get_db();
$uid = (int)($_SESSION['user_id'] ?? 0);
$stmt = $conn->prepare('SELECT role, username FROM users WHERE id_user = ? LIMIT 1');
$stmt->bind_param('i', $uid);
$stmt->execute();
$res = $stmt->get_result();
$role = 'user'; $username = '';
if ($r = $res->fetch_assoc()) $role = $r['role'] ?? 'user';
$stmt->close();

// allow admins, moderators, and authors (authors only for their own articles)
if ($role !== 'admin' && $role !== 'moderator' && $role !== 'author') { echo 'Acesso negado.'; exit; }

// If an article is selected, show comments for that article. Otherwise show list of articles.
$comments = [];
$article_id = isset($_GET['article_id']) ? (int)$_GET['article_id'] : 0;

if ($article_id > 0) {
    $sql = 'SELECT c.id_comentario, c.comentario, DATE_FORMAT(c.data, "%d/%m/%Y %H:%i") as data, u.username, a.titulo, a.URL_slug FROM comentarios c LEFT JOIN users u ON c.id_user = u.id_user LEFT JOIN artigos a ON c.id_artigo = a.id_artigo WHERE c.id_artigo = ? ORDER BY c.data DESC';
    $q = $conn->prepare($sql);
    $q->bind_param('i', $article_id);
    $q->execute();
    $res = $q->get_result();
    while ($row = $res->fetch_assoc()) $comments[] = $row;
    $res->free();
    $q->close();
} else {
    // list articles for selection
    $articles = [];
    if ($role === 'author') {
        $ast = $conn->prepare('SELECT id_artigo, titulo FROM artigos WHERE autor = ? ORDER BY data DESC');
        $ast->bind_param('i', $uid);
        $ast->execute();
        $ares = $ast->get_result();
        while ($r = $ares->fetch_assoc()) $articles[] = $r;
        $ares->free();
        $ast->close();
    } else {
        $ares = $conn->query('SELECT id_artigo, titulo FROM artigos ORDER BY data DESC');
        if ($ares) { while ($r = $ares->fetch_assoc()) $articles[] = $r; $ares->free(); }
    }
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
        <div>
            <?php if ($article_id > 0): ?>
                <?php if ($role === 'author'): ?>
                    <a class="btn btn-success" href="articles.php">Voltar</a>
                <?php else: ?>
                    <a class="btn btn-success" href="#" onclick="history.back(); return false;">Voltar</a>
                <?php endif; ?>
            <?php else: ?>
                <?php if ($role === 'admin'): ?>
                    <a class="btn btn-success" href="index.php">Voltar</a>
                <?php elseif ($role === 'author'): ?>
                    <a class="btn btn-success" href="articles.php">Voltar</a>
                <?php else: ?>
                    <a class="btn btn-success" href="../index.php">Voltar</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($article_id === 0): ?>
        <?php if (empty($articles)): ?>
            <p>Nenhum artigo encontrado.</p>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($articles as $a): ?>
                    <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="comments.php?article_id=<?php echo e($a['id_artigo']); ?>">
                        <?php echo e($a['titulo']); ?>
                        <span class="btn btn-success">Ver comentários</span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <?php if (count($comments) === 0): ?>
            <p>Nenhum comentário encontrado para este artigo.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Utilizador</th>
                            <th>Comentário</th>
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
    <?php endif; ?>
</main>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
