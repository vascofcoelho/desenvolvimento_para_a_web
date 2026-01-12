<?php
// estatisticas.php
// Mostra estatísticas de artigos para admin e para autores (apenas os próprios).

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/db.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php?redirect=estatisticas.php');
    exit;
}

$conn = get_db();
$uid = (int)($_SESSION['user_id'] ?? 0);

$stmt = $conn->prepare('SELECT role, username FROM users WHERE id_user = ? LIMIT 1');
$stmt->bind_param('i', $uid);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
$stmt->close();

$role = $r['role'] ?? 'user';
$username = $r['username'] ?? '';

// Permitir apenas admin e author (authors só vêem os seus artigos)
if ($role !== 'admin' && $role !== 'author') {
    http_response_code(403);
    echo 'Acesso negado.';
    exit;
}

$articles = [];
$sql = 'SELECT a.id_artigo, a.titulo, a.URL_slug,
        (SELECT COUNT(*) FROM likes l WHERE l.id_artigo = a.id_artigo) AS likes_count,
        (SELECT COUNT(*) FROM comentarios c WHERE c.id_artigo = a.id_artigo) AS comments_count,
        DATE_FORMAT(a.data, "%d/%m/%Y") as data,
        c.categoria
        FROM artigos a
        LEFT JOIN categorias c ON a.id_categoria = c.id_categoria';

if ($role === 'author') {
    $sql .= ' WHERE (a.autor = ? OR a.autor = ?) ORDER BY a.data DESC';
    $q = $conn->prepare($sql);
    $q->bind_param('is', $uid, $username);
    $q->execute();
    $res = $q->get_result();
    while ($row = $res->fetch_assoc()) $articles[] = $row;
    $res->free();
    $q->close();
} else {
    $sql .= ' ORDER BY a.data DESC';
    if ($res = $conn->query($sql)) {
        while ($row = $res->fetch_assoc()) $articles[] = $row;
        $res->free();
    }
}

// Totais
$totals = ['articles' => 0];
foreach ($articles as $a) {
    $totals['articles']++;
}

function e($s) { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estatísticas - Rabbit Head</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php require_once __DIR__ . '/partials/navbar.php'; ?>

<main class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Estatísticas</h2>
        <a class="btn btn-success" href="index.php">Voltar</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-2">Artigos</h5>
                    <p class="display-6 mb-0"><?php echo (int)$totals['articles']; ?></p>
                </div>
            </div>
        </div>
    </div>

    <?php if (count($articles) === 0): ?>
        <p class="text-muted">Sem artigos para apresentar.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Categoria</th>
                        <th class="text-center">Likes</th>
                        <th class="text-center">Comentários</th>
                        <th>Data</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($articles as $a): ?>
                    <tr>
                        <td><?php echo e($a['titulo']); ?></td>
                        <td><?php echo e($a['categoria'] ?? '-'); ?></td>
                        <td class="text-center"><?php echo (int)$a['likes_count']; ?></td>
                        <td class="text-center"><?php echo (int)$a['comments_count']; ?></td>
                        <td><?php echo e($a['data'] ?? ''); ?></td>
                        <td class="text-end">
                            <?php if (!empty($a['URL_slug'])): ?>
                                <a class="btn btn-sm btn-success" href="artigo.php?slug=<?php echo urlencode($a['URL_slug']); ?>" target="_blank">Ver artigo</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

