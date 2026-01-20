<?php
session_start();
require_once __DIR__ . '/../db.php';

if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=admin/articles.php');
    exit;
}

$conn = get_db();
$uid = (int)($_SESSION['user_id'] ?? 0);
$stmt = $conn->prepare('SELECT role, username FROM users WHERE id_user = ? LIMIT 1');
$stmt->bind_param('i', $uid);
$stmt->execute();
$res = $stmt->get_result();
$role = 'user'; $username = '';
if ($r = $res->fetch_assoc()) { $role = $r['role'] ?? 'user'; $username = $r['username']; }
$stmt->close();

if ($role !== 'admin' && $role !== 'author') { echo 'Acesso negado. É necessário ser administrador ou autor.'; exit; }

$articles = [];
$sql = 'SELECT a.id_artigo, a.titulo, a.autor, DATE_FORMAT(a.data, "%d/%m/%Y %H:%i") as data, a.URL_slug, c.categoria, a.foto, u.username as author_username, u.first_name as author_first, u.last_name as author_last, u.avatar as author_avatar FROM artigos a LEFT JOIN categorias c ON a.id_categoria = c.id_categoria LEFT JOIN users u ON a.autor = u.id_user '
    . ($role === 'author' ? 'WHERE a.autor = ? ' : '')
    . 'ORDER BY a.data DESC';
if ($role === 'author') {
    $q = $conn->prepare($sql);
    $q->bind_param('i', $uid);
    $q->execute();
    $res = $q->get_result();
    while ($r = $res->fetch_assoc()) $articles[] = $r;
    $res->free();
    $q->close();
} else {
    if ($res = $conn->query($sql)) {
        while ($r = $res->fetch_assoc()) $articles[] = $r;
        $res->free();
    }
}

function e($s){ return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Backoffice - Artigos</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php require_once __DIR__ . '/../partials/navbar.php'; ?>
<main class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestão de Artigos</h2>
        <div>
            <a class="btn btn-success" href="article_edit.php">Criar Novo Artigo</a>
            <a class="btn btn-success ms-2" href="../estatisticas.php">Estatísticas</a>
            <a class="btn btn-success ms-2" href="comments.php">Gestão de Comentários</a>
            <a class="btn btn-success me-2" href="index.php">Voltar</a>
        </div>
    </div>

    <?php if (count($articles) === 0): ?>
        <p>Nenhum artigo encontrado.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Categoria</th>
                        <th>Autor</th>
                        <th>Data</th>
                        <th>Foto</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($articles as $a): ?>
                    <tr>
                        <td><?php echo e($a['id_artigo']); ?></td>
                        <td><?php echo e($a['titulo']); ?></td>
                        <td><?php echo e($a['categoria']); ?></td>
                        <td>
                            <?php
                                    $author_label = htmlspecialchars($a['author_username'] ?? ($a['autor'] ?? ''));
                                    if (!empty($a['author_first']) || !empty($a['author_last'])) {
                                        $author_label = htmlspecialchars(trim(($a['author_first'] ?? '') . ' ' . ($a['author_last'] ?? '')) . ' (' . ($a['author_username'] ?? '') . ')');
                                    }
                                    // Build avatar src as root-relative so it works from /admin/ pages
                                    $author_avatar_src = '';
                                    if (!empty($a['author_avatar'])) {
                                        $av = trim($a['author_avatar']);
                                        if (preg_match('#^https?://#i', $av)) {
                                            $author_avatar_src = $av;
                                        } else {
                                            // Normalize to a root-relative path: compute project base from SCRIPT_NAME
                                            $scriptPath = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
                                            $parts = explode('/', trim($scriptPath, '/'));
                                            $base = '';
                                            if (count($parts) > 0 && $parts[0] !== '') $base = '/' . $parts[0];
                                            // If stored path starts with '/', leave it trimmed; otherwise ensure it does not duplicate slashes
                                            $author_avatar_src = $base . '/' . ltrim($av, '/');
                                        }
                                    }
                                    if (!empty($author_avatar_src)) {
                                        echo '<img src="' . e($author_avatar_src) . '" style="width:32px;height:32px;border-radius:6px;object-fit:cover;margin-right:8px;"> ' . $author_label;
                                    } else {
                                        echo $author_label;
                                    }
                            ?>
                        </td>
                        <td><?php echo e($a['data']); ?></td>
                        <td><?php if (!empty($a['foto'])): ?><img src="../imgs/<?php echo e(basename($a['foto'])); ?>" style="height:40px;"><?php endif; ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-success" href="article_edit.php?id=<?php echo e($a['id_artigo']); ?>">Editar</a>
                            <a class="btn btn-sm btn-danger" href="delete_article.php?id=<?php echo e($a['id_artigo']); ?>" onclick="return confirm('Apagar este artigo?');">Apagar</a>
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

