<?php
// perfil_autor.php
// Página pública para visualizar perfil do autor e seus artigos

require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$author_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$author_username = isset($_GET['username']) ? trim($_GET['username']) : '';

if ($author_id <= 0 && $author_username === '') {
    http_response_code(404);
    echo 'Autor não encontrado.';
    exit;
}

$conn = get_db();

// Verificar se campo biografia existe
$hasBiografia = false;
$colStmt = $conn->prepare("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Users' AND COLUMN_NAME = 'biografia'");
$colStmt->execute();
$colRes = $colStmt->get_result();
if ($colRes && ($cr = $colRes->fetch_assoc())) { $hasBiografia = ((int)$cr['cnt'] > 0); }
$colStmt->close();

// Se não existe, criar o campo biografia
if (!$hasBiografia) {
    try {
        $conn->query("ALTER TABLE Users ADD COLUMN biografia TEXT DEFAULT NULL");
        $hasBiografia = true;
    } catch (Exception $e) {
        // Ignore if already exists or error
    }
}

// Buscar informações do autor
$author = null;
$selectFields = 'id_user, username, first_name, last_name, avatar, role';
if ($hasBiografia) $selectFields .= ', biografia';

if ($author_id > 0) {
    $stmt = $conn->prepare('SELECT ' . $selectFields . ' FROM Users WHERE id_user = ? LIMIT 1');
    $stmt->bind_param('i', $author_id);
} else {
    $stmt = $conn->prepare('SELECT ' . $selectFields . ' FROM Users WHERE username = ? LIMIT 1');
    $stmt->bind_param('s', $author_username);
}
$stmt->execute();
$res = $stmt->get_result();
$author = $res->fetch_assoc();
$stmt->close();

if (!$author) {
    http_response_code(404);
    echo 'Autor não encontrado.';
    exit;
}

// Verificar se é autor (role = 'author')
if (($author['role'] ?? '') !== 'author') {
    http_response_code(404);
    echo 'Autor não encontrado.';
    exit;
}

$author_id = (int)$author['id_user'];
$author_name = trim(($author['first_name'] ?? '') . ' ' . ($author['last_name'] ?? '')) ?: ($author['username'] ?? '');
$author_avatar = '';
if (!empty($author['avatar'])) {
    $av = trim($author['avatar']);
    $author_avatar = (preg_match('#^https?://#i', $av) || strpos($av, '/') === 0) ? $av : ltrim($av, '/');
}
$biografia = $author['biografia'] ?? '';

// Buscar artigos do autor
// O campo autor pode ser ID ou username (legacy)
$articles = [];
$sql = "SELECT a.id_artigo, a.titulo, a.URL_slug, a.foto, 
        DATE_FORMAT(a.data, '%d/%m/%Y') as data,
        (SELECT COUNT(*) FROM Likes l WHERE l.id_artigo = a.id_artigo) AS likes_count,
        (SELECT COUNT(*) FROM Comentarios c WHERE c.id_artigo = a.id_artigo) AS comments_count,
        c.categoria
        FROM Artigos a
        LEFT JOIN Categorias c ON a.id_categoria = c.id_categoria
        WHERE (a.autor = ? OR a.autor = ?)
        ORDER BY a.data DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('is', $author_id, $author['username']);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $articles[] = $row;
}
$stmt->close();

function e($s) { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function excerpt($s, $len = 150) {
    $t = strip_tags($s);
    if (mb_strlen($t) <= $len) return $t;
    $tr = mb_substr($t, 0, $len);
    $lastSpace = mb_strrpos($tr, ' ');
    if ($lastSpace !== false) $tr = mb_substr($tr, 0, $lastSpace);
    return $tr . '...';
}
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?php echo e($author_name); ?> - Rabbit Head</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php require_once __DIR__ . '/partials/navbar.php'; ?>

    <main class="container my-5">
        <!-- Perfil do Autor -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <?php if (!empty($author_avatar)): ?>
                                    <img src="<?php echo e($author_avatar); ?>" alt="Avatar" class="rounded-circle" style="width:120px;height:120px;object-fit:cover;">
                                <?php else: ?>
                                    <i class="bi bi-person-circle" style="font-size:120px;color:#6c757d;"></i>
                                <?php endif; ?>
                            </div>
                            <div class="col">
                                <h2 class="mb-2"><?php echo e($author_name); ?></h2>
                                <?php if (!empty($biografia)): ?>
                                    <p class="mb-0"><?php echo nl2br(e($biografia)); ?></p>
                                <?php else: ?>
                                    <p class="text-muted mb-0"><em>Este autor ainda não adicionou uma biografia.</em></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Artigos do Autor -->
        <div class="row">
            <div class="col-12">
                <h3 class="mb-4">Artigos Publicados (<?php echo count($articles); ?>)</h3>
                <?php if (count($articles) === 0): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Este autor ainda não publicou nenhum artigo.
                    </div>
                <?php else: ?>
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        <?php foreach ($articles as $a): 
                            $img = '';
                            if (!empty($a['foto'])) {
                                $fv = trim($a['foto']);
                                if (preg_match('#^https?://#i', $fv) || strpos($fv, '/') === 0) $img = $fv; else $img = ltrim($fv, '/');
                            }
                            $link = 'artigo.php?slug=' . urlencode($a['URL_slug']);
                        ?>
                        <div class="col">
                            <a href="<?php echo e($link); ?>" class="text-decoration-none text-reset article-link">
                                <div class="card h-100">
                                    <?php if ($img): ?>
                                        <img src="<?php echo e($img); ?>" class="card-img-top" alt="<?php echo e($a['titulo']); ?>" style="height:200px;object-fit:cover;">
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo e($a['titulo']); ?></h5>
                                        <?php if (!empty($a['categoria'])): ?>
                                            <span class="badge bg-success mb-2"><?php echo e($a['categoria']); ?></span>
                                        <?php endif; ?>
                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <small class="text-muted">
                                                <i class="bi bi-calendar-event"></i> <?php echo e($a['data']); ?>
                                            </small>
                                            <div class="text-muted small">
                                                <i class="bi bi-heart"></i> <?php echo (int)$a['likes_count']; ?>
                                                <i class="bi bi-chat-left-text ms-2"></i> <?php echo (int)$a['comments_count']; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php require_once __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

