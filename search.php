<?php
session_start();
require_once __DIR__ . '/db.php';

// Get filters from GET
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$cat = isset($_GET['categoria']) && $_GET['categoria'] !== '' ? (int)$_GET['categoria'] : null;
$author = isset($_GET['autor']) && $_GET['autor'] !== '' ? (int)$_GET['autor'] : null;
$order = (isset($_GET['ordem']) && strtolower($_GET['ordem']) === 'asc') ? 'ASC' : 'DESC';

$conn = get_db();

// Load categories for filter
$categories = [];
$cres = $conn->query('SELECT id_categoria, categoria FROM categorias ORDER BY categoria');
if ($cres) { while ($c = $cres->fetch_assoc()) $categories[] = $c; $cres->free(); }

// Load authors (users with articles)
$authors = [];
$ares = $conn->query('SELECT DISTINCT u.id_user, u.username, u.first_name, u.last_name FROM users u JOIN artigos a ON a.autor = u.id_user ORDER BY u.username');
if ($ares) { while ($u = $ares->fetch_assoc()) $authors[] = $u; $ares->free(); }

// Build query with optional search
$params = [];
$types = '';
$where = [];
$sql = 'SELECT a.id_artigo, a.titulo, a.URL_slug, a.foto, DATE_FORMAT(a.data, "%d/%m/%Y %H:%i") as data, c.categoria, u.username as author_username, u.first_name as author_first, u.last_name as author_last, u.avatar as author_avatar FROM artigos a LEFT JOIN categorias c ON a.id_categoria = c.id_categoria LEFT JOIN users u ON a.autor = u.id_user';

if ($q !== '') {
    $where[] = '(a.titulo LIKE ? OR a.texto_artigo LIKE ?)';
    $types .= 'ss';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}
if ($cat) { $where[] = 'a.id_categoria = ?'; $types .= 'i'; $params[] = $cat; }
if ($author) { $where[] = 'a.autor = ?'; $types .= 'i'; $params[] = $author; }

if (count($where)) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY a.data ' . $order;

$articles = [];
if ($stmt = $conn->prepare($sql)) {
    if (count($params)) {
        // bind parameters dynamically
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $articles[] = $r;
    $stmt->close();
}

function e($s){ return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Pesquisar Artigos - Rabbit Head</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php require_once __DIR__ . '/partials/navbar.php'; ?>
<main class="container my-5">
    <h3>Pesquisar Artigos</h3>
    <form method="get" class="row g-3 mb-4">
        <div class="col-md-4">
            <label class="form-label">Pesquisar texto</label>
            <input name="q" class="form-control" value="<?php echo e($q); ?>" placeholder="Pesquisar título ou conteúdo...">
        </div>
        <div class="col-md-3">
            <label class="form-label">Categoria</label>
            <select name="categoria" class="form-select">
                <option value="">-- Todas --</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?php echo e($c['id_categoria']); ?>" <?php echo ($cat== $c['id_categoria']) ? 'selected' : ''; ?>><?php echo e($c['categoria']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Autor</label>
            <select name="autor" class="form-select">
                <option value="">-- Todos --</option>
                <?php foreach ($authors as $u): $label = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')) ?: $u['username']; ?>
                    <option value="<?php echo e($u['id_user']); ?>" <?php echo ($author == $u['id_user']) ? 'selected' : ''; ?>><?php echo e($label); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Ordenar</label>
            <select name="ordem" class="form-select">
                <option value="desc" <?php echo ($order==='DESC') ? 'selected' : ''; ?>>Mais recentes</option>
                <option value="asc" <?php echo ($order==='ASC') ? 'selected' : ''; ?>>Mais antigos</option>
            </select>
        </div>
        <div class="col-12 d-flex">
            <button class="btn btn-success me-2">Pesquisar</button>
            <a class="btn btn-success" href="search.php">Limpar</a>
        </div>
    </form>

    <?php if (count($articles) === 0): ?>
        <p>Nenhum artigo encontrado com os critérios selecionados.</p>
    <?php else: ?>
        <div class="row">
            <?php foreach ($articles as $a): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <?php if (!empty($a['foto'])): ?>
                            <img class="card-img-top" src="<?php echo e(ltrim($a['foto'], '/')); ?>" alt="">
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo e($a['titulo']); ?></h5>
                            <p class="card-text text-muted small mb-2"><?php echo e($a['data']); ?> — <?php echo e($a['categoria']); ?></p>
                            <p class="mt-auto"><a href="artigo.php?slug=<?php echo e($a['URL_slug']); ?>" class="stretched-link">Ver artigo</a></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</main>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
