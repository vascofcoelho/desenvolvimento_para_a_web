<?php
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <link rel="icon" href="imgs/logo.png">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rabbit Head Blog</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <?php require_once __DIR__ . '/partials/navbar.php'; ?>

    <?php
    // Carrega artigos da base de dados
    require_once __DIR__ . '/db.php';
    $conn = get_db();

    function e($s) { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
    function excerpt($s, $len = 150) {
        $t = strip_tags($s);
        if (mb_strlen($t) <= $len) return $t;
        $tr = mb_substr($t, 0, $len);
        $lastSpace = mb_strrpos($tr, ' ');
        if ($lastSpace !== false) $tr = mb_substr($tr, 0, $lastSpace);
        return $tr . '...';
    }

    // Paginação
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $per_page = 9; // número total por página (inclui destaque na página 1)

    // Total de artigos para calcular páginas
    $total = 0;
    $countRes = $conn->query('SELECT COUNT(*) as cnt FROM artigos');
    if ($countRes) {
        $r = $countRes->fetch_assoc();
        $total = (int) ($r['cnt'] ?? 0);
        $countRes->free();
    }

    $total_pages = $total > 0 ? (int) ceil($total / $per_page) : 1;
    if ($page > $total_pages) $page = $total_pages;

    $offset = ($page - 1) * $per_page;

    $articles = [];
    $sql = "SELECT id_artigo, titulo, texto_artigo, autor, DATE_FORMAT(data, '%d/%m/%Y') as data, foto, URL_slug FROM artigos ORDER BY data DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $per_page, $offset);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $articles[] = $row;
    }
    $stmt->close();

    // Resolve author name for each article (supports autor as id or legacy username)
    foreach ($articles as &$a) {
        $a['author_label'] = $a['autor'];
        $a['author_id'] = null;
        $a['author_username'] = null;
        if (!empty($a['autor'])) {
            if (is_numeric($a['autor'])) {
                $s = $conn->prepare('SELECT id_user, username, first_name, last_name FROM users WHERE id_user = ? LIMIT 1');
                $aid = (int)$a['autor'];
                $s->bind_param('i', $aid);
                $s->execute();
                $ur = $s->get_result()->fetch_assoc();
                if ($ur) {
                    $a['author_label'] = trim(($ur['first_name'] ?? '') . ' ' . ($ur['last_name'] ?? '')) ?: ($ur['username'] ?? $a['author_label']);
                    $a['author_id'] = (int)$ur['id_user'];
                    $a['author_username'] = $ur['username'] ?? null;
                }
                $s->close();
            } else {
                $s = $conn->prepare('SELECT id_user, username, first_name, last_name FROM users WHERE username = ? LIMIT 1');
                $s->bind_param('s', $a['autor']);
                $s->execute();
                $ur = $s->get_result()->fetch_assoc();
                if ($ur) {
                    $a['author_label'] = trim(($ur['first_name'] ?? '') . ' ' . ($ur['last_name'] ?? '')) ?: ($ur['username'] ?? $a['author_label']);
                    $a['author_id'] = (int)$ur['id_user'];
                    $a['author_username'] = $ur['username'] ?? null;
                }
                $s->close();
            }
        }
    }
    unset($a);
    ?>

    <!-- Cabeçalho -->
    <header class="bg-success text-white text-center py-3">
        <div class="container">
            <h1 class="display-7">Rabbit Head Blog</h1>
        </div>
    </header>

    <!-- Lista de Artigos -->
    <main class="container my-5">
        <?php if (count($articles) === 0): ?>
            <p class="text-center">Ainda não existem artigos publicados.</p>
        <?php else: ?>
            <?php
            if ($page === 1) {
                // Artigo em destaque = primeiro elemento
                $featured = $articles[0];
                $other = array_slice($articles, 1);
                // Resolver caminho da imagem para featured
                $feat_img = '';
                if (!empty($featured['foto'])) {
                    $fv = trim($featured['foto']);
                    if (preg_match('#^https?://#i', $fv) || strpos($fv, '/') === 0) $feat_img = $fv; else $feat_img = ltrim($fv, '/');
                }
                $feat_link = 'artigo.php?slug=' . urlencode($featured['URL_slug']);
            } else {
                // Sem destaque em páginas posteriores
                $featured = null;
                $other = $articles;
            }
            ?>

            <?php if ($featured): ?>
            <!-- Início do artigo em destaque -->
            <div class="mb-5">
                
                <div class="card featured-article">
                    <div class="row g-0">
                        <div class="col-md-6">
                            <?php if ($feat_img): ?>
                                <img src="<?php echo e($feat_img); ?>" class="img-fluid featured-image" alt="<?php echo e($featured['titulo']); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <div class="card-body featured-body">
                                <div>
                                    <span class="badge bg-success mb-3">Destaque</span>
                                    <h2 class="card-title featured-title"><?php echo e($featured['titulo']); ?></h2>
                                    <p class="card-text text-muted mb-3"><?php echo e(excerpt($featured['texto_artigo'], 220)); ?></p>
                                    <p class="card-text mb-4"><small class="text-muted">
                                        <?php if (!empty($featured['author_id'])): ?>
                                            <a href="perfil_autor.php?id=<?php echo (int)$featured['author_id']; ?>" class="text-decoration-none text-reset">
                                                <?php echo e($featured['author_label']); ?>
                                            </a>
                                        <?php else: ?>
                                            <?php echo e($featured['author_label']); ?>
                                        <?php endif; ?>
                                         • <i class="bi bi-calendar-event"></i> <?php echo e($featured['data']); ?>
                                    </small></p>
                                </div>
                                <div class="featured-actions">
                                    <a href="<?php echo e($feat_link); ?>" class="btn btn-success btn-lg">
                                        Ver artigo completo <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Outros Artigos -->
            <h4 class="mb-4">Artigos Recentes</h4>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($other as $a):
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
                                <img src="<?php echo e($img); ?>" class="card-img-top" alt="<?php echo e($a['titulo']); ?>">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo e($a['titulo']); ?></h5>
                                <p class="card-text"><?php echo e(excerpt($a['texto_artigo'], 120)); ?></p>
                                <a href="<?php echo e($link); ?>" class="btn btn-success mt-2 d-none d-md-inline-block">
                                    Ver artigo completo <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                            <div class="card-footer d-flex justify-content-between small text-muted">
                                <span>Autor: 
                                    <?php if (!empty($a['author_id'])): ?>
                                        <a href="perfil_autor.php?id=<?php echo (int)$a['author_id']; ?>" class="text-decoration-none text-reset">
                                            <?php echo e($a['author_label']); ?>
                                        </a>
                                    <?php else: ?>
                                        <?php echo e($a['author_label']); ?>
                                    <?php endif; ?>
                                </span>
                                <span><i class="bi bi-calendar-event"></i> <?php echo e($a['data']); ?></span>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Paginação -->
            <?php if ($total_pages > 1): ?>
                <?php
                // Gera intervalo de páginas a mostrar (janela)
                $window = 5;
                $start = max(1, $page - intval($window/2));
                $end = min($total_pages, $start + $window - 1);
                if ($end - $start + 1 < $window) {
                    $start = max(1, $end - $window + 1);
                }
                ?>
                <nav aria-label="Paginação" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo max(1, $page-1); ?>" aria-label="Anterior">&laquo; Anterior</a>
                        </li>
                        <?php for ($p = $start; $p <= $end; $p++): ?>
                            <li class="page-item <?php echo ($p == $page) ? 'active' : ''; ?>"><a class="page-link" href="?page=<?php echo $p; ?>"><?php echo $p; ?></a></li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo min($total_pages, $page+1); ?>" aria-label="Próximo">Próximo &raquo;</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <?php require_once __DIR__ . '/partials/footer.php'; ?>
